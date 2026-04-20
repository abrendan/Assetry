<div class="form-page">
    <div class="form-card">
        <div class="form-section">
            <h3 class="form-section-title">Asset Types</h3>
            <p class="text-muted" style="font-size:0.9rem;margin-bottom:1rem">
                Create custom types (like <em>Vibrator</em>, <em>Dildo</em>, <em>Laptop</em>, <em>Camera</em>) and define your own fields for each (e.g. Insertable Length, Storage GB, Sensor Size). When you set an asset's <strong>Type</strong> to a defined name, those fields will appear on the asset form.
            </p>
            <form method="POST" action="/types/add" style="display:flex;gap:0.5rem;align-items:end;margin-bottom:1.5rem">
                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                <div class="form-group" style="margin:0;flex:1">
                    <label class="form-label">New Type Name</label>
                    <input type="text" name="name" class="form-input" placeholder="e.g. Vibrator" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Type</button>
            </form>

            <?php if (empty($types)): ?>
            <p class="text-muted" style="font-size:0.9rem">No custom types yet. Add one above, or just import a CSV from <a href="/data">Import / Export</a> — types and fields are auto-created from Snipe-IT custom reports.</p>
            <?php else: ?>
            <?php foreach ($types as $t): ?>
            <div style="border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:1rem;margin-bottom:1rem">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem">
                    <h4 style="margin:0"><?= e($t['name']) ?></h4>
                    <form method="POST" action="/types/<?= $t['id'] ?>/delete" onsubmit="return confirm('Delete this type? Custom field values for assets of this type will become orphaned.')">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        <button type="submit" class="btn btn-sm btn-ghost" style="color:#fca5a5">Delete type</button>
                    </form>
                </div>

                <?php if (empty($t['fields'])): ?>
                <p class="text-muted" style="font-size:0.85rem;margin:0 0 0.75rem">No custom fields yet.</p>
                <?php else: ?>
                <table style="width:100%;font-size:0.85rem;margin-bottom:0.75rem">
                    <thead>
                        <tr style="text-align:left;color:var(--text-muted,#9ca3af);border-bottom:1px solid rgba(255,255,255,0.06)">
                            <th style="padding:0.4rem 0.5rem;font-weight:500">Label</th>
                            <th style="padding:0.4rem 0.5rem;font-weight:500">Key</th>
                            <th style="padding:0.4rem 0.5rem;font-weight:500">Type</th>
                            <th style="width:1%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($t['fields'] as $f): ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.04)">
                            <td style="padding:0.4rem 0.5rem"><?= e($f['field_label']) ?></td>
                            <td style="padding:0.4rem 0.5rem;font-family:monospace;color:var(--text-muted,#9ca3af)"><?= e($f['field_key']) ?></td>
                            <td style="padding:0.4rem 0.5rem"><?= e($f['field_type']) ?></td>
                            <td style="padding:0.4rem 0.5rem">
                                <form method="POST" action="/types/<?= $t['id'] ?>/fields/<?= $f['id'] ?>/delete" onsubmit="return confirm('Delete this field? Existing values will be removed.')">
                                    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                    <button type="submit" class="btn btn-sm btn-ghost" style="color:#fca5a5">×</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <form method="POST" action="/types/<?= $t['id'] ?>/fields/add" style="display:flex;gap:0.5rem;align-items:end">
                    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                    <div class="form-group" style="margin:0;flex:1">
                        <input type="text" name="field_label" class="form-input" placeholder="Field label (e.g. Insertable Length cm)" required>
                    </div>
                    <div class="form-group" style="margin:0">
                        <select name="field_type" class="form-select">
                            <?php foreach (getCustomFieldTypes() as $ft): ?>
                            <option value="<?= $ft ?>"><?= ucfirst($ft) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary">Add Field</button>
                </form>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
