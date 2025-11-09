<?php
// templates/dashboard_header.php
require_once __DIR__ . '/../src/User.php';

// Oturumu başlat/devam et
Session::start();

// Giriş yapılmamışsa login sayfasına yönlendir
if (!User::isLoggedIn()) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KGS İSG Yönetim Sistemi</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Optional: Add custom CSS for dashboard -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .main-content {
            padding-top: 2rem;
        }
        .card {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/navigation.php'; ?>

<main class="container main-content">
