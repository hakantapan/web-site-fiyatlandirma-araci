<?php
/**
 * Plugin Name: Morpheo Dijital Website Price Calculator
 * Plugin URI: https://morpheodijital.com
 * GitHub Plugin URI: https://github.com/hakantapan/web-site-fiyatlandirma-araci
 * Description: Professional website price calculator with dark mode, e-commerce modules, and appointment booking
 * Version: 2.3.0
 * Author: Morpheo Dijital
 * License: GPL v2 or later
 * Text Domain: morpheo-calculator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MORPHEO_CALC_VERSION', '2.3.0');
define('MORPHEO_CALC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MORPHEO_CALC_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include required files
require_once MORPHEO_CALC_PLUGIN_PATH . 'includes/email-templates.php';
require_once MORPHEO_CALC_PLUGIN_PATH . 'includes/email-sender.php';
require_once MORPHEO_CALC_PLUGIN_PATH . 'includes/payment-api.php';
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
        
        // WhatsApp AJAX hooks
        add_action('wp_ajax_send_whatsapp_notification', array($this, 'ajax_send_whatsapp_notification'));
        add_action('wp_ajax_nopriv_send_whatsapp_notification', array($this, 'ajax_send_whatsapp_notification'));
        add_action('wp_ajax_test_whatsapp_message', array($this, 'ajax_test_whatsapp_message'));
        
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
        register_setting('morpheo_calculator_options', 'morpheo_whatsapp_enabled', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '0'
        ));
        
        register_setting('morpheo_calculator_options', 'morpheo_whatsapp_token', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ));
        
        register_setting('morpheo_calculator_options', 'morpheo_whatsapp_from', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '905076005662'
        ));
        
        register_setting('morpheo_calculator_options', 'morpheo_whatsapp_admin', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '908503073709'
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
             WHERE appointment_date = %s AND payment_status != 'cancelled'",
            $date
        ));
        
        wp_send_json_success(array('booked_slots' => $booked_slots));
    }
    
    public function book_appointment() {
        check_ajax_referer('morpheo_calculator_nonce', 'nonce');
        
        $calculator_id = intval($_POST['calculator_id']);
        $appointment_date = sanitize_text_field($_POST['appointment_date']);
        $appointment_time = sanitize_text_field($_POST['appointment_time']);
        
        if (!$calculator_id || !$appointment_date || !$appointment_time) {
            wp_send_json_error(array('message' => 'Missing required fields'));
        }
        
        global $wpdb;
        
        // Get calculator data
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        $calculator_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $results_table WHERE id = %d",
            $calculator_id
        ));
        
        if (!$calculator_data) {
            wp_send_json_error(array('message' => 'Calculator data not found'));
        }
        
        // Check if time slot is still available
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $appointments_table 
             WHERE appointment_date = %s AND appointment_time = %s AND payment_status != 'cancelled'",
            $appointment_date, $appointment_time
        ));
        
        if ($existing) {
            wp_send_json_error(array('message' => 'Time slot is no longer available'));
        }
        
        // Create appointment
        $consultation_fee = get_option('morpheo_consultation_fee', '250');
        $appointment_data = array(
            'calculator_id' => $calculator_id,
            'appointment_date' => $appointment_date,
            'appointment_time' => $appointment_time,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_amount' => floatval($consultation_fee),
            'created_at' => current_time('mysql')
        );
        
        $result = $wpdb->insert($appointments_table, $appointment_data);
        
        if ($result) {
            $appointment_id = $wpdb->insert_id;
            
            // Generate payment URL
            $woocommerce_url = get_option('morpheo_woocommerce_url', 'https://morpheodijital.com/satis/checkout-link/?urun=web-site-on-gorusme-randevusu');
            
            // Add appointment parameters to payment URL
            $payment_params = array(
                'appointment_id' => $appointment_id,
                'calculator_id' => $calculator_id,
                'ucret' => $consultation_fee
            );
            
            $separator = strpos($woocommerce_url, '?') !== false ? '&' : '?';
            $payment_url = $woocommerce_url . $separator . http_build_query($payment_params);
            
            // Update appointment with payment URL
            $wpdb->update(
                $appointments_table,
                array('payment_url' => $payment_url),
                array('id' => $appointment_id),
                array('%s'),
                array('%d')
            );
            
            // Send confirmation email
            MorpheoEmailSender::sendCustomerConfirmation($appointment_data, $calculator_data, $payment_url);
            MorpheoEmailSender::sendAdminNotification($appointment_data, $calculator_data);
            
            // Send WhatsApp notifications
            MorpheoWhatsAppSender::sendCustomerAppointmentConfirmation($appointment_data, $calculator_data, $payment_url);
            MorpheoWhatsAppSender::sendAdminAppointmentNotification($appointment_data, $calculator_data);
            
            wp_send_json_success(array(
                'message' => 'Appointment booked successfully',
                'appointment_id' => $appointment_id,
                'payment_url' => $payment_url
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to book appointment'));
        }
    }
    
    public function ajax_send_whatsapp_notification() {
        check_ajax_referer('morpheo_calculator_nonce', 'nonce');
        
        $appointment_id = intval($_POST['appointment_id']);
        $type = sanitize_text_field($_POST['type']);
        
        if (!$appointment_id || !$type) {
            wp_send_json_error(array('message' => 'Missing parameters'));
        }
        
        global $wpdb;
        
        // Get appointment and calculator data
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        
        $appointment_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $appointments_table WHERE id = %d",
            $appointment_id
        ), ARRAY_A);
        
        if (!$appointment_data) {
            wp_send_json_error(array('message' => 'Appointment not found'));
        }
        
        $calculator_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $results_table WHERE id = %d",
            $appointment_data['calculator_id']
        ));
        
        if (!$calculator_data) {
            wp_send_json_error(array('message' => 'Calculator data not found'));
        }
        
        $success = false;
        
        switch ($type) {
            case 'new_appointment':
                $success = MorpheoWhatsAppSender::sendAdminAppointmentNotification($appointment_data, $calculator_data);
                break;
            case 'reminder':
                $success = MorpheoWhatsAppSender::sendAppointmentReminder($appointment_data, $calculator_data);
                break;
            case 'payment_confirmation':
                $success = MorpheoWhatsAppSender::sendPaymentConfirmation($appointment_data, $calculator_data);
                break;
        }
        
        if ($success) {
            wp_send_json_success(array('message' => 'WhatsApp notification sent successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to send WhatsApp notification'));
        }
    }
    
    public function ajax_test_whatsapp_message() {
        check_admin_referer('morpheo_admin_nonce', 'nonce');
        
        $result = MorpheoWhatsAppSender::sendTestMessage();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    public function ajax_check_single_payment() {
        check_admin_referer('morpheo_admin_nonce', 'nonce');
        
        $appointment_id = intval($_POST['appointment_id']);
        $email = sanitize_email($_POST['email']);
        
        if (!$appointment_id || !$email) {
            wp_send_json_error(array('message' => 'Missing required parameters'));
        }
        
        // Check payment status via API
        $payment_info = MorpheoPaymentAPI::checkPaymentStatus($email);
        
        if ($payment_info && $payment_info['paid']) {
            // Update appointment status
            global $wpdb;
            $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
            
            $updated = $wpdb->update(
                $appointments_table,
                array(
                    'payment_status' => 'paid',
                    'updated_at' => current_time('mysql'),
                    'notes' => 'Ödeme API ile doğrulandı: ' . date('d.m.Y H:i')
                ),
                array('id' => $appointment_id),
                array('%s', '%s', '%s'),
                array('%d')
            );
            
            if ($updated) {
                wp_send_json_success(array(
                    'message' => 'Ödeme doğrulandı ve randevu durumu güncellendi',
                    'payment_info' => $payment_info
                ));
            } else {
                wp_send_json_error(array('message' => 'Ödeme doğrulandı ancak veritabanı güncellenemedi'));
            }
        } else {
            wp_send_json_success(array(
                'message' => 'Henüz ödeme alınmamış',
                'payment_info' => $payment_info
            ));
        }
    }
    
    public function ajax_get_api_response() {
        check_admin_referer('morpheo_admin_nonce', 'nonce');
        
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
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Morpheo Calculator Admin Debug'
            )
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'API isteği başarısız: ' . $response->get_error_message()));
        }
        
        $body = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);
        
        // Try to parse as JSON
        $parsed = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $parsed = array('error' => 'JSON parse hatası', 'raw_response' => $body);
        }
        
        wp_send_json_success(array(
            'url' => $url,
            'status_code' => $status_code,
            'response' => $body,
            'parsed' => $parsed
        ));
    }
    
    public function ajax_get_result_details() {
        check_admin_referer('morpheo_admin_nonce', 'nonce');
        
        $result_id = intval($_POST['result_id']);
        
        if (!$result_id) {
            wp_send_json_error(array('message' => 'Result ID is required'));
        }
        
        global $wpdb;
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $results_table WHERE id = %d",
            $result_id
        ));
        
        if (!$result) {
            wp_send_json_error(array('message' => 'Result not found'));
        }
        
        // Format the result details as HTML
        $features = json_decode($result->features, true);
        $features_list = '';
        if (!empty($features) && is_array($features)) {
            $feature_names = array(
                'seo' => 'SEO Optimizasyonu',
                'cms' => 'İçerik Yönetimi',
                'multilang' => 'Çoklu Dil',
                'payment' => 'Online Ödeme'
            );
            
            foreach ($features as $feature) {
                if (isset($feature_names[$feature])) {
                    $features_list .= '<span class="feature-badge">' . esc_html($feature_names[$feature]) . '</span>';
                }
            }
        }
        
        $html = '
        <div class="result-details">
            <div class="detail-section">
                <h4>👤 Müşteri Bilgileri</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Ad Soyad:</span>
                        <span class="detail-value">' . esc_html($result->first_name . ' ' . $result->last_name) . '</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">E-posta:</span>
                        <span class="detail-value"><a href="mailto:' . esc_attr($result->email) . '">' . esc_html($result->email) . '</a></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Telefon:</span>
                        <span class="detail-value"><a href="tel:' . esc_attr($result->phone) . '">' . esc_html($result->phone) . '</a></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Şirket:</span>
                        <span class="detail-value">' . esc_html($result->company ?: 'Belirtilmemiş') . '</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Şehir:</span>
                        <span class="detail-value">' . esc_html($result->city ?: 'Belirtilmemiş') . '</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4>🌐 Proje Detayları</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Website Türü:</span>
                        <span class="detail-value">' . esc_html(ucfirst($result->website_type)) . '</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Sayfa Sayısı:</span>
                        <span class="detail-value">' . esc_html($result->page_count) . ' sayfa</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tasarım Karmaşıklığı:</span>
                        <span class="detail-value">' . esc_html(ucfirst($result->design_complexity)) . '</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Zaman Çizelgesi:</span>
                        <span class="detail-value">' . esc_html(ucfirst($result->timeline)) . '</span>
                    </div>
                </div>
                
                ' . ($features_list ? '
                <div class="detail-item">
                    <span class="detail-label">Seçilen Özellikler:</span>
                    <div class="features-container">' . $features_list . '</div>
                </div>
                ' : '') . '
            </div>
            
            <div class="detail-section">
                <h4>💰 Fiyat Bilgileri</h4>
                <div class="price-info">
                    <div class="price-range">
                        <span class="price-min">' . number_format($result->min_price, 0, ',', '.') . ' ₺</span>
                        <span class="price-separator">-</span>
                        <span class="price-max">' . number_format($result->max_price, 0, ',', '.') . ' ₺</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4>📅 Kayıt Bilgileri</h4>
                <div class="detail-item">
                    <span class="detail-label">Oluşturulma Tarihi:</span>
                    <span class="detail-value">' . date('d.m.Y H:i', strtotime($result->created_at)) . '</span>
                </div>
            </div>
        </div>
        
        <style>
        .result-details { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .detail-section { margin-bottom: 25px; padding: 20px; background: #f8fafc; border-radius: 8px; border-left: 4px solid #3498db; }
        .detail-section h4 { margin: 0 0 15px 0; color: #2c3e50; font-size: 16px; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .detail-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .detail-item:last-child { border-bottom: none; }
        .detail-label { font-weight: 600; color: #64748b; }
        .detail-value { color: #1e293b; }
        .detail-value a { color: #3498db; text-decoration: none; }
        .detail-value a:hover { text-decoration: underline; }
        .features-container { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .feature-badge { background: #e3f2fd; color: #1976d2; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .price-info { text-align: center; }
        .price-range { font-size: 24px; font-weight: 700; color: #27ae60; }
        .price-separator { margin: 0 10px; color: #95a5a6; }
        @media (max-width: 600px) {
            .detail-grid { grid-template-columns: 1fr; }
            .detail-item { flex-direction: column; }
            .detail-value { margin-top: 4px; }
        }
        </style>
        ';
        
        wp_send_json_success(array('html' => $html));
    }
    
    public function send_appointment_reminders() {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        
        // Get appointments for tomorrow that haven't been reminded
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        $appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, r.* FROM $appointments_table a 
             LEFT JOIN $results_table r ON a.calculator_id = r.id 
             WHERE a.appointment_date = %s 
             AND a.payment_status = 'paid'
             AND (a.reminder_sent IS NULL OR a.reminder_sent = 0)",
            $tomorrow
        ));
        
        foreach ($appointments as $appointment) {
            // Send email reminder
            MorpheoEmailSender::sendAppointmentReminder($appointment, $appointment);
            
            // Send WhatsApp reminder
            MorpheoWhatsAppSender::sendAppointmentReminder($appointment, $appointment);
            
            // Mark as reminded
            $wpdb->update(
                $appointments_table,
                array('reminder_sent' => 1),
                array('id' => $appointment->id)
            );
        }
    }
    
            
            // Mark as reminded
            $wpdb->update(
                $appointments_table,
                array('reminder_sent' => 1),
                array('id' => $appointment->id)
            );
        }
    }
    
    public function add_cron_intervals($schedules) {
        $schedules['morpheo_10min'] = array(
            'interval' => 600, // 10 minutes
            'display' => __('Every 10 Minutes')
        );
        return $schedules;
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
        
        // Set default options
        add_option('morpheo_woocommerce_url', 'https://morpheodijital.com/satis/checkout-link/?urun=web-site-on-gorusme-randevusu');
        add_option('morpheo_consultation_fee', '250');
        add_option('morpheo_admin_emails', '');
        add_option('morpheo_whatsapp_enabled', '0');
        add_option('morpheo_whatsapp_token', '');
        add_option('morpheo_whatsapp_from', '905076005662');
        add_option('morpheo_whatsapp_admin', '908503073709');
        
        // Schedule cron jobs
        if (!wp_next_scheduled('morpheo_send_appointment_reminders')) {
            wp_schedule_event(time(), 'daily', 'morpheo_send_appointment_reminders');
        }
        
        if (!wp_next_scheduled('morpheo_check_payments')) {
            wp_schedule_event(time(), 'morpheo_10min', 'morpheo_check_payments');
        }
        
        if (!wp_next_scheduled('morpheo_cleanup_expired')) {
            wp_schedule_event(time(), 'daily', 'morpheo_cleanup_expired');
        }
    }
    
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('morpheo_send_appointment_reminders');
        wp_clear_scheduled_hook('morpheo_check_payments');
        wp_clear_scheduled_hook('morpheo_cleanup_expired');
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Calculator results table
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        $results_sql = "CREATE TABLE $results_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            website_type varchar(50) NOT NULL,
            page_count int(11) NOT NULL,
            features text,
            design_complexity varchar(50) NOT NULL,
            timeline varchar(50) NOT NULL,
            technical_seo varchar(50) DEFAULT '',
            management_features varchar(50) DEFAULT '',
            security_features varchar(50) DEFAULT '',
            ecommerce_modules varchar(50) DEFAULT '',
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) NOT NULL,
            company varchar(200) DEFAULT '',
            city varchar(100) DEFAULT '',
            min_price decimal(10,2) NOT NULL,
            max_price decimal(10,2) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Appointments table
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $appointments_sql = "CREATE TABLE $appointments_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            calculator_id mediumint(9) NOT NULL,
            appointment_date date NOT NULL,
            appointment_time time NOT NULL,
            status varchar(20) DEFAULT 'pending',
            payment_status varchar(20) DEFAULT 'pending',
            payment_amount decimal(10,2) DEFAULT 0,
            payment_url text,
            notes text,
            reminder_sent tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY calculator_id (calculator_id),
            KEY appointment_date (appointment_date),
            KEY status (status),
            KEY payment_status (payment_status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($results_sql);
        dbDelta($appointments_sql);
    }
}

// Initialize the plugin
new MorpheoCalculator();
