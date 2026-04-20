<?php
/**
 * Vault: per-user private items (notes, credentials, configs, images, etc.).
 * Uses vault_items + vault_images tables, both scoped to the owner via user_id.
 */

function _vaultOwn(int $id, int $userId): ?array {
    $stmt = getDB()->prepare("SELECT * FROM vault_items WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    return $stmt->fetch() ?: null;
}

function _vaultImages(int $id): array {
    $stmt = getDB()->prepare("SELECT * FROM vault_images WHERE vault_item_id = ? ORDER BY is_cover DESC, id ASC");
    $stmt->execute([$id]);
    return $stmt->fetchAll();
}

function _vaultEnsureCover(PDO $db, int $itemId): void {
    $cov = $db->prepare("SELECT id FROM vault_images WHERE vault_item_id = ? AND is_cover = 1 LIMIT 1");
    $cov->execute([$itemId]);
    if (!$cov->fetch()) {
        $first = $db->prepare("SELECT id FROM vault_images WHERE vault_item_id = ? ORDER BY id ASC LIMIT 1");
        $first->execute([$itemId]);
        $row = $first->fetch();
        if ($row) $db->prepare("UPDATE vault_images SET is_cover = 1 WHERE id = ?")->execute([$row['id']]);
    }
}

function _vaultHandleUploads(PDO $db, int $itemId, int $userId): void {
    if (!empty($_FILES['cover_image']['name'])) {
        $saved = saveUploadedImage($_FILES['cover_image'], $userId);
        if ($saved) {
            $db->prepare("UPDATE vault_images SET is_cover = 0 WHERE vault_item_id = ?")->execute([$itemId]);
            $db->prepare("INSERT INTO vault_images (vault_item_id, filename, is_cover) VALUES (?, ?, 1)")
                ->execute([$itemId, $saved]);
        }
    }
    if (!empty($_FILES['images']['name']) && is_array($_FILES['images']['name'])) {
        $count = count($_FILES['images']['name']);
        for ($i = 0; $i < $count; $i++) {
            if (($_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
            $file = [
                'name' => $_FILES['images']['name'][$i],
                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                'error' => $_FILES['images']['error'][$i],
                'size' => $_FILES['images']['size'][$i],
                'type' => $_FILES['images']['type'][$i],
            ];
            $saved = saveUploadedImage($file, $userId);
            if ($saved) {
                $db->prepare("INSERT INTO vault_images (vault_item_id, filename, is_cover) VALUES (?, ?, 0)")
                    ->execute([$itemId, $saved]);
            }
        }
    }
    _vaultEnsureCover($db, $itemId);
}

route('GET', '/vault', function($params) {
    requireLogin();
    $user = currentUser();
    $db = getDB();
    $search = trim($_GET['search'] ?? '');
    $type = $_GET['type'] ?? '';

    $where = "user_id = ?";
    $binds = [$user['id']];
    if ($search) {
        $where .= " AND (title LIKE ? OR content LIKE ? OR tags LIKE ?)";
        $s = "%$search%";
        $binds = array_merge($binds, [$s, $s, $s]);
    }
    if ($type) { $where .= " AND item_type = ?"; $binds[] = $type; }

    $stmt = $db->prepare("SELECT v.*, (SELECT filename FROM vault_images WHERE vault_item_id = v.id AND is_cover = 1 LIMIT 1) AS cover_image FROM vault_items v WHERE $where ORDER BY updated_at DESC, created_at DESC");
    $stmt->execute($binds);
    $items = $stmt->fetchAll();

    $title = 'Private Vault';
    ob_start(); require __DIR__ . '/../views/pages/vault/index.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('GET', '/vault/new', function($params) {
    requireLogin();
    $title = 'Add Vault Item';
    $editing = false; $item = []; $images = [];
    ob_start(); require __DIR__ . '/../views/pages/vault/form.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('POST', '/vault/new', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $title_v = trim($_POST['title'] ?? '');
    $type = trim($_POST['item_type'] ?? 'note') ?: 'note';
    $content_v = trim((string)($_POST['content'] ?? ''));
    $tags = trim($_POST['tags'] ?? '') ?: null;
    $hasUpload = !empty($_FILES['cover_image']['name']) || (!empty($_FILES['images']['name']) && array_filter((array)$_FILES['images']['name']));
    if ($title_v === '') { flash('error', 'Title is required'); redirect('/vault/new'); }
    if ($content_v === '' && !$hasUpload) { flash('error', 'Add some content or at least one image'); redirect('/vault/new'); }
    $stmt = $db->prepare("INSERT INTO vault_items (user_id, title, item_type, content, tags) VALUES (?,?,?,?,?)");
    $stmt->execute([$user['id'], $title_v, $type, $content_v !== '' ? $content_v : '', $tags]);
    $id = (int)$db->lastInsertId();
    _vaultHandleUploads($db, $id, (int)$user['id']);
    logActivity($user['id'], 'create', 'vault_item', $id, "Added vault item: $title_v");
    flash('success', 'Vault item added');
    redirect('/vault/' . $id);
});

route('GET', '/vault/:id', function($params) {
    requireLogin();
    $user = currentUser();
    $item = _vaultOwn((int)$params['id'], (int)$user['id']);
    if (!$item) { flash('error', 'Item not found'); redirect('/vault'); }
    $images = _vaultImages((int)$item['id']);
    $title = $item['title'];
    ob_start(); require __DIR__ . '/../views/pages/vault/show.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('GET', '/vault/:id/edit', function($params) {
    requireLogin();
    $user = currentUser();
    $item = _vaultOwn((int)$params['id'], (int)$user['id']);
    if (!$item) { flash('error', 'Item not found'); redirect('/vault'); }
    $images = _vaultImages((int)$item['id']);
    $title = 'Edit Vault Item';
    $editing = true;
    ob_start(); require __DIR__ . '/../views/pages/vault/form.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('POST', '/vault/:id/edit', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $item = _vaultOwn((int)$params['id'], (int)$user['id']);
    if (!$item) { flash('error', 'Item not found'); redirect('/vault'); }
    $title_v = trim($_POST['title'] ?? '');
    $type = trim($_POST['item_type'] ?? 'note') ?: 'note';
    $content_v = trim((string)($_POST['content'] ?? ''));
    $tags = trim($_POST['tags'] ?? '') ?: null;
    if ($title_v === '') { flash('error', 'Title is required'); redirect('/vault/' . $params['id'] . '/edit'); }
    $stmt = $db->prepare("UPDATE vault_items SET title=?, item_type=?, content=?, tags=?, updated_at=CURRENT_TIMESTAMP WHERE id=? AND user_id=?");
    $stmt->execute([$title_v, $type, $content_v, $tags, $params['id'], $user['id']]);
    _vaultHandleUploads($db, (int)$params['id'], (int)$user['id']);
    logActivity($user['id'], 'update', 'vault_item', $params['id'], "Updated vault item: $title_v");
    flash('success', 'Vault item updated');
    redirect('/vault/' . $params['id']);
});

route('POST', '/vault/:id/delete', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $item = _vaultOwn((int)$params['id'], (int)$user['id']);
    if (!$item) { flash('error', 'Item not found'); redirect('/vault'); }
    $imgs = $db->prepare("SELECT filename FROM vault_images WHERE vault_item_id = ?");
    $imgs->execute([$params['id']]);
    foreach ($imgs->fetchAll() as $r) deleteUploadedImage($r['filename']);
    $db->prepare("DELETE FROM vault_items WHERE id = ? AND user_id = ?")->execute([$params['id'], $user['id']]);
    logActivity($user['id'], 'delete', 'vault_item', $params['id'], "Deleted vault item");
    flash('success', 'Vault item deleted');
    redirect('/vault');
});

route('POST', '/vault/:id/images/:img/delete', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $item = _vaultOwn((int)$params['id'], (int)$user['id']);
    if (!$item) { flash('error', 'Item not found'); redirect('/vault'); }
    $stmt = $db->prepare("SELECT * FROM vault_images WHERE id = ? AND vault_item_id = ?");
    $stmt->execute([$params['img'], $params['id']]);
    if ($img = $stmt->fetch()) {
        deleteUploadedImage($img['filename']);
        $db->prepare("DELETE FROM vault_images WHERE id = ?")->execute([$params['img']]);
        _vaultEnsureCover($db, (int)$params['id']);
    }
    redirect('/vault/' . $params['id'] . '/edit');
});

route('POST', '/vault/:id/images/:img/cover', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $item = _vaultOwn((int)$params['id'], (int)$user['id']);
    if (!$item) { flash('error', 'Item not found'); redirect('/vault'); }
    $stmt = $db->prepare("SELECT id FROM vault_images WHERE id = ? AND vault_item_id = ?");
    $stmt->execute([$params['img'], $params['id']]);
    if ($stmt->fetch()) {
        $db->prepare("UPDATE vault_images SET is_cover = 0 WHERE vault_item_id = ?")->execute([$params['id']]);
        $db->prepare("UPDATE vault_images SET is_cover = 1 WHERE id = ?")->execute([$params['img']]);
    }
    redirect('/vault/' . $params['id'] . '/edit');
});
