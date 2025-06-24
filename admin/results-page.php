<div class="wrap">
    <h1>Calculator Results</h1>
    
    <?php
    global $wpdb;
    $table_name = $wpdb->prefix . 'morpheo_calculator_results';
    
    // Pagination
    $per_page = 20;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;
    
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_items / $per_page);
    
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ));
    ?>
    
    <div class="tablenav top">
        <div class="alignleft actions">
            <a href="<?php echo admin_url('admin.php?page=morpheo-calculator-results&export=csv'); ?>" class="button">Export CSV</a>
        </div>
        <?php if ($total_pages > 1): ?>
        <div class="tablenav-pages">
            <span class="displaying-num"><?php echo $total_items; ?> items</span>
            <?php
            echo paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'total' => $total_pages,
                'current' => $current_page
            ));
            ?>
        </div>
        <?php endif; ?>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Website Type</th>
                <th>Price Range</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $result): ?>
            <tr>
                <td><?php echo $result->id; ?></td>
                <td><?php echo esc_html($result->first_name . ' ' . $result->last_name); ?></td>
                <td><?php echo esc_html($result->email); ?></td>
                <td><?php echo esc_html(ucfirst($result->website_type)); ?></td>
                <td><?php echo number_format($result->min_price, 0, ',', '.') . ' - ' . number_format($result->max_price, 0, ',', '.') . ' ₺'; ?></td>
                <td><?php echo date('d.m.Y H:i', strtotime($result->created_at)); ?></td>
                <td>
                    <a href="#" class="button view-details" data-id="<?php echo $result->id; ?>">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Details Modal -->
    <div id="result-details-modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="result-details-content"></div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.view-details').on('click', function(e) {
        e.preventDefault();
        var resultId = $(this).data('id');
        
        $.post(ajaxurl, {
            action: 'get_morpheo_result_details',
            result_id: resultId,
            nonce: '<?php echo wp_create_nonce('morpheo_admin_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                $('#result-details-content').html(response.data.html);
                $('#result-details-modal').show();
            }
        });
    });
    
    $('.close').on('click', function() {
        $('#result-details-modal').hide();
    });
});
</script>

<style>
#result-details-modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    border-radius: 5px;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: black;
}
</style>
