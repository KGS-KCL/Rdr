<?php
// src/Document.php

require_once __DIR__ . '/Database.php';

class Document {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
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
