<?php
ob_start();
require_once __DIR__ . '/../src/User.php';
require_once __DIR__ . '/../src/NonConformity.php';
// require_once __DIR__ . '/../src/Document.php'; // Gelecekte eklenecek

Session::start();

if (!User::isLoggedIn()) {
    http_response_code(403); die('Erişim engellendi.');
}

$type = $_GET['type'] ?? null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (empty($type) || $id <= 0) {
    http_response_code(400); die('Geçersiz istek.');
}

$factory_id = Session::get('factory_id');
if (empty($factory_id)) {
    http_response_code(403); die('Fabrika seçimi gerekli.');
}

$file_path = null;
$filename = null;

switch ($type) {
    case 'non_conformity':
        $handler = new NonConformity();
        $report = $handler->findById($id, $factory_id);

        if ($report && !empty($report['photo_path'])) {
            $filename = $report['photo_path'];
            $file_path = __DIR__ . '/../uploads/' . $factory_id . '/non_conformities/' . $filename;
        }
        break;

    // case 'document':
    //     $doc_handler = new Document();
    //     $document = $doc_handler->findDocumentById($id, $factory_id);
    //     if ($document && !empty($document['file_path'])) {
    //         $filename = $document['file_path'];
    //         // Document'lar için dosya yapısı farklı olabilir, düzenlenmeli
    //         $file_path = __DIR__ . '/../uploads/' . $factory_id . '/documents/' . $filename;
    //     }
    //     break;
}

if ($file_path && file_exists($file_path) && is_readable($file_path)) {
    header('Content-Type: ' . mime_content_type($file_path));
    header('Content-Length: ' . filesize($file_path));
    header('Content-Disposition: inline; filename="' . basename($filename) . '"');

    ob_clean();
    flush();
    readfile($file_path);
    exit;
} else {
    http_response_code(404);
    die('Dosya bulunamadı veya erişim yetkiniz yok.');
}
