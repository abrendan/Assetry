<?php $error = flash('error'); $success = flash('success'); ?>
<div class="page-actions">
    <span class="text-muted"><?= count($users) ?> registered user<?= count($users) !== 1 ? 's' : '' ?></span>
    <button type="button" class="btn btn-primary" onclick="document.getElementById('create-user-modal').style.display='flex'">+ New User</button>
</div>

<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th>Vault Items</th>
                <th>Last Login</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td>
                    <div class="cell-primary">
                        <div class="avatar avatar-sm" style="background:<?= e($u['avatar_color']) ?>"><?= e(initials($u['username'])) ?></div>
                        <span><?= e($u['username']) ?></span>
                    </div>
                </td>
                <td class="text-muted"><?= e($u['email']) ?></td>
                <td>
                    <form method="POST" action="/admin/users/<?= $u['id'] ?>/role" style="display:inline">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        <select name="role" onchange="this.form.submit()" class="form-select-inline <?= $u['role'] === 'admin' ? 'select-admin' : '' ?>">
                            <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </form>
                </td>
                <td class="text-muted"><?= $u['vault_count'] ?></td>
                <td class="text-muted"><?= $u['last_login'] ? formatDateTime($u['last_login']) : 'Never' ?></td>
                <td>
                    <?php if ($u['is_active']): ?>
                    <span class="badge badge-success">Active</span>
                    <?php else: ?>
                    <span class="badge badge-neutral">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex;gap:0.4rem;flex-wrap:wrap">
                        <form method="POST" action="/admin/users/<?= $u['id'] ?>/toggle" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                            <button type="submit" class="btn btn-sm <?= $u['is_active'] ? 'btn-ghost' : 'btn-secondary' ?>" onclick="return confirm('Change user status?')">
                                <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </form>
                        <button type="button" class="btn btn-sm btn-ghost" onclick="resetPassword(<?= $u['id'] ?>, '<?= e($u['username']) ?>')">Reset PW</button>
                        <form method="POST" action="/admin/users/<?= $u['id'] ?>/delete" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                            <button type="submit" class="btn btn-sm btn-ghost" style="color:#ef4444" onclick="return confirm('Delete user <?= e($u['username']) ?>? This cannot be undone.')">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="create-user-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);align-items:center;justify-content:center;z-index:100">
    <div style="background:#1a1a24;padding:2rem;border-radius:12px;width:100%;max-width:420px;border:1px solid rgba(255,255,255,0.1)">
        <h3 style="margin-top:0">Create New User</h3>
        <form method="POST" action="/admin/users/create">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" required minlength="3">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required minlength="8">
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" class="form-input">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="display:flex;gap:0.5rem;justify-content:flex-end">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('create-user-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<div id="reset-pw-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);align-items:center;justify-content:center;z-index:100">
    <div style="background:#1a1a24;padding:2rem;border-radius:12px;width:100%;max-width:420px;border:1px solid rgba(255,255,255,0.1)">
        <h3 style="margin-top:0">Reset Password for <span id="reset-pw-username"></span></h3>
        <form method="POST" id="reset-pw-form">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-input" required minlength="8">
            </div>
            <div style="display:flex;gap:0.5rem;justify-content:flex-end">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('reset-pw-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<script>
function resetPassword(id, username) {
    document.getElementById('reset-pw-username').textContent = username;
    document.getElementById('reset-pw-form').action = '/admin/users/' + id + '/reset-password';
    document.getElementById('reset-pw-modal').style.display = 'flex';
}
</script>
