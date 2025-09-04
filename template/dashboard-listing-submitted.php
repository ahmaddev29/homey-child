<?php

/**
 * Template Name: Dashboard Listing Submitted
 */
if (!is_user_logged_in()) {
    wp_redirect(home_url('/'));
}
get_header();

global $current_user, $homey_local;

wp_get_current_user();
$userID = $current_user->ID;

$user_email = $current_user->user_email;
$admin_email =  get_bloginfo('admin_email');
$panel_class = $calendar_link = '';
$dashboard_add_new = homey_get_template_link('template/dashboard-submission.php');

$dashboard = homey_get_template_link('template/dashboard.php');

if (isset($_GET['listing_id']) && !empty($_GET['listing_id'])) {
    $calendar_link  = add_query_arg(array(
        'edit_listing' => $_GET['listing_id'],
        'tab' => 'calendar',
    ), $dashboard_add_new);

    $pricing_link  = add_query_arg(array(
        'edit_listing' => $_GET['listing_id'],
        'tab' => 'pricing',
    ), $dashboard_add_new);

    $upgrade_link  = add_query_arg(array(
        'dpage' => 'upgrade_featured',
        'upgrade_id' => $_GET['listing_id'],
    ), $dashboard);
} else {
}

$update_cal_title = homey_option('update_cal_title');
$update_cal_des = homey_option('update_cal_des');
$custom_prices_title = homey_option('custom_prices_title');
$custom_prices_des = homey_option('custom_prices_des');
$update_featured_title = homey_option('update_featured_title');
$update_featured_des = homey_option('update_featured_des');
$make_featured = homey_option('make_featured');
?>


<section id="body-area">

    <div class="dashboard-page-title">
        <h1><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
    </div><!-- .dashboard-page-title -->

    <?php get_template_part('template-parts/dashboard/side-menu'); ?>

    <div class="user-dashboard-right dashboard-with-sidebar">
        <div class="dashboard-content-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="dashboard-area">
                            <div class="promo-section">
                                <div class="overlay"></div>
                                <div class="content">
                                    <h3>ExpoSure!</h3>
                                    <h1>Promote Your Listing for Only $12</h1>
                                    <p>What you <b>SEE</b> is what you get.</p>
                                    <div class="features">
                                        <div class="feature">
                                            <span>1</span>
                                            OPPORTUNITIES TO MAKE MORE MULA!
                                        </div>
                                        <div class="feature">
                                            <span>2</span>
                                            HELP YOUR LISTING STAND OUT!
                                        </div>
                                        <div class="feature">
                                            <span>3</span>
                                            YOUR LISTING FEATURED ON THE HOMEPAGE!
                                        </div>
                                        <div class="feature">
                                            <span>4</span>
                                            APPEAR AT THE TOP OF RELEVANT SEARCHES
                                        </div>
                                        <div class="feature">
                                            <span>5</span>
                                            FIVE DAYS OF PROMOTION FOR ONLY $2.40/DAY!
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-success alert-dismissible" role="alert" style="margin-top: 20px;">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                <?php echo esc_attr($homey_local['list_submit_msg']); ?>
                            </div>
                            <div class="block">
                                <!--
                                <div class="block-title">
                                    <div class="block-left">
                                        <h2 class="title"><?php echo esc_attr($homey_local['complete_list_label']); ?></h2>
                                    </div>
                                </div>
								-->
                                <div class="block-body">
                                    <!--
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                            <h3><?php echo esc_attr($update_cal_title); ?></h3>
                                            <p><?php echo esc_attr($update_cal_des); ?></p>

                                            <?php if (!empty($calendar_link)) { ?>
                                                <p><a class="btn btn-slim btn-primary" href="<?php echo esc_url($calendar_link); ?>"><?php esc_html_e('Update calendar', 'homey'); ?></a></p>
                                            <?php } ?>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                            <h3><?php echo esc_attr($custom_prices_title); ?></h3>
                                            <p><?php echo esc_attr($custom_prices_des); ?></p>

                                            <?php if (!empty($pricing_link)) { ?>
                                                <p><a class="btn btn-slim btn-primary" href="<?php echo esc_url($pricing_link); ?>"><?php esc_html_e('Setup Custom Prices', 'homey'); ?></a></p>
                                            <?php } ?>
                                        </div>
                                    </div>
                                -->
                                    <?php if ($make_featured != 0) { ?>
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                                <h3><?php echo esc_attr($update_featured_title); ?></h3>
                                                <p>Update your listing as a featured listing.</p>

                                                <?php if (!empty($upgrade_link)) { ?>
                                                    <?php if (homey_is_woocommerce()) { ?>
                                                        <a data-listid="<?php echo intval($_GET['listing_id']); ?>" data-featured="1" class="homey-woocommerce-featured-pay btn btn-secondary btn-slim" href="<?php echo esc_url($upgrade_link); ?>"><?php echo esc_attr($homey_local['upgrade_btn']); ?></a>
                                                    <?php } else { ?>
                                                        <a class="btn btn-slim btn-primary" href="<?php echo esc_url($upgrade_link); ?>"><?php echo esc_attr($homey_local['upgrade_btn']); ?></a>
                                                    <?php } ?>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                                <!-- block-body -->
                            </div>
                            <div class="block">
                                <div class="block-title">
                                    <div class="block-left">
                                        <h2 class="title">FAQ</h2>
                                    </div>
                                </div>
                                <div class="block-body">
                                    <div class="panel-group featured-faq featured-flex" id="accordion">
                                        <div class="first-acc-column">
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" data-target="#collapse1">
                                                    <strong class="panel-title">
                                                        How can I add my listing as Featured?
                                                        <span class="fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse1" class="panel-collapse collapse in">
                                                    <div class="panel-body">Easy. Find the My Listings on your dashboard and select upgrade to featured which listing you want to upgrade or the Featured option is also available at the end of the Add New Listing process. Follow the steps. Make the payment and you are live!</div>
                                                </div>
                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" data-target="#collapse2">
                                                    <strong class="panel-title">
                                                        How many of my listings can I Feature at one time?
                                                        <span class="fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse2" class="panel-collapse collapse">
                                                    <div class="panel-body">Good question! As many as you would like. If the Featured Calendar is full your listing will be next in line from the time of purchase.</div>
                                                </div>
                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" data-target="#collapse3">
                                                    <strong class="panel-title">
                                                        Will my Featured listing be on the homepage?
                                                        <span class="fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse3" class="panel-collapse collapse">
                                                    <div class="panel-body">Yes, of course. One of the benefits of having a listing Featured is your listing will be front row for all to see! The Homepage will have 8 available spots with the option for potential guests to see more featured listings by clicking the “Load More Listings” button which shows up to 20 more featured listings. No worries, if all 28 spots are full the Featured listings will rotate as the browser is refreshed.</div>
                                                </div>
                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" data-target="#collapse4">
                                                    <strong class="panel-title">
                                                        How much does it cost to be listed as Featured?
                                                        <span class="fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse4" class="panel-collapse collapse">
                                                    <div class="panel-body">$12 per listing! That’s only $2.40 per day! Wuwhooo!</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="first-acc-column">
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" data-target="#collapse5">
                                                    <strong class="panel-title">
                                                        How long will my listing be Featured for?
                                                        <span class="fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse5" class="panel-collapse collapse">
                                                    <div class="panel-body">Listings will be Featured for 5 days. </div>
                                                </div>
                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" data-target="#collapse6">
                                                    <strong class="panel-title">
                                                        What if all the Featured spots are occupied?
                                                        <span class="fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse6" class="panel-collapse collapse">
                                                    <div class="panel-body">If all of the 28 Featured listings spot are occupied the built-in calendar will show you when your listing will go live if the payment is made.</div>
                                                </div>
                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" data-target="#collapse7">
                                                    <strong class="panel-title">
                                                        What will happen with my Featured listing after the 5 days?
                                                        <span class="fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse7" class="panel-collapse collapse">
                                                    <div class="panel-body">You will have the option of relisting. Also, if there are still available Featured spots your listing will stay in the Featured rotation until it has been pushed out by newly paid Featured listings!</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- block -->
                        </div>
                        <!-- .dashboard-area -->
                    </div>
                    <!-- col-lg-12 col-md-12 col-sm-12 -->
                </div>
            </div>
            <!-- .container-fluid -->
        </div>
        <!-- .dashboard-content-area -->
        <aside class="dashboard-sidebar">
            <?php get_template_part('template-parts/dashboard/sidebar-listing'); ?>
        </aside>
        <!-- .dashboard-sidebar -->
    </div>

</section><!-- #body-area -->

<style>
    .promo-section {
        position: relative;
        width: 100%;
        height: auto;
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        text-align: center;
        background-image: url("https://backyardlease.flywheelsites.com/wp-content/uploads/2024/08/BYLDSC_4284.jpg");
        background-position: center center;
        background-repeat: no-repeat;
        background-size: cover;
    }

    .promo-section .overlay {
        position: absolute;
        width: 100%;
        height: 100%;
        background-image: url("https://backyardlease.flywheelsites.com/wp-content/uploads/2024/08/darren-lawrence-EpeNGhitrlc-unsplash@2x-scaled.jpg");
        opacity: 1;
        mix-blend-mode: multiply;
        transition: background 0.3s, border-radius 0.3s, opacity 0.3s;
    }

    .promo-section .content {
        position: relative;
        padding: 20px;
        max-width: 90%;
        box-sizing: border-box;
    }

    .promo-section .content h1 {
        font-size: 3em;
        margin: 0 auto 25px;
        max-width: 850px;
    }

    .promo-section .content h3 {
        font-size: 30px;
        font-weight: 600;
    }

    .promo-section .content p {
        font-size: 1.2em;
        margin: 30px 0;
    }

    .promo-section .features {
        display: flex;
        justify-content: center;
        gap: 50px;
        margin-top: 100px;
        padding: 0px 120px 0px 120px;
    }

    .promo-section .features .feature {
        font-weight: bold;
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1 1 200px;
        box-sizing: border-box;
    }

    .promo-section .feature span {
        margin-bottom: 10px;
        color: #D1954C;
        font-size: 30px;
        font-weight: 600;
        padding: 13px 17px;
        background-color: #FFFFFF;
        border-radius: 50px;
        width: 50px;
    }

    /* Media queries for responsiveness */
    @media (max-width: 1024px) {
        .promo-section {
            height: auto;
            padding: 50px 20px;
        }

        .promo-section .content h1 {
            font-size: 2.5em;
        }

        .promo-section .feature span {
            font-size: 24px;
            padding: 10px 15px;
            width: 40px;
        }
    }

    @media (max-width: 768px) {
        .promo-section {
            height: auto;
            padding: 20px 10px;
        }

        .promo-section .features {
            flex-direction: column;
            gap: 30px;
            padding: 0px;
        }

        .promo-section .content h1 {
            font-size: 2em;
        }

        .promo-section .feature span {
            font-size: 20px;
            padding: 8px 12px;
            width: 35px;
        }
    }

    @media (max-width: 480px) {
        .promo-section {
            height: auto;
            padding: 10px 5px;
        }

        .promo-section .features {
            flex-direction: column;
            gap: 20px;
            padding: 0px;
        }

        .promo-section .content h1 {
            font-size: 1.5em;
        }

        .promo-section .feature span {
            font-size: 18px;
            padding: 6px 10px;
            width: 30px;
        }
    }
</style>


<?php get_footer(); ?>