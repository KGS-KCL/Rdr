<?php
ob_start();
require_once __DIR__ . '/../src/Document.php';
require_once __DIR__ . '/../src/User.php';

Session::start();

if (!User::hasRole(['Süper Admin', 'ISG Uzmanı'])) {
    http_response_code(403);
    die('Yetkiniz yok.');
}

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    http_response_code(400);
    die('Geçersiz ID.');
}

$factory_id = Session::get('factory_id');
if (empty($factory_id)) {
    http_response_code(403);
    die('Fabrika seçimi gerekli.');
}

$document_id = (int)$_GET['id'];
$documentHandler = new Document();
$document = $documentHandler->findDocumentById($document_id, $factory_id);

if (!$document) {
    http_response_code(404);
    die('Evrak bulunamadı.');
}

// Dosya yolu factory_id'ye göre oluşturulmalı
// Şimdilik basit bir yapı varsayıyoruz, NonConformity'deki gibi geliştirilebilir.
$file_path = __DIR__ . '/../uploads/' . $document['file_path'];

if (!file_exists($file_path)) {
    http_response_code(404);
    die('Dosya sunucuda bulunamadı.');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

ob_clean();
flush();
readfile($file_path);
exit;
