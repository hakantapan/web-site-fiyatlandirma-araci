<?php
/**
 * Email Sender for Morpheo Calculator
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once MORPHEO_CALCULATOR_PLUGIN_PATH . 'includes/email-templates.php';

class MorpheoEmailSender {
    
    /**
     * Send customer confirmation email
     */
    public static function sendCustomerConfirmation($appointment_data, $calculator_data, $payment_url = '') {
        $to = $calculator_data->email;
        $subject = 'Randevu Onayı - Morpheo Dijital';
        
        // Get website type names
        $website_types = array(
            'corporate' => 'Kurumsal Website',
            'ecommerce' => 'E-Ticaret Sitesi',
            'blog' => 'Blog/İçerik Sitesi',
            'landing' => 'Özel Kampanya Sayfası'
        );
        
        $project_type = $website_types[$calculator_data->website_type] ?? ucfirst($calculator_data->website_type);
        $estimated_price = number_format($calculator_data->min_price, 0, ',', '.') . ' - ' . number_format($calculator_data->max_price, 0, ',', '.') . ' ₺';
        
        // Prepare email data
        $email_data = array(
            'customer_name' => $calculator_data->first_name . ' ' . $calculator_data->last_name,
            'appointment_date' => $appointment_data['appointment_date'],
            'appointment_time' => $appointment_data['appointment_time'],
            'project_type' => $project_type,
            'estimated_price' => $estimated_price,
            'page_count' => $calculator_data->page_count,
            'payment_url' => $payment_url,
            'selected_features' => array() // You can expand this based on your needs
        );
        
        $payment_status = !empty($payment_url) ? 'pending' : 'paid';
        $message = MorpheoEmailTemplates::getCustomerConfirmationEmail($email_data, $payment_status);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Morpheo Dijital <info@morpheodijital.com>'
        );
        
        $sent = wp_mail($to, $subject, $message, $headers);
        
        if ($sent) {
            error_log('Morpheo Calculator: Customer confirmation email sent to ' . $to);
        } else {
            error_log('Morpheo Calculator: Failed to send customer confirmation email to ' . $to);
        }
        
        return $sent;
    }
    
    /**
     * Send admin notification email
     */
    public static function sendAdminNotification($appointment_data, $calculator_data) {
        $admin_emails = get_option('morpheo_admin_emails', '');
        
        if (empty($admin_emails)) {
            $admin_emails = get_option('admin_email');
        }
        
        $emails = array_map('trim', explode(',', $admin_emails));
        $subject = 'Yeni Randevu Bildirimi - Morpheo Dijital';
        
        // Get website type names
        $website_types = array(
            'corporate' => 'Kurumsal Website',
            'ecommerce' => 'E-Ticaret Sitesi',
            'blog' => 'Blog/İçerik Sitesi',
            'landing' => 'Özel Kampanya Sayfası'
        );
        
        $design_levels = array(
            'basic' => 'Profesyonel & Sade',
            'custom' => 'Markanıza Özel',
            'premium' => 'Lüks & Etkileyici'
        );
        
        $project_type = $website_types[$calculator_data->website_type] ?? ucfirst($calculator_data->website_type);
        $design_level = $design_levels[$calculator_data->design_complexity] ?? ucfirst($calculator_data->design_complexity);
        $estimated_price = number_format($calculator_data->min_price, 0, ',', '.') . ' - ' . number_format($calculator_data->max_price, 0, ',', '.') . ' ₺';
        
        // Prepare email data
        $email_data = array(
            'customer_name' => $calculator_data->first_name . ' ' . $calculator_data->last_name,
            'email' => $calculator_data->email,
            'phone' => $calculator_data->phone,
            'company' => $calculator_data->company,
            'city' => $calculator_data->city,
            'appointment_date' => $appointment_data['appointment_date'],
            'appointment_time' => $appointment_data['appointment_time'],
            'project_type' => $project_type,
            'design_level' => $design_level,
            'business_type' => $project_type,
            'online_payment' => 'Evet', // You can make this dynamic
            'estimated_price' => $estimated_price,
            'page_count' => $calculator_data->page_count,
            'selected_features' => array() // You can expand this based on your needs
        );
        
        $message = MorpheoEmailTemplates::getAdminNotificationEmail($email_data);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Morpheo Calculator <noreply@morpheodijital.com>'
        );
        
        $sent = false;
        foreach ($emails as $email) {
            if (is_email($email)) {
                $result = wp_mail($email, $subject, $message, $headers);
                if ($result) {
                    $sent = true;
                    error_log('Morpheo Calculator: Admin notification email sent to ' . $email);
                } else {
                    error_log('Morpheo Calculator: Failed to send admin notification email to ' . $email);
                }
            }
        }
        
        return $sent;
    }
    
    /**
     * Send appointment reminder email
     */
    public static function sendAppointmentReminder($appointment_data, $calculator_data) {
        $to = $calculator_data->email;
        $subject = 'Randevu Hatırlatması - Morpheo Dijital';
        
        $message = '
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Randevu Hatırlatması - Morpheo Dijital</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #1d4ed8; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .appointment-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>⏰ Randevu Hatırlatması</h1>
                </div>
                <div class="content">
                    <p>Merhaba ' . esc_html($calculator_data->first_name . ' ' . $calculator_data->last_name) . ',</p>
                    
                    <p>Yarın konsültasyon randevunuz bulunmaktadır:</p>
                    
                    <div class="appointment-details">
                        <strong>📅 Tarih:</strong> ' . date('d F Y, l', strtotime($appointment_data['appointment_date'])) . '<br>
                        <strong>🕐 Saat:</strong> ' . esc_html($appointment_data['appointment_time']) . '<br>
                        <strong>⏱️ Süre:</strong> 45-60 dakika
                    </div>
                    
                    <p><strong>Hazırlık için:</strong></p>
                    <ul>
                        <li>Mevcut web siteniz varsa adresini not edin</li>
                        <li>Beğendiğiniz örnek siteleri belirleyin</li>
                        <li>Logo ve marka materyallerinizi hazırlayın</li>
                        <li>Bütçe aralığınızı netleştirin</li>
                    </ul>
                    
                    <p>Görüşmek üzere!</p>
                </div>
                <div class="footer">
                    <p>Morpheo Dijital<br>
                    📞 +90 555 123 45 67 | 📧 info@morpheodijital.com</p>
                </div>
            </div>
        </body>
        </html>';
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Morpheo Dijital <info@morpheodijital.com>'
        );
        
        $sent = wp_mail($to, $subject, $message, $headers);
        
        if ($sent) {
            error_log('Morpheo Calculator: Appointment reminder email sent to ' . $to);
        } else {
            error_log('Morpheo Calculator: Failed to send appointment reminder email to ' . $to);
        }
        
        return $sent;
    }
    
    /**
     * Send payment reminder email
     */
    public static function sendPaymentReminder($appointment_data, $calculator_data, $payment_url) {
        $to = $calculator_data->email;
        $subject = 'ACIL: Randevu Ödeme Hatırlatması - Morpheo Dijital';
        
        $minutes_left = MorpheoPaymentReminder::getMinutesLeft($appointment_data['created_at']);
        
        $message = '
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Ödeme Hatırlatması - Morpheo Dijital</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc2626; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #fef2f2; border: 2px solid #dc2626; }
                .payment-button { 
                    display: inline-block; 
                    background: #dc2626; 
                    color: white; 
                    padding: 15px 30px; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    font-weight: bold; 
                    margin: 15px 0;
                }
                .footer { text-align: center; padding: 20px; color: #666; }
                .urgent { color: #dc2626; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>⚠️ ACIL: Ödeme Gerekli</h1>
                </div>
                <div class="content">
                    <p>Merhaba ' . esc_html($calculator_data->first_name . ' ' . $calculator_data->last_name) . ',</p>
                    
                    <p class="urgent">Randevunuz için ödeme bekleniyor!</p>
                    
                    <p><strong>📅 Randevu:</strong> ' . date('d.m.Y', strtotime($appointment_data['appointment_date'])) . ' - ' . esc_html($appointment_data['appointment_time']) . '</p>
                    <p><strong>⏰ Kalan Süre:</strong> ' . $minutes_left . ' dakika</p>
                    
                    <p class="urgent">Ödeme yapılmazsa randevunuz otomatik olarak iptal olacaktır!</p>
                    
                    <div style="text-align: center;">
                        <a href="' . esc_url($payment_url) . '" class="payment-button" target="_blank">
                            💳 Hemen Ödeme Yap
                        </a>
                    </div>
                    
                    <p style="word-break: break-all; background: white; padding: 10px; border-radius: 5px;">
                        <strong>Ödeme Linki:</strong><br>
                        <a href="' . esc_url($payment_url) . '" target="_blank">' . esc_html($payment_url) . '</a>
                    </p>
                </div>
                <div class="footer">
                    <p>Morpheo Dijital<br>
                    📞 +90 555 123 45 67 | 📧 info@morpheodijital.com</p>
                </div>
            </div>
        </body>
        </html>';
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Morpheo Dijital <noreply@morpheodijital.com>'
        );
        
        $sent = wp_mail($to, $subject, $message, $headers);
        
        if ($sent) {
            error_log('Morpheo Calculator: Payment reminder email sent to ' . $to);
        } else {
            error_log('Morpheo Calculator: Failed to send payment reminder email to ' . $to);
        }
        
        return $sent;
    }
    
    /**
     * Send appointment confirmation email
     */
    public function send_appointment_confirmation($email, $name, $date, $time, $payment_url, $payment_params = array()) {
        $subject = 'Randevunuz Oluşturuldu - Ödeme Bekleniyor';
        $message = MorpheoEmailTemplates::get_appointment_confirmation_template($name, $date, $time, $payment_url, $payment_params);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Morpheo Dijital <noreply@morpheodijital.com>'
        );
        
        $sent = wp_mail($email, $subject, $message, $headers);
        
        if ($sent) {
            error_log("Appointment confirmation email sent to: {$email}");
        } else {
            error_log("Failed to send appointment confirmation email to: {$email}");
        }
        
        return $sent;
    }
    
    /**
     * Send payment reminder email
     */
    public function send_payment_reminder($email, $name, $date, $time, $payment_url, $payment_params = array()) {
        $subject = 'Ödeme Hatırlatması - Randevunuz İptal Edilebilir';
        $message = MorpheoEmailTemplates::get_payment_reminder_template($name, $date, $time, $payment_url, $payment_params);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Morpheo Dijital <noreply@morpheodijital.com>'
        );
        
        $sent = wp_mail($email, $subject, $message, $headers);
        
        if ($sent) {
            error_log("Payment reminder email sent to: {$email}");
        } else {
            error_log("Failed to send payment reminder email to: {$email}");
        }
        
        return $sent;
    }
}
?>
