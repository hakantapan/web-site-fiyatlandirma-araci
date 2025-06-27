<?php
/**
 * Payment Reminder System for Morpheo Calculator
 */

class MorpheoPaymentReminder {
    
    /**
     * Send payment reminder to customers with pending payments
     */
    public static function sendPaymentReminder($appointment_data, $calculator_data) {
        // Get WooCommerce URL from settings
        $woocommerce_url = get_option('morpheo_woocommerce_url', 'https://morpheodijital.com/satis/checkout-link/?urun=web-site-on-gorusme-randevusu');
        
        // Get website type names
        $website_types = array(
            'corporate' => 'Kurumsal Website',
            'ecommerce' => 'E-Ticaret Sitesi',
            'blog' => 'Blog/İçerik Sitesi',
            'landing' => 'Özel Kampanya Sayfası'
        );
        
        $project_type = $website_types[$calculator_data->website_type] ?? ucfirst($calculator_data->website_type);
        $estimated_price = number_format($calculator_data->min_price, 0, ',', '.') . ' - ' . number_format($calculator_data->max_price, 0, ',', '.') . ' ₺';
        $consultation_fee = get_option('morpheo_consultation_fee', '250');
        
        // Create comprehensive payment URL parameters
        $payment_params = array(
            'randevu_tarihi' => $appointment_data['appointment_date'],
            'randevu_saati' => $appointment_data['appointment_time'],
            'musteri_adi' => $calculator_data->first_name . ' ' . $calculator_data->last_name,
            'musteri_email' => $calculator_data->email,
            'musteri_telefon' => $calculator_data->phone,
            'proje_tipi' => $project_type,
            'tahmini_fiyat' => $estimated_price,
            'calculator_id' => $appointment_data['calculator_id'],
            'appointment_id' => $appointment_data['appointment_id'],
            'ucret' => $consultation_fee,
            'urun' => 'web-site-konsultasyon'
        );
        
        // Build the payment URL properly
        $separator = strpos($woocommerce_url, '?') !== false ? '&' : '?';
        $payment_url = $woocommerce_url . $separator . http_build_query($payment_params, '', '&', PHP_QUERY_RFC3986);
        
        // Log the generated payment URL for debugging
        error_log('Morpheo Payment Reminder: Generated Payment URL: ' . $payment_url);
        
        // Send email reminder
        $email_sent = MorpheoEmailSender::sendPaymentReminder($appointment_data, $calculator_data, $payment_url);
        
        // Send WhatsApp reminder
        $whatsapp_sent = MorpheoWhatsAppSender::sendPaymentReminderWhatsApp($appointment_data, $calculator_data, $payment_url);
        
        return $email_sent || $whatsapp_sent;
    }
    
    /**
     * Check all pending payments and send reminders
     */
    public static function checkPendingPayments() {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        
        // Get appointments that are pending for more than 5 minutes but less than 15 minutes
        $pending_appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, r.* FROM $appointments_table a 
             LEFT JOIN $results_table r ON a.calculator_id = r.id 
             WHERE a.payment_status = 'pending' 
             AND a.created_at BETWEEN %s AND %s
             AND a.reminder_sent = 0",
            date('Y-m-d H:i:s', strtotime('-15 minutes')),
            date('Y-m-d H:i:s', strtotime('-5 minutes'))
        ));
        
        foreach ($pending_appointments as $appointment) {
            $appointment_data = array(
                'appointment_id' => $appointment->id,
                'calculator_id' => $appointment->calculator_id,
                'appointment_date' => $appointment->appointment_date,
                'appointment_time' => $appointment->appointment_time,
                'created_at' => $appointment->created_at
            );
            
            $reminder_sent = self::sendPaymentReminder($appointment_data, $appointment);
            
            if ($reminder_sent) {
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
        
        // Cancel appointments that are pending for more than 15 minutes
        $expired_appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $appointments_table 
             WHERE payment_status = 'pending' 
             AND created_at < %s",
            date('Y-m-d H:i:s', strtotime('-15 minutes'))
        ));
        
        foreach ($expired_appointments as $appointment) {
            $wpdb->update(
                $appointments_table,
                array(
                    'payment_status' => 'cancelled',
                    'notes' => 'Otomatik iptal: 15 dakika içinde ödeme yapılmadı'
                ),
                array('id' => $appointment->id),
                array('%s', '%s'),
                array('%d')
            );
            
            error_log('Morpheo Calculator: Appointment ' . $appointment->id . ' cancelled due to payment timeout');
        }
    }
    
    /**
     * Get minutes left for payment
     */
    public static function getMinutesLeft($created_at) {
        $created_time = strtotime($created_at);
        $current_time = time();
        $elapsed_minutes = ($current_time - $created_time) / 60;
        $remaining_minutes = 15 - $elapsed_minutes;
        
        return max(0, round($remaining_minutes));
    }
    
    /**
     * Check if appointment payment is expired
     */
    public static function isPaymentExpired($created_at) {
        return self::getMinutesLeft($created_at) <= 0;
    }
}
