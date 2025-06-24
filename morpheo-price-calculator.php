<?php
/**
 * Plugin Name: Morpheo Dijital Website Price Calculator
 * Plugin URI: https://morpheodijital.com
 * Description: Professional website price calculator with dark mode, e-commerce modules, and appointment booking
 * Version: 2.0.0
 * Author: Morpheo Dijital
 * License: GPL v2 or later
 * Text Domain: morpheo-calculator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MORPHEO_CALC_VERSION', '2.0.0');
define('MORPHEO_CALC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MORPHEO_CALC_PLUGIN_PATH', plugin_dir_path(__FILE__));

class MorpheoCalculator {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_save_calculator_data', array($this, 'save_calculator_data'));
        add_action('wp_ajax_nopriv_save_calculator_data', array($this, 'save_calculator_data'));
        add_action('wp_ajax_book_appointment', array($this, 'book_appointment'));
        add_action('wp_ajax_nopriv_book_appointment', array($this, 'book_appointment'));
        
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
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
            'nonce' => wp_create_nonce('morpheo_calculator_nonce')
        ));
    }
    
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'morpheo-calculator') !== false) {
            wp_enqueue_script('morpheo-admin-js', MORPHEO_CALC_PLUGIN_URL . 'assets/admin.js', array('jquery'), MORPHEO_CALC_VERSION, true);
            wp_enqueue_style('morpheo-admin-css', MORPHEO_CALC_PLUGIN_URL . 'assets/admin.css', array(), MORPHEO_CALC_VERSION);
        }
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
            wp_send_json_error(array('message' => 'Failed to save data'));
        }
    }
    
    public function book_appointment() {
        check_ajax_referer('morpheo_calculator_nonce', 'nonce');
        
        global $wpdb;
        
        $data = array(
            'calculator_id' => intval($_POST['calculator_id']),
            'appointment_date' => sanitize_text_field($_POST['appointment_date']),
            'appointment_time' => sanitize_text_field($_POST['appointment_time']),
            'payment_status' => 'pending',
            'created_at' => current_time('mysql')
        );
        
        $table_name = $wpdb->prefix . 'morpheo_calculator_appointments';
        $result = $wpdb->insert($table_name, $data);
        
        if ($result) {
            // Send email notification
            $this->send_appointment_email($data);
            wp_send_json_success(array('message' => 'Appointment booked successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to book appointment'));
        }
    }
    
    private function send_appointment_email($appointment_data) {
        $admin_email = get_option('admin_email');
        $subject = 'New Appointment Booking - Morpheo Calculator';
        $message = "New appointment booking:\n\n";
        $message .= "Date: " . $appointment_data['appointment_date'] . "\n";
        $message .= "Time: " . $appointment_data['appointment_time'] . "\n";
        $message .= "Calculator ID: " . $appointment_data['calculator_id'] . "\n";
        
        wp_mail($admin_email, $subject, $message);
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
        flush_rewrite_rules();
    }
    
    public function deactivate() {
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
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (calculator_id) REFERENCES $table_name(id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($sql2);
    }
}

// Initialize the plugin
new MorpheoCalculator();
