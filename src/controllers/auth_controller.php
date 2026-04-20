<?php

route('GET', '/login', function($params) {
    if (isLoggedIn()) redirect('/dashboard');
    $title = 'Sign In';
    ob_start();
    require __DIR__ . '/../views/pages/login.php';
    $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/auth.php';
});

route('POST', '/login', function($params) {
    verifyCsrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login($username, $password)) {
        redirect('/dashboard');
    } else {
        flash('error', 'Invalid username or password');
        redirect('/login');
    }
});

route('GET', '/logout', function($params) {
    logout();
});

route('GET', '/', function($params) {
    if (isLoggedIn()) redirect('/dashboard');
    redirect('/login');
});
