<?php
global $post, $homey_prefix, $homey_local;
$lat     = homey_get_listing_data('geolocation_lat');
$long   = homey_get_listing_data('geolocation_long');
$show_map   = homey_get_listing_data('show_map');
$map_zoom_level = homey_option('singlemap_zoom_level');
$detail_map_pin_type = homey_option('detail_map_pin_type');
$listing_type = wp_get_post_terms($post->ID, 'listing_type', array("fields" => "ids"));

$listing_types = get_the_terms($post->ID, 'listing_type');

$listing_city = homey_get_taxonomy_title($post->ID, 'listing_city');
$listing_state = homey_get_taxonomy_title($post->ID, 'listing_state');
$listing_zip = homey_get_listing_data('zip');

$address = homey_get_listing_data('listing_address');

$listing_id = $post->ID;
$current_user_id = get_current_user_id();
$post_author_id = get_post_field('post_author', $post->ID);

$booking_query = guest_booking_confirmed($listing_id, $current_user_id);

$icon = $retinaIcon = '';
if (!empty($listing_type)) {
    $icon = get_term_meta($listing_type[0], 'homey_marker_icon', true);
    $retinaIcon = get_term_meta($listing_type[0], 'homey_marker_retina_icon', true);
}
//$marker_pin = wp_get_attachment_image_src($icon, 'full' );
$image_id = 5684;
$marker_pin = wp_get_attachment_url($image_id);
$marker_pin_retina = wp_get_attachment_image_src($retinaIcon, 'full');

if ($show_map || $current_user_id == $post_author_id) { ?>
    <div id="map-section" class="map-section">
        <div class="block">
            <div class="block-body">
                <div class="block-left">
                    <?php if (!empty($listing_types)) {
                        foreach ($listing_types as $listing_type) {
                            echo '<h3 class="title"> Where you will ' . esc_attr($listing_type->name) . '</h3>';
                        }
                    } ?>
                </div>
                <div id="homey-single-map"
                    data-zoom="<?php echo intval($map_zoom_level); ?>"
                    data-pin-type="<?php echo esc_attr($detail_map_pin_type); ?>"
                    <?php if (isset($marker_pin)) { ?>
                    data-marker-pin="<?php echo esc_url($marker_pin); ?>"
                    <?php } ?>
                    <?php if (isset($marker_pin_retina[0])) { ?>
                    data-marker-pin-retina="<?php echo esc_url($marker_pin_retina[0]); ?>"
                    <?php } ?>
                    data-lat="<?php echo esc_attr($lat); ?>"
                    data-long="<?php echo esc_attr($long); ?>" class="map-section-map">
                </div>

                <div class="detail-property-address" style="margin-top: 15px;">
                    <?php
                    if ($booking_query->have_posts() || $current_user_id == $post_author_id) {
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
        </div><!-- block -->
    </div>
<?php } ?>