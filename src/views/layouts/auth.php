<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Sign In') ?> — Assetry</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/static/app.css">
    <link rel="icon" type="image/svg+xml" href="/static/logo.svg">
</head>
<body class="auth-body">
<div class="auth-container">
    <div class="auth-brand">
        <div class="auth-brand-icon auth-brand-icon-img">
            <img src="/static/logo.svg" alt="Assetry" width="56" height="56">
        </div>
        <span class="auth-brand-name">Assetry</span>
    </div>
    <div class="auth-card">
        <?php echo $content ?? ''; ?>
    </div>
    <p class="auth-footer">Assetry &copy; <?= date('Y') ?> <a href="https://www.abrendan.dev" target="_blank" rel="noopener" style="color:inherit;text-decoration:none;border-bottom:1px dotted currentColor">abrendan</a></p>
</div>
<script src="/static/app.js"></script>
</body>
</html>
