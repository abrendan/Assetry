<?php

function _csvOpen(string $filename): void {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store');
}

function _csvWrite(array $rows): void {
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
    foreach ($rows as $row) fputcsv($out, $row);
    fclose($out);
}

function _normalizeHeader(string $h): string {
    $h = preg_replace('/^\xEF\xBB\xBF/', '', $h);
    return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '_', $h), '_'));
}

function _readCsv(string $path): array {
    $rows = [];
    if (($fh = @fopen($path, 'r')) === false) return ['headers' => [], 'rows' => []];
    $headers = fgetcsv($fh);
    if (!$headers) { fclose($fh); return ['headers' => [], 'rows' => []]; }
    $headers = array_map('_normalizeHeader', $headers);
    $seen = []; $dedup = [];
    foreach ($headers as $h) {
        $key = $h ?: 'col';
        $i = 2;
        while (isset($seen[$key])) { $key = ($h ?: 'col') . '_' . $i; $i++; }
        $seen[$key] = true;
        $dedup[] = $key;
    }
    $headers = $dedup;
    while (($r = fgetcsv($fh)) !== false) {
        if (count(array_filter($r, fn($v) => trim((string)$v) !== '')) === 0) continue;
        $r = array_pad($r, count($headers), null);
        $rows[] = array_combine($headers, array_slice($r, 0, count($headers)));
    }
    fclose($fh);
    return ['headers' => $headers, 'rows' => $rows];
}

function _detectImportType(array $headers): string {
    // Snipe-IT custom asset report: many columns, has asset_tag + category + custom field columns past col 57
    if (in_array('asset_tag', $headers) && in_array('category', $headers) && in_array('asset_name', $headers) && count($headers) > 55) {
        return 'custom_assets';
    }
    if (in_array('product_key', $headers) || in_array('licensed_to_email', $headers)) return 'keys';
    if (in_array('key_value', $headers) && in_array('product_name', $headers)) return 'keys';
    if (in_array('accessory_category', $headers) || in_array('checked_out', $headers)) return 'accessories';
    if (in_array('asset_tag', $headers) || in_array('asset_name', $headers)) return 'assets';
    if (in_array('name', $headers) && in_array('category', $headers)) return 'assets';
    return 'unknown';
}

// Standard Snipe-IT custom-asset-report columns. Anything else is treated as a custom field.
function _standardSnipeColumns(): array {
    return [
        'id','company','asset_name','asset_tag','model','model_no','category','manufacturer',
        'serial','purchased','cost','eol','order_number','supplier',
        'location','address','city','state','country','zip','default_location','checked_out',
        'type','username','employee_no','manager','department','title','phone',
        'user_address','user_city','user_state','user_country','user_zip',
        'status','warranty','warranty_expires','current_value','diff','fully_depreciated',
        'checkout_date','last_checkin_date','expected_checkin_date',
        'created_at','updated_at','deleted','last_audit','next_audit_date',
        'notes','url','mac_address',
    ];
}

function _snipeStatusToInternal(?string $s): string {
    if (!$s) return 'active';
    $s = strtolower($s);
    if (str_contains($s, 'archived')) return 'retired';
    if (str_contains($s, 'broken') || str_contains($s, 'lost') || str_contains($s, 'stolen')) return 'retired';
    if (str_contains($s, 'maintenance') || str_contains($s, 'repair')) return 'maintenance';
    return 'active';
}

function _singularize(string $s): string {
    $s = trim($s);
    if ($s === '') return $s;
    if (preg_match('/(ies)$/i', $s)) return preg_replace('/ies$/i', 'y', $s);
    if (preg_match('/(ses|xes|zes|ches|shes)$/i', $s)) return preg_replace('/es$/i', '', $s);
    if (preg_match('/s$/i', $s) && !preg_match('/(ss|us|is)$/i', $s)) return preg_replace('/s$/i', '', $s);
    return $s;
}

function _pick(array $row, array $candidates): ?string {
    foreach ($candidates as $c) {
        if (isset($row[$c]) && trim((string)$row[$c]) !== '') return trim((string)$row[$c]);
    }
    return null;
}

function _normDate(?string $s): ?string {
    if (!$s) return null;
    $s = trim($s);
    if ($s === '') return null;
    $ts = strtotime($s);
    return $ts ? date('Y-m-d', $ts) : null;
}

route('GET', '/data', function($params) {
    requireLogin();
    $title = 'Import / Export';
    ob_start(); require __DIR__ . '/../views/pages/data/index.php'; $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/main.php';
});

route('GET', '/data/export/assets', function($params) {
    requireLogin();
    $user = currentUser();
    $stmt = getDB()->query("SELECT * FROM assets ORDER BY id");
    _csvOpen('assetry-assets-' . date('Y-m-d') . '.csv');
    $cols = ['id','name','category','type','status','asset_tag','serial_number','model','manufacturer','quantity','purchase_date','purchase_price','warranty_expiry','location','assigned_to','notes','created_at'];
    $rows = [$cols];
    foreach ($stmt->fetchAll() as $r) {
        $row = [];
        foreach ($cols as $c) $row[] = $r[$c] ?? '';
        $rows[] = $row;
    }
    _csvWrite($rows);
});

route('GET', '/data/export/keys', function($params) {
    requireLogin();
    $user = currentUser();
    $stmt = getDB()->query("SELECT * FROM product_keys ORDER BY id");
    _csvOpen('assetry-keys-' . date('Y-m-d') . '.csv');
    $cols = ['id','product_name','key_value','category','platform','manufacturer','licensed_to_name','licensed_to_email','max_activations','used_activations','purchase_date','expiry_date','notes','created_at'];
    $rows = [$cols];
    foreach ($stmt->fetchAll() as $r) {
        $row = [];
        foreach ($cols as $c) $row[] = $r[$c] ?? '';
        $rows[] = $row;
    }
    _csvWrite($rows);
});

route('GET', '/data/export/licenses', function($params) {
    requireLogin();
    $user = currentUser();
    $stmt = getDB()->query("SELECT * FROM licenses ORDER BY id");
    _csvOpen('assetry-licenses-' . date('Y-m-d') . '.csv');
    $cols = ['id','software_name','vendor','license_type','seats','seats_used','start_date','expiry_date','cost','renewal_cost','notes','created_at'];
    $rows = [$cols];
    foreach ($stmt->fetchAll() as $r) {
        $row = [];
        foreach ($cols as $c) $row[] = $r[$c] ?? '';
        $rows[] = $row;
    }
    _csvWrite($rows);
});

route('GET', '/data/export/network', function($params) {
    requireLogin();
    $user = currentUser();
    $stmt = getDB()->query("SELECT * FROM network_devices ORDER BY id");
    _csvOpen('assetry-network-' . date('Y-m-d') . '.csv');
    $cols = ['id','hostname','ip_address','mac_address','device_type','manufacturer','model','firmware_version','location','status','notes','created_at'];
    $rows = [$cols];
    foreach ($stmt->fetchAll() as $r) {
        $row = [];
        foreach ($cols as $c) $row[] = $r[$c] ?? '';
        $rows[] = $row;
    }
    _csvWrite($rows);
});

route('POST', '/data/import', function($params) {
    requireLogin();
    verifyCsrf();
    $user = currentUser();
    $db = getDB();

    if (empty($_FILES['csv_file']['tmp_name']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        flash('error', 'Please select a CSV file to upload.');
        redirect('/data');
    }
    if (!is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
        flash('error', 'Invalid upload.');
        redirect('/data');
    }
    if ($_FILES['csv_file']['size'] > 10 * 1024 * 1024) {
        flash('error', 'File too large (max 10MB).');
        redirect('/data');
    }
    $origName = $_FILES['csv_file']['name'] ?? '';
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if ($ext !== '' && $ext !== 'csv' && $ext !== 'txt') {
        flash('error', 'Only .csv files are accepted.');
        redirect('/data');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES['csv_file']['tmp_name']);
    $allowedMime = ['text/csv','text/plain','application/csv','application/vnd.ms-excel','application/octet-stream'];
    if ($mime && !in_array($mime, $allowedMime, true)) {
        flash('error', 'File does not look like a CSV (' . e($mime) . ').');
        redirect('/data');
    }

    $tmp = $_FILES['csv_file']['tmp_name'];
    $parsed = _readCsv($tmp);
    if (empty($parsed['headers']) || empty($parsed['rows'])) {
        flash('error', 'No data rows found in the CSV.');
        redirect('/data');
    }

    $forced = $_POST['type'] ?? 'auto';
    $type = $forced !== 'auto' ? $forced : _detectImportType($parsed['headers']);
    $defaultCategory = trim($_POST['default_category'] ?? '') ?: 'hardware';

    if ($type === 'custom_assets') {
        [$imported, $skipped, $errors, $typeCount, $fieldCount] = _importCustomAssets($db, (int)$user['id'], $parsed, $tmp);
        logActivity($user['id'], 'import', 'custom_assets', null, "Imported $imported assets across $typeCount types ($fieldCount custom fields)");
        $msg = "Imported $imported assets, created/updated $typeCount types with $fieldCount custom fields total" . ($skipped > 0 ? " ($skipped row(s) skipped)" : '') . ".";
        if (!empty($errors)) $msg .= ' First errors: ' . implode(' | ', $errors);
        flash($imported > 0 ? 'success' : 'error', $msg);
        redirect('/data');
    }

    if ($type === 'keys') {
        [$imported, $skipped, $errors] = _importKeys($db, $user, $parsed);
    } elseif ($type === 'assets' || $type === 'accessories') {
        [$imported, $skipped, $errors] = _importAssets($db, $user, $type, $parsed, $defaultCategory);
    } else {
        flash('error', 'Could not detect file type. Please pick one explicitly.');
        redirect('/data');
    }

    logActivity($user['id'], 'import', $type, null, "Imported $imported $type rows ($skipped skipped)");
    $msg = "Imported $imported $type" . ($skipped > 0 ? " ($skipped row(s) skipped)" : '') . ".";
    if (!empty($errors)) $msg .= ' First errors: ' . implode(' | ', $errors);
    flash($imported > 0 ? 'success' : 'error', $msg);
    redirect('/data');
});

function _importKeys(PDO $db, array $user, array $parsed): array {
    $imported = 0; $skipped = 0; $errors = [];
    $sql = "INSERT INTO product_keys (product_name, key_value, category, platform, manufacturer, licensed_to_name, licensed_to_email, max_activations, used_activations, purchase_date, expiry_date, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $db->prepare($sql);
    foreach ($parsed['rows'] as $i => $row) {
        $name = _pick($row, ['name','product_name']);
        $key = _pick($row, ['product_key','key_value','key']);
        if (!$name || !$key) { $skipped++; continue; }
        $total = (int)(_pick($row, ['total','max_activations']) ?? 1);
        $avail = _pick($row, ['avail','available']);
        $used = $avail !== null ? max(0, $total - (int)$avail) : (int)(_pick($row, ['used_activations','used']) ?? 0);
        try {
            $stmt->execute([
                $name, $key,
                _pick($row, ['category']) ?: 'software',
                _pick($row, ['platform']),
                _pick($row, ['manufacturer','vendor']),
                _pick($row, ['licensed_to_name','licensed_to']),
                _pick($row, ['licensed_to_email','email']),
                $total ?: null, $used,
                _normDate(_pick($row, ['purchase_date'])),
                _normDate(_pick($row, ['expiration_date','expiry_date'])),
                _pick($row, ['notes']),
            ]);
            $imported++;
        } catch (Throwable $ex) {
            $skipped++;
            if (count($errors) < 5) $errors[] = "Row " . ($i+2) . ": " . $ex->getMessage();
        }
    }
    return [$imported, $skipped, $errors];
}

function _importAssets(PDO $db, array $user, string $type, array $parsed, string $defaultCategory): array {
    $imported = 0; $skipped = 0; $errors = [];
    $cat = $type === 'accessories' ? 'accessory' : $defaultCategory;
    $sql = "INSERT INTO assets (name, category, type, status, asset_tag, serial_number, model, manufacturer, quantity, purchase_date, purchase_price, warranty_expiry, location, assigned_to, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $db->prepare($sql);
    foreach ($parsed['rows'] as $i => $row) {
        $name = _pick($row, ['name','asset_name']);
        if (!$name) { $skipped++; continue; }
        $rowCat = $type === 'accessories' ? 'accessory' : (_pick($row, ['category','accessory_category']) ?: $cat);
        $type_field = _pick($row, ['type']) ?: ($type === 'accessories' ? (_pick($row, ['accessory_category']) ?: 'Accessory') : 'Device');
        try {
            $stmt->execute([
                $name, $rowCat, $type_field,
                _pick($row, ['status']) ?: 'active',
                _pick($row, ['asset_tag','tag']),
                _pick($row, ['serial_number','serial']),
                _pick($row, ['model','model_no','model_number']),
                _pick($row, ['manufacturer','make']),
                (int)(_pick($row, ['quantity','total','qty']) ?? 1) ?: 1,
                _normDate(_pick($row, ['purchase_date'])),
                (float)(_pick($row, ['purchase_cost','purchase_price','price','cost']) ?? 0) ?: null,
                _normDate(_pick($row, ['warranty_expiry','warranty_expires'])),
                _pick($row, ['location']),
                _pick($row, ['assigned_to','checked_out_to']),
                _pick($row, ['notes']),
            ]);
            $imported++;
        } catch (Throwable $ex) {
            $skipped++;
            if (count($errors) < 5) $errors[] = "Row " . ($i+2) . ": " . $ex->getMessage();
        }
    }
    return [$imported, $skipped, $errors];
}

function _importCustomAssets(PDO $db, int $userId, array $parsed, string $tmpPath): array {
    $imported = 0; $skipped = 0; $errors = [];
    $db->beginTransaction();
    try {
    $standard = array_flip(_standardSnipeColumns());
    $customHeaders = [];
    foreach ($parsed['headers'] as $h) {
        if (!isset($standard[$h]) && !str_starts_with($h, 'address_') && $h !== '' && $h !== 'col') {
            $customHeaders[$h] = $h;
        }
    }

    // Read raw header line for original casing labels
    $headerLabels = [];
    foreach ($customHeaders as $h) $headerLabels[$h] = ucwords(str_replace('_', ' ', $h));
    if (($fh2 = @fopen($tmpPath, 'r')) !== false) {
        $rawHeaders = fgetcsv($fh2);
        fclose($fh2);
        if ($rawHeaders) {
            $seen2 = [];
            foreach ($rawHeaders as $rh) {
                $norm = _normalizeHeader($rh) ?: 'col';
                $key = $norm; $i2 = 2;
                while (isset($seen2[$key])) { $key = $norm . '_' . $i2; $i2++; }
                $seen2[$key] = true;
                if (isset($customHeaders[$key])) $headerLabels[$key] = trim($rh);
            }
        }
    }

    // Pass 1: per category, find which custom headers actually have values
    $typeFieldsMap = [];
    foreach ($parsed['rows'] as $row) {
        $cat = trim((string)($row['category'] ?? ''));
        if ($cat === '') continue;
        $typeName = _singularize($cat);
        if (!isset($typeFieldsMap[$typeName])) $typeFieldsMap[$typeName] = [];
        foreach ($customHeaders as $h) {
            $v = trim((string)($row[$h] ?? ''));
            if ($v !== '') $typeFieldsMap[$typeName][$h] = $headerLabels[$h] ?? ucwords(str_replace('_', ' ', $h));
        }
    }

    $detectFieldType = function(string $label, array $samples) : string {
        $l = strtolower($label);
        if (preg_match('/\b(cm|gb|mb|tb|kg|hz|mhz|ghz|inch|inches|cores|ram|storage|length|width|height|depth|weight|count|number|qty|quantity)\b/', $l)) return 'number';
        if (preg_match('/\bdate\b/', $l)) return 'date';
        $nonEmpty = array_filter(array_map('trim', $samples), fn($v) => $v !== '');
        if (!empty($nonEmpty)) {
            $allBool = true;
            foreach ($nonEmpty as $v) {
                if (!preg_match('/^(yes|no|y|n|true|false|0|1)$/i', $v)) { $allBool = false; break; }
            }
            if ($allBool) return 'boolean';
            $allNum = true;
            foreach ($nonEmpty as $v) {
                if (!is_numeric($v)) { $allNum = false; break; }
            }
            if ($allNum) return 'number';
        }
        if (preg_match('/^(modded|enabled|active|supported)$/', $l) || preg_match('/\?$/', $l)) return 'boolean';
        return 'text';
    };

    // Collect samples per (type, header) for type inference
    $samples = [];
    foreach ($parsed['rows'] as $row) {
        $cat = trim((string)($row['category'] ?? ''));
        if ($cat === '') continue;
        $tn = _singularize($cat);
        foreach ($customHeaders as $h) {
            $samples[$tn][$h][] = (string)($row[$h] ?? '');
        }
    }

    // Pass 2: ensure types and fields
    $typeLookup = [];
    foreach ($typeFieldsMap as $tn => $fieldHeaders) {
        $t = ensureAssetType($tn);
        $fields = [];
        foreach ($fieldHeaders as $h => $label) {
            $f = ensureTypeField((int)$t['id'], $label, $detectFieldType($label, $samples[$tn][$h] ?? []));
            $fields[$h] = $f;
        }
        $typeLookup[$tn] = ['type' => $t, 'fields' => $fields];
    }

    // Pass 3: insert/update assets + custom values
    $existingTags = [];
    $stmtTag = $db->query("SELECT id, asset_tag FROM assets WHERE asset_tag IS NOT NULL AND asset_tag <> ''");
    foreach ($stmtTag->fetchAll() as $r) $existingTags[strtoupper($r['asset_tag'])] = (int)$r['id'];

    $insStmt = $db->prepare("INSERT INTO assets (name, category, type, status, asset_tag, serial_number, model, manufacturer, quantity, purchase_date, purchase_price, warranty_expiry, location, assigned_to, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $updStmt = $db->prepare("UPDATE assets SET name=?, category=?, type=?, status=?, serial_number=?, model=?, manufacturer=?, purchase_date=?, purchase_price=?, warranty_expiry=?, location=?, assigned_to=?, notes=? WHERE id=?");

    foreach ($parsed['rows'] as $i => $row) {
        $name = trim((string)($row['asset_name'] ?? ''));
        if ($name === '') { $skipped++; continue; }
        $cat = trim((string)($row['category'] ?? '')) ?: 'hardware';
        $typeName = _singularize($cat);
        $assetTag = trim((string)($row['asset_tag'] ?? ''));
        $serial = trim((string)($row['serial'] ?? ''));
        $model = trim((string)($row['model'] ?? ''));
        $modelNo = trim((string)($row['model_no'] ?? ''));
        if ($model === '' && $modelNo !== '') $model = $modelNo;
        elseif ($model !== '' && $modelNo !== '' && $modelNo !== $model) $model .= ' (' . $modelNo . ')';
        $manufacturer = trim((string)($row['manufacturer'] ?? ''));
        $purchased = _normDate($row['purchased'] ?? null);
        $cost = trim((string)($row['cost'] ?? ''));
        $cost = $cost !== '' ? (float)$cost : null;
        $warranty = _normDate($row['warranty_expires'] ?? null);
        $location = trim((string)($row['location'] ?? '')) ?: trim((string)($row['default_location'] ?? '')) ?: null;
        $assignedTo = trim((string)($row['checked_out'] ?? '')) ?: null;
        $notes = trim((string)($row['notes'] ?? '')) ?: null;
        $status = _snipeStatusToInternal($row['status'] ?? null);

        try {
            $tagKey = strtoupper($assetTag);
            if ($assetTag !== '' && isset($existingTags[$tagKey])) {
                $assetId = $existingTags[$tagKey];
                $updStmt->execute([
                    $name, strtolower($cat), $typeName, $status,
                    $serial ?: null, $model ?: null, $manufacturer ?: null,
                    $purchased, $cost, $warranty, $location, $assignedTo, $notes,
                    $assetId,
                ]);
            } else {
                $insStmt->execute([
                    $name, strtolower($cat), $typeName, $status,
                    $assetTag ?: null, $serial ?: null, $model ?: null, $manufacturer ?: null,
                    1, $purchased, $cost, $warranty, $location, $assignedTo, $notes,
                ]);
                $assetId = (int)$db->lastInsertId();
                if ($assetTag !== '') $existingTags[$tagKey] = $assetId;
            }

            if (isset($typeLookup[$typeName])) {
                foreach ($typeLookup[$typeName]['fields'] as $h => $field) {
                    $val = trim((string)($row[$h] ?? ''));
                    if ($field['field_type'] === 'boolean') {
                        $val = ($val === '' ? null : (preg_match('/^(yes|y|true|1)$/i', $val) ? '1' : '0'));
                    } elseif ($field['field_type'] === 'date') {
                        $val = _normDate($val);
                    }
                    saveCustomFieldValue($assetId, (int)$field['id'], ($val !== null && $val !== '') ? $val : null);
                }
            }
            $imported++;
        } catch (Throwable $ex) {
            $skipped++;
            if (count($errors) < 5) $errors[] = "Row " . ($i+2) . ": " . $ex->getMessage();
        }
    }

    $typeCount = count($typeLookup);
    $fieldCount = 0; foreach ($typeLookup as $tl) $fieldCount += count($tl['fields']);
    $db->commit();
    return [$imported, $skipped, $errors, $typeCount, $fieldCount];
    } catch (Throwable $ex) {
        if ($db->inTransaction()) $db->rollBack();
        $errors[] = 'Aborted: ' . $ex->getMessage();
        return [0, count($parsed['rows']), $errors, 0, 0];
    }
}
