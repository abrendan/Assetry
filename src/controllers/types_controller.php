<?php

route('GET', '/types', function($params) {
    requireLogin();
    $user = currentUser();
    $types = getAssetTypesForUser((int)$user['id']);
    foreach ($types as &$t) $t['fields'] = getFieldsForType((int)$t['id']);
    unset($t);
    $title = 'Asset Types';
    ob_start(); require __DIR__ . '/../views/pages/types/index.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('GET', '/types/:id/fields.json', function($params) {
    requireLogin();
    $user = currentUser();
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM asset_types WHERE id = ?");
    $stmt->execute([(int)$params['id']]);
    $type = $stmt->fetch();
    if (!$type) { http_response_code(404); header('Content-Type: application/json'); echo json_encode(['error' => 'Not found']); return; }
    $fields = getFieldsForType((int)$type['id']);
    $values = [];
    if (!empty($_GET['asset_id'])) {
        $aStmt = $db->prepare("SELECT id FROM assets WHERE id = ?");
        $aStmt->execute([(int)$_GET['asset_id']]);
        if ($aStmt->fetch()) $values = getCustomValuesForAsset((int)$_GET['asset_id']);
    }
    header('Content-Type: application/json');
    echo json_encode([
        'type' => ['id' => (int)$type['id'], 'name' => $type['name']],
        'fields' => array_map(fn($f) => [
            'id' => (int)$f['id'],
            'key' => $f['field_key'],
            'label' => $f['field_label'],
            'type' => $f['field_type'],
            'value' => $values[(int)$f['id']] ?? '',
        ], $fields),
    ]);
});

route('POST', '/types/add', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $name = trim($_POST['name'] ?? '');
    if ($name === '' || strlen($name) > 60) { flash('error', 'Invalid type name'); redirect('/types'); }
    try {
        ensureAssetType($name);
        logActivity($user['id'], 'create', 'asset_type', null, "Added type: $name");
        flash('success', "Type '$name' added");
    } catch (PDOException $e) {
        flash('error', 'That type already exists');
    }
    redirect('/types');
});

route('POST', '/types/:id/delete', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    getDB()->prepare("DELETE FROM asset_types WHERE id = ?")
        ->execute([$params['id']]);
    flash('success', 'Type removed (custom field values for this type are now orphaned)');
    redirect('/types');
});

route('POST', '/types/:id/fields/add', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM asset_types WHERE id = ?");
    $stmt->execute([$params['id']]);
    $type = $stmt->fetch();
    if (!$type) { flash('error', 'Unknown type'); redirect('/types'); }
    $label = trim($_POST['field_label'] ?? '');
    $ftype = $_POST['field_type'] ?? 'text';
    if ($label === '' || strlen($label) > 80) { flash('error', 'Invalid field label'); redirect('/types'); }
    try {
        ensureTypeField((int)$type['id'], $label, $ftype);
        flash('success', "Field '$label' added to type {$type['name']}");
    } catch (PDOException $e) {
        flash('error', 'A field with that name already exists for this type');
    }
    redirect('/types');
});

route('POST', '/types/:id/fields/:fid/delete', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();
    $stmt = $db->prepare("SELECT atf.id FROM asset_type_fields atf
        JOIN asset_types at ON at.id = atf.type_id
        WHERE atf.id = ? AND at.id = ?");
    $stmt->execute([$params['fid'], $params['id']]);
    if ($stmt->fetch()) {
        $db->prepare("DELETE FROM asset_type_fields WHERE id = ?")->execute([$params['fid']]);
        flash('success', 'Field removed');
    }
    redirect('/types');
});
