<div class="wrap">
    <h1>Randevu Yönetimi</h1>
    
    <?php
    global $wpdb;
    $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
    $results_table = $wpdb->prefix . 'morpheo_calculator_results';
    
    // Handle status updates
    if (isset($_POST['update_status']) && isset($_POST['appointment_id'])) {
        $appointment_id = intval($_POST['appointment_id']);
        $new_status = sanitize_text_field($_POST['payment_status']);
        $notes = sanitize_textarea_field($_POST['notes']);
        
        $wpdb->update(
            $appointments_table,
            array(
                'payment_status' => $new_status,
                'notes' => $notes,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $appointment_id),
            array('%s', '%s', '%s'),
            array('%d')
        );
        
        echo '<div class="notice notice-success"><p>Randevu durumu başarıyla güncellendi!</p></div>';
    }
    
    // Handle appointment deletion
    if (isset($_POST['delete_appointment']) && isset($_POST['appointment_id'])) {
        $appointment_id = intval($_POST['appointment_id']);
        
        $wpdb->delete(
            $appointments_table,
            array('id' => $appointment_id),
            array('%d')
        );
        
        echo '<div class="notice notice-success"><p>Randevu başarıyla silindi!</p></div>';
    }
    
    // Get appointments with customer info
    $appointments = $wpdb->get_results("
        SELECT a.*, r.first_name, r.last_name, r.email, r.phone, r.website_type, r.min_price, r.max_price
        FROM $appointments_table a 
        LEFT JOIN $results_table r ON a.calculator_id = r.id 
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    
    // Filter options
    $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
    $date_filter = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';
    ?>
    
    <!-- Filters -->
    <div class="tablenav top">
        <form method="get" style="display: inline-block;">
            <input type="hidden" name="page" value="morpheo-calculator-appointments">
            <select name="status">
                <option value="">Tüm Durumlar</option>
                <option value="pending" <?php selected($status_filter, 'pending'); ?>>Beklemede</option>
                <option value="paid" <?php selected($status_filter, 'paid'); ?>>Ödendi</option>
                <option value="confirmed" <?php selected($status_filter, 'confirmed'); ?>>Onaylandı</option>
                <option value="completed" <?php selected($status_filter, 'completed'); ?>>Tamamlandı</option>
                <option value="cancelled" <?php selected($status_filter, 'cancelled'); ?>>İptal</option>
            </select>
            <input type="date" name="date" value="<?php echo $date_filter; ?>" placeholder="Tarih Filtresi">
            <input type="submit" class="button" value="Filtrele">
            <a href="?page=morpheo-calculator-appointments" class="button">Temizle</a>
        </form>
        
        <div style="float: right;">
            <button id="add-manual-appointment" class="button button-primary">Manuel Randevu Ekle</button>
        </div>
    </div>
    
    <!-- Appointments Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th>Müşteri</th>
                <th>İletişim</th>
                <th>Randevu</th>
                <th>Proje Tipi</th>
                <th>Tahmini Fiyat</th>
                <th>Durum</th>
                <th>Ücret</th>
                <th style="width: 200px;">İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $filtered_appointments = $appointments;
            
            // Apply filters
            if ($status_filter) {
                $filtered_appointments = array_filter($filtered_appointments, function($app) use ($status_filter) {
                    return $app->payment_status === $status_filter;
                });
            }
            
            if ($date_filter) {
                $filtered_appointments = array_filter($filtered_appointments, function($app) use ($date_filter) {
                    return $app->appointment_date === $date_filter;
                });
            }
            
            foreach ($filtered_appointments as $appointment): 
                $status_class = 'status-' . $appointment->payment_status;
                $is_past = strtotime($appointment->appointment_date . ' ' . $appointment->appointment_time) < time();
            ?>
            <tr class="<?php echo $is_past ? 'past-appointment' : ''; ?>">
                <td><?php echo $appointment->id; ?></td>
                <td>
                    <strong><?php echo esc_html($appointment->first_name . ' ' . $appointment->last_name); ?></strong>
                    <?php if ($appointment->notes): ?>
                        <br><small class="description"><?php echo esc_html($appointment->notes); ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="mailto:<?php echo esc_attr($appointment->email); ?>"><?php echo esc_html($appointment->email); ?></a><br>
                    <a href="tel:<?php echo esc_attr($appointment->phone); ?>"><?php echo esc_html($appointment->phone); ?></a>
                </td>
                <td>
                    <strong><?php echo date('d.m.Y', strtotime($appointment->appointment_date)); ?></strong><br>
                    <span class="time-slot"><?php echo date('H:i', strtotime($appointment->appointment_time)); ?></span>
                    <?php if ($is_past): ?>
                        <br><small class="description">Geçmiş</small>
                    <?php endif; ?>
                </td>
                <td><?php echo esc_html(ucfirst($appointment->website_type)); ?></td>
                <td>
                    <?php if ($appointment->min_price && $appointment->max_price): ?>
                        <?php echo number_format($appointment->min_price, 0, ',', '.') . ' - ' . number_format($appointment->max_price, 0, ',', '.') . ' ₺'; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td>
                    <span class="status-badge <?php echo $status_class; ?>">
                        <?php 
                        $status_labels = array(
                            'pending' => 'Beklemede',
                            'paid' => 'Ödendi',
                            'confirmed' => 'Onaylandı',
                            'completed' => 'Tamamlandı',
                            'cancelled' => 'İptal'
                        );
                        echo $status_labels[$appointment->payment_status] ?? ucfirst($appointment->payment_status);
                        ?>
                    </span>
                </td>
                <td><?php echo number_format($appointment->payment_amount, 0, ',', '.'); ?> ₺</td>
                <td>
                    <button class="button button-small edit-appointment" 
                            data-id="<?php echo $appointment->id; ?>"
                            data-status="<?php echo $appointment->payment_status; ?>"
                            data-notes="<?php echo esc_attr($appointment->notes); ?>">
                        Düzenle
                    </button>
                    <button class="button button-small button-link-delete delete-appointment" 
                            data-id="<?php echo $appointment->id; ?>">
                        Sil
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($filtered_appointments)): ?>
            <tr>
                <td colspan="9" style="text-align: center; padding: 40px;">
                    <em>Henüz randevu bulunmuyor.</em>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Statistics -->
    <div class="appointment-stats">
        <h3>Randevu İstatistikleri</h3>
        <?php
        $stats = array(
            'total' => count($appointments),
            'pending' => count(array_filter($appointments, function($a) { return $a->payment_status === 'pending'; })),
            'paid' => count(array_filter($appointments, function($a) { return $a->payment_status === 'paid'; })),
            'confirmed' => count(array_filter($appointments, function($a) { return $a->payment_status === 'confirmed'; })),
            'completed' => count(array_filter($appointments, function($a) { return $a->payment_status === 'completed'; })),
            'cancelled' => count(array_filter($appointments, function($a) { return $a->payment_status === 'cancelled'; }))
        );
        
        $total_revenue = array_sum(array_map(function($a) { 
            return in_array($a->payment_status, ['paid', 'completed']) ? $a->payment_amount : 0; 
        }, $appointments));
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Toplam Randevu</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-number"><?php echo $stats['pending']; ?></div>
                <div class="stat-label">Beklemede</div>
            </div>
            <div class="stat-card confirmed">
                <div class="stat-number"><?php echo $stats['confirmed']; ?></div>
                <div class="stat-label">Onaylandı</div>
            </div>
            <div class="stat-card completed">
                <div class="stat-number"><?php echo $stats['completed']; ?></div>
                <div class="stat-label">Tamamlandı</div>
            </div>
            <div class="stat-card revenue">
                <div class="stat-number"><?php echo number_format($total_revenue, 0, ',', '.'); ?> ₺</div>
                <div class="stat-label">Toplam Gelir</div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Appointment Modal -->
<div id="edit-appointment-modal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">Randevu Düzenle</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-appointment-form" method="post">
                <input type="hidden" name="appointment_id" id="edit-appointment-id">
                <input type="hidden" name="update_status" value="1">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Durum</th>
                        <td>
                            <select name="payment_status" id="edit-payment-status">
                                <option value="pending">Beklemede</option>
                                <option value="paid">Ödendi</option>
                                <option value="confirmed">Onaylandı</option>
                                <option value="completed">Tamamlandı</option>
                                <option value="cancelled">İptal</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Notlar</th>
                        <td>
                            <textarea name="notes" id="edit-notes" rows="4" cols="50" placeholder="Randevu ile ilgili notlar..."></textarea>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Güncelle">
                    <button type="button" class="button modal-close">İptal</button>
                </p>
            </form>
        </div>
    </div>
</div>

<style>
.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-paid { background: #d1ecf1; color: #0c5460; }
.status-confirmed { background: #d4edda; color: #155724; }
.status-completed { background: #d4edda; color: #155724; font-weight: bold; }
.status-cancelled { background: #f8d7da; color: #721c24; }

.past-appointment {
    opacity: 0.7;
    background-color: #f8f9fa;
}

.appointment-stats {
    margin-top: 30px;
    padding: 20px;
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.stat-card {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #007cba;
}

.stat-card.pending { border-left-color: #f59e0b; }
.stat-card.confirmed { border-left-color: #10b981; }
.stat-card.completed { border-left-color: #059669; }
.stat-card.revenue { border-left-color: #8b5cf6; }

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #1d4ed8;
    display: block;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
    margin-top: 5px;
}

.time-slot {
    background: #e3f2fd;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Edit appointment
    $('.edit-appointment').on('click', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var notes = $(this).data('notes');
        
        $('#edit-appointment-id').val(id);
        $('#edit-payment-status').val(status);
        $('#edit-notes').val(notes);
        $('#edit-appointment-modal').show();
    });
    
    // Delete appointment
    $('.delete-appointment').on('click', function() {
        if (confirm('Bu randevuyu silmek istediğinizden emin misiniz?')) {
            var id = $(this).data('id');
            var form = $('<form method="post">' +
                '<input type="hidden" name="appointment_id" value="' + id + '">' +
                '<input type="hidden" name="delete_appointment" value="1">' +
                '</form>');
            $('body').append(form);
            form.submit();
        }
    });
    
    // Close modal
    $('.modal-close').on('click', function() {
        $('#edit-appointment-modal').hide();
    });
    
    // Close modal on outside click
    $('#edit-appointment-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
});
</script>
