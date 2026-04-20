<?php $d = $device ?? []; ?>
<div class="form-page">
    <div class="form-card">
        <form method="POST" action="<?= $editing ? '/network/'.$d['id'].'/edit' : '/network/new' ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

            <div class="form-section">
                <h3 class="form-section-title">Device Information</h3>
                <div class="form-grid">
                    <div class="form-group form-col-2">
                        <label class="form-label">Hostname <span class="required">*</span></label>
                        <input type="text" name="hostname" class="form-input mono" value="<?= e($d['hostname'] ?? '') ?>" required placeholder="e.g. core-switch-01">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Device Type <span class="required">*</span></label>
                        <select name="device_type" class="form-select" required>
                            <?php foreach (getNetworkDeviceTypes() as $t): ?>
                            <option value="<?= $t ?>" <?= ($d['device_type'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$t)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($d['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($d['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="offline" <?= ($d['status'] ?? '') === 'offline' ? 'selected' : '' ?>>Offline</option>
                            <option value="unknown" <?= ($d['status'] ?? '') === 'unknown' ? 'selected' : '' ?>>Unknown</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Network Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">IP Address</label>
                        <input type="text" name="ip_address" class="form-input mono" value="<?= e($d['ip_address'] ?? '') ?>" placeholder="192.168.1.1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">MAC Address</label>
                        <input type="text" name="mac_address" class="form-input mono" value="<?= e($d['mac_address'] ?? '') ?>" placeholder="AA:BB:CC:DD:EE:FF">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Hardware Details</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Manufacturer</label>
                        <input type="text" name="manufacturer" class="form-input" value="<?= e($d['manufacturer'] ?? '') ?>" placeholder="e.g. Cisco, Ubiquiti, MikroTik">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" class="form-input" value="<?= e($d['model'] ?? '') ?>" placeholder="e.g. SG350-28">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Firmware Version</label>
                        <input type="text" name="firmware_version" class="form-input mono" value="<?= e($d['firmware_version'] ?? '') ?>" placeholder="e.g. 2.5.7.4">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-input" value="<?= e($d['location'] ?? '') ?>" placeholder="e.g. Server Room, Rack A">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Notes</h3>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-textarea" rows="3" placeholder="VLANs, port configs, maintenance notes..."><?= e($d['notes'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <a href="/network" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary"><?= $editing ? 'Save Changes' : 'Add Device' ?></button>
            </div>
        </form>
    </div>
</div>
