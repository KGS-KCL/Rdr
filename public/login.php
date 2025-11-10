<?php
require_once __DIR__ . '/../src/User.php';
require_once __DIR__ . '/../src/Csrf.php';

Session::start();
Csrf::generateToken();

if (User::isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Csrf::validateToken($_POST['csrf_token'])) {
        die('CSRF token doğrulaması başarısız!');
    }

    $factory_id = $_POST['factory_id'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = new User();
    if ($user->login($factory_id, $email, $password)) {
        header("Location: index.php");
        exit();
    } else {
        $error_message = "Geçersiz Fabrika ID, e-posta veya şifre.";
    }
}
?>

<?php include __DIR__ . '/../templates/header.php'; ?>

<div class="login-container">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-body p-5">
                <h2 class="card-title text-center mb-4">KGS İSG Yönetim Sistemi</h2>
                <form action="login.php" method="POST">
                    <?php echo Csrf::getInputField(); ?>
                    <div class="mb-3">
                        <label for="factory_id" class="form-label">Fabrika ID</label>
                        <input type="number" class="form-control" id="factory_id" name="factory_id">
                        <small class="form-text text-muted">Süper Admin girişi için boş bırakın.</small>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta Adresi</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Şifre</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">Giriş Yap</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
