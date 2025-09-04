<?php global $author_info, $userID;
$is_photo = $author_info['is_photo'];
$is_email = $author_info['is_email'];

$user_id = get_current_user_id();
$email_verified = get_user_meta($user_id, 'email_verified', true);

$user_role = homey_get_user_role($userID);

$stripe_account_id = get_user_meta($userID, 'stripe_account_id', true);
$saved_cards = get_user_meta($userID, 'saved_stripe_cards', true);

$account_status = '';
if ($user_role != 'homey_renter') {
    $result = check_stripe_account_status($stripe_account_id);
    $account_status = $result['status'];
}

$author_picture_id = get_the_author_meta('homey_author_picture_id', $userID);

$is_doc_verified = get_the_author_meta('doc_verified', $userID);

$steps = array(
    'profile_picture' => 'Upload Profile Picture',
    'email_verified' => 'Verify Email Address',
    'id_verified' => 'Verify Your ID',
    'payout_or_payment_method' => ($user_role == 'homey_renter') ? 'Add Payment Method' : 'Add Payout Method',
);

$points = array(
    'profile_picture' => 25,
    'email_verified' => 25,
    'id_verified' => 25,
    'payout_or_payment_method' => 25,
);

$completed_points = 0;
$total_points = 0;

foreach ($steps as $key => $label) {
    $total_points += $points[$key];
    if ($key == 'profile_picture' && !empty($author_picture_id)) {
        $completed_points += $points[$key];
    } elseif ($key == 'id_verified' && !empty($is_doc_verified)) {
        $completed_points += $points[$key];
    } elseif ($key != 'profile_picture' && $key != 'id_verified' && get_user_meta($userID, $key, true)) {
        $completed_points += $points[$key];
    }
}

$payout_payment_method_completed = false;
if ($user_role != 'homey_renter' && empty($account_status) && !empty($stripe_account_id)) {
    $completed_points += $points['payout_or_payment_method'];
    $payout_payment_method_completed = true;
} elseif ($user_role == 'homey_renter' && !empty($saved_cards)) {
    $completed_points += $points['payout_or_payment_method'];
    $payout_payment_method_completed = true;
}

$completion_percentage = ($completed_points / $total_points) * 100;
?>
<div class="user-sidebar-inner">
    <div class="block">
        <div class="block-body">
            <div class="media">
                <div class="media-left">
                    <div class="media-object">
                        <?php echo '' . $author_info['photo']; ?>
                    </div>
                </div>
                <div class="media-body media-middle">
                    <h4 class="media-heading mb-0"><?php esc_html_e('Profile Completed', 'homey'); ?></h4>
                    <h1 class="media-count"><?php echo esc_attr($completion_percentage); ?>%</h1>
                </div>
            </div>
        </div>
        <div class="block-verify">
            <div class="block-col block-col-50">
                <div class="block-icon text-secondary"><i class="fa fa-user-circle-o"></i></div>
                <p><strong><?php esc_html_e('Profile Picture', 'homey'); ?></strong></p>
                <?php if ($is_photo) { ?>
                    <p class="text-success"><i class="fa fa-check-circle-o"></i> <?php esc_html_e('Done', 'homey'); ?></p>
                <?php } else { ?>
                    <p class="text-danger"><i class="fa fa-times-circle"></i></p>
                <?php } ?>
            </div>
            <div class="block-col block-col-50">
                <div class="block-icon text-secondary"><i class="fa fa-envelope-open-o"></i></div>
                <p><strong><?php esc_html_e('Email Address', 'homey'); ?></strong></p>
                <?php if (homey_is_admin() || $email_verified) { ?>
                    <p class="text-success"><i class="fa fa-check-circle-o"></i> <?php esc_html_e('VERIFIED', 'homey'); ?>
                    </p>
                <?php } else { ?>
                    <p class="text-danger"><i class="fa fa-times-circle"></i></p>
                <?php } ?>
            </div>
        </div>
    </div>
</div>