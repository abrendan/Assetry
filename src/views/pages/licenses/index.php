<div class="page-actions">
    <form method="GET" action="/licenses" class="search-bar">
        <div class="search-input-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" class="form-input search-input" placeholder="Search licenses..." value="<?= e($_GET['search'] ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if (!empty($_GET['search'])): ?>
        <a href="/licenses" class="btn btn-ghost">Clear</a>
        <?php endif; ?>
    </form>
    <a href="/licenses/new" class="btn btn-primary">+ Add License</a>
</div>

<?php if (empty($licenses)): ?>
<div class="empty-state-full">
    <div class="empty-icon-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    </div>
    <h3>No licenses tracked</h3>
    <p>Track your software licenses, renewals, and seat counts.</p>
    <a href="/licenses/new" class="btn btn-primary">Add First License</a>
</div>
<?php else: ?>
<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Software</th>
                <th>Vendor</th>
                <th>Type</th>
                <th>Seats</th>
                <th>Cost</th>
                <th>Expiry</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($licenses as $lic): ?>
            <?php
                $expired = $lic['expiry_date'] && strtotime($lic['expiry_date']) < time();
                $expiring = !$expired && $lic['expiry_date'] && strtotime($lic['expiry_date']) < strtotime('+30 days');
                $status = $expired ? 'expired' : ($expiring ? 'expiring' : 'active');
            ?>
            <tr class="<?= $expired ? 'row-danger' : ($expiring ? 'row-warning' : '') ?>">
                <td>
                    <div class="cell-primary">
                        <span class="cell-link"><?= e($lic['software_name']) ?></span>
                    </div>
                </td>
                <td class="text-muted"><?= e($lic['vendor'] ?? '—') ?></td>
                <td><span class="cat-pill"><?= e(ucfirst($lic['license_type'])) ?></span></td>
                <td>
                    <?php if ($lic['seats']): ?>
                    <div class="seats-wrap">
                        <span><?= $lic['seats_used'] ?? 0 ?> / <?= $lic['seats'] ?></span>
                        <div class="mini-bar">
                            <div class="mini-bar-fill" style="width:<?= $lic['seats'] > 0 ? min(100, round((($lic['seats_used']??0)/$lic['seats'])*100)) : 0 ?>%"></div>
                        </div>
                    </div>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td class="text-muted"><?= $lic['cost'] ? '$'.number_format($lic['cost'],2) : '—' ?></td>
                <td>
                    <?php if ($lic['expiry_date']): ?>
                    <span class="<?= $expired ? 'text-danger' : ($expiring ? 'text-warning' : '') ?>"><?= formatDate($lic['expiry_date']) ?></span>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td><?= statusBadge($status) ?></td>
                <td class="cell-actions">
                    <a href="/licenses/<?= $lic['id'] ?>/edit" class="icon-btn" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    <form method="POST" action="/licenses/<?= $lic['id'] ?>/delete" onsubmit="return confirm('Delete this license?')">
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
    <span class="text-muted"><?= count($licenses) ?> license<?= count($licenses) !== 1 ? 's' : '' ?></span>
</div>
<?php endif; ?>
