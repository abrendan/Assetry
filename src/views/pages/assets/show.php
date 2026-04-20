<div class="page-actions">
    <div class="breadcrumb">
        <a href="/assets">Assets</a>
        <span>/</span>
        <span><?= e($asset['name']) ?></span>
    </div>
    <div style="display:flex;gap:0.5rem">
        <a href="/assets/<?= $asset['id'] ?>/edit" class="btn btn-secondary">Edit</a>
        <form method="POST" action="/assets/<?= $asset['id'] ?>/delete" onsubmit="return confirm('Delete this asset permanently?')">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>

<div class="detail-layout">
    <div class="detail-main">
        <div class="detail-card">
            <div class="detail-header">
                <div class="detail-icon"><?= categoryIcon($asset['category']) ?></div>
                <div class="detail-title-wrap">
                    <h2 class="detail-title"><?= e($asset['name']) ?></h2>
                    <div class="detail-meta">
                        <?= statusBadge($asset['status']) ?>
                        <span class="cat-pill"><?= e(ucfirst($asset['category'])) ?></span>
                    </div>
                </div>
            </div>

            <div class="detail-grid">
                <div class="detail-field">
                    <span class="detail-label">Type</span>
                    <span class="detail-value"><?= e($asset['type'] ?? '—') ?></span>
                </div>
                <div class="detail-field">
                    <span class="detail-label">Manufacturer</span>
                    <span class="detail-value"><?= e($asset['manufacturer'] ?? '—') ?></span>
                </div>
                <div class="detail-field">
                    <span class="detail-label">Model</span>
                    <span class="detail-value"><?= e($asset['model'] ?? '—') ?></span>
                </div>
                <div class="detail-field">
                    <span class="detail-label">Serial Number</span>
                    <span class="detail-value mono"><?= e($asset['serial_number'] ?? '—') ?></span>
                </div>
                <div class="detail-field">
                    <span class="detail-label">Location</span>
                    <span class="detail-value"><?= e($asset['location'] ?? '—') ?></span>
                </div>
                <div class="detail-field">
                    <span class="detail-label">Assigned To</span>
                    <span class="detail-value"><?= e($asset['assigned_to'] ?? '—') ?></span>
                </div>
                <div class="detail-field">
                    <span class="detail-label">Purchase Date</span>
                    <span class="detail-value"><?= formatDate($asset['purchase_date']) ?></span>
                </div>
                <div class="detail-field">
                    <span class="detail-label">Purchase Price</span>
                    <span class="detail-value"><?= $asset['purchase_price'] ? '$' . number_format($asset['purchase_price'], 2) : '—' ?></span>
                </div>
                <div class="detail-field">
                    <span class="detail-label">Warranty Expiry</span>
                    <span class="detail-value">
                        <?php if ($asset['warranty_expiry']): ?>
                        <?php $days = daysUntil($asset['warranty_expiry']); ?>
                        <span class="<?= $days < 0 ? 'text-danger' : ($days < 60 ? 'text-warning' : '') ?>">
                            <?= formatDate($asset['warranty_expiry']) ?>
                            <?php if ($days >= 0): ?><small>(<?= $days ?> days left)</small><?php elseif ($days < 0): ?><small>(expired)</small><?php endif; ?>
                        </span>
                        <?php else: ?>—<?php endif; ?>
                    </span>
                </div>
                <div class="detail-field">
                    <span class="detail-label">Added</span>
                    <span class="detail-value"><?= formatDateTime($asset['created_at']) ?></span>
                </div>
            </div>

            <?php if ($asset['notes']): ?>
            <div class="detail-notes">
                <span class="detail-label">Notes</span>
                <p><?= nl2br(e($asset['notes'])) ?></p>
            </div>
            <?php endif; ?>

            <?php
            $_user = currentUser();
            $_t = findAssetTypeByName($asset['type'] ?? null);
            $_fields = $_t ? getFieldsForType((int)$_t['id']) : [];
            $_vals = getCustomValuesForAsset((int)$asset['id']);
            $_hasAny = false;
            foreach ($_fields as $f) { if (isset($_vals[(int)$f['id']]) && $_vals[(int)$f['id']] !== '') { $_hasAny = true; break; } }
            ?>
            <?php if ($_hasAny): ?>
            <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid rgba(255,255,255,0.06)">
                <h4 style="margin:0 0 0.75rem;font-size:0.85rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted,#9ca3af)"><?= e($_t['name']) ?> details</h4>
                <div class="detail-grid">
                    <?php foreach ($_fields as $f): $v = $_vals[(int)$f['id']] ?? null; if ($v === null || $v === '') continue; ?>
                    <div class="detail-field">
                        <span class="detail-label"><?= e($f['field_label']) ?></span>
                        <span class="detail-value"><?= $f['field_type'] === 'boolean' ? ($v ? 'Yes' : 'No') : e($v) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($assetImages)): ?>
        <div class="detail-card" style="margin-top:1rem">
            <h3 style="margin-top:0">Images</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:0.75rem">
                <?php foreach ($assetImages as $img): ?>
                <div style="position:relative;aspect-ratio:1;border-radius:8px;overflow:hidden;border:1px solid rgba(255,255,255,0.1)<?= $img['is_cover'] ? ';outline:2px solid #6366f1' : '' ?>">
                    <a href="/uploads/<?= e($img['filename']) ?>" target="_blank">
                        <img src="/uploads/<?= e($img['filename']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;display:block">
                    </a>
                    <?php if ($img['is_cover']): ?>
                    <span style="position:absolute;top:6px;left:6px;background:#6366f1;color:#fff;padding:2px 6px;border-radius:4px;font-size:0.65rem;font-weight:600">COVER</span>
                    <?php endif; ?>
                    <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(to top, rgba(0,0,0,0.85), transparent);padding:0.5rem;display:flex;gap:0.4rem;justify-content:center">
                        <?php if (!$img['is_cover']): ?>
                        <form method="POST" action="/assets/<?= $asset['id'] ?>/images/<?= $img['id'] ?>/cover" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                            <button type="submit" class="btn btn-sm btn-ghost" style="background:rgba(255,255,255,0.1)">Set cover</button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" action="/assets/<?= $asset['id'] ?>/images/<?= $img['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this image?')">
                            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                            <button type="submit" class="btn btn-sm btn-ghost" style="background:rgba(239,68,68,0.2);color:#fca5a5">Delete</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
