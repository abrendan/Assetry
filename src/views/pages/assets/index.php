<?php $vaultMode = !empty($vaultMode); $listUrl = $vaultMode ? '/vault' : '/assets'; $newUrl = $vaultMode ? '/assets/new?private=1' : '/assets/new'; $newLabel = $vaultMode ? '+ Add Private Asset' : '+ Add Asset'; ?>
<div class="page-actions">
    <form method="GET" action="<?= $listUrl ?>" class="search-bar">
        <div class="search-input-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" class="form-input search-input" placeholder="Search assets..." value="<?= e($_GET['search'] ?? '') ?>">
        </div>
        <select name="category" class="form-select">
            <option value="">All Categories</option>
            <?php foreach (getCategoriesFor('asset') as $cat): ?>
            <option value="<?= e($cat) ?>" <?= ($_GET['category'] ?? '') === $cat ? 'selected' : '' ?>><?= e(ucfirst($cat)) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" class="form-select">
            <option value="">All Statuses</option>
            <?php foreach (getAssetStatuses() as $st): ?>
            <option value="<?= $st ?>" <?= ($_GET['status'] ?? '') === $st ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$st)) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if (!empty($_GET['search']) || !empty($_GET['category']) || !empty($_GET['status'])): ?>
        <a href="<?= $listUrl ?>" class="btn btn-ghost">Clear</a>
        <?php endif; ?>
    </form>
    <a href="<?= $newUrl ?>" class="btn btn-primary"><?= $newLabel ?></a>
</div>

<?php if (empty($assets)): ?>
<div class="empty-state-full">
    <div class="empty-icon-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
    </div>
    <h3>No assets found</h3>
    <p>Start tracking your IT hardware and software.</p>
    <a href="/assets/new" class="btn btn-primary">Add First Asset</a>
</div>
<?php else: ?>
<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Model / Manufacturer</th>
                <th>Serial #</th>
                <th>Location</th>
                <th>Warranty</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($assets as $asset): ?>
            <tr>
                <td>
                    <div class="cell-primary">
                        <?php if (!empty($asset['cover_image'])): ?>
                        <div style="width:36px;height:36px;border-radius:6px;overflow:hidden;flex-shrink:0;background:#0f0f17">
                            <img src="/uploads/<?= e($asset['cover_image']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;display:block">
                        </div>
                        <?php else: ?>
                        <div class="cell-icon"><?= categoryIcon($asset['category']) ?></div>
                        <?php endif; ?>
                        <div>
                            <a href="/assets/<?= $asset['id'] ?>" class="cell-link"><?= e($asset['name']) ?></a>
                        </div>
                    </div>
                </td>
                <td><span class="cat-pill"><?= e(ucfirst($asset['category'])) ?></span></td>
                <td class="text-muted"><?= e($asset['model'] ?? '') ?><?php if ($asset['manufacturer']): ?><br><small><?= e($asset['manufacturer']) ?></small><?php endif; ?></td>
                <td class="mono text-muted"><?= e($asset['serial_number'] ?? '—') ?></td>
                <td class="text-muted"><?= e($asset['location'] ?? '—') ?></td>
                <td class="text-muted">
                    <?php if ($asset['warranty_expiry']): ?>
                    <?php $days = daysUntil($asset['warranty_expiry']); ?>
                    <span class="<?= $days < 0 ? 'text-danger' : ($days < 60 ? 'text-warning' : '') ?>"><?= formatDate($asset['warranty_expiry']) ?></span>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td><?= statusBadge($asset['status']) ?></td>
                <td class="cell-actions">
                    <a href="/assets/<?= $asset['id'] ?>/edit" class="icon-btn" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    <form method="POST" action="/assets/<?= $asset['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this asset?')">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        <button type="submit" class="icon-btn icon-btn-danger" title="Delete">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="table-footer">
    <span class="text-muted"><?= count($assets) ?> asset<?= count($assets) !== 1 ? 's' : '' ?></span>
</div>
<?php endif; ?>
