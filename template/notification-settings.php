<?php
/**
 * Template Name: Notification Settings
 */
if (!is_user_logged_in()) {
    wp_redirect(home_url('/'));
}

get_header();

$user_id = get_current_user_id();
$notification_settings = get_user_meta($user_id, 'notification_settings', true);
$email_checked = isset($notification_settings['email']) && $notification_settings['email'] ? 'checked' : '';
$sms_checked = isset($notification_settings['sms']) && $notification_settings['sms'] ? 'checked' : '';
?>


<section id="body-area">

    <div class="dashboard-page-title">
        <h1><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
    </div><!-- .dashboard-page-title -->

    <?php get_template_part('template-parts/dashboard/side-menu'); ?>

    <div class="user-dashboard-right dashboard-without-sidebar">
        <div class="dashboard-content-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="dashboard-area">
                            <div id="notification-settings-response"></div>
                            <div class="block">
                                <div class="block-title">
                                    <div class="block-left">
                                        <h2 class="title">Manage</h2>
                                    </div>
                                </div>
                                <div class="block-body">
                                    <div class="notification-settings">
                                        <form id="notification-settings-form">
                                            <div class="notification-setting">
                                                <label>
                                                    <span class="icon-circle"><i class="fas fa-envelope"></i></span>
                                                    <span class="text">Emails</span>
                                                    <input type="checkbox" name="email" value="1" <?php echo $email_checked; ?>>
                                                    <span class="custom-checkbox"></span>
                                                </label>
                                            </div>
                                            <div class="notification-setting">
                                                <label>
                                                    <span class="icon-circle"><i class="fas fa-sms"></i></span>
                                                    <span class="text">Text messages</span>
                                                    <input type="checkbox" name="sms" value="1" <?php echo $sms_checked; ?>>
                                                    <span class="custom-checkbox"></span>
                                                </label>
                                                <span>By opting in to SMS, you agree to receive automated
                                                    messages that include account and booking related notifications.
                                                    Standard messaging and data rates may apply.</span>
                                            </div>
                                            <button type="button" class="btn btn-primary"
                                                id="save-notification-settings">Save Settings<span
                                                    id="notofication-loader" style="display:none;margin-left: 10px;"><i
                                                        class="fa fa-spinner fa-spin"></i></span></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>



<?php get_footer(); ?>