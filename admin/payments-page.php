<div class="wrap">
    <h1>Ödeme Durumu Takibi</h1>
    
    <?php
    // Manual payment check
    if (isset($_POST['check_payments'])) {
        $updated_count = MorpheoPaymentAPI::checkAllPendingPayments();
        echo '<div class="notice notice-success"><p>' . $updated_count . ' randevu ödeme durumu güncellendi!</p></div>';
    }
    
    // Get payment statistics
    $stats = MorpheoPaymentAPI::getPaymentStats();
    ?>
    
    <div class="card">
        <h2>📊 Ödeme İstatistikleri</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_appointments']; ?></div>
                <div class="stat-label">Bu Ay Toplam Randevu</div>
            </div>
            <div class="stat-card confirmed">
                <div class="stat-number"><?php echo $stats['paid_appointments']; ?></div>
                <div class="stat-label">Ödenen Randevular</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-number"><?php echo $stats['pending_appointments']; ?></div>
                <div class="stat-label">Bekleyen Ödemeler</div>
            </div>
            <div class="stat-card cancelled">
                <div class="stat-number"><?php echo $stats['cancelled_appointments']; ?></div>
                <div class="stat-label">İptal Edilen</div>
            </div>
            <div class="stat-card revenue">
                <div class="stat-number"><?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?> ₺</div>
                <div class="stat-label">Bu Ay Gelir</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['conversion_rate']; ?>%</div>
                <div class="stat-label">Dönüşüm Oranı</div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h2>🔄 Manuel Ödeme Kontrolü</h2>
        <p>Bekleyen ödemeleri manuel olarak kontrol etmek için aşağıdaki butona tıklayın.</p>
        <form method="post">
            <input type="hidden" name="check_payments" value="1">
            <button type="submit" class="button button-primary">🔍 Ödemeleri Şimdi Kontrol Et</button>
        </form>
        <p class="description">
            <strong>Not:</strong> Sistem otomatik olarak her 10 dakikada bir ödeme durumlarını kontrol eder.
        </p>
    </div>
    
    <div class="card">
        <h2>⚙️ API Ayarları</h2>
        <table class="form-table">
            <tr>
                <th scope="row">API URL</th>
                <td>
                    <code>https://morpheodijital.com/satis/wp-content/themes/snn-brx-child-theme/siparis-sorgula.php</code>
                    <p class="description">Ödeme sorgulama API adresi</p>
                </td>
            </tr>
            <tr>
                <th scope="row">API Key</th>
                <td>
                    <code>t3RcN@f9h$5!ZxLuQ1W#pK7eMv%BdA82</code>
                    <p class="description">API güvenlik anahtarı</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Kontrol Sıklığı</th>
                <td>
                    <strong>Her 10 dakikada bir</strong>
                    <p class="description">Otomatik ödeme kontrolü sıklığı</p>
                </td>
            </tr>
            <tr>
                <th scope="row">İptal Süresi</th>
                <td>
                    <strong>24 saat</strong>
                    <p class="description">Ödeme yapılmayan randevular 24 saat sonra iptal edilir</p>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="card">
        <h2>📋 Son Ödeme Kontrolleri</h2>
        <?php
        global $wpdb;
        $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
        $results_table = $wpdb->prefix . 'morpheo_calculator_results';
        
        // Get recent payment updates
        $recent_updates = $wpdb->get_results("
            SELECT a.*, r.first_name, r.last_name, r.email 
            FROM $appointments_table a 
            LEFT JOIN $results_table r ON a.calculator_id = r.id 
            WHERE a.payment_status = 'paid' 
            AND a.updated_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY a.updated_at DESC 
            LIMIT 10
        ");
        ?>
        
        <?php if (!empty($recent_updates)): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Müşteri</th>
                    <th>E-posta</th>
                    <th>Randevu</th>
                    <th>Ödeme Tarihi</th>
                    <th>Tutar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_updates as $update): ?>
                <tr>
                    <td><?php echo esc_html($update->first_name . ' ' . $update->last_name); ?></td>
                    <td><?php echo esc_html($update->email); ?></td>
                    <td>
                        <?php echo date('d.m.Y', strtotime($update->appointment_date)); ?><br>
                        <small><?php echo date('H:i', strtotime($update->appointment_time)); ?></small>
                    </td>
                    <td><?php echo date('d.m.Y H:i', strtotime($update->updated_at)); ?></td>
                    <td><?php echo number_format($update->payment_amount, 0, ',', '.'); ?> ₺</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>Son 24 saatte ödeme onayı alınan randevu bulunmuyor.</p>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <h2>🚨 Bekleyen Ödemeler</h2>
        <?php
        // Get pending payments
        $pending_payments = $wpdb->get_results("
            SELECT a.*, r.first_name, r.last_name, r.email 
            FROM $appointments_table a 
            LEFT JOIN $results_table r ON a.calculator_id = r.id 
            WHERE a.payment_status = 'pending'
            ORDER BY a.created_at DESC 
            LIMIT 20
        ");
        ?>
        
        <?php if (!empty($pending_payments)): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Müşteri</th>
                    <th>E-posta</th>
                    <th>Randevu</th>
                    <th>Oluşturulma</th>
                    <th>Kalan Süre</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_payments as $payment): ?>
                <?php
                $created_time = strtotime($payment->created_at);
                $deadline = $created_time + (24 * 60 * 60); // 24 hours
                $now = time();
                $hours_left = max(0, floor(($deadline - $now) / 3600));
                $is_expired = $hours_left <= 0;
                ?>
                <tr class="<?php echo $is_expired ? 'expired-payment' : ''; ?>">
                    <td><?php echo esc_html($payment->first_name . ' ' . $payment->last_name); ?></td>
                    <td>
                        <a href="mailto:<?php echo esc_attr($payment->email); ?>"><?php echo esc_html($payment->email); ?></a>
                    </td>
                    <td>
                        <?php echo date('d.m.Y', strtotime($payment->appointment_date)); ?><br>
                        <small><?php echo date('H:i', strtotime($payment->appointment_time)); ?></small>
                    </td>
                    <td><?php echo date('d.m.Y H:i', strtotime($payment->created_at)); ?></td>
                    <td>
                        <?php if ($is_expired): ?>
                            <span style="color: #dc2626; font-weight: bold;">❌ Süresi Doldu</span>
                        <?php else: ?>
                            <span style="color: #f59e0b; font-weight: bold;">⏰ <?php echo $hours_left; ?> saat</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="https://morpheodijital.com/satis/wp-content/themes/snn-brx-child-theme/siparis-sorgula.php?email=<?php echo urlencode($payment->email); ?>&key=t3RcN%40f9h%245%21ZxLuQ1W%23pK7eMv%25BdA82" 
                           target="_blank" class="button button-small">🔍 API Kontrol</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>Bekleyen ödeme bulunmuyor.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.expired-payment {
    background-color: #fef2f2 !important;
    opacity: 0.7;
}

.stat-card.pending { border-left-color: #f59e0b; }
.stat-card.confirmed { border-left-color: #10b981; }
.stat-card.cancelled { border-left-color: #ef4444; }
.stat-card.revenue { border-left-color: #8b5cf6; }
</style>
