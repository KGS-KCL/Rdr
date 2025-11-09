<?php
// src/Training.php

require_once __DIR__ . '/Database.php';

class Training {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Belirli bir kullanıcı ID'sine atanmış tüm eğitimleri getirir.
     * @param int $user_id
     * @return array
     */
    public function getAssignedTrainingsByUserId($user_id) {
        try {
            $query = "
                SELECT
                    t.id,
                    t.title,
                    t.description,
                    ta.status,
                    ta.completed_at
                FROM
                    training_assignments ta
                JOIN
                    trainings t ON ta.training_id = t.id
                WHERE
                    ta.user_id = :user_id
                ORDER BY
                    ta.assigned_at DESC
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * ID'ye göre tek bir eğitimin detaylarını getirir.
     * @param int $id
     * @return mixed|null
     */
    public function findTrainingById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM trainings WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }
}
