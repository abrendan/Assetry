<?php
require_once __DIR__ . '/db.php';

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn() || currentUser() === null) {
        session_destroy();
        header('Location: /login');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (currentUser()['role'] !== 'admin') {
        header('Location: /dashboard');
        exit;
    }
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user === null) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
    return $user ?: null;
}

function login(string $username, string $password): bool {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?")->execute([$user['id']]);
        logActivity($user['id'], 'login', 'user', $user['id'], 'User logged in');
        return true;
    }
    return false;
}

function logout(): void {
    if (isLoggedIn()) {
        logActivity($_SESSION['user_id'], 'logout', 'user', $_SESSION['user_id'], 'User logged out');
    }
    session_destroy();
    header('Location: /login');
    exit;
}

function register(string $username, string $email, string $password): array {
    $db = getDB();
    if (strlen($username) < 3) return ['error' => 'Username must be at least 3 characters'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['error' => 'Invalid email address'];
    if (strlen($password) < 8) return ['error' => 'Password must be at least 8 characters'];

    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) return ['error' => 'Username or email already exists'];

    $colors = ['#6366f1','#8b5cf6','#ec4899','#06b6d4','#10b981','#f59e0b','#ef4444','#3b82f6'];
    $color = $colors[array_rand($colors)];

    // First user is admin
    $countStmt = $db->query("SELECT COUNT(*) as c FROM users");
    $count = $countStmt->fetch()['c'];
    $role = $count === 0 ? 'admin' : 'user';

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, role, avatar_color) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $email, $hash, $role, $color]);
    $userId = (int)$db->lastInsertId();

    seedDefaultAssetTypes($userId);
    logActivity($userId, 'register', 'user', $userId, 'Account created');
    return ['success' => true, 'user_id' => $userId, 'role' => $role];
}

function csrf(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $posted = $_POST['csrf_token'] ?? '';
    $session = $_SESSION['csrf_token'] ?? '';
    if ($posted === '' || $session === '' || !hash_equals($session, $posted)) {
        flash('error', 'Your session expired. Please try again.');
        $back = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $back);
        exit;
    }
}
