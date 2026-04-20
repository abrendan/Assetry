<?php

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function flash(string $key, string $message = null): ?string {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}

function formatBytes(int $bytes): string {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

function formatDate(string $date = null): string {
    if (!$date) return '—';
    return date('M j, Y', strtotime($date));
}

function formatDateTime(string $dt = null): string {
    if (!$dt) return '—';
    return date('M j, Y g:i A', strtotime($dt));
}

function daysUntil(string $date): int {
    $diff = (new DateTime($date))->diff(new DateTime());
    return $diff->invert ? $diff->days : -$diff->days;
}

function statusBadge(string $status): string {
    $classes = [
        'active'   => 'badge-success',
        'inactive' => 'badge-neutral',
        'retired'  => 'badge-neutral',
        'expired'  => 'badge-danger',
        'expiring' => 'badge-warning',
        'online'   => 'badge-success',
        'offline'  => 'badge-danger',
        'unknown'  => 'badge-neutral',
    ];
    $class = $classes[strtolower($status)] ?? 'badge-neutral';
    return '<span class="badge ' . $class . '">' . e(ucfirst($status)) . '</span>';
}

function categoryIcon(string $category): string {
    $icons = [
        'hardware'  => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
        'software'  => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
        'network'   => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="3"/><line x1="12" y1="8" x2="12" y2="11"/><circle cx="5" cy="19" r="3"/><circle cx="19" cy="19" r="3"/><line x1="8.5" y1="17.5" x2="10" y2="13"/><line x1="15.5" y1="17.5" x2="14" y2="13"/></svg>',
        'peripheral'=> '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M12 12h.01"/><path d="M17 12h.01"/><path d="M7 12h.01"/></svg>',
        'mobile'    => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>',
        'storage'   => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>',
        'other'     => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
    ];
    return $icons[strtolower($category)] ?? $icons['other'];
}

function getAssetCategories(): array {
    return ['hardware', 'software', 'network', 'peripheral', 'accessory', 'mobile', 'storage', 'other'];
}

function getKeyCategories(): array {
    return ['software', 'os', 'game', 'other'];
}

function builtInCategories(string $entityType): array {
    return match($entityType) {
        'asset' => getAssetCategories(),
        'key' => getKeyCategories(),
        default => [],
    };
}

function getCategoriesFor(string $entityType): array {
    $built = builtInCategories($entityType);
    $user = currentUser();
    if (!$user) return $built;
    $stmt = getDB()->prepare("SELECT name FROM custom_categories WHERE user_id = ? AND entity_type = ? ORDER BY name");
    $stmt->execute([$user['id'], $entityType]);
    $custom = array_column($stmt->fetchAll(), 'name');
    return array_values(array_unique(array_merge($built, $custom)));
}

function uploadDir(int $userId): string {
    $dir = __DIR__ . '/../data/uploads/' . $userId;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    return $dir;
}

function isAllowedImage(string $mime): bool {
    return in_array($mime, ['image/jpeg','image/png','image/gif','image/webp'], true);
}

function saveUploadedImage(array $file, int $userId): ?string {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] <= 0 || $file['size'] > 8 * 1024 * 1024) return null; // 8MB max
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isAllowedImage($mime)) return null;
    $extMap = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp'];
    $ext = $extMap[$mime];
    $name = bin2hex(random_bytes(12)) . '.' . $ext;
    $dest = uploadDir($userId) . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return null;
    return $userId . '/' . $name;
}

function deleteUploadedImage(string $relativePath): void {
    $full = __DIR__ . '/../data/uploads/' . $relativePath;
    if (is_file($full) && strpos(realpath($full), realpath(__DIR__ . '/../data/uploads/')) === 0) {
        @unlink($full);
    }
}

function getAssetStatuses(): array {
    return ['active', 'inactive', 'retired', 'in_repair', 'disposed'];
}

function getNetworkDeviceTypes(): array {
    return ['router', 'switch', 'firewall', 'access_point', 'server', 'nas', 'ups', 'other'];
}

function getLicenseTypes(): array {
    return ['perpetual', 'subscription', 'oem', 'open_source', 'trial', 'volume', 'other'];
}

function getCustomFieldTypes(): array {
    return ['text', 'number', 'date', 'boolean', 'textarea'];
}

function slugifyFieldKey(string $label): string {
    $s = strtolower(trim($label));
    $s = preg_replace('/[^a-z0-9]+/', '_', $s);
    return trim($s, '_') ?: 'field';
}

function getAllAssetTypes(): array {
    return getDB()->query("SELECT * FROM asset_types ORDER BY name")->fetchAll();
}

// Back-compat: callers may still pass a userId; it's ignored.
function getAssetTypesForUser(int $userId = 0): array {
    return getAllAssetTypes();
}

function findAssetTypeByName(?string $name, $_legacy = null): ?array {
    // Accept either (name) or legacy (userId, name) call signatures.
    if (is_int($name) && is_string($_legacy)) {
        $name = $_legacy;
    }
    if (!$name) return null;
    $stmt = getDB()->prepare("SELECT * FROM asset_types WHERE name = ? COLLATE NOCASE LIMIT 1");
    $stmt->execute([trim($name)]);
    return $stmt->fetch() ?: null;
}

function ensureAssetType(string $name, $_legacy = null): array {
    if (is_int($name) && is_string($_legacy)) { $name = $_legacy; }
    $name = trim($name);
    $existing = findAssetTypeByName($name);
    if ($existing) return $existing;
    getDB()->prepare("INSERT INTO asset_types (name) VALUES (?)")->execute([$name]);
    return findAssetTypeByName($name);
}

function getFieldsForType(int $typeId): array {
    $stmt = getDB()->prepare("SELECT * FROM asset_type_fields WHERE type_id = ? ORDER BY sort_order, id");
    $stmt->execute([$typeId]);
    return $stmt->fetchAll();
}

function ensureTypeField(int $typeId, string $label, string $type = 'text'): array {
    $key = slugifyFieldKey($label);
    $stmt = getDB()->prepare("SELECT * FROM asset_type_fields WHERE type_id = ? AND field_key = ?");
    $stmt->execute([$typeId, $key]);
    $row = $stmt->fetch();
    if ($row) return $row;
    if (!in_array($type, getCustomFieldTypes(), true)) $type = 'text';
    getDB()->prepare("INSERT INTO asset_type_fields (type_id, field_key, field_label, field_type) VALUES (?, ?, ?, ?)")
        ->execute([$typeId, $key, $label, $type]);
    $stmt->execute([$typeId, $key]);
    return $stmt->fetch();
}

function getCustomValuesForAsset(int $assetId): array {
    $stmt = getDB()->prepare("SELECT field_id, value FROM asset_field_values WHERE asset_id = ?");
    $stmt->execute([$assetId]);
    $out = [];
    foreach ($stmt->fetchAll() as $r) $out[(int)$r['field_id']] = $r['value'];
    return $out;
}

function saveCustomFieldValue(int $assetId, int $fieldId, ?string $value): void {
    $db = getDB();
    if ($value === null || $value === '') {
        $db->prepare("DELETE FROM asset_field_values WHERE asset_id = ? AND field_id = ?")->execute([$assetId, $fieldId]);
    } else {
        $db->prepare("INSERT INTO asset_field_values (asset_id, field_id, value) VALUES (?, ?, ?)
                      ON CONFLICT(asset_id, field_id) DO UPDATE SET value = excluded.value")
           ->execute([$assetId, $fieldId, $value]);
    }
}

function saveCustomFieldsFromPost(int $assetId, $typeName, $_legacy = null): void {
    // Accept either (assetId, typeName) or legacy (assetId, userId, typeName).
    if (is_int($typeName) && (is_string($_legacy) || $_legacy === null)) {
        $typeName = $_legacy;
    }
    if (!$typeName) return;
    $type = findAssetTypeByName($typeName);
    if (!$type) return;
    $fields = getFieldsForType((int)$type['id']);
    $posted = $_POST['custom'] ?? [];
    foreach ($fields as $f) {
        $val = $posted[$f['field_key']] ?? null;
        if ($f['field_type'] === 'boolean') {
            $val = !empty($val) ? '1' : '0';
        }
        if (is_string($val)) $val = trim($val);
        saveCustomFieldValue($assetId, (int)$f['id'], $val !== '' ? $val : null);
    }
}

function initials(string $name): string {
    $words = explode(' ', trim($name));
    if (count($words) >= 2) {
        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    }
    return strtoupper(substr($name, 0, 2));
}
