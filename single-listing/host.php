<?php
global $post, $homey_prefix, $homey_local, $listing_author;

$is_superhost = $listing_author['is_superhost'];
$doc_verified = $listing_author['doc_verified'];

$reviews = homey_get_host_reviews(get_the_author_meta('ID'));
$hostID = get_the_author_meta('ID');

$username = get_the_author_meta('user_login', $hostID);
$display_name_public = get_the_author_meta('display_name_public', $hostID);
$host_name = empty($display_name_public) ? $username : $display_name_public;

$author_url = get_author_posts_url($hostID);
$verified = is_host_verified($hostID);
?>
<div id="host-section" class="host-section">
    <div class="block">
        <div class="block-head">
            <div class="media">
                <div class="media-left">
                    <?php echo '' . $listing_author['photo']; ?>
                </div>
                <div class="media-body">
                    <h2 class="title">Adventure Hosted by <span><a href="<?php echo esc_url($author_url) ?>"
                                style="color: #262626 !important;text-decoration: underline;"><?php echo esc_attr($host_name); ?></a></span>
                    </h2>

                    <ul class="list-inline profile-host-info">
                        <?php if ($is_superhost) { ?>
                            <li class="super-host-flag"><i class="fa fa-bookmark"></i>
                                <?php esc_html_e('Super Host', 'homey'); ?></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div><!-- block-head -->
        <div class="block-body host-body">
            <div class="row">
                <?php if (!empty($listing_author['languages'])) { ?>
                    <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                        <dl>
                            <dt>Host Languages</dt>
                            <dd><?php echo esc_attr($listing_author['languages']); ?></dd>
                        </dl>
                    </div>
                <?php } ?>
                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                    <dl>
                        <dt><?php echo esc_attr(homey_option('sn_pr_profile_status')); ?></dt>
                        <?php if (user_can(get_the_author_meta('ID'), 'administrator')) { ?>
                            <dd class="text-success"><i class="fa fa-check-circle-o"></i>
                                <?php echo esc_attr(homey_option('sn_pr_verified')); ?></dd>
                            <?php
                        } else {
                            if ($verified) { ?>
                                <dd class="text-success"><i class="fa fa-check-circle-o"></i>
                                    <?php esc_html_e('Verified', 'homey'); ?></dd>
                            <?php } else { ?>
                                <dd class="text-danger"><i class="fa fa-close"></i>
                                    <?php esc_html_e('Not Verified', 'homey'); ?></dd>
                        <?php }
                        } ?>
                    </dl>
                </div>

                <?php if ($reviews['is_host_have_reviews']) { ?>
                    <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                        <dl>
                            <dt><?php echo esc_attr(homey_option('sn_pr_h_rating')); ?></dt>
                            <dd>
                                <div class="rating">
                                    <?php echo '' . $reviews['host_rating']; ?>
                                </div>
                            </dd>
                        </dl>
                    </div>
                <?php } ?>

            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-12">
                    <i class="fa-solid fa-flag"></i>
                    <a href="#" class="report-profile" data-user-id="<?php echo esc_attr($hostID); ?>"
                        style="color: #262626 !important;text-decoration: underline;"><strong>Report
                            this
                            profile</strong></a>
                </div>
            </div>
            <div class="row">
                <div id="report_profile_message"></div>
            </div>
            <!--
            <div class="host-section-buttons">

                <?php if (homey_option('detail_contact_form') != 0 && homey_option('hide-host-contact') != 1) { ?>
                <a href="#" data-toggle="modal" data-target="#modal-contact-host" class="btn btn-grey-outlined btn-half-width"><?php echo esc_attr(homey_option('sn_pr_cont_host')); ?></a>
                <?php } ?>

                <a <?php if (homey_option('hide-host-contact') != 0) {
                        echo 'style="width:100%"';
                    } ?> href="<?php echo esc_url($listing_author['link']); ?>" class="btn btn-grey-outlined btn-half-width">
                    <?php echo esc_attr(homey_option('sn_view_profile')); ?>        
                </a>
            </div> --> <!-- block-body -->
        </div><!-- block-body -->

    </div><!-- block -->
</div><!-- host-section -->