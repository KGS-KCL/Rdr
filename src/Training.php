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
            // Bu sorgu dolaylı olarak factory_id'ye bağlıdır, çünkü user_id fabrikaya özeldir.
            $query = "
                SELECT t.id, t.title, t.description, ta.status, ta.completed_at
                FROM training_assignments ta
                JOIN trainings t ON ta.training_id = t.id
                WHERE ta.user_id = :user_id
                ORDER BY ta.assigned_at DESC
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
     * ID'ye ve factory_id'ye göre tek bir eğitimin detaylarını getirir.
     * @param int $id
     * @param int $factory_id
     * @return mixed|null
     */
    public function findTrainingById($id, $factory_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM trainings WHERE id = :id AND factory_id = :factory_id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':factory_id', $factory_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Belirli bir fabrikadaki tüm eğitimleri getirir.
     * @param int $factory_id
     * @return array
     */
    public function getAllTrainingsByFactory($factory_id) {
        try {
            $stmt = $this->db->prepare("SELECT id, title FROM trainings WHERE factory_id = :factory_id ORDER BY title");
            $stmt->bindParam(':factory_id', $factory_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function updateUserTrainingAssignments($user_id, $training_ids = []) {
        try {
            $this->db->beginTransaction();
            $delete_stmt = $this->db->prepare("DELETE FROM training_assignments WHERE user_id = :user_id");
            $delete_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $delete_stmt->execute();

            if (!empty($training_ids)) {
                $insert_query = "INSERT INTO training_assignments (user_id, training_id, status) VALUES (:user_id, :training_id, 'Atandı')";
                $insert_stmt = $this->db->prepare($insert_query);

                foreach ($training_ids as $training_id) {
                    $insert_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $insert_stmt->bindParam(':training_id', $training_id, PDO::PARAM_INT);
                    $insert_stmt->execute();
                }
            }
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
}
