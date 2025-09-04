<?php
global $wp_query, $homey_local, $homey_prefix;
$current_author = $wp_query->get_queried_object();
$author_id = $current_author->ID;
$author_meta = get_user_meta($author_id);
$display_name_public = get_the_author_meta('display_name_public_guest', $author_id);
$user_meta = homey_get_author_by_id('100', '100', 'img-circle', $author_id);

$native_language_guest = get_the_author_meta($homey_prefix . 'native_language_guest', $author_id);
$other_language_guest = get_the_author_meta($homey_prefix . 'other_language_guest', $author_id);

$phone_number = get_the_author_meta('homey_phone_number', $author_id);

$author = homey_get_author_by_id('70', '70', 'img-circle media-object avatar', $author_id);
$facebook = $author['facebook'];
$twitter = $author['twitter'];
$linkedin = $author['linkedin'];
$pinterest = $author['pinterest'];
$instagram = $author['instagram'];
$googleplus = $author['googleplus'];
$youtube = $author['youtube'];
$vimeo = $author['vimeo'];
$doc_verified = $author['doc_verified'];

$state = get_the_author_meta($homey_prefix . 'state_guest', $author_id);
$city = get_the_author_meta($homey_prefix . 'city_guest', $author_id);
$bio = get_the_author_meta('bio_guest', $author_id);
$work_place = get_the_author_meta('work_place_guest', $author_id);


// Emergency Contact
$em_contact_name = $user_meta['em_contact_name_guest'];
$em_relationship = $user_meta['em_relationship_guest'];
$em_email = $user_meta['em_email_guest'];
$em_phone = $user_meta['em_phone_guest'];

$show_social = true;
if (empty($facebook) && empty($twitter) && empty($linkedin) && empty($pinterest) && empty($instagram) && empty($googleplus) && empty($youtube) && empty($vimeo)) {
    $show_social = false;
}

$verified = is_guest_verified($author_id);

$current_page_user = homey_user_role_by_user_id($author_id);

$reviews = homey_get_host_reviews($author_id);
$guide_reviews = homey_get_host_guide_reviews($author_id);

$host_email = is_email($author['email']);

$enable_forms_gdpr = homey_option('enable_forms_gdpr');
$forms_gdpr_text = homey_option('forms_gdpr_text');
$form_type = homey_option('form_type');
$host_profile_contact = homey_option('host_profile_contact');
$hide_host_contact = homey_option('hide-host-contact');

$is_superhost = $author['is_superhost'];

if ($hide_host_contact == 1) {
    $con_classes = 'col-xs-12 col-sm-12 col-md-12 col-lg-12';
} else {
    $con_classes = 'col-xs-12 col-sm-12 col-md-12 col-lg-12';
}

?>

<section class="main-content-area user-profile host-profile">
    <div class="container">
        <div class="host-section clearfix">
            <div class="row">
                <div class="<?php echo esc_attr($con_classes); ?>">
                    <div class="block" <?php if ($hide_host_contact == 1) {
                                            echo 'style="min-height:auto"';
                                        } ?>>
                        <div class="block-head">
                            <div class="media">
                                <div class="media-left">
                                    <?php echo '' . $author['photo']; ?>
                                </div>
                                <div class="media-body">
                                    <h2 class="title"><span><?php echo esc_attr($homey_local['pr_iam']); ?></span>
                                        <?php echo esc_attr($display_name_public); ?></h2>


                                    <ul class="list-inline profile-host-info">
                                        <?php if ($is_superhost) { ?>
                                            <li class="super-host-flag"><i class="fa fa-bookmark"></i>
                                                <?php esc_html_e('Super Host', 'homey'); ?></li>
                                        <?php } ?>

                                        <?php if (!empty($city) && !empty($state)) { ?>
                                            <li>
                                                <address><i class="fa fa-map-marker" aria-hidden="true"></i>
                                                    <?php echo esc_attr($city); ?>, <?php echo esc_attr($state); ?>
                                                </address>
                                            </li>
                                        <?php } ?>
                                    </ul>

                                </div>
                            </div>
                        </div><!-- block-head -->
                        <div class="block-body">
                            <?php if (!empty($bio)) { ?>
                                <div class="row" style="margin-bottom: 10px;">
                                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                        <dl>
                                            <dt>Bio</dt>
                                            <dd><?php echo '' . ($bio); ?></dd>
                                        </dl>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($show_social && $hide_host_contact != 1) { ?>
                                <div class="profile-social-icons">
                                    <?php if ($hide_host_contact != 1) { ?>
                                        <?php echo esc_attr($homey_local['pr_followme']); ?>:
                                        <?php if (!empty($facebook)) { ?>
                                            <a class="btn-facebook" href="<?php echo esc_url($facebook); ?>"><i
                                                    class="fa fa-facebook"></i></a>
                                        <?php } ?>

                                        <?php if (!empty($twitter)) { ?>
                                            <a class="btn-twitter" href="<?php echo esc_url($twitter); ?>"><i
                                                    class="fa fa-twitter"></i></a>
                                        <?php } ?>

                                        <?php if (!empty($googleplus)) { ?>
                                            <a class="btn-google" href="<?php echo esc_url($googleplus); ?>"><i
                                                    class="fa fa-google"></i></a>
                                        <?php } ?>

                                        <?php if (!empty($instagram)) { ?>
                                            <a class="btn-instagram" href="<?php echo esc_url($instagram); ?>"><i
                                                    class="fa fa-instagram"></i></a>
                                        <?php } ?>

                                        <?php if (!empty($pinterest)) { ?>
                                            <a class="btn-pinterest" href="<?php echo esc_url($pinterest); ?>"><i
                                                    class="fa fa-pinterest"></i></a>
                                        <?php } ?>

                                        <?php if (!empty($linkedin)) { ?>
                                            <a class="btn-linkedin" href="<?php echo esc_url($linkedin); ?>"><i
                                                    class="fa fa-linkedin"></i></a>
                                        <?php } ?>

                                        <?php if (!empty($youtube)) { ?>
                                            <a class="btn-youtube" href="<?php echo esc_url($youtube); ?>"><i
                                                    class="fa fa-youtube"></i></a>
                                        <?php } ?>

                                        <?php if (!empty($vimeo)) { ?>
                                            <a class="btn-vimeo" href="<?php echo esc_url($vimeo); ?>"><i
                                                    class="fa fa-vimeo"></i></a>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            <?php } ?>


                            <div class="row">
                                <?php if (!empty($work_place)) { ?>
                                    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                                        <dl>
                                            <dt>Where I work</dt>
                                            <dd><?php echo esc_html($work_place); ?></dd>
                                        </dl>
                                    </div>
                                <?php } ?>

                                <?php if (!empty($author['languages'])) { ?>
                                    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                                        <dl>
                                            <dt>Guest Languages</dt>
                                            <dd><?php echo esc_attr($native_language_guest); ?>, <?php echo esc_attr($other_language_guest); ?></dd>
                                        </dl>
                                    </div>
                                <?php } ?>
                                <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                                    <dl>

                                        <dt><?php echo esc_attr($homey_local['pr_profile_status']); ?> </dt>

                                        <?php
                                        if ($current_page_user == 'administrator') { ?>

                                            <dd class="text-success">
                                                <i class="fa fa-check-circle-o"></i>
                                                <?php echo esc_attr($homey_local['pr_verified']); ?>
                                            </dd>

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
                                <?php if (homey_is_admin() && !empty($phone_number)) { ?>
                                    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                                        <dl>
                                            <dt>Phone Number</dt>
                                            <dd><?php echo esc_html($phone_number); ?></dd>
                                        </dl>
                                    </div>
                                <?php } ?>
                            </div>
                        </div><!-- block-body -->
                    </div><!-- block -->
                    <?php if (homey_is_admin()) { ?>
                        <!--zahid.k-->
                        <div class="block">
                            <div class="block-title">
                                <h2 class="title"><?php esc_html_e('Emergency Contact', 'homey'); ?></h2>
                            </div>
                            <div class="block-body">
                                <ul class="list-unstyled list-lined">
                                    <li>
                                        <strong><?php esc_html_e('Contact Name', 'homey'); ?></strong>
                                        <?php
                                        if (!empty($em_contact_name)) {
                                            echo esc_attr($em_contact_name);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </li>
                                    <li>
                                        <strong><?php esc_html_e('Relationship', 'homey'); ?></strong>
                                        <?php
                                        if (!empty($em_relationship)) {
                                            echo esc_attr($em_relationship);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </li>
                                </ul>
                                <ul class="list-unstyled list-lined mb-0">
                                    <li>
                                        <strong><?php esc_html_e('Phone Number', 'homey'); ?></strong>
                                        <?php
                                        if (!empty($em_phone)) {
                                            echo esc_attr($em_phone);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </li>
                                    <li>
                                        <strong><?php esc_html_e('Email', 'homey'); ?></strong>
                                        <?php
                                        if (!empty($em_email)) {
                                            echo esc_attr($em_email);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!--zahid.k-->
                    <?php } ?>
                </div><!-- col-xs-12 col-sm-12 col-md-8 col-lg-8 -->
            </div>
        </div><!-- host-section -->
    </div>
</section><!-- main-content-area -->