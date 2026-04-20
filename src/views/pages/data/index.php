<div class="form-page">
    <div class="form-card">
        <div class="form-section">
            <h3 class="form-section-title">Export Data</h3>
            <p class="text-muted" style="font-size:0.9rem;margin-bottom:1rem">Download your data as CSV files. Files include all columns and can be re-imported into Assetry or used as a backup.</p>
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem">
                <a href="/data/export/assets" class="btn btn-secondary">Export Assets</a>
                <a href="/data/export/keys" class="btn btn-secondary">Export Product Keys</a>
                <a href="/data/export/licenses" class="btn btn-secondary">Export Licenses</a>
                <a href="/data/export/network" class="btn btn-secondary">Export Network Devices</a>
            </div>
        </div>

        <form method="POST" action="/data/import" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
            <div class="form-section">
                <h3 class="form-section-title">Import Data</h3>
                <p class="text-muted" style="font-size:0.9rem;margin-bottom:1rem">
                    Upload a CSV file. The format is auto-detected. Supported sources:
                </p>
                <ul style="font-size:0.85rem;color:var(--text-muted,#9ca3af);margin:0 0 1rem 1.25rem;line-height:1.6">
                    <li><strong>Snipe-IT exports</strong> — assets, accessories, and licenses CSVs work directly.</li>
                    <li><strong>Assetry exports</strong> — files exported above can be re-imported as-is.</li>
                </ul>

                <div class="form-grid">
                    <div class="form-group form-col-2">
                        <label class="form-label">CSV File <span class="required">*</span></label>
                        <input type="file" name="csv_file" accept=".csv,text/csv" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">File type</label>
                        <select name="type" class="form-select">
                            <option value="auto">Auto-detect</option>
                            <option value="assets">Assets</option>
                            <option value="accessories">Accessories (→ assets)</option>
                            <option value="keys">Product Keys / Licenses</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Default category (assets only)</label>
                        <select name="default_category" class="form-select">
                            <?php foreach (getCategoriesFor('asset') as $cat): ?>
                            <option value="<?= e($cat) ?>"><?= e(ucfirst($cat)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted" style="font-size:0.75rem">Used when the CSV doesn't specify a category.</small>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Field Mapping Reference</h3>
                <div style="font-size:0.8rem;color:var(--text-muted,#9ca3af);line-height:1.6">
                    <p style="margin:0 0 0.5rem"><strong>Snipe-IT licenses CSV →</strong> <em>Product Keys</em>: Name → Product Name; Product Key → Key; Total/Avail → Max/Used Activations; Manufacturer, Licensed to Email/Name, Expiration Date all preserved.</p>
                    <p style="margin:0 0 0.5rem"><strong>Snipe-IT accessories CSV →</strong> <em>Assets (category: accessory)</em>: Name, Accessory Category → Type, Model No. → Model, Location, Total → Quantity, Purchase Cost → Purchase Price.</p>
                    <p style="margin:0"><strong>Snipe-IT assets CSV →</strong> <em>Assets</em>: Asset Name, Asset Tag, Model, Manufacturer, Purchase Date.</p>
                </div>
            </div>

            <div class="form-actions">
                <a href="/" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Import CSV</button>
            </div>
        </form>
    </div>
</div>
