<?php
/**
 * WhatsApp Sender for Morpheo Calculator
 */

class MorpheoWhatsAppSender {
    
    /**
     * Send customer confirmation WhatsApp message
     */
    public static function sendCustomerConfirmationWhatsApp($appointment_data, $calculator_data, $payment_url = '') {
        // Check if WhatsApp is enabled
        if (get_option('morpheo_whatsapp_enable', 'no') !== 'yes') {
            return false;
        }
        
        $api_token = get_option('morpheo_whatsapp_api_token', '');
        $from_number = get_option('morpheo_whatsapp_from_number', '');
        
        if (empty($api_token) || empty($from_number)) {
            error_log('Morpheo Calculator: WhatsApp API token or from number not configured');
            return false;
        }
        
        // Clean phone number (remove non-digits and add country code if needed)
        $to_number = preg_replace('/[^0-9]/', '', $calculator_data->phone);
        if (substr($to_number, 0, 1) !== '9' && substr($to_number, 0, 2) !== '90') {
            $to_number = '90' . $to_number;
        }
        
        $consultation_fee = get_option('morpheo_consultation_fee', '250');
        $customer_name = $calculator_data->first_name . ' ' . $calculator_data->last_name;
        
        // Prepare message
        $message = "🎉 *Randevunuz Onaylandı!*\n\n";
        $message .= "Sayın *{$customer_name}*,\n";
        $message .= "Web sitesi konsültasyon randevunuz başarıyla oluşturuldu.\n\n";
        
        $message .= "📅 *Randevu Detayları:*\n";
        $message .= "• Tarih: " . date('d F Y, l', strtotime($appointment_data['appointment_date'])) . "\n";
        $message .= "• Saat: {$appointment_data['appointment_time']}\n";
        $message .= "• Ücret: " . number_format($consultation_fee, 0, ',', '.') . " ₺\n";
        $message .= "• Süre: 45-60 dakika\n\n";
        
        if (!empty($payment_url)) {
            $message .= "💳 *ÖNEMLİ: Ödeme Gerekli*\n";
            $message .= "Randevunuzu kesinleştirmek için 15 dakika içinde ödeme yapmanız gerekmektedir.\n\n";
            $message .= "Ödeme yapmak için:\n";
            $message .= $payment_url . "\n\n";
            $message .= "⏰ 15 dakika içinde ödeme yapılmazsa randevunuz iptal olacaktır.\n\n";
        }
        
        $message .= "📞 *İletişim:*\n";
        $message .= "Sorularınız için: +90 555 123 45 67\n\n";
        $message .= "Morpheo Dijital\n";
        $message .= "Profesyonel Web Tasarım & Dijital Pazarlama";
        
        return self::sendWhatsAppMessage($to_number, $message, $api_token, $from_number);
    }
    
    /**
     * Send admin notification WhatsApp message
     */
    public static function sendAdminNotificationWhatsApp($appointment_data, $calculator_data) {
        // Check if WhatsApp is enabled
        if (get_option('morpheo_whatsapp_enable', 'no') !== 'yes') {
            return false;
        }
        
        $api_token = get_option('morpheo_whatsapp_api_token', '');
        $from_number = get_option('morpheo_whatsapp_from_number', '');
        
        if (empty($api_token) || empty($from_number)) {
            return false;
        }
        
        // Admin phone number (you can make this configurable)
        $admin_phone = '905551234567'; // Replace with actual admin number
        
        $consultation_fee = get_option('morpheo_consultation_fee', '250');
        $customer_name = $calculator_data->first_name . ' ' . $calculator_data->last_name;
        
        // Prepare admin message
        $message = "🚨 *YENİ RANDEVU BİLDİRİMİ*\n\n";
        $message .= "👤 *Müşteri:* {$customer_name}\n";
        $message .= "📞 *Telefon:* {$calculator_data->phone}\n";
        $message .= "📧 *E-posta:* {$calculator_data->email}\n\n";
        
        $message .= "📅 *Randevu:*\n";
        $message .= "• Tarih: " . date('d.m.Y', strtotime($appointment_data['appointment_date'])) . "\n";
        $message .= "• Saat: {$appointment_data['appointment_time']}\n";
        $message .= "• Ücret: " . number_format($consultation_fee, 0, ',', '.') . " ₺\n\n";
        
        $message .= "🌐 *Proje:*\n";
        $message .= "• Tür: " . self::getProjectTypeName($calculator_data->website_type) . "\n";
        $message .= "• Sayfa: {$calculator_data->page_count}\n";
        $message .= "• Fiyat: " . number_format($calculator_data->min_price, 0, ',', '.') . " - " . number_format($calculator_data->max_price, 0, ',', '.') . " ₺\n\n";
        
        $message .= "⚡ *HEMEN MÜŞTERİYLE İLETİŞİME GEÇİN!*";
        
        return self::sendWhatsAppMessage($admin_phone, $message, $api_token, $from_number);
    }
    
    /**
     * Send payment confirmation WhatsApp message
     */
    public static function sendPaymentConfirmationWhatsApp($appointment_data, $calculator_data) {
        // Check if WhatsApp is enabled
        if (get_option('morpheo_whatsapp_enable', 'no') !== 'yes') {
            return false;
        }
        
        $api_token = get_option('morpheo_whatsapp_api_token', '');
        $from_number = get_option('morpheo_whatsapp_from_number', '');
        
        if (empty($api_token) || empty($from_number)) {
            return false;
        }
        
        // Clean phone number
        $to_number = preg_replace('/[^0-9]/', '', $calculator_data->phone);
        if (substr($to_number, 0, 1) !== '9' && substr($to_number, 0, 2) !== '90') {
            $to_number = '90' . $to_number;
        }
        
        $customer_name = $calculator_data->first_name . ' ' . $calculator_data->last_name;
        
        // Prepare payment confirmation message
        $message = "✅ *Ödeme Onaylandı!*\n\n";
        $message .= "Sayın *{$customer_name}*,\n";
        $message .= "Konsültasyon randevu ödemeniz başarıyla alınmıştır.\n\n";
        
        $message .= "📅 *Onaylanan Randevu:*\n";
        $message .= "• Tarih: " . date('d F Y, l', strtotime($appointment_data->appointment_date)) . "\n";
        $message .= "• Saat: {$appointment_data->appointment_time}\n\n";
        
        $message .= "🎯 *Sonraki Adımlar:*\n";
        $message .= "• Randevu tarihinden 1 gün önce hatırlatma mesajı alacaksınız\n";
        $message .= "• Görüşme öncesi hazırlık listesini e-postanızdan kontrol edin\n";
        $message .= "• Sorularınız için bize ulaşabilirsiniz\n\n";
        
        $message .= "📞 *İletişim:* +90 555 123 45 67\n\n";
        $message .= "Teşekkürler!\n";
        $message .= "Morpheo Dijital";
        
        return self::sendWhatsAppMessage($to_number, $message, $api_token, $from_number);
    }
    
    /**
     * Send appointment reminder WhatsApp message
     */
    public static function sendAppointmentReminderWhatsApp($appointment_data, $calculator_data) {
        // Check if WhatsApp is enabled
        if (get_option('morpheo_whatsapp_enable', 'no') !== 'yes') {
            return false;
        }
        
        $api_token = get_option('morpheo_whatsapp_api_token', '');
        $from_number = get_option('morpheo_whatsapp_from_number', '');
        
        if (empty($api_token) || empty($from_number)) {
            return false;
        }
        
        // Clean phone number
        $to_number = preg_replace('/[^0-9]/', '', $calculator_data->phone);
        if (substr($to_number, 0, 1) !== '9' && substr($to_number, 0, 2) !== '90') {
            $to_number = '90' . $to_number;
        }
        
        $customer_name = $calculator_data->first_name . ' ' . $calculator_data->last_name;
        
        // Prepare reminder message
        $message = "⏰ *Randevu Hatırlatması*\n\n";
        $message .= "Sayın *{$customer_name}*,\n";
        $message .= "Yarın konsültasyon randevunuz bulunmaktadır.\n\n";
        
        $message .= "📅 *Randevu Detayları:*\n";
        $message .= "• Tarih: " . date('d F Y, l', strtotime($appointment_data['appointment_date'])) . "\n";
        $message .= "• Saat: {$appointment_data['appointment_time']}\n\n";
        
        $message .= "📋 *Hazırlık:*\n";
        $message .= "• Mevcut web siteniz varsa adresini not edin\n";
        $message .= "• Beğendiğiniz örnek siteleri belirleyin\n";
        $message .= "• Logo ve marka materyallerinizi hazırlayın\n\n";
        
        $message .= "📞 *İletişim:* +90 555 123 45 67\n\n";
        $message .= "Görüşmek üzere!\n";
        $message .= "Morpheo Dijital";
        
        return self::sendWhatsAppMessage($to_number, $message, $api_token, $from_number);
    }
    
    /**
     * Send WhatsApp message via API
     */
    private static function sendWhatsAppMessage($to_number, $message, $api_token, $from_number) {
        $api_url = 'https://api.whatsapp.com/send'; // Replace with actual WhatsApp API endpoint
        
        $data = array(
            'token' => $api_token,
            'to' => $to_number,
            'body' => $message,
            'from' => $from_number
        );
        
        $response = wp_remote_post($api_url, array(
            'body' => json_encode($data),
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_token
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            error_log('Morpheo Calculator WhatsApp Error: ' . $response->get_error_message());
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code === 200) {
            error_log('Morpheo Calculator: WhatsApp message sent successfully to ' . $to_number);
            return true;
        } else {
            error_log('Morpheo Calculator WhatsApp Error: HTTP ' . $response_code . ' - ' . $response_body);
            return false;
        }
    }
    
    /**
     * Helper function to get project type name
     */
    private static function getProjectTypeName($type) {
        $types = array(
            'corporate' => 'Kurumsal Website',
            'ecommerce' => 'E-Ticaret Sitesi',
            'blog' => 'Blog/İçerik Sitesi',
            'landing' => 'Özel Kampanya Sayfası'
        );
        return $types[$type] ?? ucfirst($type);
    }
}
