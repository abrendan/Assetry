<?php

route('GET', '/profile', function($params) {
    requireLogin();
    $user = currentUser();
    $db = getDB();
    $stats = [
        'assets' => $db->query("SELECT COUNT(*) FROM assets")->fetchColumn(),
        'keys' => $db->query("SELECT COUNT(*) FROM product_keys")->fetchColumn(),
        'licenses' => $db->query("SELECT COUNT(*) FROM licenses")->fetchColumn(),
        'vault' => $db->query("SELECT COUNT(*) FROM vault_items WHERE user_id={$user['id']}")->fetchColumn(),
    ];
    $title = 'Profile';
    ob_start(); require __DIR__ . '/../views/pages/profile.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('POST', '/profile', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();

    $email = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $color = $_POST['avatar_color'] ?? $user['avatar_color'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Invalid email address');
        redirect('/profile');
    }

    // Check email uniqueness
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user['id']]);
    if ($stmt->fetch()) {
        flash('error', 'Email already in use');
        redirect('/profile');
    }

    if (!empty($newPassword)) {
        if (!password_verify($currentPassword, $user['password_hash'])) {
            flash('error', 'Current password is incorrect');
            redirect('/profile');
        }
        if (strlen($newPassword) < 8) {
            flash('error', 'New password must be at least 8 characters');
            redirect('/profile');
        }
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $db->prepare("UPDATE users SET email=?, password_hash=?, avatar_color=? WHERE id=?")->execute([$email, $hash, $color, $user['id']]);
    } else {
        $db->prepare("UPDATE users SET email=?, avatar_color=? WHERE id=?")->execute([$email, $color, $user['id']]);
    }

    logActivity($user['id'], 'update_profile', 'user', $user['id'], 'Profile updated');
    flash('success', 'Profile updated successfully');
    redirect('/profile');
});
