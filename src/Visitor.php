<?php
// src/Visitor.php

require_once __DIR__ . '/Database.php';

class Visitor {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Yeni bir ziyaretçi kaydı oluşturur.
     * @param array $data
     * @param int $factory_id
     * @return bool
     */
    public function createVisitor($data, $factory_id) {
        try {
            // `recorded_by` kullanıcısının o fabrikaya ait olduğunu varsayıyoruz.
            $query = "INSERT INTO visitors (full_name, company, visiting_department, reason_for_visit, recorded_by)
                      VALUES (:full_name, :company, :visiting_department, :reason_for_visit, :recorded_by)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':full_name', $data['full_name']);
            $stmt->bindParam(':company', $data['company']);
            $stmt->bindParam(':visiting_department', $data['visiting_department']);
            $stmt->bindParam(':reason_for_visit', $data['reason_for_visit']);
            $stmt->bindParam(':recorded_by', $data['recorded_by'], PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Belirli bir fabrikadaki aktif ziyaretçileri getirir.
     * @param int $factory_id
     * @return array
     */
    public function getActiveVisitors($factory_id) {
        try {
            $query = "
                SELECT v.* FROM visitors v
                JOIN users u ON v.recorded_by = u.id
                WHERE v.exit_time IS NULL AND u.factory_id = :factory_id
                ORDER BY v.entry_time DESC
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
     * Bir ziyaretçinin çıkışını işaretler (factory_id kontrolü ile).
     * @param int $visitor_id
     * @param int $factory_id
     * @return bool
     */
    public function markAsExited($visitor_id, $factory_id) {
        try {
            // Sadece o fabrikaya ait bir güvenlik görevlisinin kaydettiği ziyaretçiyi güncelleyebilmesini sağla
            $query = "
                UPDATE visitors v
                JOIN users u ON v.recorded_by = u.id
                SET v.exit_time = CURRENT_TIMESTAMP
                WHERE v.id = :visitor_id AND v.exit_time IS NULL AND u.factory_id = :factory_id
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':visitor_id', $visitor_id, PDO::PARAM_INT);
            $stmt->bindParam(':factory_id', $factory_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
