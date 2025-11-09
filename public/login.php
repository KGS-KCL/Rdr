<?php
require_once __DIR__ . '/../src/User.php';

Session::start();

// Eğer kullanıcı zaten giriş yapmışsa, ana sayfaya yönlendir
if (User::isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = new User();
    if ($user->login($email, $password)) {
        header("Location: index.php"); // Başarılı girişte yönlendir
        exit();
    } else {
        $error_message = "Geçersiz e-posta veya şifre. Lütfen tekrar deneyin.";
    }
}
?>

<?php include __DIR__ . '/../templates/header.php'; ?>

<div class="login-container">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-body p-5">
                <h2 class="card-title text-center mb-4">KGS İSG Yönetim Sistemi</h2>
                <p class="text-center text-muted mb-4">Lütfen giriş yapın</p>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta Adresi</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Şifre</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Giriş Yap</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
