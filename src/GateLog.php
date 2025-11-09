<?php
// src/GateLog.php

require_once __DIR__ . '/Database.php';

class GateLog {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Bir kullanıcı için yeni bir giriş/çıkış logu oluşturur.
     * @param int $user_id
     * @param string $action ('Giriş' veya 'Çıkış')
     * @param int $security_personnel_id
     * @return bool
     */
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

    /**
     * Bir kullanıcının son log kaydını getirir.
     * @param int $user_id
     * @return mixed|null
     */
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
     * Anlık olarak içeride olan tüm kullanıcıları (giriş yapmış ama çıkış yapmamış) getirir.
     * @return array
     */
    public function getCurrentlyInsideUsers() {
        try {
            // Bu sorgu, her kullanıcı için en son log kaydını bulur ve sadece 'Giriş' olanları listeler.
            $query = "
                SELECT u.first_name, u.last_name, c.company_name, gl.timestamp AS entry_time
                FROM gate_logs gl
                JOIN (
                    SELECT user_id, MAX(timestamp) AS max_timestamp
                    FROM gate_logs
                    GROUP BY user_id
                ) latest_logs ON gl.user_id = latest_logs.user_id AND gl.timestamp = latest_logs.max_timestamp
                JOIN users u ON gl.user_id = u.id
                JOIN companies c ON u.company_id = c.id
                WHERE gl.action = 'Giriş'
                ORDER BY gl.timestamp DESC
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
