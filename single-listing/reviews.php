<?php global $post, $homey_local;
$num_of_review = homey_option('num_of_review');
$args = array(
    'post_type' =>
        'homey_review',
    'meta_key' => 'reservation_listing_id',
    'meta_value' => $post->ID,
    'posts_per_page' => $num_of_review,
    'post_status' => 'publish'
);

$review_query = new WP_Query($args);

$total_review = $review_query->found_posts;

$total_pages = $review_query->max_num_pages;

if ($total_review > 1) {
    $review_label = $homey_local['rating_reviews_label'];
} else {
    $review_label = $homey_local['rating_review_label'];
}
?>
<div id="reviews-section" class="reviews-section">
    <div class="sort-wrap clearfix">
        <div class="pull-left" style="display: none;">
            <h2><?php echo intval($total_review) . ' ' . esc_attr($review_label); ?></h2>
        </div>
        <div class="pull-right" style="display: none;">
            <ul class="list-inline">
                <li><strong><?php echo esc_attr($homey_local['sort_by']); ?>:</strong></li>
                <li>
                    <select id="sort_review" class="selectpicker bs-select-hidden" data-live-search-style="begins"
                        data-live-search="false">
                        <option value=""><?php esc_html_e('Default Order', 'homey'); ?></option>
                        <option value="a_date"><?php esc_html_e('Date Old to New', 'homey'); ?></option>
                        <option value="d_date"><?php esc_html_e('Date New to Old', 'homey'); ?></option>
                        <option value="a_rating"><?php esc_html_e('Rating (Low to High)', 'homey'); ?></option>
                        <option value="d_rating"><?php esc_html_e('Rating (High to Low)', 'homey'); ?></option>
                    </select>
                </li>
            </ul>
        </div>
    </div>

    <div class="alert alert-info" role="alert" style="display: none;">
        <i class="fa fa-check-circle-o" aria-hidden="true"></i> <?php echo esc_attr($homey_local['rating_noti']); ?>
    </div>
    <input type="hidden" name="review_listing_id" id="review_listing_id" value="<?php echo intval($post->ID); ?>">
    <input type="hidden" name="review_paged" id="review_paged" value="1">
    <input type="hidden" name="total_pages" id="total_pages" value="<?php echo intval($total_pages); ?>">
    <input type="hidden" name="page_sort" id="page_sort" value="">
    <ul id="homey_reviews" class="list-unstyled">

        <?php
        if ($review_query->have_posts()) {
            while ($review_query->have_posts()):
                $review_query->the_post();
                $review_author = homey_get_author('70', '70', 'img-circle');
                $homey_rating = get_post_meta(get_the_ID(), 'homey_rating', true);
                ?>
                <li id="review-<?php the_ID(); ?>" class="review-block">
                    <div class="media">
                        <div class="media-left">
                            <a class="media-object">
                                <?php echo '' . $review_author['photo']; ?>
                            </a>
                        </div>
                        <div class="media-body media-middle">
                            <div class="msg-user-info">
                                <div class="msg-user-left">
                                    <div>
                                        <strong><?php echo esc_attr($review_author['name']); ?></strong>
                                        <p><?php echo esc_attr($review_author['city']); ?>
                                            <?php echo esc_attr($review_author['state']); ?>
                                        </p>
                                    </div>

                                    <!-- <div class="message-date">
                                            <i class="fa fa-calendar"></i>
                                            <?php esc_attr(the_time(get_option('date_format'))); ?>
                                            <i class="fa fa-clock-o"></i>
                                            <?php esc_attr(the_time(get_option('time_format'))); ?>

                                        </div> -->
                                </div>
                            </div>

                        </div>
                    </div>
                    <span class="rating">
                        <?php echo homey_get_review_stars($homey_rating, true, true, false); ?>
                    </span>
                    <div class="review-rating-content" style="margin-top:5px">
                        <?php
                        $content = get_the_content();
                        $word_limit = 200;
                        $content_words = explode(' ', wp_strip_all_tags($content));

                        if (count($content_words) > $word_limit) {
                            $truncated_content = implode(' ', array_slice($content_words, 0, $word_limit));
                            ?>
                            <span class="review-short-content"><?php echo esc_html($truncated_content); ?>...</span>
                            <span class="review-full-content" style="display:none;"><?php echo wp_kses_post($content); ?></span>
                            <a href="#" class="review-read-more">Read More</a>
                            <a href="#" class="review-read-less" style="display:none;">Read Less</a>
                        <?php } else { ?>
                            <span class="review-full-content"><?php echo wp_kses_post($content); ?></span>
                        <?php } ?>
                    </div>

                </li>
            <?php endwhile;
            wp_reset_postdata(); ?>
        <?php } ?>
    </ul>

    <?php
    if ($total_review > $num_of_review) { ?>
        <nav class="pagination-wrap" aria-label="Page navigation">
            <ul class="pagination">
                <li>
                    <button class="btn btn-primary-outlined" disabled id="review_prev">
                        <span aria-hidden="true">&lt;</span>
                    </button>
                </li>
                <li>
                    <button class="btn btn-primary-outlined" id="review_next">
                        <span aria-hidden="true">&gt;</span>
                    </button>
                </li>
            </ul>
        </nav>
    <?php } ?>
</div><!-- reviews-section -->