<?php
ob_start();
require_once __DIR__ . '/../src/NonConformity.php';
require_once __DIR__ . '/../templates/dashboard_header.php';

// Yetki kontrolü
if (!User::hasRole(['Süper Admin', 'ISG Uzmanı'])) {
    header("Location: index.php");
    exit();
}

$handler = new NonConformity();
$all_reports = $handler->getAll();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Uygunsuzluk Yönetim Paneli</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tüm Bildirimler</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Fotoğraf</th>
                            <th>Açıklama</th>
                            <th>Kategori</th>
                            <th>Önem</th>
                            <th>Durum</th>
                            <th>Bildiren</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_reports as $report): ?>
                            <tr>
                                <td>
                                    <?php if ($report['photo_path']): ?>
                                        <a href="/uploads/<?php echo htmlspecialchars($report['photo_path']); ?>" target="_blank">
                                            Görüntüle
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($report['description']); ?></td>
                                <td><?php echo htmlspecialchars($report['category']); ?></td>
                                <td><?php echo htmlspecialchars($report['severity']); ?></td>
                                <td><span class="badge bg-info"><?php echo htmlspecialchars($report['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($report['first_name'] . ' ' . $report['last_name']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($report['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../templates/dashboard_footer.php';
ob_end_flush();
?>
