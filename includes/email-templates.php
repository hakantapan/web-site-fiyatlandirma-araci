<?php
if (!defined('ABSPATH')) {
    exit;
}

class MorpheoEmailTemplates {
    
    public static function get_appointment_confirmation_template($name, $date, $time, $payment_url, $payment_params = array()) {
        $formatted_date = date('d F Y', strtotime($date));
        $formatted_time = date('H:i', strtotime($time));
        
        $template = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Randevu Onayı</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; margin: -20px -20px 20px -20px; }
                .header h1 { margin: 0; font-size: 28px; }
                .content { padding: 20px 0; }
                .appointment-details { background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea; }
                .appointment-details h3 { margin-top: 0; color: #667eea; }
                .detail-row { display: flex; justify-content: space-between; margin: 10px 0; padding: 8px 0; border-bottom: 1px solid #eee; }
                .detail-label { font-weight: bold; color: #555; }
                .detail-value { color: #333; }
                .payment-section { background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
                .payment-button { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; font-size: 16px; margin: 10px 0; transition: transform 0.2s; }
                .payment-button:hover { transform: translateY(-2px); color: white; text-decoration: none; }
                .warning { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px; }
                .contact-info { background-color: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; }
                @media only screen and (max-width: 600px) {
                    .container { padding: 10px; }
                    .header { padding: 20px; margin: -10px -10px 20px -10px; }
                    .header h1 { font-size: 24px; }
                    .detail-row { flex-direction: column; }
                    .payment-button { display: block; margin: 10px 0; padding: 12px 20px; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎉 Randevunuz Oluşturuldu!</h1>
                    <p>Merhaba ' . esc_html($name) . ', randevunuz başarıyla kaydedildi.</p>
                </div>
                
                <div class="content">
                    <div class="appointment-details">
                        <h3>📅 Randevu Detayları</h3>
                        <div class="detail-row">
                            <span class="detail-label">Tarih:</span>
                            <span class="detail-value">' . $formatted_date . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Saat:</span>
                            <span class="detail-value">' . $formatted_time . '</span>
                        </div>
                    </div>
                    
                    <div class="payment-section">
                        <h3>💳 Ödeme Tamamlama</h3>
                        <p>Randevunuzu onaylamak için lütfen ödemenizi tamamlayın:</p>
                        <a href="' . esc_url($payment_url) . '" class="payment-button" target="_blank" style="color: white; text-decoration: none;">
                            💳 Ödemeyi Tamamla
                        </a>
                        <p style="margin-top: 15px; font-size: 14px;">
                            Butona tıklayamıyorsanız, aşağıdaki linki kopyalayıp tarayıcınıza yapıştırın:<br>
                            <a href="' . esc_url($payment_url) . '" target="_blank" style="word-break: break-all; color: #667eea;">' . esc_url($payment_url) . '</a>
                        </p>
                    </div>
                    
                    <div class="warning">
                        <strong>⚠️ Önemli:</strong> Ödemenizi 24 saat içinde tamamlamazsanız randevunuz otomatik olarak iptal edilecektir.
                    </div>
                    
                    <div class="contact-info">
                        <h4>📞 İletişim</h4>
                        <p>Herhangi bir sorunuz varsa bizimle iletişime geçebilirsiniz:</p>
                        <p>
                            <strong>E-posta:</strong> info@morpheodijital.com<br>
                            <strong>Telefon:</strong> +90 XXX XXX XX XX
                        </p>
                    </div>
                </div>
                
                <div class="footer">
                    <p>Bu e-posta Morpheo Dijital tarafından gönderilmiştir.</p>
                    <p>© 2024 Morpheo Dijital. Tüm hakları saklıdır.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $template;
    }
    
    public static function get_payment_reminder_template($name, $date, $time, $payment_url, $payment_params = array()) {
        $formatted_date = date('d F Y', strtotime($date));
        $formatted_time = date('H:i', strtotime($time));
        
        $template = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Ödeme Hatırlatması</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; margin: -20px -20px 20px -20px; }
                .header h1 { margin: 0; font-size: 28px; }
                .content { padding: 20px 0; }
                .appointment-details { background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #f39c12; }
                .appointment-details h3 { margin-top: 0; color: #f39c12; }
                .detail-row { display: flex; justify-content: space-between; margin: 10px 0; padding: 8px 0; border-bottom: 1px solid #eee; }
                .detail-label { font-weight: bold; color: #555; }
                .detail-value { color: #333; }
                .payment-section { background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
                .payment-button { display: inline-block; background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; font-size: 16px; margin: 10px 0; transition: transform 0.2s; }
                .payment-button:hover { transform: translateY(-2px); color: white; text-decoration: none; }
                .urgent-warning { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center; }
                .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px; }
                @media only screen and (max-width: 600px) {
                    .container { padding: 10px; }
                    .header { padding: 20px; margin: -10px -10px 20px -10px; }
                    .header h1 { font-size: 24px; }
                    .detail-row { flex-direction: column; }
                    .payment-button { display: block; margin: 10px 0; padding: 12px 20px; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>⏰ Ödeme Hatırlatması</h1>
                    <p>Merhaba ' . esc_html($name) . ', randevunuz için ödeme bekleniyor.</p>
                </div>
                
                <div class="content">
                    <div class="appointment-details">
                        <h3>📅 Randevu Detayları</h3>
                        <div class="detail-row">
                            <span class="detail-label">Tarih:</span>
                            <span class="detail-value">' . $formatted_date . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Saat:</span>
                            <span class="detail-value">' . $formatted_time . '</span>
                        </div>
                    </div>
                    
                    <div class="payment-section">
                        <h3>💳 Ödemenizi Tamamlayın</h3>
                        <p>Randevunuzu kaybetmemek için lütfen ödemenizi tamamlayın:</p>
                        <a href="' . esc_url($payment_url) . '" class="payment-button" target="_blank" style="color: white; text-decoration: none;">
                            💳 Hemen Öde
                        </a>
                        <p style="margin-top: 15px; font-size: 14px;">
                            Butona tıklayamıyorsanız, aşağıdaki linki kopyalayıp tarayıcınıza yapıştırın:<br>
                            <a href="' . esc_url($payment_url) . '" target="_blank" style="word-break: break-all; color: #f39c12;">' . esc_url($payment_url) . '</a>
                        </p>
                    </div>
                    
                    <div class="urgent-warning">
                        <h4>🚨 Acil: Randevunuz İptal Edilebilir!</h4>
                        <p>Ödemenizi yakında tamamlamazsanız randevunuz otomatik olarak iptal edilecektir.</p>
                    </div>
                </div>
                
                <div class="footer">
                    <p>Bu e-posta Morpheo Dijital tarafından gönderilmiştir.</p>
                    <p>© 2024 Morpheo Dijital. Tüm hakları saklıdır.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $template;
    }
}
?>
