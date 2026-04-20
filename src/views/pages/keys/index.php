<div class="page-actions">
    <form method="GET" action="/keys" class="search-bar">
        <div class="search-input-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" class="form-input search-input" placeholder="Search keys..." value="<?= e($_GET['search'] ?? '') ?>">
        </div>
        <select name="category" class="form-select">
            <option value="">All Types</option>
            <option value="software" <?= ($_GET['category'] ?? '') === 'software' ? 'selected' : '' ?>>Software</option>
            <option value="os" <?= ($_GET['category'] ?? '') === 'os' ? 'selected' : '' ?>>Operating System</option>
            <option value="game" <?= ($_GET['category'] ?? '') === 'game' ? 'selected' : '' ?>>Game</option>
            <option value="other" <?= ($_GET['category'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if (!empty($_GET['search']) || !empty($_GET['category'])): ?>
        <a href="/keys" class="btn btn-ghost">Clear</a>
        <?php endif; ?>
    </form>
    <a href="/keys/new" class="btn btn-primary">+ Add Key</a>
</div>

<div class="security-notice">
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    Product keys are stored securely. Click the eye icon to reveal a key.
</div>

<?php if (empty($keys)): ?>
<div class="empty-state-full">
    <div class="empty-icon-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
    </div>
    <h3>No product keys yet</h3>
    <p>Store and manage your software license keys securely.</p>
    <a href="/keys/new" class="btn btn-primary">Add First Key</a>
</div>
<?php else: ?>
<div class="keys-grid">
    <?php foreach ($keys as $key): ?>
    <div class="key-card">
        <div class="key-card-header">
            <div class="key-card-title">
                <span class="key-name"><?= e($key['product_name']) ?></span>
            </div>
            <div class="key-actions">
                <a href="/keys/<?= $key['id'] ?>/edit" class="icon-btn" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </a>
                <form method="POST" action="/keys/<?= $key['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this key?')">
                    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                    <button type="submit" class="icon-btn icon-btn-danger" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                </form>
            </div>
        </div>
        <div class="key-meta">
            <?php if ($key['platform']): ?><span class="tag"><?= e($key['platform']) ?></span><?php endif; ?>
            <span class="tag"><?= e(ucfirst($key['category'])) ?></span>
            <?php if ($key['expiry_date']): ?>
            <?php $days = daysUntil($key['expiry_date']); ?>
            <span class="tag <?= $days < 0 ? 'tag-danger' : ($days < 30 ? 'tag-warning' : '') ?>">Exp: <?= formatDate($key['expiry_date']) ?></span>
            <?php endif; ?>
        </div>
        <div class="key-value-row">
            <code class="key-value" id="key-<?= $key['id'] ?>" data-key="<?= e($key['key_value']) ?>">••••••••••••••••••••••••••••••</code>
            <button type="button" class="icon-btn reveal-btn" onclick="toggleKey(<?= $key['id'] ?>)" title="Reveal key">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
            <button type="button" class="icon-btn copy-btn" onclick="copyKey(<?= $key['id'] ?>)" title="Copy key">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            </button>
        </div>
        <?php if ($key['max_activations']): ?>
        <div class="key-activations">
            <span><?= $key['used_activations'] ?> / <?= $key['max_activations'] ?> activations used</span>
            <div class="activation-bar">
                <div class="activation-fill" style="width:<?= min(100, round(($key['used_activations']/$key['max_activations'])*100)) ?>%"></div>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($key['notes']): ?>
        <p class="key-notes"><?= e($key['notes']) ?></p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<div class="table-footer">
    <span class="text-muted"><?= count($keys) ?> key<?= count($keys) !== 1 ? 's' : '' ?></span>
</div>
<?php endif; ?>
