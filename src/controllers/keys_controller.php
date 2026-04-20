<?php

route('GET', '/keys', function($params) {
    requireLogin();
    $user = currentUser();
    $db = getDB();
    $search = trim($_GET['search'] ?? '');
    $category = $_GET['category'] ?? '';
    $where = "1=1";
    $binds = [];
    if ($search) { $where .= " AND (product_name LIKE ? OR platform LIKE ?)"; $s = "%$search%"; $binds = array_merge($binds, [$s,$s]); }
    if ($category) { $where .= " AND category = ?"; $binds[] = $category; }
    $stmt = $db->prepare("SELECT pk.*, (SELECT filename FROM key_images WHERE key_id = pk.id AND is_cover = 1 LIMIT 1) AS cover_image FROM product_keys pk WHERE $where ORDER BY created_at DESC");
    $stmt->execute($binds);
    $keys = $stmt->fetchAll();
    $title = 'Product Keys';
    ob_start(); require __DIR__ . '/../views/pages/keys/index.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('GET', '/keys/new', function($params) {
    requireLogin();
    $title = 'Add Product Key';
    $editing = false; $k = []; $keyCover = null;
    ob_start(); require __DIR__ . '/../views/pages/keys/form.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

function _saveKeyCover(int $keyId, int $userId): void {
    if (empty($_FILES['cover_image']['name'])) return;
    $saved = saveUploadedImage($_FILES['cover_image'], $userId);
    if (!$saved) return;
    $db = getDB();
    $stmt = $db->prepare("SELECT filename FROM key_images WHERE key_id = ? AND is_cover = 1");
    $stmt->execute([$keyId]);
    foreach ($stmt->fetchAll() as $old) deleteUploadedImage($old['filename']);
    $db->prepare("DELETE FROM key_images WHERE key_id = ?")->execute([$keyId]);
    $db->prepare("INSERT INTO key_images (key_id, filename, is_cover) VALUES (?, ?, 1)")
       ->execute([$keyId, $saved]);
}

route('POST', '/keys/new', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $fields = ['product_name','key_value','category','platform','manufacturer','licensed_to_name','licensed_to_email','max_activations','used_activations','purchase_date','expiry_date','notes'];
    $data = [];
    foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '') ?: null;
    $cols = implode(',', array_keys($data));
    $placeholders = implode(',', array_fill(0, count($data), '?'));
    $stmt = $db->prepare("INSERT INTO product_keys ($cols) VALUES ($placeholders)");
    $stmt->execute(array_values($data));
    $id = (int)$db->lastInsertId();
    _saveKeyCover($id, (int)$user['id']);
    logActivity($user['id'], 'create', 'product_key', $id, "Added key: {$data['product_name']}");
    flash('success', 'Product key added');
    redirect('/keys');
});

function _syncKeyActivationCount(int $keyId): void {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM key_activations WHERE key_id = ?");
    $stmt->execute([$keyId]);
    $count = (int)$stmt->fetchColumn();
    $db->prepare("UPDATE product_keys SET used_activations = ? WHERE id = ?")->execute([$count, $keyId]);
}

function getActivationsForKey(int $keyId): array {
    $stmt = getDB()->prepare("
        SELECT ka.*, a.name AS asset_name, a.asset_tag AS asset_tag
        FROM key_activations ka
        LEFT JOIN assets a ON a.id = ka.asset_id
        WHERE ka.key_id = ?
        ORDER BY ka.created_at DESC
    ");
    $stmt->execute([$keyId]);
    return $stmt->fetchAll();
}

route('GET', '/keys/:id/edit', function($params) {
    requireLogin();
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM product_keys WHERE id = ?");
    $stmt->execute([$params['id']]);
    $key = $stmt->fetch();
    if (!$key) redirect('/keys');
    $covStmt = $db->prepare("SELECT * FROM key_images WHERE key_id = ? AND is_cover = 1 LIMIT 1");
    $covStmt->execute([$params['id']]);
    $keyCover = $covStmt->fetch() ?: null;
    $activations = getActivationsForKey((int)$params['id']);
    $availableAssets = $db->query("SELECT id, name, asset_tag FROM assets ORDER BY name ASC")->fetchAll();
    $title = 'Edit Product Key';
    $editing = true;
    ob_start(); require __DIR__ . '/../views/pages/keys/form.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('POST', '/keys/:id/activations/add', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $keyId = (int)$params['id'];
    $own = $db->prepare("SELECT id, max_activations FROM product_keys WHERE id = ?");
    $own->execute([$keyId]);
    $key = $own->fetch();
    if (!$key) { flash('error', 'Key not found'); redirect('/keys'); }
    $assetId = (int)($_POST['asset_id'] ?? 0) ?: null;
    $assetLabel = trim($_POST['asset_label'] ?? '') ?: null;
    $notes = trim($_POST['notes'] ?? '') ?: null;
    if (!$assetId && !$assetLabel) { flash('error', 'Choose an asset or enter a device label'); redirect('/keys/'.$keyId.'/edit'); }
    if ($assetId) {
        $a = $db->prepare("SELECT id FROM assets WHERE id = ?");
        $a->execute([$assetId]);
        if (!$a->fetch()) { flash('error', 'Selected asset not found'); redirect('/keys/'.$keyId.'/edit'); }
    }
    // Optional: enforce max_activations
    if (!empty($key['max_activations'])) {
        $cur = (int)$db->query("SELECT COUNT(*) FROM key_activations WHERE key_id = $keyId")->fetchColumn();
        if ($cur >= (int)$key['max_activations']) {
            flash('error', 'This key has reached its maximum activations limit');
            redirect('/keys/'.$keyId.'/edit');
        }
    }
    $db->prepare("INSERT INTO key_activations (key_id, asset_id, asset_label, notes) VALUES (?, ?, ?, ?)")
        ->execute([$keyId, $assetId, $assetLabel, $notes]);
    _syncKeyActivationCount($keyId);
    logActivity($user['id'], 'create', 'key_activation', $db->lastInsertId(), "Logged activation on key #$keyId");
    flash('success', 'Activation logged');
    redirect('/keys/'.$keyId.'/edit');
});

route('POST', '/keys/:id/activations/:aid/delete', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $keyId = (int)$params['id'];
    $aid = (int)$params['aid'];
    $chk = $db->prepare("SELECT id FROM key_activations WHERE id = ? AND key_id = ?");
    $chk->execute([$aid, $keyId]);
    if (!$chk->fetch()) { flash('error', 'Activation not found'); redirect('/keys/'.$keyId.'/edit'); }
    $db->prepare("DELETE FROM key_activations WHERE id = ?")->execute([$aid]);
    _syncKeyActivationCount($keyId);
    logActivity($user['id'], 'delete', 'key_activation', $aid, "Removed activation from key #$keyId");
    flash('success', 'Activation removed');
    redirect('/keys/'.$keyId.'/edit');
});

route('POST', '/keys/:id/edit', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $fields = ['product_name','key_value','category','platform','manufacturer','licensed_to_name','licensed_to_email','max_activations','purchase_date','expiry_date','notes'];
    $data = [];
    foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '') ?: null;
    // Authorize first: load existing row before any side effects
    $existStmt = $db->prepare("SELECT id FROM product_keys WHERE id = ?");
    $existStmt->execute([$params['id']]);
    if (!$existStmt->fetch()) { flash('error', 'Not found'); redirect('/keys'); }
    $sets = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
    $stmt = $db->prepare("UPDATE product_keys SET $sets WHERE id = ?");
    $stmt->execute([...array_values($data), $params['id']]);
    _saveKeyCover((int)$params['id'], (int)$user['id']);
    logActivity($user['id'], 'update', 'product_key', $params['id'], "Updated key");
    flash('success', 'Product key updated');
    redirect('/keys');
});

route('POST', '/keys/:id/delete', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $own = $db->prepare("SELECT id FROM product_keys WHERE id = ?");
    $own->execute([$params['id']]);
    if (!$own->fetch()) { redirect('/keys'); }
    $imgs = $db->prepare("SELECT filename FROM key_images WHERE key_id = ?");
    $imgs->execute([$params['id']]);
    foreach ($imgs->fetchAll() as $img) deleteUploadedImage($img['filename']);
    $db->prepare("DELETE FROM product_keys WHERE id = ?")->execute([$params['id']]);
    logActivity($user['id'], 'delete', 'product_key', $params['id'], "Deleted product key");
    flash('success', 'Product key deleted');
    redirect('/keys');
});
