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
             WHERE appointment_date =  AND status != 'cancelled'",
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
             WHERE appointment_date = %s AND appointment_time = %s AND status != 'cancelled'",
            $appointment_date, $appointment_time
        ));
        
        if ($existing) {
            wp_send_json_error(array('message' => 'Time slot is no longer available'));
        }
        
        // Create appointment
        $appointment_data = array(
            'calculator_id' => $calculator_id,
            'appointment_date' => $appointment_date,
            'appointment_time' => $appointment_time,
            'status' => 'pending',
            'payment_status' => 'pending',
            'created_at' => current_time('mysql')
        );
        
        $result = $wpdb->insert($appointments_table, $appointment_data);
        
        if ($result) {
            $appointment_id = $wpdb->insert_id;
            
            // Generate payment URL
            $payment_url = MorpheoPaymentAPI::generatePaymentURL($appointment_id, $calculator_data);
            
            // Send confirmation email
            MorpheoEmailSender::sendCustomerAppointmentConfirmation($appointment_data, $calculator_data, $payment_url);
            MorpheoEmailSender::sendAdminAppointmentNotification($appointment_data, $calculator_data);
            
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
        
        if (!$appointment_id) {
            wp_send_json_error(array('message' => 'Appointment ID is required'));
        }
        
        $result = MorpheoPaymentAPI::checkSinglePayment($appointment_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Payment status updated'));
        } else {
            wp_send_json_error(array('message' => 'Failed to check payment status'));
        }
    }
    
    public function ajax_get_api_response() {
        check_admin_referer('morpheo_admin_nonce', 'nonce');
        
        $appointment_id = intval($_POST['appointment_id']);
        
        if (!$appointment_id) {
            wp_send_json_error(array('message' => 'Appointment ID is required'));
        }
        
        $response = MorpheoPaymentAPI::getAPIResponse($appointment_id);
        
        wp_send_json_success(array('response' => $response));
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
        
        if ($result) {
            // Format the data for display
            $formatted_result = array(
                'id' => $result->id,
                'customer_name' => $result->first_name . ' ' . $result->last_name,
                'email' => $result->email,
                'phone' => $result->phone,
                'company' => $result->company,
                'city' => $result->city,
                'website_type' => $result->website_type,
                'page_count' => $result->page_count,
                'features' => json_decode($result->features, true),
                'design_complexity' => $result->design_complexity,
                'timeline' => $result->timeline,
                'technical_seo' => $result->technical_seo,
                'management_features' => $result->management_features,
                'security_features' => $result->security_features,
                'ecommerce_modules' => $result->ecommerce_modules,
                'min_price' => number_format($result->min_price, 0, ',', '.'),
                'max_price' => number_format($result->max_price, 0, ',', '.'),
                'created_at' => date('d.m.Y H:i', strtotime($result->created_at))
            );
            
            wp_send_json_success($formatted_result);
        } else {
            wp_send_json_error(array('message' => 'Result not found'));
        }
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
             AND a.status = 'confirmed' 
             AND a.payment_status = 'completed'
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
            payment_url text,
            api_response text,
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
