<?php
ob_start();
require_once __DIR__ . '/../src/User.php';
require_once __DIR__ . '/../src/GateLog.php';
require_once __DIR__ . '/../src/Notification.php';
require_once __DIR__ . '/../templates/dashboard_header.php';

// Yetki kontrolü
if (!User::hasRole(['Süper Admin', 'ISG Uzmanı', 'Evrak Kontrol / Güvenlik'])) {
    header("Location: index.php");
    exit();
}

$user_handler = new User();
$gatelog_handler = new GateLog();
$notification_handler = new Notification();

$search_results = [];
$search_term = '';

// Arama yapılmışsa
if (isset($_GET['search_term']) && !empty($_GET['search_term'])) {
    $search_term = $_GET['search_term'];
    $search_results = $user_handler->searchEmployees($search_term);
}

// Giriş/Çıkış işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    $security_id = Session::get('user_id');

    $gatelog_handler->createLog($user_id, $action, $security_id);

    // Eğer giriş yapılıyorsa, bilgilendirme e-postası gönder
    if ($action === 'Giriş') {
        $user_info = $user_handler->findUserById($user_id);
        if ($user_info && !empty($user_info['email'])) {
            // Kullanıcıya özel benzersiz token veya ID kullanarak link oluştur
            $token = !empty($user_info['qr_code_token']) ? $user_info['qr_code_token'] : $user_id;
            $unique_link = "http://{$_SERVER['HTTP_HOST']}/safety_rules.php?token=" . urlencode($token);

            $message_body = $notification_handler->createSafetyInfoEmailBody($user_info['first_name'], $unique_link);
            $notification_handler->sendEmail($user_info['email'], 'KGS İSG - Güvenlik Bilgilendirmesi', $message_body);
        }
    }

    header("Location: gate_control.php");
    exit();
}

$inside_users = $gatelog_handler->getCurrentlyInsideUsers();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Kapı Kontrol Paneli</h1>

    <div class="row">
        <!-- Arama ve Sonuçlar -->
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Personel Arama ve İşlem</h6></div>
                <div class="card-body">
                    <form method="GET" action="gate_control.php">
                        <div class="input-group mb-3">
                            <input type="text" name="search_term" class="form-control" placeholder="Ad, Soyad, TCKN veya Sicil No..." value="<?php echo htmlspecialchars($search_term); ?>">
                            <button class="btn btn-info" type="submit">Ara</button>
                        </div>
                    </form>

                    <?php if (!empty($search_term)): ?>
                        <table class="table table-sm">
                        <?php foreach ($search_results as $user):
                            $last_log = $gatelog_handler->getLastLogByUserId($user['id']);
                            $action = (!$last_log || $last_log['action'] === 'Çıkış') ? 'Giriş' : 'Çıkış';
                            $btn_class = ($action === 'Giriş') ? 'btn-success' : 'btn-danger';
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?><br><small><?php echo htmlspecialchars($user['company_name']); ?></small></td>
                                <td class="text-end">
                                    <form method="POST" action="gate_control.php">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                                        <button type="submit" class="btn <?php echo $btn_class; ?>"><?php echo $action; ?> Yap</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- İçerideki Personel -->
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header"><h6 class="m-0 font-weight-bold text-warning">İçerideki Personel</h6></div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach ($inside_users as $user): ?>
                            <li class="list-group-item"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?> (<?php echo htmlspecialchars($user['company_name']); ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../templates/dashboard_footer.php';
ob_end_flush();
?>
