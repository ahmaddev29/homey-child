<?php
global $post, $current_user, $homey_prefix, $homey_local;
$current_user = wp_get_current_user();
$userID = $current_user->ID;

$owner_name = $owner_pic_escaped = $owner_languages = '';

$listing_id = isset($_GET['listing_id']) ? $_GET['listing_id'] : '';
$guests = isset($_GET['guest']) ? $_GET['guest'] : '';
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$start_hour = isset($_GET['start_hour']) ? $_GET['start_hour'] : '';
$end_hour = isset($_GET['end_hour']) ? $_GET['end_hour'] : '';
$extra_options = isset($_GET['extra_options']) ? $_GET['extra_options'] : array();
$guest_message = isset($_GET['guest_message']) ? $_GET['guest_message'] : '';
$security = isset($_GET['security']) ? $_GET['security'] : '';
$accommodation_number = isset($_GET['accommodation_number']) ? $_GET['accommodation_number'] : '';
$choose_guided_service = isset($_GET['choose_guided_service']) ? $_GET['choose_guided_service'] : '';
$guests_participating = isset($_GET['guests_participating']) ? $_GET['guests_participating'] : '';
$extra_participants = isset($_GET['extra_participants']) ? $_GET['extra_participants'] : '';
$guests_gears = isset($_GET['guests_gears']) ? $_GET['guests_gears'] : '';
$guests_ages = isset($_GET['guests_ages']) ? $_GET['guests_ages'] : '';
$health_conditions = isset($_GET['health_conditions']) ? $_GET['health_conditions'] : '';
$experience_level = isset($_GET['experience_level']) ? $_GET['experience_level'] : '';
$first_timers = isset($_GET['first_timers']) ? $_GET['first_timers'] : '';
$extra_equipments = isset($_GET['extra_equipments']) ? $_GET['extra_equipments'] : array();
$additional_vehicles = isset($_GET['additional_vehicles']) ? $_GET['additional_vehicles'] : '';
$coupon_code = isset($_GET['coupon_code']) ? $_GET['coupon_code'] : '';

echo '<input type="hidden" id="booking_listing_id" name="booking_listing_id" value="' . htmlspecialchars($listing_id) . '">';
echo '<input type="hidden" id="booking_guests" name="booking_guest" value="' . htmlspecialchars($guests) . '">';
echo '<input type="hidden" id="booking_check_in" name="booking_check_in" value="' . htmlspecialchars($check_in) . '">';
echo '<input type="hidden" id="booking_start_hour" name="booking_start_hour" value="' . htmlspecialchars($start_hour) . '">';
echo '<input type="hidden" id="booking_end_hour" name="booking_end_hour" value="' . htmlspecialchars($end_hour) . '">';


if (!empty($extra_options)) {
    foreach ($extra_options as $index => $option) {
        list($name, $price, $type) = explode('|', $option);
        echo '<input type="hidden" id="booking_extra_options' . $index . '" name="booking_extra_options[]" value="' . htmlspecialchars($option) . '" data-name="' . htmlspecialchars($name) . '" data-price="' . htmlspecialchars($price) . '" data-type="' . htmlspecialchars($type) . '">';
    }
}

echo '<input type="hidden" id="booking_guest_message" name="booking_guest_message" value="' . htmlspecialchars($guest_message) . '">';
echo '<input type="hidden" id="booking_security" name="booking_security" value="' . htmlspecialchars($security) . '">';
echo '<input type="hidden" id="booking_accommodation_number" name="booking_accommodation_number" value="' . htmlspecialchars($accommodation_number) . '">';
echo '<input type="hidden" id="booking_choose_guided_service" name="booking_choose_guided_service" value="' . htmlspecialchars($choose_guided_service) . '">';
echo '<input type="hidden" id="booking_guests_participating" name="booking_guests_participating" value="' . htmlspecialchars($guests_participating) . '">';
echo '<input type="hidden" id="booking_extra_participants" name="booking_extra_participants" value="' . htmlspecialchars($extra_participants) . '">';
echo '<input type="hidden" id="booking_guests_gears" name="booking_guests_gears" value="' . htmlspecialchars($guests_gears) . '">';
echo '<input type="hidden" id="booking_guests_ages" name="booking_guests_ages" value="' . htmlspecialchars($guests_ages) . '">';
echo '<input type="hidden" id="booking_health_conditions" name="booking_health_conditions" value="' . htmlspecialchars($health_conditions) . '">';
echo '<input type="hidden" id="booking_experience_level" name="booking_experience_level" value="' . htmlspecialchars($experience_level) . '">';
echo '<input type="hidden" id="booking_first_timers" name="booking_first_timers" value="' . htmlspecialchars($first_timers) . '">';

if (!empty($extra_equipments)) {
    foreach ($extra_equipments as $index => $equipment) {
        list($name, $price, $type) = explode('|', $equipment);
        echo '<input type="hidden" id="booking_extra_equipments' . $index . '" name="booking_extra_equipments[]" value="' . htmlspecialchars($equipment) . '" data-name="' . htmlspecialchars($name) . '" data-price="' . htmlspecialchars($price) . '" data-type="' . htmlspecialchars($type) . '">';
    }
}

echo '<input type="hidden" id="booking_additional_vehicles" name="booking_additional_vehicles" value="' . htmlspecialchars($additional_vehicles) . '">';
echo '<input type="hidden" id="booking_coupon_code" name="booking_coupon_code" value="' . htmlspecialchars($coupon_code) . '">';


$check_in_hour = $check_in . ' ' . $start_hour;
$check_out_hour = $check_in . ' ' . $end_hour;

if (!empty($listing_id)) {
    $listing_owner_id = get_post_field('post_author', $listing_id);

    $listing_owner = homey_get_author_by_id($w = '70', $h = '70', $classes = 'img-responsive img-circle', $listing_owner_id);

    $owner_pic_escaped = $listing_owner['photo'];
    $owner_name = $listing_owner['name'];
    $owner_languages = $listing_owner['languages'];
}

$check_availability = check_hourly_booking_availability($check_in, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $listing_id, $guests);
$is_available = $check_availability['success'];
$check_message = $check_availability['message'];
$ins_learnmore = homey_option('ins_learnmore');

$current_user_id = get_current_user_id();
$saved_cards = get_user_meta($current_user_id, 'saved_stripe_cards', true);
$saved_cards = $saved_cards ? json_decode($saved_cards, true) : [];
?>

<section class="main-content-area booking-page">
    <?php
    if (!$is_available || empty($listing_id)) { ?>

        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

                    <?php
                    if (!$is_available) {
                        $ins_warning = $check_message;
                    } elseif (empty($listing_id)) {
                        $ins_warning = $homey_local['ins_no_listing'];
                    }
                    ?>

                    <div class="alert alert-danger" role="alert">
                        <i class="fa fa-exclamation-circle"></i> <?php echo esc_html($ins_warning); ?>
                    </div>
                    <a href="<?php echo get_permalink($listing_id); ?>"
                        class="btn btn-primary"><?php echo esc_attr($homey_local['continue_btn']); ?></a>
                </div><!-- col-xs-12 col-sm-12 col-md-12 col-lg-12 -->
            </div><!-- .row -->
        </div><!-- .container -->

    <?php
    } else {
    ?>
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="page-title text-left">
                        <div class="block-top-title block-top-flex">
                            <img src="<?php echo wp_get_attachment_url(5684); ?>" width="200" height="200"
                                alt="Confirm & Pay">
                            <h1 class="listing-title">Confirm and Pay</h1>
                        </div><!-- block-top-title -->
                    </div><!-- page-title -->
                </div><!-- col-xs-12 col-sm-12 col-md-12 col-lg-12 -->
            </div><!-- .row -->
        </div><!-- .container -->

        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-5 col-md-push-7 col-lg-push-7">

                    <?php get_template_part('single-listing/booking/sidebar-instance-booking-hourly'); ?>

                </div>

                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-7 col-md-pull-5 col-lg-pull-5">

                    <div class="block homey-booking-block-title-1">
                        <div class="block-head table-block">
                            <h2 class="title">Your Adventure</h2>
                            <div class="booking-dates">
                                <h3>Dates</h3>
                                <p>
                                    Check In:
                                    <strong>
                                        <?php echo homey_format_date_simple($check_in); ?>
                                        <?php echo esc_html__('at', 'homey'); ?>
                                        <?php echo date(homey_time_format(), strtotime($start_hour)); ?>
                                    </strong>
                                </p>
                                <p>
                                    Check Out:
                                    <strong>
                                        <?php echo homey_format_date_simple($check_in); ?>
                                        <?php echo esc_html__('at', 'homey'); ?>
                                        <?php echo date(homey_time_format(), strtotime($end_hour)); ?>
                                    </strong>
                                </p>
                            </div>
                            <div class="booking-guests">
                                <h3>Guests</h3>
                                <p><?php echo $guests ?> Guests</p>
                            </div>
                        </div>
                        <div class="table-block homey-booking-block-2">
                            <h2 class="title">Select Payment Method</h2>
                            <?php if ($saved_cards) { ?>
                                <?php foreach ($saved_cards as $card): ?>
                                    <div class="stripe-payment-methods"
                                        style="display: grid;grid-template-columns: 10% 90%;grid-gap: .75rem 1rem;margin-top: 30px;">
                                        <input type="radio" name="payment_method" value="<?php echo esc_attr($card['id']); ?>">
                                        <div>
                                            <?php echo esc_html('**** **** **** ' . $card['last4'] . ' (' . $card['brand'] . ')'); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div class="Pay-button-wrapper">
                                    <button id="booking-pay-button" class="btn btn-primary">
                                        <?php esc_html_e('Pay Now', 'homey'); ?>
                                    </button>
                                </div>
                            <?php } else { ?>
                                <p style="margin: 30px 0 10px !important;">No payment method found.</p>
                            <?php } ?>
                            <div class="booking-pay-notifications"></div>
                        </div>
                    </div>
                </div>
            </div><!-- .row -->
        </div><!-- .container -->
    <?php } ?> <!-- end check availability if -->

</section><!-- main-content-area -->