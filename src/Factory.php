<?php
// src/Factory.php

require_once __DIR__ . '/Database.php';

class Factory {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Yeni bir fabrika hesabı oluşturur.
     * @param array $data
     * @return bool
     */
    public function create($data) {
        try {
            $query = "INSERT INTO factories (factory_name, user_limit) VALUES (:factory_name, :user_limit)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':factory_name', $data['factory_name']);
            $stmt->bindParam(':user_limit', $data['user_limit'], PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Tüm fabrika hesaplarını listeler.
     * @return array
     */
    public function getAll() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM factories ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Bir fabrikayı ID'sine göre siler.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            // ON DELETE CASCADE sayesinde bu fabrikaya bağlı tüm veriler (users, companies etc.) silinecektir.
            $stmt = $this->db->prepare("DELETE FROM factories WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
