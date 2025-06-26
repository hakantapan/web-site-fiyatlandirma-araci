<?php
/**
 * Payment API Integration for Morpheo Calculator
 */

class MorpheoPaymentAPI {
    
    private static $api_url = 'https://morpheodijital.com/satis/wp-content/themes/snn-brx-child-theme/siparis-sorgula.php';
    private static $api_key = 't3RcN@f9h$5!ZxLuQ1W#pK7eMv%BdA82';
    
    /**
     * Check payment status for a customer email
     */
    public static function checkPaymentStatus($email) {
        $url = self::$api_url . '?' . http_build_query(array(
            'email' => $email,
            'key' => self::$api_key
        ));
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Morpheo Calculator Plugin'
            )
        ));
        
        if (is_wp_error($response)) {
            error_log('Morpheo Payment API Error: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code !== 200) {
            error_log('Morpheo Payment API HTTP Error: ' . $status_code);
            return false;
        }
        
        // Parse the response
        return self::parsePaymentResponse($body, $email);
    }
    
    /**
     * Parse payment API response
     */
    private static function parsePaymentResponse($response_body, $email) {
        // Clean the response from PHP notices/warnings
        $clean_response = self::cleanResponse($response_body);
        
        // Try to decode as JSON first
        $json_data = json_decode($clean_response, true);
        
        if ($json_data !== null) {
            return self::processJsonResponse($json_data, $email);
        }
        
        // If not JSON, try to parse as HTML/text
        return self::processTextResponse($clean_response, $email);
    }
    
    /**
     * Clean response from PHP notices and warnings
     */
    private static function cleanResponse($response) {
        // Remove PHP notices and warnings
        $lines = explode("\n", $response);
        $clean_lines = array();
        
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip PHP notices, warnings, and empty lines
            if (empty($line) || 
                strpos($line, 'Notice:') === 0 || 
                strpos($line, 'Warning:') === 0 ||
                strpos($line, 'Fatal error:') === 0) {
                continue;
            }
            $clean_lines[] = $line;
        }
        
        return implode("\n", $clean_lines);
    }
    
    /**
     * Process JSON response
     */
    private static function processJsonResponse($data, $email) {
        if (isset($data['status']) && $data['status'] === 'paid') {
            return array(
                'paid' => true,
                'order_id' => $data['order_id'] ?? null,
                'amount' => $data['amount'] ?? null,
                'date' => $data['date'] ?? null,
                'product' => $data['product'] ?? null
            );
        }
        
        return array('paid' => false);
    }
    
    /**
     * Process text/HTML response
     */
    private static function processTextResponse($response, $email) {
        // Look for payment indicators in the response
        $response_lower = strtolower($response);
        
        // Common payment success indicators
        $success_indicators = array(
            'ödeme alındı',
            'ödeme tamamlandı',
            'payment completed',
            'sipariş tamamlandı',
            'order completed',
            'başarılı',
            'success',
            'paid'
        );
        
        foreach ($success_indicators as $indicator) {
            if (strpos($response_lower, $indicator) !== false) {
                return array(
                    'paid' => true,
                    'response' => $response,
                    'detected_indicator' => $indicator
                );
            }
        }
        
        // Look for order/payment details
        if (preg_match('/sipariş\s*(?:no|id|numarası)?\s*:?\s*(\d+)/i', $response, $matches)) {
            return array(
                'paid' => true,
                'order_id' => $matches[1],
                'response' => $response
            );
        }
        
        return array('paid' => false, 'response' => $response);
    }
    
    /**
     * Check all pending appointments for payment status
     */
    public static function checkAllPendingPayments() {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        
        // Get all pending appointments
        $pending_appointments = $wpdb->get_results("
            SELECT a.*, r.email, r.first_name, r.last_name 
            FROM $appointments_table a 
            LEFT JOIN $results_table r ON a.calculator_id = r.id 
            WHERE a.payment_status = 'pending'
            AND a.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY a.created_at DESC
        ");
        
        $updated_count = 0;
        
        foreach ($pending_appointments as $appointment) {
            if (empty($appointment->email)) {
                continue;
            }
            
            $payment_status = self::checkPaymentStatus($appointment->email);
            
            if ($payment_status && $payment_status['paid']) {
                // Update appointment status to paid
                $update_result = $wpdb->update(
                    $appointments_table,
                    array(
                        'payment_status' => 'paid',
                        'updated_at' => current_time('mysql'),
                        'notes' => 'Ödeme API ile doğrulandı: ' . date('d.m.Y H:i')
                    ),
                    array('id' => $appointment->id),
                    array('%s', '%s', '%s'),
                    array('%d')
                );
                
                if ($update_result) {
                    $updated_count++;
                    
                    // Send payment confirmation email
                    self::sendPaymentConfirmationEmail($appointment, $payment_status);
                    
                    // Log the payment confirmation
                    error_log("Morpheo Calculator: Payment confirmed for appointment ID {$appointment->id}, email: {$appointment->email}");
                }
            }
        }
        
        if ($updated_count > 0) {
            error_log("Morpheo Calculator: Updated {$updated_count} appointments to paid status");
        }
        
        return $updated_count;
    }
    
    /**
     * Send payment confirmation email
     */
    private static function sendPaymentConfirmationEmail($appointment, $payment_info) {
        $subject = '✅ Ödemeniz Alındı - Randevunuz Onaylandı!';
        
        $message = '
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Ödeme Onayı</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f8fafc; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #059669, #047857); padding: 30px; text-align: center; color: white; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { padding: 30px; }
                .success-box { background: #dcfce7; border: 2px solid #16a34a; border-radius: 12px; padding: 25px; margin: 20px 0; text-align: center; }
                .appointment-details { background: #f8fafc; border-radius: 12px; padding: 20px; margin: 20px 0; }
                .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
                .detail-row:last-child { border-bottom: none; }
                .contact-section { text-align: center; margin: 25px 0; }
                .contact-button { display: inline-block; background: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin: 5px; }
                .footer { background: #1e293b; color: #94a3b8; padding: 20px; text-align: center; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>✅ Ödemeniz Alındı!</h1>
                    <p>Randevunuz başarıyla onaylandı</p>
                </div>
                <div class="content">
                    <div class="success-box">
                        <h2 style="color: #166534; margin-bottom: 15px;">🎉 Ödeme Başarılı!</h2>
                        <p style="color: #166534; font-size: 16px; margin: 0;">
                            Sayın ' . esc_html($appointment->first_name . ' ' . $appointment->last_name) . ', 
                            konsültasyon ücreti ödemeniz başarıyla alınmıştır.
                        </p>
                    </div>
                    
                    <div class="appointment-details">
                        <h3>📅 Onaylanan Randevu Detayları</h3>
                        <div class="detail-row">
                            <span><strong>Tarih:</strong></span>
                            <span>' . date('d F Y, l', strtotime($appointment->appointment_date)) . '</span>
                        </div>
                        <div class="detail-row">
                            <span><strong>Saat:</strong></span>
                            <span>' . esc_html($appointment->appointment_time) . '</span>
                        </div>
                        <div class="detail-row">
                            <span><strong>Ödenen Tutar:</strong></span>
                            <span>' . number_format($appointment->payment_amount, 0, ',', '.') . ' ₺</span>
                        </div>
                        <div class="detail-row">
                            <span><strong>Durum:</strong></span>
                            <span style="color: #059669; font-weight: bold;">✅ Onaylandı</span>
                        </div>
                    </div>
                    
                    <div style="background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 12px; padding: 20px; margin: 20px 0;">
                        <h4 style="color: #0c4a6e; margin-bottom: 15px;">📋 Randevuya Hazırlık</h4>
                        <ul style="color: #0c4a6e; margin: 0; padding-left: 20px;">
                            <li>Mevcut web siteniz varsa adresini hazırlayın</li>
                            <li>Beğendiğiniz örnek siteleri belirleyin</li>
                            <li>Logo ve marka materyallerinizi toplayın</li>
                            <li>Proje bütçenizi netleştirin</li>
                        </ul>
                    </div>
                    
                    <div class="contact-section">
                        <p>Randevunuzla ilgili sorularınız için:</p>
                        <a href="tel:+905551234567" class="contact-button">📞 Ara</a>
                        <a href="https://wa.me/905551234567" class="contact-button">💬 WhatsApp</a>
                    </div>
                </div>
                <div class="footer">
                    <p>Morpheo Dijital - Profesyonel Web Tasarım</p>
                    <p>Randevunuza zamanında katılmayı unutmayın!</p>
                </div>
            </div>
        </body>
        </html>';
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Morpheo Dijital <info@morpheodijital.com>'
        );
        
        wp_mail($appointment->email, $subject, $message, $headers);
    }
    
    /**
     * Cancel expired appointments
     */
    public static function cancelExpiredAppointments() {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        
        // Cancel appointments older than 24 hours with pending payment
        $cancelled_count = $wpdb->query("
            UPDATE $appointments_table 
            SET payment_status = 'cancelled', 
                notes = CONCAT(IFNULL(notes, ''), ' - Otomatik iptal: 24 saat ödeme beklendi'),
                updated_at = NOW()
            WHERE payment_status = 'pending' 
            AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        
        if ($cancelled_count > 0) {
            error_log("Morpheo Calculator: Cancelled {$cancelled_count} expired appointments");
        }
        
        return $cancelled_count;
    }
    
    /**
     * Get payment statistics
     */
    public static function getPaymentStats() {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        
        $stats = array();
        
        // Total appointments this month
        $stats['total_appointments'] = $wpdb->get_var("
            SELECT COUNT(*) FROM $appointments_table 
            WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
        ");
        
        // Paid appointments this month
        $stats['paid_appointments'] = $wpdb->get_var("
            SELECT COUNT(*) FROM $appointments_table 
            WHERE payment_status = 'paid' 
            AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
        ");
        
        // Pending appointments
        $stats['pending_appointments'] = $wpdb->get_var("
            SELECT COUNT(*) FROM $appointments_table 
            WHERE payment_status = 'pending'
        ");
        
        // Cancelled appointments this month
        $stats['cancelled_appointments'] = $wpdb->get_var("
            SELECT COUNT(*) FROM $appointments_table 
            WHERE payment_status = 'cancelled' 
            AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
        ");
        
        // Total revenue this month
        $stats['total_revenue'] = $wpdb->get_var("
            SELECT SUM(payment_amount) FROM $appointments_table 
            WHERE payment_status = 'paid' 
            AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
        ") ?: 0;
        
        // Conversion rate
        $stats['conversion_rate'] = $stats['total_appointments'] > 0 
            ? round(($stats['paid_appointments'] / $stats['total_appointments']) * 100, 1) 
            : 0;
        
        return $stats;
    }
}
