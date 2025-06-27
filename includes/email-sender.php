<?php
/**
 * Email Sender for Morpheo Calculator
 */

class MorpheoEmailSender {
    
    /**
     * Send appointment confirmation email to customer
     */
    public static function sendCustomerConfirmation($appointment_data, $calculator_data, $payment_url = '') {
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
        
        // Determine payment status
        $payment_status = !empty($payment_url) ? 'pending' : 'paid';
        
        // Email content
        $to = $calculator_data->email;
        $subject = $payment_status === 'pending' 
            ? '⏰ Randevunuz Beklemede - Ödeme Gerekli - Morpheo Dijital'
            : '🎉 Randevunuz Onaylandı - Morpheo Dijital';
        $message = MorpheoEmailTemplates::getCustomerConfirmationEmail($email_data, $payment_status);
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Morpheo Dijital <info@morpheodijital.com>',
            'Reply-To: info@morpheodijital.com'
        );
        
        // Send email
        $sent = wp_mail($to, $subject, $message, $headers);
        
        // Log email sending
        if ($sent) {
            error_log('Morpheo Calculator: Customer confirmation email sent to ' . $to);
        } else {
            error_log('Morpheo Calculator: Failed to send customer confirmation email to ' . $to);
        }
        
        return $sent;
    }
    
    /**
     * Send appointment notification email to admin
     */
    public static function sendAdminNotification($appointment_data, $calculator_data) {
        // Prepare email data
        $email_data = array(
            'customer_name' => $calculator_data->first_name . ' ' . $calculator_data->last_name,
            'phone' => $calculator_data->phone,
            'email' => $calculator_data->email,
            'company' => $calculator_data->company,
            'city' => $calculator_data->city,
            'appointment_date' => $appointment_data['appointment_date'],
            'appointment_time' => $appointment_data['appointment_time'],
            'project_type' => self::getProjectTypeName($calculator_data->website_type),
            'estimated_price' => number_format($calculator_data->min_price, 0, ',', '.') . ' - ' . number_format($calculator_data->max_price, 0, ',', '.') . ' ₺',
            'page_count' => $calculator_data->page_count,
            'design_level' => self::getDesignLevelName($calculator_data->design_complexity),
            'business_type' => self::getBusinessTypeName($calculator_data->management_features),
            'online_payment' => self::getOnlinePaymentStatus($calculator_data->ecommerce_modules),
            'selected_features' => self::getSelectedFeatures($calculator_data->features)
        );
        
        // Admin email
        $admin_email = get_option('admin_email');
        $additional_emails = get_option('morpheo_admin_emails', '');
        
        $to = array($admin_email);
        if (!empty($additional_emails)) {
            $additional = array_map('trim', explode(',', $additional_emails));
            $to = array_merge($to, $additional);
        }
        
        $subject = '🚨 YENİ RANDEVU: ' . $calculator_data->first_name . ' ' . $calculator_data->last_name . ' - ' . date('d.m.Y H:i', strtotime($appointment_data['appointment_date'] . ' ' . $appointment_data['appointment_time']));
        $message = MorpheoEmailTemplates::getAdminNotificationEmail($email_data);
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Morpheo Calculator <noreply@morpheodijital.com>',
            'Reply-To: ' . $calculator_data->email
        );
        
        // Send email to all admins
        $sent = true;
        foreach ($to as $admin_email_addr) {
            $result = wp_mail($admin_email_addr, $subject, $message, $headers);
            if (!$result) {
                $sent = false;
                error_log('Morpheo Calculator: Failed to send admin notification to ' . $admin_email_addr);
            }
        }
        
        if ($sent) {
            error_log('Morpheo Calculator: Admin notification emails sent successfully');
        }
        
        return $sent;
    }
    
    /**
     * Send appointment reminder email (24 hours before)
     */
    public static function sendAppointmentReminder($appointment_data, $calculator_data) {
        $email_data = array(
            'customer_name' => $calculator_data->first_name . ' ' . $calculator_data->last_name,
            'appointment_date' => $appointment_data['appointment_date'],
            'appointment_time' => $appointment_data['appointment_time'],
            'project_type' => self::getProjectTypeName($calculator_data->website_type),
            'estimated_price' => number_format($calculator_data->min_price, 0, ',', '.') . ' - ' . number_format($calculator_data->max_price, 0, ',', '.') . ' ₺',
            'page_count' => $calculator_data->page_count,
            'selected_features' => self::getSelectedFeatures($calculator_data->features)
        );
        
        $to = $calculator_data->email;
        $subject = '⏰ Yarın Randevunuz Var - Morpheo Dijital';
        $message = self::getReminderEmailTemplate($email_data);
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Morpheo Dijital <info@morpheodijital.com>'
        );
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Helper functions
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
    
    private static function getDesignLevelName($level) {
        $levels = array(
            'basic' => 'Profesyonel & Sade',
            'custom' => 'Markanıza Özel',
            'premium' => 'Lüks & Etkileyici'
        );
        return $levels[$level] ?? 'Belirtilmemiş';
    }
    
    private static function getBusinessTypeName($features) {
        return 'Belirtilmemiş';
    }
    
    private static function getOnlinePaymentStatus($modules) {
        return 'Belirtilmemiş';
    }
    
    private static function getSelectedFeatures($features_json) {
        if (empty($features_json)) return array();
        
        $features = json_decode($features_json, true);
        if (!is_array($features)) return array();
        
        $feature_names = array(
            'seo' => 'SEO Optimizasyonu',
            'cms' => 'İçerik Yönetimi',
            'multilang' => 'Çoklu Dil',
            'payment' => 'Online Ödeme'
        );
        
        $selected = array();
        foreach ($features as $feature) {
            if (isset($feature_names[$feature])) {
                $selected[] = $feature_names[$feature];
            }
        }
        
        return $selected;
    }
    
    private static function getReminderEmailTemplate($data) {
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
                .header { background: linear-gradient(135deg, #f59e0b, #d97706); padding: 30px; text-align: center; color: white; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { padding: 30px; }
                .reminder-box { background: #fef3c7; border: 2px solid #f59e0b; border-radius: 12px; padding: 20px; margin: 20px 0; text-align: center; }
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
                    <h1>⏰ Randevu Hatırlatması</h1>
                    <p>Yarın randevunuz var!</p>
                </div>
                <div class="content">
                    <div class="reminder-box">
                        <h2 style="color: #92400e; margin-bottom: 10px;">🔔 Unutmayın!</h2>
                        <p style="color: #92400e; margin: 0;">Yarın saat ' . esc_html($data['appointment_time']) . ' randevunuz bulunmaktadır.</p>
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
                            <span><strong>Proje:</strong></span>
                            <span>' . esc_html($data['project_type']) . '</span>
                        </div>
                    </div>
                    
                    <div class="contact-section">
                        <p>Sorularınız için:</p>
                        <a href="tel:+905551234567" class="contact-button">📞 Ara</a>
                        <a href="https://wa.me/905551234567" class="contact-button">💬 WhatsApp</a>
                    </div>
                </div>
                <div class="footer">
                    <p>Morpheo Dijital - Profesyonel Web Tasarım</p>
                </div>
            </div>
        </body>
        </html>';
    }
}
