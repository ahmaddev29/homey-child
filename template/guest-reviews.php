<?php
/**
 * Template Name: Guest Reviews
 */
if (!is_user_logged_in() || homey_is_host()) {
    wp_redirect(home_url('/'));
}

get_header();

global $wp_query, $homey_local, $homey_prefix;

$current_user_id = get_current_user_id();

$host_args = array(
    'post_type' => 'homey_review',
    'author'    => $current_user_id,
    'post_status' =>  'publish',
    'posts_per_page' => -1,
    'meta_query'     => array(
        array(
            'key'     => 'service_type',
            'compare' => 'NOT EXISTS',
        ),
    ),
);

$host_reviews_query = new WP_Query( $host_args );

$guided_args = array(
    'post_type' => 'homey_review',
    'author'    => $current_user_id,
    'post_status' =>  'publish',
    'posts_per_page' => -1,
    'meta_query'     => array(
        array(
            'key'     => 'service_type',
            'value' => 'guided',
        ),
    ),
);

$guided_reviews_query = new WP_Query( $guided_args );

?>

<section id="body-area">

    <div class="dashboard-page-title">
        <h1>Reviews</h1>
    </div><!-- .dashboard-page-title -->

    <?php get_template_part('template-parts/dashboard/side-menu'); ?>

    <div class="user-dashboard-right dashboard-without-sidebar">
        <div class="dashboard-content-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="dashboard-area">

                            <div class="host-profile-tabs">

                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs" role="tablist">
                                    <li role="presentation" class="active"><a href="#guest_reviews" aria-controls="guest_reviews" role="tab" data-toggle="tab"><?php echo esc_attr($homey_local['rating_reviews_label']); ?></a></li>
                                    <li role="presentation"><a href="#guest_guided_reviews" aria-controls="guest_guided_reviews" role="tab" data-toggle="tab">Guided Service Reviews</a></li>
                                </ul>

                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane fade in active" id="guest_reviews">
                                        <div class="host-rating-section">
                                            <div class="block">
                                                <div class="block-body">
                                                    <div class="reviews-section">
                                                        <div class="page-wrap">
                                                            <div class="article-main">
                                                                <?php if ( $host_reviews_query->have_posts() ) :
                                                                    while ( $host_reviews_query->have_posts() ): $host_reviews_query->the_post();
                                                                        $meta = get_post_meta( get_the_id() );
                                                                        $listing = get_post( $meta['reservation_listing_id'][0] );
                                                                        $rating = $meta['homey_rating'][0];
                                                                        ?>
                                                                        <div class="resource">
                                                                            <div class="content">
                                                                                <div class="title">
                                                                                    <h3 style="font-weight: bold;">
                                                                                        on
                                                                                        <a href="<?php echo get_the_permalink( $meta['reservation_listing_id'][0] ); ?>">
                                                                                            <?php if ( null !== $listing ) :
                                                                                                echo $listing->post_title;
                                                                                            else :
                                                                                                echo 'Listing Deleted';
                                                                                            endif; ?></a></h3>
                                                                                </div>
                                                                                <span class="rating">
                                                                                    <?php echo homey_get_review_stars($rating, true, true, false); ?>
                                                                                </span>
                                                                                <div class="message-date">
                                                                                    <i class="fa fa-calendar"></i> <?php echo esc_attr( get_the_time( get_option( 'date_format' ) )) ?>
                                                                                    <i class="fa fa-clock-o"></i> <?php echo esc_attr( get_the_time( get_option( 'time_format' ) )) ?>
                                                                                </div>
                                                                                <div>
                                                                                    <?php the_content(); ?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    <?php endwhile;
                                                                    wp_reset_postdata(); ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div><!-- page-wrap -->
                                                    </div><!-- reviews-section -->
                                                </div><!-- block-body -->
                                            </div><!-- block -->
                                        </div><!-- host-rating-section -->
                                    </div>

                                    <div role="tabpanel" class="tab-pane fade" id="guest_guided_reviews">
                                        <div class="host-rating-section">
                                            <div class="block">
                                                <div class="block-body">
                                                    <div class="reviews-section">
                                                        <div class="page-wrap">
                                                            <div class="article-main">
                                                                <?php if ( $guided_reviews_query->have_posts() ) :
                                                                    while ( $guided_reviews_query->have_posts() ): $guided_reviews_query->the_post();
                                                                        $meta = get_post_meta( get_the_id() );
                                                                        $listing = get_post( $meta['reservation_listing_id'][0] );
                                                                        $rating = $meta['homey_rating'][0];
                                                                        ?>
                                                                        <div class="resource">
                                                                            <div class="content">
                                                                                <div class="title">
                                                                                    <h3 style="font-weight: bold;">
                                                                                        on
                                                                                        <a href="<?php echo get_the_permalink( $meta['reservation_listing_id'][0] ); ?>">
                                                                                            <?php if ( null !== $listing ) :
                                                                                                echo $listing->post_title;
                                                                                            else :
                                                                                                echo 'Listing Deleted';
                                                                                            endif; ?></a></h3>
                                                                                </div>
                                                                                <span class="rating">
                                                                                    <?php echo homey_get_review_stars($rating, true, true, false); ?>
                                                                                </span>
                                                                                <div class="message-date">
                                                                                    <i class="fa fa-calendar"></i> <?php echo esc_attr( get_the_time( get_option( 'date_format' ) )) ?>
                                                                                    <i class="fa fa-clock-o"></i> <?php echo esc_attr( get_the_time( get_option( 'time_format' ) )) ?>
                                                                                </div>
                                                                                <div>
                                                                                    <?php the_content(); ?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    <?php endwhile;
                                                                    wp_reset_postdata(); ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div><!-- page-wrap -->
                                                    </div><!-- reviews-section -->
                                                </div><!-- block-body -->
                                            </div><!-- block -->
                                        </div><!-- host-rating-section -->
                                    </div>
                                </div>
                            </div><!-- host-profile-tabs -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>

<?php get_footer();?>
