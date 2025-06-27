<?php
/**
 * Admin Page for Morpheo Calculator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['submit']) && check_admin_referer('morpheo_calculator_settings', 'morpheo_calculator_nonce')) {
    update_option('morpheo_woocommerce_url', esc_url_raw($_POST['morpheo_woocommerce_url']));
    update_option('morpheo_consultation_fee', sanitize_text_field($_POST['morpheo_consultation_fee']));
    update_option('morpheo_admin_emails', sanitize_text_field($_POST['morpheo_admin_emails']));
    
    // WhatsApp settings
    update_option('morpheo_whatsapp_enable', sanitize_text_field($_POST['morpheo_whatsapp_enable']));
    update_option('morpheo_whatsapp_api_token', sanitize_text_field($_POST['morpheo_whatsapp_api_token']));
    update_option('morpheo_whatsapp_from_number', sanitize_text_field($_POST['morpheo_whatsapp_from_number']));
    
    echo '<div class="notice notice-success"><p>Ayarlar kaydedildi!</p></div>';
}

// Get current settings
$woocommerce_url = get_option('morpheo_woocommerce_url', 'https://morpheodijital.com/satis/checkout-link/?urun=web-site-on-gorusme-randevusu');
$consultation_fee = get_option('morpheo_consultation_fee', '250');
$admin_emails = get_option('morpheo_admin_emails', '');
$whatsapp_enable = get_option('morpheo_whatsapp_enable', 'no');
$whatsapp_api_token = get_option('morpheo_whatsapp_api_token', '');
$whatsapp_from_number = get_option('morpheo_whatsapp_from_number', '');
?>

<div class="wrap">
    <h1>Morpheo Calculator Ayarları</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('morpheo_calculator_settings', 'morpheo_calculator_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="morpheo_woocommerce_url">WooCommerce Ödeme URL'si</label>
                </th>
                <td>
                    <input type="url" id="morpheo_woocommerce_url" name="morpheo_woocommerce_url" 
                           value="<?php echo esc_attr($woocommerce_url); ?>" class="regular-text" required />
                    <p class="description">
                        Randevu ödemelerinin yönlendirileceği WooCommerce checkout URL'si. 
                        Örnek: https://morpheodijital.com/satis/checkout-link/?urun=web-site-on-gorusme-randevusu
                        <br><strong>Not:</strong> Bu URL'ye otomatik olarak randevu parametreleri eklenecektir.
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="morpheo_consultation_fee">Konsültasyon Ücreti (₺)</label>
                </th>
                <td>
                    <input type="number" id="morpheo_consultation_fee" name="morpheo_consultation_fee" 
                           value="<?php echo esc_attr($consultation_fee); ?>" class="small-text" min="0" step="0.01" />
                    <p class="description">Randevu konsültasyon ücreti (Türk Lirası)</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="morpheo_admin_emails">Admin E-posta Adresleri</label>
                </th>
                <td>
                    <input type="text" id="morpheo_admin_emails" name="morpheo_admin_emails" 
                           value="<?php echo esc_attr($admin_emails); ?>" class="regular-text" />
                    <p class="description">Yeni randevu bildirimlerinin gönderileceği e-posta adresleri (virgülle ayırın)</p>
                </td>
            </tr>
        </table>
        
        <h2>WhatsApp Entegrasyonu</h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="morpheo_whatsapp_enable">WhatsApp'ı Etkinleştir</label>
                </th>
                <td>
                    <select id="morpheo_whatsapp_enable" name="morpheo_whatsapp_enable">
                        <option value="no" <?php selected($whatsapp_enable, 'no'); ?>>Hayır</option>
                        <option value="yes" <?php selected($whatsapp_enable, 'yes'); ?>>Evet</option>
                    </select>
                    <p class="description">WhatsApp mesaj gönderme özelliğini etkinleştir/devre dışı bırak</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="morpheo_whatsapp_api_token">WhatsApp API Token</label>
                </th>
                <td>
                    <input type="text" id="morpheo_whatsapp_api_token" name="morpheo_whatsapp_api_token" 
                           value="<?php echo esc_attr($whatsapp_api_token); ?>" class="regular-text" />
                    <p class="description">OtomatikBot.com API token'ınız</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="morpheo_whatsapp_from_number">Gönderen Telefon Numarası</label>
                </th>
                <td>
                    <input type="text" id="morpheo_whatsapp_from_number" name="morpheo_whatsapp_from_number" 
                           value="<?php echo esc_attr($whatsapp_from_number); ?>" class="regular-text" 
                           placeholder="905551234567" />
                    <p class="description">WhatsApp mesajlarının gönderileceği telefon numarası (90 ile başlayan 12 haneli format)</p>
                </td>
            </tr>
        </table>
        
        <?php submit_button('Ayarları Kaydet'); ?>
    </form>
    
    <hr>
    
    <h2>Kısa Kod Kullanımı</h2>
    <p>Hesap makinesini sayfalarınızda göstermek için aşağıdaki kısa kodu kullanın:</p>
    <code>[morpheo_web_calculator]</code>
    
    <h3>Kısa Kod Parametreleri</h3>
    <ul>
        <li><code>theme="dark"</code> - Tema (dark/light)</li>
        <li><code>show_appointment="true"</code> - Randevu bölümünü göster (true/false)</li>
    </ul>
    
    <p><strong>Örnek:</strong> <code>[morpheo_web_calculator theme="light" show_appointment="false"]</code></p>
</div>
