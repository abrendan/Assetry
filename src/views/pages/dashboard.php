<div class="dashboard-grid">

    <!-- Stats row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon stat-blue">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?= $totalAssets ?></span>
                <span class="stat-label">Total Assets</span>
            </div>
            <div class="stat-sub"><?= $activeAssets ?> active</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-purple">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?= $totalKeys ?></span>
                <span class="stat-label">Product Keys</span>
            </div>
            <div class="stat-sub">managed</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-teal">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?= $totalLicenses ?></span>
                <span class="stat-label">Licenses</span>
            </div>
            <div class="stat-sub"><?= count($expiringLicenses) ?> expiring</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-orange">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="3"/><line x1="12" y1="8" x2="12" y2="11"/><circle cx="5" cy="19" r="3"/><circle cx="19" cy="19" r="3"/><line x1="8.5" y1="17.5" x2="10" y2="13"/><line x1="15.5" y1="17.5" x2="14" y2="13"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?= $networkDevices ?></span>
                <span class="stat-label">Network Devices</span>
            </div>
            <div class="stat-sub">tracked</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-pink">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?= $vaultItems ?></span>
                <span class="stat-label">Vault Items</span>
            </div>
            <div class="stat-sub">private</div>
        </div>
    </div>

    <!-- Alerts row -->
    <?php if (!empty($expiringLicenses) || !empty($expiringWarranties)): ?>
    <div class="alerts-section">
        <?php if (!empty($expiringLicenses)): ?>
        <div class="alert-card alert-warning">
            <div class="alert-card-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span>Licenses Expiring Soon</span>
            </div>
            <?php foreach ($expiringLicenses as $lic): ?>
            <div class="alert-item">
                <span><?= e($lic['software_name']) ?></span>
                <span class="alert-date"><?= formatDate($lic['expiry_date']) ?></span>
            </div>
            <?php endforeach; ?>
            <a href="/licenses" class="alert-link">View all licenses</a>
        </div>
        <?php endif; ?>

        <?php if (!empty($expiringWarranties)): ?>
        <div class="alert-card alert-info">
            <div class="alert-card-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>Warranties Expiring</span>
            </div>
            <?php foreach ($expiringWarranties as $asset): ?>
            <div class="alert-item">
                <span><?= e($asset['name']) ?></span>
                <span class="alert-date"><?= formatDate($asset['warranty_expiry']) ?></span>
            </div>
            <?php endforeach; ?>
            <a href="/assets" class="alert-link">View all assets</a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Main content area -->
    <div class="dash-main">
        <!-- Category breakdown -->
        <?php if (!empty($categoryBreakdown)): ?>
        <div class="dash-card">
            <div class="card-header">
                <h3 class="card-title">Assets by Category</h3>
            </div>
            <div class="category-bars">
                <?php
                $maxCount = max(array_column($categoryBreakdown, 'count'));
                $colorMap = ['hardware'=>'#6366f1','software'=>'#8b5cf6','network'=>'#06b6d4','peripheral'=>'#10b981','mobile'=>'#f59e0b','storage'=>'#ec4899','other'=>'#64748b'];
                foreach ($categoryBreakdown as $cat): 
                    $pct = $maxCount > 0 ? round(($cat['count'] / $maxCount) * 100) : 0;
                    $color = $colorMap[strtolower($cat['category'])] ?? '#6366f1';
                ?>
                <div class="cat-bar-row">
                    <span class="cat-bar-label"><?= e(ucfirst($cat['category'])) ?></span>
                    <div class="cat-bar-track">
                        <div class="cat-bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
                    </div>
                    <span class="cat-bar-count"><?= $cat['count'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent assets -->
        <div class="dash-card">
            <div class="card-header">
                <h3 class="card-title">Recent Assets</h3>
                <a href="/assets/new" class="btn btn-primary btn-sm">+ Add Asset</a>
            </div>
            <?php if (empty($recentAssets)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                </div>
                <p>No assets yet</p>
                <a href="/assets/new" class="btn btn-primary btn-sm">Add your first asset</a>
            </div>
            <?php else: ?>
            <div class="item-list">
                <?php foreach ($recentAssets as $asset): ?>
                <a href="/assets/<?= $asset['id'] ?>" class="item-row">
                    <div class="item-icon-wrap"><?= categoryIcon($asset['category']) ?></div>
                    <div class="item-meta">
                        <span class="item-name"><?= e($asset['name']) ?></span>
                        <span class="item-sub"><?= e(ucfirst($asset['category'])) ?> <?php if ($asset['model']): ?>· <?= e($asset['model']) ?><?php endif; ?></span>
                    </div>
                    <div class="item-end">
                        <?= statusBadge($asset['status']) ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Activity feed -->
    <div class="dash-side">
        <div class="dash-card">
            <div class="card-header">
                <h3 class="card-title">Recent Activity</h3>
            </div>
            <?php if (empty($recentActivity)): ?>
            <div class="empty-state-sm">No activity yet</div>
            <?php else: ?>
            <div class="activity-feed">
                <?php foreach ($recentActivity as $log): ?>
                <div class="activity-item">
                    <div class="activity-dot"></div>
                    <div class="activity-content">
                        <span class="activity-action"><?= e(ucfirst(str_replace('_', ' ', $log['action']))) ?></span>
                        <?php if ($log['entity_type']): ?>
                        <span class="activity-entity"><?= e(str_replace('_', ' ', $log['entity_type'])) ?></span>
                        <?php endif; ?>
                        <span class="activity-time"><?= formatDateTime($log['created_at']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="dash-card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="quick-actions">
                <a href="/assets/new" class="quick-action">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Add Asset
                </a>
                <a href="/keys/new" class="quick-action">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                    Add Product Key
                </a>
                <a href="/licenses/new" class="quick-action">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Add License
                </a>
                <a href="/network/new" class="quick-action">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="3"/><line x1="12" y1="8" x2="12" y2="11"/><circle cx="5" cy="19" r="3"/><circle cx="19" cy="19" r="3"/><line x1="8.5" y1="17.5" x2="10" y2="13"/><line x1="15.5" y1="17.5" x2="14" y2="13"/></svg>
                    Add Network Device
                </a>
                <a href="/vault/new" class="quick-action">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Add to Vault
                </a>
            </div>
        </div>
    </div>
</div>
