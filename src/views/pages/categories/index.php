<?php $error = flash('error'); $success = flash('success'); ?>
<div class="page-actions">
    <span class="text-muted">Add custom categories for your assets and product keys</span>
</div>

<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

<div class="detail-layout" style="grid-template-columns: 1fr 1fr; display:grid; gap:1.5rem">
    <?php foreach (['asset' => 'Asset Categories', 'key' => 'Product Key Categories'] as $type => $label): ?>
    <div class="detail-card">
        <h3 style="margin-top:0"><?= e($label) ?></h3>

        <div style="margin-bottom:1rem">
            <span class="detail-label">Built-in</span>
            <div style="display:flex;flex-wrap:wrap;gap:0.4rem;margin-top:0.5rem">
                <?php foreach (builtInCategories($type) as $bi): ?>
                <span class="cat-pill"><?= e(ucfirst($bi)) ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <span class="detail-label">Your custom categories</span>
        <?php if (empty($grouped[$type])): ?>
        <p class="text-muted" style="margin:0.5rem 0 1rem;font-size:0.9rem">None yet.</p>
        <?php else: ?>
        <div style="display:flex;flex-wrap:wrap;gap:0.4rem;margin:0.5rem 0 1rem">
            <?php foreach ($grouped[$type] as $c): ?>
            <span class="cat-pill" style="display:inline-flex;align-items:center;gap:0.4rem">
                <?= e($c['name']) ?>
                <form method="POST" action="/categories/<?= $c['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Remove this category?')">
                    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                    <button type="submit" style="background:none;border:none;color:#ef4444;cursor:pointer;padding:0;font-size:1rem;line-height:1">×</button>
                </form>
            </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/categories/add" style="display:flex;gap:0.5rem;margin-top:1rem">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
            <input type="hidden" name="entity_type" value="<?= $type ?>">
            <input type="text" name="name" class="form-input" placeholder="New category name" required maxlength="50">
            <button type="submit" class="btn btn-primary">Add</button>
        </form>
    </div>
    <?php endforeach; ?>
</div>
