<?php

/**
 * Template Name: All Coupons
 */
if (!is_user_logged_in() || homey_is_renter()) {
    wp_redirect(home_url('/'));
    exit;
}

get_header();
global $homey_local;

$current_user_id = get_current_user_id();
$args = array(
    'post_type' => 'host_coupon',
    'author' => $current_user_id,
    'posts_per_page' => -1,
);

if (isset($_GET['keyword'])) {
    $keyword = trim($_GET['keyword']);
    if (!empty($keyword)) {
        $args['s'] = $keyword;

        // to search with ID
        if (is_numeric($keyword)) {
            $id = abs(intval($keyword));
            if ($id > 0) {
                unset($args['s']);
                $args['post__in'] = array($keyword);
            }
        }
        // end of to search with ID
    }
}

$coupons_query = new WP_Query($args);
?>

<section id="body-area">

    <div class="dashboard-page-title">
        <h1><?php the_title(); ?></h1>
    </div><!-- .dashboard-page-title -->

    <?php get_template_part('template-parts/dashboard/side-menu'); ?>

    <div class="user-dashboard-right dashboard-without-sidebar">
        <div class="dashboard-content-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="dashboard-area">
                            <div id="coupon_message"></div>
                            <div class="block">
                                <div class="block-title">
                                    <div class="block-left">
                                        <h2 class="title"><?php echo esc_attr($homey_local['manage_label']); ?></h2>
                                    </div>
                                    <div class="block-right">
                                        <div class="dashboard-form-inline">
                                            <form class="form-inline">
                                                <div class="form-group">
                                                    <input name="keyword" type="text" class="form-control"
                                                        value="<?php echo isset($_GET['keyword']) ? esc_attr($_GET['keyword']) : ''; ?>"
                                                        placeholder="<?php esc_attr_e('Search coupon', 'homey'); ?>">
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-search-icon"><i
                                                        class="fa fa-search" aria-hidden="true"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($coupons_query->have_posts()): ?>
                                    <div class="table-block dashboard-listing-table dashboard-table">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Coupon ID</th>
                                                    <th>Coupon Name</th>
                                                    <th>Discount</th>
                                                    <th>Guests</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="module_listings">
                                                <?php
                                                while ($coupons_query->have_posts()):
                                                    $coupons_query->the_post();
                                                    $coupon_guests = get_field('coupon_guests'); ?>
                                                    <tr class="coupon-item" id="coupon-<?php echo get_the_ID(); ?>">
                                                        <td><?php echo get_the_ID(); ?></td>
                                                        <td><?php echo esc_html(get_field('coupon_name')); ?></td>
                                                        <td><?php echo esc_html(get_field('coupon_discount')); ?>%</td>
                                                        <td>
                                                            <select class="guest-dropdown selectpicker">
                                                                <?php
                                                                if (!empty($coupon_guests)) {
                                                                    echo '<option value="guests">Selected Guests</option>';
                                                                    foreach ($coupon_guests as $guest) {
                                                                        $display_name_public = get_user_meta($guest['ID'], 'display_name_public_guest', true);
                                                                        $display_name = $display_name_public ? $display_name_public : $guest['display_name'];
                                                                        echo '<option value="' . esc_attr($guest['ID']) . '">' . esc_html($display_name) . '</option>';
                                                                    }
                                                                } else {
                                                                    echo '<option value="">No guest selected</option>';
                                                                }
                                                                ?>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <div class="custom-actions">
                                                                <button class="btn-action"
                                                                    onclick="location.href='<?php echo esc_url(home_url('/edit-coupon/?edit_coupon=' . get_the_ID())); ?>';"
                                                                    data-toggle="tooltip" data-placement="top"
                                                                    data-original-title="<?php echo esc_attr($homey_local['edit_btn']); ?>">
                                                                    <i class="fa fa-pencil"></i>
                                                                </button>
                                                                <button class="btn-action send-coupon-mail"
                                                                    data-id="<?php echo intval(get_the_ID()); ?>"
                                                                    data-toggle="tooltip" data-placement="top"
                                                                    data-original-title="Email">
                                                                    <i class="fa fa-envelope"></i>
                                                                </button>
                                                                <button class="btn-action send-coupon-message"
                                                                    data-id="<?php echo intval(get_the_ID()); ?>"
                                                                    data-toggle="tooltip" data-placement="top"
                                                                    data-original-title="Message">
                                                                    <i class="fa fa-comments-o"></i>
                                                                </button>
                                                                <button class="btn-action delete-coupon"
                                                                    data-id="<?php echo intval(get_the_ID()); ?>"
                                                                    data-toggle="tooltip" data-placement="top"
                                                                    data-original-title="<?php echo esc_attr($homey_local['delete_btn']); ?>">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php
                                                endwhile;
                                                wp_reset_postdata();
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="block-body">
                                        <?php esc_html_e('No coupon found', 'homey'); ?>
                                    </div>
                                <?php endif; ?>
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