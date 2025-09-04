<?php

/**
 * Template Name: All Favorites
 */
if (!is_user_logged_in() || homey_is_renter()) {
    wp_redirect(home_url('/'));
}

get_header();

global $current_user;
$host_favorites = get_favorites_summary_for_host($current_user->ID);
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
                        <div class="dashboard-area"></div>
                        <div class="block">
                            <div class="block-title">
                                <div class="block-left">
                                    <h2 class="title">All Favorites</h2>
                                </div>
                            </div>
                            <div class="table-block dashboard-listing-table dashboard-table">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Favorited By</th>
                                        </tr>
                                    </thead>
                                    <tbody id="module_listings">
                                        <?php
                                        foreach ($host_favorites as $listing_id => $details) {
                                            if ($listing_id === 'total_favorites') {
                                                continue;
                                            }

                                            echo '<tr>';
                                            echo '<td>' . esc_html($details['listing_id']) . '</td>';
                                            echo '<td>' . esc_html($details['listing_title']) . '</td>';
                                            echo '<td>' . esc_html(implode(', ', $details['favorited_by'])) . '</td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
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