<?php
ob_start();
// Dashboard header'ı normal yoldan include ediyoruz, çünkü yol seviyesi farklı
require_once __DIR__ . '/../../templates/dashboard_header.php';
require_once __DIR__ . '/../../src/Factory.php';

// Sadece Süper Admin erişebilir
if (!User::hasRole('Süper Admin')) {
    header("Location: /index.php"); // Ana dizine yönlendir
    exit();
}

$factory_handler = new Factory();

// Form işlemleri (yeni fabrika ekleme veya silme)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' && !empty($_POST['factory_name'])) {
            $data = [
                'factory_name' => $_POST['factory_name'],
                'user_limit' => (int)$_POST['user_limit']
            ];
            $factory_handler->create($data);
        } elseif ($_POST['action'] === 'delete' && isset($_POST['factory_id'])) {
            $factory_handler->delete((int)$_POST['factory_id']);
        }
    }
    header("Location: index.php"); // Sayfayı yeniden yükle
    exit();
}

$factories = $factory_handler->getAll();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Süper Admin Paneli - Fabrika Yönetimi</h1>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Yeni Fabrika Hesabı Oluştur</h6></div>
                <div class="card-body">
                    <form method="POST" action="index.php">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label>Fabrika Adı:</label>
                            <input type="text" name="factory_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Kullanıcı Limiti:</label>
                            <input type="number" name="user_limit" class="form-control" value="10" required>
                        </div>
                        <button type="submit" class="btn btn-success">Oluştur</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header"><h6 class="m-0 font-weight-bold text-info">Mevcut Fabrikalar</h6></div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead><tr><th>ID</th><th>Fabrika Adı</th><th>Kullanıcı Limiti</th><th>İşlemler</th></tr></thead>
                        <tbody>
                            <?php foreach ($factories as $factory): ?>
                            <tr>
                                <td><?php echo $factory['id']; ?></td>
                                <td><?php echo htmlspecialchars($factory['factory_name']); ?></td>
                                <td><?php echo htmlspecialchars($factory['user_limit']); ?></td>
                                <td>
                                    <form method="POST" action="index.php" onsubmit="return confirm('Bu fabrikayı ve ilişkili tüm verileri silmek istediğinizden emin misiniz?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="factory_id" value="<?php echo $factory['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../templates/dashboard_footer.php';
ob_end_flush();
?>
