<?php
global $post, $homey_local, $homey_prefix, $hide_labels;

$guests = homey_get_listing_data('guests');
$hour_price = homey_get_listing_data('hour_price');
$weekends_price = homey_get_listing_data('hourly_weekends_price');
$weekends_days = homey_get_listing_data('weekends_days');
$amenity_price_type = get_field('amenity_price_type', $post->ID);
$price_type_text = '';

$i = 0;
$number_to_show_image = 7;

if ($amenity_price_type == 'price_per_hour') {
    $price_type_text = 'HR';
} elseif ($amenity_price_type == 'price_per_day') {
    $price_type_text = 'DAY';
} elseif ($amenity_price_type == 'price_per_half_day') {
    $price_type_text = 'HALF DAY';
}
$min_book_hours = homey_get_listing_data('min_book_hours');
$security_deposit = homey_get_listing_data('security_deposit');
$cleaning_fee = homey_get_listing_data('cleaning_fee');
$cleaning_fee_type = homey_get_listing_data('cleaning_fee_type');
$city_fee = homey_get_listing_data('city_fee');
$city_fee_type = homey_get_listing_data('city_fee_type');
$additional_guests_price = homey_get_listing_data('additional_guests_price');
$allow_additional_guests = homey_get_listing_data('allow_additional_guests');
$num_additional_guests = homey_get_listing_data('num_additional_guests');
$restroom_access = get_field('restroom_access', $post->ID);
$how_private = get_field('how_private', $post->ID);
$car_amount = get_field('car_amount', $post->ID);
$cost_per_additional_car = get_field('cost_per_additional_car', $post->ID);
$present_on_property = get_field('present_on_property', $post->ID);
$security_cameras = get_field('security_cameras', $post->ID);
$how_much_notice = get_field('how_much_notice', $post->ID);
$how_far_in_advance = get_field('how_far_in_advance', $post->ID);
$how_much_time = get_field('how_much_time', $post->ID);
$day_of_booking = get_field('day_of_booking', $post->ID);
$more_enjoyable_experience = get_field('more_enjoyable_experience', $post->ID);
$included_with_booking = get_field('included_with_booking', $post->ID);
$special_details_about_the_booking = get_field('field_6479ea3662296', $post->ID);
$special_detail_images = get_field('special_detail_images', $post->ID);

$listing_id = $post->ID;
$current_user_id = get_current_user_id();
$hostID = get_the_author_meta('ID');

$special_features = get_field('field_6479250ae9990', $listing_id);

$booking_query = guest_booking_confirmed($listing_id, $current_user_id);

if ($restroom_access == 'primary-residence') {
    $restroom_access = esc_html__('Primary residence restroom', 'homey');
} elseif ($restroom_access == 'private') {
    $restroom_access = esc_html__('Private restroom', 'homey');
} elseif ($restroom_access == 'portable') {
    $restroom_access = esc_html__('Portable restroom', 'homey');
} elseif ($restroom_access == 'none') {
    $restroom_access = esc_html__('No restroom available', 'homey');
}

if ($how_private == 'completely-secluded') {
    $how_private = esc_html__('Completely secluded', 'homey');
} elseif ($how_private == 'private') {
    $how_private = esc_html__('Semi secluded', 'homey');
} elseif ($how_private == 'not-seculuded') {
    $how_private = esc_html__('Not secluded', 'homey');
}

if ($present_on_property == 'yes') {
    $present_on_property = esc_html__('Yes', 'homey');
} elseif ($present_on_property == 'no') {
    $present_on_property = esc_html__('No', 'homey');
} elseif ($present_on_property == 'greet-and-go') {
    $present_on_property = esc_html__('Greet & Go', 'homey');
} elseif ($present_on_property == 'not-sure') {
    $present_on_property = esc_html__('Not Sure', 'homey');
}

if ($security_cameras == 'yes') {
    $security_cameras = esc_html__('Yes', 'homey');
} elseif ($security_cameras == 'no') {
    $security_cameras = esc_html__('No', 'homey');
}

if ($how_much_notice == '1') {
    $how_much_notice = esc_html__('1 Hour', 'homey');
} elseif ($how_much_notice == '12') {
    $how_much_notice = esc_html__('12 Hours', 'homey');
} elseif ($how_much_notice == '24') {
    $how_much_notice = esc_html__('24 Hours', 'homey');
} elseif ($how_much_notice == '48') {
    $how_much_notice = esc_html__('48 Hours', 'homey');
}

if ($how_far_in_advance == '1') {
    $how_far_in_advance = esc_html__('1 Week', 'homey');
} elseif ($how_far_in_advance == '4') {
    $how_far_in_advance = esc_html__('1 Month', 'homey');
} elseif ($how_far_in_advance == '12') {
    $how_far_in_advance = esc_html__('3 Months', 'homey');
} elseif ($how_far_in_advance == '24') {
    $how_far_in_advance = esc_html__('6 Months', 'homey');
} elseif ($how_far_in_advance == '36') {
    $how_far_in_advance = esc_html__('9 Months', 'homey');
} elseif ($how_far_in_advance == '48') {
    $how_far_in_advance = esc_html__('1 Year', 'homey');
}

if ($how_much_time == '30') {
    $how_much_time = esc_html__('30 min', 'homey');
} elseif ($how_much_time == '60') {
    $how_much_time = esc_html__('1 Hour', 'homey');
} elseif ($how_much_time == '120') {
    $how_much_time = esc_html__('2 Hours', 'homey');
} elseif ($how_much_time == '180') {
    $how_much_time = esc_html__('3 Hours', 'homey');
}

if ($allow_additional_guests == 'yes') {
    $allow_additional_guests = esc_html__('Yes', 'homey');
} else {
    $allow_additional_guests = esc_html__('No', 'homey');
}

$cleaning_fee_period = $city_fee_period = '';

if ($cleaning_fee_type == 'per_stay') {
    $cleaning_fee_period = esc_html__('Per Stay', 'homey');
} elseif ($cleaning_fee_type == 'daily') {
    $cleaning_fee_period = esc_html__('Hourly', 'homey');
}

if ($city_fee_type == 'per_stay') {
    $city_fee_period = esc_html__('Per Stay', 'homey');
} elseif ($city_fee_type == 'daily') {
    $city_fee_period = esc_html__('Hourly', 'homey');
}

if ($weekends_days == 'sat_sun') {
    $weekendDays = esc_html__('Sat & Sun', 'homey');
} elseif ($weekends_days == 'fri_sat') {
    $weekendDays = esc_html__('Fri & Sat', 'homey');
} elseif ($weekends_days == 'fri_sat_sun') {
    $weekendDays = esc_html__('Fri, Sat & Sun', 'homey');
}

?>
<div id="price-section" class="price-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="section-title">
                    <h3 class="title">About the Adventure</h3>
                </div><!-- block-left -->
                <div class="section-body">
                    <ul class="detail-list detail-list-2-cols">

                        <?php if (!empty($guests) && $hide_labels['sn_guests_label'] != 1) { ?>
                            <li>
                                <i class="fa-solid fa-user" aria-hidden="true"></i>
                                Guests:
                                <?php echo esc_attr($guests); ?>
                            </li>
                        <?php } ?>

                        <?php if (!empty($hour_price) && $hide_labels['sn_hourly_label'] != 1) { ?>
                            <li>
                                <i class="fa-solid fa-user" aria-hidden="true"></i>
                                Price:
                                <?php echo homey_formatted_price($hour_price, false); ?>/<?php echo $price_type_text; ?>
                            </li>
                        <?php } ?>

                        <?php if (!empty($weekendDays) && $hide_labels['sn_weekends_label'] != 1) { ?>
                            <li>
                                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                                <?php echo esc_attr(homey_option('sn_weekends_label')); ?>:
                                <?php echo esc_attr($weekendDays); ?>
                            </li>
                        <?php } ?>

                        <?php if (!empty($weekends_price) && $hide_labels['sn_weekends_label'] != 1) { ?>
                            <li>
                                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                                Weekends Price:
                                <?php echo homey_formatted_price($weekends_price, false); ?>/<?php echo $price_type_text; ?>
                            </li>
                        <?php } ?>

                        <?php if (!empty($allow_additional_guests) && $hide_labels['sn_allow_additional_guests'] != 1) { ?>
                            <li>
                                <i class="fa-solid fa-users" aria-hidden="true"></i>
                                <?php echo esc_attr(homey_option('sn_allow_additional_guests')); ?>:
                                <?php echo esc_attr($allow_additional_guests); ?>
                            </li>
                        <?php } ?>

                        <?php if (!empty($num_additional_guests) && $hide_labels['sn_addinal_guests_label'] != 1) { ?>
                            <li>
                                <i class="fa-solid fa-users" aria-hidden="true"></i>
                                <?php echo esc_attr(homey_option('sn_addinal_guests_label')); ?>:
                                <?php echo esc_attr($num_additional_guests); ?>
                            </li>
                        <?php } ?>

                        <?php if (!empty($additional_guests_price) && $hide_labels['sn_addinal_guests_label'] != 1) { ?>
                            <li>
                                <i class="fa-solid fa-users" aria-hidden="true"></i>
                                Additional Guests Price:
                                <?php echo homey_formatted_price($additional_guests_price, false); ?>
                            </li>
                        <?php } ?>

                        <?php if (!empty($car_amount)) { ?>
                            <li>
                                <i class="fa-solid fa-car" aria-hidden="true"></i>
                                Cars Allowed:
                                <?php echo esc_attr($car_amount); ?>
                            </li>
                        <?php } ?>

                        <?php if (!empty($cost_per_additional_car)) { ?>
                            <li>
                                <i class="fa-solid fa-car" aria-hidden="true"></i>
                                Additional Car Price:
                                <?php echo homey_formatted_price($cost_per_additional_car, false); ?>
                            </li>
                        <?php } ?>

                        <?php if (!empty($min_book_hours) && $hide_labels['sn_min_no_of_hours'] != 1) { ?>
                            <li>
                                <i class="fa-solid fa-clock" aria-hidden="true"></i>
                                Minimum Booking Hours:
                                <?php echo esc_attr($min_book_hours); ?>
                            </li>
                        <?php } ?>

                        <?php if (!empty($present_on_property)) { ?>
                            <li>
                                <i class="fa-solid fa-thumbs-up" aria-hidden="true"></i>
                                Present on property:
                                <?php echo esc_attr($present_on_property); ?>
                            </li>
                        <?php } ?>

                        <?php if (!empty($security_cameras)) { ?>
                            <li>
                                <i class="fa-solid fa-video" aria-hidden="true"></i>
                                Security Cameras:
                                <?php echo esc_attr($security_cameras); ?>
                            </li>
                        <?php } ?>

                        <?php if (!empty($how_much_notice)) { ?>
                            <li>
                                <i class="fa-solid fa-clock" aria-hidden="true"></i>
                                Booking Notice Time:
                                <?php echo esc_attr($how_much_notice); ?>
                            </li>
                        <?php } ?>

                        <?php if (!empty($how_far_in_advance)) { ?>
                            <li>
                                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                                Advance Booking:
                                <?php echo esc_attr($how_far_in_advance); ?>
                            </li>
                        <?php } ?>

                        <?php if (!empty($how_much_time)) { ?>
                            <li>
                                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                                Time In-between Booking:
                                <?php echo esc_attr($how_much_time); ?>
                            </li>
                        <?php } ?>

                        <?php if (!empty($restroom_access)) { ?>
                            <li>
                                <i class="fa-solid fa-bed" aria-hidden="true"></i>
                                Restroom Access:
                                <?php echo esc_attr($restroom_access); ?>
                            </li>
                        <?php } ?>

                        <?php if (!empty($how_private)) { ?>
                            <li>
                                <i class="fa-solid fa-location-pin-lock" aria-hidden="true"></i>
                                Backyard Privacy:
                                <?php echo esc_attr($how_private); ?>
                            </li>
                        <?php } ?>

                    </ul>

                </div><!-- block-right -->
            </div><!-- block-body -->
        </div><!-- block-section -->
    </div><!-- block -->

    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div>
                    <!-- <h3 class="title">About the Adventure</h3> -->
                </div>
                <div>
                    <div class="row">
                        <?php if (!empty($special_features)) { ?>
                            <div class="col-sm-6 col-xs-12">
                                <strong>Special Features</strong>
                                <p>
                                    <?php
                                    $features = array_map(function ($feature) {
                                        return esc_html($feature['feature']);
                                    }, $special_features);

                                    echo implode(', ', $features);
                                    ?>
                                </p>
                            </div>
                        <?php } ?>

                        <?php if (!empty($day_of_booking)) { ?>
                            <div class="col-sm-6 col-xs-12">
                                <strong>Guest Must Bring The Day Of Booking</strong>
                                <p><?php echo esc_html($day_of_booking); ?></p>
                            </div>
                        <?php } ?>

                        <?php if (!empty($more_enjoyable_experience)) { ?>
                            <div class="col-sm-6 col-xs-12">
                                <strong>Guest Should Bring To Have A More Enjoyable Experience</strong>
                                <p><?php echo esc_html($more_enjoyable_experience); ?></p>
                            </div>
                        <?php } ?>

                        <?php if (!empty($included_with_booking)) { ?>
                            <div class="col-sm-6 col-xs-12">
                                <strong>What Will Be Included With Your Booking</strong>
                                <p><?php echo esc_html($included_with_booking); ?></p>
                            </div>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
    </div><!-- block -->

    <?php if ($booking_query->have_posts() || $current_user_id == $hostID) { ?>
        <?php if (!empty($special_details_about_the_booking)) { ?>
            <div class="block">
                <div class="block-section">
                    <div class="block-body">
                        <div class="section-title">
                            <h3 class="title">Special Details About the Booking</h3>
                        </div><!-- block-left -->
                        <div class="section-body">
                            <div class="row">
                                <div class="col-sm-12 col-xs-12">
                                    <p><?php echo strip_tags($special_details_about_the_booking); ?></p>

                                    <?php if (isset($special_detail_images) && false != $special_detail_images): ?>
                                        <div id="gallery-section" class="gallery-section">
                                            <div class="featured-image-wrap featured-slide-gallery-wrap clearfix">
                                                <?php foreach ($special_detail_images as $image) { ?>
                                                    <a href="<?php echo esc_url($image['full_url']); ?>" class="swipebox <?php if ($i == $number_to_show_image) {
                                                                                                                                echo 'more-images';
                                                                                                                            } elseif ($i > $number_to_show_image) {
                                                                                                                                echo 'gallery-hidden';
                                                                                                                            } ?>">
                                                        <?php if ($i >= $number_to_show_image) {
                                                            echo '<span class="specialGallery-item" data-fancy-image-index="<?php echo $number_to_show_image?>">' . count($special_detail_images) . '+</span>';
                                                        } ?>
                                                        <img data-fancy-image-index="<?php echo $i; ?>" class="specialGallery-item"
                                                            src="<?php echo esc_url($image['url']); ?>"
                                                            alt="<?php echo esc_attr($image['alt']); ?>">
                                                    </a>
                                                <?php $i++;
                                                } ?>
                                            </div>
                                        </div>
                                        <?php fancybox_gallery_html($special_detail_images, 'specialGallery'); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } ?>


    <div class="block">
        <div class="block-section">
            <?php get_template_part('single-listing/extra-prices'); ?>
        </div>
    </div>
</div>

<script>
    (function($) {
        $(".specialGallery-item").on("click", function(e) {
            e.preventDefault();
            var fancy_image_index = $(this).data("fancyImageIndex");
            $.fancybox.open($(".specialGallery")).jumpTo(fancy_image_index);
        });
    })(jQuery);
</script>