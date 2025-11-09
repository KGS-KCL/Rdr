<?php
require_once __DIR__ . '/../src/User.php';
// Yeni dashboard header'ı dahil et
include __DIR__ . '/../templates/dashboard_header.php';

// Kullanıcı bilgilerini oturumdan al
$user_email = Session::get('user_email');
$user_role = Session::get('user_role');
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Gösterge Paneli</h1>
    <div class="alert alert-success">
        Hoş geldiniz, <strong><?php echo htmlspecialchars($user_email); ?></strong>! Rolünüz: <strong><?php echo htmlspecialchars($user_role); ?></strong>
    </div>

    <!-- Rol Bazlı Dashboard İçeriği -->
    <div class="row">
        <?php if (User::hasRole('Süper Admin')): ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Toplam Kullanıcı</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"> (Dinamik veri gelecek)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (User::hasRole('ISG Uzmanı')): ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Onay Bekleyen Evrak</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"> (Dinamik veri gelecek)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (User::hasRole('Tedarikçi')): ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Süresi Dolan Evrak</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"> (Dinamik veri gelecek)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

         <?php if (User::hasRole('Alt Kullanıcı')): ?>
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tamamlanmamış Eğitimler</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"> (Dinamik veri gelecek)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Yeni dashboard footer'ı dahil et
include __DIR__ . '/../templates/dashboard_footer.php';
?>
