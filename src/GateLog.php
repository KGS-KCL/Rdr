<?php
// src/GateLog.php

require_once __DIR__ . '/Database.php';

class GateLog {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createLog($user_id, $action, $security_personnel_id) {
        try {
            $query = "INSERT INTO gate_logs (user_id, action, security_personnel_id) VALUES (:user_id, :action, :security_personnel_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':security_personnel_id', $security_personnel_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getLastLogByUserId($user_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM gate_logs WHERE user_id = :user_id ORDER BY timestamp DESC LIMIT 1");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Belirli bir fabrikada anlık olarak içeride olan tüm kullanıcıları getirir.
     * @param int $factory_id
     * @return array
     */
    public function getCurrentlyInsideUsers($factory_id) {
        try {
            $query = "
                SELECT u.first_name, u.last_name, c.company_name, gl.timestamp AS entry_time
                FROM gate_logs gl
                JOIN (
                    SELECT user_id, MAX(timestamp) AS max_timestamp
                    FROM gate_logs
                    GROUP BY user_id
                ) latest_logs ON gl.user_id = latest_logs.user_id AND gl.timestamp = latest_logs.max_timestamp
                JOIN users u ON gl.user_id = u.id
                LEFT JOIN companies c ON u.company_id = c.id
                WHERE gl.action = 'Giriş' AND u.factory_id = :factory_id
                ORDER BY gl.timestamp DESC
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
