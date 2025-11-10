<?php
// src/Csrf.php

// Oturumun zaten başlatıldığını varsayarız
// Session::start();

class Csrf {

    /**
     * Oturum için benzersiz bir CSRF token'ı oluşturur ve kaydeder.
     * Eğer zaten bir token varsa, onu kullanır.
     * @return string
     */
    public static function generateToken() {
        if (empty($_SESSION['csrf_token'])) {
            // Güçlü, rastgele bir token oluştur
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Mevcut CSRF token'ını döndürür.
     * @return string|null
     */
    public static function getToken() {
        return $_SESSION['csrf_token'] ?? null;
    }

    /**
     * Gönderilen bir token'ın oturumdakiyle eşleşip eşleşmediğini doğrular.
     * @param string $posted_token Formdan gelen token.
     * @return bool
     */
    public static function validateToken($posted_token) {
        if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $posted_token)) {
            return true;
        }
        return false;
    }

    /**
     * Tüm POST formlarına eklenecek gizli input alanını oluşturur.
     * @return string
     */
    public static function getInputField() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}
