<?php
// src/NonConformity.php

require_once __DIR__ . '/Database.php';

class NonConformity {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Yeni bir uygunsuzluk kaydı oluşturur.
     * @param array $data
     * @param array|null $file
     * @return bool
     */
    public function create($data, $file) {
        $photo_path = null;
        if (isset($file['photo']) && $file['photo']['error'] === UPLOAD_ERR_OK) {
            // Güvenli dosya yükleme mantığı
            $upload_dir = __DIR__ . '/../uploads/non_conformities/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $filename = uniqid() . '-' . basename($file['photo']['name']);
            $photo_path = 'non_conformities/' . $filename;

            if (!move_uploaded_file($file['photo']['tmp_name'], $upload_dir . $filename)) {
                // Yükleme başarısız olursa null olarak devam et
                $photo_path = null;
            }
        }

        try {
            $query = "INSERT INTO non_conformities (description, category, severity, reported_by, photo_path)
                      VALUES (:description, :category, :severity, :reported_by, :photo_path)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':severity', $data['severity']);
            $stmt->bindParam(':reported_by', $data['reported_by'], PDO::PARAM_INT);
            $stmt->bindParam(':photo_path', $photo_path);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Tüm uygunsuzlukları detaylı bilgi ile getirir.
     * @return array
     */
    public function getAll() {
        try {
            $query = "
                SELECT nc.*, u.first_name, u.last_name
                FROM non_conformities nc
                JOIN users u ON nc.reported_by = u.id
                ORDER BY nc.created_at DESC
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

}
