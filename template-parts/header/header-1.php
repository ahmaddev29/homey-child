<?php
global $homey_local;

$enable_search = homey_option('enable_search');
$search_position = homey_option('search_position');
$search_pages = homey_option('search_pages');
$search_selected_pages = homey_option('search_selected_pages');

$user_id = get_current_user_id();
$unread_notifications = get_unread_notifications($user_id);
$all_notifications = get_all_notifications($user_id);

if (isset($_GET['search_position'])) {
    $search_position = $_GET['search_position'];
}

$splash_page_nav = homey_option('splash_page_nav');
$menu_sticky = homey_option('menu-sticky');

if (homey_is_dashboard()) { ?>
    <div class="header-dashboard no-cache-<?php echo strtotime("now"); ?>">
    <?php } ?>

    <div class="nav-area header-type-1 <?php if (is_front_page()) : homey_transparent();
                                        endif; ?> no-cache-<?php echo strtotime("now"); ?>">
        <div class="<?php if (! is_front_page()) : ?> white-bg <?php endif; ?>">
            <!-- top bar -->
            <?php
            if (homey_topbar_needed()) {
                get_template_part('template-parts/header/top-bar');
            }
            ?>

            <!-- desktop nav -->
            <header id="homey_nav_sticky" class="header-nav hidden-sm hidden-xs no-cache-<?php echo strtotime("now"); ?>" data-sticky="<?php echo esc_attr($menu_sticky); ?>">
                <div class="<?php homey_header_container(); ?>">
                    <div class="header-inner table-block">
                        <div class="header-comp-logo">
                            <?php get_template_part('template-parts/header/logo'); ?>
                        </div>

                        <div class="header-comp-nav <?php homey_header_menu_align(); ?>">
                            <?php if (!homey_is_splash() || $splash_page_nav != 0) { ?>
                                <?php get_template_part('template-parts/header/main-nav'); ?>
                            <?php } ?>
                        </div>

                        <?php if (is_user_logged_in()) { ?>
                            <div class="notification-icon">
                                <span class="unread-count"><?php echo count($unread_notifications); ?></span>
                                <i class="fa fa-bell"></i>
                            </div>

                            <!-- Add this HTML for the notification box -->
                            <div class="notification-box">
                                <div class="notification-header">
                                    <span class="notification-title">Notifications: <span class="unread-count"><?php echo count($unread_notifications); ?></span></span>
                                    <span class="close-btn">&times;</span>
                                </div>
                                <div class="notification-list">
                                    <?php
                                    foreach ($all_notifications as $notification) {
                                        $notificationId = $notification->ID;
                                        $notification_link = get_post_meta($notificationId, '_notification_link', true);
                                        $statusClass = get_post_meta($notificationId, '_notification_status', true) === 'read' ? 'read' : '';
                                        echo '<div class="notification-item ' . esc_attr($statusClass) . '" data-notification-id="' . esc_attr($notificationId) . '">';
                                        if (empty($notification_link)) {
                                            echo '<span class="notification-title">' . esc_html($notification->post_title) . '</span>';
                                        } else {
                                            echo '<a href="' . esc_html($notification_link) . '" style="color: #262626;"><span class="notification-title">' . esc_html($notification->post_title) . '</span></a>';
                                        }
                                        echo '<span class="close-icon">&times;</span>';
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                            </div>

                        <?php } ?>

                        <?php if (class_exists('Homey_login_register')): ?>
                            <div class="header-comp-right no-cache-<?php echo strtotime("now"); ?>">
                                <?php
                                if (homey_is_login_register()) {
                                    if (is_user_logged_in()) {
                                        get_template_part('template-parts/header/account');
                                    } else {
                                        get_template_part('template-parts/header/login-register-v1');
                                    }
                                } else {
                                    get_template_part('template-parts/header/social');
                                }
                                ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </header>
            <!-- mobile header -->
            <?php get_template_part('template-parts/header/header-mobile'); ?>

            <?php
            if (!homey_is_dashboard()) {

                if (homey_search_needed()) {

                    if (!is_home() && !is_singular('post')) {
                        if ($enable_search != 0 && $search_position == 'under_nav') {
                            if ($search_pages == 'only_home') {
                                if (is_front_page()) {
                                    get_template_part('template-parts/search/main-search');
                                }
                            } elseif ($search_pages == 'all_pages') {
                                get_template_part('template-parts/search/main-search');
                            } elseif ($search_pages == 'only_innerpages') {
                                if (!is_front_page()) {
                                    get_template_part('template-parts/search/main-search');
                                }
                            } else if ($search_pages == 'specific_pages') {
                                if (is_page($search_selected_pages)) {
                                    get_template_part('template-parts/search/main-search');
                                }
                            } else if ($search_pages == 'only_taxonomy_pages') {
                                if (is_tax()) {
                                    get_template_part('template-parts/search/main-search');
                                }
                            }
                        }
                    }
                } //homey_search_needed
            } //homey_is_dashboard
            ?>
        </div>
    </div>

    <?php if (homey_is_dashboard()) { ?>
    </div>
<?php } ?>

<script>
    jQuery(document).ready(function($) {
        var $notificationIcon = $('.notification-icon');
        var $notificationBox = $('.notification-box');

        // Click event for the notification icon
        $notificationIcon.on('click', function() {
            $notificationBox.slideToggle();
        });

        // Click event for closing the notification box
        $notificationBox.find('.close-btn').on('click', function() {
            $notificationBox.slideUp();
        });

        // AJAX request to mark a notification as read when clicked
        $notificationBox.find('.notification-item').on('click', function() {
            var notificationId = $(this).data('notification-id');
            var $notificationItem = $(this);

            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                data: {
                    action: 'mark_notification_as_read',
                    notification_id: notificationId,
                },
                success: function(response) {
                    //$(this).remove();
                    $notificationItem.addClass('read');
                }
            });
        });

        // Click event for closing individual notifications
        $notificationBox.find('.notification-item .close-icon').on('click', function(e) {
            e.stopPropagation();

            var $notificationItem = $(this).closest('.notification-item');
            var notificationId = $notificationItem.data('notification-id');

            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                data: {
                    action: 'remove_notification',
                    notification_id: notificationId,
                },
                success: function(response) {
                    $notificationItem.remove();
                }
            });
        });

    });
</script>