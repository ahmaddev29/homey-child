<?php
global $current_user, $userID, $user_email, $homey_prefix, $homey_local;

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
    'payout_or_payment_method' => ($user_role == 'homey_host') ? 'Add Payout Method' : 'Add Payment Method',
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

<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title">Profile Completed</h2>
        </div>
        <div class="block-right">
            <h2 class="title"><?php echo esc_attr($completion_percentage); ?>%</h2>
        </div>
    </div>
    <div class="block-body">
        <div class="row">
            <div class="profile-completion">
                <div class="completion-bar">
                    <div class="completion-percentage" style="width: <?php echo esc_attr($completion_percentage); ?>%;">
                        <?php echo esc_html($completion_percentage); ?>%
                    </div>
                </div>
                <ul class="completion-steps">
                    <?php foreach ($steps as $key => $label) : ?>
                        <li>
                            <span class="step-icon">
                                <?php
                                if ($key == 'payout_or_payment_method' && $payout_payment_method_completed) {
                                    echo '<p class="text-success"><i class="fa fa-check-circle-o"></i></p>';
                                } elseif ($key == 'profile_picture' && !empty($author_picture_id)) {
                                    echo '<p class="text-success"><i class="fa fa-check-circle-o"></i></p>';
                                } elseif ($key == 'id_verified' && !empty($is_doc_verified)) {
                                    echo '<p class="text-success"><i class="fa fa-check-circle-o"></i></p>';
                                } else {
                                    echo get_user_meta($userID, $key, true) ? '<p class="text-success"><i class="fa fa-check-circle-o"></i></p>' : '<p class="text-danger"><i class="fa fa-times-circle"></i></p>';
                                }
                                ?>
                            </span>
                            <h4 class="step-description">
                                <?php echo esc_html($label); ?>
                            </h4>
                            <span class="step-points">
                                <?php echo esc_html($points[$key]); ?>%
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-completion {
        width: 100%;
        margin: 0 auto;
    }

    .completion-bar {
        background-color: #e0e0e0;
        border-radius: 5px;
        overflow: hidden;
        margin-bottom: 15px;
    }

    .completion-percentage {
        background-color: #85c341;
        height: 30px;
        color: white;
        text-align: center;
        line-height: 30px;
        font-weight: bold;
    }

    .completion-steps {
        list-style: none;
        padding: 0;
    }

    .completion-steps li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .step-icon {
        width: 24px;
        height: 22px;
    }

    .step-description {
        flex-grow: 1;
        padding: 0 10px;
        margin: 0;
    }

    .step-points {
        width: 50px;
        text-align: right;
        font-weight: bold;
    }
</style>