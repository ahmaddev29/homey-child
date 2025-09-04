<?php

/**
 * Template Name: Create New Coupon
 */
if (!is_user_logged_in() || homey_is_renter()) {
    wp_redirect(home_url('/'));
}

get_header();
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
                            <div class="block">
                                <div class="block-title">
                                    <div class="block-left">
                                        <h2 class="title">Create New Coupon (The host can only provide coupon discounts to guests who are registered on the Backyard Lease platform.)</h2>
                                    </div>
                                </div>
                                <div class="block-body">
                                    <?php if (isset($_POST['create_coupon'])): ?>
                                        <?php
                                        $title = sanitize_text_field($_POST['coupon_name']);
                                        $discount = intval($_POST['coupon_discount']);
                                        $guests = isset($_POST['coupon_guests']) ? $_POST['coupon_guests'] : array();

                                        $coupon_post = array(
                                            'post_title' => $title,
                                            'post_type' => 'host_coupon',
                                            'post_status' => 'publish',
                                        );

                                        $coupon_id = wp_insert_post($coupon_post);

                                        if ($coupon_id) {
                                            update_field('coupon_name', $title, $coupon_id);
                                            update_field('coupon_discount', $discount, $coupon_id);
                                            update_field('coupon_guests', $guests, $coupon_id);
                                            update_post_meta($coupon_id, 'coupon_guests_ids', $guests);
                                            echo '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Coupon created successfully!</div>';
                                        } else {
                                            echo '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Failed to create coupon.</div>';
                                        }
                                        ?>
                                    <?php endif; ?>

                                    <form method="POST">
                                        <div class="row">
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <label for="coupon_name">Coupon Name</label>
                                                    <input type="text" id="coupon_name" class="form-control"
                                                        name="coupon_name" placeholder="Enter Coupon Name" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <label for="coupon_discount">Discount</label>
                                                    <input type="number" id="coupon_discount" class="form-control"
                                                        name="coupon_discount"
                                                        placeholder="Enter Discount in Percentage" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <label for="coupon_guests">Select guests, you want to give
                                                        discount</label>
                                                    <div class="guests-outer-flex">
                                                        <select name="coupon_guests[]" id="coupon-guests-select" class="js-multi-select" multiple="multiple">
                                                            <?php
                                                            $current_user_id = get_current_user_id();
                                                            $guests = get_users(array(
                                                                'role__in' => array('homey_renter', 'homey_host'),
                                                                'exclude'  => array($current_user_id),
                                                            ));

                                                            foreach ($guests as $guest) {
                                                                $display_name_public = get_user_meta($guest->ID, 'display_name_public_guest', true);
                                                                $display_name = $display_name_public ? $display_name_public : $guest->display_name;
                                                                echo '<option value="' . esc_attr($guest->ID) . '">' . esc_html($display_name) . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-2">
                                                <input type="submit" name="create_coupon" class="btn btn-primary"
                                                    value="Create Coupon">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="block">
                                <div class="block-title">
                                    <div class="block-left">
                                        <h2 class="title">FAQs</h2>
                                    </div>
                                </div>
                                <div class="block-body">
                                    <div class="panel-group featured-faq" id="accordion">
                                        <div class="panel panel-default">
                                            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                data-target="#collapse1">
                                                <strong class="panel-title">
                                                    How do I create a new coupon?
                                                    <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                </strong>
                                            </div>
                                            <div id="collapse1" class="panel-collapse collapse in">
                                                <div class="panel-body">To create a new coupon, go to the Create Coupon page in the dashboard, fill in the coupon details such as coupon name, discount, select guests who will get coupon, and click Create Coupon.</div>
                                            </div>
                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                data-target="#collapse2">
                                                <strong class="panel-title">
                                                    What types of discounts can I create?
                                                    <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                </strong>
                                            </div>
                                            <div id="collapse2" class="panel-collapse collapse">
                                                <div class="panel-body">The coupon system supports Percentage Discounts (e.g., 20% off)</div>
                                            </div>
                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                data-target="#collapse3">
                                                <strong class="panel-title">
                                                    How many times a coupon can be used?
                                                    <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                </strong>
                                            </div>
                                            <div id="collapse3" class="panel-collapse collapse">
                                                <div class="panel-body">A coupon can be used one time.</div>
                                            </div>
                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                data-target="#collapse10">
                                                <strong class="panel-title">
                                                    How many guests can I assign a coupon to?
                                                    <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                </strong>
                                            </div>
                                            <div id="collapse10" class="panel-collapse collapse">
                                                <div class="panel-body">
                                                    You can assign a coupon to as many guests as you want.</div>
                                            </div>
                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                data-target="#collapse11">
                                                <strong class="panel-title">
                                                    Can a coupon apply on all my listings?
                                                    <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                </strong>
                                            </div>
                                            <div id="collapse11" class="panel-collapse collapse">
                                                <div class="panel-body">
                                                    Yes a coupon can apply on all your listings.</div>
                                            </div>
                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                data-target="#collapse4">
                                                <strong class="panel-title">
                                                    How guests will receive coupon code?
                                                    <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                </strong>
                                            </div>
                                            <div id="collapse4" class="panel-collapse collapse">
                                                <div class="panel-body">Guests will receive coupon code through emails. After creating coupon go to All Coupons page. Every coupon has some actions, under actions click on email icon and email will be sent to all selected guests.</div>
                                            </div>
                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                data-target="#collapse5">
                                                <strong class="panel-title">
                                                    How can I edit a coupon?
                                                    <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                </strong>
                                            </div>
                                            <div id="collapse5" class="panel-collapse collapse">
                                                <div class="panel-body">To edit a coupon go to All Coupons page. Every coupon has some actions under actions select pencil icon to edit a coupon.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                data-target="#collapse6">
                                                <strong class="panel-title">
                                                    How can I delete a coupon?
                                                    <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                </strong>
                                            </div>
                                            <div id="collapse6" class="panel-collapse collapse">
                                                <div class="panel-body">To delete a coupon go to All Coupons page. Every coupon has some actions under actions select trash icon to delete a coupon.</div>
                                            </div>
                                        </div>
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