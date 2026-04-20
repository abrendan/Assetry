<div class="vault-notice">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    <span>Your private vault — items here are only visible to you</span>
</div>

<div class="page-actions">
    <form method="GET" action="/vault" class="search-bar">
        <div class="search-input-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" class="form-input search-input" placeholder="Search vault..." value="<?= e($_GET['search'] ?? '') ?>">
        </div>
        <select name="type" class="form-select">
            <option value="">All Types</option>
            <option value="note" <?= ($_GET['type'] ?? '') === 'note' ? 'selected' : '' ?>>Notes</option>
            <option value="credential" <?= ($_GET['type'] ?? '') === 'credential' ? 'selected' : '' ?>>Credentials</option>
            <option value="config" <?= ($_GET['type'] ?? '') === 'config' ? 'selected' : '' ?>>Configs</option>
            <option value="document" <?= ($_GET['type'] ?? '') === 'document' ? 'selected' : '' ?>>Documents</option>
            <option value="other" <?= ($_GET['type'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if (!empty($_GET['search']) || !empty($_GET['type'])): ?>
        <a href="/vault" class="btn btn-ghost">Clear</a>
        <?php endif; ?>
    </form>
    <a href="/vault/new" class="btn btn-primary">+ Add Item</a>
</div>

<?php if (empty($items)): ?>
<div class="empty-state-full">
    <div class="empty-icon-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    </div>
    <h3>Your vault is empty</h3>
    <p>Store private notes, credentials, configs, and sensitive IT documents.</p>
    <a href="/vault/new" class="btn btn-primary">Add First Item</a>
</div>
<?php else: ?>
<div class="vault-grid">
    <?php
    $typeIcons = [
        'note' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
        'credential' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>',
        'config' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
        'document' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
        'other' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
    ];
    foreach ($items as $item):
        $icon = $typeIcons[$item['item_type']] ?? $typeIcons['other'];
        $tags = $item['tags'] ? explode(',', $item['tags']) : [];
    ?>
    <div class="vault-card">
        <?php if (!empty($item['cover_image'])): ?>
        <a href="/vault/<?= $item['id'] ?>" style="display:block;width:100%;aspect-ratio:16/9;border-radius:6px;overflow:hidden;margin-bottom:0.75rem;background:rgba(0,0,0,0.3)">
            <img src="/uploads/<?= e($item['cover_image']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;display:block">
        </a>
        <?php endif; ?>
        <div class="vault-card-header">
            <div class="vault-type-icon"><?= $icon ?></div>
            <div class="vault-card-title">
                <a href="/vault/<?= $item['id'] ?>" class="vault-name"><?= e($item['title']) ?></a>
                <span class="vault-type-label"><?= e(ucfirst($item['item_type'])) ?></span>
            </div>
            <div class="vault-card-actions">
                <a href="/vault/<?= $item['id'] ?>/edit" class="icon-btn" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </a>
                <form method="POST" action="/vault/<?= $item['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this vault item?')">
                    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                    <button type="submit" class="icon-btn icon-btn-danger" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                </form>
            </div>
        </div>
        <?php if (!empty($tags)): ?>
        <div class="vault-tags">
            <?php foreach ($tags as $tag): ?>
            <span class="tag"><?= e(trim($tag)) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="vault-date">Updated <?= formatDateTime($item['updated_at']) ?></div>
    </div>
    <?php endforeach; ?>
</div>
<div class="table-footer">
    <span class="text-muted"><?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?></span>
</div>
<?php endif; ?>
