<?php
// src/Company.php

require_once __DIR__ . '/Database.php';

class Company {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Belirli bir fabrikaya ait tüm şirketleri (tedarikçileri) getirir.
     * @param int $factory_id
     * @return array
     */
    public function getAllByFactoryId($factory_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM companies WHERE factory_id = :factory_id ORDER BY company_name");
            $stmt->bindParam(':factory_id', $factory_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Belirli bir fabrikaya yeni bir şirket ekler.
     * @param array $data
     * @param int $factory_id
     * @return bool
     */
    public function create($data, $factory_id) {
        try {
            $query = "INSERT INTO companies (company_name, contact_person, contact_email, contact_phone, factory_id)
                      VALUES (:company_name, :contact_person, :contact_email, :contact_phone, :factory_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':company_name', $data['company_name']);
            $stmt->bindParam(':contact_person', $data['contact_person']);
            $stmt->bindParam(':contact_email', $data['contact_email']);
            $stmt->bindParam(':contact_phone', $data['contact_phone']);
            $stmt->bindParam(':factory_id', $factory_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
