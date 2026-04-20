<div class="page-actions">
    <span class="text-muted">Page <?= $page ?> of <?= $totalPages ?> — <?= $total ?> total entries</span>
</div>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Time</th>
                <th>User</th>
                <th>Action</th>
                <th>Entity</th>
                <th>Details</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td class="text-muted" style="white-space:nowrap"><?= formatDateTime($log['created_at']) ?></td>
                <td><?= e($log['username'] ?? 'system') ?></td>
                <td><span class="action-badge"><?= e($log['action']) ?></span></td>
                <td class="text-muted"><?= $log['entity_type'] ? e($log['entity_type']) . ' #' . $log['entity_id'] : '—' ?></td>
                <td class="text-muted"><?= e($log['details'] ?? '—') ?></td>
                <td class="mono text-muted" style="font-size:0.75rem"><?= e($log['ip_address'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?>" class="btn btn-ghost btn-sm">Previous</a>
    <?php endif; ?>
    <?php for ($p = max(1, $page-2); $p <= min($totalPages, $page+2); $p++): ?>
    <a href="?page=<?= $p ?>" class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-ghost' ?>"><?= $p ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
    <a href="?page=<?= $page + 1 ?>" class="btn btn-ghost btn-sm">Next</a>
    <?php endif; ?>
</div>
<?php endif; ?>
