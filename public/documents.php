<?php
ob_start();
require_once __DIR__ . '/../src/Document.php';
require_once __DIR__ . '/../templates/dashboard_header.php';

// Yetki kontrolü
if (!User::hasRole(['Süper Admin', 'ISG Uzmanı'])) {
    // Yetkisiz erişim durumunda ana sayfaya yönlendir
    header("Location: index.php");
    exit();
}

$document = new Document();
$all_documents = $document->getAllDocumentsWithDetails();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Tüm Evraklar</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Evrak Listesi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="documentsTable" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>Çalışan</th>
                            <th>Firma</th>
                            <th>Evrak Türü</th>
                            <th>Yüklenme Tarihi</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($all_documents)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Gösterilecek evrak bulunamadı.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($all_documents as $doc): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($doc['company_name']); ?></td>
                                    <td><?php echo htmlspecialchars($doc['document_name']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($doc['uploaded_at'])); ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($doc['status']); ?></span>
                                    </td>
                                    <td>
                                        <a href="download_document.php?id=<?php echo $doc['id']; ?>" class="btn btn-primary btn-sm" title="İndir">
                                            İndir
                                        </a>
                                        <!-- Onay/Red butonları buraya eklenebilir -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
