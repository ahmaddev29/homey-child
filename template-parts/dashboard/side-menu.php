<?php
global $current_user, $post, $homey_local;
$current_user = wp_get_current_user();
$enable_wallet = homey_option('enable_wallet');
$reservation_payment = homey_option('reservation_payment');
$offsite_payment = homey_option('off-site-payment');

$wallet_page_link = homey_get_template_link('template/dashboard-wallet.php');
$earnings_page_link = add_query_arg('dpage', 'earnings', $wallet_page_link);
$payout_request_link = add_query_arg('dpage', 'payout-request', $wallet_page_link);
$payouts_page_link = add_query_arg('dpage', 'payouts', $wallet_page_link);
//$payouts_setup_page = add_query_arg( 'dpage', 'payment-method', $wallet_page_link );
$security_deposits_page = add_query_arg('dpage', 'security-deposits', $wallet_page_link);

$dashboard = homey_get_template_link_dash('template/dashboard.php');
$dashboard_profile = homey_get_template_link_dash('template/dashboard-profile.php');
$payment_method_setup = add_query_arg('dpage', 'payment-method', $dashboard_profile);

$dashboard_listings = homey_get_template_link_dash('template/dashboard-listings.php');
$referral_program = homey_get_template_link_dash('template/referral-program.php');
$notification_settings = homey_get_template_link_dash('template/notification-settings.php');
$featured_info = homey_get_template_link_dash('template/featured-info.php');
$create_coupon = homey_get_template_link_dash('template/create-coupon.php');
$all_coupons = homey_get_template_link_dash('template/all-coupons.php');
$guest_reviews = homey_get_template_link_dash('template/guest-reviews.php');
$dashboard_membership = homey_get_template_link_dash('template/dashboard-membership-host.php');
$dashboard_add_listing = homey_get_template_link_dash('template/dashboard-submission.php');
$dashboard_favorites = homey_get_template_link_dash('template/dashboard-favorites.php');
$dashboard_search = homey_get_template_link_dash('template/dashboard-saved-searches.php');
$dashboard_reservations = homey_get_template_link_dash('template/dashboard-reservations.php');
$dashboard_host_reservations = homey_get_template_link_dash('template/dashboard-reservations2.php');
$dashboard_messages = homey_get_template_link_dash('template/dashboard-chat.php');
$dashboard_invoices = homey_get_template_link_dash('template/dashboard-invoices.php');
$dashboard_wallet = homey_get_template_link_dash('template/dashboard-wallet.php');
$home_link = home_url('/');

$all_users = add_query_arg('dpage', 'users', $dashboard);
$verification_page = add_query_arg('dpage', 'verification', $dashboard_profile);
$password_page = add_query_arg('dpage', 'password-reset', $dashboard_profile);

$ac_wallet = $ac_dash = $ac_profile = $ac_fav = $ac_listings = $ac_membership = $ac_referral_program = $ac_featured_info = $ac_create_coupon = $ac_all_coupons = $ac_notification_settings = $ac_guest_reviews = $ac_invoices = $ac_msgs = $ac_submission = $ac_reserv = $ac_reserv_host = '';
if (is_page_template('template/dashboard.php') && !isset($_GET['dpage'])) {
  $ac_dash = 'board-panel-item-active';
} elseif (is_page_template('template/dashboard-profile.php')) {
  $ac_profile = 'board-panel-item-active';
} elseif (is_page_template('template/dashboard-listings.php')) {
  $ac_listings = 'board-panel-item-active';
} elseif (is_page_template('template/dashboard-submission.php')) {
  $ac_submission = 'board-panel-item-active';
} elseif (is_page_template('template/dashboard-favorites.php')) {
  $ac_fav = 'board-panel-item-active';
} elseif (is_page_template('template/referral-program.php')) {
  $ac_referral_program = 'board-panel-item-active';
} elseif (is_page_template('template/notification-settings.php')) {
  $ac_notification_settings = 'board-panel-item-active';
} elseif (is_page_template('template/featured-info.php')) {
  $ac_featured_info = 'board-panel-item-active';
} elseif (is_page_template('template/create-coupon.php')) {
  $ac_create_coupon = 'board-panel-item-active';
} elseif (is_page_template('template/all-coupons.php')) {
  $ac_all_coupons = 'board-panel-item-active';
} elseif (is_page_template('template/guest-reviews.php')) {
  $ac_guest_reviews = 'board-panel-item-active';
} elseif (is_page_template('template/dashboard-invoices.php')) {
  $ac_invoices = 'board-panel-item-active';
} elseif (is_page_template('template/dashboard-chat.php')) {
  $ac_msgs = 'board-panel-item-active';
} elseif (is_page_template('template/dashboard-reservations.php')) {
  $ac_reserv = 'board-panel-item-active';
} elseif (is_page_template('template/dashboard-reservations2.php')) {
  $ac_reserv_host = 'board-panel-item-active';
} elseif (is_page_template('template/dashboard-wallet.php')) {
  $ac_wallet = 'board-panel-item-active';
} elseif (is_page_template('template/dashboard-membership-host.php')) {
  $ac_wallet = 'board-panel-item-active';
}

?>
<div class="user-dashboard-left white-bg">
  <div class="navi">
    <ul class="board-panel-menu">
      <?php
      if (!empty($dashboard)) {
        echo '<li class="' . esc_attr($ac_dash) . '">
                        <a href="' . esc_url($dashboard) . '">
                            ' . $homey_local['m_dashboard_label'] . '
                        </a>
                    </li>';
      }

      if (!empty($dashboard_profile)) {
        echo '<li class="has-child ' . esc_attr($ac_profile) . '">
                    <a href="' . esc_url($dashboard_profile) . '">
                        ' . $homey_local['m_profile_label'] . ' <i class="fa fa-angle-down"></i>
                    </a>';

        echo '<ul>';
        if (!homey_is_admin() && !homey_is_guide()) {
          echo '<li><a href="' . esc_url($verification_page) . '">' . esc_html__('Verification', 'homey') . '</a></li>';
        }
        echo '<li><a href="' . esc_url($password_page) . '">' . esc_html__('Password', 'homey') . '</a></li>';

        if ($offsite_payment != 0 || ($enable_wallet != 0 && !homey_is_guide())) {
          $link_text = '';

          if (homey_is_renter()) {
            $link_text = esc_html__('Payment Method', 'homey');
          } elseif (homey_is_host()) {
            $link_text = esc_html__('Payout Method', 'homey');
          }

          echo '<li><a href="' . esc_url($payment_method_setup) . '">' . $link_text . '</a></li>';
        }
        echo '</ul>';

        echo '</li>';
      }

      if (!homey_is_renter() && !homey_is_guide()) {
        if (!empty($dashboard_listings)) {
          echo '<li class="' . esc_attr($ac_listings) . '">
                        <a href="' . esc_url($dashboard_listings) . '">' . $homey_local['m_listings_label'] . '</a>
                    </li>';
        }

        if (!empty($featured_info)) {
          echo '<li class="' . esc_attr($ac_featured_info) . '">
                    <a href="' . esc_url($featured_info) . '">' . esc_html__('Featured', 'homey') . '</a>
                </li>';
        }

        if (!empty($dashboard_add_listing)) {
          echo '<li class="' . esc_attr($ac_submission) . '">
                        <a href="' . esc_url($dashboard_add_listing) . '" class="new-listing-button">' . $homey_local['m_add_listing_label'] . '</a>
                    </li>';
        }
      }

      if (!homey_is_renter() && !homey_is_guide() && !homey_is_admin() && in_array('homey-membership/homey-membership.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        if (!empty($dashboard_membership)) {
          echo '<li class="' . esc_attr($ac_membership) . '">
                        <a href="' . esc_url($dashboard_membership) . '">' . esc_html__('Membership', 'homey') . '</a>
                    </li>';
        }
      }

      if (!empty($dashboard_reservations) && !homey_is_guide()) {

        if (homey_is_renter()) {
          echo '<li class="' . esc_attr($ac_reserv) . '">
                        <a href="' . esc_url($dashboard_reservations) . '">' . $homey_local['m_reservation_label'] . '</a>
                    </li>';
        } elseif (homey_is_admin()) {
          echo '<li class="' . esc_attr($ac_reserv) . '">
                        <a href="' . esc_url($dashboard_reservations) . '">' . esc_html__('Bookings', 'homey') . '</a>
                    </li>';
        } else {
          $new_notification = homey_booking_notification(1);
          $new_notification = $new_notification > 0 ? '<span class="new-booking-alert" style="display: block;"></span>' : '<span class="new-booking-alert" style="display: none;"></span>';

          echo '<li class="' . esc_attr($ac_reserv) . '">
                        <a href="' . esc_url($dashboard_reservations) . '">' . esc_html__('My Bookings', 'homey') . ' ' . $new_notification . '</a>
                    </li>';
        }
      }

      if (!empty($dashboard_host_reservations) && !homey_is_renter()) {
        echo '<li class="' . $ac_reserv_host . '">
                    <a href="' . esc_url($dashboard_host_reservations) . '">' . esc_html__('My Reservations', 'homey') . '</a>
                </li>';
      }

      if (!empty($notification_settings)) {
        echo '<li class="' . $ac_notification_settings . '">
                    <a href="' . esc_url($notification_settings) . '">' . esc_html__('Notification Settings', 'homey') . '</a>
                </li>';
      }

      if (!empty($create_coupon) && !homey_is_renter()) {
        echo '<li class="' . $ac_create_coupon . '">
                    <a href="' . esc_url($create_coupon) . '">' . esc_html__('Create Coupon', 'homey') . '</a>
                </li>';
      }

      if (!empty($all_coupons) && !homey_is_renter()) {
        echo '<li class="' . $ac_all_coupons . '">
                    <a href="' . esc_url($all_coupons) . '">' . esc_html__('All Coupons', 'homey') . '</a>
                </li>';
      }

      if ($enable_wallet != 0) {
        if ($reservation_payment == 'percent' || $reservation_payment == 'full') {
          if (homey_is_host()) {
            if (!empty($dashboard_wallet)) {
              echo '<li class="' . esc_attr($ac_wallet) . ' has-child">
                                <a href="' . esc_url($dashboard_wallet) . '">' . esc_html__('Wallet', 'homey') . ' <i class="fa fa-angle-down"></i></a>
                                <ul>
                                    <li><a href="' . esc_url($payment_method_setup) . '">' . esc_html__('Payout Method', 'homey') . '</a></li>
                                </ul>
                            </li>';
            }
          }

          /**
            <ul>
                                          <li><a href="' . esc_url($security_deposits_page) . '">' . esc_html__('Security Deposit', 'homey') . '</a></li>
                                          <li><a href="' . esc_url($payouts_page_link) . '">' . esc_html__('Payouts', 'homey') . '</a></li>
                                      </ul>
                      
           **/

          /** 
            if (homey_is_renter()) {
             if (!empty($dashboard_wallet)) {
               echo '<li class="' . esc_attr($ac_wallet) . '">
                                 <a href="' . esc_url($dashboard_wallet) . '">' . esc_html__('Wallet', 'homey') . '</a>
                             </li>';
             }
           } 
           **/

          if (homey_is_admin()) {
            if (!empty($dashboard_wallet)) {
              echo '<li class="' . esc_attr($ac_wallet) . '">
                                <a href="' . esc_url($payouts_page_link) . '">' . esc_html__('Payouts', 'homey') . '</a>
                            </li>';
            }
          }
        }
      }

      if (!empty($dashboard_messages) && !homey_is_guide()) {
        echo '<li class="' . esc_attr($ac_msgs) . '">
                    <a href="' . esc_url($dashboard_messages) . '">' . $homey_local['m_messages_label'] . '
                    ' . homey_messages_notification() . '
                    </a>
                </li>';
      }

      /*
      if (!empty($dashboard_invoices) && !homey_is_guide()) {
        echo '<li class="' . esc_attr($ac_invoices) . '">
                    <a href="' . esc_url($dashboard_invoices) . '">' . $homey_local['m_invoices_label'] . '</a>
                </li>';
      } */

      if (homey_is_admin()) {
        if (!empty($all_users)) {
          echo '<li class="">
                        <a href="' . esc_url($all_users) . '">' . esc_html__('Users', 'homey') . '</a>
                    </li>';
        }
      }

      if (!empty($dashboard_favorites) && !homey_is_guide()) {
        echo '<li class="' . esc_attr($ac_fav) . '">
                    <a href="' . esc_url($dashboard_favorites) . '">' . $homey_local['m_favorites_label'] . '</a>
                </li>';
      }

      if (homey_is_renter()) {
        if (!empty($guest_reviews)) {
          echo '<li class="' . esc_attr($ac_guest_reviews) . '">
                                <a href="' . esc_url($guest_reviews) . '">' . esc_html__('Reviews', 'homey') . '</a>
                            </li>';
        }
      }

      if (!homey_is_renter()) {
        if (!empty($referral_program)) {
          echo '<li class="' . esc_attr($ac_referral_program) . '">
                    <a href="' . esc_url($referral_program) . '">' . esc_html__('Referral Program', 'homey') . '</a>
                </li>';
        }
      }


      echo '<li>
                <a href="' . wp_logout_url(home_url('/')) . '">' . $homey_local['m_logout_label'] . '</a>
            </li>';
      ?>

    </ul>
  </div>
</div>