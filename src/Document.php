<?php
// src/Document.php

require_once __DIR__ . '/Database.php';

class Document {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Yeni bir evrak kaydı oluşturur ve dosyasını yükler.
     * @param array $data
     * @param array $file
     * @param int $factory_id
     * @return bool
     */
    public function create($data, $file, $factory_id) {
        $file_path = null;
        if (isset($file['document']) && $file['document']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/' . $factory_id . '/documents/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $filename = uniqid() . '-' . basename($file['document']['name']);
            $file_path = $filename; // Sadece dosya adını sakla

            if (!move_uploaded_file($file['document']['tmp_name'], $upload_dir . $filename)) {
                return false; // Yükleme başarısız olursa işlemi durdur
            }
        } else {
            return false; // Dosya yoksa veya hatalıysa işlemi durdur
        }

        try {
            $query = "INSERT INTO documents (user_id, document_type_id, file_path, expires_at)
                      VALUES (:user_id, :document_type_id, :file_path, :expires_at)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':document_type_id', $data['document_type_id'], PDO::PARAM_INT);
            $stmt->bindParam(':file_path', $file_path);
            $stmt->bindParam(':expires_at', $data['expires_at']);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Belirli bir fabrikadaki tüm kullanıcı evraklarını detaylı bilgi ile getirir.
     * @param int $factory_id
     * @return array
     */
    public function getAllDocumentsByFactory($factory_id) {
        try {
            $query = "
                SELECT d.id, d.file_path, d.status, d.uploaded_at,
                       u.first_name, u.last_name, c.company_name, dt.document_name
                FROM documents d
                JOIN users u ON d.user_id = u.id
                JOIN companies c ON u.company_id = c.id
                JOIN document_types dt ON d.document_type_id = dt.id
                WHERE u.factory_id = :factory_id
                ORDER BY d.uploaded_at DESC
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':factory_id', $factory_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * ID'ye ve factory_id'ye göre tek bir evrakın bilgilerini getirir.
     * @param int $id
     * @param int $factory_id
     * @return mixed|null
     */
    public function findDocumentById($id, $factory_id) {
        try {
            $query = "
                SELECT d.* FROM documents d
                JOIN users u ON d.user_id = u.id
                WHERE d.id = :id AND u.factory_id = :factory_id
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':factory_id', $factory_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }
}
