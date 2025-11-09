<?php
// templates/navigation.php

// Oturumun başlatıldığından emin ol
if (session_status() === PHP_SESSION_NONE) {
    Session::start();
}
$user_role = Session::get('user_role');
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">KGS İSG</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php">Gösterge Paneli</a>
                </li>

                <?php if (User::hasRole('Süper Admin')): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Yönetim
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                            <li><a class="dropdown-item" href="users.php">Kullanıcı Yönetimi</a></li>
                            <li><a class="dropdown-item" href="companies.php">Firma Yönetimi</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="settings.php">Sistem Ayarları</a></li>
                            <li><a class="dropdown-item" href="logs.php">Sistem Logları</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (User::hasRole(['Süper Admin', 'ISG Uzmanı'])): ?>
                    <li class="nav-item"><a class="nav-link" href="documents.php">Evrak Yönetimi</a></li>
                    <li class="nav-item"><a class="nav-link" href="trainings.php">Eğitim Yönetimi</a></li>
                <?php endif; ?>

                <?php if (User::hasRole('Tedarikçi')): ?>
                    <li class="nav-item"><a class="nav-link" href="my_staff.php">Çalışanlarım</a></li>
                    <li class="nav-item"><a class="nav-link" href="my_documents.php">Evraklarım</a></li>
                <?php endif; ?>

                <?php if (User::hasRole('Alt Kullanıcı')): ?>
                    <li class="nav-item"><a class="nav-link" href="my_trainings.php">Eğitimlerim</a></li>
                    <li class="nav-item"><a class="nav-link" href="my_documents_status.php">Evrak Durumum</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                 <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo htmlspecialchars(Session::get('user_email')); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="profile.php">Profilim</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Çıkış Yap</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
