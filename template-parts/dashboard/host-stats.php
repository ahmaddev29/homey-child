<?php
global $wpdb, $current_user, $userID, $homey_local, $homey_threads, $author;
$reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
$submission_page_link = homey_get_template_link('template/dashboard-submission.php');
$total_favorites_link = homey_get_template_link('template/total-favorites.php');
$wallet_page_link = homey_get_template_link('template/dashboard-wallet.php');
$total_earnings = get_user_meta($userID, 'host_total_earning', $total_earning);
$enable_wallet = homey_option('enable_wallet');
if ($enable_wallet != 0) {
    $block_class = "block-col-25";
} else {
    $block_class = "block-col-50";
}
?>
<div class="block">

    <!-- <div class="block-head">
         <h2 class="title text-center"><?php echo esc_attr($homey_local['welcome_back_text']); ?> <?php echo esc_attr($author['name']); ?> </h2> 
    </div> -->
    <div class="block-verify">
        <div class="block-col <?php echo esc_attr($block_class); ?>">
            <h3>Total Favorites</h3>
            <p class="block-big-text">
                <?php
                $host_favorites = get_favorites_summary_for_host($current_user->ID);
                echo $host_favorites['total_favorites'];
                ?>
            </p>
            <?php if ($host_favorites['total_favorites'] > 0) { ?>
                <a href="<?php echo esc_url($total_favorites_link); ?>"><?php esc_html_e('View All', 'homey'); ?></a>
            <?php } ?>
        </div>
        <div class="block-col <?php echo esc_attr($block_class); ?>">
            <h3>Published Listings</h3>
            <p class="block-big-text">
                <?php echo esc_attr(isset($author['publish_listing_count']) ? $author['publish_listing_count'] : 0); ?>
            </p>
            <a href="<?php echo esc_url($submission_page_link); ?>"><?php esc_html_e('Add New', 'homey'); ?></a>
        </div>
        <div class="block-col <?php echo esc_attr($block_class); ?>">
            <h3><?php echo esc_attr($homey_local['pr_resv_label']); ?></h3>
            <p class="block-big-text"><?php echo homey_reservation_count($userID); ?></p>
            <a href="<?php echo esc_url($reservation_page_link); ?>"><?php esc_html_e('Manage', 'homey'); ?></a>
        </div>
        <?php if ($enable_wallet != 0) { ?>
            <div class="block-col <?php echo esc_attr($block_class); ?>">
                <h3><?php esc_html_e('Earnings', 'homey'); ?></h3>
                <p class="block-big-text">
                    <?php
                    if ($total_earnings[0] != 0) {
                        echo homey_formatted_price($total_earnings[0]);
                    } else {
                        echo '$0';
                    }
                    ?>
                </p>
                <a href="<?php echo esc_url($wallet_page_link); ?>"><?php esc_html_e('Wallet', 'homey'); ?></a>
            </div>
        <?php } ?>
    </div>

</div>