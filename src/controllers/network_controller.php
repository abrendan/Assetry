<?php

route('GET', '/network', function($params) {
    requireLogin();
    $user = currentUser();
    $db = getDB();
    $search = trim($_GET['search'] ?? '');
    $type = $_GET['type'] ?? '';
    $where = "1=1";
    $binds = [];
    if ($search) { $where .= " AND (hostname LIKE ? OR ip_address LIKE ? OR manufacturer LIKE ?)"; $s = "%$search%"; $binds = [$s,$s,$s]; }
    if ($type) { $where .= " AND device_type = ?"; $binds[] = $type; }
    $stmt = $db->prepare("SELECT * FROM network_devices WHERE $where ORDER BY hostname ASC");
    $stmt->execute($binds);
    $devices = $stmt->fetchAll();
    $title = 'Network Devices';
    ob_start(); require __DIR__ . '/../views/pages/network/index.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('GET', '/network/new', function($params) {
    requireLogin();
    $title = 'Add Network Device';
    $editing = false; $d = [];
    ob_start(); require __DIR__ . '/../views/pages/network/form.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('POST', '/network/new', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $fields = ['hostname','ip_address','mac_address','device_type','manufacturer','model','firmware_version','location','status','notes'];
    $data = [];
    foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '') ?: null;
    $cols = implode(',', array_keys($data));
    $placeholders = implode(',', array_fill(0, count($data), '?'));
    $stmt = $db->prepare("INSERT INTO network_devices ($cols) VALUES ($placeholders)");
    $stmt->execute(array_values($data));
    $id = $db->lastInsertId();
    logActivity($user['id'], 'create', 'network_device', $id, "Added device: {$data['hostname']}");
    flash('success', 'Network device added');
    redirect('/network');
});

route('GET', '/network/:id/edit', function($params) {
    requireLogin();
    $user = currentUser();
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM network_devices WHERE id = ?");
    $stmt->execute([$params['id']]);
    $device = $stmt->fetch();
    if (!$device) redirect('/network');
    $title = 'Edit Network Device';
    $editing = true;
    ob_start(); require __DIR__ . '/../views/pages/network/form.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('POST', '/network/:id/edit', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $fields = ['hostname','ip_address','mac_address','device_type','manufacturer','model','firmware_version','location','status','notes'];
    $data = [];
    foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '') ?: null;
    $data['updated_at'] = date('Y-m-d H:i:s');
    $sets = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
    $stmt = $db->prepare("UPDATE network_devices SET $sets WHERE id = ?");
    $stmt->execute([...array_values($data), $params['id']]);
    logActivity($user['id'], 'update', 'network_device', $params['id'], "Updated device");
    flash('success', 'Device updated');
    redirect('/network');
});

route('POST', '/network/:id/delete', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $db->prepare("DELETE FROM network_devices WHERE id = ?")->execute([$params['id']]);
    logActivity($user['id'], 'delete', 'network_device', $params['id'], "Deleted network device");
    flash('success', 'Device deleted');
    redirect('/network');
});
