<?php
/**
 * Template Name: Instance Booking Addons
 */

$no_login_needed_for_booking = homey_option('no_login_needed_for_booking');
if (
    $no_login_needed_for_booking == 'no' &&
    !is_user_logged_in()
) {
    wp_redirect(home_url('/'));
    return false;
}
get_header();
global $post, $current_user,
$homey_prefix, $homey_local;
$current_user = wp_get_current_user();
$userID = $current_user->ID;

$owner_name = $owner_pic_escaped = $owner_languages = '';

$listing_id = isset($_GET['listing_id']) ? $_GET['listing_id'] : '';
$guests = isset($_GET['guest']) ? $_GET['guest'] : '';
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$start_hour = isset($_GET['start_hour']) ? $_GET['start_hour'] : '';
$end_hour = isset($_GET['end_hour']) ? $_GET['end_hour'] : '';
$extra_options = isset($_GET['extra_options']) ? $_GET['extra_options'] : '';
$guest_message = isset($_GET['guest_message']) ? $_GET['guest_message'] : '';

$check_in_hour = $check_in . ' ' . $start_hour;
$check_out_hour = $check_in . ' ' . $end_hour;

if (!empty($listing_id)) {
    $listing_owner_id = get_post_field('post_author', $listing_id);

    $listing_owner = homey_get_author_by_id(
        $w = '70',
        $h = '70',
        $classes = 'img-responsive img-circle',
        $listing_owner_id
    );

    $owner_pic_escaped = $listing_owner['photo'];
    $owner_name = $listing_owner['name'];
    $owner_languages = $listing_owner['languages'];

    $extra_prices = get_post_meta($listing_id, 'homey_extra_prices', true);

    // Parse the URL parameters to get the 'extra_options'
    $parsed_url = parse_url($_SERVER['REQUEST_URI']);
    parse_str($parsed_url['query'], $url_params);

    // Extract the extra_options from the URL
    $selected_extra_options = isset($url_params['extra_options']) ? $url_params['extra_options'] : [];
}
?>
<section class="main-content-area booking-addons-page">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="page-title text-left">
                    <div class="block-top-title">
                        <h1 class="listing-title">Add Ons</h1>
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
                <div class="booking-main-content">
                    <div class="block">
                        <?php if (!empty($extra_prices[0]['name']) || !empty($extra_prices[-1]['name'])) { ?>
                            <div class="search-extra-services">
                                <ul class="extra-services-list list-unstyled clearfix">
                                    <?php
                                    if (is_array($extra_prices)) {
                                        foreach ($extra_prices as $key => $option) {
                                            // Prepare the current option in the format used in the URL (e.g., "Heated Pool|25|per_night")
                                            $current_option = $option['name'] . '|' . $option['price'] . '|' . $option['type'];

                                            // Check if the current option is in the selected extra options from the URL
                                            $is_checked = in_array($current_option, $selected_extra_options) ? 'checked' : '';
                                            ?>
                                            <li>
                                                <label class="homey_extra_options control control--checkbox">
                                                    <input type="checkbox" class="extra-option-checkbox" name="extra_price[]"
                                                        data-name="<?php echo esc_html__(esc_attr($option['name']), 'homey'); ?>"
                                                        data-price="<?php echo esc_attr($option['price']); ?>"
                                                        data-type="<?php echo esc_html__(esc_attr($option['type']), 'homey'); ?>"
                                                        <?php echo $is_checked; ?>>
                                                    <span
                                                        class="control-text"><?php echo esc_html__(esc_attr($option['name']), 'homey'); ?></span>
                                                    <span class="control__indicator"></span>
                                                </label>
                                                <span><?php echo homey_formatted_price($option['price']); ?></span>
                                            </li>
                                            <?php
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                        <?php } ?>
                        <div class="continue-button-wrapper">
                            <button id="continue-instance-button" class="btn btn-primary">
                                <?php esc_html_e('Continue', 'homey'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>

<script>
    jQuery(document).ready(function ($) {
        $('.extra-option-checkbox').on('change', function () {
            const currentUrl = window.location.href;
            var url = new URL(currentUrl);
            var searchParams = new URLSearchParams(url.search);

            let keysToDelete = [];
            searchParams.forEach(function (value, key) {
                let decodedKey = decodeURIComponent(key);
                if (decodedKey.startsWith('extra_options')) {
                    keysToDelete.push(key);
                }
            });

            keysToDelete.forEach(function (key) {
                searchParams.delete(key);
            });

            url.search = searchParams.toString();
            let checkedOptions = [];

            $('.extra-option-checkbox:checked').each(function () {
                const optionValue = $(this).data('name') + '|' + $(this).data('price') + '|' + $(this).data('type');
                searchParams.append('extra_options[]', optionValue);
            });

            url.search = searchParams.toString();
            window.history.replaceState({}, '', url.href);
            window.location.href = url.href;

        });

        $('#continue-instance-button').on('click', function () {
            const currentUrl = new URL(window.location.href);
            const confirmationPageUrl = currentUrl.origin + '/instance' + currentUrl.search;
            window.location.href = confirmationPageUrl;
        });
    });
</script>

<?php get_footer(); ?>