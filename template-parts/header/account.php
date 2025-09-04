<?php
global $current_user, $homey_local;
wp_get_current_user();
$userID  =  $current_user->ID;
$first_name  =  $current_user->first_name;
$last_name  =  $current_user->last_name;
$display_name = empty(trim($current_user->display_name)) ? $current_user->user_nicename : $current_user->display_name;
$display_name_public    =   get_the_author_meta('display_name_public', $userID);
$display_name_public_guest = get_the_author_meta('display_name_public_guest', $userID);

$enable_wallet = homey_option('enable_wallet');


$homey_author = homey_get_author_by_id('36', '36', 'img-circle', $userID);
$author_photo = $homey_author['photo'];

$dashboard_membership = homey_get_template_link_dash('template/dashboard-membership-host.php');
$dashboard = homey_get_template_link_dash('template/dashboard.php');
$dashboard_profile = homey_get_template_link_dash('template/dashboard-profile.php');
$dashboard_listings = homey_get_template_link_dash('template/dashboard-listings.php');
$dashboard_add_listing = homey_get_template_link_dash('template/dashboard-submission.php');
$dashboard_favorites = homey_get_template_link_dash('template/dashboard-favorites.php');
$dashboard_search = homey_get_template_link_dash('template/dashboard-saved-searches.php');
$dashboard_reservations = homey_get_template_link_dash('template/dashboard-reservations.php');
$dashboard_host_reservations = homey_get_template_link_dash('template/dashboard-reservations2.php');
$dashboard_messages = homey_get_template_link_dash('template/dashboard-chat.php');
$dashboard_invoices = homey_get_template_link_dash('template/dashboard-invoices.php');

$notification_settings = homey_get_template_link_dash('template/notification-settings.php');
$featured = homey_get_template_link_dash('template/featured-info.php');
$guest_reviews = homey_get_template_link_dash('template/guest-reviews.php');
$create_coupon = homey_get_template_link_dash('template/create-coupon.php');
$all_coupons = homey_get_template_link_dash('template/all-coupons.php');
$referral_program = homey_get_template_link_dash('template/referral-program.php');


$dashboard_wallet = homey_get_template_link_dash('template/dashboard-wallet.php');
$earnings_page_link = add_query_arg('dpage', 'earnings', $dashboard_wallet);
$payout_request_link = add_query_arg('dpage', 'payout-request', $dashboard_wallet);
$payouts_page_link = add_query_arg('dpage', 'payouts', $dashboard_wallet);
$payouts_setup_page = add_query_arg('dpage', 'payment-method', $dashboard_wallet);

$all_users = add_query_arg('dpage', 'users', $dashboard);

$home_link = home_url('/');

$reservation_payment = homey_option('reservation_payment');

?>
<div class="account-loggedin no-cace-<?php echo strtotime("now"); ?>">

  <?php if (homey_is_renter()) {
    echo esc_html($display_name_public_guest);
  } else {
    echo esc_html($display_name_public);
  } ?>

  <span class="user-image">
    <?php echo homey_messages_notification('user-alert'); ?>
    <?php echo '' . $author_photo; ?>
  </span>
  <div class="account-dropdown">
    <ul>
      <?php
      if (!empty($dashboard)) {
        echo '<li><a href="' . esc_url($dashboard) . '"><i class="fa fa-home"></i>' . $homey_local['m_dashboard_label'] . '</a></li>';
      }

      if (!empty($dashboard_profile)) {
        echo '<li><a href="' . esc_url($dashboard_profile) . '"><i class="fa fa-user-o"></i>' . $homey_local['m_profile_label'] . '</a></li>';
      }

      if (!homey_is_renter() && !homey_is_guide()) {
        if (!empty($dashboard_listings)) {
          echo '<li><a href="' . esc_url($dashboard_listings) . '"><i class="fa fa-th-list"></i>' . $homey_local['m_listings_label'] . '</a></li>';
        }

        if (!empty($featured)) {
          echo '<li><a href="' . esc_url($featured) . '"><i class="fa-solid fa-web-awesome"></i>' . esc_html__('Featured', 'homey') . '</a></li>';
        }

        if (!empty($dashboard_add_listing)) {
          echo '<li><a href="' . esc_url($dashboard_add_listing) . '"><i class="fa fa-plus-circle"></i>' . $homey_local['m_add_listing_label'] . ' </a></li>';
        }

        if (!homey_is_renter() && !homey_is_admin() && in_array('homey-membership/homey-membership.php', apply_filters('active_plugins', get_option('active_plugins')))) {
          if (!empty($dashboard_membership)) {
            echo '<li>
                        <a href="' . esc_url($dashboard_membership) . '"><i class="fa fa-money"></i>' . esc_html__('Membership', 'homey') . '</a>
                    </li>';
          }
        }
      }

      if (!empty($dashboard_reservations) && !homey_is_guide()) {
        if (homey_is_renter()) {
          echo '<li><a href="' . esc_url($dashboard_reservations) . '"><i class="fa fa-calendar"></i>' . $homey_local['m_reservation_label'] . '</a></li>';
        } elseif (homey_is_admin()) {
          echo '<li><a href="' . esc_url($dashboard_reservations) . '"><i class="fa fa-calendar"></i>' . esc_html__('Bookings', 'homey') . '</a></li>';
        } else {
          $new_notification = homey_booking_notification(1);
          $new_notification = $new_notification > 0 ? '<span class="new-booking-alert" style="display: block;"></span>' : '<span class="new-booking-alert" style="display: none;"></span>';
          echo '<li><a href="' . esc_url($dashboard_reservations) . '"><i class="fa fa-calendar"></i>' . esc_html__('My Bookings', 'homey') . ' ' . $new_notification . '</a></li>';
        }
      }

      if (!empty($dashboard_host_reservations) && !homey_is_renter() && !homey_is_guide()) {
        echo '<li><a href="' . esc_url($dashboard_host_reservations) . '"><i class="fa fa-calendar"></i>' . esc_html__('My Reservations', 'homey') . '</a></li>';
      }

      if (!empty($notification_settings)) {
        echo '<li><a href="' . esc_url($notification_settings) . '"><i class="fa fa-bell"></i>' . esc_html__('Notification Settings', 'homey') . '</a></li>';
      }

      if (!homey_is_renter() && !homey_is_guide()) {
        if (!empty($create_coupon)) {
          echo '<li><a href="' . esc_url($create_coupon) . '"><i class="fa-solid fa-tag"></i>' . esc_html__('Create Coupon', 'homey') . '</a></li>';
        }

        if (!empty($all_coupons)) {
          echo '<li><a href="' . esc_url($all_coupons) . '"><i class="fa-solid fa-tags"></i>' . esc_html__('All Coupons', 'homey') . '</a></li>';
        }
      }

      if ($enable_wallet != 0) {
        if ($reservation_payment == 'percent' || $reservation_payment == 'full') {
          if (homey_is_host()) {
            if (!empty($dashboard_wallet)) {
              echo '<li><a href="' . esc_url($dashboard_wallet) . '"><i class="fa-solid fa-wallet"></i>' . esc_html__('Wallet', 'homey') . '</a></li>';
            }
          }

          // if (homey_is_renter()) {
          //   if (!empty($dashboard_wallet)) {
          //     echo '<li><a href="' . esc_url($dashboard_wallet) . '"><i class="fa fa-money"></i>' . esc_html__('Wallet', 'homey') . '</a></li>';
          //   }
          // }

          if (homey_is_admin()) {
            if (!empty($dashboard_wallet)) {
              echo '<li><a href="' . esc_url($payouts_page_link) . '"><i class="fa fa-money"></i>' . esc_html__('Payouts', 'homey') . '</a></li>';
            }
          }
        }
      }

      if (!empty($dashboard_favorites) && !homey_is_guide()) {
        echo '<li><a href="' . esc_url($dashboard_favorites) . '"><i class="fa fa-heart-o"></i>' . $homey_local['m_favorites_label'] . ' </a></li>';
      }

      // if (!empty($dashboard_invoices) && !homey_is_guide()) {
      //   echo '<li><a href="' . esc_url($dashboard_invoices) . '"><i class="fa fa-file"></i>' . $homey_local['m_invoices_label'] . ' </a></li>';
      // }

      if (homey_is_admin()) {
        if (!empty($all_users)) {
          echo '<li class=""><a href="' . esc_url($all_users) . '"><i class="fa fa-users"></i>' . esc_html__('Users', 'homey') . '</a></li>';
        }
      }


      if (!empty($dashboard_messages) && !homey_is_guide()) {
        echo '<li><a href="' . esc_url($dashboard_messages) . '"><i class="fa fa-comments-o"></i>' . $homey_local['m_messages_label'] . '
                    ' . homey_messages_notification() . '
                    </a></li>';
      }

      if (homey_is_renter()) {
        if (!empty($guest_reviews)) {
          echo '<li><a href="' . esc_url($guest_reviews) . '"><i class="fa fa-star"></i>' . esc_html__('Reviews', 'homey') . '</a></li>';
        }
      }

      if (!homey_is_renter() && !homey_is_guide()) {
        if (!empty($referral_program)) {
          echo '<li><a href="' . esc_url($referral_program) . '"><i class="fa fa-money"></i>' . esc_html__('Referral Program', 'homey') . '</a></li>';
        }
      }

      echo '<li><a href="' . wp_logout_url(home_url('/')) . '"><i class="fa fa-sign-out"></i>' . $homey_local['m_logout_label'] . ' </a></li>';
      ?>
    </ul>
  </div>
</div>