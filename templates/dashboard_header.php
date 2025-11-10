<?php
// templates/dashboard_header.php
require_once __DIR__ . '/../src/User.php';
require_once __DIR__ . '/../src/Csrf.php';

Session::start();
Csrf::generateToken();

// Giriş yapılmamışsa mutlak yola yönlendir
if (!User::isLoggedIn()) {
    header('Location: /login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KGS İSG Yönetim Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> body { background-color: #f8f9fa; } .main-content { padding-top: 2rem; } .card { margin-bottom: 1.5rem; } </style>
</head>
<body>
<?php include __DIR__ . '/navigation.php'; ?>
<main class="container main-content">
