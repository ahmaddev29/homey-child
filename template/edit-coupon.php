<?php

/**
 * Template Name: Edit Coupon
 */
if (!is_user_logged_in() || homey_is_renter()) {
    wp_redirect(home_url('/'));
    exit;
}

get_header();

$coupon_id = isset($_GET['edit_coupon']) ? intval($_GET['edit_coupon']) : 0;

if ($coupon_id > 0) {
    $coupon = get_post($coupon_id);
    if (!$coupon || $coupon->post_type !== 'host_coupon' || $coupon->post_author != get_current_user_id()) {
        echo '<p>' . esc_html__('You do not have permission to edit this coupon.', 'text-domain') . '</p>';
        get_footer();
        exit;
    }
} else {
    echo '<p>' . esc_html__('Invalid coupon ID.', 'text-domain') . '</p>';
    get_footer();
    exit;
}
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
                                        <h2 class="title">Edit Coupon (The host can only provide coupon discounts to guests who are registered on the Backyard Lease platform.)</h2>
                                    </div>
                                </div>
                                <div class="block-body">
                                    <?php
                                    if (isset($_POST['edit_coupon']) && wp_verify_nonce($_POST['edit_coupon_nonce'], 'edit_coupon')) {
                                        $title = sanitize_text_field($_POST['coupon_name']);
                                        $discount = intval($_POST['coupon_discount']);
                                        $guests = isset($_POST['coupon_guests']) ? $_POST['coupon_guests'] : array();

                                        // Update the coupon post
                                        $coupon_post = array(
                                            'ID' => $coupon_id,
                                            'post_title' => $title,
                                        );

                                        $updated = wp_update_post($coupon_post);

                                        if ($updated) {
                                            update_field('coupon_name', $title, $coupon_id);
                                            update_field('coupon_discount', $discount, $coupon_id);
                                            update_field('coupon_guests', $guests, $coupon_id);
                                            update_post_meta($coupon_id, 'coupon_guests_ids', $guests);
                                            echo '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Coupon updated successfully!</div>';
                                        } else {
                                            echo '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Failed to update coupon.</div>';
                                        }
                                    }
                                    $coupon_title = get_post_meta($coupon_id, 'coupon_name', true);
                                    $coupon_discount = get_post_meta($coupon_id, 'coupon_discount', true);
                                    $coupon_guests = get_post_meta($coupon_id, 'coupon_guests', true);
                                    ?>
                                    <form method="POST">
                                        <?php wp_nonce_field('edit_coupon', 'edit_coupon_nonce'); ?>
                                        <div class="row">
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <label for="coupon_name">Coupon Name</label>
                                                    <input type="text" id="coupon_name" class="form-control"
                                                        name="coupon_name"
                                                        value="<?php echo esc_attr($coupon_title); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <label for="coupon_discount">Discount</label>
                                                    <input type="number" id="coupon_discount" class="form-control"
                                                        name="coupon_discount"
                                                        value="<?php echo esc_attr($coupon_discount); ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 col-xs-12">
                                                <div class="form-group">
                                                    <label for="coupon_guests">Select guests you want to give
                                                        discount</label>
                                                    <div class="guests-outer-flex">
                                                        <select name="coupon_guests[]" id="coupon-guests-select" class="js-multi-select" multiple="multiple">
                                                            <?php
                                                            $current_user_id = get_current_user_id();
                                                            $guests = get_users(array(
                                                                'role__in' => array('homey_renter', 'homey_host'),
                                                                'exclude'  => array($current_user_id),
                                                            ));


                                                            $selected_guests = (array) $coupon_guests;

                                                            foreach ($guests as $guest) {
                                                                $display_name_public = get_user_meta($guest->ID, 'display_name_public_guest', true);
                                                                $display_name = $display_name_public ? $display_name_public : $guest->display_name;


                                                                $selected = in_array($guest->ID, $selected_guests) ? 'selected="selected"' : '';

                                                                echo '<option value="' . esc_attr($guest->ID) . '" ' . $selected . '>'
                                                                    . esc_html($display_name) . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-2">
                                                <input type="submit" name="edit_coupon" class="btn btn-primary"
                                                    value="Update Coupon">
                                            </div>
                                        </div>
                                    </form>
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