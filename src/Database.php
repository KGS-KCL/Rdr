<?php

// src/Database.php

class Database {
    // Sınıfın tek örneğini tutacak olan statik özellik
    private static $instance = null;
    private $connection;

    // Veritabanı bağlantı bilgileri
    private $host = DB_HOST;
    private $dbname = DB_NAME;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $charset = DB_CHARSET;

    /**
     * Kurucu metot, dışarıdan erişilemez (private).
     * Bu, Singleton deseninin bir gereğidir.
     */
    private function __construct() {
        // DSN (Data Source Name) string'ini oluştur
        $dsn = "mysql:host=$this->host;dbname=$this->dbname;charset=$this->charset";

        // PDO seçenekleri
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Hataları yakalamak için
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Sonuçları assoc array olarak getirmek için
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Gerçek prepared statement'lar kullanmak için
        ];

        try {
            // PDO bağlantısını oluştur
            $this->connection = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // Bağlantı hatası durumunda programı sonlandır ve hata mesajı göster
            // Canlı ortamda bu mesaj loglanmalı, kullanıcıya gösterilmemeli.
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Sınıfın tek örneğini döndüren statik metot.
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance == null) {
            // config/database.php dosyasını dahil et
            require_once __DIR__ . '/../config/database.php';
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * PDO bağlantı nesnesini döndüren metot.
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Klonlamayı engellemek için.
     */
    private function __clone() {}

    /**
     * Unserialize edilmesini engellemek için.
     */
    public function __wakeup() {}
}
