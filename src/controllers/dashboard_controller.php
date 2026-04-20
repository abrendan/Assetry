<?php

route('GET', '/dashboard', function($params) {
    requireLogin();
    $user = currentUser();
    $db = getDB();

    $uid = (int)$user['id'];
    $totalAssets = $db->query("SELECT COUNT(*) FROM assets")->fetchColumn();
    $activeAssets = $db->query("SELECT COUNT(*) FROM assets WHERE status = 'active'")->fetchColumn();
    $totalKeys = $db->query("SELECT COUNT(*) FROM product_keys")->fetchColumn();
    $totalLicenses = $db->query("SELECT COUNT(*) FROM licenses")->fetchColumn();
    $networkDevices = $db->query("SELECT COUNT(*) FROM network_devices")->fetchColumn();
    $vaultItems = $db->query("SELECT COUNT(*) FROM vault_items WHERE user_id = $uid")->fetchColumn();

    $expiringLicenses = $db->query("
        SELECT * FROM licenses
        WHERE expiry_date IS NOT NULL
        AND expiry_date <= date('now', '+30 days')
        AND expiry_date >= date('now')
        ORDER BY expiry_date ASC LIMIT 5
    ")->fetchAll();

    $expiringWarranties = $db->query("
        SELECT * FROM assets
        WHERE warranty_expiry IS NOT NULL
        AND warranty_expiry <= date('now', '+60 days')
        AND warranty_expiry >= date('now')
        ORDER BY warranty_expiry ASC LIMIT 5
    ")->fetchAll();

    $categoryBreakdown = $db->query("
        SELECT category, COUNT(*) as count FROM assets
        GROUP BY category ORDER BY count DESC
    ")->fetchAll();

    $recentAssets = $db->query("
        SELECT * FROM assets
        ORDER BY created_at DESC LIMIT 8
    ")->fetchAll();

    // Recent activity
    $recentActivity = $db->query("
        SELECT al.*, u.username FROM activity_log al
        LEFT JOIN users u ON al.user_id = u.id
        WHERE al.user_id = {$user['id']}
        ORDER BY al.created_at DESC LIMIT 10
    ")->fetchAll();

    $title = 'Dashboard';
    ob_start();
    require __DIR__ . '/../views/pages/dashboard.php';
    $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});
