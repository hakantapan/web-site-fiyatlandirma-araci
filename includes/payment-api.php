<?php
/**
 * Payment API Integration for Morpheo Calculator
 */

class MorpheoPaymentAPI {
    
    private static $api_url = 'https://morpheodijital.com/satis/wp-content/themes/snn-brx-child-theme/siparis-sorgula.php';
    private static $api_key = 't3RcN@f9h$5!ZxLuQ1W#pK7eMv%BdA82';
    
    /**
     * Check payment status for a given email
     */
    public static function checkPaymentStatus($email) {
        if (empty($email)) {
            return false;
        }
        
        $url = self::$api_url . '?' . http_build_query(array(
            'email' => $email,
            'key' => self::$api_key
        ));
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Morpheo Calculator Payment Checker'
            )
        ));
        
        if (is_wp_error($response)) {
            error_log('Morpheo Payment API Error: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code !== 200) {
            error_log('Morpheo Payment API HTTP Error: ' . $status_code . ' - Response: ' . $body);
            return false;
        }
        
        // Try to decode JSON response
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Morpheo Payment API JSON Error: ' . json_last_error_msg() . ' - Raw: ' . $body);
            return false;
        }
        
        // Check if payment is successful
        $paid = false;
        $order_info = array();
        
        if (isset($data['status']) && $data['status'] === 'success' && isset($data['orders'])) {
            foreach ($data['orders'] as $order) {
                if (isset($order['status']) && strtolower($order['status']) === 'completed') {
                    $paid = true;
                    $order_info = $order;
                    break;
                }
            }
        }
        
        return array(
            'paid' => $paid,
            'order_info' => $order_info,
            'raw_response' => $data
        );
    }
    
    /**
     * Check all pending payments and update status
     */
    public static function checkAllPendingPayments() {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        
        // Get all pending appointments
        $pending_appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, r.email FROM $appointments_table a 
             LEFT JOIN $results_table r ON a.calculator_id = r.id 
             WHERE a.payment_status = 'pending' 
             AND a.created_at > %s",
            date('Y-m-d H:i:s', strtotime('-24 hours'))
        ));
        
        foreach ($pending_appointments as $appointment) {
            if (empty($appointment->email)) {
                continue;
            }
            
            $payment_status = self::checkPaymentStatus($appointment->email);
            
            if ($payment_status && $payment_status['paid']) {
                // Update appointment status
                $update_result = $wpdb->update(
                    $appointments_table,
                    array(
                        'payment_status' => 'paid',
                        'updated_at' => current_time('mysql'),
                        'notes' => 'Otomatik ödeme kontrolü: ' . date('d.m.Y H:i')
                    ),
                    array('id' => $appointment->id),
                    array('%s', '%s', '%s'),
                    array('%d')
                );
                
                if ($update_result) {
                    error_log('Morpheo Calculator: Payment confirmed for appointment ' . $appointment->id);
                    
                    // Get calculator data for WhatsApp confirmation
                    $calculator_data = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM $results_table WHERE email = %s",
                        $appointment->email
                    ));
                    
                    if ($calculator_data) {
                        // Send payment confirmation WhatsApp
                        MorpheoWhatsAppSender::sendPaymentConfirmationWhatsApp($appointment, $calculator_data);
                    }
                }
            }
        }
    }
    
    /**
     * Cancel expired appointments
     */
    public static function cancelExpiredAppointments() {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        
        // Cancel appointments that are pending for more than 15 minutes
        $expired_count = $wpdb->query($wpdb->prepare(
            "UPDATE $appointments_table 
             SET payment_status = 'cancelled', 
                 notes = 'Otomatik iptal: 15 dakika içinde ödeme yapılmadı',
                 updated_at = %s
             WHERE payment_status = 'pending' 
             AND created_at < %s",
            current_time('mysql'),
            date('Y-m-d H:i:s', strtotime('-15 minutes'))
        ));
        
        if ($expired_count > 0) {
            error_log('Morpheo Calculator: ' . $expired_count . ' expired appointments cancelled');
        }
        
        return $expired_count;
    }
    
    /**
     * Get payment statistics
     */
    public static function getPaymentStats($period = 'month') {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        
        switch ($period) {
            case 'today':
                $date_condition = "DATE(created_at) = CURDATE()";
                break;
            case 'week':
                $date_condition = "created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                break;
            case 'month':
            default:
                $date_condition = "created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
        }
        
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_appointments,
                SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_appointments,
                SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_appointments,
                SUM(CASE WHEN payment_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_appointments,
                SUM(CASE WHEN payment_status = 'paid' THEN payment_amount ELSE 0 END) as total_revenue
             FROM $appointments_table 
             WHERE %s",
            $date_condition
        ));
        
        return $stats;
    }
}
