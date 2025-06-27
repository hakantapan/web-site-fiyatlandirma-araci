<?php
/**
 * Email Sender for Morpheo Calculator
 */

class MorpheoEmailSender {
    
    /**
     * Send customer confirmation email
     */
    public static function sendCustomerConfirmation($appointment_data, $calculator_data, $payment_url = '') {
        $consultation_fee = get_option('morpheo_consultation_fee', '250');
        
        // Prepare email data
        $email_data = array(
            'customer_name' => $calculator_data->first_name . ' ' . $calculator_data->last_name,
            'appointment_date' => $appointment_data['appointment_date'],
            'appointment_time' => $appointment_data['appointment_time'],
            'project_type' => self::getProjectTypeName($calculator_data->website_type),
            'estimated_price' => number_format($calculator_data->min_price, 0, ',', '.') . ' - ' . number_format($calculator_data->max_price, 0, ',', '.') . ' ₺',
            'page_count' => $calculator_data->page_count,
            'selected_features' => self::getSelectedFeatures($calculator_data->features),
            'payment_url' => $payment_url
        );
        
        $to = $calculator_data->email;
        $subject = '🎉 Randevunuz Onaylandı - Morpheo Dijital';
        $message = MorpheoEmailTemplates::getCustomerConfirmationEmail($email_data, empty($payment_url) ? 'paid' : 'pending');
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Morpheo Dijital <info@morpheodijital.com>'
        );
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Send admin notification email
     */
    public static function sendAdminNotification($appointment_data, $calculator_data) {
        $admin_emails = get_option('morpheo_admin_emails', '');
        if (empty($admin_emails)) {
            $admin_emails = get_option('admin_email');
        }
        
        $consultation_fee = get_option('morpheo_consultation_fee', '250');
        
        // Prepare email data
        $email_data = array(
            'customer_name' => $calculator_data->first_name . ' ' . $calculator_data->last_name,
            'email' => $calculator_data->email,
            'phone' => $calculator_data->phone,
            'company' => $calculator_data->company,
            'city' => $calculator_data->city,
            'appointment_date' => $appointment_data['appointment_date'],
            'appointment_time' => $appointment_data['appointment_time'],
            'project_type' => self::getProjectTypeName($calculator_data->website_type),
            'estimated_price' => number_format($calculator_data->min_price, 0, ',', '.') . ' - ' . number_format($calculator_data->max_price, 0, ',', '.') . ' ₺',
            'page_count' => $calculator_data->page_count,
            'design_level' => self::getDesignLevelName($calculator_data->design_complexity),
            'business_type' => $calculator_data->website_type,
            'online_payment' => 'Evet', // This could be dynamic based on features
            'selected_features' => self::getSelectedFeatures($calculator_data->features)
        );
        
        $to = $admin_emails;
        $subject = '🚨 Yeni Randevu Bildirimi - ' . $calculator_data->first_name . ' ' . $calculator_data->last_name;
        $message = MorpheoEmailTemplates::getAdminNotificationEmail($email_data);
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Morpheo Dijital <info@morpheodijital.com>'
        );
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Send appointment reminder email
     */
    public static function sendAppointmentReminder($appointment_data, $calculator_data) {
        $consultation_fee = get_option('morpheo_consultation_fee', '250');
        
        $email_data = array(
            'customer_name' => $calculator_data->first_name . ' ' . $calculator_data->last_name,
            'appointment_date' => $appointment_data['appointment_date'],
            'appointment_time' => $appointment_data['appointment_time'],
            'project_type' => self::getProjectTypeName($calculator_data->website_type)
        );
        
        $to = $calculator_data->email;
        $subject = '⏰ Randevu Hatırlatması - Yarın Görüşüyoruz!';
        $message = self::getAppointmentReminderTemplate($email_data);
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Morpheo Dijital <info@morpheodijital.com>'
        );
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Get appointment reminder email template
     */
    private static function getAppointmentReminderTemplate($data) {
        return '
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Randevu Hatırlatması</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f8fafc; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #059669, #047857); padding: 30px; text-align: center; color: white; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { padding: 30px; }
                .reminder-box { background: #f0f9ff; border: 2px solid #0ea5e9; border-radius: 12px; padding: 25px; margin: 20px 0; text-align: center; }
                .appointment-details { background: #f8fafc; border-radius: 12px; padding: 20px; margin: 20px 0; }
                .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
                .detail-row:last-child { border-bottom: none; }
                .footer { background: #1e293b; color: #94a3b8; padding: 20px; text-align: center; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>⏰ Randevu Hatırlatması</h1>
                    <p>Yarın konsültasyon randevunuz var!</p>
                </div>
                <div class="content">
                    <div class="reminder-box">
                        <h2 style="color: #0c4a6e; margin-bottom: 15px;">📅 Yarın Görüşüyoruz!</h2>
                        <p style="color: #0c4a6e; font-size: 16px;">
                            Sayın ' . esc_html($data['customer_name']) . ', yarın web sitesi konsültasyon randevunuz bulunmaktadır.
                        </p>
                    </div>
                    
                    <div class="appointment-details">
                        <h3>📋 Randevu Detayları</h3>
                        <div class="detail-row">
                            <span><strong>Tarih:</strong></span>
                            <span>' . date('d F Y, l', strtotime($data['appointment_date'])) . '</span>
                        </div>
                        <div class="detail-row">
                            <span><strong>Saat:</strong></span>
                            <span>' . esc_html($data['appointment_time']) . '</span>
                        </div>
                        <div class="detail-row">
                            <span><strong>Proje Türü:</strong></span>
                            <span>' . esc_html($data['project_type']) . '</span>
                        </div>
                    </div>
                    
                    <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 15px; margin: 20px 0;">
                        <h4 style="color: #92400e; margin-bottom: 10px;">📋 Hazırlık Listesi</h4>
                        <ul style="color: #92400e; margin: 0; padding-left: 20px;">
                            <li>Mevcut web siteniz varsa adresini hazırlayın</li>
                            <li>Beğendiğiniz örnek siteleri belirleyin</li>
                            <li>Logo ve marka materyallerinizi toplayın</li>
                            <li>Proje bütçenizi netleştirin</li>
                        </ul>
                    </div>
                    
                    <div style="text-align: center; margin: 25px 0;">
                        <p>Sorularınız için:</p>
                        <p><strong>📞 0555 123 45 67</strong></p>
                        <p><strong>📧 info@morpheodijital.com</strong></p>
                    </div>
                </div>
                <div class="footer">
                    <p>Morpheo Dijital - Profesyonel Web Tasarım</p>
                    <p>Görüşmek üzere!</p>
                </div>
            </div>
        </body>
        </html>';
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
    
    /**
     * Helper function to get design level name
     */
    private static function getDesignLevelName($level) {
        $levels = array(
            'basic' => 'Profesyonel & Sade',
            'custom' => 'Markanıza Özel',
            'premium' => 'Lüks & Etkileyici'
        );
        return $levels[$level] ?? ucfirst($level);
    }
    
    /**
     * Helper function to get selected features
     */
    private static function getSelectedFeatures($features_json) {
        if (empty($features_json)) {
            return array();
        }
        
        $features = json_decode($features_json, true);
        if (!is_array($features)) {
            return array();
        }
        
        $feature_names = array(
            'seo' => 'SEO Optimizasyonu',
            'cms' => 'İçerik Yönetimi',
            'multilang' => 'Çoklu Dil',
            'payment' => 'Online Ödeme',
            'analytics' => 'Analytics',
            'social' => 'Sosyal Medya Entegrasyonu'
        );
        
        $selected = array();
        foreach ($features as $feature) {
            if (isset($feature_names[$feature])) {
                $selected[] = $feature_names[$feature];
            }
        }
        
        return $selected;
    }
}
