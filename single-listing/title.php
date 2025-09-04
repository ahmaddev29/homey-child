<?php
global $post, $homey_prefix, $listing_author;
$address = homey_get_listing_data('listing_address');

$is_superhost = $listing_author['is_superhost'];

$rating = homey_option('rating');
$total_rating = get_post_meta($post->ID, 'listing_total_rating', true);
$address = get_post_meta($post->ID, $homey_prefix . 'listing_address', true);
$listing_types = get_the_terms($post->ID, 'listing_type');
$subcategory = get_field('field_6479eb70249d7', $post->ID);

$listing_city = homey_get_taxonomy_title($post->ID, 'listing_city');
$listing_state = homey_get_taxonomy_title($post->ID, 'listing_state');
$listing_zip = homey_get_listing_data('zip');

$num_of_review = homey_option('num_of_review');

$args = array(
    'post_type' => 'homey_review',
    'meta_key' => 'reservation_listing_id',
    'meta_value' => $post->ID,
    'posts_per_page' => $num_of_review,
    'post_status' => 'publish'
);

$review_query = new WP_Query($args);
$total_review = $review_query->found_posts;

$listing_id = $post->ID;
$current_user_id = get_current_user_id();

$reservation_query = guest_booking_confirmed($listing_id, $current_user_id);

?>
<div class="title-section">
    <div class="block block-top-title">
        <div class="block-body">
            <?php get_template_part('template-parts/breadcrumb'); ?>
            <h1 class="listing-title"><?php the_title(); ?> <?php homey_listing_featured(get_the_ID()); ?></h1>

            <div class="detail-property-flexbox">
                <div class="detail-property-category" style="width:100%">
                    <?php if (!empty($listing_types)) {
                        foreach ($listing_types as $listing_type) {
                            echo '<strong>Unique Adventure:</strong> ' . esc_attr($listing_type->name);
                            if (!empty($subcategory)) {
                                echo ' - ' . esc_attr($subcategory);
                            }

                        }
                    } ?>
                </div>

                <div class="detail-property-address" style="width:100%">
                    <?php
                    if ($reservation_query->have_posts()) {
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

            <?php if ($rating && ($total_rating != '' && $total_rating != 0)) { ?>
                <div class="list-inline rating hidden-xs span_rate_number_container" style="width:100%;margin-top:15px">
                    <?php echo homey_get_review_stars($total_rating, true, false); ?>
                    <span class="span_rate_number"><span><?php echo $total_rating; ?> - <a
                                href="#reviews-section"><?php echo $total_review; ?>
                                Reviews</a></span></span>
                </div>
            <?php } ?>
        </div><!-- block-body -->
    </div><!-- block -->
</div><!-- title-section -->