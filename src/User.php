<?php
// src/User.php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Session.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * E-posta adresine ve fabrika ID'sine göre bir kullanıcıyı bulur.
     * Süper Adminler (factory_id IS NULL) için özel durum içerir.
     * @param int|null $factory_id
     * @param string $email
     * @return mixed|null
     */
    public function findUserByFactoryAndEmail($factory_id, $email) {
        try {
            $query = "SELECT u.*, r.role_name
                      FROM users u JOIN roles r ON u.role_id = r.id
                      WHERE u.email = :email AND ";

            if ($factory_id === null) { // Süper Admin girişi
                $query .= "u.factory_id IS NULL AND r.role_name = 'Süper Admin'";
            } else { // Normal kullanıcı girişi
                $query .= "u.factory_id = :factory_id";
            }

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            if ($factory_id !== null) {
                $stmt->bindParam(':factory_id', $factory_id, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Kullanıcı girişi işlemini gerçekleştirir (Multi-tenant).
     * @param int $factory_id
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function login($factory_id, $email, $password) {
        // Süper admin girişi için factory_id'yi null yap
        $user = $this->findUserByFactoryAndEmail(empty($factory_id) ? null : $factory_id, $email);

        if ($user && password_verify($password, $user['password'])) {
            if (!$user['is_active']) return false;

            Session::start();
            Session::set('user_id', $user['id']);
            Session::set('user_email', $user['email']);
            Session::set('user_role', $user['role_name']);
            Session::set('user_role_id', $user['role_id']);
            // En önemli: factory_id'yi oturuma kaydet
            Session::set('factory_id', $user['factory_id']);
            Session::set('is_logged_in', true);

            return true;
        }
        return false;
    }

    public function logout() {
        Session::start();
        Session::destroy();
    }

    public static function isLoggedIn() {
        Session::start();
        return Session::get('is_logged_in') === true;
    }

    // Diğer metotlar (hasRole, getAllEmployees, searchEmployees, findUserById) buraya gelecek...
    // Bu metotların da factory_id ile güncellenmesi gerekecek (Planın bir sonraki adımı)

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function hasRole($roles) {
        if (!self::isLoggedIn()) return false;
        $userRole = Session::get('user_role');
        if (is_array($roles)) return in_array($userRole, $roles);
        return $userRole === $roles;
    }

    public function findUserById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    // getAllEmployees ve searchEmployees metotları factory_id'ye göre güncellenmeli.
    // Bu, planın bir sonraki adımı olan "Uygulama Mantığının Güncellenmesi"nde yapılacak.
    public function getAllEmployees() {
        try {
            $factory_id = Session::get('factory_id');
            $role_stmt = $this->db->prepare("SELECT id FROM roles WHERE role_name = 'Alt Kullanıcı'");
            $role_stmt->execute();
            $role = $role_stmt->fetch();
            if (!$role) return [];

            $stmt = $this->db->prepare("SELECT id, first_name, last_name, tckn FROM users WHERE role_id = :role_id AND factory_id = :factory_id ORDER BY first_name, last_name");
            $stmt->bindParam(':role_id', $role['id'], PDO::PARAM_INT);
            $stmt->bindParam(':factory_id', $factory_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function searchEmployees($term) {
        try {
            $factory_id = Session::get('factory_id');
            $term = "%" . $term . "%";
            $query = "
                SELECT u.id, u.first_name, u.last_name, u.tckn, u.staff_id, c.company_name
                FROM users u
                JOIN roles r ON u.role_id = r.id
                LEFT JOIN companies c ON u.company_id = c.id
                WHERE u.factory_id = :factory_id AND r.role_name = 'Alt Kullanıcı' AND (
                    u.first_name LIKE :term OR
                    u.last_name LIKE :term OR
                    u.tckn LIKE :term OR
                    u.staff_id LIKE :term OR
                    CONCAT(u.first_name, ' ', u.last_name) LIKE :term
                )
                LIMIT 10
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':factory_id', $factory_id, PDO::PARAM_INT);
            $stmt->bindParam(':term', $term);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
}
