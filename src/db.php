<?php
require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $db = null;
    if ($db === null) {
        $dir = dirname(DB_PATH);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->exec('PRAGMA journal_mode=WAL');
        $db->exec('PRAGMA foreign_keys=ON');
        initSchema($db);
        migrateSchema($db);
    }
    return $db;
}

function addColumnIfMissing(PDO $db, string $table, string $column, string $definition): void {
    $stmt = $db->query("PRAGMA table_info($table)");
    foreach ($stmt->fetchAll() as $row) {
        if ($row['name'] === $column) return;
    }
    $db->exec("ALTER TABLE $table ADD COLUMN $column $definition");
}

function tableNeedsDetach(PDO $db, string $table, string $userCol = 'user_id'): bool {
    $rows = $db->query("PRAGMA table_info($table)")->fetchAll();
    foreach ($rows as $r) {
        if ($r['name'] === $userCol) {
            return (int)$r['notnull'] === 1;
        }
    }
    return false;
}

function hasColumn(PDO $db, string $table, string $col): bool {
    foreach ($db->query("PRAGMA table_info($table)") as $r) {
        if ($r['name'] === $col) return true;
    }
    return false;
}

function dropOwnershipFromSharedTables(PDO $db): void {
    // Final detachment: shared resources have no owner at all. Drop user_id
    // (and is_private, which only makes sense with an owner) from every
    // shared table. Vault keeps user_id since vault items are private.
    $db->exec('PRAGMA legacy_alter_table=ON');
    $db->exec('PRAGMA foreign_keys=OFF');
    try {
        if (hasColumn($db, 'assets', 'user_id') || hasColumn($db, 'assets', 'is_private')) {
            rebuildTableNoLegacyToggle($db, 'assets', "CREATE TABLE assets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                category TEXT NOT NULL,
                type TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'active',
                asset_tag TEXT,
                serial_number TEXT,
                model TEXT,
                manufacturer TEXT,
                quantity INTEGER DEFAULT 1,
                purchase_date DATE,
                purchase_price REAL,
                warranty_expiry DATE,
                location TEXT,
                assigned_to TEXT,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )", ['id','name','category','type','status','asset_tag','serial_number','model','manufacturer','quantity','purchase_date','purchase_price','warranty_expiry','location','assigned_to','notes','created_at','updated_at']);
        }
        if (hasColumn($db, 'product_keys', 'user_id') || hasColumn($db, 'product_keys', 'is_private')) {
            rebuildTableNoLegacyToggle($db, 'product_keys', "CREATE TABLE product_keys (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_name TEXT NOT NULL,
                key_value TEXT NOT NULL,
                category TEXT NOT NULL DEFAULT 'software',
                platform TEXT,
                manufacturer TEXT,
                licensed_to_name TEXT,
                licensed_to_email TEXT,
                max_activations INTEGER,
                used_activations INTEGER DEFAULT 0,
                purchase_date DATE,
                expiry_date DATE,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )", ['id','product_name','key_value','category','platform','manufacturer','licensed_to_name','licensed_to_email','max_activations','used_activations','purchase_date','expiry_date','notes','created_at']);
        }
        if (hasColumn($db, 'licenses', 'user_id')) {
            rebuildTableNoLegacyToggle($db, 'licenses', "CREATE TABLE licenses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                software_name TEXT NOT NULL,
                vendor TEXT,
                license_type TEXT NOT NULL,
                seats INTEGER DEFAULT 1,
                seats_used INTEGER DEFAULT 0,
                start_date DATE,
                expiry_date DATE,
                cost REAL,
                renewal_cost REAL,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )", ['id','software_name','vendor','license_type','seats','seats_used','start_date','expiry_date','cost','renewal_cost','notes','created_at','updated_at']);
        }
        if (hasColumn($db, 'network_devices', 'user_id')) {
            rebuildTableNoLegacyToggle($db, 'network_devices', "CREATE TABLE network_devices (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                hostname TEXT NOT NULL,
                ip_address TEXT,
                mac_address TEXT,
                device_type TEXT NOT NULL,
                manufacturer TEXT,
                model TEXT,
                firmware_version TEXT,
                location TEXT,
                status TEXT NOT NULL DEFAULT 'active',
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )", ['id','hostname','ip_address','mac_address','device_type','manufacturer','model','firmware_version','location','status','notes','created_at','updated_at']);
        }
        if (hasColumn($db, 'asset_types', 'user_id')) {
            rebuildTableNoLegacyToggle($db, 'asset_types', "CREATE TABLE asset_types (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE COLLATE NOCASE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )", ['id','name','created_at']);
        }
        if (hasColumn($db, 'asset_images', 'user_id')) {
            rebuildTableNoLegacyToggle($db, 'asset_images', "CREATE TABLE asset_images (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                asset_id INTEGER NOT NULL,
                filename TEXT NOT NULL,
                is_cover INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE
            )", ['id','asset_id','filename','is_cover','created_at']);
        }
        if (hasColumn($db, 'key_images', 'user_id')) {
            rebuildTableNoLegacyToggle($db, 'key_images', "CREATE TABLE key_images (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                key_id INTEGER NOT NULL,
                filename TEXT NOT NULL,
                is_cover INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (key_id) REFERENCES product_keys(id) ON DELETE CASCADE
            )", ['id','key_id','filename','is_cover','created_at']);
        }
        if (hasColumn($db, 'key_activations', 'logged_by_user_id')) {
            rebuildTableNoLegacyToggle($db, 'key_activations', "CREATE TABLE key_activations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                key_id INTEGER NOT NULL,
                asset_id INTEGER,
                asset_label TEXT,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (key_id) REFERENCES product_keys(id) ON DELETE CASCADE,
                FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE SET NULL
            )", ['id','key_id','asset_id','asset_label','notes','created_at']);
        }
    } finally {
        $db->exec('PRAGMA foreign_keys=ON');
        $db->exec('PRAGMA legacy_alter_table=OFF');
    }
}

function rebuildTableNoLegacyToggle(PDO $db, string $table, string $newSchema, array $columns): void {
    $colList = implode(',', $columns);
    $db->exec("ALTER TABLE $table RENAME TO {$table}_old_drop");
    $db->exec($newSchema);
    $db->exec("INSERT INTO $table ($colList) SELECT $colList FROM {$table}_old_drop");
    $db->exec("DROP TABLE {$table}_old_drop");
}

function rebuildTable(PDO $db, string $table, string $newSchema, array $columns): void {
    // legacy_alter_table=ON prevents SQLite from rewriting FK references in
    // *other* tables when we rename — important so we don't corrupt schema.
    $db->exec('PRAGMA legacy_alter_table=ON');
    $colList = implode(',', $columns);
    $db->exec("ALTER TABLE $table RENAME TO {$table}_old_detach");
    $db->exec($newSchema);
    $db->exec("INSERT INTO $table ($colList) SELECT $colList FROM {$table}_old_detach");
    $db->exec("DROP TABLE {$table}_old_detach");
    $db->exec('PRAGMA legacy_alter_table=OFF');
}

function detachSharedTablesFromUsers(PDO $db): void {
    // Make shared resources stand on their own — user_id becomes nullable with
    // ON DELETE SET NULL so deleting a user no longer wipes their assets/keys/etc.
    $db->exec('PRAGMA foreign_keys=OFF');
    try {
        if (tableNeedsDetach($db, 'assets')) {
            rebuildTable($db, 'assets', "CREATE TABLE assets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                name TEXT NOT NULL,
                category TEXT NOT NULL,
                type TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'active',
                asset_tag TEXT,
                serial_number TEXT,
                model TEXT,
                manufacturer TEXT,
                quantity INTEGER DEFAULT 1,
                purchase_date DATE,
                purchase_price REAL,
                warranty_expiry DATE,
                location TEXT,
                assigned_to TEXT,
                notes TEXT,
                is_private INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )", ['id','user_id','name','category','type','status','asset_tag','serial_number','model','manufacturer','quantity','purchase_date','purchase_price','warranty_expiry','location','assigned_to','notes','is_private','created_at','updated_at']);
        }
        if (tableNeedsDetach($db, 'product_keys')) {
            rebuildTable($db, 'product_keys', "CREATE TABLE product_keys (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                product_name TEXT NOT NULL,
                key_value TEXT NOT NULL,
                category TEXT NOT NULL DEFAULT 'software',
                platform TEXT,
                manufacturer TEXT,
                licensed_to_name TEXT,
                licensed_to_email TEXT,
                max_activations INTEGER,
                used_activations INTEGER DEFAULT 0,
                purchase_date DATE,
                expiry_date DATE,
                notes TEXT,
                is_private INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )", ['id','user_id','product_name','key_value','category','platform','manufacturer','licensed_to_name','licensed_to_email','max_activations','used_activations','purchase_date','expiry_date','notes','is_private','created_at']);
        }
        if (tableNeedsDetach($db, 'licenses')) {
            rebuildTable($db, 'licenses', "CREATE TABLE licenses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                software_name TEXT NOT NULL,
                vendor TEXT,
                license_type TEXT NOT NULL,
                seats INTEGER DEFAULT 1,
                seats_used INTEGER DEFAULT 0,
                start_date DATE,
                expiry_date DATE,
                cost REAL,
                renewal_cost REAL,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )", ['id','user_id','software_name','vendor','license_type','seats','seats_used','start_date','expiry_date','cost','renewal_cost','notes','created_at','updated_at']);
        }
        if (tableNeedsDetach($db, 'network_devices')) {
            rebuildTable($db, 'network_devices', "CREATE TABLE network_devices (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                hostname TEXT NOT NULL,
                ip_address TEXT,
                mac_address TEXT,
                device_type TEXT NOT NULL,
                manufacturer TEXT,
                model TEXT,
                firmware_version TEXT,
                location TEXT,
                status TEXT NOT NULL DEFAULT 'active',
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )", ['id','user_id','hostname','ip_address','mac_address','device_type','manufacturer','model','firmware_version','location','status','notes','created_at','updated_at']);
        }
        if (tableNeedsDetach($db, 'asset_types')) {
            rebuildTable($db, 'asset_types', "CREATE TABLE asset_types (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                name TEXT NOT NULL UNIQUE COLLATE NOCASE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )", ['id','user_id','name','created_at']);
        }
        if (tableNeedsDetach($db, 'asset_images')) {
            rebuildTable($db, 'asset_images', "CREATE TABLE asset_images (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                asset_id INTEGER NOT NULL,
                user_id INTEGER,
                filename TEXT NOT NULL,
                is_cover INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )", ['id','asset_id','user_id','filename','is_cover','created_at']);
        }
        if (tableNeedsDetach($db, 'key_images')) {
            rebuildTable($db, 'key_images', "CREATE TABLE key_images (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                key_id INTEGER NOT NULL,
                user_id INTEGER,
                filename TEXT NOT NULL,
                is_cover INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (key_id) REFERENCES product_keys(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )", ['id','key_id','user_id','filename','is_cover','created_at']);
        }
        if (tableNeedsDetach($db, 'key_activations', 'logged_by_user_id')) {
            rebuildTable($db, 'key_activations', "CREATE TABLE key_activations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                key_id INTEGER NOT NULL,
                asset_id INTEGER,
                asset_label TEXT,
                notes TEXT,
                logged_by_user_id INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (key_id) REFERENCES product_keys(id) ON DELETE CASCADE,
                FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE SET NULL,
                FOREIGN KEY (logged_by_user_id) REFERENCES users(id) ON DELETE SET NULL
            )", ['id','key_id','asset_id','asset_label','notes','logged_by_user_id','created_at']);
        }
    } finally {
        $db->exec('PRAGMA foreign_keys=ON');
    }
}

function migrateSchema(PDO $db): void {
    addColumnIfMissing($db, 'assets', 'asset_tag', 'TEXT');
    addColumnIfMissing($db, 'assets', 'quantity', 'INTEGER DEFAULT 1');
    addColumnIfMissing($db, 'product_keys', 'manufacturer', 'TEXT');
    addColumnIfMissing($db, 'product_keys', 'licensed_to_email', 'TEXT');
    addColumnIfMissing($db, 'product_keys', 'licensed_to_name', 'TEXT');
    detachSharedTablesFromUsers($db);
    dropOwnershipFromSharedTables($db);

    $db->exec("CREATE TABLE IF NOT EXISTS asset_types (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(user_id, name),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $db->exec("CREATE TABLE IF NOT EXISTS asset_type_fields (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        type_id INTEGER NOT NULL,
        field_key TEXT NOT NULL,
        field_label TEXT NOT NULL,
        field_type TEXT NOT NULL DEFAULT 'text',
        sort_order INTEGER DEFAULT 0,
        UNIQUE(type_id, field_key),
        FOREIGN KEY (type_id) REFERENCES asset_types(id) ON DELETE CASCADE
    )");
    $db->exec("CREATE TABLE IF NOT EXISTS asset_field_values (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        asset_id INTEGER NOT NULL,
        field_id INTEGER NOT NULL,
        value TEXT,
        UNIQUE(asset_id, field_id),
        FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
        FOREIGN KEY (field_id) REFERENCES asset_type_fields(id) ON DELETE CASCADE
    )");
}

function initSchema(PDO $db): void {
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'user',
            avatar_color TEXT NOT NULL DEFAULT '#6366f1',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME,
            is_active INTEGER DEFAULT 1
        );

        CREATE TABLE IF NOT EXISTS assets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            category TEXT NOT NULL,
            type TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'active',
            asset_tag TEXT,
            serial_number TEXT,
            model TEXT,
            manufacturer TEXT,
            quantity INTEGER DEFAULT 1,
            purchase_date DATE,
            purchase_price REAL,
            warranty_expiry DATE,
            location TEXT,
            assigned_to TEXT,
            notes TEXT,
            is_private INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS product_keys (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            product_name TEXT NOT NULL,
            key_value TEXT NOT NULL,
            category TEXT NOT NULL DEFAULT 'software',
            platform TEXT,
            manufacturer TEXT,
            licensed_to_name TEXT,
            licensed_to_email TEXT,
            max_activations INTEGER,
            used_activations INTEGER DEFAULT 0,
            purchase_date DATE,
            expiry_date DATE,
            notes TEXT,
            is_private INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS licenses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            software_name TEXT NOT NULL,
            vendor TEXT,
            license_type TEXT NOT NULL,
            seats INTEGER DEFAULT 1,
            seats_used INTEGER DEFAULT 0,
            start_date DATE,
            expiry_date DATE,
            cost REAL,
            renewal_cost REAL,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS network_devices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            hostname TEXT NOT NULL,
            ip_address TEXT,
            mac_address TEXT,
            device_type TEXT NOT NULL,
            manufacturer TEXT,
            model TEXT,
            firmware_version TEXT,
            location TEXT,
            status TEXT NOT NULL DEFAULT 'active',
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS documents (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            asset_id INTEGER,
            title TEXT NOT NULL,
            filename TEXT NOT NULL,
            file_size INTEGER,
            mime_type TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS activity_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            action TEXT NOT NULL,
            entity_type TEXT,
            entity_id INTEGER,
            details TEXT,
            ip_address TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS custom_categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            entity_type TEXT NOT NULL,
            name TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, entity_type, name),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS asset_images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            asset_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            filename TEXT NOT NULL,
            is_cover INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS key_images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            key_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            filename TEXT NOT NULL,
            is_cover INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (key_id) REFERENCES product_keys(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS key_activations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            key_id INTEGER NOT NULL,
            asset_id INTEGER,
            asset_label TEXT,
            notes TEXT,
            logged_by_user_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (key_id) REFERENCES product_keys(id) ON DELETE CASCADE,
            FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE SET NULL,
            FOREIGN KEY (logged_by_user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS vault_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            item_type TEXT NOT NULL DEFAULT 'note',
            content TEXT NOT NULL,
            tags TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS vault_images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            vault_item_id INTEGER NOT NULL,
            filename TEXT NOT NULL,
            is_cover INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (vault_item_id) REFERENCES vault_items(id) ON DELETE CASCADE
        );
    ");
    seedDefaultAdmin($db);
}

function seedDefaultAdmin(PDO $db): void {
    $count = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($count > 0) return;
    $hash = password_hash('admin', PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, role, avatar_color) VALUES (?, ?, ?, 'admin', ?)");
    $stmt->execute(['admin', 'admin@assetry.local', $hash, '#6366f1']);
    seedDefaultAssetTypes((int)$db->lastInsertId());
}

function seedDefaultAssetTypes(int $userId): void {
    if (!function_exists('ensureAssetType') || !function_exists('ensureTypeField')) {
        require_once __DIR__ . '/helpers.php';
    }
    $defaults = [
        'Laptop' => [
            ['Operating System', 'text'],
            ['Processor', 'text'],
            ['RAM (GB)', 'number'],
            ['Storage (GB)', 'number'],
        ],
        'Desktop' => [
            ['Operating System', 'text'],
            ['Processor', 'text'],
            ['RAM (GB)', 'number'],
            ['Storage (GB)', 'number'],
        ],
        'Phone' => [
            ['Operating System', 'text'],
            ['Storage (GB)', 'number'],
            ['IMEI', 'text'],
        ],
        'Monitor' => [
            ['Size (inches)', 'number'],
            ['Resolution', 'text'],
            ['Refresh Rate (Hz)', 'number'],
        ],
        'Network Device' => [
            ['IP Address', 'text'],
            ['MAC Address', 'text'],
            ['Firmware Version', 'text'],
        ],
        'Peripheral' => [
            ['Connection Type', 'text'],
        ],
    ];
    foreach ($defaults as $typeName => $fields) {
        $t = ensureAssetType($userId, $typeName);
        foreach ($fields as [$label, $ftype]) {
            ensureTypeField((int)$t['id'], $label, $ftype);
        }
    }
}

function logActivity(int $userId, string $action, string $entityType = null, int $entityId = null, string $details = null): void {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $entityType, $entityId, $details, $_SERVER['REMOTE_ADDR'] ?? null]);
}
