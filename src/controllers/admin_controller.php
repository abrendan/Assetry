<?php

route('GET', '/admin/users', function($params) {
    requireAdmin();
    $db = getDB();
    $users = $db->query("SELECT u.*, (SELECT COUNT(*) FROM vault_items WHERE user_id=u.id) as vault_count FROM users u ORDER BY created_at DESC")->fetchAll();
    $title = 'User Management';
    ob_start(); require __DIR__ . '/../views/pages/admin/users.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('POST', '/admin/users/:id/toggle', function($params) {
    requireAdmin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    if ((int)$params['id'] === $user['id']) {
        flash('error', 'Cannot deactivate your own account');
        redirect('/admin/users');
    }
    $stmt = $db->prepare("SELECT is_active FROM users WHERE id = ?");
    $stmt->execute([$params['id']]);
    $target = $stmt->fetch();
    if ($target) {
        $newState = $target['is_active'] ? 0 : 1;
        $db->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([$newState, $params['id']]);
        logActivity($user['id'], $newState ? 'activate_user' : 'deactivate_user', 'user', $params['id']);
        flash('success', 'User status updated');
    }
    redirect('/admin/users');
});

route('POST', '/admin/users/:id/role', function($params) {
    requireAdmin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    if ((int)$params['id'] === $user['id']) {
        flash('error', 'Cannot change your own role');
        redirect('/admin/users');
    }
    $role = in_array($_POST['role'] ?? '', ['admin','user']) ? $_POST['role'] : 'user';
    $db->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$role, $params['id']]);
    logActivity($user['id'], 'change_role', 'user', $params['id'], "Changed role to $role");
    flash('success', 'User role updated');
    redirect('/admin/users');
});

route('POST', '/admin/users/create', function($params) {
    requireAdmin();
    verifyCsrf();
    $user = currentUser();
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? '', ['admin','user']) ? $_POST['role'] : 'user';
    $result = register($username, $email, $password);
    if (isset($result['error'])) {
        flash('error', $result['error']);
    } else {
        $db = getDB();
        $db->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$role, $result['user_id']]);
        logActivity($user['id'], 'create_user', 'user', $result['user_id'], "Created user $username with role $role");
        flash('success', "User '$username' created successfully");
    }
    redirect('/admin/users');
});

route('POST', '/admin/users/:id/reset-password', function($params) {
    requireAdmin();
    verifyCsrf();
    $user = currentUser();
    $newPassword = $_POST['password'] ?? '';
    if (strlen($newPassword) < 8) {
        flash('error', 'Password must be at least 8 characters');
        redirect('/admin/users');
    }
    $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    $db = getDB();
    $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $params['id']]);
    logActivity($user['id'], 'reset_password', 'user', $params['id'], 'Password reset by admin');
    flash('success', 'Password reset successfully');
    redirect('/admin/users');
});

route('POST', '/admin/users/:id/delete', function($params) {
    requireAdmin();
    verifyCsrf();
    $user = currentUser();
    if ((int)$params['id'] === $user['id']) {
        flash('error', 'Cannot delete your own account');
        redirect('/admin/users');
    }
    $db = getDB();
    // Shared resources (assets, keys, licenses, network devices, asset types,
    // images, activations) have ON DELETE SET NULL on user_id, so they survive
    // user deletion. Only the user's own private vault is removed.
    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$params['id']]);
    logActivity($user['id'], 'delete_user', 'user', $params['id'], 'User deleted');
    flash('success', 'User deleted. Shared assets, keys and other items were preserved.');
    redirect('/admin/users');
});

route('GET', '/admin/logs', function($params) {
    requireAdmin();
    $db = getDB();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 50;
    $offset = ($page - 1) * $perPage;
    $total = $db->query("SELECT COUNT(*) FROM activity_log")->fetchColumn();
    $logs = $db->query("SELECT al.*, u.username FROM activity_log al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT $perPage OFFSET $offset")->fetchAll();
    $totalPages = ceil($total / $perPage);
    $title = 'Activity Log';
    ob_start(); require __DIR__ . '/../views/pages/admin/logs.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('GET', '/admin/sql', function($params) {
    requireAdmin();
    $db = getDB();
    $tables = [];
    $rows = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll();
    foreach ($rows as $r) {
        $name = $r['name'];
        try {
            $count = (int)$db->query("SELECT COUNT(*) FROM \"$name\"")->fetchColumn();
            $cols = $db->query("PRAGMA table_info(\"$name\")")->fetchAll();
            $colList = implode(', ', array_map(fn($c) => $c['name'] . ' (' . $c['type'] . ')', $cols));
        } catch (Throwable $e) {
            $count = 0;
            $colList = '';
        }
        $tables[] = ['name' => $name, 'count' => $count, 'columns' => $colList];
    }
    $title = 'SQL Console';
    ob_start(); require __DIR__ . '/../views/pages/admin/sql.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('POST', '/admin/sql', function($params) {
    requireAdmin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();

    $query = trim($_POST['query'] ?? '');
    $results = null;
    $columns = [];
    $error = null;
    $rowsAffected = null;
    $execTime = null;
    $queryType = '';

    if ($query === '') {
        flash('error', 'Query cannot be empty');
        redirect('/admin/sql');
    }

    $firstWord = strtoupper(preg_replace('/^\s*(\w+).*$/s', '$1', $query));
    $queryType = in_array($firstWord, ['SELECT','WITH','PRAGMA','EXPLAIN']) ? 'SELECT' : $firstWord;

    try {
        $start = microtime(true);
        $stmt = $db->query($query);
        $execTime = microtime(true) - $start;
        if ($queryType === 'SELECT') {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $columns = !empty($results) ? array_keys($results[0]) : [];
        } else {
            $rowsAffected = $stmt->rowCount();
            $results = [];
        }
        logActivity($user['id'], 'sql_query', null, null, substr($queryType . ': ' . preg_replace('/\s+/', ' ', $query), 0, 240));
    } catch (Throwable $e) {
        $error = $e->getMessage();
        logActivity($user['id'], 'sql_query_failed', null, null, substr($e->getMessage() . ' | ' . preg_replace('/\s+/', ' ', $query), 0, 240));
    }

    $tables = [];
    $rows = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll();
    foreach ($rows as $r) {
        $name = $r['name'];
        try {
            $count = (int)$db->query("SELECT COUNT(*) FROM \"$name\"")->fetchColumn();
            $cols = $db->query("PRAGMA table_info(\"$name\")")->fetchAll();
            $colList = implode(', ', array_map(fn($c) => $c['name'] . ' (' . $c['type'] . ')', $cols));
        } catch (Throwable $e) {
            $count = 0;
            $colList = '';
        }
        $tables[] = ['name' => $name, 'count' => $count, 'columns' => $colList];
    }

    $title = 'SQL Console';
    ob_start(); require __DIR__ . '/../views/pages/admin/sql.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});
