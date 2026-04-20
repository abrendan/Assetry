<?php
$a = $asset ?? [];
$images = $assetImages ?? [];
$_user = currentUser();
$_allTypes = getAssetTypesForUser((int)$_user['id']);
$_currentType = findAssetTypeByName($a['type'] ?? null);
$_typeFields = $_currentType ? getFieldsForType((int)$_currentType['id']) : [];
$_customValues = ($editing && !empty($a['id'])) ? getCustomValuesForAsset((int)$a['id']) : [];
?>
<div class="form-page">
    <div class="form-card">
        <form method="POST" action="<?= $editing ? '/assets/'.$a['id'].'/edit' : '/assets/new' ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

            <div class="form-section">
                <h3 class="form-section-title">Basic Information</h3>
                <div class="form-grid">
                    <div class="form-group form-col-2">
                        <label class="form-label">Asset Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-input" value="<?= e($a['name'] ?? '') ?>" required placeholder="e.g. MacBook Pro 16 M3">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="">Select category...</option>
                            <?php foreach (getCategoriesFor('asset') as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= ($a['category'] ?? '') === $cat ? 'selected' : '' ?>><?= e(ucfirst($cat)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted" style="font-size:0.75rem"><a href="/categories">Manage categories</a></small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type <span class="required">*</span></label>
                        <select name="type" id="asset-type-select" class="form-select" data-current-type-id="<?= $_currentType ? (int)$_currentType['id'] : '' ?>" data-asset-id="<?= $editing && !empty($a['id']) ? (int)$a['id'] : '' ?>">
                            <option value="">Select type...</option>
                            <?php foreach ($_allTypes as $tt): ?>
                            <option value="<?= e($tt['name']) ?>" data-type-id="<?= (int)$tt['id'] ?>" <?= ($_currentType && (int)$_currentType['id'] === (int)$tt['id']) ? 'selected' : '' ?>><?= e($tt['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted" style="font-size:0.75rem"><a href="/types" target="_blank">Manage types &amp; custom fields</a></small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (getAssetStatuses() as $st): ?>
                            <option value="<?= $st ?>" <?= ($a['status'] ?? 'active') === $st ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$st)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Device Details</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Asset Tag</label>
                        <input type="text" name="asset_tag" class="form-input mono" value="<?= e($a['asset_tag'] ?? '') ?>" placeholder="e.g. AB00001">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-input" min="1" step="1" value="<?= e($a['quantity'] ?? 1) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Manufacturer</label>
                        <input type="text" name="manufacturer" class="form-input" value="<?= e($a['manufacturer'] ?? '') ?>" placeholder="e.g. Apple, Dell, HP">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" class="form-input" value="<?= e($a['model'] ?? '') ?>" placeholder="e.g. MacBook Pro 16 (2024)">
                    </div>
                    <div class="form-group form-col-2">
                        <label class="form-label">Serial Number</label>
                        <input type="text" name="serial_number" class="form-input mono" value="<?= e($a['serial_number'] ?? '') ?>" placeholder="Serial number">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-input" value="<?= e($a['location'] ?? '') ?>" placeholder="e.g. Office A, Rack 3">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Assigned To</label>
                        <input type="text" name="assigned_to" class="form-input" value="<?= e($a['assigned_to'] ?? '') ?>" placeholder="Name or department">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Purchase & Warranty</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-input" value="<?= e($a['purchase_date'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Purchase Price</label>
                        <div class="input-prefix-wrap">
                            <span class="input-prefix">$</span>
                            <input type="number" name="purchase_price" class="form-input with-prefix" step="0.01" min="0" value="<?= e($a['purchase_price'] ?? '') ?>" placeholder="0.00">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Warranty Expiry</label>
                        <input type="date" name="warranty_expiry" class="form-input" value="<?= e($a['warranty_expiry'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div id="custom-fields-container">
                <?php if (!empty($_typeFields)): ?>
                <div class="form-section">
                    <h3 class="form-section-title">Custom Fields — <span data-type-name><?= e($_currentType['name']) ?></span></h3>
                    <div class="form-grid">
                        <?php foreach ($_typeFields as $f): $val = $_customValues[(int)$f['id']] ?? ''; ?>
                        <div class="form-group<?= $f['field_type'] === 'textarea' ? ' form-col-2' : '' ?>">
                            <label class="form-label"><?= e($f['field_label']) ?></label>
                            <?php if ($f['field_type'] === 'textarea'): ?>
                                <textarea name="custom[<?= e($f['field_key']) ?>]" class="form-textarea" rows="3"><?= e($val) ?></textarea>
                            <?php elseif ($f['field_type'] === 'number'): ?>
                                <input type="number" step="any" name="custom[<?= e($f['field_key']) ?>]" class="form-input" value="<?= e($val) ?>">
                            <?php elseif ($f['field_type'] === 'date'): ?>
                                <input type="date" name="custom[<?= e($f['field_key']) ?>]" class="form-input" value="<?= e($val) ?>">
                            <?php elseif ($f['field_type'] === 'boolean'): ?>
                                <label class="checkbox-label"><input type="checkbox" name="custom[<?= e($f['field_key']) ?>]" value="1" class="checkbox" <?= $val ? 'checked' : '' ?>><span class="checkbox-text">Yes</span></label>
                            <?php else: ?>
                                <input type="text" name="custom[<?= e($f['field_key']) ?>]" class="form-input" value="<?= e($val) ?>">
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <script>
            (function() {
                var sel = document.getElementById('asset-type-select');
                if (!sel) return;
                var container = document.getElementById('custom-fields-container');
                var assetId = sel.dataset.assetId || '';
                function escapeHtml(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c];}); }
                function renderFields(typeName, fields) {
                    if (!fields || !fields.length) {
                        container.innerHTML = typeName
                            ? '<div class="form-section"><p class="text-muted" style="font-size:0.85rem;margin:0">No custom fields defined for type <strong>' + escapeHtml(typeName) + '</strong>. <a href="/types" target="_blank">Add some →</a></p></div>'
                            : '';
                        return;
                    }
                    var html = '<div class="form-section"><h3 class="form-section-title">Custom Fields — ' + escapeHtml(typeName) + '</h3><div class="form-grid">';
                    fields.forEach(function(f) {
                        var name = 'custom[' + escapeHtml(f.key) + ']';
                        var val = escapeHtml(f.value);
                        var col2 = f.type === 'textarea' ? ' form-col-2' : '';
                        html += '<div class="form-group' + col2 + '"><label class="form-label">' + escapeHtml(f.label) + '</label>';
                        if (f.type === 'textarea') html += '<textarea name="' + name + '" class="form-textarea" rows="3">' + val + '</textarea>';
                        else if (f.type === 'number') html += '<input type="number" step="any" name="' + name + '" class="form-input" value="' + val + '">';
                        else if (f.type === 'date') html += '<input type="date" name="' + name + '" class="form-input" value="' + val + '">';
                        else if (f.type === 'boolean') { var on = (f.value === '1' || f.value === 1 || f.value === true); html += '<label class="checkbox-label"><input type="checkbox" name="' + name + '" value="1" class="checkbox"' + (on ? ' checked' : '') + '><span class="checkbox-text">Yes</span></label>'; }
                        else html += '<input type="text" name="' + name + '" class="form-input" value="' + val + '">';
                        html += '</div>';
                    });
                    html += '</div></div>';
                    container.innerHTML = html;
                }
                sel.addEventListener('change', function() {
                    var opt = sel.options[sel.selectedIndex];
                    var typeId = opt ? opt.dataset.typeId : '';
                    if (!typeId) { container.innerHTML = ''; return; }
                    var url = '/types/' + typeId + '/fields.json' + (assetId ? '?asset_id=' + encodeURIComponent(assetId) : '');
                    fetch(url, { credentials: 'same-origin' })
                        .then(function(r) { return r.ok ? r.json() : Promise.reject(r.status); })
                        .then(function(data) { renderFields(data.type.name, data.fields); })
                        .catch(function() { container.innerHTML = '<div class="form-section"><p class="text-muted" style="font-size:0.85rem;margin:0">Could not load custom fields.</p></div>'; });
                });
            })();
            </script>

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
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($editing): ?>
                <p class="text-muted" style="font-size:0.85rem">Manage existing images on the asset detail page.</p>
                <?php endif; ?>
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

            <div class="form-section">
                <h3 class="form-section-title">Notes</h3>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-textarea" rows="3" placeholder="Any additional details..."><?= e($a['notes'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= $editing ? '/assets/'.$a['id'] : '/assets' ?>" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary"><?= $editing ? 'Save Changes' : 'Add Asset' ?></button>
            </div>
        </form>
    </div>
</div>
