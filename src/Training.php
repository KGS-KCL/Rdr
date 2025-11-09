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

    /**
     * Sistemdeki tüm eğitimleri getirir.
     * @return array
     */
    public function getAllTrainings() {
        try {
            $stmt = $this->db->prepare("SELECT id, title FROM trainings ORDER BY title");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Bir kullanıcının eğitim atamalarını günceller.
     * Önce mevcut atamaları siler, sonra yenilerini ekler.
     * @param int $user_id
     * @param array $training_ids
     * @return bool
     */
    public function updateUserTrainingAssignments($user_id, $training_ids = []) {
        try {
            // Veritabanı transaction'ını başlat
            $this->db->beginTransaction();

            // 1. Bu kullanıcı için mevcut tüm atamaları sil
            $delete_stmt = $this->db->prepare("DELETE FROM training_assignments WHERE user_id = :user_id");
            $delete_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $delete_stmt->execute();

            // 2. Yeni atamaları ekle
            if (!empty($training_ids)) {
                $insert_query = "INSERT INTO training_assignments (user_id, training_id, status) VALUES (:user_id, :training_id, 'Atandı')";
                $insert_stmt = $this->db->prepare($insert_query);

                foreach ($training_ids as $training_id) {
                    $insert_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $insert_stmt->bindParam(':training_id', $training_id, PDO::PARAM_INT);
                    $insert_stmt->execute();
                }
            }

            // Değişiklikleri onayla
            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            // Bir hata olursa değişiklikleri geri al
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
}
