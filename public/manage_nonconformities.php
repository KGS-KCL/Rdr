<?php
ob_start();
require_once __DIR__ . '/../src/NonConformity.php';
require_once __DIR__ . '/../templates/dashboard_header.php';

if (!User::hasRole(['Süper Admin', 'ISG Uzmanı'])) {
    header("Location: index.php");
    exit();
}

$factory_id = Session::get('factory_id');
if (empty($factory_id)) {
    die('Bu panele erişim için bir fabrika seçimi gereklidir.');
}

$handler = new NonConformity();
$all_reports = $handler->getAll($factory_id);
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Uygunsuzluk Yönetim Paneli</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Fotoğraf</th>
                            <th>Açıklama</th>
                            <th>Kategori / Önem</th>
                            <th>Durum</th>
                            <th>Bildiren / Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_reports as $report): ?>
                            <tr>
                                <td>
                                    <?php if ($report['photo_path']): ?>
                                        <a href="serve_file.php?type=non_conformity&id=<?php echo $report['id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            Görüntüle
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($report['description']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($report['category']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($report['severity']); ?></small>
                                </td>
                                <td><span class="badge bg-info"><?php echo htmlspecialchars($report['status']); ?></span></td>
                                <td>
                                    <?php echo htmlspecialchars($report['first_name'] . ' ' . $report['last_name']); ?><br>
                                    <small><?php echo date('d/m/Y H:i', strtotime($report['created_at'])); ?></small>
                                </td>
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
