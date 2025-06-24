<div class="wrap">
    <h1>Appointments</h1>
    
    <?php
    global $wpdb;
    $appointments_table = $wpdb->prefix . 'morpheo_calculator_appointments';
    $results_table = $wpdb->prefix . 'morpheo_calculator_results';
    
    // Handle status updates
    if (isset($_POST['update_status']) && isset($_POST['appointment_id'])) {
        $appointment_id = intval($_POST['appointment_id']);
        $new_status = sanitize_text_field($_POST['payment_status']);
        
        $wpdb->update(
            $appointments_table,
            array('payment_status' => $new_status),
            array('id' => $appointment_id),
            array('%s'),
            array('%d')
        );
        
        echo '<div class="notice notice-success"><p>Appointment status updated successfully!</p></div>';
    }
    
    $appointments = $wpdb->get_results("
        SELECT a.*, r.first_name, r.last_name, r.email, r.phone, r.website_type 
        FROM $appointments_table a 
        LEFT JOIN $results_table r ON a.calculator_id = r.id 
        ORDER BY a.created_at DESC
    ");
    ?>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Contact</th>
                <th>Appointment Date</th>
                <th>Time</th>
                <th>Payment Status</th>
                <th>Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($appointments as $appointment): ?>
            <tr>
                <td><?php echo $appointment->id; ?></td>
                <td><?php echo esc_html($appointment->first_name . ' ' . $appointment->last_name); ?></td>
                <td>
                    <?php echo esc_html($appointment->email); ?><br>
                    <?php echo esc_html($appointment->phone); ?>
                </td>
                <td><?php echo date('d.m.Y', strtotime($appointment->appointment_date)); ?></td>
                <td><?php echo date('H:i', strtotime($appointment->appointment_time)); ?></td>
                <td>
                    <span class="status-<?php echo $appointment->payment_status; ?>">
                        <?php echo ucfirst($appointment->payment_status); ?>
                    </span>
                </td>
                <td><?php echo number_format($appointment->payment_amount, 2); ?> ₺</td>
                <td>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="appointment_id" value="<?php echo $appointment->id; ?>">
                        <select name="payment_status">
                            <option value="pending" <?php selected($appointment->payment_status, 'pending'); ?>>Pending</option>
                            <option value="paid" <?php selected($appointment->payment_status, 'paid'); ?>>Paid</option>
                            <option value="cancelled" <?php selected($appointment->payment_status, 'cancelled'); ?>>Cancelled</option>
                            <option value="completed" <?php selected($appointment->payment_status, 'completed'); ?>>Completed</option>
                        </select>
                        <input type="submit" name="update_status" value="Update" class="button button-small">
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.status-pending { color: #f56500; }
.status-paid { color: #00a32a; }
.status-cancelled { color: #d63638; }
.status-completed { color: #00a32a; font-weight: bold; }
</style>
