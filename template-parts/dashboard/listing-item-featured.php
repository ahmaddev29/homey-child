<?php
global $post,
    $edit_link,
    $homey_local,
    $homey_prefix,
    $prop_address,
    $prop_featured,
    $payment_status;
$hide_labels = homey_option('show_hide_labels');
$post_id = get_the_ID();

$amenity_price_type = get_field('amenity_price_type', $post_id);
$price_type_text = '';

if ($amenity_price_type == 'price_per_hour') {
    $price_type_text = 'Hour';
} elseif ($amenity_price_type == 'price_per_day') {
    $price_type_text = 'Day';
} elseif ($amenity_price_type == 'price_per_half_day') {
    $price_type_text = 'Half Day';
}

$dashboard = homey_get_template_link('template/dashboard.php');
$upgrade_link = add_query_arg(
    array(
        'dpage' => 'upgrade_featured',
        'upgrade_id' => $post_id,
    ),
    $dashboard
);
$listing_images = get_post_meta(get_the_ID(), $homey_prefix . 'listing_images', false);
$address = get_post_meta(get_the_ID(), $homey_prefix . 'listing_address', true);
$bedrooms = get_post_meta(get_the_ID(), $homey_prefix . 'listing_bedrooms', true);
$guests = get_post_meta(get_the_ID(), $homey_prefix . 'guests', true);
$beds = get_post_meta(get_the_ID(), $homey_prefix . 'beds', true);
$baths = get_post_meta(get_the_ID(), $homey_prefix . 'baths', true);
$night_price = get_post_meta(get_the_ID(), $homey_prefix . 'night_price', true);
$featured = get_post_meta(get_the_ID(), $homey_prefix . 'featured', true);
$queued_featured = get_post_meta(get_the_ID(), 'homey_queued_featured', true);

$listing_price = homey_get_price_by_id($post_id);

$dashboard_listings = homey_get_template_link('template/dashboard-listing.php');
$edit_link = add_query_arg('edit_listing', $post_id, $edit_link);
$delete_link = add_query_arg('listing_id', $post_id, $dashboard_listings);
$property_status = get_post_status($post->ID);
$check_listing_status = $property_status;
$dashboard = homey_get_template_link('template/dashboard.php');
$price_separator = homey_option('currency_separator');
$make_featured = homey_option('make_featured');

if ($property_status == 'publish') {
    $property_status = esc_html__('Published', 'homey');
    $status_class = "label-success";
} elseif ($property_status == 'pending') {
    $status_class = "label-warning";
    $property_status = esc_html__('Waiting for Approval', 'homey');
} elseif ($property_status == 'draft') {
    $status_class = 'label-default';
    $property_status = esc_html__('Draft', 'homey');
} elseif ($property_status == 'disabled') {
    $status_class = 'label-danger';
    $property_status = esc_html__('Disabled', 'homey');
} elseif ($property_status == 'declined') {
    $status_class = 'label-danger';
    $property_status = esc_html__('Disapproved', 'homey');
} else {
    $status_class = "label-success";
    $property_status = esc_html__(strtoupper($property_status), 'homey');
}

if ($check_listing_status == 'publish') {
    $disable_list_text = esc_html__('Disable Listing', 'homey');
    $icon = 'fa-pause';
    $list_current_status = 'enabled';
} elseif ($check_listing_status == 'disabled') {
    $disable_list_text = esc_html__('Enable Listing', 'homey');
    $list_current_status = 'disabled';
    $icon = 'fa-play';
}

$upgrade_link = add_query_arg(
    array(
        'dpage' => 'upgrade_featured',
        'upgrade_id' => $post_id,
    ),
    $dashboard
);
?>

<tr>
    <td data-label="<?php echo homey_option('sn_id_label'); ?>"><?php echo get_the_ID(); ?></td>
    <td data-label="<?php echo esc_attr($homey_local['thumb_label']); ?>">
        <a href="<?php the_permalink(); ?>">
            <?php
            if (has_post_thumbnail($post->ID)) {
                the_post_thumbnail('homey-listing-thumb', array('class' => 'img-responsive dashboard-listing-thumbnail'));
            } else {
                homey_image_placeholder('homey-listing-thumb');
            }
            ?>
        </a>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['address']); ?>">
        <a href="<?php the_permalink(); ?>"><strong><?php the_title(); ?></strong></a>
        <?php if (!empty($address)) { ?>
            <address><?php echo esc_attr($address); ?></address>
        <?php } ?>
    </td>
    <!-- <td data-label="ID">HY01</td> -->
    <td data-label="<?php echo homey_option('sn_type_label'); ?>"><?php echo homey_taxonomy_simple('listing_type'); ?>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['price_label']); ?>">
        <?php if (!empty($listing_price)) { ?>
            <strong><?php echo homey_formatted_price($listing_price, false); ?><?php echo esc_attr($price_separator); ?><?php echo $price_type_text; ?></strong><br>
        <?php } ?>
    </td>
    <?php if ($hide_labels['sn_bedrooms_label'] != 1) { ?>
        <td data-label="<?php echo homey_option('glc_bedrooms_label'); ?>"><?php echo esc_attr($bedrooms); ?></td>
    <?php } ?>
    <?php if ($hide_labels['sn_bathrooms_label'] != 1) { ?>
        <td data-label="<?php echo homey_option('glc_baths_label'); ?>"><?php echo esc_attr($baths); ?></td>
    <?php } ?>
    <td data-label="<?php echo homey_option('glc_guests_label'); ?>"><?php echo esc_attr($guests); ?></td>
    <td>
        <?php if ($featured == 1 || $queued_featured == 1) { ?>
            <span class="label label-success">Featured</span>
        <?php } else { ?>
            <span class="label label-danger">Not Featured</span>
        <?php } ?>
    </td>
    <td>
        <?php if ($featured == 1 || $queued_featured == 1) { ?>
            <a class="btn btn-slim btn-primary" style="margin-top: 5px;display: block;"
                href="<?php echo home_url('/featured-listing/?listing_id=' . $post_id); ?>">See Calendar</a>
        <?php } else { ?>
            <a class="btn btn-slim btn-primary"
                href="<?php echo esc_url($upgrade_link); ?>"><?php echo esc_attr($homey_local['upgrade_btn']); ?></a>
        <?php } ?>
    </td>
</tr>