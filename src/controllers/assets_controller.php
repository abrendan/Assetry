<?php

route('GET', '/assets', function($params) {
    requireLogin();
    $db = getDB();
    $search = trim($_GET['search'] ?? '');
    $category = $_GET['category'] ?? '';
    $status = $_GET['status'] ?? '';

    $where = "1=1";
    $binds = [];
    if ($search) { $where .= " AND (a.name LIKE ? OR a.model LIKE ? OR a.serial_number LIKE ? OR a.manufacturer LIKE ?)"; $s = "%$search%"; $binds = array_merge($binds, [$s,$s,$s,$s]); }
    if ($category) { $where .= " AND a.category = ?"; $binds[] = $category; }
    if ($status) { $where .= " AND a.status = ?"; $binds[] = $status; }

    $stmt = $db->prepare("SELECT a.*, (SELECT filename FROM asset_images WHERE asset_id = a.id AND is_cover = 1 LIMIT 1) AS cover_image FROM assets a WHERE $where ORDER BY a.created_at DESC");
    $stmt->execute($binds);
    $assets = $stmt->fetchAll();

    $title = 'Assets';
    ob_start(); require __DIR__ . '/../views/pages/assets/index.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('GET', '/assets/new', function($params) {
    requireLogin();
    $title = 'Add Asset';
    $editing = false; $a = []; $assetImages = [];
    ob_start(); require __DIR__ . '/../views/pages/assets/form.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

function _saveAssetImages(int $assetId, int $userId): void {
    $db = getDB();
    if (!empty($_FILES['cover_image']['name'])) {
        $saved = saveUploadedImage($_FILES['cover_image'], $userId);
        if ($saved) {
            $db->prepare("UPDATE asset_images SET is_cover = 0 WHERE asset_id = ?")->execute([$assetId]);
            $db->prepare("INSERT INTO asset_images (asset_id, filename, is_cover) VALUES (?, ?, 1)")
               ->execute([$assetId, $saved]);
        }
    }
    if (!empty($_FILES['images']['name'][0])) {
        $count = count($_FILES['images']['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
            $file = [
                'name' => $_FILES['images']['name'][$i],
                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                'error' => $_FILES['images']['error'][$i],
                'size' => $_FILES['images']['size'][$i],
            ];
            $saved = saveUploadedImage($file, $userId);
            if ($saved) {
                $db->prepare("INSERT INTO asset_images (asset_id, filename, is_cover) VALUES (?, ?, 0)")
                   ->execute([$assetId, $saved]);
            }
        }
    }
    $cov = $db->prepare("SELECT id FROM asset_images WHERE asset_id = ? AND is_cover = 1 LIMIT 1");
    $cov->execute([$assetId]);
    if (!$cov->fetch()) {
        $first = $db->prepare("SELECT id FROM asset_images WHERE asset_id = ? ORDER BY id ASC LIMIT 1");
        $first->execute([$assetId]);
        $row = $first->fetch();
        if ($row) {
            $db->prepare("UPDATE asset_images SET is_cover = 1 WHERE id = ?")->execute([$row['id']]);
        }
    }
}

route('POST', '/assets/new', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $fields = ['name','category','type','status','asset_tag','serial_number','model','manufacturer','quantity','purchase_date','purchase_price','warranty_expiry','location','assigned_to','notes'];
    $data = [];
    foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '') ?: null;
    if ($data['quantity'] === null) $data['quantity'] = 1;
    $cols = implode(',', array_keys($data));
    $placeholders = implode(',', array_fill(0, count($data), '?'));
    $stmt = $db->prepare("INSERT INTO assets ($cols) VALUES ($placeholders)");
    $stmt->execute(array_values($data));
    $id = (int)$db->lastInsertId();
    _saveAssetImages($id, $user['id']);
    saveCustomFieldsFromPost($id, $data['type'] ?? null);
    logActivity($user['id'], 'create', 'asset', $id, "Created asset: {$data['name']}");
    flash('success', 'Asset added successfully');
    redirect('/assets/' . $id);
});

route('GET', '/assets/:id', function($params) {
    requireLogin();
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM assets WHERE id = ?");
    $stmt->execute([$params['id']]);
    $asset = $stmt->fetch();
    if (!$asset) { http_response_code(404); require __DIR__ . '/../views/pages/404.php'; return; }
    $imgStmt = $db->prepare("SELECT * FROM asset_images WHERE asset_id = ? ORDER BY is_cover DESC, id ASC");
    $imgStmt->execute([$params['id']]);
    $assetImages = $imgStmt->fetchAll();
    $title = e($asset['name']);
    ob_start(); require __DIR__ . '/../views/pages/assets/show.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('GET', '/assets/:id/edit', function($params) {
    requireLogin();
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM assets WHERE id = ?");
    $stmt->execute([$params['id']]);
    $asset = $stmt->fetch();
    if (!$asset) redirect('/assets');
    $imgStmt = $db->prepare("SELECT * FROM asset_images WHERE asset_id = ? ORDER BY is_cover DESC, id ASC");
    $imgStmt->execute([$params['id']]);
    $assetImages = $imgStmt->fetchAll();
    $title = 'Edit Asset';
    $editing = true;
    ob_start(); require __DIR__ . '/../views/pages/assets/form.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('POST', '/assets/:id/edit', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $fields = ['name','category','type','status','asset_tag','serial_number','model','manufacturer','quantity','purchase_date','purchase_price','warranty_expiry','location','assigned_to','notes'];
    $data = [];
    foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '') ?: null;
    if ($data['quantity'] === null) $data['quantity'] = 1;
    $data['updated_at'] = date('Y-m-d H:i:s');
    $existStmt = $db->prepare("SELECT id FROM assets WHERE id = ?");
    $existStmt->execute([$params['id']]);
    if (!$existStmt->fetch()) { flash('error', 'Not found'); redirect('/assets'); }
    $sets = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
    $stmt = $db->prepare("UPDATE assets SET $sets WHERE id = ?");
    $stmt->execute([...array_values($data), $params['id']]);
    _saveAssetImages((int)$params['id'], (int)$user['id']);
    saveCustomFieldsFromPost((int)$params['id'], $data['type'] ?? null);
    logActivity($user['id'], 'update', 'asset', $params['id'], "Updated asset");
    flash('success', 'Asset updated');
    redirect('/assets/' . $params['id']);
});

route('POST', '/assets/:id/delete', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $stmt = $db->prepare("SELECT name FROM assets WHERE id = ?");
    $stmt->execute([$params['id']]);
    $asset = $stmt->fetch();
    if ($asset) {
        $imgs = $db->prepare("SELECT filename FROM asset_images WHERE asset_id = ?");
        $imgs->execute([$params['id']]);
        foreach ($imgs->fetchAll() as $img) deleteUploadedImage($img['filename']);
        $db->prepare("DELETE FROM assets WHERE id = ?")->execute([$params['id']]);
        logActivity($user['id'], 'delete', 'asset', $params['id'], "Deleted asset: {$asset['name']}");
        flash('success', 'Asset deleted');
    }
    redirect('/assets');
});

route('POST', '/assets/:id/images/:img/delete', function($params) {
    requireLogin();
    verifyCsrf();
    $db = getDB();
    $aStmt = $db->prepare("SELECT id FROM assets WHERE id = ?");
    $aStmt->execute([$params['id']]);
    if (!$aStmt->fetch()) { redirect('/assets'); }
    $stmt = $db->prepare("SELECT * FROM asset_images WHERE id = ? AND asset_id = ?");
    $stmt->execute([$params['img'], $params['id']]);
    $img = $stmt->fetch();
    if ($img) {
        deleteUploadedImage($img['filename']);
        $db->prepare("DELETE FROM asset_images WHERE id = ?")->execute([$params['img']]);
        if ($img['is_cover']) {
            $next = $db->prepare("SELECT id FROM asset_images WHERE asset_id = ? ORDER BY id ASC LIMIT 1");
            $next->execute([$params['id']]);
            $row = $next->fetch();
            if ($row) $db->prepare("UPDATE asset_images SET is_cover = 1 WHERE id = ?")->execute([$row['id']]);
        }
        flash('success', 'Image removed');
    }
    redirect('/assets/' . $params['id']);
});

route('POST', '/assets/:id/images/:img/cover', function($params) {
    requireLogin();
    verifyCsrf();
    $db = getDB();
    $aStmt = $db->prepare("SELECT id FROM assets WHERE id = ?");
    $aStmt->execute([$params['id']]);
    if (!$aStmt->fetch()) { redirect('/assets'); }
    $stmt = $db->prepare("SELECT id FROM asset_images WHERE id = ? AND asset_id = ?");
    $stmt->execute([$params['img'], $params['id']]);
    if ($stmt->fetch()) {
        $db->prepare("UPDATE asset_images SET is_cover = 0 WHERE asset_id = ?")->execute([$params['id']]);
        $db->prepare("UPDATE asset_images SET is_cover = 1 WHERE id = ?")->execute([$params['img']]);
        flash('success', 'Cover image updated');
    }
    redirect('/assets/' . $params['id']);
});
