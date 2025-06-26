<?php
/**
 * Email Templates for Morpheo Calculator
 */

class MorpheoEmailTemplates {
    
    /**
     * Customer appointment confirmation email
     */
    public static function getCustomerConfirmationEmail($data) {
        $consultation_fee = get_option('morpheo_consultation_fee', '250');
        
        return '
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Randevu Onayı - Morpheo Dijital</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; background-color: #f8fafc; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
                .header { background: linear-gradient(135deg, #00ff00, #1d4ed8); padding: 40px 30px; text-align: center; }
                .header h1 { color: #ffffff; font-size: 28px; font-weight: 700; margin-bottom: 10px; }
                .header p { color: rgba(255,255,255,0.9); font-size: 16px; }
                .content { padding: 40px 30px; }
                .success-badge { background: #dcfce7; color: #166534; padding: 15px 20px; border-radius: 12px; text-align: center; margin-bottom: 30px; border-left: 4px solid #22c55e; }
                .success-badge h2 { font-size: 20px; margin-bottom: 5px; }
                .appointment-card { background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 16px; padding: 25px; margin: 25px 0; }
                .appointment-header { text-align: center; margin-bottom: 20px; }
                .appointment-header h3 { color: #1e293b; font-size: 18px; margin-bottom: 10px; }
                .appointment-details { display: table; width: 100%; }
                .detail-row { display: table-row; }
                .detail-label, .detail-value { display: table-cell; padding: 8px 0; vertical-align: top; }
                .detail-label { font-weight: 600; color: #64748b; width: 40%; }
                .detail-value { color: #1e293b; }
                .highlight { background: linear-gradient(135deg, #00ff00, #1d4ed8); color: white; padding: 2px 8px; border-radius: 6px; font-weight: 600; }
                .project-summary { background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 12px; padding: 20px; margin: 20px 0; }
                .project-summary h4 { color: #0c4a6e; margin-bottom: 15px; font-size: 16px; }
                .project-features { display: flex; flex-wrap: wrap; gap: 8px; }
                .feature-tag { background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; }
                .preparation-section { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 12px; padding: 20px; margin: 25px 0; }
                .preparation-section h4 { color: #92400e; margin-bottom: 15px; }
                .preparation-list { list-style: none; }
                .preparation-list li { margin: 8px 0; color: #92400e; }
                .preparation-list li:before { content: "✓ "; color: #059669; font-weight: bold; margin-right: 8px; }
                .contact-section { background: #f1f5f9; border-radius: 12px; padding: 20px; margin: 25px 0; text-align: center; }
                .contact-button { display: inline-block; background: linear-gradient(135deg, #00ff00, #1d4ed8); color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 5px; }
                .footer { background: #1e293b; color: #94a3b8; padding: 30px; text-align: center; }
                .footer h4 { color: #f8fafc; margin-bottom: 15px; }
                .social-links { margin: 20px 0; }
                .social-links a { color: #00ff00; text-decoration: none; margin: 0 10px; }
                .warning-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 15px; margin: 20px 0; }
                .warning-box p { color: #dc2626; margin: 0; font-size: 14px; }
                @media (max-width: 600px) {
                    .container { margin: 0; }
                    .header, .content { padding: 20px; }
                    .appointment-details { display: block; }
                    .detail-label, .detail-value { display: block; padding: 4px 0; }
                    .detail-label { font-weight: 600; }
                    .project-features { justify-content: center; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <!-- Header -->
                <div class="header">
                    <h1>🎉 Randevunuz Onaylandı!</h1>
                    <p>Web sitesi projeniz için konsültasyon randevunuz başarıyla oluşturuldu</p>
                </div>
                
                <!-- Content -->
                <div class="content">
                    <!-- Success Message -->
                    <div class="success-badge">
                        <h2>✅ Randevu Başarıyla Kaydedildi</h2>
                        <p>Sayın ' . esc_html($data['customer_name']) . ', randevunuz onaylanmıştır.</p>
                    </div>
                    
                    <!-- Appointment Details -->
                    <div class="appointment-card">
                        <div class="appointment-header">
                            <h3>📅 Randevu Detaylarınız</h3>
                        </div>
                        <div class="appointment-details">
                            <div class="detail-row">
                                <div class="detail-label">👤 Müşteri:</div>
                                <div class="detail-value"><strong>' . esc_html($data['customer_name']) . '</strong></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">📅 Tarih:</div>
                                <div class="detail-value"><span class="highlight">' . date('d F Y, l', strtotime($data['appointment_date'])) . '</span></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">🕐 Saat:</div>
                                <div class="detail-value"><span class="highlight">' . esc_html($data['appointment_time']) . '</span></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">💰 Konsültasyon Ücreti:</div>
                                <div class="detail-value"><strong>' . number_format($consultation_fee, 0, ',', '.') . ' ₺</strong></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">⏱️ Süre:</div>
                                <div class="detail-value">45-60 dakika</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">📍 Konum:</div>
                                <div class="detail-value">Online (Zoom/Teams) veya Ofisimiz</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Project Summary -->
                    <div class="project-summary">
                        <h4>🌐 Proje Özeti</h4>
                        <div class="appointment-details">
                            <div class="detail-row">
                                <div class="detail-label">Proje Türü:</div>
                                <div class="detail-value">' . esc_html($data['project_type']) . '</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Tahmini Fiyat:</div>
                                <div class="detail-value">' . esc_html($data['estimated_price']) . '</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Sayfa Sayısı:</div>
                                <div class="detail-value">' . esc_html($data['page_count']) . ' sayfa</div>
                            </div>
                        </div>
                        ' . (!empty($data['selected_features']) ? '
                        <div style="margin-top: 15px;">
                            <strong>Seçilen Özellikler:</strong>
                            <div class="project-features">
                                ' . implode('', array_map(function($feature) {
                                    return '<span class="feature-tag">' . esc_html($feature) . '</span>';
                                }, $data['selected_features'])) . '
                            </div>
                        </div>
                        ' : '') . '
                    </div>
                    
                    <!-- Preparation Section -->
                    <div class="preparation-section">
                        <h4>📋 Görüşme Öncesi Hazırlık</h4>
                        <ul class="preparation-list">
                            <li>Mevcut web siteniz varsa adresini not edin</li>
                            <li>Beğendiğiniz rakip/örnek siteleri belirleyin</li>
                            <li>Logo ve marka materyallerinizi hazırlayın</li>
                            <li>İçerik metinlerinizi (hakkımızda, hizmetler vb.) düşünün</li>
                            <li>Fotoğraf/görsel ihtiyaçlarınızı listeleyin</li>
                            <li>Bütçe aralığınızı netleştirin</li>
                            <li>Proje teslim tarih beklentinizi belirleyin</li>
                        </ul>
                    </div>
                    
                    <!-- Contact Section -->
                    <div class="contact-section">
                        <h4>📞 İletişim & Destek</h4>
                        <p style="margin-bottom: 15px;">Randevunuzla ilgili sorularınız için:</p>
                        <a href="tel:+905551234567" class="contact-button">📞 Hemen Ara</a>
                        <a href="https://wa.me/905551234567" class="contact-button">💬 WhatsApp</a>
                        <a href="mailto:info@morpheodijital.com" class="contact-button">📧 E-posta</a>
                    </div>
                    
                    <!-- Warning -->
                    <div class="warning-box">
                        <p><strong>⚠️ Önemli:</strong> Randevunuza katılamayacaksanız lütfen en az 24 saat önceden bizi bilgilendirin. Geç iptal durumunda konsültasyon ücreti iade edilmez.</p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="footer">
                    <h4>Morpheo Dijital</h4>
                    <p>Profesyonel Web Tasarım & Dijital Pazarlama</p>
                    <div class="social-links">
                        <a href="https://morpheodijital.com">🌐 Web Sitesi</a>
                        <a href="https://instagram.com/morpheodijital">📱 Instagram</a>
                        <a href="https://linkedin.com/company/morpheodijital">💼 LinkedIn</a>
                    </div>
                    <p style="font-size: 12px; margin-top: 20px; color: #64748b;">
                        Bu e-posta otomatik olarak gönderilmiştir. Lütfen yanıtlamayın.<br>
                        © 2024 Morpheo Dijital. Tüm hakları saklıdır.
                    </p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Admin notification email
     */
    public static function getAdminNotificationEmail($data) {
        $consultation_fee = get_option('morpheo_consultation_fee', '250');
        
        return '
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Yeni Randevu - Morpheo Dijital</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; background-color: #f8fafc; }
                .container { max-width: 700px; margin: 0 auto; background: #ffffff; }
                .header { background: linear-gradient(135deg, #dc2626, #991b1b); padding: 30px; text-align: center; }
                .header h1 { color: #ffffff; font-size: 24px; font-weight: 700; margin-bottom: 8px; }
                .header p { color: rgba(255,255,255,0.9); font-size: 14px; }
                .content { padding: 30px; }
                .alert-badge { background: #fef2f2; color: #dc2626; padding: 15px 20px; border-radius: 12px; text-align: center; margin-bottom: 25px; border-left: 4px solid #dc2626; }
                .alert-badge h2 { font-size: 18px; margin-bottom: 5px; }
                .customer-card { background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 16px; padding: 25px; margin: 20px 0; }
                .customer-header { background: #1e293b; color: white; padding: 15px 20px; margin: -25px -25px 20px -25px; border-radius: 16px 16px 0 0; }
                .customer-header h3 { font-size: 16px; margin: 0; }
                .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0; }
                .info-section { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; }
                .info-section h4 { color: #1e293b; margin-bottom: 15px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
                .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
                .detail-row:last-child { border-bottom: none; }
                .detail-label { font-weight: 600; color: #64748b; font-size: 13px; }
                .detail-value { color: #1e293b; font-weight: 500; text-align: right; }
                .priority-high { background: #fef2f2; border-color: #fecaca; }
                .priority-high h4 { color: #dc2626; }
                .project-details { background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 12px; padding: 20px; margin: 20px 0; }
                .project-details h4 { color: #0c4a6e; margin-bottom: 15px; }
                .features-list { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
                .feature-tag { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 16px; font-size: 11px; font-weight: 500; }
                .action-buttons { text-align: center; margin: 30px 0; }
                .action-button { display: inline-block; padding: 12px 24px; margin: 5px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; }
                .btn-primary { background: linear-gradient(135deg, #00ff00, #1d4ed8); color: white; }
                .btn-secondary { background: #64748b; color: white; }
                .btn-success { background: #059669; color: white; }
                .stats-section { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 12px; padding: 20px; margin: 20px 0; }
                .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px; text-align: center; }
                .stat-item { background: white; padding: 15px; border-radius: 8px; }
                .stat-number { font-size: 20px; font-weight: 700; color: #92400e; }
                .stat-label { font-size: 11px; color: #92400e; text-transform: uppercase; }
                .footer { background: #1e293b; color: #94a3b8; padding: 20px; text-align: center; font-size: 12px; }
                @media (max-width: 600px) {
                    .container { margin: 0; }
                    .header, .content { padding: 20px; }
                    .info-grid { grid-template-columns: 1fr; }
                    .detail-row { flex-direction: column; align-items: flex-start; }
                    .detail-value { text-align: left; margin-top: 4px; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <!-- Header -->
                <div class="header">
                    <h1>🚨 Yeni Randevu Bildirimi</h1>
                    <p>Web sitesi konsültasyon randevusu oluşturuldu</p>
                </div>
                
                <!-- Content -->
                <div class="content">
                    <!-- Alert Message -->
                    <div class="alert-badge">
                        <h2>⚡ Acil: Yeni Müşteri Randevusu</h2>
                        <p>Hemen müşteriyle iletişime geçin ve randevuyu onaylayın!</p>
                    </div>
                    
                    <!-- Customer Information -->
                    <div class="customer-card">
                        <div class="customer-header">
                            <h3>👤 Müşteri Bilgileri</h3>
                        </div>
                        <div class="info-grid">
                            <div class="info-section priority-high">
                                <h4>🔥 Öncelikli Bilgiler</h4>
                                <div class="detail-row">
                                    <span class="detail-label">Ad Soyad:</span>
                                    <span class="detail-value"><strong>' . esc_html($data['customer_name']) . '</strong></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Telefon:</span>
                                    <span class="detail-value"><a href="tel:' . esc_attr($data['phone']) . '" style="color: #dc2626; text-decoration: none;"><strong>' . esc_html($data['phone']) . '</strong></a></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">E-posta:</span>
                                    <span class="detail-value"><a href="mailto:' . esc_attr($data['email']) . '" style="color: #dc2626; text-decoration: none;">' . esc_html($data['email']) . '</a></span>
                                </div>
                            </div>
                            
                            <div class="info-section">
                                <h4>📋 Ek Bilgiler</h4>
                                <div class="detail-row">
                                    <span class="detail-label">Şirket:</span>
                                    <span class="detail-value">' . (esc_html($data['company']) ?: 'Belirtilmemiş') . '</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Şehir:</span>
                                    <span class="detail-value">' . (esc_html($data['city']) ?: 'Belirtilmemiş') . '</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Kayıt Tarihi:</span>
                                    <span class="detail-value">' . date('d.m.Y H:i') . '</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Appointment Details -->
                    <div class="info-grid">
                        <div class="info-section priority-high">
                            <h4>📅 Randevu Detayları</h4>
                            <div class="detail-row">
                                <span class="detail-label">Tarih:</span>
                                <span class="detail-value"><strong>' . date('d F Y, l', strtotime($data['appointment_date'])) . '</strong></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Saat:</span>
                                <span class="detail-value"><strong>' . esc_html($data['appointment_time']) . '</strong></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Ücret:</span>
                                <span class="detail-value"><strong>' . number_format($consultation_fee, 0, ',', '.') . ' ₺</strong></span>
                            </div>
                        </div>
                        
                        <div class="info-section">
                            <h4>💰 Finansal Bilgiler</h4>
                            <div class="detail-row">
                                <span class="detail-label">Tahmini Proje:</span>
                                <span class="detail-value">' . esc_html($data['estimated_price']) . '</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Ödeme Durumu:</span>
                                <span class="detail-value" style="color: #f59e0b;"><strong>Beklemede</strong></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Potansiyel Gelir:</span>
                                <span class="detail-value" style="color: #059669;"><strong>' . esc_html($data['estimated_price']) . '</strong></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Project Details -->
                    <div class="project-details">
                        <h4>🌐 Proje Detayları</h4>
                        <div class="info-grid">
                            <div>
                                <div class="detail-row">
                                    <span class="detail-label">Proje Türü:</span>
                                    <span class="detail-value">' . esc_html($data['project_type']) . '</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Sayfa Sayısı:</span>
                                    <span class="detail-value">' . esc_html($data['page_count']) . ' sayfa</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Tasarım Seviyesi:</span>
                                    <span class="detail-value">' . esc_html($data['design_level']) . '</span>
                                </div>
                            </div>
                            <div>
                                <div class="detail-row">
                                    <span class="detail-label">İşletme Türü:</span>
                                    <span class="detail-value">' . esc_html($data['business_type']) . '</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Online Ödeme:</span>
                                    <span class="detail-value">' . esc_html($data['online_payment']) . '</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Aciliyet:</span>
                                    <span class="detail-value" style="color: #dc2626;"><strong>Yüksek</strong></span>
                                </div>
                            </div>
                        </div>
                        ' . (!empty($data['selected_features']) ? '
                        <div style="margin-top: 15px;">
                            <strong>Seçilen Özellikler:</strong>
                            <div class="features-list">
                                ' . implode('', array_map(function($feature) {
                                    return '<span class="feature-tag">' . esc_html($feature) . '</span>';
                                }, $data['selected_features'])) . '
                            </div>
                        </div>
                        ' : '') . '
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="action-buttons">
                        <a href="tel:' . esc_attr($data['phone']) . '" class="action-button btn-primary">📞 Hemen Ara</a>
                        <a href="https://wa.me/90' . preg_replace('/[^0-9]/', '', $data['phone']) . '" class="action-button btn-success">💬 WhatsApp</a>
                        <a href="mailto:' . esc_attr($data['email']) . '" class="action-button btn-secondary">📧 E-posta Gönder</a>
                        <a href="' . admin_url('admin.php?page=morpheo-calculator-appointments') . '" class="action-button btn-secondary">⚙️ Admin Panel</a>
                    </div>
                    
                    <!-- Statistics -->
                    <div class="stats-section">
                        <h4 style="text-align: center; color: #92400e; margin-bottom: 20px;">📊 Bu Ay İstatistikleri</h4>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-number">' . self::getMonthlyStats('appointments') . '</div>
                                <div class="stat-label">Randevu</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">' . self::getMonthlyStats('calculations') . '</div>
                                <div class="stat-label">Hesaplama</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">' . number_format(self::getMonthlyStats('revenue'), 0, ',', '.') . '₺</div>
                                <div class="stat-label">Gelir</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">' . self::getMonthlyStats('conversion') . '%</div>
                                <div class="stat-label">Dönüşüm</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="footer">
                    <p><strong>Morpheo Dijital Admin Panel</strong></p>
                    <p>Bu e-posta otomatik olarak gönderilmiştir. Hemen müşteriyle iletişime geçin!</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Get monthly statistics
     */
    private static function getMonthlyStats($type) {
        global $wpdb;
        
        $current_month = date('Y-m');
        
        switch($type) {
            case 'appointments':
                $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
                return $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $appointments_table WHERE DATE_FORMAT(created_at, '%%Y-%%m') = %s",
                    $current_month
                ));
                
            case 'calculations':
                $results_table = $wpdb->prefix . 'morpheo_calculator_results';
                return $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $results_table WHERE DATE_FORMAT(created_at, '%%Y-%%m') = %s",
                    $current_month
                ));
                
            case 'revenue':
                $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
                return $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(payment_amount) FROM $appointments_table 
                     WHERE DATE_FORMAT(created_at, '%%Y-%%m') = %s 
                     AND payment_status IN ('paid', 'completed')",
                    $current_month
                )) ?: 0;
                
            case 'conversion':
                $results_table = $wpdb->prefix . 'morpheo_calculator_results';
                $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
                
                $calculations = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $results_table WHERE DATE_FORMAT(created_at, '%%Y-%%m') = %s",
                    $current_month
                ));
                
                $appointments = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $appointments_table WHERE DATE_FORMAT(created_at, '%%Y-%%m') = %s",
                    $current_month
                ));
                
                return $calculations > 0 ? round(($appointments / $calculations) * 100, 1) : 0;
                
            default:
                return 0;
        }
    }
}
