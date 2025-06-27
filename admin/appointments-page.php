<div class="wrap">
    <h1>Randevu Yönetimi</h1>
    
    <?php
    global $wpdb;
    $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
    $results_table = $wpdb->prefix . 'morpheo_calculator_results';
    
    // Handle status updates
    if (isset($_POST['update_status'])) {
        $appointment_id = intval($_POST['appointment_id']);
        $new_status = sanitize_text_field($_POST['new_status']);
        
        $updated = $wpdb->update(
            $appointments_table,
            array('payment_status' => $new_status),
            array('id' => $appointment_id)
        );
        
        if ($updated) {
            echo '<div class="notice notice-success"><p>Randevu durumu güncellendi!</p></div>';
        }
    }
    
    // Get appointments with pagination
    $per_page = 20;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;
    
    $total_appointments = $wpdb->get_var("
        SELECT COUNT(*) 
        FROM $appointments_table a 
        LEFT JOIN $results_table r ON a.calculator_id = r.id
    ");
    
    $appointments = $wpdb->get_results("
        SELECT a.*, r.first_name, r.last_name, r.email, r.phone, r.website_type_tr, r.price_range
        FROM $appointments_table a 
        LEFT JOIN $results_table r ON a.calculator_id = r.id 
        ORDER BY a.created_at DESC 
        LIMIT $per_page OFFSET $offset
    ");
    
    $total_pages = ceil($total_appointments / $per_page);
    ?>
    
    <div class="tablenav top">
        <div class="alignleft actions">
            <span class="displaying-num"><?php echo $total_appointments; ?> öğe</span>
        </div>
        <?php if ($total_pages > 1): ?>
        <div class="tablenav-pages">
            <span class="pagination-links">
                <?php
                $page_links = paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $total_pages,
                    'current' => $current_page
                ));
                echo $page_links;
                ?>
            </span>
        </div>
        <?php endif; ?>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Müşteri</th>
                <th>İletişim</th>
                <th>Proje</th>
                <th>Randevu</th>
                <th>Durum</th>
                <th>Tutar</th>
                <th>Oluşturulma</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($appointments)): ?>
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">
                    Henüz randevu bulunmuyor.
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($appointments as $appointment): ?>
            <tr>
                <td><?php echo $appointment->id; ?></td>
                <td>
                    <strong><?php echo esc_html($appointment->first_name . ' ' . $appointment->last_name); ?></strong>
                </td>
                <td>
                    <a href="mailto:<?php echo esc_attr($appointment->email); ?>"><?php echo esc_html($appointment->email); ?></a><br>
                    <small><a href="tel:<?php echo esc_attr($appointment->phone); ?>"><?php echo esc_html($appointment->phone); ?></a></small>
                </td>
                <td>
                    <strong><?php echo esc_html($appointment->website_type_tr); ?></strong><br>
                    <small><?php echo esc_html($appointment->price_range); ?></small>
                </td>
                <td>
                    <?php echo date('d.m.Y', strtotime($appointment->appointment_date)); ?><br>
                    <small><?php echo date('H:i', strtotime($appointment->appointment_time)); ?></small>
                </td>
                <td>
                    <span class="appointment-status status-<?php echo $appointment->payment_status; ?>">
                        <?php
                        switch($appointment->payment_status) {
                            case 'pending': echo '⏳ Bekliyor'; break;
                            case 'paid': echo '✅ Ödendi'; break;
                            case 'cancelled': echo '❌ İptal'; break;
                            default: echo $appointment->payment_status;
                        }
                        ?>
                    </span>
                </td>
                <td><?php echo number_format($appointment->payment_amount, 0, ',', '.'); ?> ₺</td>
                <td>
                    <?php echo date('d.m.Y H:i', strtotime($appointment->created_at)); ?>
                </td>
                <td>
                    <div class="appointment-actions">
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="appointment_id" value="<?php echo $appointment->id; ?>">
                            <select name="new_status" onchange="this.form.submit()">
                                <option value="">Durum Değiştir</option>
                                <option value="pending" <?php selected($appointment->payment_status, 'pending'); ?>>Bekliyor</option>
                                <option value="paid" <?php selected($appointment->payment_status, 'paid'); ?>>Ödendi</option>
                                <option value="cancelled" <?php selected($appointment->payment_status, 'cancelled'); ?>>İptal</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                        
                        <?php if (!empty($appointment->payment_url)): ?>
                        <a href="<?php echo esc_url($appointment->payment_url); ?>" target="_blank" class="button button-small">
                            💳 Ödeme Linki
                        </a>
                        <?php endif; ?>
                        
                        <a href="mailto:<?php echo esc_attr($appointment->email); ?>?subject=Randevunuz Hakkında" class="button button-small">
                            ✉️ E-posta Gönder
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if ($total_pages > 1): ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="pagination-links">
                <?php echo $page
