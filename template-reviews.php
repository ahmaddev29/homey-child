<?php
/**
 * Template Name: Reviews
**/

get_header();
global $post;

$sidebar_meta = homey_get_sidebar_meta($post->ID);
$sticky_sidebar = homey_option('sticky_sidebar');

if($sidebar_meta['homey_sidebar'] != 'yes') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-12 col-lg-12';

} elseif($sidebar_meta['homey_sidebar'] == 'yes' && $sidebar_meta['sidebar_position'] == 'right') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-8 col-lg-8';
    $sidebar_classes = 'col-xs-12 col-sm-12 col-md-4 col-lg-4';

} elseif($sidebar_meta['homey_sidebar'] == 'yes' && $sidebar_meta['sidebar_position'] == 'left') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-8 col-lg-8 col-md-push-4 col-lg-push-4';
    $sidebar_classes = 'col-xs-12 col-sm-12 col-md-4 col-lg-4 col-md-pull-8 col-lg-pull-8';
}

$reviews = get_reviews_query(10);

?>

<section class="main-content-area">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
                <div class="page-title">
                    <div class="block-top-title">
                        <?php get_template_part('template-parts/breadcrumb'); ?>
                        <h1 class="listing-title"><?php the_title(); ?></h1>
                    </div><!-- block-top-title -->
                </div><!-- page-title -->
            </div>
        </div><!-- .row -->
    </div><!-- .container -->

    <div class="container">
        <div class="row">
            <div class="<?php echo esc_attr($content_classes); ?>">

                <div class="page-wrap">
                    <div class="article-main"><?php
                    if ( $reviews->have_posts() ) :
                      while ( $reviews->have_posts() ): $reviews->the_post();
                      $meta = get_post_meta( get_the_id() );
                      $listing = get_post( $meta['reservation_listing_id'][0] );
                      $rating = $meta['homey_rating'][0];
                      ?>
                        <div class="resource">
                          <div class="content">
                            <div class="title">
                              <a href="<?php echo get_the_permalink( $meta['reservation_listing_id'][0] ); ?>">
                                 <h3 style="font-weight: bold;"><?php
                                if ( null !== $listing ) :
                                  echo $listing->post_title;
                                else :
                                   echo 'Listing Deleted';
                                endif; ?></h3>
                              </a>
                              <?php echo homey_get_review_stars( $rating, false, false); ?>
                            </div>
                            <div>
                              <?php the_content(); ?>
                            </div>
                          </div>

                        </div><?php
                      endwhile;
                      wp_reset_postdata(); ?><?php
                    endif;
						?>
                    </div>
                </div><!-- grid-listing-page -->
                <?php homey_pagination( $reviews->max_num_pages ); ?>
            </div>

        </div><!-- .row -->
    </div>   <!-- .container -->


</section><!-- main-content-area listing-page grid-listing-page -->
<?php get_footer(); ?>