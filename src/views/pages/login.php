<?php
$error = flash('error');
$success = flash('success');
?>
<div class="auth-inner">
    <h2 class="auth-title">Welcome back</h2>
    <p class="auth-desc">Sign in to your Assetry account</p>

    <?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="/login" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
        <div class="form-group">
            <label class="form-label">Username or Email</label>
            <input type="text" name="username" class="form-input" placeholder="Enter username or email" required autofocus>
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <div class="input-with-action">
                <input type="password" name="password" id="password-input" class="form-input" placeholder="Enter password" required>
                <button type="button" class="input-action-btn" onclick="togglePassword()">
                    <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Sign in</button>
    </form>

    <p class="auth-switch text-muted" style="font-size:0.85rem">Contact your administrator if you need an account.</p>
</div>
<script>
function togglePassword() {
    const input = document.getElementById('password-input');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
