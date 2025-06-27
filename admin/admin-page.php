<?php
/**
 * Admin Page for Morpheo Calculator Settings
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Check user capabilities
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Save settings on form submission
if (isset($_POST['morpheo_calculator_settings_nonce']) && wp_verify_nonce($_POST['morpheo_calculator_settings_nonce'], 'morpheo_calculator_settings_action')) {
    update_option('morpheo_woocommerce_url', sanitize_url($_POST['morpheo_woocommerce_url']));
    update_option('morpheo_consultation_fee', sanitize_text_field($_POST['morpheo_consultation_fee']));
    update_option('morpheo_admin_emails', sanitize_text_field($_POST['morpheo_admin_emails']));
    update_option('morpheo_whatsapp_enable', isset($_POST['morpheo_whatsapp_enable']) ? 'yes' : 'no');
    update_option('morpheo_whatsapp_api_token', sanitize_text_field($_POST['morpheo_whatsapp_api_token']));
    update_option('morpheo_whatsapp_from_number', sanitize_text_field($_POST['morpheo_whatsapp_from_number']));
    echo '<div class="notice notice-success is-dismissible"><p>Ayarlar kaydedildi.</p></div>';
}

$woocommerce_url = get_option('morpheo_woocommerce_url', 'https://morpheodijital.com/satis/checkout-link/?urun=web-site-on-gorusme-randevusu');
$consultation_fee = get_option('morpheo_consultation_fee', '250');
$admin_emails = get_option('morpheo_admin_emails', '');
$whatsapp_enabled = get_option('morpheo_whatsapp_enable', 'no');
$whatsapp_api_token = get_option('morpheo_whatsapp_api_token', '');
$whatsapp_from_number = get_option('morpheo_whatsapp_from_number', '');

?>
<div class="wrap">
    <h1>Morpheo Dijital Fiyat Hesaplayıcı Ayarları</h1>

    <form method="post" action="">
        <?php wp_nonce_field('morpheo_calculator_settings_action', 'morpheo_calculator_settings_nonce'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">WooCommerce Ödeme URL'si</th>
                <td>
                    <input type="url" name="morpheo_woocommerce_url" value="<?php echo esc_url($woocommerce_url); ?>" class="regular-text" placeholder="https://satis.siteadresi.com/checkout-link/" />
                    <p class="description">Müşterilerin konsültasyon ücreti ödemesi için yönlendirileceği WooCommerce ürün sayfasının veya ödeme linkinin URL'si.</p>
                    <p class="description"><strong>Mevcut WooCommerce URL:</strong> <a href="<?php echo esc_url($woocommerce_url); ?>" target="_blank"><?php echo esc_url($woocommerce_url); ?></a></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Konsültasyon Ücreti (₺)</th>
                <td>
                    <input type="number" name="morpheo_consultation_fee" value="<?php echo esc_attr($consultation_fee); ?>" class="small-text" min="0" step="1" /> ₺
                    <p class="description">Randevu için alınacak konsültasyon ücreti.</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Yönetici E-posta Adresleri</th>
                <td>
                    <input type="text" name="morpheo_admin_emails" value="<?php echo esc_attr($admin_emails); ?>" class="regular-text" placeholder="admin@example.com, yonetici@example.com" />
                    <p class="description">Yeni randevu bildirimlerinin gönderileceği e-posta adresleri (virgülle ayırın).</p>
                </td>
            </tr>
        </table>

        <h2>WhatsApp Entegrasyon Ayarları</h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">WhatsApp Entegrasyonunu Etkinleştir</th>
                <td>
                    <label>
                        <input type="checkbox" name="morpheo_whatsapp_enable" value="yes" <?php checked('yes', $whatsapp_enabled); ?> />
                        Evet, WhatsApp bildirimlerini etkinleştir
                    </label>
                    <p class="description">Müşterilere ve yöneticilere WhatsApp üzerinden bildirim göndermek için etkinleştirin.</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">WhatsApp API Token</th>
                <td>
                    <input type="text" name="morpheo_whatsapp_api_token" value="<?php echo esc_attr($whatsapp_api_token); ?>" class="regular-text" placeholder="API token'ınızı buraya girin" />
                    <p class="description">OtomatikBot.com veya benzeri bir WhatsApp API sağlayıcısından aldığınız API token'ı.</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">WhatsApp Gönderen Numara</th>
                <td>
                    <input type="text" name="morpheo_whatsapp_from_number" value="<?php echo esc_attr($whatsapp_from_number); ?>" class="regular-text" placeholder="905XXXXXXXXX" />
                    <p class="description">Mesajların gönderileceği WhatsApp numarası (ülke koduyla birlikte, örn: 905XXXXXXXXX).</p>
                </td>
            </tr>
        </table>

        <?php submit_button('Ayarları Kaydet'); ?>
    </form>

    <hr>

    <h2>🧪 WhatsApp Test</h2>
    <p>WhatsApp entegrasyonunuzun doğru çalışıp çalışmadığını test edin.</p>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Test Numarası</th>
            <td>
                <input type="text" id="morpheo_whatsapp_test_number" class="regular-text" placeholder="905XXXXXXXXX" />
                <p class="description">Test mesajı göndermek istediğiniz WhatsApp numarası.</p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"></th>
            <td>
                <button type="button" id="morpheo_send_whatsapp_test" class="button button-secondary">🚀 WhatsApp Test Mesajı Gönder</button>
                <p id="whatsapp_test_status" style="margin-top: 10px;"></p>
            </td>
        </tr>
    </table>

    <h3>WhatsApp Entegrasyon Durumu</h3>
    <div class="whatsapp-status-box">
        <p><strong>Entegrasyon:</strong> 
            <span class="status-indicator <?php echo ($whatsapp_enabled === 'yes') ? 'status-active' : 'status-inactive'; ?>">
                <?php echo ($whatsapp_enabled === 'yes') ? 'Aktif' : 'Devre Dışı'; ?>
            </span>
        </p>
        <p><strong>API Token:</strong> 
            <span class="status-indicator <?php echo (!empty($whatsapp_api_token)) ? 'status-active' : 'status-inactive'; ?>">
                <?php echo (!empty($whatsapp_api_token)) ? 'Girildi' : 'Eksik'; ?>
            </span>
        </p>
        <p><strong>Gönderen Numara:</strong> 
            <span class="status-indicator <?php echo (!empty($whatsapp_from_number) && strlen(preg_replace('/[^0-9]/', '', $whatsapp_from_number)) === 12 && substr(preg_replace('/[^0-9]/', '', $whatsapp_from_number), 0, 2) === '90') ? 'status-active' : 'status-inactive'; ?>">
                <?php echo (!empty($whatsapp_from_number) && strlen(preg_replace('/[^0-9]/', '', $whatsapp_from_number)) === 12 && substr(preg_replace('/[^0-9]/', '', $whatsapp_from_number), 0, 2) === '90') ? 'Geçerli' : 'Eksik/Hatalı'; ?>
            </span>
        </p>
        <?php if ($whatsapp_enabled === 'yes' && (empty($whatsapp_api_token) || empty($whatsapp_from_number) || strlen(preg_replace('/[^0-9]/', '', $whatsapp_from_number)) !== 12 || substr(preg_replace('/[^0-9]/', '', $whatsapp_from_number), 0, 2) !== '90')): ?>
            <p class="description error-message">WhatsApp entegrasyonu etkin ancak ayarlar eksik veya hatalı. Lütfen yukarıdaki alanları kontrol edin.</p>
        <?php endif; ?>
    </div>

    <style>
        .whatsapp-status-box {
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 5px;
            background-color: #f9f9f9;
            margin-top: 20px;
        }
        .whatsapp-status-box p {
            margin: 5px 0;
        }
        .status-indicator {
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            color: white;
        }
        .status-active {
            background-color: #28a745; /* Green */
        }
        .status-inactive {
            background-color: #dc3545; /* Red */
        }
        .error-message {
            color: #dc3545;
            font-weight: bold;
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            $('#morpheo_send_whatsapp_test').on('click', function() {
                var testNumber = $('#morpheo_whatsapp_test_number').val();
                var statusDiv = $('#whatsapp_test_status');
                statusDiv.removeClass('status-active status-inactive error-message').html('Mesaj gönderiliyor...');

                if (!testNumber) {
                    statusDiv.addClass('error-message').html('Lütfen bir test numarası girin.');
                    return;
                }

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'morpheo_send_whatsapp_test_message',
                        nonce: '<?php echo wp_create_nonce('morpheo_admin_nonce'); ?>',
                        test_number: testNumber
                    },
                    success: function(response) {
                        if (response.success) {
                            statusDiv.addClass('status-active').html('✅ Test mesajı başarıyla gönderildi!');
                        } else {
                            statusDiv.addClass('error-message').html('❌ Test mesajı gönderilemedi: ' + (response.data.message || 'Bilinmeyen hata. Logları kontrol edin.'));
                        }
                    },
                    error: function(xhr, status, error) {
                        statusDiv.addClass('error-message').html('❌ AJAX hatası: ' + error);
                        console.error('WhatsApp Test AJAX Error:', xhr.responseText);
                    }
                });
            });
        });
    </script>
</div>

<?php
// Add AJAX handler for WhatsApp test message
add_action('wp_ajax_morpheo_send_whatsapp_test_message', 'morpheo_send_whatsapp_test_message_callback');
function morpheo_send_whatsapp_test_message_callback() {
    check_ajax_referer('morpheo_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
    }

    $test_number = sanitize_text_field($_POST['test_number']);
    $message_text = "Morpheo Dijital Fiyat Hesaplayıcı WhatsApp test mesajı. Entegrasyonunuz başarılı!";

    if (empty($test_number)) {
        wp_send_json_error(array('message' => 'Test numarası boş olamaz.'));
    }

    $sent = MorpheoWhatsAppSender::sendMessage($test_number, $message_text);

    if ($sent) {
        wp_send_json_success(array('message' => 'Test mesajı başarıyla gönderildi.'));
    } else {
        wp_send_json_error(array('message' => 'Test mesajı gönderilemedi. Lütfen ayarları ve logları kontrol edin.'));
    }
}
?>
