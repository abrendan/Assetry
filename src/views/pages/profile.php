<?php $error = flash('error'); $success = flash('success'); ?>

<div class="profile-layout">
    <div class="profile-sidebar">
        <div class="profile-avatar-section">
            <div class="avatar avatar-xl" style="background:<?= e($user['avatar_color']) ?>"><?= e(initials($user['username'])) ?></div>
            <h3><?= e($user['username']) ?></h3>
            <span class="badge <?= $user['role'] === 'admin' ? 'badge-purple' : 'badge-neutral' ?>"><?= ucfirst($user['role']) ?></span>
        </div>
        <div class="profile-stats">
            <div class="profile-stat">
                <span class="profile-stat-value"><?= $stats['assets'] ?></span>
                <span class="profile-stat-label">Assets</span>
            </div>
            <div class="profile-stat">
                <span class="profile-stat-value"><?= $stats['keys'] ?></span>
                <span class="profile-stat-label">Keys</span>
            </div>
            <div class="profile-stat">
                <span class="profile-stat-value"><?= $stats['licenses'] ?></span>
                <span class="profile-stat-label">Licenses</span>
            </div>
            <div class="profile-stat">
                <span class="profile-stat-value"><?= $stats['vault'] ?></span>
                <span class="profile-stat-label">Vault Items</span>
            </div>
        </div>
        <div class="profile-info">
            <div class="detail-field">
                <span class="detail-label">Member since</span>
                <span class="detail-value"><?= formatDate($user['created_at']) ?></span>
            </div>
            <div class="detail-field">
                <span class="detail-label">Last login</span>
                <span class="detail-value"><?= $user['last_login'] ? formatDateTime($user['last_login']) : 'Now' ?></span>
            </div>
        </div>
    </div>

    <div class="profile-main">
        <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <div class="detail-card">
            <h3 class="form-section-title" style="margin-bottom:1.5rem">Account Settings</h3>
            <form method="POST" action="/profile">
                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

                <div class="form-section">
                    <h4 class="form-section-title">Profile</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-input" value="<?= e($user['username']) ?>" disabled>
                            <span class="form-hint">Username cannot be changed</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-input" value="<?= e($user['email']) ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Avatar Color</label>
                        <div class="color-picker">
                            <?php
                            $colors = ['#6366f1','#8b5cf6','#ec4899','#06b6d4','#10b981','#f59e0b','#ef4444','#3b82f6','#14b8a6','#f97316'];
                            foreach ($colors as $c):
                            ?>
                            <label class="color-swatch <?= $user['avatar_color'] === $c ? 'color-selected' : '' ?>">
                                <input type="radio" name="avatar_color" value="<?= $c ?>" <?= $user['avatar_color'] === $c ? 'checked' : '' ?> style="display:none">
                                <span class="color-dot" style="background:<?= $c ?>"></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="form-section-title">Change Password</h4>
                    <p class="form-hint">Leave blank to keep your current password</p>
                    <div class="form-grid">
                        <div class="form-group form-col-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-input" placeholder="Current password">
                        </div>
                        <div class="form-group form-col-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-input" placeholder="Min 8 characters" minlength="8">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
