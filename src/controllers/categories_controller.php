<?php

route('GET', '/categories', function($params) {
    requireLogin();
    $user = currentUser();
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM custom_categories WHERE user_id = ? ORDER BY entity_type, name");
    $stmt->execute([$user['id']]);
    $custom = $stmt->fetchAll();
    $grouped = ['asset' => [], 'key' => []];
    foreach ($custom as $c) {
        $grouped[$c['entity_type']][] = $c;
    }
    $title = 'Categories';
    ob_start(); require __DIR__ . '/../views/pages/categories/index.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('POST', '/categories/add', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $type = in_array($_POST['entity_type'] ?? '', ['asset','key']) ? $_POST['entity_type'] : null;
    $name = trim($_POST['name'] ?? '');
    if (!$type || $name === '' || strlen($name) > 50) {
        flash('error', 'Invalid category name or type');
        redirect('/categories');
    }
    $built = builtInCategories($type);
    if (in_array(strtolower($name), array_map('strtolower', $built), true)) {
        flash('error', "'$name' is already a built-in category");
        redirect('/categories');
    }
    try {
        $db = getDB();
        $db->prepare("INSERT INTO custom_categories (user_id, entity_type, name) VALUES (?, ?, ?)")
           ->execute([$user['id'], $type, $name]);
        logActivity($user['id'], 'create', 'category', null, "Added $type category: $name");
        flash('success', "Category '$name' added");
    } catch (PDOException $e) {
        flash('error', 'That category already exists');
    }
    redirect('/categories');
});

route('POST', '/categories/:id/delete', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $db->prepare("DELETE FROM custom_categories WHERE id = ? AND user_id = ?")
       ->execute([$params['id'], $user['id']]);
    logActivity($user['id'], 'delete', 'category', $params['id'], 'Deleted custom category');
    flash('success', 'Category removed');
    redirect('/categories');
});

route('GET', '/uploads/:user/:file', function($params) {
    requireLogin();
    $user = currentUser();
    // Only allow access to your own uploads (or admin)
    if ((int)$params['user'] !== (int)$user['id'] && $user['role'] !== 'admin') {
        http_response_code(403);
        exit('Forbidden');
    }
    $file = basename($params['file']);
    $full = __DIR__ . '/../../data/uploads/' . (int)$params['user'] . '/' . $file;
    if (!is_file($full)) {
        http_response_code(404);
        exit('Not found');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($full) ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($full));
    header('Cache-Control: private, max-age=3600');
    readfile($full);
    exit;
});
