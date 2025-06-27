<?php
/**
 * Plugin Name: Morpheo Dijital Website Price Calculator
 * Plugin URI: https://morpheodijital.com
 * GitHub Plugin URI: https://github.com/hakantapan/web-site-fiyatlandirma-araci
 * Description: Professional website price calculator with dark mode, e-commerce modules, and appointment booking
 * Version: 2.2.1
 * Author: Morpheo Dijital
 * License: GPL v2 or later
 * Text Domain: morpheo-calculator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MORPHEO_CALC_VERSION', '2.2.1');
define('MORPHEO_CALC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MORPHEO_CALC_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include required files
require_once MORPHEO_CALC_PLUGIN_PATH . 'includes/email-templates.php';
require_once MORPHEO_CALC_PLUGIN_PATH . 'includes/email-sender.php';
require_once MORPHEO_CALC_PLUGIN_PATH . 'includes/payment-api.php';
require_once MORPHEO_CALC_PLUGIN_PATH . 'includes/payment-reminder.php';
require_once MORPHEO_CALC_PLUGIN_PATH . 'includes/whatsapp-sender.php';

class MorpheoCalculator {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_save_calculator_data', array($this, 'save_calculator_data'));
        add_action('wp_ajax_nopriv_save_calculator_data', array($this, 'save_calculator_data'));
        add_action('wp_ajax_get_available_time_slots', array($this, 'get_available_time_slots'));
        add_action('wp_ajax_nopriv_get_available_time_slots', array($this, 'get_available_time_slots'));
        add_action('wp_ajax_book_appointment', array($this, 'book_appointment'));
        add_action('wp_ajax_nopriv_book_appointment', array($this, 'book_appointment'));
        
        // Admin AJAX hooks
        add_action('wp_ajax_check_single_payment', array($this, 'ajax_check_single_payment'));
        add_action('wp_ajax_get_api_response', array($this, 'ajax_get_api_response'));
        add_action('wp_ajax_get_morpheo_result_details', array($this, 'ajax_get_result_details'));
        
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Schedule reminder emails
        add_action('morpheo_send_appointment_reminders', array($this, 'send_appointment_reminders'));
        if (!wp_next_scheduled('morpheo_send_appointment_reminders')) {
            wp_schedule_event(time(), 'daily', 'morpheo_send_appointment_reminders');
        }
        
        // Schedule payment checks (every 10 minutes)
        add_action('morpheo_check_payments', array('MorpheoPaymentAPI', 'checkAllPendingPayments'));
        if (!wp_next_scheduled('morpheo_check_payments')) {
            wp_schedule_event(time(), 'morpheo_10min', 'morpheo_check_payments');
        }
        
        // Schedule expired appointment cleanup (daily)
        add_action('morpheo_cleanup_expired', array('MorpheoPaymentAPI', 'cancelExpiredAppointments'));
        if (!wp_next_scheduled('morpheo_cleanup_expired')) {
            wp_schedule_event(time(), 'daily', 'morpheo_cleanup_expired');
        }
    }
    
    public function init() {
        // Add shortcode
        add_shortcode('morpheo_web_calculator', array($this, 'calculator_shortcode'));
        
        // Add custom cron intervals
        add_filter('cron_schedules', array($this, 'add_cron_intervals'));
        
        // Load text domain
        load_plugin_textdomain('morpheo-calculator', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('morpheo-calculator-js', MORPHEO_CALC_PLUGIN_URL . 'assets/calculator.js', array('jquery'), MORPHEO_CALC_VERSION, true);
        wp_enqueue_style('morpheo-calculator-css', MORPHEO_CALC_PLUGIN_URL . 'assets/calculator.css', array(), MORPHEO_CALC_VERSION);
        
        // Localize script for AJAX
        wp_localize_script('morpheo-calculator-js', 'morpheo_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('morpheo_calculator_nonce'),
            'booking_url' => esc_url(get_option('morpheo_booking_url', home_url('/iletisim'))),
            'woocommerce_url' => get_option('morpheo_woocommerce_url', 'https://morpheodijital.com/satis/checkout-link/?urun=web-site-on-gorusme-randevusu'),
            'consultation_fee' => get_option('morpheo_consultation_fee', '250')
        ));
    }
    
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'morpheo-calculator') !== false) {
            wp_enqueue_script('morpheo-admin-js', MORPHEO_CALC_PLUGIN_URL . 'assets/admin.js', array('jquery'), MORPHEO_CALC_VERSION, true);
            wp_enqueue_style('morpheo-admin-css', MORPHEO_CALC_PLUGIN_URL . 'assets/admin.css', array(), MORPHEO_CALC_VERSION);
            
            wp_localize_script('morpheo-admin-js', 'morpheo_admin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('morpheo_admin_nonce')
            ));
        }
    }

    public function register_settings() {
        register_setting('morpheo_calculator_options', 'morpheo_woocommerce_url', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => 'https://morpheodijital.com/satis/checkout-link/?urun=web-site-on-gorusme-randevusu'
        ));
        
        register_setting('morpheo_calculator_options', 'morpheo_consultation_fee', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '250'
        ));
        
        register_setting('morpheo_calculator_options', 'morpheo_admin_emails', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ));

        // WhatsApp settings
        register_setting('morpheo_calculator_options', 'morpheo_whatsapp_enable', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'no'
        ));
        register_setting('morpheo_calculator_options', 'morpheo_whatsapp_api_token', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ));
        register_setting('morpheo_calculator_options', 'morpheo_whatsapp_from_number', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Morpheo Calculator',
            'Price Calculator',
            'manage_options',
            'morpheo-calculator',
            array($this, 'admin_page'),
            'dashicons-calculator',
            30
        );
        
        add_submenu_page(
            'morpheo-calculator',
            'Calculator Results',
            'Results',
            'manage_options',
            'morpheo-calculator-results',
            array($this, 'results_page')
        );
        
        add_submenu_page(
            'morpheo-calculator',
            'Appointments',
            'Appointments',
            'manage_options',
            'morpheo-calculator-appointments',
            array($this, 'appointments_page')
        );
        
        add_submenu_page(
            'morpheo-calculator',
            'Payment Status',
            'Payment Status',
            'manage_options',
            'morpheo-calculator-payments',
            array($this, 'payments_page')
        );
    }
    
    public function calculator_shortcode($atts) {
        $atts = shortcode_atts(array(
            'theme' => 'dark',
            'show_appointment' => 'true'
        ), $atts);
        
        ob_start();
        include MORPHEO_CALC_PLUGIN_PATH . 'templates/calculator.php';
        return ob_get_clean();
    }
    
    public function save_calculator_data() {
        check_ajax_referer('morpheo_calculator_nonce', 'nonce');
        
        global $wpdb;
        
        $data = array(
            'website_type' => sanitize_text_field($_POST['website_type']),
            'page_count' => intval($_POST['page_count']),
            'features' => sanitize_text_field($_POST['features']),
            'design_complexity' => sanitize_text_field($_POST['design_complexity']),
            'timeline' => sanitize_text_field($_POST['timeline']),
            'technical_seo' => sanitize_text_field($_POST['technical_seo']),
            'management_features' => sanitize_text_field($_POST['management_features']),
            'security_features' => sanitize_text_field($_POST['security_features']),
            'ecommerce_modules' => sanitize_text_field($_POST['ecommerce_modules']),
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'company' => sanitize_text_field($_POST['company']),
            'city' => sanitize_text_field($_POST['city']),
            'min_price' => floatval($_POST['min_price']),
            'max_price' => floatval($_POST['max_price']),
            'created_at' => current_time('mysql')
        );
        
        $table_name = $wpdb->prefix . 'morpheo_calculator_results';
        $result = $wpdb->insert($table_name, $data);

        if ($result) {
            wp_send_json_success(array('message' => 'Data saved successfully', 'id' => $wpdb->insert_id));
        } else {
            $error_message = $wpdb->last_error;
            error_log('Morpheo Calculator insert failed: ' . $error_message);
            wp_send_json_error(array('message' => 'Failed to save data', 'error' => $error_message));
        }
    }
    
    public function get_available_time_slots() {
        check_ajax_referer('morpheo_calculator_nonce', 'nonce');
        
        $date = sanitize_text_field($_POST['date']);
        
        if (!$date) {
            wp_send_json_error(array('message' => 'Date is required'));
        }
        
        global $wpdb;
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        
        // Get booked time slots for the selected date
        $booked_slots = $wpdb->get_col($wpdb->prepare(
            "SELECT appointment_time FROM $appointments_table 
             WHERE appointment_date = %s 
             AND payment_status IN ('paid', 'confirmed', 'pending')",
            $date
        ));
        
        // Convert to simple time format (HH:MM)
        $booked_times = array();
        foreach ($booked_slots as $slot) {
            $booked_times[] = date('H:i', strtotime($slot));
        }
        
        wp_send_json_success(array('booked_slots' => $booked_times));
    }
    
    public function book_appointment() {
        check_ajax_referer('morpheo_calculator_nonce', 'nonce');
        
        global $wpdb;
        
        $calculator_id = intval($_POST['calculator_id']);
        $appointment_date = sanitize_text_field($_POST['appointment_date']);
        $appointment_time = sanitize_text_field($_POST['appointment_time']);
        $consultation_fee = get_option('morpheo_consultation_fee', '250');
        
        // Check if the time slot is still available
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $appointments_table 
             WHERE appointment_date = %s 
             AND appointment_time = %s 
             AND payment_status IN ('paid', 'confirmed', 'pending')",
            $appointment_date,
            $appointment_time
        ));
        
        if ($existing) {
            wp_send_json_error(array('message' => 'Bu saat dilimi artık müsait değil. Lütfen başka bir saat seçin.'));
        }
        
        // Book the appointment
        $data = array(
            'calculator_id' => $calculator_id,
            'appointment_date' => $appointment_date,
            'appointment_time' => $appointment_time,
            'payment_status' => 'pending',
            'payment_amount' => floatval($consultation_fee),
            'created_at' => current_time('mysql')
        );
        
        $result = $wpdb->insert($appointments_table, $data);
        
        if ($result) {
            $appointment_id = $wpdb->insert_id;
            
            // Get calculator data for emails and WhatsApp
            $results_table = $wpdb->prefix . 'morpheo_calculator_results';
            $calculator_data = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $results_table WHERE id = %d",
                $calculator_id
            ));
            
            if ($calculator_data) {
                // Create payment URL parameters
                $payment_params = array(
                    'randevu_tarihi' => $appointment_date,
                    'randevu_saati' => $appointment_time,
                    'musteri_adi' => $calculator_data->first_name . ' ' . $calculator_data->last_name,
                    'musteri_email' => $calculator_data->email,
                    'musteri_telefon' => $calculator_data->phone,
                    'proje_tipi' => $calculator_data->website_type,
                    'calculator_id' => $calculator_id,
                    'appointment_id' => $appointment_id,
                    'ucret' => $consultation_fee,
                    'urun' => 'web-site-konsultasyon'
                );
                
                // Build the payment URL properly
                $base_url = 'https://morpheodijital.com/satis/';
                $payment_url = $base_url . '?' . http_build_query($payment_params, '', '&', PHP_QUERY_RFC3986);
                
                // Log the generated payment URL for debugging
                error_log('Morpheo Calculator: Generated Payment URL: ' . $payment_url);
                error_log('Morpheo Calculator: Payment Parameters: ' . print_r($payment_params, true));

                // Prepare appointment data
                $appointment_data = array(
                    'appointment_id' => $appointment_id,
                    'appointment_date' => $appointment_date,
                    'appointment_time' => $appointment_time
                );

                // Send customer confirmation email with payment link
                MorpheoEmailSender::sendCustomerConfirmation($appointment_data, $calculator_data, $payment_url);
                
                // Send admin notification email
                MorpheoEmailSender::sendAdminNotification($appointment_data, $calculator_data);

                // Send customer confirmation WhatsApp message
                MorpheoWhatsAppSender::sendCustomerConfirmationWhatsApp($appointment_data, $calculator_data, $payment_url);

                // Send admin notification WhatsApp message
                MorpheoWhatsAppSender::sendAdminNotificationWhatsApp($appointment_data, $calculator_data);
            }
            
            wp_send_json_success(array(
                'message' => 'Appointment booked successfully',
                'appointment_id' => $appointment_id,
                'payment_url' => $payment_url
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to book appointment'));
        }
    }
    
    // New AJAX handlers for admin
    public function ajax_check_single_payment() {
        check_ajax_referer('morpheo_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $appointment_id = intval($_POST['appointment_id']);
        $email = sanitize_email($_POST['email']);
        
        if (!$email) {
            wp_send_json_error(array('message' => 'E-posta adresi gerekli'));
        }
        
        // Check payment status via API
        $payment_status = MorpheoPaymentAPI::checkPaymentStatus($email);
        
        if ($payment_status && $payment_status['paid']) {
            // Update appointment status
            global $wpdb;
            $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
            
            $update_result = $wpdb->update(
                $appointments_table,
                array(
                    'payment_status' => 'paid',
                    'updated_at' => current_time('mysql'),
                    'notes' => 'API ile manuel kontrol: ' . date('d.m.Y H:i')
                ),
                array('id' => $appointment_id),
                array('%s', '%s', '%s'),
                array('%d')
            );
            
            if ($update_result) {
                // Get calculator data for WhatsApp
                $results_table = $wpdb->prefix . 'morpheo_calculator_results';
                $calculator_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM $results_table WHERE email = %s",
                    $email
                ));
                if ($calculator_data) {
                    $appointment_obj = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM $appointments_table WHERE id = %d",
                        $appointment_id
                    ));
                    MorpheoWhatsAppSender::sendPaymentConfirmationWhatsApp($appointment_obj, $calculator_data);
                }

                wp_send_json_success(array(
                    'message' => 'Ödeme doğrulandı! Randevu durumu güncellendi.',
                    'payment_info' => $payment_status
                ));
            } else {
                wp_send_json_error(array('message' => 'Veritabanı güncellenirken hata oluştu'));
            }
        } else {
            wp_send_json_success(array(
                'message' => 'Henüz ödeme alınmamış',
                'payment_info' => $payment_status
            ));
        }
    }
    
    public function ajax_get_api_response() {
        check_ajax_referer('morpheo_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $email = sanitize_email($_POST['email']);
        
        if (!$email) {
            wp_send_json_error(array('message' => 'E-posta adresi gerekli'));
        }
        
        // Get raw API response for debugging
        $api_url = 'https://morpheodijital.com/satis/wp-content/themes/snn-brx-child-theme/siparis-sorgula.php';
        $api_key = 't3RcN@f9h$5!ZxLuQ1W#pK7eMv%BdA82';
        
        $url = $api_url . '?' . http_build_query(array(
            'email' => $email,
            'key' => $api_key
        ));
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'API hatası: ' . $response->get_error_message()));
        }
        
        $body = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);
        
        wp_send_json_success(array(
            'url' => $url,
            'status_code' => $status_code,
            'response' => $body,
            'parsed' => MorpheoPaymentAPI::checkPaymentStatus($email)
        ));
    }
    
    // New AJAX handler for result details
    public function ajax_get_result_details() {
        check_ajax_referer('morpheo_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $result_id = intval($_POST['result_id']);
        
        if (!$result_id) {
            wp_send_json_error(array('message' => 'Result ID is required'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'morpheo_calculator_results';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $result_id
        ));
        
        if (!$result) {
            wp_send_json_error(array('message' => 'Result not found'));
        }
        
        // Check if there are any appointments for this result
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $appointments_table WHERE calculator_id = %d ORDER BY created_at DESC",
            $result_id
        ));
        
        // Generate HTML for the modal
        $html = $this->generate_result_details_html($result, $appointments);
        
        wp_send_json_success(array('html' => $html));
    }
    
    private function generate_result_details_html($result, $appointments) {
        $website_types = array(
            'corporate' => 'Kurumsal Website',
            'ecommerce' => 'E-Ticaret Sitesi',
            'blog' => 'Blog/İçerik Sitesi',
            'landing' => 'Özel Kampanya Sayfası'
        );
        
        $design_levels = array(
            'basic' => 'Profesyonel & Sade',
            'custom' => 'Markanıza Özel',
            'premium' => 'Lüks & Etkileyici'
        );
        
        $features = json_decode($result->features, true);
        $feature_names = array(
            'seo' => 'SEO Optimizasyonu',
            'cms' => 'İçerik Yönetimi',
            'multilang' => 'Çoklu Dil',
            'payment' => 'Online Ödeme'
        );
        
        ob_start();
        ?>
        <div class="result-details">
            <div class="detail-section">
                <h3>👤 Müşteri Bilgileri</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <strong>Ad Soyad:</strong>
                        <span><?php echo esc_html($result->first_name . ' ' . $result->last_name); ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>E-posta:</strong>
                        <span><a href="mailto:<?php echo esc_attr($result->email); ?>"><?php echo esc_html($result->email); ?></a></span>
                    </div>
                    <div class="detail-item">
                        <strong>Telefon:</strong>
                        <span><a href="tel:<?php echo esc_attr($result->phone); ?>"><?php echo esc_html($result->phone); ?></a></span>
                    </div>
                    <div class="detail-item">
                        <strong>Şirket:</strong>
                        <span><?php echo esc_html($result->company ?: 'Belirtilmemiş'); ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Şehir:</strong>
                        <span><?php echo esc_html($result->city ?: 'Belirtilmemiş'); ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Kayıt Tarihi:</strong>
                        <span><?php echo date('d.m.Y H:i', strtotime($result->created_at)); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h3>🌐 Proje Detayları</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <strong>Website Türü:</strong>
                        <span><?php echo esc_html($website_types[$result->website_type] ?? ucfirst($result->website_type)); ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Sayfa Sayısı:</strong>
                        <span><?php echo esc_html($result->page_count); ?> sayfa</span>
                    </div>
                    <div class="detail-item">
                        <strong>Tasarım Seviyesi:</strong>
                        <span><?php echo esc_html($design_levels[$result->design_complexity] ?? ucfirst($result->design_complexity)); ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Fiyat Aralığı:</strong>
                        <span class="price-range"><?php echo number_format($result->min_price, 0, ',', '.') . ' - ' . number_format($result->max_price, 0, ',', '.') . ' ₺'; ?></span>
                    </div>
                </div>
                
                <?php if (!empty($features) && is_array($features)): ?>
                <div class="detail-item">
                    <strong>Seçilen Özellikler:</strong>
                    <div class="features-list">
                        <?php foreach ($features as $feature): ?>
                            <?php if (isset($feature_names[$feature])): ?>
                                <span class="feature-tag"><?php echo esc_html($feature_names[$feature]); ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($appointments)): ?>
            <div class="detail-section">
                <h3>📅 Randevular</h3>
                <div class="appointments-list">
                    <?php foreach ($appointments as $appointment): ?>
                        <?php
                        $status_labels = array(
                            'pending' => 'Beklemede',
                            'paid' => 'Ödendi',
                            'confirmed' => 'Onaylandı',
                            'completed' => 'Tamamlandı',
                            'cancelled' => 'İptal'
                        );
                        $status_class = 'status-' . $appointment->payment_status;
                        ?>
                        <div class="appointment-item">
                            <div class="appointment-info">
                                <strong><?php echo date('d.m.Y', strtotime($appointment->appointment_date)); ?></strong>
                                <span class="time"><?php echo date('H:i', strtotime($appointment->appointment_time)); ?></span>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo $status_labels[$appointment->payment_status] ?? ucfirst($appointment->payment_status); ?>
                                </span>
                            </div>
                            <div class="appointment-amount">
                                <?php echo number_format($appointment->payment_amount, 0, ',', '.'); ?> ₺
                            </div>
                            <?php if ($appointment->notes): ?>
                                <div class="appointment-notes">
                                    <small><?php echo esc_html($appointment->notes); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="detail-actions">
                <a href="mailto:<?php echo esc_attr($result->email); ?>" class="button button-primary">📧 E-posta Gönder</a>
                <a href="tel:<?php echo esc_attr($result->phone); ?>" class="button button-secondary">📞 Ara</a>
                <?php if (!empty($appointments)): ?>
                    <a href="<?php echo admin_url('admin.php?page=morpheo-calculator-appointments&customer=' . urlencode($result->email)); ?>" class="button">📅 Randevuları Görüntüle</a>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
        .result-details {
            max-width: 100%;
        }
        
        .detail-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .detail-section:last-child {
            border-bottom: none;
        }
        
        .detail-section h3 {
            margin-bottom: 15px;
            color: #1d4ed8;
            font-size: 16px;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 12px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .detail-item strong {
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detail-item span {
            color: #1f2937;
            font-weight: 500;
        }
        
        .price-range {
            color: #059669 !important;
            font-weight: 700 !important;
            font-size: 16px !important;
        }
        
        .features-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        
        .feature-tag {
            background: #dcfce7;
            color: #166534;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .appointments-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .appointment-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
        }
        
        .appointment-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        
        .appointment-info .time {
            background: #e3f2fd;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d1ecf1; color: #0c5460; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .appointment-amount {
            font-weight: 700;
            color: #059669;
        }
        
        .appointment-notes {
            margin-top: 8px;
            color: #6b7280;
            font-style: italic;
        }
        
        .detail-actions {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        @media (max-width: 600px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
            
            .appointment-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .detail-actions {
                flex-direction: column;
            }
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    public function send_appointment_reminders() {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        
        // Get appointments for tomorrow
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        $appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, r.* FROM $appointments_table a 
             LEFT JOIN $results_table r ON a.calculator_id = r.id 
             WHERE a.appointment_date = %s 
             AND a.payment_status IN ('paid', 'confirmed')
             AND a.reminder_sent = 0",
            $tomorrow
        ));
        
        foreach ($appointments as $appointment) {
            $appointment_data = array(
                'appointment_date' => $appointment->appointment_date,
                'appointment_time' => $appointment->appointment_time
            );
            
            $sent_email = MorpheoEmailSender::sendAppointmentReminder($appointment_data, $appointment);
            $sent_whatsapp = MorpheoWhatsAppSender::sendAppointmentReminderWhatsApp($appointment_data, $appointment);
            
            if ($sent_email || $sent_whatsapp) {
                // Mark reminder as sent if either email or WhatsApp was successful
                $wpdb->update(
                    $appointments_table,
                    array('reminder_sent' => 1),
                    array('id' => $appointment->id),
                    array('%d'),
                    array('%d')
                );
            }
        }
    }
    
    public function admin_page() {
        include MORPHEO_CALC_PLUGIN_PATH . 'admin/admin-page.php';
    }
    
    public function results_page() {
        include MORPHEO_CALC_PLUGIN_PATH . 'admin/results-page.php';
    }
    
    public function appointments_page() {
        include MORPHEO_CALC_PLUGIN_PATH . 'admin/appointments-page.php';
    }
    
    public function payments_page() {
        include MORPHEO_CALC_PLUGIN_PATH . 'admin/payments-page.php';
    }
    
    public function activate() {
        $this->create_tables();
        if (get_option('morpheo_booking_url') === false) {
            add_option('morpheo_booking_url', home_url('/iletisim'));
        }
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        wp_clear_scheduled_hook('morpheo_send_appointment_reminders');
        wp_clear_scheduled_hook('morpheo_check_payments');
        wp_clear_scheduled_hook('morpheo_cleanup_expired');
        flush_rewrite_rules();
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Calculator results table
        $table_name = $wpdb->prefix . 'morpheo_calculator_results';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            website_type varchar(50) NOT NULL,
            page_count int(11) NOT NULL,
            features text,
            design_complexity varchar(50),
            timeline varchar(50),
            technical_seo varchar(50),
            management_features text,
            security_features text,
            ecommerce_modules text,
            first_name varchar(100),
            last_name varchar(100),
            email varchar(100),
            phone varchar(20),
            company varchar(200),
            city varchar(100),
            min_price decimal(10,2),
            max_price decimal(10,2),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Appointments table
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $sql2 = "CREATE TABLE $appointments_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            calculator_id mediumint(9),
            appointment_date date,
            appointment_time time,
            payment_status varchar(20) DEFAULT 'pending',
            payment_amount decimal(10,2) DEFAULT 250.00,
            reminder_sent tinyint(1) DEFAULT 0,
            notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_appointment (appointment_date, appointment_time),
            FOREIGN KEY (calculator_id) REFERENCES $table_name(id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($sql2);
    }

    public function add_cron_intervals($schedules) {
        $schedules['morpheo_10min'] = array(
            'interval' => 600, // 10 minutes
            'display' => 'Every 10 Minutes'
        );
        return $schedules;
    }
}

// Initialize the plugin
new MorpheoCalculator();
