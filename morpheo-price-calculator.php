<?php
/**
 * Plugin Name: Morpheo Dijital Website Price Calculator
 * Plugin URI: https://morpheodijital.com
 * GitHub Plugin URI: https://github.com/hakantapan/web-site-fiyatlandirma-araci
 * Description: Professional website price calculator with dark mode, e-commerce modules, and appointment booking
 * Version: 2.1.0
 * Author: Morpheo Dijital
 * License: GPL v2 or later
 * Text Domain: morpheo-calculator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MORPHEO_CALC_VERSION', '2.1.0');
define('MORPHEO_CALC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MORPHEO_CALC_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include required files
require_once MORPHEO_CALC_PLUGIN_PATH . 'includes/email-templates.php';
require_once MORPHEO_CALC_PLUGIN_PATH . 'includes/email-sender.php';

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
    }
    
    public function init() {
        // Add shortcode
        add_shortcode('morpheo_web_calculator', array($this, 'calculator_shortcode'));
        
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
            'woocommerce_url' => esc_url(get_option('morpheo_woocommerce_url', 'https://odeme.morpheodijital.com/konsultasyon')),
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
        register_setting('morpheo_calculator_options', 'morpheo_booking_url', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => home_url('/iletisim')
        ));
        register_setting('morpheo_calculator_options', 'morpheo_woocommerce_url', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => 'https://odeme.morpheodijital.com/konsultasyon'
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
            if (function_exists('wp_log_error')) {
                wp_log_error('Morpheo Calculator insert failed: ' . $error_message);
            } else {
                error_log('Morpheo Calculator insert failed: ' . $error_message);
            }
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
            
            // Get calculator data for emails
            $results_table = $wpdb->prefix . 'morpheo_calculator_results';
            $calculator_data = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $results_table WHERE id = %d",
                $calculator_id
            ));
            
            if ($calculator_data) {
                // Send emails
                $appointment_data = array(
                    'appointment_id' => $appointment_id,
                    'appointment_date' => $appointment_date,
                    'appointment_time' => $appointment_time
                );
                
                // Send customer confirmation email
                MorpheoEmailSender::sendCustomerConfirmation($appointment_data, $calculator_data);
                
                // Send admin notification email
                MorpheoEmailSender::sendAdminNotification($appointment_data, $calculator_data);
            }
            
            wp_send_json_success(array(
                'message' => 'Appointment booked successfully',
                'appointment_id' => $appointment_id
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to book appointment'));
        }
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
            
            $sent = MorpheoEmailSender::sendAppointmentReminder($appointment_data, $appointment);
            
            if ($sent) {
                // Mark reminder as sent
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
    
    public function activate() {
        $this->create_tables();
        if (get_option('morpheo_booking_url') === false) {
            add_option('morpheo_booking_url', home_url('/iletisim'));
        }
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        wp_clear_scheduled_hook('morpheo_send_appointment_reminders');
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
}

// Initialize the plugin
new MorpheoCalculator();
