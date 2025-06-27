<?php
/**
 * WhatsApp Sender for Morpheo Calculator
 */

class MorpheoWhatsAppSender {
    private static $api_base_url = 'https://otomatikbot.com/api/qr/rest/send_message';

    /**
     * Sends a WhatsApp message.
     *
     * @param string $to_number The recipient's phone number (e.g., '905XXXXXXXXX').
     * @param string $message_text The text content of the message.
     * @return bool True on success, false on failure.
     */
    public static function sendMessage($to_number, $message_text) {
        $token = get_option('morpheo_whatsapp_api_token');
        $from_number = get_option('morpheo_whatsapp_from_number');
        $whatsapp_enabled = get_option('morpheo_whatsapp_enable', 'no');

        if ($whatsapp_enabled !== 'yes') {
            error_log('Morpheo WhatsApp: WhatsApp integration is disabled. Message not sent.');
            return false;
        }

        if (empty($token) || empty($from_number) || empty($to_number) || empty($message_text)) {
            error_log('Morpheo WhatsApp: Missing required parameters (token, from_number, to_number, or message_text) for sending message.');
            return false;
        }

        // Clean and format phone numbers
        $clean_from_number = self::cleanPhoneNumber($from_number);
        $clean_to_number = self::cleanPhoneNumber($to_number);

        if (empty($clean_from_number) || empty($clean_to_number)) {
            error_log('Morpheo WhatsApp: Invalid phone number format after cleaning. Message not sent.');
            return false;
        }

        $args = array(
            'messageType' => 'text',
            'requestType' => 'GET',
            'token' => $token,
            'from' => $clean_from_number,
            'to' => $clean_to_number,
            'text' => $message_text,
        );

        $url = add_query_arg($args, self::$api_base_url);

        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'sslverify' => false, // Consider setting to true in production with proper SSL certs
            'headers' => array(
                'User-Agent' => 'Morpheo Calculator WhatsApp Sender'
            )
        ));

        if (is_wp_error($response)) {
            error_log('Morpheo WhatsApp API Error: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            error_log('Morpheo WhatsApp API HTTP Error: ' . $status_code . ' - Response: ' . $body);
            return false;
        }

        $decoded_body = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Morpheo WhatsApp API JSON Decode Error: ' . json_last_error_msg() . ' - Raw: ' . $body);
            return false;
        }

        if (isset($decoded_body['status']) && $decoded_body['status'] === 'success') {
            error_log('Morpheo WhatsApp: Message sent successfully to ' . $clean_to_number);
            return true;
        } else {
            error_log('Morpheo WhatsApp: Failed to send message to ' . $clean_to_number . ' - API Response: ' . $body);
            return false;
        }
    }

    /**
     * Cleans and formats a phone number for WhatsApp API.
     * Ensures it starts with '90' and is 12 digits long for Turkish numbers.
     *
     * @param string $phone The raw phone number.
     * @return string The cleaned phone number, or empty string if invalid.
     */
    private static function cleanPhoneNumber($phone) {
        // Remove all non-numeric characters
        $clean_phone = preg_replace('/[^0-9]/', '', $phone);

        // If it starts with 0, remove it and add 90 for Turkish numbers (e.g., 05XX -> 905XX)
        if (substr($clean_phone, 0, 1) === '0' && strlen($clean_phone) === 11) {
            $clean_phone = '90' . substr($clean_phone, 1);
        } elseif (strlen($clean_phone) === 10) { // Assume 10-digit number is missing 90 prefix (e.g., 5XX -> 905XX)
            $clean_phone = '90' . $clean_phone;
        }

        // Ensure it starts with 90 and is 12 digits long (90 + 10 digits)
        if (substr($clean_phone, 0, 2) === '90' && strlen($clean_phone) === 12) {
            return $clean_phone;
        }

        error_log('Morpheo WhatsApp: Invalid phone number format for cleaning: ' . $phone . ' -> ' . $clean_phone);
        return ''; // Return empty string for invalid numbers
    }

    /**
     * Sends a WhatsApp confirmation message to the customer after appointment booking.
     */
    public static function sendCustomerConfirmationWhatsApp($appointment_data, $calculator_data, $payment_url = '') {
        $message = "Merhaba " . $calculator_data->first_name . " " . $calculator_data->last_name . ",\n";
        $message .= "Randevunuz başarıyla oluşturuldu!\n";
        $message .= "📅 Tarih: " . date('d.m.Y', strtotime($appointment_data['appointment_date'])) . "\n";
        $message .= "🕐 Saat: " . $appointment_data['appointment_time'] . "\n";
        $message .= "💰 Konsültasyon Ücreti: " . number_format(get_option('morpheo_consultation_fee', '250'), 0, ',', '.') . " ₺\n";
        if (!empty($payment_url)) {
            $message .= "Ödeme yapmak için: " . $payment_url . "\n";
            $message .= "⚠️ Önemli: Ödeme işlemini 15 dakika içinde tamamlamazsanız randevunuz iptal olacaktır.";
        } else {
            $message .= "Ödemeniz alınmıştır. Randevunuz onaylandı.";
        }
        return self::sendMessage($calculator_data->phone, $message);
    }

    /**
     * Sends a WhatsApp notification message to the admin after a new appointment booking.
     */
    public static function sendAdminNotificationWhatsApp($appointment_data, $calculator_data) {
        $message = "🚨 YENİ RANDEVU BİLDİRİMİ!\n";
        $message .= "Müşteri: " . $calculator_data->first_name . " " . $calculator_data->last_name . "\n";
        $message .= "Telefon: " . $calculator_data->phone . "\n";
        $message .= "E-posta: " . $calculator_data->email . "\n";
        $message .= "Randevu: " . date('d.m.Y', strtotime($appointment_data['appointment_date'])) . " " . $appointment_data['appointment_time'] . "\n";
        $message .= "Proje Tipi: " . self::getProjectTypeName($calculator_data->website_type) . "\n";
        $message .= "Tahmini Fiyat: " . number_format($calculator_data->min_price, 0, ',', '.') . " - " . number_format($calculator_data->max_price, 0, ',', '.') . " ₺\n";
        $message .= "Ödeme Durumu: Beklemede\n";
        $message .= "Hemen iletişime geçin!";

        // Send to the configured 'from' number, assuming it's an admin's number.
        // For multiple admins, a new option 'morpheo_admin_whatsapp_numbers' could be added.
        $admin_whatsapp_number = get_option('morpheo_whatsapp_from_number'); 
        return self::sendMessage($admin_whatsapp_number, $message);
    }

    /**
     * Sends a WhatsApp confirmation message to the customer after payment is received.
     */
    public static function sendPaymentConfirmationWhatsApp($appointment, $calculator_data) {
        $message = "Merhaba " . $calculator_data->first_name . " " . $calculator_data->last_name . ",\n";
        $message .= "Ödemeniz başarıyla alındı ve randevunuz onaylandı!\n";
        $message .= "📅 Tarih: " . date('d.m.Y', strtotime($appointment->appointment_date)) . "\n";
        $message .= "🕐 Saat: " . $appointment->appointment_time . "\n";
        $message .= "Ödenen Tutar: " . number_format($appointment->payment_amount, 0, ',', '.') . " ₺\n";
        $message .= "Randevunuza zamanında katılmayı unutmayın!";
        return self::sendMessage($calculator_data->phone, $message);
    }

    /**
     * Sends a WhatsApp reminder message for pending payments.
     */
    public static function sendPaymentReminderWhatsApp($appointment_data, $calculator_data, $payment_url) {
        $message = "⚠️ Acil: Randevunuz İptal Olmak Üzere!\n";
        $message .= "Merhaba " . $calculator_data->first_name . " " . $calculator_data->last_name . ",\n";
        $message .= "Randevunuz için ödeme bekleniyor. Kalan süre: " . MorpheoPaymentReminder::getMinutesLeft($appointment_data['created_at']) . " dakika.\n";
        $message .= "Ödeme yapılmazsa randevunuz otomatik olarak iptal olacaktır!\n";
        $message .= "Hemen ödeme yapın: " . $payment_url;
        return self::sendMessage($calculator_data->phone, $message);
    }

    /**
     * Sends a WhatsApp reminder message 24 hours before the appointment.
     */
    public static function sendAppointmentReminderWhatsApp($appointment_data, $calculator_data) {
        $message = "⏰ Randevu Hatırlatması!\n";
        $message .= "Merhaba " . $calculator_data->first_name . " " . $calculator_data->last_name . ",\n";
        $message .= "Yarın saat " . $appointment_data['appointment_time'] . " randevunuz bulunmaktadır.\n";
        $message .= "Proje: " . self::getProjectTypeName($calculator_data->website_type) . "\n";
        $message .= "Randevunuza zamanında katılmayı unutmayın!";
        return self::sendMessage($calculator_data->phone, $message);
    }

    /**
     * Helper function to get project type name.
     */
    private static function getProjectTypeName($type) {
        $types = array(
            'corporate' => 'Kurumsal Website',
            'ecommerce' => 'E-Ticaret Sitesi',
            'blog' => 'Blog/İçerik Sitesi',
            'landing' => 'Özel Kampanya Sayfası'
        );
        return $types[$type] ?? 'Belirtilmemiş';
    }
}
