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
     * E-posta adresine göre bir kullanıcıyı bulur.
     * @param string $email
     * @return mixed|null
     */
    public function findUserByEmail($email) {
        try {
            $stmt = $this->db->prepare("SELECT users.*, roles.role_name FROM users JOIN roles ON users.role_id = roles.id WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            // Hata loglanabilir
            return null;
        }
    }

    /**
     * Kullanıcı girişi işlemini gerçekleştirir.
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function login($email, $password) {
        $user = $this->findUserByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            // Kullanıcı aktif mi kontrolü
            if (!$user['is_active']) {
                return false;
            }

            // Oturumu başlat ve kullanıcı bilgilerini kaydet
            Session::start();
            Session::set('user_id', $user['id']);
            Session::set('user_email', $user['email']);
            Session::set('user_role', $user['role_name']);
            Session::set('user_role_id', $user['role_id']);
            Session::set('is_logged_in', true);

            return true;
        }

        return false;
    }

    /**
     * Kullanıcı çıkış işlemini gerçekleştirir.
     */
    public function logout() {
        Session::start();
        Session::destroy();
    }

    /**
     * Oturumun açık olup olmadığını kontrol eder.
     * @return bool
     */
    public static function isLoggedIn() {
        Session::start();
        return Session::get('is_logged_in') === true;
    }

    /**
     * Şifre oluşturmak için yardımcı bir metot.
     * @param string $password
     * @return string
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Belirli bir role sahip olup olmadığını kontrol eder.
     * @param string|array $roles Kontrol edilecek rol(ler)
     * @return bool
     */
    public static function hasRole($roles) {
        if (!self::isLoggedIn()) {
            return false;
        }

        $userRole = Session::get('user_role');
        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }

        return $userRole === $roles;
    }

}
