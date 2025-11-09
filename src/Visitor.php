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
     * @return bool
     */
    public function createVisitor($data) {
        try {
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
     * Henüz çıkış yapmamış tüm ziyaretçileri getirir.
     * @return array
     */
    public function getActiveVisitors() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM visitors WHERE exit_time IS NULL ORDER BY entry_time DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Tüm ziyaretçi kayıtlarını (geçmiş ve aktif) getirir.
     * @return array
     */
    public function getAllVisitors() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM visitors ORDER BY entry_time DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Bir ziyaretçinin çıkış saatini güncelleyerek çıkış yaptığını işaretler.
     * @param int $visitor_id
     * @return bool
     */
    public function markAsExited($visitor_id) {
        try {
            $query = "UPDATE visitors SET exit_time = CURRENT_TIMESTAMP WHERE id = :id AND exit_time IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $visitor_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
