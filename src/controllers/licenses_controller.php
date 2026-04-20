<?php

route('GET', '/licenses', function($params) {
    requireLogin();
    $user = currentUser();
    $db = getDB();
    $search = trim($_GET['search'] ?? '');
    $where = "1=1";
    $binds = [];
    if ($search) { $where .= " AND (software_name LIKE ? OR vendor LIKE ?)"; $s = "%$search%"; $binds = [$s,$s]; }
    $stmt = $db->prepare("SELECT * FROM licenses WHERE $where ORDER BY expiry_date ASC, software_name ASC");
    $stmt->execute($binds);
    $licenses = $stmt->fetchAll();
    $title = 'Licenses';
    ob_start(); require __DIR__ . '/../views/pages/licenses/index.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('GET', '/licenses/new', function($params) {
    requireLogin();
    $title = 'Add License';
    $editing = false; $l = [];
    ob_start(); require __DIR__ . '/../views/pages/licenses/form.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('POST', '/licenses/new', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $fields = ['software_name','vendor','license_type','seats','seats_used','start_date','expiry_date','cost','renewal_cost','notes'];
    $data = [];
    foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '') ?: null;
    $cols = implode(',', array_keys($data));
    $placeholders = implode(',', array_fill(0, count($data), '?'));
    $stmt = $db->prepare("INSERT INTO licenses ($cols) VALUES ($placeholders)");
    $stmt->execute(array_values($data));
    $id = $db->lastInsertId();
    logActivity($user['id'], 'create', 'license', $id, "Added license: {$data['software_name']}");
    flash('success', 'License added');
    redirect('/licenses');
});

route('GET', '/licenses/:id/edit', function($params) {
    requireLogin();
    $user = currentUser();
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM licenses WHERE id = ?");
    $stmt->execute([$params['id']]);
    $license = $stmt->fetch();
    if (!$license) redirect('/licenses');
    $title = 'Edit License';
    $editing = true;
    ob_start(); require __DIR__ . '/../views/pages/licenses/form.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('POST', '/licenses/:id/edit', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $fields = ['software_name','vendor','license_type','seats','seats_used','start_date','expiry_date','cost','renewal_cost','notes'];
    $data = [];
    foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '') ?: null;
    $data['updated_at'] = date('Y-m-d H:i:s');
    $sets = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
    $stmt = $db->prepare("UPDATE licenses SET $sets WHERE id = ?");
    $stmt->execute([...array_values($data), $params['id']]);
    logActivity($user['id'], 'update', 'license', $params['id'], "Updated license");
    flash('success', 'License updated');
    redirect('/licenses');
});

route('POST', '/licenses/:id/delete', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $db->prepare("DELETE FROM licenses WHERE id = ?")->execute([$params['id']]);
    logActivity($user['id'], 'delete', 'license', $params['id'], "Deleted license");
    flash('success', 'License deleted');
    redirect('/licenses');
});
