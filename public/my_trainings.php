<?php
ob_start();
require_once __DIR__ . '/../src/Training.php';
require_once __DIR__ . '/../templates/dashboard_header.php';

// Sadece giriş yapmış kullanıcılar erişebilir
if (!User::isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$training_handler = new Training();
$user_id = Session::get('user_id');
$assigned_trainings = $training_handler->getAssignedTrainingsByUserId($user_id);
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Eğitimlerim</h1>

    <?php if (empty($assigned_trainings)): ?>
        <div class="alert alert-info">Henüz size atanmış bir eğitim bulunmamaktadır.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($assigned_trainings as $training): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($training['title']); ?></h5>
                            <p class="card-text flex-grow-1"><?php echo nl2br(htmlspecialchars($training['description'])); ?></p>
                            <div class="mt-auto">
                                <?php if ($training['status'] === 'Tamamlandı'): ?>
                                    <p class="text-success mb-2">
                                        <strong>Durum:</strong> Tamamlandı
                                        <br>
                                        <small>Tarih: <?php echo date('d/m/Y', strtotime($training['completed_at'])); ?></small>
                                    </p>
                                    <button class="btn btn-secondary" disabled>Eğitim Tamamlandı</button>
                                <?php else: ?>
                                    <p class="text-warning mb-2">
                                        <strong>Durum:</strong> <?php echo htmlspecialchars($training['status']); ?>
                                    </p>
                                    <a href="watch_training.php?id=<?php echo $training['id']; ?>" class="btn btn-primary">
                                        Eğitimi Başlat
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
include __DIR__ . '/../templates/dashboard_footer.php';
ob_end_flush();
?>
