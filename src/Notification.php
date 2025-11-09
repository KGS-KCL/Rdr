<?php
// src/Notification.php

class Notification {

    /**
     * Kullanıcıya bilgilendirme e-postası gönderir.
     * Gelecekte bu metot, SMS gibi farklı kanalları da destekleyebilir.
     *
     * @param string $to E-posta'nın gönderileceği adres.
     * @param string $subject E-posta'nın konusu.
     * @param string $message E-posta'nın içeriği (HTML olabilir).
     * @param string $from Gönderen adresi.
     * @return bool Gönderim başarılı ise true, değilse false.
     */
    public function sendEmail($to, $subject, $message, $from = 'no-reply@kgs-isg.com') {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: <' . $from . '>' . "\r\n";

        // PHP'nin mail() fonksiyonunu kullanarak e-postayı gönder
        try {
            if (mail($to, $subject, $message, $headers)) {
                return true;
            } else {
                // Hata durumunda loglama yapılabilir
                error_log("E-posta gönderilemedi: $to");
                return false;
            }
        } catch (Exception $e) {
            error_log("E-posta gönderim hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Güvenlik bilgilendirme linki için e-posta içeriğini oluşturur.
     * @param string $user_name
     * @param string $unique_link
     * @return string
     */
    public function createSafetyInfoEmailBody($user_name, $unique_link) {
        return "
            <html>
            <head><title>Güvenlik Bilgilendirmesi</title></head>
            <body>
                <h3>Merhaba " . htmlspecialchars($user_name) . ",</h3>
                <p>Fabrikaya/Şantiyeye hoş geldiniz. Girişiniz onaylanmıştır.</p>
                <p>Lütfen aşağıdakı linke tıklayarak saha içerisindeki uymanız gereken kurallar, acil durum numaraları ve diğer önemli güvenlik bilgilerini içeren size özel sayfanızı görüntüleyin.</p>
                <p><a href='" . $unique_link . "'>Güvenlik Bilgileri Linkiniz</a></p>
                <p>Bu link size özeldir. Lütfen kimseyle paylaşmayın.</p>
                <br>
                <p>İyi çalışmalar dileriz.</p>
            </body>
            </html>
        ";
    }

    // Gelecekte SMS gönderme metodu buraya eklenebilir.
    // public function sendSms($phoneNumber, $message) { ... }
}
