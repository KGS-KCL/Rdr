<?php
// src/NonConformity.php

require_once __DIR__ . '/Database.php';

class NonConformity {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Yeni bir uygunsuzluk kaydı oluşturur (factory_id ve dosya izolasyonu ile).
     * @param array $data
     * @param array|null $file
     * @param int $factory_id
     * @return bool
     */
    public function create($data, $file, $factory_id) {
        $photo_path = null;
        if (isset($file['photo']) && $file['photo']['error'] === UPLOAD_ERR_OK) {
            // Fabrikaya özel dosya yolu oluştur
            $upload_dir = __DIR__ . '/../uploads/' . $factory_id . '/non_conformities/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $filename = uniqid() . '-' . basename($file['photo']['name']);
            // Veritabanına sadece dosya adını kaydet, yol programatik olarak oluşturulacak
            $photo_path = $filename;

            if (!move_uploaded_file($file['photo']['tmp_name'], $upload_dir . $filename)) {
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
     * Belirli bir fabrikadaki tüm uygunsuzlukları getirir.
     * @param int $factory_id
     * @return array
     */
    public function findById($id, $factory_id) {
        try {
            $query = "
                SELECT nc.*
                FROM non_conformities nc
                JOIN users u ON nc.reported_by = u.id
                WHERE nc.id = :id AND u.factory_id = :factory_id
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

    public function getAll($factory_id) {
        try {
            $query = "
                SELECT nc.*, u.first_name, u.last_name
                FROM non_conformities nc
                JOIN users u ON nc.reported_by = u.id
                WHERE u.factory_id = :factory_id
                ORDER BY nc.created_at DESC
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
}
