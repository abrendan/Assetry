<?php
require_once __DIR__ . '/../../auth.php';
$user = currentUser();
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function navActive(string $path): string {
    global $currentPath;
    if ($path === '/dashboard') {
        return $currentPath === '/dashboard' ? 'nav-item active' : 'nav-item';
    }
    return strpos($currentPath, $path) === 0 ? 'nav-item active' : 'nav-item';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Assetry') ?> — Assetry</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/static/app.css?v=<?= filemtime(__DIR__ . '/../../../public/static/app.css') ?>">
    <link rel="icon" type="image/svg+xml" href="/static/logo.svg">
</head>
<body>
<div class="app-shell">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="/dashboard" class="brand">
                <div class="brand-icon brand-icon-img">
                    <img src="/static/logo.svg" alt="Assetry" width="32" height="32">
                </div>
                <span class="brand-name">Assetry</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <span class="nav-label">Overview</span>
                <a href="/dashboard" class="<?= navActive('/dashboard') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="nav-section">
                <span class="nav-label">Assets</span>
                <a href="/assets" class="<?= navActive('/assets') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <span>Hardware & Software</span>
                </a>
                <a href="/network" class="<?= navActive('/network') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="3"/><line x1="12" y1="8" x2="12" y2="11"/><circle cx="5" cy="19" r="3"/><circle cx="19" cy="19" r="3"/><line x1="8.5" y1="17.5" x2="10" y2="13"/><line x1="15.5" y1="17.5" x2="14" y2="13"/></svg>
                    <span>Network Devices</span>
                </a>
                <a href="/licenses" class="<?= navActive('/licenses') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <span>Licenses</span>
                </a>
                <a href="/keys" class="<?= navActive('/keys') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                    <span>Product Keys</span>
                </a>
            </div>

            <div class="nav-section">
                <span class="nav-label">Settings</span>
                <a href="/categories" class="<?= navActive('/categories') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                    <span>Categories</span>
                </a>
                <a href="/types" class="<?= navActive('/types') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/></svg>
                    <span>Types &amp; Fields</span>
                </a>
                <a href="/data" class="<?= navActive('/data') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span>Import / Export</span>
                </a>
            </div>

            <div class="nav-section">
                <span class="nav-label">Secure</span>
                <a href="/vault" class="<?= navActive('/vault') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span>Private Assets</span>
                </a>
            </div>

            <?php if ($user && $user['role'] === 'admin'): ?>
            <div class="nav-section">
                <span class="nav-label">Administration</span>
                <a href="/admin/users" class="<?= navActive('/admin') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>User Management</span>
                </a>
                <a href="/admin/logs" class="<?= navActive('/admin/logs') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <span>Activity Log</span>
                </a>
                <a href="/admin/sql" class="<?= navActive('/admin/sql') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>
                    <span>SQL Console</span>
                </a>
            </div>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <a href="/profile" class="user-card">
                <div class="avatar" style="background: <?= e($user['avatar_color'] ?? '#6366f1') ?>">
                    <?= e(initials($user['username'] ?? 'U')) ?>
                </div>
                <div class="user-info">
                    <span class="user-name"><?= e($user['username'] ?? '') ?></span>
                    <span class="user-role"><?= e(ucfirst($user['role'] ?? 'user')) ?></span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
            </a>
        </div>
    </aside>

    <!-- Main content -->
    <main class="main-content">
        <!-- Top bar -->
        <header class="topbar">
            <div class="topbar-left">
                <h1 class="page-title"><?= e($title ?? 'Dashboard') ?></h1>
                <?php if (!empty($subtitle)): ?>
                <span class="page-subtitle"><?= e($subtitle) ?></span>
                <?php endif; ?>
            </div>
            <div class="topbar-right">
                <?php if ($flash = flash('success')): ?>
                <div class="flash flash-success"><?= e($flash) ?></div>
                <?php endif; ?>
                <?php if ($flash = flash('error')): ?>
                <div class="flash flash-error"><?= e($flash) ?></div>
                <?php endif; ?>
                <button type="button" class="btn-ghost btn-sm" onclick="document.getElementById('about-modal').style.display='flex'" title="About Assetry" style="padding:0.4rem">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                </button>
                <a href="/logout" class="btn-ghost btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Sign out
                </a>
            </div>
        </header>

        <div class="content-area">
            <?php echo $content ?? ''; ?>
        </div>
    </main>
</div>

<div id="about-modal" onclick="if(event.target===this)this.style.display='none'" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);z-index:1000;align-items:center;justify-content:center;padding:1rem;overflow-y:auto">
    <div style="background:linear-gradient(180deg,#22242c 0%,#1a1c22 100%);border:1px solid rgba(255,255,255,0.1);border-radius:16px;max-width:680px;width:100%;position:relative;box-shadow:0 30px 80px rgba(0,0,0,0.6);max-height:90vh;overflow-y:auto">
        <button type="button" onclick="document.getElementById('about-modal').style.display='none'" style="position:absolute;top:1rem;right:1rem;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.08);border-radius:8px;color:#cbd5e1;cursor:pointer;padding:0.4rem;line-height:0;z-index:2" aria-label="Close">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <div style="padding:2.5rem 2rem 1.5rem;text-align:center;background:radial-gradient(ellipse at top,rgba(99,102,241,0.18),transparent 70%);border-radius:16px 16px 0 0">
            <div style="display:inline-flex;align-items:center;justify-content:center;width:120px;height:120px;border-radius:28px;background:linear-gradient(135deg,#6366f1,#8b5cf6);margin-bottom:1rem;box-shadow:0 12px 40px rgba(99,102,241,0.4)">
                <img src="/static/logo.svg" alt="Assetry" style="width:84px;height:84px">
            </div>
            <h2 style="margin:0;font-size:2rem;color:#f9fafb;letter-spacing:-0.02em">Assetry</h2>
            <p style="margin:0.5rem 0 0;font-size:0.95rem;color:#9ca3af">Modern, self-hosted IT asset management</p>
            <div style="display:inline-block;margin-top:0.75rem;padding:0.2rem 0.65rem;background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.3);border-radius:999px;font-size:0.7rem;color:#a5b4fc;font-weight:600;letter-spacing:0.05em">VERSION 1.0</div>
        </div>

        <div style="padding:0 2rem 1.5rem">
            <p style="margin:0 0 1.5rem;line-height:1.65;color:#d1d5db;font-size:0.9rem;text-align:center">
                A complete inventory platform for hardware, software keys, licenses, network gear,
                and sensitive credentials &mdash; built for small IT teams and homelabs that want
                full control of their data.
            </p>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:0.75rem;margin-bottom:1.5rem">
                <div style="padding:0.85rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:10px">
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.35rem">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#a5b4fc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        <span style="font-weight:600;font-size:0.8rem;color:#f3f4f6">Asset Tracking</span>
                    </div>
                    <p style="margin:0;font-size:0.75rem;color:#9ca3af;line-height:1.45">Track hardware with custom types, images, serials, and warranty dates.</p>
                </div>
                <div style="padding:0.85rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:10px">
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.35rem">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#a5b4fc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                        <span style="font-weight:600;font-size:0.8rem;color:#f3f4f6">Product Keys</span>
                    </div>
                    <p style="margin:0;font-size:0.75rem;color:#9ca3af;line-height:1.45">Store license keys with seat counts and per-device activation tracking.</p>
                </div>
                <div style="padding:0.85rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:10px">
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.35rem">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#a5b4fc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span style="font-weight:600;font-size:0.8rem;color:#f3f4f6">Private Vault</span>
                    </div>
                    <p style="margin:0;font-size:0.75rem;color:#9ca3af;line-height:1.45">Per-user vault for credentials, notes, and image attachments.</p>
                </div>
                <div style="padding:0.85rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:10px">
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.35rem">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#a5b4fc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        <span style="font-weight:600;font-size:0.8rem;color:#f3f4f6">Network Inventory</span>
                    </div>
                    <p style="margin:0;font-size:0.75rem;color:#9ca3af;line-height:1.45">Document switches, APs, IPs, MACs, and other network infrastructure.</p>
                </div>
                <div style="padding:0.85rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:10px">
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.35rem">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#a5b4fc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span style="font-weight:600;font-size:0.8rem;color:#f3f4f6">Multi-User</span>
                    </div>
                    <p style="margin:0;font-size:0.75rem;color:#9ca3af;line-height:1.45">Internal accounts with roles, activity logs, and admin management.</p>
                </div>
                <div style="padding:0.85rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:10px">
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.35rem">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#a5b4fc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <span style="font-weight:600;font-size:0.8rem;color:#f3f4f6">Import / Export</span>
                    </div>
                    <p style="margin:0;font-size:0.75rem;color:#9ca3af;line-height:1.45">CSV import and export for bulk migration and backups.</p>
                </div>
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:0.4rem;justify-content:center;margin-bottom:1.5rem">
                <span style="padding:0.25rem 0.6rem;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:6px;font-size:0.7rem;color:#cbd5e1;font-family:monospace">PHP 8.2</span>
                <span style="padding:0.25rem 0.6rem;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:6px;font-size:0.7rem;color:#cbd5e1;font-family:monospace">SQLite</span>
                <span style="padding:0.25rem 0.6rem;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:6px;font-size:0.7rem;color:#cbd5e1;font-family:monospace">Vanilla JS / CSS</span>
                <span style="padding:0.25rem 0.6rem;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:6px;font-size:0.7rem;color:#cbd5e1;font-family:monospace">Apache / Docker</span>
                <span style="padding:0.25rem 0.6rem;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:6px;font-size:0.7rem;color:#cbd5e1;font-family:monospace">No Framework</span>
            </div>

            <a href="https://www.abrendan.dev" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:1rem;padding:1.25rem;background:linear-gradient(135deg,rgba(99,102,241,0.1),rgba(139,92,246,0.05));border:1px solid rgba(255,255,255,0.1);border-radius:12px;text-decoration:none;color:inherit;transition:transform 0.15s,border-color 0.15s" onmouseover="this.style.borderColor='rgba(165,180,252,0.4)';this.style.transform='translateY(-1px)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)';this.style.transform='translateY(0)'">
                <img src="/static/abrendan.svg" alt="abrendan" style="width:72px;height:72px;border-radius:12px;background:#fff;padding:6px;flex-shrink:0">
                <div style="flex:1;min-width:0">
                    <div style="font-size:0.7rem;color:#9ca3af;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.2rem">Crafted by</div>
                    <div style="font-weight:700;font-size:1.15rem;color:#f9fafb">abrendan</div>
                    <div style="color:#a5b4fc;font-size:0.8rem;margin-top:0.15rem">www.abrendan.dev &rarr;</div>
                </div>
            </a>
        </div>
    </div>
</div>
<script src="/static/app.js"></script>
</body>
</html>
