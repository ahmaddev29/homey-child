<?php
global $current_user, $reservation_page_link, $wallet_page_link, $earnings_page_link, $payout_request_link, $homey_prefix, $homey_local;
$current_user = wp_get_current_user();
$userID = $current_user->ID;
$local = homey_get_localization();
$allowded_html = array();

$args = array(
    'post_type' => 'homey_reservation',
    'meta_query' => array(
        array(
            'key' => 'reservation_status',
            'value' => 'under_review',
            'compare' => '='
        ),
        array(
            'key' => 'listing_owner',
            'value' => $userID,
            'compare' => '='
        )
    )
);

$query = new WP_Query($args);

$pending_earnings = 0;

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        $pending_earnings_host = get_post_meta(get_the_ID(), 'total_earning_pending', true);
        $pending_earnings += (float) $pending_earnings_host;
    }
} else {
    $pending_earnings = 0;
}

wp_reset_postdata();

$dashboard_profile = homey_get_template_link_dash('template/dashboard-profile.php');
$payment_method_setup = add_query_arg('dpage', 'payment-method', $dashboard_profile);

if (isset($_GET['host']) && $_GET['host'] != '') {
    $host_id = $_GET['host'];
    $userID = $host_id;
} else {
    $host_id = null;
}

$host_earnings = homey_get_earnings($limit = 5, $host_id);
$payouts = homey_get_host_payouts($limit = 5, $host_id);
$available_balance = homey_get_host_available_earnings($userID);
$total_earnings = get_user_meta($userID, 'host_total_earning', $total_earning);

$listing_no = '9';
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$new_args = array(
    'post_type' => 'homey_reservation',
    'paged' => $paged,
    'posts_per_page' => $listing_no,
    'meta_query' => array(
        array(
            'key' => 'host_earning_status',
            'value' => 'transfered',
            'compare' => '='
        ),
        array(
            'key' => 'listing_owner',
            'value' => $userID,
            'compare' => '='
        )
    )
);
?>

<div class="wallet-box-wrap">
    <div class="row">
        <div class="col-sm-4 col-xs-12">
            <div class="wallet-box">
                <div class="block-big-text">
                    <?php
                    if ($total_earnings[0] != 0) {
                        echo homey_formatted_price($total_earnings[0]);
                    } else {
                        echo '$0';
                    }
                    ?>
                </div>
                <h3><?php esc_html_e('Total Earnings', 'homey'); ?> <span
                        class="wallet-label"><?php esc_html_e('Host Fee:', 'homey'); ?>
                        <?php homey_host_fee_percent(); ?>%</span></h3>
                <div class="wallet-box-info"><?php esc_html_e('Excluding the service fee and the host fee', 'homey'); ?>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-xs-12">
            <div class="wallet-box">
                <div class="block-big-text">
                    <?php
                    if ($pending_earnings != 0) {
                        echo homey_formatted_price($pending_earnings);
                    } else {
                        echo '$0';
                    }
                    ?>
                </div>
                <h3><?php esc_html_e('Pending Earnings', 'homey'); ?> <span
                        class="wallet-label"><?php esc_html_e('Host Fee:', 'homey'); ?>
                        <?php homey_host_fee_percent(); ?>%</span></h3>
                <div class="wallet-box-info"><?php esc_html_e('Excluding the service fee and the host fee', 'homey'); ?>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-xs-12">
            <div class="wallet-box">
                <div class="block-big-text"><?php echo homey_reservation_count($userID); ?></div>
                <h3><?php esc_html_e('Total reservations', 'homey'); ?></h3>
                <div class="wallet-box-info">
                    <?php esc_html_e('Represents the total number of reservations you have received', 'homey'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="wallet-box-wrap">
    <div class="row">
        <div class="col-sm-12 col-xs-12">
            <div class="block">
                <div class="block-title">
                    <div class="block-left">
                        <h2 class="title"><?php esc_html_e('Earning Calculator', 'homey'); ?></h2>
                    </div>
                </div>
                <div class="block-body">
                    <!-- Earning Calculator Form -->
                    <form id="earning-calculator-form">
                        <div class="form-group">
                            <label for="earning-type"><?php esc_html_e('Select Earning Type', 'homey'); ?></label>
                            <select class="form-control" id="earning-type" name="earning_type">
                                <option value="amenity"><?php esc_html_e('Amenity', 'homey'); ?></option>
                                <option value="sleeping_accommodation"><?php esc_html_e('Sleeping Accommodation', 'homey'); ?></option>
                                <option value="guided_service"><?php esc_html_e('Guided Service', 'homey'); ?></option>
                                <option value="additional_vehicles"><?php esc_html_e('Additional Vehicles', 'homey'); ?></option>
                                <option value="occupancy_tax"><?php esc_html_e('Occupancy Tax', 'homey'); ?></option>
                                <option value="sales_tax"><?php esc_html_e('Sales Tax', 'homey'); ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="earning-start-date"><?php esc_html_e('Start Date', 'homey'); ?></label>
                            <input type="text" class="form-control datepicker" id="earning-start-date" name="earning_start_date" placeholder="<?php esc_html_e('Start Date', 'homey'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="earning-end-date"><?php esc_html_e('End Date', 'homey'); ?></label>
                            <input type="text" class="form-control datepicker" id="earning-end-date" name="earning_end_date" placeholder="<?php esc_html_e('End Date', 'homey'); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary"><?php esc_html_e('Calculate', 'homey'); ?></button>
                    </form>

                    <!-- Result Display Area -->
                    <div id="earning-calculator-result" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="wallet-box-wrap">
    <div class="row">
        <div class="col-sm-12 col-xs-12">
            <div class="block table-block dashboard-withdraw-table dashboard-table">
                <div class="block-title">
                    <div class="block-left">
                        <h2 class="title"><?php esc_html_e('History', 'homey'); ?></h2>
                    </div>
                    <div class="block-right wallet-filters">
                        <div class="form-group" style="margin-bottom: 0px;">
                            <input name="wallet_reservations_search" type="text" class="form-control" value="" placeholder="Search...">
                        </div>
                        <div class="form-group" style="margin-bottom: 0px;">
                            <input type="text" name="wallet_reservations_start_date" id="wallet_reservations_start_date" class="form-control datepicker" placeholder="Start Date">
                        </div>
                        <div class="form-group" style="margin-bottom: 0px;">
                            <input type="text" name="wallet_reservations_end_date" id="wallet_reservations_end_date" class="form-control datepicker" placeholder="End Date">
                        </div>
                        <div class="search-filters wallet-filters">
                            <div class="dropdown">
                                <button type="button" class="btn btn-grey-outlined wallet-filter-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-sliders fa-rotate-90 search-filter-btn-i" aria-hidden="true"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right wallet-filter-dropdown">
                                    <div class="dropdown-item">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" class="wallet-filter-checkbox" value="amenity"> Amenity
                                        </label>
                                    </div>
                                    <div class="dropdown-item">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" class="wallet-filter-checkbox" value="sleeping_accommodation"> Sleeping Accommodation
                                        </label>
                                    </div>
                                    <div class="dropdown-item">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" class="wallet-filter-checkbox" value="guided_service"> Guided Service
                                        </label>
                                    </div>
                                    <div class="dropdown-item">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" class="wallet-filter-checkbox" value="additional_vehicles"> Additional Vehicles
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                $reservations_query = new WP_Query($new_args);
                if ($reservations_query->have_posts()) {
                ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('ID', 'homey'); ?></th>
                                <th><?php esc_html_e('Status', 'homey'); ?></th>
                                <th><?php esc_html_e('Title', 'homey'); ?></th>
                                <th><?php esc_html_e('Date', 'homey'); ?></th>
                                <th><?php esc_html_e('Amenity', 'homey'); ?></th>
                                <th><?php esc_html_e('Guided Service', 'homey'); ?></th>
                                <th><?php esc_html_e('Sleeping Accomodation', 'homey'); ?></th>
                                <th><?php esc_html_e('Additional Vehicles', 'homey'); ?></th>
                                <th><?php esc_html_e('Occupancy Tax', 'homey'); ?></th>
                                <th><?php esc_html_e('Sales Tax', 'homey'); ?></th>
                                <th><?php esc_html_e('Tax Status', 'homey'); ?></th>
                                <th><?php esc_html_e('Total Earning', 'homey'); ?></th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            while ($reservations_query->have_posts()):
                                $reservations_query->the_post();
                                $reservation_id = get_the_ID();
                                $reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
                                $listing_author = homey_get_author('40', '40', 'img-circle media-object avatar');
                                $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
                                $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
                                $hours_total_price = $reservation_meta['hours_total_price'];
                                $additional_guests_total_price = doubleval($reservation_meta['additional_guests_total_price']);
                                $amenity_price = $hours_total_price + $additional_guests_total_price;
                                $cleaning_fee = $reservation_meta['cleaning_fee'];
                                $accomodation_fee = $reservation_meta['total_accomodation_fee'];
                                $total_acc_fee = $accomodation_fee + $cleaning_fee;
                                $additional_vehicles_fee = $reservation_meta['additional_vehicles_fee'];
                                $occ_tax_amount = homey_formatted_price($reservation_meta['occ_tax_amount']);
                                $total_state_tax = homey_formatted_price($reservation_meta['total_state_tax']);
                                $host_transferred_amount = get_post_meta($reservation_id, 'host_transferred_amount', true);

                                $total_guided_price = 0;

                                $total_non_participants_price = floatval($reservation_meta['total_non_participants_price']);
                                if (!empty($total_non_participants_price)) {
                                    $total_guided_price += $total_non_participants_price;
                                }

                                $total_guest_hourly = floatval($reservation_meta['total_guest_hourly']);
                                if (!empty($total_guest_hourly)) {
                                    $total_guided_price += $total_guest_hourly;
                                }

                                $total_guest_fixed = floatval($reservation_meta['total_guest_fixed']);
                                if (!empty($total_guest_fixed)) {
                                    $total_guided_price += $total_guest_fixed;
                                }

                                $total_group_hourly = floatval($reservation_meta['total_group_hourly']);
                                if (!empty($total_group_hourly)) {
                                    $total_guided_price += $total_group_hourly;
                                }

                                $total_group_fixed = floatval($reservation_meta['total_group_fixed']);
                                if (!empty($total_group_fixed)) {
                                    $total_guided_price += $total_group_fixed;
                                }

                                $total_flat_hourly = floatval($reservation_meta['total_flat_hourly']);
                                if (!empty($total_flat_hourly)) {
                                    $total_guided_price += $total_flat_hourly;
                                }

                                $total_equipments_price = floatval($reservation_meta['total_equipments_price']);
                                if (!empty($total_equipments_price)) {
                                    $total_guided_price += $total_equipments_price;
                                }

                                $tax_status = get_post_meta($reservation_id, 'tax_status', true);
                                $tax_status = empty($tax_status) ? 'unpaid' : $tax_status;
                            ?>
                                <tr>
                                    <td data-label="<?php esc_html_e('ID', 'homey'); ?>">
                                        #<?php echo esc_attr($reservation_id); ?>
                                    </td>
                                    <td data-label="<?php esc_html_e('Status', 'homey'); ?>">
                                        <span class="label label-success"><?php echo esc_attr($reservation_status); ?></span>
                                    </td>
                                    <td data-label="<?php esc_html_e('Title', 'homey'); ?>">
                                        <a href="<?php echo get_permalink($listing_id); ?>"><strong><?php echo get_the_title($listing_id); ?></strong></a>
                                    </td>
                                    <td data-label="<?php echo esc_attr($homey_local['date_label']); ?>">
                                        <?php esc_attr(the_time(homey_convert_date(homey_option('homey_date_format')))); ?><br>
                                        <?php esc_attr(the_time(homey_time_format())); ?>
                                    </td>
                                    <td data-label="<?php esc_html_e('Amenity', 'homey'); ?>">
                                        <?php echo homey_formatted_price($amenity_price); ?>
                                    </td>
                                    <td data-label="<?php esc_html_e('Guided Service', 'homey'); ?>">
                                        <?php echo !empty($total_guided_price) ? homey_formatted_price($total_guided_price) : 'x'; ?>
                                    </td>
                                    <td data-label="<?php esc_html_e('Accommodation', 'homey'); ?>">
                                        <?php echo !empty($accomodation_fee) ? homey_formatted_price($total_acc_fee) : 'x'; ?>
                                    </td>
                                    <td data-label="<?php esc_html_e('Additional Vehicles', 'homey'); ?>">
                                        <?php echo !empty($additional_vehicles_fee) ? homey_formatted_price($additional_vehicles_fee) : 'x'; ?>
                                    </td>
                                    <td data-label="<?php esc_html_e('Occupancy Tax', 'homey'); ?>">
                                        <?php echo !empty($occ_tax_amount) ? $occ_tax_amount : 'x'; ?>
                                    </td>
                                    <td data-label="<?php esc_html_e('Sales Tax', 'homey'); ?>">
                                        <?php echo !empty($total_state_tax) ? $total_state_tax : 'x'; ?>
                                    </td>
                                    <td data-label="<?php esc_html_e('Tax Status', 'homey'); ?>" class="wallet-tax-toggle" data-reservation-id="<?php echo esc_attr($reservation_id); ?>">
                                        <label class="switch">
                                            <input type="checkbox" class="tax-status-toggle" data-reservation-id="<?php echo esc_attr($reservation_id); ?>" <?php echo ($tax_status == 'paid' ? 'checked' : ''); ?>>
                                            <span class="slider round <?php echo ($tax_status == 'paid' ? 'paid' : 'unpaid'); ?>"></span>
                                        </label>
                                        <span class="tax-status-text"><?php echo ucfirst($tax_status); ?></span>
                                    </td>
                                    <td data-label="<?php esc_html_e('Host Amount', 'homey'); ?>">
                                        <?php echo homey_formatted_price($host_transferred_amount); ?>
                                    </td>
                                </tr>
                            <?php endwhile;
                            wp_reset_postdata(); ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <div class="block-body">
                        <?php esc_html_e('At the moment there are no booked reservations.', 'homey'); ?>
                    </div>
                <?php } ?>
                <div class="wallet-filters-pagination">
                    <?php homey_pagination($reservations_query->max_num_pages, $range = 2); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .wallet-filters {
        display: flex;
        gap: 20px;
    }

    .wallet-tax-toggle .switch {
        position: relative;
        display: inline-block;
        width: 55px;
        height: 28px;
    }

    .wallet-tax-toggle label {
        margin: 0px;
    }

    .wallet-tax-toggle .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .wallet-tax-toggle .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
    }

    .wallet-tax-toggle .slider.round {
        border-radius: 34px;
    }

    .wallet-tax-toggle .slider.round:before {
        border-radius: 50%;
    }

    .wallet-tax-toggle input:checked+.slider {
        background-color: #4CAF50;
    }

    .wallet-tax-toggle input:checked+.slider:before {
        transform: translateX(26px);
    }

    .wallet-tax-toggle .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
    }

    .wallet-tax-toggle .slider.paid {
        background-color: #4CAF50;
    }

    .wallet-tax-toggle .slider.unpaid {
        background-color: #ccc;
    }

    .wallet-tax-toggle .tax-status-text {
        margin-left: 10px;
        top: 3px;
        position: relative;
    }

    .wallet-filters-pagination .pagination li:first-child,
    .wallet-filters-pagination .pagination li:last-child {
        display: none;
    }

    .wallet-filters button {
        background: #3A3D32;
        background-color: rgb(58, 61, 50);
        border-color: #3A3D32 !important;
        width: 42px !important;
        height: 42px !important;
        position: relative;
        padding: 0;
        font-size: 14px;
    }

    .wallet-filters button i {
        transform: rotate(180deg);
        position: absolute;
        color: #fff;
        font-size: 18px;
        top: 28%;
        left: 28%;
    }

    /* Dropdown styling */
    .wallet-filter-dropdown {
        padding: 10px;
        min-width: 250px;
    }

    .wallet-filter-dropdown .dropdown-item {
        padding: 5px 10px;
    }

    .wallet-filter-dropdown .checkbox-inline {
        display: flex;
        align-items: center;
    }

    .wallet-filter-dropdown .checkbox-inline input {
        width: 30px;
        margin: 0px;
        margin-left: -30px;
    }

    #earning-calculator-result {
        margin-top: 15px;
    }

    #earning-calculator-form {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    #earning-calculator-form .form-group,
    #earning-calculator-form button {
        flex: 1;
    }

    #earning-calculator-form button {
        margin-top: 10px;
    }
</style>