<?php
// src/Session.php

class Session {
    /**
     * Oturumu güvenli bir şekilde başlatır.
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            // Cookie ayarlarını daha güvenli hale getir
            session_set_cookie_params([
                'lifetime' => 86400, // 1 gün
                'path' => '/',
                'domain' => '', // Mevcut domain
                'secure' => isset($_SERVER['HTTPS']), // Sadece HTTPS üzerinden
                'httponly' => true, // JavaScript erişimini engelle
                'samesite' => 'Lax'
            ]);
            session_start();
        }

        // Oturumun yeniden oluşturulması (Session Hijacking önlemi)
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) { // 30 dakikada bir
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }

    /**
     * Oturuma veri ekler.
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Oturumdan veri alır.
     * @param string $key
     * @return mixed|null
     */
    public static function get($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    /**
     * Oturumdan bir anahtarı siler.
     * @param string $key
     */
    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Oturumu sonlandırır.
     */
    public static function destroy() {
        // Tüm oturum değişkenlerini temizle
        $_SESSION = array();

        // Oturum çerezini sil
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }
}
