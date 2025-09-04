<?php $copy_rights = homey_option('copy_rights');
global $homey_local;
$footer_cols = homey_option('footer_cols');
$custom_logo = homey_option('custom_logo', false, 'url');
$splash_logo = homey_option(
    'custom_logo_splash',
    false,
    'url'
);
if (homey_is_transparent_logo()) {
    $custom_logo = $splash_logo;
} ?>
<footer class="footer-wrap footer">
    <?php
    if (
        is_active_sidebar('footer-sidebar-1')
        || is_active_sidebar('footer-sidebar-2')
        || is_active_sidebar('footer-sidebar-3')
        || is_active_sidebar('footer-sidebar-4')
    ) { ?>

        <div class="footer-top-wrap">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-sm-12 col-xs-12">
                        <a class="homey_logo" href="<?php echo esc_url(home_url('/')); ?>">
                            <?php if (!empty($splash_logo)) { ?>
                                <img src="<?php echo esc_url($splash_logo); ?>" alt="<?php bloginfo('name'); ?>"
                                    title="<?php bloginfo('name'); ?> - <?php bloginfo('description'); ?>">
                            <?php } else {
                                echo '<h1>';
                                bloginfo('name');
                                echo '</h1>';
                            } ?>
                        </a>
                    </div>
                    <div class="col-md-9 col-sm-12 col-xs-12">
                        <nav class="bottom-navi">
                            <?php
                            // Pages Menu
                            if (has_nav_menu('main-menu')):
                                wp_nav_menu(
                                    array(
                                        'theme_location' => 'main-menu',
                                        'container' => '',
                                        'container_class' => '',
                                        'menu_class' => 'main-menu',
                                        'menu_id' => 'main-menu',
                                        'depth' => 4
                                    )
                                );
                            endif;
                            ?>
                        </nav>
                    </div>
                </div>
                <div class="row" style="margin-top: 75px;">
                    <?php
                    if ($footer_cols === 'one_col') {
                        if (is_active_sidebar('footer-sidebar-1')) {
                            echo '<div class="col-md-12 col-sm-12">';
                            dynamic_sidebar('footer-sidebar-1');
                            echo '</div>';
                        }
                    } elseif ($footer_cols === 'two_col') {
                        if (is_active_sidebar('footer-sidebar-1')) {
                            echo '<div class="col-md-6 col-sm-12">';
                            dynamic_sidebar('footer-sidebar-1');
                            echo '</div>';
                        }
                        if (is_active_sidebar('footer-sidebar-2')) {
                            echo '<div class="col-md-6 col-sm-12">';
                            dynamic_sidebar('footer-sidebar-2');
                            echo '</div>';
                        }
                    } elseif ($footer_cols === 'three_cols_middle') {
                        if (is_active_sidebar('footer-sidebar-1')) {
                            echo '<div class="col-md-4 col-sm-12 col-xs-12">';
                            dynamic_sidebar('footer-sidebar-1');
                            echo '</div>';
                        }
                        if (is_active_sidebar('footer-sidebar-2')) {
                            echo '<div class="col-md-4 col-sm-12 col-xs-12">';
                            dynamic_sidebar('footer-sidebar-2');
                            echo '</div>';
                        }
                        if (is_active_sidebar('footer-sidebar-3')) {
                            echo '<div class="col-md-4 col-sm-12 col-xs-12">';
                            dynamic_sidebar('footer-sidebar-3');
                            echo '</div>';
                        }
                    } elseif ($footer_cols === 'three_cols') {
                        if (is_active_sidebar('footer-sidebar-1')) {
                            echo '<div class="col-md-6 col-sm-12 col-xs-12">';
                            dynamic_sidebar('footer-sidebar-1');
                            echo '</div>';
                        }
                        if (is_active_sidebar('footer-sidebar-2')) {
                            echo '<div class="col-md-3 col-sm-12 col-xs-12">';
                            dynamic_sidebar('footer-sidebar-2');
                            echo '</div>';
                        }
                        if (is_active_sidebar('footer-sidebar-3')) {
                            echo '<div class="col-md-3 col-sm-12 col-xs-12">';
                            dynamic_sidebar('footer-sidebar-3');
                            echo '</div>';
                        }
                    } elseif ($footer_cols === 'four_cols') {
                        if (is_active_sidebar('footer-sidebar-1')) {
                            echo '<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">';
                            dynamic_sidebar('footer-sidebar-1');
                            echo '</div>';
                        }
                        if (is_active_sidebar('footer-sidebar-2')) {
                            echo '<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">';
                            dynamic_sidebar('footer-sidebar-2');
                            echo '</div>';
                        }
                        if (is_active_sidebar('footer-sidebar-3')) {
                            echo '<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">';
                            dynamic_sidebar('footer-sidebar-3');
                            echo '</div>';
                        }
                        if (is_active_sidebar('footer-sidebar-4')) {
                            echo '<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">';
                            dynamic_sidebar('footer-sidebar-4');
                            echo '</div>';
                        }
                    }
                    ?>
                </div><!-- row -->
            </div><!-- container -->
        </div><!-- footer-top-wrap -->
    <?php } ?>

    <?php if (homey_option('social-footer') != '0' || !empty(trim($copy_rights))) { ?>
        <div class="footer-bottom-wrap">
            <div class="container">
                <div class="row">
                    <?php if (homey_option('social-footer') != '0') { ?>
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                        <?php } else { ?>
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <?php } ?>
                            <div class="footer-copyright">
                                <?php echo esc_html($copy_rights); ?>
                            </div>
                        </div><!-- col-xs-12 col-sm-6 col-md-6 col-lg-6 -->
                        <?php if (homey_option('social-footer') != '0') { ?>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="social-footer">
                                    <?php get_template_part('template-parts/footer/social'); ?>
                                </div>
                            </div><!-- col-xs-12 col-sm-6 col-md-6 col-lg-6 -->
                        <?php } ?>
                    </div><!-- row -->
                </div><!-- container -->
            </div><!-- footer-bottom-wrap -->
        <?php } ?>
</footer><!-- footer-wrap -->