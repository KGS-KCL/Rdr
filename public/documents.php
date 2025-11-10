<?php
ob_start();
require_once __DIR__ . '/../src/Document.php';
require_once __DIR__ . '/../templates/dashboard_header.php';

if (!User::hasRole(['Süper Admin', 'ISG Uzmanı'])) {
    header("Location: index.php");
    exit();
}

$factory_id = Session::get('factory_id');
// Süper Admin tüm fabrikaların evraklarını görmemeli, bu nedenle factory_id yoksa çık.
if (empty($factory_id)) {
    die('Bu panele erişim için bir fabrika seçimi gereklidir.');
}

$document = new Document();
$all_documents = $document->getAllDocumentsByFactory($factory_id);
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Tüm Evraklar</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Çalışan</th>
                            <th>Firma</th>
                            <th>Evrak Türü</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_documents as $doc): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($doc['company_name']); ?></td>
                                <td><?php echo htmlspecialchars($doc['document_name']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($doc['status']); ?></span></td>
                                <td>
                                    <a href="download_document.php?id=<?php echo $doc['id']; ?>" class="btn btn-primary btn-sm">İndir</a>
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
