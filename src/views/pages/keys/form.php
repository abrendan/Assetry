<?php $k = $key ?? []; $cover = $keyCover ?? null; $acts = $activations ?? []; $assets = $availableAssets ?? []; ?>
<div class="form-page">
    <div class="form-card">
        <form method="POST" action="<?= $editing ? '/keys/'.$k['id'].'/edit' : '/keys/new' ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

            <div class="form-section">
                <h3 class="form-section-title">Product Information</h3>
                <div class="form-grid">
                    <div class="form-group form-col-2">
                        <label class="form-label">Product Name <span class="required">*</span></label>
                        <input type="text" name="product_name" class="form-input" value="<?= e($k['product_name'] ?? '') ?>" required placeholder="e.g. Windows 11 Pro">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <?php foreach (getCategoriesFor('key') as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= ($k['category'] ?? 'software') === $cat ? 'selected' : '' ?>><?= e(ucfirst($cat)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted" style="font-size:0.75rem"><a href="/categories">Manage categories</a></small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Platform</label>
                        <input type="text" name="platform" class="form-input" value="<?= e($k['platform'] ?? '') ?>" placeholder="e.g. Windows, Mac, Cross-platform">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Manufacturer</label>
                        <input type="text" name="manufacturer" class="form-input" value="<?= e($k['manufacturer'] ?? '') ?>" placeholder="e.g. Microsoft, Adobe">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Licensed To (Name)</label>
                        <input type="text" name="licensed_to_name" class="form-input" value="<?= e($k['licensed_to_name'] ?? '') ?>" placeholder="Full name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Licensed To (Email)</label>
                        <input type="email" name="licensed_to_email" class="form-input" value="<?= e($k['licensed_to_email'] ?? '') ?>" placeholder="email@example.com">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">License Key</h3>
                <div class="form-group">
                    <label class="form-label">Key / Serial <span class="required">*</span></label>
                    <div class="input-with-action">
                        <input type="password" name="key_value" id="key-input" class="form-input mono" value="<?= e($k['key_value'] ?? '') ?>" required placeholder="XXXXX-XXXXX-XXXXX-XXXXX-XXXXX">
                        <button type="button" class="input-action-btn" onclick="toggleKeyInput()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Max Activations</label>
                        <input type="number" name="max_activations" class="form-input" value="<?= e($k['max_activations'] ?? '') ?>" min="1" placeholder="Leave blank if unlimited">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Used Activations</label>
                        <input type="number" class="form-input" value="<?= (int)($k['used_activations'] ?? 0) ?>" disabled readonly>
                        <small class="text-muted" style="font-size:0.75rem">Auto-calculated from logged activations below.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-input" value="<?= e($k['purchase_date'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-input" value="<?= e($k['expiry_date'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Cover Image</h3>
                <?php if ($cover): ?>
                <div style="margin-bottom:1rem">
                    <img src="/uploads/<?= e($cover['filename']) ?>" alt="" style="max-width:200px;max-height:200px;border-radius:8px;border:1px solid rgba(255,255,255,0.1)">
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label class="form-label"><?= $cover ? 'Replace cover image' : 'Upload cover image' ?></label>
                    <input type="file" name="cover_image" accept="image/jpeg,image/png,image/gif,image/webp" class="form-input">
                    <small class="text-muted" style="font-size:0.75rem">JPEG, PNG, GIF or WebP. Max 8MB. Stored locally on the server.</small>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Notes</h3>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-textarea" rows="3" placeholder="Additional notes..."><?= e($k['notes'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <a href="/keys" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary"><?= $editing ? 'Save Changes' : 'Add Key' ?></button>
            </div>
        </form>

        <?php if ($editing): ?>
        <div class="form-section" style="margin-top:2rem;border-top:1px solid rgba(255,255,255,0.08);padding-top:1.5rem">
            <h3 class="form-section-title">Logged Activations
                <?php if (!empty($k['max_activations'])): ?>
                    <span style="font-weight:normal;font-size:0.8rem;color:var(--text-muted,#9ca3af);margin-left:0.5rem">
                        (<?= count($acts) ?> / <?= (int)$k['max_activations'] ?>)
                    </span>
                <?php else: ?>
                    <span style="font-weight:normal;font-size:0.8rem;color:var(--text-muted,#9ca3af);margin-left:0.5rem">
                        (<?= count($acts) ?>)
                    </span>
                <?php endif; ?>
            </h3>

            <?php if (empty($acts)): ?>
                <p style="color:var(--text-muted,#9ca3af);font-size:0.9rem;margin:0 0 1rem">No activations logged yet.</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:0.5rem;margin-bottom:1.25rem">
                    <?php foreach ($acts as $act): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:0.75rem 1rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:8px">
                        <div style="flex:1;min-width:0">
                            <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.9rem;font-weight:500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#10b981;flex-shrink:0"><polyline points="20 6 9 17 4 12"/></svg>
                                <?php if (!empty($act['asset_id']) && !empty($act['asset_name'])): ?>
                                    <a href="/assets/<?= (int)$act['asset_id'] ?>" style="color:var(--text,#e5e7eb);text-decoration:none">
                                        <?= e($act['asset_name']) ?><?= $act['asset_tag'] ? ' <span style="color:var(--text-muted,#9ca3af);font-weight:normal">('.e($act['asset_tag']).')</span>' : '' ?>
                                    </a>
                                <?php elseif (!empty($act['asset_label'])): ?>
                                    <span><?= e($act['asset_label']) ?></span>
                                    <span style="font-size:0.7rem;color:var(--text-muted,#9ca3af);font-weight:normal">(unlinked)</span>
                                <?php else: ?>
                                    <span style="color:var(--text-muted,#9ca3af);font-style:italic">Asset removed</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($act['notes'])): ?>
                                <div style="font-size:0.8rem;color:var(--text-muted,#9ca3af);margin-top:0.25rem;margin-left:1.25rem"><?= e($act['notes']) ?></div>
                            <?php endif; ?>
                            <div style="font-size:0.7rem;color:var(--text-muted,#9ca3af);margin-top:0.25rem;margin-left:1.25rem">
                                Logged by <?= e($act['logged_by_username'] ?? 'unknown') ?> on <?= e(date('M j, Y g:i A', strtotime($act['created_at']))) ?>
                            </div>
                        </div>
                        <form method="POST" action="/keys/<?= (int)$k['id'] ?>/activations/<?= (int)$act['id'] ?>/delete" onsubmit="return confirm('Remove this activation?')" style="margin:0">
                            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                            <button type="submit" class="icon-btn" title="Remove activation" style="color:#ef4444">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php
            $atMax = !empty($k['max_activations']) && count($acts) >= (int)$k['max_activations'];
            ?>
            <?php if ($atMax): ?>
                <p style="color:#f59e0b;font-size:0.85rem;margin:0">Maximum activations reached. Remove one above to log a new device.</p>
            <?php else: ?>
                <form method="POST" action="/keys/<?= (int)$k['id'] ?>/activations/add" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:8px;padding:1rem">
                    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                    <h4 style="margin:0 0 0.75rem;font-size:0.85rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted,#9ca3af)">Log a new activation</h4>
                    <div class="form-grid">
                        <div class="form-group form-col-2">
                            <label class="form-label">Activated on (asset)</label>
                            <select name="asset_id" class="form-select">
                                <option value="">— Choose an asset —</option>
                                <?php foreach ($assets as $a): ?>
                                <option value="<?= (int)$a['id'] ?>"><?= e($a['name']) ?><?= $a['asset_tag'] ? ' ('.e($a['asset_tag']).')' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group form-col-2">
                            <label class="form-label">Or device label (free text)</label>
                            <input type="text" name="asset_label" class="form-input" placeholder="e.g. John's MacBook, Server-01">
                            <small class="text-muted" style="font-size:0.75rem">Use this if the device isn't tracked as an asset.</small>
                        </div>
                        <div class="form-group form-col-2">
                            <label class="form-label">Notes (optional)</label>
                            <input type="text" name="notes" class="form-input" placeholder="Activation context, who installed it, etc.">
                        </div>
                    </div>
                    <div style="margin-top:0.75rem;text-align:right">
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Log Activation
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<script>
function toggleKeyInput() {
    const input = document.getElementById('key-input');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
