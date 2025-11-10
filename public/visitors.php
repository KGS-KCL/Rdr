<?php
ob_start();
require_once __DIR__ . '/../src/Visitor.php';
require_once __DIR__ . '/../templates/dashboard_header.php'; // Bu dosya Csrf.php'yi zaten içeriyor

if (!User::hasRole(['Süper Admin', 'ISG Uzmanı', 'Evrak Kontrol / Güvenlik'])) {
    header("Location: index.php");
    exit();
}

$factory_id = Session::get('factory_id');
$visitor_handler = new Visitor();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Doğrulaması
    if (!isset($_POST['csrf_token']) || !Csrf::validateToken($_POST['csrf_token'])) {
        die('CSRF token doğrulaması başarısız!');
    }

    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'exit' && isset($_POST['visitor_id'])) {
            $visitor_handler->markAsExited((int)$_POST['visitor_id'], $factory_id);
        } elseif ($_POST['action'] === 'add') {
            $data = [
                'full_name' => $_POST['full_name'],
                'company' => $_POST['company'],
                'visiting_department' => $_POST['visiting_department'],
                'reason_for_visit' => $_POST['reason_for_visit'],
                'recorded_by' => Session::get('user_id')
            ];
            $visitor_handler->createVisitor($data, $factory_id);
        }
    }
    header("Location: visitors.php");
    exit();
}

$active_visitors = $visitor_handler->getActiveVisitors($factory_id);
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Ziyaretçi Yönetimi</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Yeni Ziyaretçi Girişi</h6></div>
        <div class="card-body">
            <form method="POST" action="visitors.php">
                <?php echo Csrf::getInputField(); ?>
                <input type="hidden" name="action" value="add">
                <div class="row mb-3">
                    <div class="col-md-6"><input type="text" name="full_name" class="form-control" placeholder="Adı Soyadı" required></div>
                    <div class="col-md-6"><input type="text" name="company" class="form-control" placeholder="Firma"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><input type="text" name="visiting_department" class="form-control" placeholder="Ziyaret Ettiği Birim"></div>
                    <div class="col-md-6"><input type="text" name="reason_for_visit" class="form-control" placeholder="Ziyaret Nedeni"></div>
                </div>
                <button type="submit" class="btn btn-success">Ziyaretçi Ekle</button>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-warning">İçerideki Ziyaretçiler</h6></div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead><tr><th>Adı Soyadı</th><th>Firma</th><th>Giriş Saati</th><th>İşlemler</th></tr></thead>
                <tbody>
                    <?php foreach ($active_visitors as $visitor): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($visitor['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($visitor['company']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($visitor['entry_time'])); ?></td>
                        <td>
                            <form method="POST" action="visitors.php" style="display:inline;">
                                <?php echo Csrf::getInputField(); ?>
                                <input type="hidden" name="action" value="exit">
                                <input type="hidden" name="visitor_id" value="<?php echo $visitor['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Çıkış Yap</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../templates/dashboard_footer.php';
ob_end_flush();
?>
