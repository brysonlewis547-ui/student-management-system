<?php
require 'config.php';
if (!empty($_SESSION['user'])) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['u'] ?? ''));
    $password = $_POST['p'] ?? '';
    if ($username === '' || $password === '') {
        $error = 'Enter your username and password.';
    } else {
        $st = db()->prepare('SELECT * FROM users WHERE username = ?');
        $st->execute([$username]);
        $user = $st->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['username'], 'role' => $user['role']];
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Incorrect username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Login - Student Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<section id="login">
    <h1>Student Management System</h1>
    <p class="note">Sign in to record attendance and academic performance.</p>
    <form method="post">
        <label for="u">Username</label>
        <input id="u" name="u" autocomplete="username" value="<?= e($_POST['u'] ?? '') ?>">
        <label for="p">Password</label>
        <input id="p" name="p" type="password" autocomplete="current-password">
        <div class="err" role="alert"><?= e($error) ?></div>
        <button class="btn" type="submit" style="width:100%">Sign in</button>
    </form>
    <p class="note"><b>admin</b> / Admin@123 or <b>teacher</b> / Teach@123</p>
</section>
</body>
</html>
