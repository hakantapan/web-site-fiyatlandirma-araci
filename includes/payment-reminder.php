<?php
/**
 * Payment Reminder System for Morpheo Calculator
 */

class MorpheoPaymentReminder {
    
    /**
     * Send payment reminder email for pending appointments
     */
    public static function sendPaymentReminder($appointment_data, $calculator_data, $payment_url) {
        $consultation_fee = get_option('morpheo_consultation_fee', '250');
        
        $email_data = array(
            'customer_name' => $calculator_data->first_name . ' ' . $calculator_data->last_name,
            'appointment_date' => $appointment_data['appointment_date'],
            'appointment_time' => $appointment_data['appointment_time'],
            'payment_url' => $payment_url,
            'consultation_fee' => $consultation_fee,
            'minutes_left' => self::getMinutesLeft($appointment_data['created_at'])
        );
        
        $to = $calculator_data->email;
        $subject = '⚠️ Randevu İptali Yaklaşıyor - Hemen Ödeme Yapın!';
        $message = self::getPaymentReminderTemplate($email_data);
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Morpheo Dijital <info@morpheodijital.com>'
        );
        
        $sent_email = wp_mail($to, $subject, $message, $headers);

        // Send WhatsApp reminder
        MorpheoWhatsAppSender::sendPaymentReminderWhatsApp($appointment_data, $calculator_data, $payment_url);

        return $sent_email;
    }
    
    /**
     * Get payment reminder email template
     */
    private static function getPaymentReminderTemplate($data) {
        return '
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Ödeme Hatırlatması</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f8fafc; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #dc2626, #991b1b); padding: 30px; text-align: center; color: white; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { padding: 30px; }
                .urgent-box { background: #fef2f2; border: 3px solid #dc2626; border-radius: 12px; padding: 25px; margin: 20px 0; text-align: center; animation: pulse 2s infinite; }
                @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.02); } }
                .countdown { font-size: 24px; font-weight: bold; color: #dc2626; margin: 15px 0; }
                .payment-button { 
                    display: inline-block; 
                    background: linear-gradient(135deg, #dc2626, #991b1b); 
                    color: white !important; 
                    padding: 20px 40px; 
                    text-decoration: none !important; 
                    border-radius: 12px; 
                    font-weight: 700; 
                    font-size: 18px; 
                    margin: 20px 0; 
                    box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3); 
                }
                .payment-button:hover { 
                    transform: translateY(-2px); 
                    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4); 
                    color: white !important;
                    text-decoration: none !important;
                }
                .appointment-details { background: #f8fafc; border-radius: 12px; padding: 20px; margin: 20px 0; }
                .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
                .detail-row:last-child { border-bottom: none; }
                .footer { background: #1e293b; color: #94a3b8; padding: 20px; text-align: center; }
                .payment-url {
                    word-break: break-all; 
                    background: #f8fafc; 
                    padding: 15px; 
                    border-radius: 8px; 
                    font-family: monospace;
                    border: 1px solid #e2e8f0;
                    margin: 15px 0;
                }
                .payment-url a {
                    color: #dc2626 !important;
                    text-decoration: underline !important;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>⚠️ Acil: Ödeme Gerekli!</h1>
                    <p>Randevunuz iptal olmak üzere</p>
                </div>
                <div class="content">
                    <div class="urgent-box">
                        <h2 style="color: #dc2626; margin-bottom: 15px;">🚨 Randevunuz İptal Olacak!</h2>
                        <p style="color: #dc2626; font-size: 16px; margin-bottom: 15px;">
                            Sayın ' . esc_html($data['customer_name']) . ', randevunuz için ödeme bekleniyor.
                        </p>
                        <div class="countdown">
                            ⏰ Kalan Süre: ' . $data['minutes_left'] . ' dakika
                        </div>
                        <p style="color: #dc2626; font-weight: bold;">
                            Ödeme yapılmazsa randevunuz otomatik olarak iptal olacaktır!
                        </p>
                    </div>
                    
                    <div style="text-align: center;">
                        <a href="' . esc_url($data['payment_url']) . '" target="_blank" class="payment-button">
                            💳 HEMEN ÖDEME YAP - ' . number_format($data['consultation_fee'], 0, ',', '.') . ' ₺
                        </a>
                    </div>
                    
                    <div style="margin-top: 20px; font-size: 14px; color: #64748b; text-align: center;">
                        <p><strong>Ödeme yapmak için yukarıdaki butona tıklayın veya aşağıdaki linki kopyalayın:</strong></p>
                        <div class="payment-url">
                            <a href="' . esc_url($data['payment_url']) . '" target="_blank">' . esc_html($data['payment_url']) . '</a>
                        </div>
                    </div>
                    
                    <div class="appointment-details">
                        <h3>📅 Randevu Detayları</h3>
                        <div class="detail-row">
                            <span><strong>Tarih:</strong></span>
                            <span>' . date('d F Y, l', strtotime($data['appointment_date'])) . '</span>
                        </div>
                        <div class="detail-row">
                            <span><strong>Saat:</strong></span>
                            <span>' . esc_html($data['appointment_time']) . '</span>
                        </div>
                        <div class="detail-row">
                            <span><strong>Ücret:</strong></span>
                            <span>' . number_format($data['consultation_fee'], 0, ',', '.') . ' ₺</span>
                        </div>
                    </div>
                    
                    <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 15px; margin: 20px 0;">
                        <p style="color: #92400e; margin: 0; text-align: center;">
                            <strong>📞 Yardım:</strong> Ödeme konusunda sorun yaşıyorsanız hemen bizi arayın: 
                            <a href="tel:+905551234567" style="color: #dc2626;">0555 123 45 67</a>
                        </p>
                    </div>
                </div>
                <div class="footer">
                    <p>Morpheo Dijital - Profesyonel Web Tasarım</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Calculate minutes left for payment
     */
    public static function getMinutesLeft($created_at) {
        $created_time = strtotime($created_at);
        $deadline = $created_time + (15 * 60); // 15 minutes
        $now = time();
        $minutes_left = max(0, floor(($deadline - $now) / 60));
        
        return $minutes_left;
    }
    
    /**
     * Check and send payment reminders
     */
    public static function checkPendingPayments() {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        
        // Get appointments pending payment for more than 10 minutes but less than 15
        $pending_appointments = $wpdb->get_results("
            SELECT a.*, r.* FROM $appointments_table a 
            LEFT JOIN $results_table r ON a.calculator_id = r.id 
            WHERE a.payment_status = 'pending' 
            AND a.created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
            AND a.created_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
            AND a.reminder_sent = 0
        ");
        
        foreach ($pending_appointments as $appointment) {
            // Get WooCommerce URL from settings
            $woocommerce_url = get_option('morpheo_woocommerce_url', 'https://morpheodijital.com/satis/checkout-link/?urun=web-site-on-gorusme-randevusu');
            
            // Create payment URL parameters
            $payment_params = array(
                'appointment_id' => $appointment->id,
                'calculator_id' => $appointment->calculator_id,
                'ucret' => $appointment->payment_amount
            );
            
            // Build the payment URL properly
            $separator = strpos($woocommerce_url, '?') !== false ? '&' : '?';
            $payment_url = $woocommerce_url . $separator . http_build_query($payment_params);
            
            $appointment_data = array(
                'appointment_date' => $appointment->appointment_date,
                'appointment_time' => $appointment->appointment_time,
                'created_at' => $appointment->created_at
            );
            
            $sent = self::sendPaymentReminder($appointment_data, $appointment, $payment_url);
            
            if ($sent) {
                // Mark reminder as sent
                $wpdb->update(
                    $appointments_table,
                    array('reminder_sent' => 1),
                    array('id' => $appointment->id),
                    array('%d'),
                    array('%d')
                );
            }
        }
        
        // Cancel appointments older than 15 minutes with pending payment
        $wpdb->update(
            $appointments_table,
            array('payment_status' => 'cancelled'),
            array('payment_status' => 'pending'),
            array('%s'),
            array('%s')
        );
        $wpdb->query("
            UPDATE $appointments_table 
            SET payment_status = 'cancelled' 
            WHERE payment_status = 'pending' 
            AND created_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ");
    }
}
