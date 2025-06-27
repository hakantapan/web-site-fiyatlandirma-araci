<?php
/**
 * WhatsApp Sender for Morpheo Calculator
 * Uses OtomatikBot.com API
 */

class MorpheoWhatsAppSender {
    
    private static $api_url = 'https://otomatikbot.com/api/qr/rest/send_message';
    
    /**
     * Send WhatsApp message
     */
    public static function sendMessage($to, $message, $messageType = 'text') {
        // Check if WhatsApp is enabled
        if (!get_option('morpheo_whatsapp_enabled', '0')) {
            return false;
        }
        
        $token = get_option('morpheo_whatsapp_token', '');
        $from = get_option('morpheo_whatsapp_from', '');
        
        if (empty($token) || empty($from)) {
            error_log('Morpheo WhatsApp: Token or from number not configured');
            return false;
        }
        
        // Clean phone number (remove spaces, dashes, parentheses)
        $to = preg_replace('/[^0-9]/', '', $to);
        
        // Add country code if not present
        if (!str_starts_with($to, '90') && strlen($to) == 10) {
            $to = '90' . $to;
        }
        
        // Prepare API parameters
        $params = array(
            'messageType' => $messageType,
            'requestType' => 'GET',
            'token' => $token,
            'from' => $from,
            'to' => $to,
            'text' => $message
        );
        
        $url = self::$api_url . '?' . http_build_query($params);
        
        // Send request
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Morpheo Calculator WhatsApp Integration'
            )
        ));
        
        if (is_wp_error($response)) {
            error_log('Morpheo WhatsApp Error: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);
        
        // Log the response for debugging
        error_log('Morpheo WhatsApp Response: ' . $body);
        
        if ($status_code == 200) {
            $data = json_decode($body, true);
            if ($data && isset($data['success']) && $data['success']) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Send appointment confirmation to customer
     */
    public static function sendCustomerAppointmentConfirmation($appointment_data, $calculator_data, $payment_url = '') {
        $customer_phone = $calculator_data->phone;
        
        if (empty($customer_phone)) {
            return false;
        }
        
        $message = self::getCustomerConfirmationMessage($appointment_data, $calculator_data, $payment_url);
        
        return self::sendMessage($customer_phone, $message);
    }
    
    /**
     * Send new appointment notification to admin
     */
    public static function sendAdminAppointmentNotification($appointment_data, $calculator_data) {
        $admin_phone = get_option('morpheo_whatsapp_admin', '');
        
        if (empty($admin_phone)) {
            return false;
        }
        
        $message = self::getAdminNotificationMessage($appointment_data, $calculator_data);
        
        return self::sendMessage($admin_phone, $message);
    }
    
    /**
     * Send appointment reminder to customer
     */
    public static function sendAppointmentReminder($appointment_data, $calculator_data) {
        $customer_phone = $calculator_data->phone;
        
        if (empty($customer_phone)) {
            return false;
        }
        
        $message = self::getReminderMessage($appointment_data, $calculator_data);
        
        return self::sendMessage($customer_phone, $message);
    }
    
    /**
     * Send payment confirmation
     */
    public static function sendPaymentConfirmation($appointment_data, $calculator_data) {
        $customer_phone = $calculator_data->phone;
        
        if (empty($customer_phone)) {
            return false;
        }
        
        $message = self::getPaymentConfirmationMessage($appointment_data, $calculator_data);
        
        return self::sendMessage($customer_phone, $message);
    }
    
    /**
     * Test WhatsApp connection
     */
    public static function sendTestMessage() {
        $admin_phone = get_option('morpheo_whatsapp_admin', '');
        
        if (empty($admin_phone)) {
            return array('success' => false, 'message' => 'Admin WhatsApp numarası ayarlanmamış');
        }
        
        $message = "🧪 *Morpheo Calculator Test Mesajı*\n\n";
        $message .= "Bu bir test mesajıdır. WhatsApp entegrasyonunuz başarıyla çalışıyor! ✅\n\n";
        $message .= "Tarih: " . date('d.m.Y H:i') . "\n";
        $message .= "Sistem: Morpheo Dijital Website Price Calculator";
        
        $result = self::sendMessage($admin_phone, $message);
        
        if ($result) {
            return array('success' => true, 'message' => 'Test mesajı başarıyla gönderildi: ' . $admin_phone);
        } else {
            return array('success' => false, 'message' => 'Test mesajı gönderilemedi. Lütfen ayarlarınızı kontrol edin.');
        }
    }
    
    /**
     * Get customer confirmation message
     */
    private static function getCustomerConfirmationMessage($appointment_data, $calculator_data, $payment_url = '') {
        $project_types = array(
            'corporate' => 'Kurumsal Website',
            'ecommerce' => 'E-Ticaret Sitesi',
            'blog' => 'Blog/İçerik Sitesi',
            'landing' => 'Özel Kampanya Sayfası'
        );
        
        $project_type = $project_types[$calculator_data->website_type] ?? 'Website Projesi';
        $appointment_date = date('d F Y, l', strtotime($appointment_data['appointment_date']));
        $appointment_time = date('H:i', strtotime($appointment_data['appointment_time']));
        $price_range = number_format($calculator_data->min_price, 0, ',', '.') . ' - ' . number_format($calculator_data->max_price, 0, ',', '.') . ' ₺';
        
        $message = "🎉 *Randevunuz Oluşturuldu!*\n\n";
        $message .= "Merhaba *{$calculator_data->first_name} {$calculator_data->last_name}*,\n\n";
        $message .= "📅 *Randevu Detayları:*\n";
        $message .= "• Tarih: {$appointment_date}\n";
        $message .= "• Saat: {$appointment_time}\n";
        $message .= "• Proje: {$project_type}\n";
        $message .= "• Tahmini Fiyat: {$price_range}\n\n";
        
        if (!empty($payment_url)) {
            $consultation_fee = get_option('morpheo_consultation_fee', '250');
            $message .= "💳 *Ödeme Gerekli:*\n";
            $message .= "Randevunuzu onaylamak için {$consultation_fee} ₺ konsültasyon ücreti ödemeniz gerekmektedir.\n\n";
            $message .= "🔗 *Ödeme Linki:*\n{$payment_url}\n\n";
            $message .= "⚠️ *Önemli:* Ödeme yapılmadığı takdirde randevunuz iptal edilecektir.\n\n";
        } else {
            $message .= "✅ *Randevunuz Onaylandı!*\n\n";
        }
        
        $message .= "📞 *İletişim:*\n";
        $message .= "• Telefon: +90 555 123 45 67\n";
        $message .= "• E-posta: info@morpheodijital.com\n\n";
        $message .= "Teşekkürler,\n*Morpheo Dijital Ekibi* 🚀";
        
        return $message;
    }
    
    /**
     * Get admin notification message
     */
    private static function getAdminNotificationMessage($appointment_data, $calculator_data) {
        $project_types = array(
            'corporate' => 'Kurumsal Website',
            'ecommerce' => 'E-Ticaret Sitesi',
            'blog' => 'Blog/İçerik Sitesi',
            'landing' => 'Özel Kampanya Sayfası'
        );
        
        $project_type = $project_types[$calculator_data->website_type] ?? 'Website Projesi';
        $appointment_date = date('d F Y, l', strtotime($appointment_data['appointment_date']));
        $appointment_time = date('H:i', strtotime($appointment_data['appointment_time']));
        $price_range = number_format($calculator_data->min_price, 0, ',', '.') . ' - ' . number_format($calculator_data->max_price, 0, ',', '.') . ' ₺';
        
        $message = "🚨 *YENİ RANDEVU!*\n\n";
        $message .= "👤 *Müşteri Bilgileri:*\n";
        $message .= "• Ad Soyad: {$calculator_data->first_name} {$calculator_data->last_name}\n";
        $message .= "• Telefon: {$calculator_data->phone}\n";
        $message .= "• E-posta: {$calculator_data->email}\n";
        
        if (!empty($calculator_data->company)) {
            $message .= "• Şirket: {$calculator_data->company}\n";
        }
        
        if (!empty($calculator_data->city)) {
            $message .= "• Şehir: {$calculator_data->city}\n";
        }
        
        $message .= "\n📅 *Randevu Detayları:*\n";
        $message .= "• Tarih: {$appointment_date}\n";
        $message .= "• Saat: {$appointment_time}\n";
        $message .= "• Proje: {$project_type}\n";
        $message .= "• Sayfa Sayısı: {$calculator_data->page_count}\n";
        $message .= "• Tahmini Fiyat: {$price_range}\n\n";
        
        // Add selected features
        $features = json_decode($calculator_data->features, true);
        if (!empty($features) && is_array($features)) {
            $feature_names = array(
                'seo' => 'SEO Optimizasyonu',
                'cms' => 'İçerik Yönetimi',
                'multilang' => 'Çoklu Dil',
                'payment' => 'Online Ödeme',
                'booking' => 'Randevu Sistemi',
                'analytics' => 'Analitik Raporlama'
            );
            
            $message .= "⚙️ *Seçilen Özellikler:*\n";
            foreach ($features as $feature) {
                if (isset($feature_names[$feature])) {
                    $message .= "• {$feature_names[$feature]}\n";
                }
            }
            $message .= "\n";
        }
        
        $message .= "🔗 *Admin Panel:*\n";
        $message .= admin_url('admin.php?page=morpheo-calculator-appointments') . "\n\n";
        $message .= "*Morpheo Calculator* 🚀";
        
        return $message;
    }
    
    /**
     * Get reminder message
     */
    private static function getReminderMessage($appointment_data, $calculator_data) {
        $appointment_date = date('d F Y, l', strtotime($appointment_data['appointment_date']));
        $appointment_time = date('H:i', strtotime($appointment_data['appointment_time']));
        
        $message = "⏰ *Randevu Hatırlatması*\n\n";
        $message .= "Merhaba *{$calculator_data->first_name}*,\n\n";
        $message .= "Yarın randevunuz bulunmaktadır:\n\n";
        $message .= "📅 *Randevu Detayları:*\n";
        $message .= "• Tarih: {$appointment_date}\n";
        $message .= "• Saat: {$appointment_time}\n\n";
        $message .= "🔔 *Unutmayın!*\n";
        $message .= "Randevunuza zamanında katılmayı unutmayın.\n\n";
        $message .= "📞 Sorularınız için: +90 555 123 45 67\n\n";
        $message .= "Teşekkürler,\n*Morpheo Dijital* 🚀";
        
        return $message;
    }
    
    /**
     * Get payment confirmation message
     */
    private static function getPaymentConfirmationMessage($appointment_data, $calculator_data) {
        $appointment_date = date('d F Y, l', strtotime($appointment_data['appointment_date']));
        $appointment_time = date('H:i', strtotime($appointment_data['appointment_time']));
        
        $message = "✅ *Ödeme Onaylandı!*\n\n";
        $message .= "Merhaba *{$calculator_data->first_name}*,\n\n";
        $message .= "Konsültasyon ücretiniz başarıyla alındı. Randevunuz onaylanmıştır.\n\n";
        $message .= "📅 *Onaylanan Randevu:*\n";
        $message .= "• Tarih: {$appointment_date}\n";
        $message .= "• Saat: {$appointment_time}\n\n";
        $message .= "🎉 Randevunuzda görüşmek üzere!\n\n";
        $message .= "📞 İletişim: +90 555 123 45 67\n\n";
        $message .= "Teşekkürler,\n*Morpheo Dijital* 🚀";
        
        return $message;
    }
}
