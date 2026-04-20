<div class="page-actions">
    <form method="GET" action="/network" class="search-bar">
        <div class="search-input-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" class="form-input search-input" placeholder="Search by hostname, IP..." value="<?= e($_GET['search'] ?? '') ?>">
        </div>
        <select name="type" class="form-select">
            <option value="">All Types</option>
            <?php foreach (getNetworkDeviceTypes() as $t): ?>
            <option value="<?= $t ?>" <?= ($_GET['type'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$t)) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if (!empty($_GET['search']) || !empty($_GET['type'])): ?>
        <a href="/network" class="btn btn-ghost">Clear</a>
        <?php endif; ?>
    </form>
    <a href="/network/new" class="btn btn-primary">+ Add Device</a>
</div>

<?php if (empty($devices)): ?>
<div class="empty-state-full">
    <div class="empty-icon-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="3"/><line x1="12" y1="8" x2="12" y2="11"/><circle cx="5" cy="19" r="3"/><circle cx="19" cy="19" r="3"/><line x1="8.5" y1="17.5" x2="10" y2="13"/><line x1="15.5" y1="17.5" x2="14" y2="13"/></svg>
    </div>
    <h3>No network devices</h3>
    <p>Track routers, switches, servers, and other network infrastructure.</p>
    <a href="/network/new" class="btn btn-primary">Add First Device</a>
</div>
<?php else: ?>
<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Hostname</th>
                <th>Type</th>
                <th>IP Address</th>
                <th>MAC Address</th>
                <th>Manufacturer / Model</th>
                <th>Location</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($devices as $dev): ?>
            <tr>
                <td>
                    <span class="cell-link"><?= e($dev['hostname']) ?></span>
                    <?php if ($dev['firmware_version']): ?><br><small class="text-muted">fw: <?= e($dev['firmware_version']) ?></small><?php endif; ?>
                </td>
                <td><span class="cat-pill"><?= e(ucfirst(str_replace('_',' ',$dev['device_type']))) ?></span></td>
                <td class="mono text-muted"><?= e($dev['ip_address'] ?? '—') ?></td>
                <td class="mono text-muted" style="font-size:0.8rem"><?= e($dev['mac_address'] ?? '—') ?></td>
                <td class="text-muted"><?= e($dev['manufacturer'] ?? '') ?><?php if ($dev['model']): ?> <?= e($dev['model']) ?><?php endif; ?></td>
                <td class="text-muted"><?= e($dev['location'] ?? '—') ?></td>
                <td><?= statusBadge($dev['status']) ?></td>
                <td class="cell-actions">
                    <a href="/network/<?= $dev['id'] ?>/edit" class="icon-btn" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    <form method="POST" action="/network/<?= $dev['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this device?')">
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
    <span class="text-muted"><?= count($devices) ?> device<?= count($devices) !== 1 ? 's' : '' ?></span>
</div>
<?php endif; ?>
