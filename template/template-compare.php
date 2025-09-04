<?php
/**
 * Template Name: Compare
 */
get_header();
global $homey_local, $homey_prefix;

$ids = !empty($_COOKIE['homey_compare_listings']) ? explode(',', $_COOKIE['homey_compare_listings']) : [];

$basic_info_escaped = $list_night_price = $listing_title = $list_bedrooms = $list_guests = $list_beds = $list_baths = $list_type = $list_size = $accommodations_allow = $guided_allow = $pets_allow = $party_allow = $list_address = $city_state_address =
    $show_rating = $host_name = $book_now = '';
$counter   =  0;
$listings  =  array();
$hide_labels = homey_option('show_hide_labels');
?>

<section class="main-content-area listing-page listing-page-full-width">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="page-title">
                    <div class="block-top-title">
                        <h1 class="listing-title">DETAILS</h1>
                    </div><!-- block-top-title -->
                </div><!-- page-title -->
            </div><!-- col-xs-12 col-sm-12 col-md-12 col-lg-12 -->
        </div><!-- .row -->
    </div><!-- .container -->

    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <?php
                if( !empty($ids) ) {
                $args = array(
                    'post_type' => 'listing',
                    'post__in' => $ids,
                    'post_status' => 'publish',
                    'order' => 'ASC',
                    'orderby' => 'post__in'
                );

                //do the query
                $the_query = New WP_Query($args);
                if( $the_query->have_posts() ): while( $the_query->have_posts() ): $the_query->the_post();

                    if( homey_booking_type() == 'per_hour') {
                        $night_price = get_post_meta( get_the_ID(), $homey_prefix.'hour_price', true );
                    } else {
                        $night_price = get_post_meta( get_the_ID(), $homey_prefix.'night_price', true );
                    }
                    
                    $bedrooms     = get_post_meta( get_the_ID(), $homey_prefix.'listing_bedrooms', true );
                    $guests       = get_post_meta( get_the_ID(), $homey_prefix.'guests', true );
                    $beds         = get_post_meta( get_the_ID(), $homey_prefix.'beds', true );
                    $baths        = get_post_meta( get_the_ID(), $homey_prefix.'baths', true );
                    $size         = get_post_meta( get_the_ID(), $homey_prefix.'listing_size', true );
                    $size_unit    = get_post_meta( get_the_ID(), $homey_prefix.'listing_size_unit', true );
                    $listing_type = homey_taxonomy_simple('listing_type');
                    $pets             = homey_get_listing_data('pets');
                    $party            = homey_get_listing_data('party');

                    if($pets != 1) {
                        $pets_allow .= '<td><i class="fa fa-times text-danger"></i></td>';
                    } else {
                        $pets_allow .= '<td><i class="fa fa-check text-success"></i></td>';
                    }

                    if($party != 1) {
                        $party_allow .= '<td><i class="fa fa-times text-danger"></i></td>'; 
                    } else {
                        $party_allow .= '<td><i class="fa fa-check text-success"></i></td>';
                    }

                    $have_sleeping_accommodations = get_field('field_6479eb9f0208c', get_the_ID());
                    if ($have_sleeping_accommodations == 'no' || empty($have_sleeping_accommodations)){
                        $accommodations_allow .= '<td><i class="fa fa-times text-danger"></i></td>';
                    }else{
                        $accommodations_allow .= '<td><i class="fa fa-check text-success"></i></td>';
                    }

                    $have_guided_service = get_field('have_guided_service', get_the_ID());
                    if ($have_guided_service == 'no_guide_needed' || empty($have_guided_service)){
                        $guided_allow .= '<td><i class="fa fa-times text-danger"></i></td>';
                    }else{
                        $guided_allow .= '<td><i class="fa fa-check text-success"></i></td>';
                    }

                    $address        = get_post_meta(get_the_ID(), $homey_prefix . 'listing_address', true);
                    $listing_city   = homey_get_taxonomy_title(get_the_ID(), 'listing_city');
                    $listing_state  = homey_get_taxonomy_title(get_the_ID(), 'listing_state');
                    $listing_zip    = homey_get_listing_data('zip');

                    if( !empty($address) ) {
                        $list_address .= '<td>'.esc_html($address).'</td>';
                    } else {
                        $list_address .= '<td>---</td>';
                    }

                    if (!empty($listing_city) || !empty($listing_state) || !empty($listing_zip)) {
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
                    }

                    if( !empty($full_address) ) {
                        $city_state_address .= '<td>'.$full_address.'</td>';
                    } else {
                        $city_state_address .= '<td>---</td>';
                    }

                    $listing_id = get_the_ID();
                    $current_user_id = get_current_user_id();
                    $reservation_query = guest_booking_confirmed($listing_id, $current_user_id);

                    $rating = homey_option('rating');
                    $total_rating = get_post_meta( get_the_ID(), 'listing_total_rating', true );
                    $num_of_review = homey_option('num_of_review');

                    $args = array(
                        'post_type' =>  'homey_review',
                        'meta_key' => 'reservation_listing_id',
                        'meta_value' => $listing_id,
                        'posts_per_page' => $num_of_review,
                        'post_status' =>  'publish'
                    );

                    $review_query = new WP_Query($args);
                    $total_review = $review_query->found_posts;

                    if($rating && ($total_rating != '' && $total_rating != 0 ) ) {
                        $listing_rating = '';
                        $listing_rating .= '<span class="fa fa-star" style="font-size: 14px !important; margin-right: 5px;"></span>';
                        $listing_rating .= ' ' . $total_rating;
                        $listing_rating .= ' - ' . $total_review . ' Reviews';
                    } else {
                        $listing_rating = 'No Review yet';
                    }

                    $show_rating .= '<td>'.$listing_rating.'</td>';
                    
                    $hostID = get_the_author_meta( 'ID' );
                    $display_name_public = get_the_author_meta( 'display_name_public' , $hostID );
                    $author_url = get_author_posts_url($hostID);
                    $profile_picture_id = get_user_meta($hostID, 'homey_author_picture_id', true);
                    $profile_picture_url = wp_get_attachment_image_src($profile_picture_id, 'thumbnail');

                    if( !empty($display_name_public) ) {
                        $host_name .= '<td><img src="' . esc_url($profile_picture_url[0]) . '" alt="' . esc_attr($display_name_public) . '" style="width: 28px; height: 28px; border-radius: 50%; margin-right: 5px;">';
                        $host_name .= '<a href="' . esc_url($author_url) . '" class="host-url">' . esc_html($display_name_public) . '</a></td>';
                    } else {
                        $host_name .= '<td>---</td>';
                    } 

                    $book_now .= '<td><a href="'.get_permalink().'" class="btn-list-compare">Book Now</a></td>';

                    $basic_info_escaped .= '
                            <th><a href="'.esc_url(get_permalink()).'" class="compare-img">'.get_the_post_thumbnail( get_the_id(), 'homey-listing-thumb', array( 'class' => 'img-responsive' ) ).'</a></th>';

                    
                    $listing_title .= '<td>' . esc_html(get_the_title()) . '</td>';
                    
                    if( !empty($night_price) ) {

                        if( homey_booking_type() == 'per_hour') {
                            $list_night_price .= '<td>'.homey_formatted_price($night_price, false, true).'/'.homey_option('glc_hour_label').'</td>';
                        } else {
                            $list_night_price .= '<td>'.homey_formatted_price($night_price, false, true).'/'.homey_option('glc_day_night_label').'</td>';
                        }
                        
                    } else {
                        $list_night_price .= '<td>---</td>';
                    }

                    if( !empty($bedrooms) ) {
                        $list_bedrooms .= '<td>'.$bedrooms.'</td>';
                    } else {
                        $list_bedrooms .= '<td>---</td>';
                    }            

                    if( !empty($guests) ) {
                        $list_guests .= '<td>'.$guests.'</td>';
                    } else {
                        $list_guests .= '<td>---</td>';
                    }

                    if( !empty($beds) ) {
                        $list_beds .= '<td>'.$beds.'</td>';
                    } else {
                        $list_beds .= '<td>---</td>';
                    }

                    if( !empty($baths) ) {
                        $list_baths .= '<td>'.$baths.'</td>';
                    } else {
                        $list_baths .= '<td>---</td>';
                    }

                    if( !empty($size) ) {
                        $list_size .= '<td>'.$size.' '.$size_unit.'</td>';
                    } else {
                        $list_size .= '<td>---</td>';
                    }

                    if( !empty($listing_type) ) {
                        $list_type .= '<td>'.$listing_type.'</td>';
                    } else {
                        $list_type .= '<td>---</td>';
                    }

                    $counter++;

                endwhile; endif; wp_reset_postdata();
                ?>

                <div class="compare-table">
                    <table class="table-striped table-hover">
                        <thead>
                            <tr>
                                <th><!-- empty --></th>
                                <?php 
                                echo $basic_info_escaped; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong><?php esc_html_e('Title', 'homey'); ?></strong></td>
                                <?php echo wp_kses($listing_title, homey_allowed_html()); ?>
                            </tr>
                            
                            <tr>
                                <td><strong><?php esc_html_e('Price', 'homey'); ?></strong></td>
                                <?php echo wp_kses($list_night_price, homey_allowed_html()); ?>
                            </tr>
                            
                            <?php if($hide_labels['sn_type_label'] != 1) { ?>
                            <tr>
                                <td><strong>Category</strong></td>
                                <?php echo wp_kses($list_type, homey_allowed_html()); ?> 
                            </tr>
                            <?php } ?>
                            
                            <tr>
                                <td><strong>Guests</strong></td>
                                <?php echo wp_kses($list_guests, homey_allowed_html()); ?>                               
                            </tr>

                            <tr>
                                <td><strong>Location</strong></td>
                                <?php 
                                if ($reservation_query->have_posts()) { 
                                    echo wp_kses($list_address, homey_allowed_html());
                                } else {
                                    echo wp_kses($city_state_address, homey_allowed_html());
                                } ?>                              
                            </tr>

                            <tr>
                                <td><strong>Reviews</strong></td>
                                <?php echo $show_rating; ?>                               
                            </tr>

                            <tr>
                                <td><strong>Hosted By</strong></td>
                                <?php echo $host_name; ?>                               
                            </tr>

                            <tr>
                                <td><strong>Sleeping Accomodation</strong></td>
                                <?php echo $accommodations_allow; ?>                               
                            </tr>

                            <tr>
                                <td><strong>Guided Service</strong></td>
                                <?php echo $guided_allow ?>                               
                            </tr>

                            <tr>
                                <td><strong>Parties Allowed</strong></td>
                                <?php echo $party_allow ?>                               
                            </tr>

                            <tr>
                                <td><strong>Pets Allowed</strong></td>
                                <?php echo $pets_allow ?>                               
                            </tr>
                            <tr>
                                <td><strong>Actions</strong></td>
                                   <?php echo $book_now ?>                         
                            </tr>
                            
                        </tbody>
                    </table>
                </div>

                <?php } ?>

            </div><!-- col-xs-12 col-sm-12 col-md-8 col-lg-8 -->
        </div><!-- .row -->
    </div>   <!-- .container -->
    
    
</section><!-- main-content-area listing-page grid-listing-page -->


<?php get_footer(); ?>
