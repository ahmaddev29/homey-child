<?php
global $post, $homey_local;

$listing_id = isset($_GET['listing_id']) ? $_GET['listing_id'] : '';
$guests = isset($_GET['guest']) ? $_GET['guest'] : '';
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$start_hour = isset($_GET['start_hour']) ? $_GET['start_hour'] : '';
$end_hour = isset($_GET['end_hour']) ? $_GET['end_hour'] : '';

$check_in_hour = $check_in . ' ' . $start_hour;
$check_out_hour = $check_in . ' ' . $end_hour;

$check_in_unix = strtotime($check_in);

$check_in_hour_unix = strtotime($check_in_hour);
$check_out_hour_unix = strtotime($check_out_hour);

$start_hour_unix = strtotime($start_hour);
$end_hour_unix = strtotime($end_hour);

$booking_hide_fields = homey_option('booking_hide_fields');

$listing_price = floatval(get_post_meta($listing_id, 'homey_hour_price', true));
$current_user_id = get_current_user_id();
$booking_query = guest_booking_confirmed($listing_id, $current_user_id);
$listing_city = homey_get_taxonomy_title($listing_id, 'listing_city');
$listing_state = homey_get_taxonomy_title($listing_id, 'listing_state');
$address = get_post_meta($listing_id, 'homey_listing_address', true);

$check_in_date = date(get_homey_to_std_date_format(), $check_in_unix);
$check_in_time = date(homey_time_format(), $start_hour_unix);
$check_out_time = date(homey_time_format(), $end_hour_unix);

$room_type = homey_taxonomy_simple_by_ID('room_type', $listing_id);
$listing_type = homey_taxonomy_simple_by_ID('listing_type', $listing_id);
$slash = '';
if (!empty($room_type) && !empty($listing_type)) {
    $slash = '/';
}

$guests_label = homey_option('srh_guest_label');
if ($guests > 1) {
    $guests_label = homey_option('srh_guests_label');
}

$rating = homey_option('rating');
$total_rating = get_post_meta($listing_id, 'listing_total_rating', true);
?>
<div class="booking-sidebar">

    <div class="block">

        <div class="booking-property clearfix">
            <div class="booking-property-img">
                <?php
                if (has_post_thumbnail($listing_id)) {
                    echo get_the_post_thumbnail($listing_id, 'homey-listing-thumb', array('class' => 'img-responsive'));
                } else {
                    homey_image_placeholder('homey-listing-thumb');
                }
                ?>
            </div>

            <div class="booking-property-info">
                <?php
                if ($rating && ($total_rating != '' && $total_rating != 0)) { ?>
                    <div class="list-inline rating">
                        <div>
                            <li class="fa fa-star"></li>
                            <span class="star_number"><?php echo $total_rating; ?></span>
                        </div>
                        <div class="addons-booking-price">
                            <strong>$<?php echo $listing_price; ?>/HR</strong>
                        </div>
                    </div>
                <?php } ?>
                <h3><?php echo get_the_title($listing_id); ?></h3>
                <div class="booking-property-address">
                    <?php
                    if ($booking_query->have_posts()) {
                        if (!empty($address)) {
                            echo '<address class="item-address">' . esc_attr($address) . '</address>';
                        }

                    } else {

                        if (!empty($listing_city) || !empty($listing_state) || !empty($listing_zip)) {
                            echo '<address class="item-address">';

                            $full_address = '';
                            if (!empty($listing_city)) {
                                $full_address .= esc_attr($listing_city);
                            }
                            if (!empty($listing_state)) {
                                $full_address .= ', ' . esc_attr($listing_state);
                            }

                            echo $full_address;

                            echo '</address>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="block-body">
            <?php get_template_part('single-listing/booking/payment-list-collapse-hourly'); ?>
        </div><!-- block-body -->
    </div><!-- block -->
</div><!-- booking-sidebar -->