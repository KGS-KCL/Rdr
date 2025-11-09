<?php
ob_start();
require_once __DIR__ . '/../src/Training.php';
require_once __DIR__ . '/../templates/dashboard_header.php';

// Yetki ve ID kontrolü
if (!User::isLoggedIn() || !isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: my_trainings.php");
    exit();
}

$training_id = (int)$_GET['id'];
$training_handler = new Training();
$training = $training_handler->findTrainingById($training_id);

// Eğitim bulunamazsa veya video linki yoksa listeye geri yönlendir
if (!$training || (empty($training['video_url']) && empty($training['file_path']))) {
    header("Location: my_trainings.php");
    exit();
}

$video_source = !empty($training['video_url']) ? $training['video_url'] : 'uploads/trainings/' . $training['file_path'];

?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800"><?php echo htmlspecialchars($training['title']); ?></h1>
    <p class="mb-4"><?php echo nl2br(htmlspecialchars($training['description'])); ?></p>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Eğitim Videosu</h6>
        </div>
        <div class="card-body text-center">
            <video id="trainingVideo" class="img-fluid" controls controlsList="nodownload">
                <source src="<?php echo htmlspecialchars($video_source); ?>" type="video/mp4">
                Tarayıcınız video etiketini desteklemiyor.
            </video>
        </div>
    </div>

    <div class="text-center mb-4">
        <button id="startTestBtn" class="btn btn-success btn-lg" disabled>
            Eğitimi Tamamladım, Teste Başla
        </button>
        <p id="watchNotice" class="text-muted mt-2">Teste başlamak için videonun tamamını izlemelisiniz.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('trainingVideo');
    const startTestBtn = document.getElementById('startTestBtn');
    const watchNotice = document.getElementById('watchNotice');
    let videoWatched = false;

    // 1. Video Tamamen İzlenince Test Butonunu Aktif Et
    video.addEventListener('ended', function() {
        startTestBtn.disabled = false;
        watchNotice.textContent = 'Tebrikler! Artık teste başlayabilirsiniz.';
        watchNotice.classList.remove('text-muted');
        watchNotice.classList.add('text-success');
        videoWatched = true;
    });

    // Kullanıcı videoyu ileri sararsa, izlenmiş sayma
    // Bu basit bir kontrol, daha karmaşık hale getirilebilir.
    video.addEventListener('timeupdate', function() {
        // Eğer video son 5 saniyeye gelmişse ve henüz bitmemişse, izlenmiş kabul et.
        // Bu, bazı tarayıcılardaki 'ended' olayının tetiklenmemesi sorununu aşmaya yardımcı olur.
        if (!videoWatched && (video.duration - video.currentTime) < 5) {
             if (!startTestBtn.disabled) return; // Zaten aktifse bir şey yapma

             startTestBtn.disabled = false;
             watchNotice.textContent = 'Tebrikler! Artık teste başlayabilirsiniz.';
             watchNotice.classList.remove('text-muted');
             watchNotice.classList.add('text-success');
             videoWatched = true;
        }
    });


    // 2. Sekme Değiştiğinde veya Pencere Küçültüldüğünde Videoyu Duraklat
    // Tarayıcı sekmesi görünürlüğü değiştiğinde
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            video.pause();
        }
    });

    // Pencere odaktan çıktığında (başka uygulamaya geçildiğinde)
    window.addEventListener('blur', function() {
        video.pause();
    });
});
</script>

<?php
include __DIR__ . '/../templates/dashboard_footer.php';
ob_end_flush();
?>
