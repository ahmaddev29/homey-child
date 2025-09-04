<?php
global $post, $homey_prefix, $homey_local, $listing_data;
$listing_images = get_post_meta(get_the_ID(), $homey_prefix . 'listing_images', false);
$address        = get_post_meta(get_the_ID(), $homey_prefix . 'listing_address', true);
$bedrooms       = get_post_meta(get_the_ID(), $homey_prefix . 'listing_bedrooms', true);
$guests         = get_post_meta(get_the_ID(), $homey_prefix . 'guests', true);

$listing_city   = homey_get_taxonomy_title(get_the_ID(), 'listing_city');
$listing_state   = homey_get_taxonomy_title(get_the_ID(), 'listing_state');
$listing_zip = homey_get_listing_data('zip');

$listing_id = get_the_ID();
$current_user_id = get_current_user_id();

$amenity_price_type = get_field('amenity_price_type', $listing_id);
$price_type_text = '';

if ($amenity_price_type == 'price_per_hour') {
    $price_type_text = 'HR';
} elseif ($amenity_price_type == 'price_per_day') {
    $price_type_text = 'DAY';
} elseif ($amenity_price_type == 'price_per_half_day') {
    $price_type_text = 'HALF DAY';
}

$booking_query = guest_booking_confirmed($listing_id, $current_user_id);

$allow_additional_guests = get_post_meta(get_the_ID(), $homey_prefix . 'allow_additional_guests', true);
$num_additional_guests = get_post_meta(get_the_ID(), $homey_prefix . 'num_additional_guests', true);

$is_accomodation = get_field('field_6479eb9f0208c', get_the_ID());

if ($allow_additional_guests == 'yes' && !empty($num_additional_guests)) {
    $guests = $guests + $num_additional_guests;
}

$beds           = get_post_meta(get_the_ID(), $homey_prefix . 'beds', true);
$baths          = get_post_meta(get_the_ID(), $homey_prefix . 'baths', true);
$night_price    = get_post_meta(get_the_ID(), $homey_prefix . 'night_price', true);
$listing_author = homey_get_author();
$enable_host = homey_option('enable_host');
$compare_favorite = homey_option('compare_favorite');

$listing_price = homey_get_price();

$cgl_meta = homey_option('cgl_meta');
$cgl_beds = homey_option('cgl_beds');
$cgl_baths = homey_option('cgl_baths');
$cgl_guests = homey_option('cgl_guests');
$cgl_types = homey_option('cgl_types');
$rating = homey_option('rating');
$total_rating = get_post_meta(get_the_ID(), 'listing_total_rating', true);

$bedrooms_icon = homey_option('lgc_bedroom_icon');
$bathroom_icon = homey_option('lgc_bathroom_icon');
$guests_icon = homey_option('lgc_guests_icon');
$price_separator = homey_option('currency_separator');

if (!empty($bedrooms_icon)) {
    $bedrooms_icon = '<i class="' . esc_attr($bedrooms_icon) . '"></i>';
}
if (!empty($bathroom_icon)) {
    $bathroom_icon = '<i class="' . esc_attr($bathroom_icon) . '"></i>';
}
if (!empty($guests_icon)) {
    $guests_icon = '<i class="' . esc_attr($guests_icon) . '"></i>';
}
$homey_permalink = homey_listing_permalink();
?>
<div class="item-wrap infobox_trigger homey-matchHeight" data-id="<?php echo $post->ID; ?>">
    <div class="media property-item">
        <div class="media-left">
            <div class="item-media item-media-thumb">

                <?php homey_listing_featured(get_the_ID()); ?>

                <a class="hover-effect" href="<?php echo esc_url($homey_permalink); ?>">
                    <?php
                    if (has_post_thumbnail($post->ID)) {
                        the_post_thumbnail('homey-listing-thumb',  array('class' => 'img-responsive'));
                    } else {
                        homey_image_placeholder('homey-listing-thumb');
                    }
                    ?>
                </a>

                <?php if ($compare_favorite) { ?>
                    <div class="footer-right posistion_fav_button">
                        <div class="item-tools">
                            <div class="btn-group dropup">
                                <?php get_template_part('template-parts/listing/compare-fav'); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>


            </div>
        </div>
        <div class="media-body item-body clearfix">
            <div class="item_rate_price_container">
                <p class="item_star_review">
                    <?php
                    if ($rating && ($total_rating != '' && $total_rating != 0)) { ?>

                        <!--  <?php echo homey_get_review_stars($total_rating, false, true); ?> -->
                        <img src="/wp-content/uploads/2022/11/star.svg" alt="star"><span><span><?php echo $total_rating ?></span></span>
                </p>

            <?php } else { ?>
                <span>No Review Yet</span>
            <?php } ?>
            <?php if (!empty($listing_price)) { ?>


                <p class="item_price_p">

                    $<?php echo $listing_price; ?>/<?php echo $price_type_text; ?>
                </p>

            <?php } ?>
            </div>
            <div class="item-title-head table-block">
                <div class="title-head-left">
                    <h2 class="title"><a href="<?php echo esc_url($homey_permalink); ?>">
                            <?php the_title(); ?></a></h2>

                    <?php
                    if ($booking_query->have_posts()) {

                        if (!empty($address)) {
                            echo '<address class="item-address" style="margin-top:5px;margin-bottom:5px">' . esc_attr($address) . '</address>';
                        }
                    } else {

                        if (!empty($listing_city) || !empty($listing_state) || !empty($listing_zip)) {
                            echo '<address class="item-address" style="margin-top:5px;margin-bottom:5px">';

                            $full_address = '';
                            if (!empty($listing_city)) {
                                $full_address .= esc_attr($listing_city);
                            }
                            if (!empty($listing_state)) {
                                $full_address .= ', ' . esc_attr($listing_state);
                            }
                            if (!empty($listing_zip)) {
                                $full_address .= ' ' . esc_attr($listing_zip);
                            }

                            echo $full_address;

                            echo '</address>';
                        }
                    }
                    echo '<span>Sleeping Accomodation Available: ' . ($is_accomodation == 'no' ? '<b>NO</b>' : '<b>YES</b>') . '</span></br>';
                    ?>
                </div>
            </div>






        </div>
    </div>
</div><!-- .item-wrap -->