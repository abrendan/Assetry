<?php $v = $item ?? []; $images = $images ?? []; ?>
<div class="form-page">
    <div class="form-card">
        <form method="POST" action="<?= $editing ? '/vault/'.$v['id'].'/edit' : '/vault/new' ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

            <div class="form-section">
                <h3 class="form-section-title">Vault Item</h3>
                <div class="form-grid">
                    <div class="form-group form-col-2">
                        <label class="form-label">Title <span class="required">*</span></label>
                        <input type="text" name="title" class="form-input" value="<?= e($v['title'] ?? '') ?>" required placeholder="e.g. Server SSH Keys, Router Photo, License Cert">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="item_type" class="form-select">
                            <option value="note" <?= ($v['item_type'] ?? 'note') === 'note' ? 'selected' : '' ?>>Note</option>
                            <option value="credential" <?= ($v['item_type'] ?? '') === 'credential' ? 'selected' : '' ?>>Credential</option>
                            <option value="config" <?= ($v['item_type'] ?? '') === 'config' ? 'selected' : '' ?>>Config</option>
                            <option value="document" <?= ($v['item_type'] ?? '') === 'document' ? 'selected' : '' ?>>Document</option>
                            <option value="other" <?= ($v['item_type'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group form-col-2">
                        <label class="form-label">Tags</label>
                        <input type="text" name="tags" class="form-input" value="<?= e($v['tags'] ?? '') ?>" placeholder="server, ssh, production (comma-separated)">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Content</h3>
                <div class="form-group">
                    <textarea name="content" class="form-textarea vault-textarea" rows="10" placeholder="Optional — passwords, configs, notes, keys, anything you want to keep private..."><?= e($v['content'] ?? '') ?></textarea>
                    <small class="text-muted" style="font-size:0.75rem">Optional if you upload at least one image.</small>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Images</h3>
                <?php if (!empty($images)): ?>
                <div style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-bottom:1rem">
                    <?php foreach ($images as $img): ?>
                    <div style="position:relative;width:120px;height:120px;border-radius:8px;overflow:hidden;border:1px solid rgba(255,255,255,0.1)<?= $img['is_cover'] ? ';outline:2px solid #6366f1' : '' ?>">
                        <img src="/uploads/<?= e($img['filename']) ?>" alt="" style="width:100%;height:100%;object-fit:cover">
                        <?php if ($img['is_cover']): ?>
                        <span style="position:absolute;top:4px;left:4px;background:#6366f1;color:#fff;padding:2px 6px;border-radius:4px;font-size:0.65rem">COVER</span>
                        <?php endif; ?>
                        <div style="position:absolute;bottom:4px;right:4px;display:flex;gap:4px">
                            <?php if (!$img['is_cover']): ?>
                            <form method="POST" action="/vault/<?= $v['id'] ?>/images/<?= $img['id'] ?>/cover">
                                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                <button type="submit" class="icon-btn" title="Set as cover" style="background:rgba(0,0,0,0.6);color:#fff;border:none;padding:3px 6px;border-radius:4px;font-size:0.65rem;cursor:pointer">★</button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="/vault/<?= $v['id'] ?>/images/<?= $img['id'] ?>/delete" onsubmit="return confirm('Delete this image?')">
                                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                <button type="submit" class="icon-btn" title="Delete" style="background:rgba(220,38,38,0.85);color:#fff;border:none;padding:3px 6px;border-radius:4px;font-size:0.65rem;cursor:pointer">✕</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label class="form-label">Cover Image</label>
                    <input type="file" name="cover_image" accept="image/jpeg,image/png,image/gif,image/webp" class="form-input">
                    <small class="text-muted" style="font-size:0.75rem">JPEG, PNG, GIF or WebP. Max 8MB.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Additional Images</label>
                    <input type="file" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp" class="form-input" multiple>
                    <small class="text-muted" style="font-size:0.75rem">Upload multiple images at once.</small>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= $editing ? '/vault/'.$v['id'] : '/vault' ?>" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary"><?= $editing ? 'Save Changes' : 'Add to Vault' ?></button>
            </div>
        </form>
    </div>
</div>
