<?php
ob_start();
require_once __DIR__ . '/../src/Document.php';
require_once __DIR__ . '/../src/User.php';

Session::start();

// Yetki kontrolü
if (!User::hasRole(['Süper Admin', 'ISG Uzmanı'])) {
    http_response_code(403); // Forbidden
    die('Bu dosyaya erişim yetkiniz yok.');
}

// 1. ID'nin varlığını ve geçerliliğini kontrol et
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    http_response_code(400); // Bad Request
    die('Geçersiz evrak IDsi.');
}

$document_id = (int)$_GET['id'];
$documentHandler = new Document();
$document = $documentHandler->findDocumentById($document_id);

// 2. Evrakın veritabanında olup olmadığını kontrol et
if (!$document) {
    http_response_code(404); // Not Found
    die('Evrak bulunamadı.');
}

// Varsayılan yükleme dizini. Bu, daha sonra bir ayar dosyasına taşınabilir.
$upload_dir = __DIR__ . '/../uploads/';
$file_path = $upload_dir . $document['file_path'];

// 3. Dosyanın sunucuda var olup olmadığını kontrol et
if (!file_exists($file_path) || !is_readable($file_path)) {
    http_response_code(404);
    die('Dosya sunucuda bulunamadı veya okunamıyor.');
}

// 4. Güvenli indirme işlemini gerçekleştir
// Tarayıcıya dosyanın ne olduğunu ve nasıl davranması gerektiğini söyleyen başlıkları (header) ayarla
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream'); // Genel dosya tipi
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

// Tamponu temizle (önceki çıktıları sil)
ob_clean();
flush();

// Dosyayı oku ve tarayıcıya gönder
readfile($file_path);

exit;
