<?php $l = $license ?? []; ?>
<div class="form-page">
    <div class="form-card">
        <form method="POST" action="<?= $editing ? '/licenses/'.$l['id'].'/edit' : '/licenses/new' ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

            <div class="form-section">
                <h3 class="form-section-title">License Details</h3>
                <div class="form-grid">
                    <div class="form-group form-col-2">
                        <label class="form-label">Software Name <span class="required">*</span></label>
                        <input type="text" name="software_name" class="form-input" value="<?= e($l['software_name'] ?? '') ?>" required placeholder="e.g. Adobe Creative Cloud">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Vendor</label>
                        <input type="text" name="vendor" class="form-input" value="<?= e($l['vendor'] ?? '') ?>" placeholder="e.g. Adobe Inc.">
                    </div>
                    <div class="form-group">
                        <label class="form-label">License Type</label>
                        <select name="license_type" class="form-select">
                            <?php foreach (getLicenseTypes() as $lt): ?>
                            <option value="<?= $lt ?>" <?= ($l['license_type'] ?? '') === $lt ? 'selected' : '' ?>><?= ucfirst($lt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Seats & Validity</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Total Seats</label>
                        <input type="number" name="seats" class="form-input" value="<?= e($l['seats'] ?? '1') ?>" min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Seats Used</label>
                        <input type="number" name="seats_used" class="form-input" value="<?= e($l['seats_used'] ?? '0') ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-input" value="<?= e($l['start_date'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-input" value="<?= e($l['expiry_date'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Cost</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">License Cost</label>
                        <div class="input-prefix-wrap">
                            <span class="input-prefix">$</span>
                            <input type="number" name="cost" class="form-input with-prefix" step="0.01" min="0" value="<?= e($l['cost'] ?? '') ?>" placeholder="0.00">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Renewal Cost</label>
                        <div class="input-prefix-wrap">
                            <span class="input-prefix">$</span>
                            <input type="number" name="renewal_cost" class="form-input with-prefix" step="0.01" min="0" value="<?= e($l['renewal_cost'] ?? '') ?>" placeholder="0.00">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Notes</h3>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-textarea" rows="3" placeholder="License terms, support contact, etc."><?= e($l['notes'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <a href="/licenses" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary"><?= $editing ? 'Save Changes' : 'Add License' ?></button>
            </div>
        </form>
    </div>
</div>
