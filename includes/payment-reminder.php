<?php
if (!defined('ABSPATH')) {
    exit;
}

class MorpheoPaymentReminder {
    
    public static function send_reminders() {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        
        // Get appointments that need reminders (created 2+ hours ago, still pending, no reminder sent)
        $appointments = $wpdb->get_results("
            SELECT a.*, r.first_name, r.last_name, r.email, r.phone, r.website_type_tr, r.price_range
            FROM $appointments_table a 
            LEFT JOIN $results_table r ON a.calculator_id = r.id 
            WHERE a.payment_status = 'pending' 
            AND a.reminder_sent = 0 
            AND a.created_at < DATE_SUB(NOW(), INTERVAL 2 HOUR)
            AND a.created_at > DATE_SUB(NOW(), INTERVAL 22 HOUR)
        ");
        
        $reminder_count = 0;
        
        foreach ($appointments as $appointment) {
            // Create payment parameters for reminder
            $payment_params = array(
                'randevu_tarihi' => $appointment->appointment_date,
                'randevu_saati' => $appointment->appointment_time,
                'musteri_adi' => $appointment->first_name . ' ' . $appointment->last_name,
                'musteri_email' => $appointment->email,
                'musteri_telefon' => $appointment->phone,
                'proje_tipi' => $appointment->website_type_tr,
                'tahmini_fiyat' => $appointment->price_range,
                'calculator_id' => $appointment->calculator_id,
                'appointment_id' => $appointment->id
            );
            
            // Get WooCommerce URL from settings
            $woocommerce_url = get_option('morpheo_woocommerce_url', 'https://morpheodijital.com/satis/');
            $woocommerce_url = rtrim($woocommerce_url, '/') . '/';
            $payment_url = $woocommerce_url . '?' . http_build_query($payment_params);
            
            // Send email reminder
            $email_sender = new MorpheoEmailSender();
            $email_sent = $email_sender->send_payment_reminder(
                $appointment->email,
                $appointment->first_name,
                $appointment->appointment_date,
                $appointment->appointment_time,
                $payment_url,
                $payment_params
            );
            
            // Send WhatsApp reminder if enabled and phone available
            $whatsapp_enabled = get_option('morpheo_whatsapp_enabled', false);
            $whatsapp_sent = false;
            
            if ($whatsapp_enabled && !empty($appointment->phone)) {
                $whatsapp_sender = new MorpheoWhatsAppSender();
                $whatsapp_sent = $whatsapp_sender->send_payment_reminder(
                    $appointment->phone,
                    $appointment->first_name,
                    $appointment->appointment_date,
                    $appointment->appointment_time,
                    $payment_url,
                    $payment_params
                );
            }
            
            // Mark reminder as sent if at least email was sent
            if ($email_sent) {
                $wpdb->update(
                    $appointments_table,
                    array('reminder_sent' => 1),
                    array('id' => $appointment->id)
                );
                $reminder_count++;
            }
        }
        
        if ($reminder_count > 0) {
            error_log("Morpheo Payment Reminder: {$reminder_count} reminders sent");
        }
        
        return $reminder_count;
    }
}
?>
