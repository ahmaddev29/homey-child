<?php
global $post, $homey_prefix, $homey_local;
$video_url = homey_get_listing_data('video_url');
$appove_video = get_field('approve_video');
$current_user_id = get_current_user_id();
$post_author_id = get_post_field('post_author', $post->ID);

if ((!empty($video_url) && 'Approved' == $appove_video) || (!empty($video_url) && $current_user_id == $post_author_id)) {
?>
    <div id="video-section" class="video-section">
        <div class="block">
            <div class="block-section">
                <div class="block-body">
                    <div>
                        <!-- <h3 class="title"><?php echo esc_attr(homey_option('sn_video_heading')); ?></h3> -->
                    </div><!-- block-left -->
                    <div>
                        <div class="block-video">
                            <?php echo wp_oembed_get($video_url); ?>
                        </div>
                    </div><!-- block-right -->
                </div><!-- block-body -->
            </div><!-- block-section -->
        </div><!-- block -->
    </div>
<?php } ?>