<div class="wrap">
    <h1>Morpheo Dijital Website Price Calculator Settings</h1>
    
    <?php
    // Handle form submission
    if (isset($_POST['submit']) && check_admin_referer('morpheo_calculator_options-options')) {
        // Update WooCommerce URL
        if (isset($_POST['morpheo_woocommerce_url'])) {
            update_option('morpheo_woocommerce_url', esc_url_raw($_POST['morpheo_woocommerce_url']));
        }
        
        // Update consultation fee
        if (isset($_POST['morpheo_consultation_fee'])) {
            update_option('morpheo_consultation_fee', sanitize_text_field($_POST['morpheo_consultation_fee']));
        }
        
        // Update admin emails
        if (isset($_POST['morpheo_admin_emails'])) {
            update_option('morpheo_admin_emails', sanitize_text_field($_POST['morpheo_admin_emails']));
        }
        
        echo '<div class="notice notice-success is-dismissible"><p>Ayarlar başarıyla kaydedildi!</p></div>';
    }
    ?>
    
    <form method="post" action="">
        <?php wp_nonce_field('morpheo_calculator_options-options'); ?>
        
        <div class="card">
            <h2>Konsültasyon Ayarları</h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">WooCommerce Ödeme URL'si</th>
                    <td>
                        <?php $woocommerce_url = get_option('morpheo_woocommerce_url', 'https://morpheodijital.com/satis/checkout-link/?urun=web-site-on-gorusme-randevusu'); ?>
                        <input type="url" name="morpheo_woocommerce_url" value="<?php echo esc_url($woocommerce_url); ?>" class="regular-text" style="width: 100%; max-width: 600px;" />
                        <p class="description">Ücretli konsültasyon ödemesi için WooCommerce ürün sayfası URL'si.</p>
                        <p class="description"><strong>Varsayılan:</strong> https://morpheodijital.com/salis/checkout-link/?urun=web-site-on-gorusme-randevusu</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Konsültasyon Ücreti</th>
                    <td>
                        <?php $consultation_fee = get_option('morpheo_consultation_fee', '250'); ?>
                        <input type="number" name="morpheo_consultation_fee" value="<?php echo esc_attr($consultation_fee); ?>" class="small-text" min="0" step="1" /> ₺
                        <p class="description">Konsültasyon randevusu ücreti.</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Admin E-posta Adresleri</th>
                    <td>
                        <?php $admin_emails = get_option('morpheo_admin_emails', ''); ?>
                        <input type="text" name="morpheo_admin_emails" value="<?php echo esc_attr($admin_emails); ?>" class="regular-text" />
                        <p class="description">Randevu bildirimlerinin gönderileceği ek e-posta adresleri (virgülle ayırın). Ana admin e-postası otomatik eklenir.</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit" class="button-primary" value="Ayarları Kaydet" />
            </p>
        </div>
    </form>

    <div class="card">
        <h2>📧 E-posta Bildirimleri</h2>
        <div class="email-status">
            <?php
            // Test email functionality
            if (function_exists('wp_mail')) {
                echo '<p style="color: green;">✅ E-posta sistemi aktif</p>';
            } else {
                echo '<p style="color: red;">❌ E-posta sistemi devre dışı</p>';
            }
            ?>
            
            <h4>📋 Gönderilen E-posta Türleri:</h4>
            <ul>
                <li><strong>Müşteri Onay E-postası:</strong> Randevu oluşturulduğunda müşteriye gönderilir</li>
                <li><strong>Admin Bildirim E-postası:</strong> Yeni randevu oluşturulduğunda admin(ler)e gönderilir</li>
                <li><strong>Hatırlatma E-postası:</strong> Randevudan 24 saat önce müşteriye gönderilir</li>
            </ul>
            
            <h4>📊 E-posta İstatistikleri:</h4>
            <?php
            // Get email statistics (you could track these in a separate table)
            $today_appointments = 0; // This would come from your database
            ?>
            <p>Bugün gönderilen bildirimler: <strong><?php echo $today_appointments; ?></strong></p>
        </div>
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
    
    <div class="card">
        <h2>🔧 Test Ayarları</h2>
        <p>Mevcut ayarlarınızı test edin:</p>
        <table class="form-table">
            <tr>
                <th scope="row">Mevcut WooCommerce URL:</th>
                <td>
                    <code><?php echo esc_url(get_option('morpheo_woocommerce_url', 'Ayarlanmamış')); ?></code>
                    <br>
                    <a href="<?php echo esc_url(get_option('morpheo_woocommerce_url', '#')); ?>" target="_blank" class="button button-small">🔗 URL'yi Test Et</a>
                </td>
            </tr>
            <tr>
                <th scope="row">Mevcut Konsültasyon Ücreti:</th>
                <td><strong><?php echo esc_html(get_option('morpheo_consultation_fee', '250')); ?> ₺</strong></td>
            </tr>
            <tr>
                <th scope="row">Admin E-postalar:</th>
                <td>
                    <?php 
                    $admin_emails = get_option('morpheo_admin_emails', '');
                    if ($admin_emails) {
                        echo '<code>' . esc_html($admin_emails) . '</code>';
                    } else {
                        echo '<em>Ek admin e-postası ayarlanmamış</em>';
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div>

<style>
.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
}

.card h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.form-table input[type="url"] {
    width: 100%;
    max-width: 600px;
}

.notice {
    margin: 5px 0 15px;
}
</style>
