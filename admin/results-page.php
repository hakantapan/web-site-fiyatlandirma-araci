<div class="wrap">
    <h1>Calculator Results</h1>
    
    <?php
    global $wpdb;
    $table_name = $wpdb->prefix . 'morpheo_calculator_results';
    
    // Handle CSV export
    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        $this->export_csv();
        exit;
    }
    
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
            <a href="<?php echo admin_url('admin.php?page=morpheo-calculator-results&export=csv'); ?>" class="button">📊 Export CSV</a>
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
                <th style="width: 60px;">ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Website Type</th>
                <th>Price Range</th>
                <th>Date</th>
                <th style="width: 120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $result): ?>
            <tr>
                <td><?php echo $result->id; ?></td>
                <td>
                    <strong><?php echo esc_html($result->first_name . ' ' . $result->last_name); ?></strong>
                    <?php if ($result->company): ?>
                        <br><small class="description"><?php echo esc_html($result->company); ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="mailto:<?php echo esc_attr($result->email); ?>"><?php echo esc_html($result->email); ?></a>
                    <?php if ($result->phone): ?>
                        <br><a href="tel:<?php echo esc_attr($result->phone); ?>"><?php echo esc_html($result->phone); ?></a>
                    <?php endif; ?>
                </td>
                <td><?php echo esc_html(ucfirst($result->website_type)); ?></td>
                <td>
                    <span class="price-range">
                        <?php echo number_format($result->min_price, 0, ',', '.') . ' - ' . number_format($result->max_price, 0, ',', '.') . ' ₺'; ?>
                    </span>
                </td>
                <td><?php echo date('d.m.Y H:i', strtotime($result->created_at)); ?></td>
                <td>
                    <button class="button button-small view-details" data-id="<?php echo $result->id; ?>">
                        👁️ View Details
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($results)): ?>
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px;">
                    <em>No results found.</em>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Pagination Bottom -->
    <?php if ($total_pages > 1): ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
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
    </div>
    <?php endif; ?>
</div>

<!-- Details Modal -->
<div id="result-details-modal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">📋 Calculator Result Details</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="result-details-content">
                <div class="loading-spinner">
                    <p>Loading details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.price-range {
    color: #059669;
    font-weight: 600;
}

.loading-spinner {
    text-align: center;
    padding: 40px;
    color: #666;
}

.loading-spinner::before {
    content: "⏳";
    font-size: 24px;
    display: block;
    margin-bottom: 10px;
}

.view-details {
    background-color: #0073aa !important;
    color: white !important;
    border-color: #0073aa !important;
}

.view-details:hover {
    background-color: #005a87 !important;
    border-color: #005a87 !important;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(2px);
}

.modal-dialog {
    background: #fff;
    border-radius: 8px;
    max-width: 800px;
    width: 90%;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fafc;
}

.modal-title {
    margin: 0;
    font-size: 18px;
    color: #1d4ed8;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: #f1f5f9;
    color: #1d4ed8;
}

.modal-body {
    padding: 0;
    max-height: calc(90vh - 80px);
    overflow-y: auto;
}

#result-details-content {
    padding: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .modal-dialog {
        width: 95%;
        margin: 20px;
        max-height: 95vh;
    }
    
    .modal-header {
        padding: 15px;
    }
    
    #result-details-content {
        padding: 15px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // View details functionality
    $('.view-details').on('click', function(e) {
        e.preventDefault();
        var resultId = $(this).data('id');
        
        // Show modal with loading state
        $('#result-details-content').html('<div class="loading-spinner"><p>Loading details...</p></div>');
        $('#result-details-modal').show();
        
        // Make AJAX request
        $.post(ajaxurl, {
            action: 'get_morpheo_result_details',
            result_id: resultId,
            nonce: '<?php echo wp_create_nonce('morpheo_admin_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                $('#result-details-content').html(response.data.html);
            } else {
                $('#result-details-content').html(
                    '<div style="text-align: center; padding: 40px; color: #dc2626;">' +
                    '<h3>❌ Error</h3>' +
                    '<p>' + (response.data.message || 'Failed to load details') + '</p>' +
                    '</div>'
                );
            }
        }).fail(function() {
            $('#result-details-content').html(
                '<div style="text-align: center; padding: 40px; color: #dc2626;">' +
                '<h3>❌ Connection Error</h3>' +
                '<p>Failed to connect to server. Please try again.</p>' +
                '</div>'
            );
        });
    });
    
    // Close modal functionality
    $('.modal-close').on('click', function() {
        $('#result-details-modal').hide();
    });
    
    // Close modal on outside click
    $('#result-details-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
    
    // Close modal on Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('#result-details-modal').hide();
        }
    });
});
</script>

<?php
// CSV Export function
function export_csv() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'morpheo_calculator_results';
    
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
    
    $filename = 'morpheo-calculator-results-' . date('Y-m-d') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // CSV Headers
    fputcsv($output, array(
        'ID',
        'First Name',
        'Last Name',
        'Email',
        'Phone',
        'Company',
        'City',
        'Website Type',
        'Page Count',
        'Design Complexity',
        'Features',
        'Min Price',
        'Max Price',
        'Created At'
    ));
    
    // CSV Data
    foreach ($results as $result) {
        fputcsv($output, array(
            $result->id,
            $result->first_name,
            $result->last_name,
            $result->email,
            $result->phone,
            $result->company,
            $result->city,
            $result->website_type,
            $result->page_count,
            $result->design_complexity,
            $result->features,
            $result->min_price,
            $result->max_price,
            $result->created_at
        ));
    }
    
    fclose($output);
}

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    export_csv();
    exit;
}
?>
