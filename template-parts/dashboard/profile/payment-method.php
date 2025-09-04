<?php
global $current_user, $post, $homey_local;
$current_user = wp_get_current_user();

$userID = $current_user->ID;
$saved_cards = get_user_meta($userID, 'saved_stripe_cards', true);
$saved_cards = $saved_cards ? json_decode($saved_cards, true) : [];
$payout_stripe_id = get_user_meta($userID, 'payout_stripe_id', true);
$stripe_account_id = get_user_meta($userID, 'stripe_account_id', true);

$user_data = homey_get_author_by_id('36', '36', 'img-responsive img-circle', $userID);
$payout_payment_method = $user_data['payout_payment_method'];

$enable_wallet = homey_option('enable_wallet');
$reservation_payment = homey_option('reservation_payment');

$wallet_page_link = homey_get_template_link('template/dashboard-wallet.php');
$payout_request_link = add_query_arg('dpage', 'payout-request', $wallet_page_link);
?>

<!-- when user is guest -->
<?php if (homey_is_renter()) { ?>
    <div class="block">
        <div class="block-title">
            <h2 class="title"><?php esc_html_e('Rules Of Conduct', 'homey'); ?></h2>
        </div>
        <div class="block-body">
            <div class="icon-boxes">
                <div class="icon-box-homey" style="padding-top: 0px;">
                    <div class="icon-homey">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div class="text-homey">
                        <h3 class="title-homey">Protection</h3>
                        <span class="subtitle-homey">Keep your information and all transactions private.</span>
                    </div>
                </div>
                <div class="icon-box-homey">
                    <div class="icon-homey">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <div class="text-homey">
                        <h3 class="title-homey">Be Smart</h3>
                        <span class="subtitle-homey">Always be proactive and prevent scams before the occur. If something
                            doesn't seem right, it probably isn't.</span>
                    </div>
                </div>
                <div class="icon-box-homey" style="padding-bottom: 0px;border-bottom:none;">
                    <div class="icon-homey">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div class="text-homey">
                        <h3 class="title-homey">Keep all payment on Backyard Lease</h3>
                        <span class="subtitle-homey">Let's keep the Backyard Lease community safe and trustworthy. Do not
                            accept payment or give payment outside of this platform.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="block">
        <div class="block-title">
            <h2 class="title"><?php esc_html_e('Enter Card Details', 'homey'); ?></h2>
        </div>
        <div class="block-body">
            <form id="add-card-form">
                <div id="stripe-card-element" style="margin-bottom:20px"></div>
                <div id="stripe-card-errors" role="alert" style="margin-bottom:20px">
                </div>
                <button type="submit" class="btn btn-primary">Add Card<span id="loader"
                        style="display:none;margin-left: 10px;"><i class="fa fa-spinner fa-spin"></i></span></button>

            </form>
        </div>
    </div>

    <div id="delete-card-errors" class="alert alert-danger alert-dismissible" style="display: none;"></div>
    <div class="block">
        <div class="block-title">
            <h2 class="title"><?php esc_html_e('Saved Cards', 'homey'); ?></h2>
        </div>
        <div class="table-block dashboard-listing-table dashboard-table">
            <?php if ($saved_cards) { ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Card Number', 'homey'); ?></th>
                            <th><?php esc_html_e('Brand', 'homey'); ?></th>
                            <th><?php esc_html_e('Expiry Date', 'homey'); ?></th>
                            <th><?php esc_html_e('Actions', 'homey'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="module_listings">
                        <?php foreach ($saved_cards as $card): ?>
                            <tr>
                                <td><?php echo esc_html('**** **** **** ' . $card['last4']); ?></td>
                                <td><?php echo esc_html($card['brand']); ?></td>
                                <td><?php echo esc_html($card['exp_month'] . '/' . $card['exp_year']); ?></td>
                                <td>
                                    <div class="custom-actions">
                                        <button class="btn-action delete-card" data-card-id="<?php echo esc_attr($card['id']); ?>"
                                            data-toggle="tooltip" data-placement="top"
                                            data-original-title="<?php echo esc_attr($homey_local['delete_btn']); ?>">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="alert alert-info">
                    <?php esc_html_e('No saved credit card found.', 'homey'); ?>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>

<!-- When user is host -->
<?php if (homey_is_host()) { ?>

    <div class="block define-payout-methods">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title"><?php esc_html_e('Payout Method', 'homey'); ?></h2>
            </div>
        </div>
        <div class="table-block dashboard-listing-table dashboard-table">
            <table class="table table-hover">
                <?php
                if ($stripe_account_id) {
                    $result = check_stripe_account_status($stripe_account_id);

                    if (isset($result['error'])) {
                        echo '<p>Error checking account status: ' . esc_html($result['error']) . '</p>';
                    } else {
                        $account_status = $result['status'];
                        if ($account_status) {
                ?>
                            <thead>
                                <tr>
                                    <th>Stripe Account ID</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="module_listings">
                                <tr>
                                    <td><?php echo esc_html($stripe_account_id); ?></td>
                                    <td><span class="label label-danger">Restricted</span></td>
                                    <td><button id="complete-stripe-account" class="btn btn-primary">Complete Account</button></td>
                                </tr>
                            </tbody>
                        <?php
                        } else {
                        ?>
                            <thead>
                                <tr>
                                    <th>Stripe Account ID</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="module_listings">
                                <tr>
                                    <td><?php echo esc_html($stripe_account_id); ?></td>
                                    <td><span class="label label-secondary">Enabled</span></td>
                                </tr>
                            </tbody>
                <?php
                        }
                    }
                } else {
                    echo '<button id="create-stripe-account" class="btn btn-primary">Connect to Stripe</button>';
                }
                ?>
            </table>
        </div>

    <?php } ?>

    <?php if (homey_is_renter()): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var stripe_pk = "<?php echo homey_option('stripe_publishable_key'); ?>";
                var stripe = Stripe(stripe_pk);
                var elements = stripe.elements();
                var cardElement = elements.create('card');
                cardElement.mount('#stripe-card-element');
                let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";

                document.getElementById('add-card-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    document.getElementById('loader').style.display = 'inline';
                    stripe.createToken(cardElement).then(function(result) {
                        if (result.error) {
                            document.getElementById('stripe-card-errors').textContent = result.error.message;
                            document.getElementById('loader').style.display = 'none';
                        } else {
                            var token = result.token.id;
                            jQuery.ajax({
                                url: ajaxurl,
                                method: 'POST',
                                data: {
                                    action: 'save_stripe_card',
                                    token: token,
                                    user_id: <?php echo $userID; ?>
                                },
                                success: function(response) {
                                    if (response.success) {
                                        location.reload();
                                    } else {
                                        var errorElement = document.getElementById('stripe-card-errors');
                                        errorElement.textContent = response.data;
                                        errorElement.classList.add('alert', 'alert-danger', 'alert-dismissible');
                                        document.getElementById('loader').style.display = 'none';
                                    }
                                }
                            });
                        }
                    });
                });

                document.querySelectorAll('.delete-card').forEach(function(button) {
                    button.addEventListener('click', function() {
                        var cardId = this.getAttribute('data-card-id');
                        var icon = this.querySelector('i');

                        icon.classList.remove('fa-trash');
                        icon.classList.add('fa-spinner', 'fa-spin');

                        var errorDiv = document.getElementById('delete-card-errors');
                        errorDiv.style.display = 'none';
                        errorDiv.textContent = '';

                        jQuery.ajax({
                            url: ajaxurl,
                            method: 'POST',
                            data: {
                                action: 'delete_stripe_card',
                                card_id: cardId,
                                user_id: <?php echo $userID; ?>
                            },
                            success: function(response) {
                                if (response.success) {
                                    location.reload();
                                } else {
                                    icon.classList.remove('fa-spinner', 'fa-spin');
                                    icon.classList.add('fa-trash');
                                    errorDiv.textContent = 'Failed to delete the card.';
                                    errorDiv.style.display = 'block';
                                }
                            },
                            error: function() {
                                icon.classList.remove('fa-spinner', 'fa-spin');
                                icon.classList.add('fa-trash');
                                errorDiv.textContent = 'An error occurred while deleting the card.';
                                errorDiv.style.display = 'block';
                            }
                        });
                    });
                });
            });
        </script>
    <?php endif; ?>