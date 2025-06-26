<div class="wrap">
    <h1>Morpheo Dijital Website Price Calculator Settings</h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('morpheo_calculator_options'); ?>
        <div class="card">
            <h2>Konsültasyon Ayarları</h2>
            <?php $woocommerce_url = esc_url(get_option('morpheo_woocommerce_url', 'https://odeme.morpheodijital.com/konsultasyon')); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">WooCommerce Ödeme URL'si</th>
                    <td>
                        <input type="text" name="morpheo_woocommerce_url" value="<?php echo $woocommerce_url; ?>" class="regular-text" />
                        <p class="description">Ücretli konsültasyon ödemesi için WooCommerce ürün sayfası URL'si.</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Konsültasyon Ücreti</th>
                    <td>
                        <?php $consultation_fee = get_option('morpheo_consultation_fee', '250'); ?>
                        <input type="number" name="morpheo_consultation_fee" value="<?php echo $consultation_fee; ?>" class="small-text" /> ₺
                        <p class="description">Konsültasyon randevusu ücreti.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </div>

        <div class="card">
            <h2>Calculator Usage</h2>
        <p>Use the shortcode <code>[morpheo_web_calculator]</code> to display the calculator on any page or post.</p>
        
        <h3>Shortcode Parameters:</h3>
        <ul>
            <li><code>theme</code> - Set default theme (dark/light). Default: dark</li>
            <li><code>show_appointment</code> - Show appointment booking (true/false). Default: true</li>
        </ul>
        
        <h3>Examples:</h3>
        <code>[morpheo_web_calculator theme="dark"]</code><br>
        <code>[morpheo_web_calculator theme="light" show_appointment="false"]</code>
    </div>
    
    <div class="card">
        <h2>Quick Stats</h2>
        <?php
        global $wpdb;
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        
        $total_calculations = $wpdb->get_var("SELECT COUNT(*) FROM $results_table");
        $total_appointments = $wpdb->get_var("SELECT COUNT(*) FROM $appointments_table");
        $pending_appointments = $wpdb->get_var("SELECT COUNT(*) FROM $appointments_table WHERE payment_status = 'pending'");
        ?>
        
        <table class="wp-list-table widefat fixed striped">
            <tr>
                <td><strong>Total Price Calculations:</strong></td>
                <td><?php echo $total_calculations; ?></td>
            </tr>
            <tr>
                <td><strong>Total Appointments:</strong></td>
                <td><?php echo $total_appointments; ?></td>
            </tr>
            <tr>
                <td><strong>Pending Payments:</strong></td>
                <td><?php echo $pending_appointments; ?></td>
            </tr>
        </table>
    </div>
    </form>
</div>
