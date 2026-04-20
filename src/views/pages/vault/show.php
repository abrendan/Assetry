<div class="page-actions">
    <div class="breadcrumb">
        <a href="/vault">Vault</a>
        <span>/</span>
        <span><?= e($item['title']) ?></span>
    </div>
    <div style="display:flex;gap:0.5rem">
        <a href="/vault/<?= $item['id'] ?>/edit" class="btn btn-secondary">Edit</a>
        <form method="POST" action="/vault/<?= $item['id'] ?>/delete" onsubmit="return confirm('Delete this vault item permanently?')">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>

<div class="detail-layout">
    <div class="detail-main">
        <div class="detail-card">
            <div class="detail-header">
                <div class="vault-badge-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <div class="detail-title-wrap">
                    <h2 class="detail-title"><?= e($item['title']) ?></h2>
                    <div class="detail-meta">
                        <span class="cat-pill"><?= e(ucfirst($item['item_type'])) ?></span>
                        <span class="text-muted" style="font-size:0.8rem">Updated <?= formatDateTime($item['updated_at']) ?></span>
                    </div>
                </div>
            </div>

            <?php if (!empty($images)): ?>
            <div style="margin-bottom:1.5rem">
                <?php $cover = null; foreach ($images as $i) { if ($i['is_cover']) { $cover = $i; break; } } if (!$cover) $cover = $images[0]; ?>
                <a href="/uploads/<?= e($cover['filename']) ?>" target="_blank">
                    <img src="/uploads/<?= e($cover['filename']) ?>" alt="" style="width:100%;max-height:420px;object-fit:contain;border-radius:8px;background:rgba(0,0,0,0.3)">
                </a>
                <?php if (count($images) > 1): ?>
                <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:0.75rem">
                    <?php foreach ($images as $img): if ($img['id'] === $cover['id']) continue; ?>
                    <a href="/uploads/<?= e($img['filename']) ?>" target="_blank" style="display:block;width:80px;height:80px;border-radius:6px;overflow:hidden;border:1px solid rgba(255,255,255,0.1)">
                        <img src="/uploads/<?= e($img['filename']) ?>" alt="" style="width:100%;height:100%;object-fit:cover">
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($item['tags']): ?>
            <div style="margin-bottom:1.5rem">
                <?php foreach (explode(',', $item['tags']) as $tag): ?>
                <span class="tag"><?= e(trim($tag)) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty(trim((string)$item['content']))): ?>
            <div class="vault-content-wrap">
                <div class="vault-content-header">
                    <span class="detail-label">Content</span>
                    <button type="button" class="btn-ghost btn-sm" onclick="copyVaultContent()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        Copy
                    </button>
                </div>
                <pre id="vault-content" class="vault-content-pre"><?= e($item['content']) ?></pre>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
function copyVaultContent() {
    const text = document.getElementById('vault-content').textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.currentTarget;
        btn.textContent = 'Copied!';
        setTimeout(() => btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> Copy', 2000);
    });
}
</script>
