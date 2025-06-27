<?php
/**
 * Plugin Name: Morpheo Web Sitesi Fiyatlandırma Aracı
 * Plugin URI: https://morpheodijital.com
 * Description: Web sitesi projelerinin fiyatlandırılması ve randevu rezervasyonu için gelişmiş hesaplama aracı
 * Version: 2.2.4
 * Author: Morpheo Dijital
 * Author URI: https://morpheodijital.com
 * Text Domain: morpheo-calculator
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MORPHEO_CALCULATOR_VERSION', '2.2.4');
define('MORPHEO_CALCULATOR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MORPHEO_CALCULATOR_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include required files
require_once MORPHEO_CALCULATOR_PLUGIN_PATH . 'includes/payment-api.php';
require_once MORPHEO_CALCULATOR_PLUGIN_PATH . 'includes/payment-reminder.php';
require_once MORPHEO_CALCULATOR_PLUGIN_PATH . 'includes/whatsapp-sender.php';
require_once MORPHEO_CALCULATOR_PLUGIN_PATH . 'includes/email-templates.php';
require_once MORPHEO_CALCULATOR_PLUGIN_PATH . 'includes/email-sender.php';

class MorpheoCalculator {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_book_appointment', array($this, 'book_appointment'));
        add_action('wp_ajax_nopriv_book_appointment', array($this, 'book_appointment'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_shortcode('morpheo_calculator', array($this, 'calculator_shortcode'));
        
        // Payment reminder cron
        add_action('morpheo_payment_reminder_cron', array('MorpheoPaymentReminder', 'send_reminders'));
        if (!wp_next_scheduled('morpheo_payment_reminder_cron')) {
            wp_schedule_event(time(), 'hourly', 'morpheo_payment_reminder_cron');
        }
        
        // Payment status check cron
        add_action('morpheo_payment_check_cron', array('MorpheoPaymentAPI', 'checkAllPendingPayments'));
        if (!wp_next_scheduled('morpheo_payment_check_cron')) {
            wp_schedule_event(time(), 'morpheo_10min', 'morpheo_payment_check_cron');
        }
        
        // Add custom cron interval
        add_filter('cron_schedules', array($this, 'add_cron_intervals'));
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function add_cron_intervals($schedules) {
        $schedules['morpheo_10min'] = array(
            'interval' => 600, // 10 minutes
            'display' => __('Every 10 Minutes')
        );
        return $schedules;
    }
    
    public function init() {
        $this->create_tables();
    }
    
    public function activate() {
        $this->create_tables();
        
        // Schedule cron jobs
        if (!wp_next_scheduled('morpheo_payment_reminder_cron')) {
            wp_schedule_event(time(), 'hourly', 'morpheo_payment_reminder_cron');
        }
        
        if (!wp_next_scheduled('morpheo_payment_check_cron')) {
            wp_schedule_event(time(), 'morpheo_10min', 'morpheo_payment_check_cron');
        }
    }
    
    public function deactivate() {
        wp_clear_scheduled_hook('morpheo_payment_reminder_cron');
        wp_clear_scheduled_hook('morpheo_payment_check_cron');
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Results table
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        $results_sql = "CREATE TABLE $results_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(20) NOT NULL,
            website_type varchar(50) NOT NULL,
            website_type_tr varchar(100) NOT NULL,
            pages int(11) NOT NULL,
            features text NOT NULL,
            features_tr text NOT NULL,
            design_complexity varchar(50) NOT NULL,
            design_complexity_tr varchar(100) NOT NULL,
            timeline varchar(50) NOT NULL,
            timeline_tr varchar(100) NOT NULL,
            price_range varchar(100) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Appointments table
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $appointments_sql = "CREATE TABLE $appointments_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            calculator_id int(11) NOT NULL,
            appointment_date date NOT NULL,
            appointment_time time NOT NULL,
            payment_status varchar(20) DEFAULT 'pending',
            payment_amount decimal(10,2) DEFAULT 500.00,
            payment_url text,
            reminder_sent tinyint(1) DEFAULT 0,
            whatsapp_sent tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (calculator_id) REFERENCES $results_table(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($results_sql);
        dbDelta($appointments_sql);
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('morpheo-calculator-js', MORPHEO_CALCULATOR_PLUGIN_URL . 'assets/calculator.js', array('jquery'), MORPHEO_CALCULATOR_VERSION, true);
        wp_enqueue_style('morpheo-calculator-css', MORPHEO_CALCULATOR_PLUGIN_URL . 'assets/calculator.css', array(), MORPHEO_CALCULATOR_VERSION);
        
        wp_localize_script('morpheo-calculator-js', 'morpheo_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('morpheo_calculator_nonce')
        ));
    }
    
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'morpheo-calculator') !== false) {
            wp_enqueue_style('morpheo-admin-css', MORPHEO_CALCULATOR_PLUGIN_URL . 'assets/admin.css', array(), MORPHEO_CALCULATOR_VERSION);
            wp_enqueue_script('morpheo-admin-js', MORPHEO_CALCULATOR_PLUGIN_URL . 'assets/admin.js', array('jquery'), MORPHEO_CALCULATOR_VERSION, true);
        }
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Morpheo Calculator',
            'Morpheo Calculator',
            'manage_options',
            'morpheo-calculator',
            array($this, 'admin_page'),
            'dashicons-calculator',
            30
        );
        
        add_submenu_page(
            'morpheo-calculator',
            'Sonuçlar',
            'Sonuçlar',
            'manage_options',
            'morpheo-calculator-results',
            array($this, 'results_page')
        );
        
        add_submenu_page(
            'morpheo-calculator',
            'Randevular',
            'Randevular',
            'manage_options',
            'morpheo-calculator-appointments',
            array($this, 'appointments_page')
        );
        
        add_submenu_page(
            'morpheo-calculator',
            'Ödeme Takibi',
            'Ödeme Takibi',
            'manage_options',
            'morpheo-calculator-payments',
            array($this, 'payments_page')
        );
    }
    
    public function admin_page() {
        include MORPHEO_CALCULATOR_PLUGIN_PATH . 'admin/admin-page.php';
    }
    
    public function results_page() {
        include MORPHEO_CALCULATOR_PLUGIN_PATH . 'admin/results-page.php';
    }
    
    public function appointments_page() {
        include MORPHEO_CALCULATOR_PLUGIN_PATH . 'admin/appointments-page.php';
    }
    
    public function payments_page() {
        include MORPHEO_CALCULATOR_PLUGIN_PATH . 'admin/payments-page.php';
    }
    
    public function calculator_shortcode($atts) {
        ob_start();
        include MORPHEO_CALCULATOR_PLUGIN_PATH . 'templates/calculator.php';
        return ob_get_clean();
    }
    
    public function book_appointment() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'morpheo_calculator_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        
        // Get form data
        $calculator_data = json_decode(stripslashes($_POST['calculatorData']), true);
        $appointment_date = sanitize_text_field($_POST['appointmentDate']);
        $appointment_time = sanitize_text_field($_POST['appointmentTime']);
        
        // Insert calculator result
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        $result = $wpdb->insert(
            $results_table,
            array(
                'first_name' => sanitize_text_field($calculator_data['userData']['firstName']),
                'last_name' => sanitize_text_field($calculator_data['userData']['lastName']),
                'email' => sanitize_email($calculator_data['userData']['email']),
                'phone' => sanitize_text_field($calculator_data['userData']['phone']),
                'website_type' => sanitize_text_field($calculator_data['websiteType']),
                'website_type_tr' => sanitize_text_field($this->get_turkish_website_type($calculator_data['websiteType'])),
                'pages' => intval($calculator_data['pages']),
                'features' => sanitize_text_field(implode(', ', $calculator_data['features'])),
                'features_tr' => sanitize_text_field($this->get_turkish_features($calculator_data['features'])),
                'design_complexity' => sanitize_text_field($calculator_data['designComplexity']),
                'design_complexity_tr' => sanitize_text_field($this->get_turkish_design_complexity($calculator_data['designComplexity'])),
                'timeline' => sanitize_text_field($calculator_data['timeline']),
                'timeline_tr' => sanitize_text_field($this->get_turkish_timeline($calculator_data['timeline'])),
                'price_range' => sanitize_text_field($calculator_data['priceRange'])
            )
        );
        
        if ($result === false) {
            wp_send_json_error('Hesaplama sonucu kaydedilemedi');
            return;
        }
        
        $calculator_id = $wpdb->insert_id;
        
        // Create payment URL with all parameters
        $woocommerce_url = get_option('morpheo_woocommerce_url', 'https://morpheodijital.com/satis/');
        $woocommerce_url = rtrim($woocommerce_url, '/') . '/';
        
        $payment_params = array(
            'randevu_tarihi' => $appointment_date,
            'randevu_saati' => $appointment_time,
            'musteri_adi' => $calculator_data['userData']['firstName'] . ' ' . $calculator_data['userData']['lastName'],
            'musteri_email' => $calculator_data['userData']['email'],
            'musteri_telefon' => $calculator_data['userData']['phone'],
            'proje_tipi' => $this->get_turkish_website_type($calculator_data['websiteType']),
            'tahmini_fiyat' => $calculator_data['priceRange'],
            'calculator_id' => $calculator_id,
            'appointment_id' => '' // Will be set after appointment creation
        );
        
        // Insert appointment
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $appointment_result = $wpdb->insert(
            $appointments_table,
            array(
                'calculator_id' => $calculator_id,
                'appointment_date' => $appointment_date,
                'appointment_time' => $appointment_time,
                'payment_status' => 'pending',
                'payment_amount' => 500.00
            )
        );
        
        if ($appointment_result === false) {
            wp_send_json_error('Randevu kaydedilemedi');
            return;
        }
        
        $appointment_id = $wpdb->insert_id;
        
        // Update payment parameters with appointment_id
        $payment_params['appointment_id'] = $appointment_id;
        
        // Create complete payment URL
        $payment_url = $woocommerce_url . '?' . http_build_query($payment_params);
        
        // Update appointment with payment URL
        $wpdb->update(
            $appointments_table,
            array('payment_url' => $payment_url),
            array('id' => $appointment_id)
        );
        
        // Log the payment URL for debugging
        error_log('Morpheo Calculator - Generated Payment URL: ' . $payment_url);
        error_log('Morpheo Calculator - Payment Parameters: ' . print_r($payment_params, true));
        
        // Send confirmation email
        $email_sender = new MorpheoEmailSender();
        $email_sent = $email_sender->send_appointment_confirmation(
            $calculator_data['userData']['email'],
            $calculator_data['userData']['firstName'],
            $appointment_date,
            $appointment_time,
            $payment_url,
            $payment_params
        );
        
        // Send WhatsApp message if phone number is provided and WhatsApp is enabled
        $whatsapp_enabled = get_option('morpheo_whatsapp_enabled', false);
        $whatsapp_sent = false;
        
        if ($whatsapp_enabled && !empty($calculator_data['userData']['phone'])) {
            $whatsapp_sender = new MorpheoWhatsAppSender();
            $whatsapp_sent = $whatsapp_sender->send_appointment_confirmation(
                $calculator_data['userData']['phone'],
                $calculator_data['userData']['firstName'],
                $appointment_date,
                $appointment_time,
                $payment_url,
                $payment_params
            );
            
            // Update WhatsApp sent status
            if ($whatsapp_sent) {
                $wpdb->update(
                    $appointments_table,
                    array('whatsapp_sent' => 1),
                    array('id' => $appointment_id)
                );
            }
        }
        
        wp_send_json_success(array(
            'message' => 'Randevunuz başarıyla oluşturuldu!',
            'calculator_id' => $calculator_id,
            'appointment_id' => $appointment_id,
            'payment_url' => $payment_url,
            'email_sent' => $email_sent,
            'whatsapp_sent' => $whatsapp_sent
        ));
    }
    
    private function get_turkish_website_type($type) {
        $types = array(
            'business' => 'Kurumsal Web Sitesi',
            'ecommerce' => 'E-Ticaret Sitesi',
            'portfolio' => 'Portföy/Kişisel Site',
            'blog' => 'Blog/İçerik Sitesi',
            'landing' => 'Landing Page',
            'custom' => 'Özel Proje'
        );
        return isset($types[$type]) ? $types[$type] : $type;
    }
    
    private function get_turkish_features($features) {
        $feature_map = array(
            'responsive' => 'Mobil Uyumlu Tasarım',
            'seo' => 'SEO Optimizasyonu',
            'cms' => 'İçerik Yönetim Sistemi',
            'ecommerce' => 'E-Ticaret Entegrasyonu',
            'blog' => 'Blog Sistemi',
            'contact' => 'İletişim Formu',
            'gallery' => 'Galeri/Portföy',
            'social' => 'Sosyal Medya Entegrasyonu',
            'analytics' => 'Analytics Entegrasyonu',
            'multilingual' => 'Çoklu Dil Desteği'
        );
        
        $turkish_features = array();
        foreach ($features as $feature) {
            $turkish_features[] = isset($feature_map[$feature]) ? $feature_map[$feature] : $feature;
        }
        
        return implode(', ', $turkish_features);
    }
    
    private function get_turkish_design_complexity($complexity) {
        $complexities = array(
            'simple' => 'Basit Tasarım',
            'moderate' => 'Orta Düzey Tasarım',
            'complex' => 'Karmaşık Tasarım'
        );
        return isset($complexities[$complexity]) ? $complexities[$complexity] : $complexity;
    }
    
    private function get_turkish_timeline($timeline) {
        $timelines = array(
            'asap' => 'En Kısa Sürede',
            '1-2weeks' => '1-2 Hafta',
            '1month' => '1 Ay',
            '2-3months' => '2-3 Ay',
            'flexible' => 'Esnek'
        );
        return isset($timelines[$timeline]) ? $timelines[$timeline] : $timeline;
    }
}

// Initialize the plugin
new MorpheoCalculator();
?>
