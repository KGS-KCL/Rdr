<?php
// src/Document.php

require_once __DIR__ . '/Database.php';

class Document {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Tüm kullanıcı evraklarını detaylı bilgi ile birlikte getirir.
     * ISG Uzmanı ve Süper Admin'in tüm evrakları görmesi için tasarlanmıştır.
     * @return array
     */
    public function getAllDocumentsWithDetails() {
        try {
            $query = "
                SELECT
                    d.id,
                    d.file_path,
                    d.status,
                    d.uploaded_at,
                    u.first_name,
                    u.last_name,
                    c.company_name,
                    dt.document_name
                FROM
                    documents d
                JOIN
                    users u ON d.user_id = u.id
                JOIN
                    companies c ON u.company_id = c.id
                JOIN
                    document_types dt ON d.document_type_id = dt.id
                ORDER BY
                    d.uploaded_at DESC
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Hata durumunda boş bir dizi döndür. Gerçek bir uygulamada bu loglanmalıdır.
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * ID'ye göre tek bir evrakın bilgilerini getirir.
     * Güvenli indirme işlemi için dosya yolunu doğrulamak amacıyla kullanılır.
     * @param int $id
     * @return mixed|null
     */
    public function findDocumentById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM documents WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }
}
