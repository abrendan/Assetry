<?php
$query = $query ?? '';
$results = $results ?? null;
$columns = $columns ?? [];
$error = $error ?? null;
$rowsAffected = $rowsAffected ?? null;
$execTime = $execTime ?? null;
$tables = $tables ?? [];
$queryType = $queryType ?? '';
?>
<div class="page-actions">
    <p class="text-muted" style="margin:0;font-size:0.85rem">
        Run raw SQL against the SQLite database. <strong style="color:var(--warning,#fbbf24)">Use with care</strong> — destructive queries cannot be undone.
    </p>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1fr) 240px;gap:1.25rem;align-items:start">
    <div>
        <form method="POST" action="/admin/sql" class="form-card" style="margin:0">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
            <div class="form-section" style="padding-bottom:0">
                <h3 class="form-section-title" style="display:flex;align-items:center;gap:0.5rem">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>
                    SQL Console
                </h3>
                <div class="form-group">
                    <label class="form-label">Query</label>
                    <textarea name="query" rows="8" class="form-input mono" spellcheck="false" autocomplete="off" placeholder="SELECT * FROM users LIMIT 10;" required style="font-size:0.85rem;line-height:1.5;resize:vertical"><?= e($query) ?></textarea>
                    <small class="text-muted" style="margin-top:0.4rem;display:block">Tip: <kbd>Ctrl</kbd>+<kbd>Enter</kbd> to run</small>
                </div>
                <div style="display:flex;gap:0.5rem;align-items:center;margin-top:0.5rem">
                    <button type="submit" class="btn btn-primary">Run Query</button>
                    <a href="/admin/sql" class="btn btn-ghost">Clear</a>
                </div>
            </div>
        </form>

        <?php if ($error): ?>
        <div class="form-card" style="margin-top:1rem;border-color:rgba(239,68,68,0.4)">
            <div class="form-section">
                <h3 class="form-section-title" style="color:#f87171">Error</h3>
                <pre class="mono" style="white-space:pre-wrap;color:#fca5a5;font-size:0.85rem;margin:0;background:rgba(239,68,68,0.06);padding:0.75rem;border-radius:6px;border:1px solid rgba(239,68,68,0.2)"><?= e($error) ?></pre>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($results !== null && !$error): ?>
        <div class="form-card" style="margin-top:1rem">
            <div class="form-section" style="padding-bottom:0.5rem">
                <h3 class="form-section-title" style="display:flex;align-items:center;justify-content:space-between">
                    <span>Results</span>
                    <span class="text-muted" style="font-size:0.75rem;font-weight:400">
                        <?php if ($queryType === 'SELECT'): ?>
                            <?= count($results) ?> row<?= count($results) !== 1 ? 's' : '' ?>
                        <?php else: ?>
                            <?= (int)$rowsAffected ?> row<?= $rowsAffected !== 1 ? 's' : '' ?> affected
                        <?php endif; ?>
                        <?php if ($execTime !== null): ?> &middot; <?= number_format($execTime * 1000, 2) ?>&nbsp;ms<?php endif; ?>
                    </span>
                </h3>
            </div>
            <?php if ($queryType === 'SELECT' && empty($results)): ?>
            <div style="padding:1rem 1.25rem"><p class="text-muted" style="margin:0">Query returned no rows.</p></div>
            <?php elseif ($queryType === 'SELECT'): ?>
            <div class="data-table-wrap" style="border-radius:0;border-left:none;border-right:none;border-bottom:none;max-height:500px;overflow:auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <?php foreach ($columns as $col): ?>
                            <th><?= e($col) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                        <tr>
                            <?php foreach ($columns as $col): ?>
                            <td class="mono" style="font-size:0.8rem;max-width:340px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e((string)($row[$col] ?? '')) ?>">
                                <?php $v = $row[$col] ?? null; ?>
                                <?php if ($v === null): ?>
                                <span class="text-muted" style="font-style:italic">NULL</span>
                                <?php else: ?>
                                <?= e((string)$v) ?>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div style="padding:1rem 1.25rem"><p class="text-muted" style="margin:0">Statement executed successfully.</p></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <aside>
        <div class="form-card" style="margin:0;position:sticky;top:1rem">
            <div class="form-section">
                <h3 class="form-section-title">Schema</h3>
                <p class="text-muted" style="font-size:0.75rem;margin:-0.25rem 0 0.5rem">Click a table to inspect its columns.</p>
                <div style="display:flex;flex-direction:column;gap:0.25rem">
                    <?php foreach ($tables as $t): ?>
                    <button type="button" class="sql-table-btn" data-query="SELECT * FROM <?= e($t['name']) ?> LIMIT 50;" title="<?= e($t['columns']) ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/></svg>
                        <span class="mono"><?= e($t['name']) ?></span>
                        <span class="text-muted" style="margin-left:auto;font-size:0.7rem"><?= (int)$t['count'] ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </aside>
</div>

<style>
.sql-table-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.45rem 0.6rem;
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: 6px;
    color: var(--text);
    cursor: pointer;
    font-size: 0.82rem;
    text-align: left;
    transition: all 0.12s;
}
.sql-table-btn:hover {
    border-color: var(--accent);
    color: var(--accent);
}
kbd {
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: 3px;
    padding: 0.05rem 0.35rem;
    font-family: var(--font-mono, monospace);
    font-size: 0.7rem;
}
@media (max-width: 900px) {
    div[style*="grid-template-columns:minmax(0,1fr) 240px"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
(function () {
    const ta = document.querySelector('textarea[name="query"]');
    if (!ta) return;
    ta.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            ta.form.submit();
        }
    });
    document.querySelectorAll('.sql-table-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            ta.value = btn.dataset.query;
            ta.focus();
        });
    });
})();
</script>
