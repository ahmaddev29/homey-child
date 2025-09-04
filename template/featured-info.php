<?php

/**
 * Template Name: Featured Info
 */
if (!is_user_logged_in() || homey_is_renter()) {
    wp_redirect(home_url('/'));
}

get_header();

global $current_user, $post;
$hide_labels = homey_option('show_hide_labels');

wp_get_current_user();
$userID         = $current_user->ID;
$user_login     = $current_user->user_login;
$edit_link      = homey_get_template_link('template/dashboard-submission.php');
$listings_page  = homey_get_template_link('template/dashboard-listings.php');

$publish_active = $pending_active = $draft_active = $mine_active = $all_active = $disabled_active = 'btn btn-primary-outlined btn-slim';
if (isset($_GET['status']) && $_GET['status'] == 'publish') {
    $publish_active = 'btn btn-primary btn-slim';
} elseif (isset($_GET['status']) && $_GET['status'] == 'pending') {
    $pending_active = 'btn btn-primary btn-slim';
} elseif (isset($_GET['status']) && $_GET['status'] == 'draft') {
    $draft_active = 'btn btn-primary btn-slim';
} elseif (isset($_GET['status']) && $_GET['status'] == 'disabled') {
    $disabled_active = 'btn btn-primary btn-slim';
} elseif (isset($_GET['status']) && $_GET['status'] == 'mine') {
    $mine_active = 'btn btn-primary btn-slim';
} else {
    $all_active = 'btn btn-primary btn-slim';
}

$all_link = add_query_arg('status', 'any', $listings_page);
$publish_link = add_query_arg('status', 'publish', $listings_page);
$pending_link = add_query_arg('status', 'pending', $listings_page);
$draft_link = add_query_arg('status', 'draft', $listings_page);
$disabled_link = add_query_arg('status', 'disabled', $listings_page);
$mine_link = add_query_arg('status', 'mine', $listings_page);

$qry_status = isset($_GET['status']) ? $_GET['status'] : 'any';

$no_of_listing   =  '9';
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = array(
    'post_type'         =>  'listing',
    'orderby'           => 'modified',
    'order'             => 'DESC',
    'paged'             => $paged,
    'posts_per_page'    => $no_of_listing,
    'author'            => $userID,
    'post_status'       =>  'publish'
);

// if (homey_is_host() || homey_is_renter()) {
//     $args['author'] = $userID;
// } else {
//     if (isset($_GET['status']) && $_GET['status'] == 'mine') {
//         $args['author'] = $userID;
//     }
// }

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

$args = homey_listing_sort($args);
$listing_qry = new WP_Query($args);

$post_type = 'listing';
$user_post_count = count_user_posts($userID, $post_type);
$num_posts    = wp_count_posts($post_type, 'readable');
/*print_r($num_posts);
echo $num_posts->publish;*/
$num_post_arr = (array) $num_posts;
unset($num_post_arr['auto-draft']);
$total_posts  = array_sum($num_post_arr);

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
                            <div id="listings_module_section" class="dashboard-area" style="margin-top: 30px;margin-bottom: 30px;">
                                <div class="block">
                                    <div class="block-title">
                                        <div class="block-left">
                                            <h2 class="title"><?php echo esc_attr($homey_local['manage_label']); ?></h2>
                                        </div>
                                        <div class="block-right">
                                            <div class="dashboard-form-inline">
                                                <form class="form-inline">
                                                    <div class="form-group">
                                                        <input name="keyword" type="text" class="form-control" value="<?php echo isset($_GET['keyword']) ? $_GET['keyword'] : ''; ?>" placeholder="<?php echo esc_attr__('Search listing', 'homey'); ?>">
                                                    </div>
                                                    <button type="submit" class="btn btn-primary btn-search-icon"><i class="fa fa-search" aria-hidden="true"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    if ($listing_qry->have_posts()) : ?>
                                        <div class="table-block dashboard-listing-table dashboard-table">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo homey_option('sn_id_label'); ?></th>
                                                        <th><?php echo esc_attr($homey_local['thumb_label']); ?></th>
                                                        <th><?php echo esc_attr($homey_local['address']); ?></th>
                                                        <th><?php echo homey_option('sn_type_label'); ?></th>
                                                        <th><?php echo esc_attr($homey_local['price_label']); ?></th>
                                                        <?php if ($hide_labels['sn_guests_label'] != 1) { ?>
                                                            <th><?php echo homey_option('glc_guests_label'); ?></th>
                                                        <?php } ?>
                                                        <th>Featured</th>
                                                        <th><?php echo esc_attr($homey_local['actions_label']); ?></th>

                                                    </tr>
                                                </thead>
                                                <tbody id="module_listings">
                                                    <?php
                                                    while ($listing_qry->have_posts()) : $listing_qry->the_post();
                                                        get_template_part('template-parts/dashboard/listing-item-featured');
                                                    endwhile;
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php
                                    else :
                                        echo '<div class="block-body">';
                                        echo esc_attr($homey_local['listing_dont_have']);
                                        echo '</div>';
                                    endif;
                                    ?>
                                </div><!-- .block -->

                                <?php homey_pagination($listing_qry->max_num_pages, $range = 2); ?>

                            </div><!-- .dashboard-area -->
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>

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
        flex-wrap: wrap;
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

<script>
    jQuery(document).ready(function() {
        jQuery('.panel-heading').click(function(event) {
            event.preventDefault();
            var target = jQuery(this).attr('data-target');
            jQuery(target).collapse('toggle');
        });
    });
</script>


<?php get_footer(); ?>