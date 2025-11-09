<?php
ob_start();
require_once __DIR__ . '/../src/NonConformity.php';
require_once __DIR__ . '/../templates/dashboard_header.php';

// Sadece giriş yapmış kullanıcılar erişebilir
if (!User::isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$message = '';
$success = false;

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $handler = new NonConformity();
    $data = [
        'description' => $_POST['description'],
        'category' => $_POST['category'],
        'severity' => $_POST['severity'],
        'reported_by' => Session::get('user_id'),
    ];

    if ($handler->create($data, $_FILES)) {
        $message = 'Uygunsuzluk başarıyla bildirildi. Yöneticilere iletildi.';
        $success = true;
    } else {
        $message = 'Bildirim sırasında bir hata oluştu. Lütfen tekrar deneyin.';
        $success = false;
    }
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Saha Uygunsuzluk Bildirimi</h1>

    <?php if ($message): ?>
        <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if (!$success): // Sadece işlem başarılı olmadığında formu göster ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Yeni Bildirim Formu</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="report_nonconformity.php" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="photo" class="form-label">Fotoğraf (İsteğe Bağlı)</label>
                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Açıklama</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="category" class="form-label">Kategori</label>
                        <select class="form-select" id="category" name="category">
                            <option value="KKD Eksikliği">KKD Eksikliği</option>
                            <option value="Ekipman Hasarı">Ekipman Hasarı</option>
                            <option value="Tehlikeli Durum">Tehlikeli Durum</option>
                            <option value="Çevresel Risk">Çevresel Risk</option>
                            <option value="Diğer">Diğer</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="severity" class="form-label">Önem Derecesi</label>
                        <select class="form-select" id="severity" name="severity">
                            <option value="Düşük">Düşük</option>
                            <option value="Orta">Orta</option>
                            <option value="Yüksek">Yüksek</option>
                            <option value="Kritik">Kritik</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Bildirimi Gönder</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
include __DIR__ . '/../templates/dashboard_footer.php';
ob_end_flush();
?>
