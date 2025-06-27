<?php
if (!defined('ABSPATH')) {
    exit;
}

class MorpheoPaymentAPI {
    
    private static $api_url = 'https://morpheodijital.com/satis/wp-content/themes/snn-brx-child-theme/siparis-sorgula.php';
    private static $api_key = 't3RcN@f9h$5!ZxLuQ1W#pK7eMv%BdA82';
    
    public static function checkPaymentStatus($email) {
        $url = self::$api_url . '?email=' . urlencode($email) . '&key=' . urlencode(self::$api_key);
        
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
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Morpheo Payment API JSON Error: ' . json_last_error_msg());
            return false;
        }
        
        return $data;
    }
    
    public static function checkAllPendingPayments() {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        
        // Get all pending payments
        $pending_appointments = $wpdb->get_results("
            SELECT a.*, r.email 
            FROM $appointments_table a 
            LEFT JOIN $results_table r ON a.calculator_id = r.id 
            WHERE a.payment_status = 'pending'
            AND a.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        
        $updated_count = 0;
        
        foreach ($pending_appointments as $appointment) {
            $payment_status = self::checkPaymentStatus($appointment->email);
            
            if ($payment_status && isset($payment_status['status']) && $payment_status['status'] === 'paid') {
                // Update appointment status
                $wpdb->update(
                    $appointments_table,
                    array(
                        'payment_status' => 'paid',
                        'updated_at' => current_time('mysql')
                    ),
                    array('id' => $appointment->id)
                );
                
                $updated_count++;
                
                // Log the update
                error_log("Morpheo Payment: Appointment {$appointment->id} marked as paid for {$appointment->email}");
            }
        }
        
        // Cancel appointments older than 24 hours with pending status
        $cancelled_count = $wpdb->query("
            UPDATE $appointments_table 
            SET payment_status = 'cancelled' 
            WHERE payment_status = 'pending' 
            AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        
        if ($cancelled_count > 0) {
            error_log("Morpheo Payment: {$cancelled_count} appointments cancelled due to timeout");
        }
        
        return $updated_count;
    }
    
    public static function getPaymentStats() {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        
        // Get current month stats
        $current_month = date('Y-m');
        
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_appointments,
                SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_appointments,
                SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_appointments,
                SUM(CASE WHEN payment_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_appointments,
                SUM(CASE WHEN payment_status = 'paid' THEN payment_amount ELSE 0 END) as total_revenue
            FROM $appointments_table 
            WHERE DATE_FORMAT(created_at, '%Y-%m') = '$current_month'
        ", ARRAY_A); // Return as associative array instead of object
        
        // Calculate conversion rate
        if ($stats['total_appointments'] > 0) {
            $stats['conversion_rate'] = round(($stats['paid_appointments'] / $stats['total_appointments']) * 100, 1);
        } else {
            $stats['conversion_rate'] = 0;
        }
        
        // Ensure all values are set
        $stats['total_appointments'] = intval($stats['total_appointments']);
        $stats['paid_appointments'] = intval($stats['paid_appointments']);
        $stats['pending_appointments'] = intval($stats['pending_appointments']);
        $stats['cancelled_appointments'] = intval($stats['cancelled_appointments']);
        $stats['total_revenue'] = floatval($stats['total_revenue']);
        
        return $stats;
    }
}
?>
