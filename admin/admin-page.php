<div class="wrap">
    <h1>Morpheo Calculator Ayarları</h1>
    
    <?php
    // Save settings
    if (isset($_POST['submit'])) {
        update_option('morpheo_woocommerce_url', sanitize_url($_POST['woocommerce_url']));
        update_option('morpheo_whatsapp_enabled', isset($_POST['whatsapp_enabled']) ? 1 : 0);
        update_option('morpheo_whatsapp_token', sanitize_text_field($_POST['whatsapp_token']));
        update_option('morpheo_whatsapp_phone', sanitize_text_field($_POST['whatsapp_phone']));
        echo '<div class="notice notice-success"><p>Ayarlar kaydedildi!</p></div>';
    }
    
    // Get current settings
    $woocommerce_url = get_option('morpheo_woocommerce_url', 'https://morpheodijital.com/satis/');
    $whatsapp_enabled = get_option('morpheo_whatsapp_enabled', false);
    $whatsapp_token = get_option('morpheo_whatsapp_token', '');
    $whatsapp_phone = get_option('morpheo_whatsapp_phone', '');
    ?>
    
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="woocommerce_url">WooCommerce URL</label>
                </th>
                <td>
                    <input type="url" id="woocommerce_url" name="woocommerce_url" value="<?php echo esc_attr($woocommerce_url); ?>" class="regular-text" required />
                    <p class="description">
                        Ödeme sayfasının bulunduğu WooCommerce URL'si. Tüm ödeme linkleri bu URL'yi kullanacaktır.
                        <br><strong>Örnek:</strong> https://morpheodijital.com/satis/
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">WhatsApp Entegrasyonu</th>
                <td>
                    <fieldset>
                        <label for="whatsapp_enabled">
                            <input type="checkbox" id="whatsapp_enabled" name="whatsapp_enabled" value="1" <?php checked($whatsapp_enabled, 1); ?> />
                            WhatsApp mesajlarını etkinleştir
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="whatsapp_token">WhatsApp API Token</label>
                </th>
                <td>
                    <input type="text" id="whatsapp_token" name="whatsapp_token" value="<?php echo esc_attr($whatsapp_token); ?>" class="regular-text" />
                    <p class="description">WhatsApp Business API token'ınızı girin.</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="whatsapp_phone">WhatsApp Telefon ID</label>
                </th>
                <td>
                    <input type="text" id="whatsapp_phone" name="whatsapp_phone" value="<?php echo esc_attr($whatsapp_phone); ?>" class="regular-text" />
                    <p class="description">WhatsApp Business telefon ID'nizi girin.</p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
    
    <div class="card">
        <h2>📊 Plugin Bilgileri</h2>
        <table class="form-table">
            <tr>
                <th scope="row">Plugin Versiyonu</th>
                <td><strong><?php echo MORPHEO_CALCULATOR_VERSION; ?></strong></td>
            </tr>
            <tr>
                <th scope="row">Veritabanı Durumu</th>
                <td>
                    <?php
                    global $wpdb;
                    $results_table = $wpdb->prefix . 'morpheo_calculator_results';
                    $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
                    
                    $results_count = $wpdb->get_var("SELECT COUNT(*) FROM $results_table");
                    $appointments_count = $wpdb->get_var("SELECT COUNT(*) FROM $appointments_table");
                    ?>
                    <strong><?php echo $results_count; ?></strong> hesaplama sonucu, 
                    <strong><?php echo $appointments_count; ?></strong> randevu kaydı
                </td>
            </tr>
            <tr>
                <th scope="row">Cron Jobs</th>
                <td>
                    <?php
                    $payment_reminder_next = wp_next_scheduled('morpheo_payment_reminder_cron');
                    $payment_check_next = wp_next_scheduled('morpheo_payment_check_cron');
                    ?>
                    <strong>Ödeme Hatırlatma:</strong> <?php echo $payment_reminder_next ? date('d.m.Y H:i', $payment_reminder_next) : 'Planlanmamış'; ?><br>
                    <strong>Ödeme Kontrolü:</strong> <?php echo $payment_check_next ? date('d.m.Y H:i', $payment_check_next) : 'Planlanmamış'; ?>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="card">
        <h2>🔧 Test Araçları</h2>
        <p>Plugin fonksiyonlarını test etmek için aşağıdaki araçları kullanabilirsiniz:</p>
        
        <h3>Ödeme URL Testi</h3>
        <p>Örnek ödeme URL'si:</p>
        <code style="display: block; background: #f1f1f1; padding: 10px; margin: 10px 0; word-break: break-all;">
            <?php 
            $test_params = array(
                'randevu_tarihi' => '2024-01-15',
                'randevu_saati' => '14:30',
                'musteri_adi' => 'Test Kullanıcı',
                'musteri_email' => 'test@example.com',
                'musteri_telefon' => '05551234567',
                'proje_tipi' => 'Kurumsal Web Sitesi',
                'tahmini_fiyat' => '15.000₺ - 25.000₺',
                'calculator_id' => '123',
                'appointment_id' => '456'
            );
            echo esc_url($woocommerce_url . '?' . http_build_query($test_params));
            ?>
        </code>
        
        <h3>Shortcode Kullanımı</h3>
        <p>Hesaplama aracını sayfalarınızda göstermek için:</p>
        <code>[morpheo_calculator]</code>
    </div>
</div>
