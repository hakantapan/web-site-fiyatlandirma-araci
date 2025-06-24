<div class="wrap">
    <h1>Morpheo Dijital Website Price Calculator Settings</h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('morpheo_calculator_options'); ?>
        <div class="card">
            <h2>Appointment Settings</h2>
            <?php $booking_url = esc_url(get_option('morpheo_booking_url', home_url('/iletisim'))); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Appointment Redirect URL</th>
                    <td>
                        <input type="text" name="morpheo_booking_url" value="<?php echo $booking_url; ?>" class="regular-text" />
                        <p class="description">Link to open after confirming appointment.</p>
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
