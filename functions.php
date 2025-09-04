<?php
add_action('wp_enqueue_scripts', 'load_select2_for_multiselect');
function load_select2_for_multiselect()
{
  wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
  wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);

  // Initialize Select2 (after DOM loads)
  wp_add_inline_script('select2-js', '
        jQuery(document).ready(function($) {
            $("#coupon-guests-select").select2({
                placeholder: "Search guests...",
                allowClear: true,
                width: "100%"
            });
        });
    ');
}

// Add custom column to the Users page
function custom_user_id_column($columns)
{
  unset($columns['user_id']);
  $column_keys = array_keys($columns);

  $columns = array_merge(
    array_slice($columns, 0, -1, true),
    array('user_id' => 'User ID'),
    array_slice($columns, -1, 1, true)
  );

  return $columns;
}
add_filter('manage_users_columns', 'custom_user_id_column');

// Display user ID in the custom column
function custom_user_id_column_content($value, $column_name, $user_id)
{
  if ('user_id' == $column_name) {
    return $user_id;
  }
  return $value;
}
add_action('manage_users_custom_column', 'custom_user_id_column_content', 10, 3);

function create_notification_post_type()
{
  register_post_type(
    'booking_notification',
    array(
      'labels' => array(
        'name' => __('Notifications'),
        'singular_name' => __('Notification'),
      ),
      'public' => false,
      'show_ui' => false,
      'supports' => array('title', 'editor', 'author'),
    )
  );
}

add_action('init', 'create_notification_post_type');

// Function to save booking request notification to the host
function save_booking_notification($host_id, $notification_title, $notification_content, $notification_link = '')
{
  $notification_data = array(
    'post_title' => $notification_title,
    'post_content' => $notification_content,
    'post_status' => 'publish',
    'post_type' => 'booking_notification',
    'post_author' => $host_id,
  );

  $notification_id = wp_insert_post($notification_data);
  if (!empty($notification_link)) {
    update_post_meta($notification_id, '_notification_link', $notification_link);
  }

  update_post_meta($notification_id, '_notification_status', 'unread');
}

// Function to get unread notifications for a host
function get_unread_notifications($host_id)
{
  $args = array(
    'post_type' => 'booking_notification',
    'post_status' => 'publish',
    'author' => $host_id,
    'meta_query' => array(
      array(
        'key' => '_notification_status',
        'value' => 'unread',
        'compare' => '=',
      ),
    ),
  );

  $notifications = get_posts($args);

  return $notifications;
}

function get_all_notifications($host_id)
{
  $args = array(
    'post_type' => 'booking_notification',
    'post_status' => 'publish',
    'author' => $host_id,
  );

  $notifications = get_posts($args);
  return $notifications;
}

add_action('wp_ajax_remove_notification', 'remove_notification');
add_action('wp_ajax_nopriv_remove_notification', 'remove_notification');
function remove_notification()
{
  $notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;

  if ($notification_id > 0) {
    wp_delete_post($notification_id, true);
  }

  echo json_encode(array('success' => true));
  wp_die();
}

add_action('wp_ajax_clear_all_notifications', 'clear_all_notifications');
add_action('wp_ajax_nopriv_clear_all_notifications', 'clear_all_notifications');
function clear_all_notifications()
{
  $host_id = get_current_user_id();
  $notifications = get_all_notifications($host_id);

  foreach ($notifications as $notification) {
    wp_delete_post($notification->ID, true);
  }

  echo json_encode(array('success' => true));
  wp_die();
}

function mark_notification_as_read()
{
  $notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;
  if ($notification_id > 0) {
    update_post_meta($notification_id, '_notification_status', 'read');
  }

  echo json_encode(array('success' => true));
  wp_die();
}
add_action('wp_ajax_mark_notification_as_read', 'mark_notification_as_read');
add_action('wp_ajax_nopriv_mark_notification_as_read', 'mark_notification_as_read');

// Add State Sales Tax menu page
function state_sales_tax_menu_page()
{
  add_menu_page(
    'State Sales Tax',
    'State Sales Tax',
    'manage_options',
    'state_sales_tax_settings',
    'state_sales_tax_page'
  );
}
add_action('admin_menu', 'state_sales_tax_menu_page');

// Register State Sales Tax settings
function state_sales_tax_settings_init()
{
  $states = array(
    'Alabama',
    'Alaska',
    'Arizona',
    'Arkansas',
    'California',
    'Colorado',
    'Connecticut',
    'Delaware',
    'Florida',
    'Georgia',
    'Hawaii',
    'Idaho',
    'Illinois',
    'Indiana',
    'Iowa',
    'Kansas',
    'Kentucky',
    'Louisiana',
    'Maine',
    'Maryland',
    'Massachusetts',
    'Michigan',
    'Minnesota',
    'Mississippi',
    'Missouri',
    'Montana',
    'Nebraska',
    'Nevada',
    'New Hampshire',
    'New Jersey',
    'New Mexico',
    'New York',
    'North Carolina',
    'North Dakota',
    'Ohio',
    'Oklahoma',
    'Oregon',
    'Pennsylvania',
    'Rhode Island',
    'South Carolina',
    'South Dakota',
    'Tennessee',
    'Texas',
    'Utah',
    'Vermont',
    'Virginia',
    'Washington',
    'West Virginia',
    'Wisconsin',
    'Wyoming'
  );
  foreach ($states as $state) {
    $state_option_name = 'state_tax_rate_' . sanitize_title($state);
    register_setting('state_sales_tax_group', $state_option_name);
  }
}
add_action('admin_init', 'state_sales_tax_settings_init');

function state_sales_tax_page()
{
?>
  <div class="wrap">
    <h1>State Sales Tax Settings</h1>
    <?php settings_errors(); ?>
    <form method="post" action="options.php">
      <?php

      settings_fields('state_sales_tax_group');
      do_settings_sections('state_sales_tax_settings');
      submit_button();
      ?>
    </form>
  </div>
<?php
}

function state_tax_rate_field_callback($args)
{
  $state_option_name = $args['label_for'];
  $state_tax_rate = get_option($state_option_name);
?>
  <input type="number" step="0.01" name="<?php echo esc_attr($state_option_name); ?>"
    id="<?php echo esc_attr($state_option_name); ?>" value="<?php echo esc_attr($state_tax_rate); ?>" />
  <?php
}

function state_sales_tax_settings_init_sections()
{
  $states = array(
    'Alabama',
    'Alaska',
    'Arizona',
    'Arkansas',
    'California',
    'Colorado',
    'Connecticut',
    'Delaware',
    'Florida',
    'Georgia',
    'Hawaii',
    'Idaho',
    'Illinois',
    'Indiana',
    'Iowa',
    'Kansas',
    'Kentucky',
    'Louisiana',
    'Maine',
    'Maryland',
    'Massachusetts',
    'Michigan',
    'Minnesota',
    'Mississippi',
    'Missouri',
    'Montana',
    'Nebraska',
    'Nevada',
    'New Hampshire',
    'New Jersey',
    'New Mexico',
    'New York',
    'North Carolina',
    'North Dakota',
    'Ohio',
    'Oklahoma',
    'Oregon',
    'Pennsylvania',
    'Rhode Island',
    'South Carolina',
    'South Dakota',
    'Tennessee',
    'Texas',
    'Utah',
    'Vermont',
    'Virginia',
    'Washington',
    'West Virginia',
    'Wisconsin',
    'Wyoming'
  );
  foreach ($states as $state) {
    $state_option_name = 'state_tax_rate_' . sanitize_title($state);
    add_settings_section(
      $state_option_name . '_section',
      $state,
      '',
      'state_sales_tax_settings'
    );
    add_settings_field(
      $state_option_name,
      'Tax Rate',
      'state_tax_rate_field_callback',
      'state_sales_tax_settings',
      $state_option_name . '_section',
      array('label_for' => $state_option_name)
    );
  }
}
add_action('admin_init', 'state_sales_tax_settings_init_sections');


function send_verification_email_after_registration($user_id)
{
  $user_info = get_userdata($user_id);
  $user_email = $user_info->user_email;

  send_verification_email($user_id, $user_email);
}

add_action('user_register', 'send_verification_email_after_registration', 10, 1);

function send_verification_email($user_id, $user_email)
{
  $verification_key = md5(time() . $user_email);
  update_user_meta($user_id, 'verification_key', $verification_key);

  $verification_link = home_url("/verify-email/?key=$verification_key");
  $subject = 'Verify Your Email Address';
  $message = "Click the following link to verify your email address: <a href='$verification_link'>$verification_link</a>";
  $headers = array('Content-Type: text/html; charset=UTF-8');
  wp_mail($user_email, $subject, $message, $headers);
  update_user_meta($user_id, 'email_verified', false);
}

function handle_email_verification()
{
  if (isset($_GET['key'])) {
    global $wpdb;

    $verification_key = sanitize_text_field($_GET['key']);
    $user_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'verification_key' AND meta_value = %s", $verification_key));

    if ($user_id) {
      update_user_meta($user_id, 'email_verified', true);
      verify_user_status($user_id);
      echo "<strong>Your email is verified Now.</strong>";
      wp_redirect(home_url('/profile/?dpage=verification'));
      exit;
    } else {
      echo "<strong>Your Email is not verified.</strong>";
      //wp_redirect(home_url('/profile/?dpage=verification'));
      exit;
    }
  }
}

function custom_validate_registration_form($errors, $sanitized_user_login, $user_email)
{
  if (strlen($_POST['register_pass']) < 8) {
    $errors->add('password_length', __('Password should be at least 8 characters long.', 'textdomain'));
  }

  if (!preg_match('/[A-Z]/', $_POST['register_pass'])) {
    $errors->add('password_caps', __('Password should contain at least one uppercase letter.', 'textdomain'));
  }

  if (!preg_match('/[0-9]/', $_POST['register_pass'])) {
    $errors->add('password_number', __('Password should contain at least one number.', 'textdomain'));
  }

  if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $_POST['register_pass'])) {
    $errors->add('password_symbol', __('Password should contain at least one special character.', 'textdomain'));
  }

  return $errors;
}

add_filter('registration_errors', 'custom_validate_registration_form', 10, 3);

function enqueue_stripe_scripts()
{
  wp_enqueue_script('stripe', 'https://js.stripe.com/v3/', [], null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_stripe_scripts');


function homey_enqueue_child_styles()
{

  // enqueue parent styles
  wp_enqueue_style('homey-parent-theme', get_template_directory_uri() . '/style.css');

  // enqueue child styles
  wp_enqueue_style('homey-child-theme', get_stylesheet_directory_uri() . '/style.css', array('homey-parent-theme'));
}
add_action('wp_enqueue_scripts', 'homey_enqueue_child_styles');

function haversine_distance_km($lat1, $lon1, $lat2, $lon2)
{
  $earth_radius = 6371; // Earth radius in kilometers

  $dlat = deg2rad($lat2 - $lat1);
  $dlon = deg2rad($lon2 - $lon1);

  $a = sin($dlat / 2) * sin($dlat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dlon / 2) * sin($dlon / 2);
  $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

  $distance = $earth_radius * $c;

  return $distance;
}

function haversine_distance_mi($lat1, $lon1, $lat2, $lon2)
{
  $earth_radius_mi = 3959; // Earth radius in miles

  $dlat = deg2rad($lat2 - $lat1);
  $dlon = deg2rad($lon2 - $lon1);

  $a = sin($dlat / 2) * sin($dlat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dlon / 2) * sin($dlon / 2);
  $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

  // Calculate distance in miles
  $distance_mi = $earth_radius_mi * $c;

  return $distance_mi;
}

/**
 * Enqueue scripts.
 */
function jsltheme_scripts()
{
  global $current_user;
  $userID = $current_user->ID;
  wp_dequeue_style('fullcalendar-core');
  wp_dequeue_style('fullcalendar-daygrid');
  wp_dequeue_style('fullcalendar-timegrid');

  wp_dequeue_script('fullcalendar-core');
  wp_dequeue_script('fullcalendar-local-all');
  wp_dequeue_script('fullcalendar-daygrid');
  wp_dequeue_script('fullcalendar-timegrid');

  //wp_deregister_script('homey-listing');
  wp_deregister_script('homey-profile');

  wp_enqueue_style('font-awesome-6', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css');

  // Enqueue FullCalendar CSS
  wp_enqueue_style('fullcalendar-css', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css', array(), '5.11.5');

  // Enqueue Leaflet.markercluster CSS
  wp_enqueue_style('leaflet-markercluster-css', 'https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css');
  wp_enqueue_style('leaflet-markercluster-default-css', 'https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css');

  wp_enqueue_script('homey-listing-jsl', get_stylesheet_directory_uri() . '/js/homey-listing.js', array('jquery', 'plupload', 'jquery-ui-sortable'), '5.4', true);

  if (is_page_template('template/dashboard-profile.php') || homey_is_dashboard()) {
    wp_register_script('homey-profile-jsl', get_stylesheet_directory_uri() . '/js/homey-profile.js', array('jquery', 'plupload'), '5.8', true);
    $profile_data = array(
      'ajaxURL' => admin_url('admin-ajax.php'),
      'user_id' => $userID,
      'homey_upload_nonce' => wp_create_nonce('homey_upload_nonce'),
      'verify_file_type' => esc_html__('Valid file formats', 'homey'),
      'homey_site_url' => esc_url(home_url()),
      'process_loader_refresh' => 'fa fa-spin fa-refresh',
      'process_loader_spinner' => 'fa fa-spin fa-spinner',
      'process_loader_circle' => 'fa fa-spin fa-circle-o-notch',
      'process_loader_cog' => 'fa fa-spin fa-cog',
      'success_icon' => 'fa fa-check',
      'processing_text' => esc_html__('Processing, Please wait...', 'homey'),
      'gdpr_agree_text' => esc_html__('Please Agree with GDPR', 'homey'),
      'sending_info' => esc_html__('Sending info', 'homey'),
    );
    wp_localize_script('homey-profile-jsl', 'homeyProfile', $profile_data);
    wp_enqueue_script('homey-profile-jsl');
  }

  wp_enqueue_script('jsltheme-site', get_stylesheet_directory_uri() . '/js/byl-custom.js', array('jquery'), '10.9', true);

  // Enqueue Leaflet.markercluster JavaScript
  wp_enqueue_script('leaflet-markercluster-js', 'https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js', array('leaflet'), null, true);

  // Enqueue FullCalendar JS
  wp_enqueue_script('moment-js', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js', array(), '2.30.1', true);
  wp_enqueue_script('fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js', array('jquery'), '5.11.5', true);

  if (is_page_template('template/dashboard-submission.php')) {
    wp_enqueue_script(
      'calendar-script',
      get_stylesheet_directory_uri() . '/js/calendar.js',
      array('jquery'),
      filemtime(get_stylesheet_directory() . '/js/calendar.js'),
      true
    );

    // Localize script for edit-calendar.php
    if (isset($_GET['edit_listing'])) {
      $booked_hours_array2 = $pending_hours_array2 = $completed_hours_array2 = $booking_start_hour2 = $booking_end_hour2 = array();

      if (is_rtl()) {
        $homey_rtl = "yes";
      } else {
        $homey_rtl = "no";
      }

      $edit_listing_id = isset($_GET['edit_listing']) ? $_GET['edit_listing'] : '';
      $homey_local = homey_get_localization();

      if (!empty($edit_listing_id)) {
        $edit_listing_id = intval(trim($edit_listing_id));

        $booking_start_hour2 = get_post_meta($edit_listing_id, 'homey_start_hour', true);
        $booking_end_hour2 = get_post_meta($edit_listing_id, 'homey_end_hour', true);
        $booked_hours_array2 = homey_get_booked_hours_slots($edit_listing_id);
        $pending_hours_array2 = homey_get_pending_hours_slots($edit_listing_id);
        $completed_hours_array2 = homey_get_completed_hours_slots($edit_listing_id);

        $prep_time = get_field('how_much_time', $edit_listing_id);
        if (empty($prep_time)) {
          $prep_time = 0;
        }

        if (empty($booking_start_hour2)) {
          $booking_start_hour2 = '01:00';
        }

        if (empty($booking_end_hour2)) {
          $booking_end_hour2 = '24:00';
        }
      }

      $instances_data = get_post_meta($edit_listing_id, '_listing_calendar_instances', true);

      if ($instances_data) {
        foreach ($instances_data as &$instance) {
          // Ensure selected_time_slots is always an array when passing to JS
          if (!isset($instance['selected_time_slots']) || !is_array($instance['selected_time_slots'])) {
            $instance['selected_time_slots'] = [];
          }

          // Convert selected_dates to string if it's an array
          if (is_array($instance['selected_dates'])) {
            $instance['selected_dates'] = implode(',', $instance['selected_dates']);
          }
        }
      }

      $homey_current_lang = get_locale();
      $homey_current_lang = explode('_', $homey_current_lang);

      $listing_data = array(
        'instances' => $instances_data ?: [],
        'booked_hours_array' => json_encode($booked_hours_array2),
        'pending_hours_array' => json_encode($pending_hours_array2),
        'completed_hours_array' => json_encode($completed_hours_array2),
        'booking_start_hour' => $booking_start_hour2,
        'prep_time' => $prep_time,
        'booking_end_hour' => $booking_end_hour2,
        'hc_reserved_label' => $homey_local['hc_reserved_label'],
        'hc_completed_label' => 'Completed',
        'hc_pending_label' => $homey_local['hc_pending_label'],
        'hc_hours_label' => $homey_local['hc_hours_label'],
        'hc_today_label' => $homey_local['hc_today_label'],
        'homey_is_rtl' => $homey_rtl,
        'homey_current_lang' => $homey_current_lang,
        'homey_timezone' => get_option('timezone_string'),
      );

      wp_localize_script(
        'calendar-script',
        'Listing_Calendar',
        $listing_data
      );
    } else {
      // Pass an empty array if not on the edit-calendar page
      wp_localize_script(
        'calendar-script',
        'Listing_Calendar',
        array()
      );
    }
  }

  wp_enqueue_script(
    'moment-timezone',
    'https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data.min.js',
    array('moment-js'),
    '0.5.34',
    true
  );
}
add_action('wp_enqueue_scripts', 'jsltheme_scripts', 20);

/**
 * Add font awesome to dashboarad.
 */
function fontawesome_dashboard()
{
  wp_enqueue_style('fontawesome', 'https://use.fontawesome.com/releases/v5.8.1/css/all.css', '', '5.8.1', 'all');
}
add_action('admin_init', 'fontawesome_dashboard');



/**
 * Reviews Query
 */
function get_reviews_query($per_page = 3)
{
  $args = [
    'post_type' => 'homey_review',
    'posts_per_page' => $per_page,
    'post_status' => ['public', 'publish'],
    'paged' => get_query_var('paged') ? get_query_var('paged') : 1
  ];
  return new WP_Query($args);
}

/**
 * Register dashboard widgets
 */
function jsl_custom_dashboard_widgets()
{
  global $wp_meta_boxes;

  wp_add_dashboard_widget('custom_help_widget', 'Backyard Lease Reviews', 'custom_dashboard_reviews');
}

/**
 * Reviews Dashboard Widget
 */

function custom_dashboard_reviews()
{

  $reviews = get_reviews_query();
  if ($reviews->have_posts()):
    while ($reviews->have_posts()):
      $reviews->the_post();
      $meta = get_post_meta(get_the_id());
      $listing = get_post($meta['reservation_listing_id'][0]);
      $rating = $meta['homey_rating'][0];
  ?>
      <div class="resource">
        <div class="content">
          <div class="title">
            <h3 style="font-weight: bold;">
              <?php if (null !== $listing):
                echo $listing->post_title;
              else:
                echo 'Listing Deleted';
              endif; ?>
            </h3>
            <?php echo homey_get_review_stars($rating, false, false); ?>
          </div>
          <div>
            <?php the_content(); ?>
          </div>
        </div>

      </div>
    <?php
    endwhile;
    wp_reset_postdata(); ?>
  <?php
  endif;
  ?>
  <a href="<?php echo home_url(); ?>/admin-reviews" class="button button-primary">All Reviews</a>
<?php

}

add_action('wp_dashboard_setup', 'jsl_custom_dashboard_widgets');

/**
 * Redirect to homepage if is reviews page.
 */
function redirect_to_home()
{
  if (!current_user_can('administrator') && is_page(4894)) {
    wp_redirect(home_url());
    exit();
  }
}
add_action('template_redirect', 'redirect_to_home');


/**
 * Check if host by name.
 */
if (!function_exists('homey_is_host_by_name')) {
  function homey_is_host_by_name($name)
  {
    global $current_user;
    $current_user = get_user_by('login', $name);

    if (in_array('homey_host', (array) $current_user->roles) || in_array('author', (array) $current_user->roles)) {
      return true;
    }
    return false;
  }
}

/**
 * Check if booked, if not cancel due to 48 hour cancellation policy.
 */
add_action('schedule_reservation_check', function ($id) {
  $reservation_status = get_post_meta($id, 'reservation_status', true);
  if ('booked' == $reservation_status || 'available' == $reservation_status) {
    return;
  }
  $reason = 'The host did not approve your Backyard Lease within 48 hours.';
  // Set reservation status from under_review to available
  update_post_meta($id, 'reservation_status', 'declined');
  update_post_meta($id, 'res_decline_reason', $reason);
});

/**
 * Set cron job to run in 48 hours.
 */
function decline_reservation_after_2_days($id)
{
  $reservation = get_post($id);
  $date_requested = $reservation->post_date;
  $date = new DateTime($date_requested);
  $date_expire = $date->modify(' + 2 minutes');
  $args = array($id);

  wp_schedule_single_event($date_expire->getTimestamp(), 'schedule_reservation_check', $args, true);
}


/**
 * Get all favorites to list on dashboard for hosts.
 */
function get_favorites_summary_for_host($hostID)
{
  // Get all users
  $all_users = get_users();
  $favorites = array(); // Store favorites with user IDs

  // Loop through each user
  foreach ($all_users as $user) {
    $fav_option = 'homey_favorites-' . $user->ID;
    $fav_ids = get_option($fav_option);

    // If the user has favorited listings
    if (!empty($fav_ids) && is_array($fav_ids)) {
      foreach ($fav_ids as $listing_id) {
        // Store the user ID for each favorite listing
        $favorites[$listing_id][] = $user->ID;
      }
    }
  }

  // Get listings from current host
  $args = array(
    'post_type' => 'listing',
    'author' => $hostID,
    'orderby' => 'post_date',
    'order' => 'ASC',
    'posts_per_page' => -1
  );

  $listings = get_posts($args);
  $result = array(); // To store the result
  $total_favorites_count = 0; // Total count of favorites for all listings

  // Loop through host's listings and gather favorite information
  foreach ($listings as $listing) {
    $listing_id = $listing->ID;

    // Initialize or get favorites for this listing
    $favorites_for_listing = isset($favorites[$listing_id]) ? $favorites[$listing_id] : array();

    // Convert user IDs to usernames
    $usernames = array();
    foreach ($favorites_for_listing as $user_id) {
      $user_info = get_userdata($user_id);
      $usernames[] = $user_info ? $user_info->user_login : 'Unknown User';
    }

    $result[$listing_id] = array(
      'listing_id' => $listing_id,
      'listing_title' => get_the_title($listing_id),
      'favorited_by' => $usernames,
    );

    // Add to total count
    $total_favorites_count += count($favorites_for_listing);
  }

  // Add total favorites count to the result
  $result['total_favorites'] = $total_favorites_count;

  return $result;
}




/**
 * Make sure there is a '' value for acf field when listing post is saved.
 * This allows for the key to show up in database.
 */
function approve_video_acf_save_post($post_id)
{

  // https://www.youtube.com/watch?v=EAV6J6hE9rk
  if ('listing' != get_post_type($post_id)) {
    return;
  }
  $video_url = homey_get_listing_data_by_id('video_url', $post_id);
  // https://www.youtube.com/watch?v=DXUAyRRkI6k
  $approve_video = get_field('approve_video', $post_id);
  if ('Approved' == $approve_video || 'Not Approved' == $approve_video) {
    if (isset($_POST['video_url']) && $_POST['video_url'] != $video_url) {
      update_field('field_63c19fbf69f45', '', $post_id);
      return;
    }
    return;
  }
  update_field('field_63c19fbf69f45', '', $post_id);
}
add_action('save_post', 'approve_video_acf_save_post', 25);

/**
 * Ajax button that grabs data after approve button is clicked and then save to database.
 */
function jsl_approve_video()
{
  $listing_id = intval($_POST['listing_id']);
  $approval = intval($_POST['approval']);
  update_field('field_63c19fbf69f45', $approval, $listing_id);
}

add_action('wp_ajax_jsl_approve_video', 'jsl_approve_video');


/**
 * Listing Page: Special features - save from front end.
 */
function listing_special_features_acf_save_post($post_id)
{

  // die(print_r($_FILES));

  if ('listing' != get_post_type($post_id)) {
    return;
  }

  if (!isset($_POST))
    return;


  if (isset($_FILES['gallery_images'])) {
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $gallery_images = $_FILES['gallery_images'];
    if (!empty($gallery_images)) {
      $attachment_ids = array();

      foreach ($gallery_images['name'] as $key => $image_name) {



        if ($gallery_images['error'][$key] === 0) {
          // $uploaded_file = wp_handle_upload($gallery_images['tmp_name'][$key]);
          $uploaded_file = wp_upload_bits($image_name, null, file_get_contents($gallery_images['tmp_name'][$key]));



          if ($uploaded_file) {
            $attachment = array(
              'post_title' => sanitize_file_name($image_name),
              'post_content' => '',
              'post_status' => 'inherit',
              'post_mime_type' => $uploaded_file['type']
            );

            $attachment_id = wp_insert_attachment($attachment, $uploaded_file['file']);

            if (!is_wp_error($attachment_id)) {
              $attachment_data = wp_generate_attachment_metadata($attachment_id, $uploaded_file['file']);
              wp_update_attachment_metadata($attachment_id, $attachment_data);

              $attachment_ids[] = $attachment_id;
            }
          }
        }
      }

      // Save the attachment IDs to the ACF field
      update_field('field_6479f394ee87b', $attachment_ids, $post_id); // Replace 'gallery_field_name' with the actual field name and 'post_id' with the desired post ID.

    }
  }

  if (isset($_FILES['acc_images'])) {
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $acc_images = $_FILES['acc_images'];
    if (!empty($acc_images)) {
      $attachment_ids = array();

      foreach ($acc_images['name'] as $key => $image_name) {



        if ($acc_images['error'][$key] === 0) {
          // $uploaded_file = wp_handle_upload($acc_images['tmp_name'][$key]);
          $uploaded_file = wp_upload_bits($image_name, null, file_get_contents($acc_images['tmp_name'][$key]));



          if ($uploaded_file) {
            $attachment = array(
              'post_title' => sanitize_file_name($image_name),
              'post_content' => '',
              'post_status' => 'inherit',
              'post_mime_type' => $uploaded_file['type']
            );

            $attachment_id = wp_insert_attachment($attachment, $uploaded_file['file']);

            if (!is_wp_error($attachment_id)) {
              $attachment_data = wp_generate_attachment_metadata($attachment_id, $uploaded_file['file']);
              wp_update_attachment_metadata($attachment_id, $attachment_data);

              $attachment_ids[] = $attachment_id;
            }
          }
        }
      }

      // Save the attachment IDs to the ACF field
      update_field('sleep_acc_imgs', $attachment_ids, $post_id);
    }
  }

  if (null != $_POST['special_feature']):
    $special_features = $_POST['special_feature'];
    $new_special_featues = array();
    foreach ($special_features as $key => $feature):
      $new_special_featues[] = ['feature' => $feature['name']];
    endforeach;
    update_field('field_6479250ae9990', $new_special_featues, $post_id);
  endif;

  if (null != $_POST['day-of-booking']):
    update_field('field_6479e8adec72b', $_POST['day-of-booking'], $post_id);
  endif;

  if (null != $_POST['more-enjoyable-experience']):
    update_field('field_6479e8c7418f9', $_POST['more-enjoyable-experience'], $post_id);
  endif;

  if (null != $_POST['included-with-booking']):
    update_field('field_6479e92a1cff1', $_POST['included-with-booking'], $post_id);
  endif;

  if (null != $_POST['restroom-access']):
    update_field('restroom_access', $_POST['restroom-access'], $post_id);
  endif;

  if (null != $_POST['how-private']):
    update_field('field_6479e99854f4e', $_POST['how-private'], $post_id);
  endif;

  if (null != $_POST['car-amount']):
    update_field('field_6479e9b2fd2c3', $_POST['car-amount'], $post_id);
  endif;

  if (null != $_POST['extra-car-allow']):
    update_field('field_6707ba502807e', $_POST['extra-car-allow'], $post_id);
  endif;

  if (null != $_POST['cost-per-additional-car']):
    update_field('field_6479e9ea51f0c', $_POST['cost-per-additional-car'], $post_id);
  endif;

  if (null != $_POST['special-details-booking']):
    update_field(
      'field_6479ea3662296',
      $_POST['special-details-booking'],
      $post_id
    );
  endif;

  if (null != $_POST['present-on-property']):
    update_field('field_6479ea57ae6ac', $_POST['present-on-property'], $post_id);
  endif;

  if (null != $_POST['security-cameras']):
    update_field('field_6479ea7ee287b', $_POST['security-cameras'], $post_id);
  endif;

  if (null != $_POST['camera-amount']):
    update_field('field_6479ea94942bc', $_POST['camera-amount'], $post_id);
  endif;

  if (null != $_POST['camera-locations']):
    update_field('field_6479ead3f25a9', $_POST['camera-locations'], $post_id);
  endif;

  if (null != $_POST['how-much-notice']):
    update_field('field_6479eae7ad081', $_POST['how-much-notice'], $post_id);
  endif;

  if (null != $_POST['how-far-in-advance']):
    update_field('field_6479eb074f6e1', $_POST['how-far-in-advance'], $post_id);
  endif;

  if (null != $_POST['how-much-time']):
    update_field('field_6479eb1b5c526', $_POST['how-much-time'], $post_id);
  endif;

  if (null != $_POST['sub_cat_type']):
    update_field('field_6479eb70249d7', $_POST['sub_cat_type'], $post_id);
  endif;

  if (null != $_POST['have_sleeping_accommodations']):
    update_field('field_6479eb9f0208c', $_POST['have_sleeping_accommodations'], $post_id);
  endif;

  if (null != $_POST['include_backyard_amenity']):
    update_field('include_backyard_amenity', $_POST['include_backyard_amenity'], $post_id);
  endif;

  if (null != $_POST['how_many_bedrooms']):
    update_field('how_many_bedrooms', $_POST['how_many_bedrooms'], $post_id);
  endif;

  if (null != $_POST['how_many_bathrooms']):
    update_field('field_6479ec0f536c1', $_POST['how_many_bathrooms'], $post_id);
  endif;

  if (null != $_POST['number_of_guests']):
    update_field('field_6479ec324f322', $_POST['number_of_guests'], $post_id);
  endif;

  if (null != $_POST['features_sleeping']):
    update_field('field_6479ec5012841', $_POST['features_sleeping'], $post_id);
  endif;

  if (null != $_POST['how_many_beds']):
    update_field('field_6479ebf2fc2e3', $_POST['how_many_beds'], $post_id);
  endif;

  if (null != $_POST['price_per_night']):
    update_field('field_6479ec8dfe126', $_POST['price_per_night'], $post_id);
  endif;

  if (null != $_POST['cleaning_fee']):
    update_field('field_6479ecbf61005', $_POST['cleaning_fee'], $post_id);
  endif;

  if (null != $_POST['accommodations_desc']):
    update_field('field_6479ecf2f88fb', $_POST['accommodations_desc'], $post_id);
  endif;

  if (null != $_POST['acc_smoke']):
    update_field('smoking_allowed', $_POST['acc_smoke'], $post_id);
  endif;

  if (null != $_POST['acc_pets']):
    update_field('pets_allowed', $_POST['acc_pets'], $post_id);
  endif;

  if (null != $_POST['acc_rules']):
    update_field('prohibited_things', $_POST['acc_rules'], $post_id);
  endif;

  if (null != $_POST['have_guided_service']):
    update_field('have_guided_service', $_POST['have_guided_service'], $post_id);
  endif;

  if (null != $_POST['guide_bio']):
    update_field('guide_bio', $_POST['guide_bio'], $post_id);
  endif;

  if (null != $_POST['what_expect']):
    update_field('what_expect', $_POST['what_expect'], $post_id);
  endif;

  if (null != $_POST['price_type']):
    update_field('price_type', $_POST['price_type'], $post_id);
  endif;

  if (null != $_POST['price_rate']):
    update_field('price_rate', $_POST['price_rate'], $post_id);
  endif;

  if (null != $_POST['guided_price']):
    update_field('guided_price', $_POST['guided_price'], $post_id);
  endif;

  if (null != $_POST['what_permitted']):
    update_field('what_permitted', $_POST['what_permitted'], $post_id);
  endif;

  if (null != $_POST['what_not_permitted']):
    update_field('what_not_permitted', $_POST['what_not_permitted'], $post_id);
  endif;

  if (null != $_POST['who_permitted']):
    update_field('who_permitted', $_POST['who_permitted'], $post_id);
  endif;

  if (null != $_POST['who_not_permitted']):
    update_field('who_not_permitted', $_POST['who_not_permitted'], $post_id);
  endif;

  if (null != $_POST['license_required']):
    update_field('license_required', $_POST['license_required'], $post_id);
  endif;

  if (null != $_POST['guest_provide']):
    update_field('guest_provide', $_POST['guest_provide'], $post_id);
  endif;

  if (null != $_POST['guest_wear']):
    update_field('guest_wear', $_POST['guest_wear'], $post_id);
  endif;

  if (null != $_POST['maximum_guests']):
    update_field('maximum_guests', $_POST['maximum_guests'], $post_id);
  endif;

  if (null != $_POST['non_participants']):
    update_field('non_participants', $_POST['non_participants'], $post_id);
  endif;

  if (null != $_POST['non_participants_price']):
    update_field('non_participants_price', $_POST['non_participants_price'], $post_id);
  endif;

  if (isset($_POST['have_occupancy_tax'])) {
    $have_occupancy_tax_value = $_POST['have_occupancy_tax'] ? true : false;
    update_field('have_occupancy_tax', $have_occupancy_tax_value, $post_id);
  } else {
    update_field('have_occupancy_tax', false, $post_id);
  }


  if (null != $_POST['business_tax_id']):
    update_field('business_tax_id', $_POST['business_tax_id'], $post_id);
  endif;

  if (null != $_POST['acc_tax_num']):
    update_field('accommodation_tax_number', $_POST['acc_tax_num'], $post_id);
  endif;

  if (null != $_POST['legal_ein_name']):
    update_field('legal_ein_name', $_POST['legal_ein_name'], $post_id);
  endif;

  if (null != $_POST['occ_state']):
    update_field('current_occupancy_state', $_POST['occ_state'], $post_id);
  endif;

  if (null != $_POST['occ_city']):
    update_field('current_occupancy_city', $_POST['occ_city'], $post_id);
  endif;

  if (null != $_POST['occ_tax_rate']):
    update_field('occupancy_tax_rate', $_POST['occ_tax_rate'], $post_id);
  endif;

  if (isset($_POST['agreed_disclaimer'])) {
    $agreed_disclaimer = $_POST['agreed_disclaimer'] ? true : false;
    update_field('agreed_disclaimer', $agreed_disclaimer, $post_id);
  } else {
    update_field('agreed_disclaimer', false, $post_id);
  }

  if (isset($_POST['not_apply'])) {
    $not_apply = $_POST['not_apply'] ? true : false;
    update_field('not_apply', $not_apply, $post_id);
  } else {
    update_field('not_apply', false, $post_id);
  }

  if (null != $_POST['select_cancellation']):
    update_field('select_cancellation', $_POST['select_cancellation'], $post_id);
  endif;

  if (null != $_POST['amenity_price_type']):
    update_field('amenity_price_type', $_POST['amenity_price_type'], $post_id);
  endif;

  if (null != $_POST['checkin_first_half_after']):
    update_field('first_half_start_hour', $_POST['checkin_first_half_after'], $post_id);
  endif;

  if (null != $_POST['checkout_first_half_before']):
    update_field('first_half_end_hour', $_POST['checkout_first_half_before'], $post_id);
  endif;

  if (null != $_POST['checkin_second_half_after']):
    update_field('second_half_start_hour', $_POST['checkin_second_half_after'], $post_id);
  endif;

  if (null != $_POST['checkout_second_half_before']):
    update_field('second_half_end_hour', $_POST['checkout_second_half_before'], $post_id);
  endif;


  // Retrieve and sanitize the data from the $_POST array
  $equipment_names = isset($_POST['equipment_name']) ? array_map('sanitize_text_field', $_POST['equipment_name']) : array();
  $equipment_prices = isset($_POST['equipment_price']) ? array_map('sanitize_text_field', $_POST['equipment_price']) : array();
  $equipment_types = isset($_POST['equipment_type']) ? array_map('sanitize_text_field', $_POST['equipment_type']) : array();

  // Combine data into an array of arrays
  $equipment_data = array();
  for ($i = 0; $i < count($equipment_names); $i++) {
    $equipment_data[] = array(
      'equipment_name' => $equipment_names[$i],
      'equipment_price' => $equipment_prices[$i],
      'equipment_type' => $equipment_types[$i]
    );
  }

  // Update ACF repeater field with the data
  update_field('equipments_rows', $equipment_data, $post_id);

  // Save the instances data
  if (isset($_POST['instances'])) {
    $instances = $_POST['instances'];
    $sanitized_instances = array();

    foreach ($instances as $instanceId => $instanceData) {
      // Initialize the array for this instance
      $sanitized_instance = array(
        'selected_dates' => '',
        'selected_time_slots' => array(),
        'amenity' => '',
        'sleeping' => '',
        'gservice' => ''
      );

      // Sanitize selected dates
      if (!empty($instanceData['selected_dates'])) {
        $sanitized_instance['selected_dates'] = sanitize_text_field($instanceData['selected_dates']);
      }

      // Sanitize time slots - handle both JSON string and array
      if (!empty($instanceData['selected_time_slots'])) {
        $time_slots = $instanceData['selected_time_slots'];

        // If it's a JSON string, decode it
        if (is_string($time_slots)) {
          $decoded = json_decode(stripslashes($time_slots), true);
          if (is_array($decoded)) {
            $time_slots = $decoded;
          } else {
            $time_slots = array();
          }
        }

        // Sanitize each time slot
        if (is_array($time_slots)) {
          foreach ($time_slots as $slot) {
            $sanitized_slot = array(
              'time' => isset($slot['time']) ? sanitize_text_field($slot['time']) : '',
              'timeUnix' => isset($slot['timeUnix']) ? intval($slot['timeUnix']) : 0,
              'dateStr' => isset($slot['dateStr']) ? sanitize_text_field($slot['dateStr']) : ''
            );

            // Only add if we have valid data
            if (!empty($sanitized_slot['time']) && !empty($sanitized_slot['dateStr'])) {
              $sanitized_instance['selected_time_slots'][] = $sanitized_slot;
            }
          }
        }
      }

      // Sanitize other fields
      $sanitized_instance['amenity'] = sanitize_text_field($instanceData['amenity'] ?? '');
      $sanitized_instance['sleeping'] = sanitize_text_field($instanceData['sleeping'] ?? '');
      $sanitized_instance['gservice'] = sanitize_text_field($instanceData['gservice'] ?? '');

      $sanitized_instances[$instanceId] = $sanitized_instance;
    }

    // Save the sanitized data
    update_post_meta($post_id, '_listing_calendar_instances', $sanitized_instances);
  }
}
add_action('save_post', 'listing_special_features_acf_save_post', 25);

function homey_get_user_role($user_id)
{
  $user = get_userdata($user_id);
  if ($user) {
    $roles = $user->roles;
    $priority_roles = ['homey_renter', 'homey_host', 'administrator'];

    foreach ($priority_roles as $role) {
      if (in_array($role, $roles)) {
        return $role;
      }
    }
    return '';
  }
  return '';
}

// Contact Host
function jsl_contact_host()
{
  if (!isset($_POST)) {
    wp_send_json_error('Invalid request.');
    return;
  }

  $receiver_id = intval($_POST['receiver_id']);
  $sender_id = get_current_user_id();
  $sender_role = homey_get_user_role($sender_id);
  $receiver_role = homey_get_user_role($receiver_id);
  $listing_id = intval($_POST['listing_id']);
  $message = sanitize_text_field($_POST['message']);
  $time = current_time('mysql');

  if ($sender_id == $receiver_id) {
    wp_send_json_error('You cannot send a message to yourself.');
    return;
  }

  $sender_username = get_the_author_meta('user_login', $sender_id);
  $sender_display_name_public = '';
  if ($sender_role == 'homey_renter') {
    $sender_display_name_public = get_the_author_meta('display_name_public_guest', $sender_id);
  } else {
    $sender_display_name_public = get_the_author_meta('display_name_public', $sender_id);
  }
  $sender_name = empty($sender_display_name_public) ? $sender_username : $sender_display_name_public;

  if (empty($message)) {
    wp_send_json_error('Please enter your message.');
    return;
  }

  // Validate message content for prohibited information
  $validation_result = validate_message_content($message);
  if (!$validation_result['valid']) {
    wp_send_json_error($validation_result['message']);
    return;
  }

  global $wpdb;
  $table_name = $wpdb->prefix . 'homey_messages';

  // Check if this user already contacted this host about this listing
  $existing_message = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table_name 
     WHERE sender_id = %d 
     AND receiver_id = %d 
     AND listing_id = %d
     AND listing_id IS NOT NULL
     AND listing_id != ''
     ORDER BY created_at DESC
     LIMIT 1",
    $sender_id,
    $receiver_id,
    $listing_id
  ));

  // Insert message in the database
  $inserted = $wpdb->insert($table_name, array(
    'receiver_id' => $receiver_id,
    'sender_id' => $sender_id,
    'sender_role' => $sender_role,
    'receiver_role' => $receiver_role,
    'listing_id' => $listing_id,
    'message' => $message,
    'created_at' => $time,
    'duplicate_inquiry' => ($existing_message) ? 1 : 0,
  ));

  if ($inserted) {

    // Save the new message notification to the receiver
    $notification_title = 'New Message Received from ' . $sender_name;
    $notification_content = 'New Message Received from ' . $sender_name;
    $notification_link = home_url('/all-messages/?chat_with=' . $sender_id);
    save_booking_notification($receiver_id, $notification_title, $notification_content, $notification_link);

    wp_send_json_success('Message sent successfully.');
  } else {
    wp_send_json_error('Failed to send message. Please try again.');
  }
}
add_action('wp_ajax_jsl_contact_host', 'jsl_contact_host');

function validate_message_content($message)
{
  // Patterns to detect prohibited content
  $patterns = array(
    'email' => '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
    'phone' => '/\+?[\d\s\-\(\)]{7,}/',
    'url' => '/(https?:\/\/|www\.)[^\s]+/i',
    'social_media' => '/(facebook|twitter|instagram|linkedin|tiktok|pinterest|snapchat|reddit)\.(com|org|net|[a-z]{2,})/i',
    'payment_services' => '/(zelle|venmo|cashapp|paypal|wire transfer|cash app)/i'
  );

  $matches = array();

  foreach ($patterns as $type => $pattern) {
    if (preg_match($pattern, $message, $matches)) {
      switch ($type) {
        case 'email':
          return array(
            'valid' => false,
            'message' => 'For your safety, we do not allow email addresses in messages. Please remove any email address and try again.'
          );
        case 'phone':
          return array(
            'valid' => false,
            'message' => 'For your safety, we do not allow phone numbers in messages. Please remove any phone numbers and try again.'
          );
        case 'url':
        case 'social_media':
          return array(
            'valid' => false,
            'message' => 'For your safety, we do not allow links in messages. Please remove any links and try again.'
          );
        case 'payment_services':
          return array(
            'valid' => false,
            'message' => 'For your safety, we do not allow references to external payment services. Please keep all transactions on our platform.'
          );
      }
    }
  }

  return array('valid' => true, 'message' => '');
}

// Send Admin Message
add_action('wp_ajax_send_admin_message', 'send_admin_message');
function send_admin_message()
{
  if (!isset($_POST['message'])) {
    wp_send_json_error('Invalid request.');
    return;
  }

  $message = sanitize_text_field($_POST['message']);
  $current_user_id = get_current_user_id();

  if (!$current_user_id || !current_user_can('administrator')) {
    wp_send_json_error('You must be an admin to send a broadcast message.');
    return;
  }

  global $wpdb;
  $table_name = $wpdb->prefix . 'homey_messages';

  // Get all users except the current admin
  $users = get_users(array(
    'exclude' => array($current_user_id),
    'fields' => 'ID',
  ));

  if (empty($users)) {
    wp_send_json_error('No users found.');
    return;
  }

  $sender_role = homey_get_user_role($current_user_id);
  $success = true;

  // Send the message to each user
  foreach ($users as $user_id) {
    $receiver_role = homey_get_user_role($user_id);

    $inserted = $wpdb->insert(
      $table_name,
      array(
        'sender_id' => $current_user_id,
        'receiver_id' => $user_id,
        'sender_role' => $sender_role,
        'receiver_role' => $receiver_role,
        'message' => $message,
        'created_at' => current_time('mysql'),
      ),
      array('%d', '%d', '%s', '%s', '%s', '%s')
    );

    if (!$inserted) {
      $success = false;
    }
  }

  if ($success) {
    wp_send_json_success('Message sent to all users.');
  } else {
    wp_send_json_error('Failed to send message to some users.');
  }
}

function fetch_new_messages()
{
  global $wpdb;
  $current_user_id = get_current_user_id();
  $other_user_id = intval($_POST['user_id']);
  $last_message_id = intval($_POST['last_message_id']);
  $current_user_role = homey_get_user_role($current_user_id);

  $last_message_before_new = $wpdb->get_row($wpdb->prepare(
    "
          SELECT created_at FROM {$wpdb->prefix}homey_messages
          WHERE (
          (sender_id = %d AND receiver_id = %d AND sender_role = %s)
          OR (sender_id = %d AND receiver_id = %d AND receiver_role = %s)
          )
          AND id <= %d
          ORDER BY id DESC LIMIT 1",
    $current_user_id,
    $other_user_id,
    $current_user_role,
    $other_user_id,
    $current_user_id,
    $current_user_role,
    $last_message_id
  ));

  $last_message_date = '';
  if ($last_message_before_new) {
    $last_message_date = date('M d, Y', strtotime($last_message_before_new->created_at));
  }

  $messages = $wpdb->get_results($wpdb->prepare(
    "
      SELECT * FROM {$wpdb->prefix}homey_messages
      WHERE (
          (sender_id = %d AND receiver_id = %d AND sender_role = %s)
          OR (sender_id = %d AND receiver_id = %d AND receiver_role = %s)
      )
      AND id > %d
      ORDER BY created_at ASC",
    $current_user_id,
    $other_user_id,
    $current_user_role,
    $other_user_id,
    $current_user_id,
    $current_user_role,
    $last_message_id
  ));

  if (!empty($messages)) {
    $chat_html = '';

    foreach ($messages as $message) {
      $current_message_date = date('M d, Y', strtotime($message->created_at));

      if ($current_message_date !== $last_message_date) {
        $chat_html .= '<div class="message-date">' . esc_html($current_message_date) . '</div>';
        $last_message_date = $current_message_date; // Update the last message date
      }

      $sender_username = get_the_author_meta('user_login', $message->sender_id);
      $user_role = homey_get_user_role($message->sender_id);

      if (user_can($message->sender_id, 'administrator')) {
        $sender_name = 'Backyard Lease Platform';
        $profile_link = '#';
        $user_image = '<img src="' . esc_url(homey_option('custom_logo', false, 'url')) . '" class="img-circle" alt="Backyard Lease Platform" width="36" height="36">';
      } else {
        $sender_display_name_public = '';
        if ($user_role == 'homey_renter') {
          $sender_display_name_public = get_the_author_meta('display_name_public_guest', $message->sender_id);
        } else {
          $sender_display_name_public = get_the_author_meta('display_name_public', $message->sender_id);
        }
        $sender_name = empty($sender_display_name_public) ? $sender_username : $sender_display_name_public;
        $profile_link = get_author_posts_url($message->sender_id);
        $homey_author = homey_get_author_by_id('36', '36', 'img-circle', $message->sender_id);
        $user_image = $homey_author['photo'];
      }

      $sender_role = homey_get_user_role($message->sender_id);
      if ($sender_role === 'homey_host') {
        $sender_name .= ' (Host)';
      }

      $message_time = date('h:i A', strtotime($message->created_at));

      $is_favorite = $wpdb->get_var($wpdb->prepare(
        "SELECT is_favorite 
         FROM {$wpdb->prefix}homey_message_favorites 
         WHERE user_id = %d AND message_id = %d",
        $current_user_id,
        $message->id
      ));
      $favorite_class = $is_favorite ? 'fas' : 'far';
      $favorite_data = 'data-message-id="' . esc_attr($message->id) . '"';
      $tooltip_text = $is_favorite ? 'Remove from Favorites' : 'Add to Favorites';

      $chat_html .= '<div class="message-user-info">';
      if (empty($message->sender_role)) {
        $chat_html .= '<div class="message-user-content">';
        $chat_html .= '<div class="system-message">';
        $chat_html .= '<span class="message-time">' . esc_html($message_time) . '</span>';
        $chat_html .= '<span class="last-message last-message-id" data-message-id="' . esc_attr($message->id) . '">' . esc_html($message->message) . '</span>';
        $chat_html .= '</div>';
        $chat_html .= '</div>';
        $chat_html .= '</div>';
      } else {
        $chat_html .= '<div class="message-user-img">';
        $chat_html .= $user_image;
        $chat_html .= '</div>';
        $chat_html .= '<div class="message-user-content">';
        $chat_html .= '<span class="user-name" data-sender-id="' . esc_attr($message->sender_id) . '"><a class="user-name-link" href="' . $profile_link . '">' . esc_html($sender_name) . '</a></span>';
        $chat_html .= '<span class="message-time">' . esc_html($message_time) . '</span>';
        $chat_html .= '<span class="last-message last-message-id" data-message-id="' . esc_attr($message->id) . '">' . esc_html($message->message) . '</span>';
        $chat_html .= '</div>';
        $chat_html .= '<div class="favorite-icon-container">';
        $chat_html .= '<i class="' . esc_attr($favorite_class) . ' fa-heart favorite-icon" ' . $favorite_data . ' title="' . esc_attr($tooltip_text) . '"></i>';
        $chat_html .= '<span class="tooltip">' . esc_html($tooltip_text) . '</span>';
        $chat_html .= '</div>';
        $chat_html .= '</div>';
      }
    }

    wp_send_json_success($chat_html);
  } else {
    wp_send_json_error('No new messages.');
  }
}
add_action('wp_ajax_fetch_new_messages', 'fetch_new_messages');

function fetch_chat_messages()
{
  //error_reporting(E_ALL);
  //ini_set("display_errors", 1);
  global $wpdb;
  $current_user_id = get_current_user_id();
  $other_user_id = intval($_POST['user_id']);
  $original_role = get_user_meta($other_user_id, 'original_role', true);
  $username = get_the_author_meta('user_login', $other_user_id);
  $display_name_public = '';
  $user_role = homey_get_user_role($other_user_id);
  if (user_can($other_user_id, 'administrator')) {
    $user_name = 'Backyard Lease Platform';
  } else {
    $sender_display_name_public = '';
    if ($user_role == 'homey_renter') {
      $display_name_public = get_the_author_meta('display_name_public_guest', $other_user_id);
    } else {
      $display_name_public = get_the_author_meta('display_name_public', $other_user_id);
    }
    $user_name = empty($display_name_public) ? $username : $display_name_public;
  }

  $current_user_role = homey_get_user_role($current_user_id);

  $messages = $wpdb->get_results($wpdb->prepare(
    "
      SELECT * FROM {$wpdb->prefix}homey_messages
      WHERE (
          (sender_id = %d AND receiver_id = %d AND sender_role = %s)
          OR (sender_id = %d AND receiver_id = %d AND receiver_role = %s)
      )
      ORDER BY created_at ASC",
    $current_user_id,
    $other_user_id,
    $current_user_role,
    $other_user_id,
    $current_user_id,
    $current_user_role
  ));

  $booking_info_html = '';
  $booking_id = null;

  foreach (array_reverse($messages) as $message) {
    if (!empty($message->reservation_id)) {
      $booking_id = $message->reservation_id;
      break;
    }
  }

  if ($booking_id) {
    $reservation_listing_id = get_post_meta($booking_id, 'reservation_listing_id', true);
    $reservation_listing_title = get_the_title($reservation_listing_id);
    $reservation_listing_permalink = get_permalink($reservation_listing_id);
    if (has_post_thumbnail($reservation_listing_id)) {
      $reservation_listing_thumbnail = get_the_post_thumbnail($reservation_listing_id, 'homey-listing-thumb', array('class' => 'img-responsive'));
    } else {
      $fallback_image_url = wp_get_attachment_image_url(4704);
      $reservation_listing_thumbnail = '<img src="' . esc_url($fallback_image_url) . '" class="img-responsive" />';
    }

    $reservation_status = get_post_meta($booking_id, 'reservation_status', true);
    if ($reservation_status == 'under_review') {
      $status_label = '<span class="label label-warning">UNDER REVIEW</span>';
    } elseif ($reservation_status == 'booked') {
      $status_label = '<span class="label label-success">BOOKED</span>';
    } elseif ($reservation_status == 'declined') {
      $status_label = '<span class="label label-danger">DECLINED</span>';
    } elseif ($reservation_status == 'cancelled') {
      $status_label = '<span class="label label-grey">CANCELLED</span>';
    } elseif ($reservation_status == 'completed') {
      $status_label = '<span class="label label-complete">COMPLETED</span>';
    }

    $reservation_meta = get_post_meta($booking_id, 'reservation_meta', true);
    $check_in_date = $reservation_meta['check_in_date'];
    $date = new DateTime($check_in_date);
    $formatted_date = $date->format('D, M j');
    $check_in = $reservation_meta['start_hour'];
    $check_out = $reservation_meta['end_hour'];
    $guests = $reservation_meta['guests'];
    $total_price = $reservation_meta['total'];

    $booking_permalink = home_url('reservations/?reservation_detail=' . $booking_id);
    $hours_total_price = homey_formatted_price($reservation_meta['hours_total_price'], false);
    $total_extra_services = homey_formatted_price($reservation_meta['total_extra_services'], false);
    $additional_guests = $reservation_meta['additional_guests'];
    $additional_guests_total_price = homey_formatted_price(doubleval($reservation_meta['additional_guests_total_price']));

    $total_guide_price = 0.0;
    $total_non_participants_price = $reservation_meta['total_non_participants_price'];
    if (!empty($total_non_participants_price)) {
      $total_guide_price = $total_guide_price + floatval($total_non_participants_price);
    }

    $total_equipments_price = $reservation_meta['total_equipments_price'];
    if (!empty($total_equipments_price)) {
      $total_guide_price = $total_guide_price + floatval($total_equipments_price);
    }
    $total_guest_hourly = $reservation_meta['total_guest_hourly'];
    if (!empty($total_guest_hourly)) {
      $total_guide_price = $total_guide_price + floatval($total_guest_hourly);
    }
    $total_guest_fixed = $reservation_meta['total_guest_fixed'];
    if (!empty($total_guest_fixed)) {
      $total_guide_price = $total_guide_price + floatval($total_guest_fixed);
    }
    $total_group_hourly = $reservation_meta['total_group_hourly'];
    if (!empty($total_group_hourly)) {
      $total_guide_price = $total_guide_price + floatval($total_group_hourly);
    }
    $total_group_fixed = $reservation_meta['total_group_fixed'];
    if (!empty($total_group_fixed)) {
      $total_guide_price = $total_guide_price + floatval($total_group_fixed);
    }
    $total_flat_hourly = $reservation_meta['total_flat_hourly'];
    if (!empty($total_flat_hourly)) {
      $total_guide_price = $total_guide_price + floatval($total_flat_hourly);
    }
    $total_flat_fixed = $reservation_meta['total_flat_fixed'];
    if (!empty($total_flat_fixed)) {
      $total_guide_price = $total_guide_price + floatval($total_flat_fixed);
    }

    $total_accomodation_price = 0.0;
    $cleaning_fee = $reservation_meta['cleaning_fee'];
    $total_accomodation_fee = $reservation_meta['total_accomodation_fee'];
    if (!empty($total_accomodation_fee)) {
      $total_accomodation_price = floatval($total_accomodation_fee) + floatval($cleaning_fee);
    }

    $additional_vehicles_fee = homey_formatted_price($reservation_meta['additional_vehicles_fee']);
    $coupon_discount = $reservation_meta['coupon_discount'];
    $services_fee = doubleval($reservation_meta['services_fee']);
    $occ_tax_amount = homey_formatted_price($reservation_meta['occ_tax_amount']);
    $total_state_tax = homey_formatted_price($reservation_meta['total_state_tax']);

    $booking_info_html .= '<div class="listing-details-block hide-booking-col">';
    $booking_info_html .= '<div class="message-user-header listing-details-header">';
    $booking_info_html .= '<h2 class="title">Reservation Details</h2>';
    $booking_info_html .= '<a id="hide-booking-details" class="hide-booking-details">x</a>';
    $booking_info_html .= '</div>';
    $booking_info_html .= '<div class="listing-info">';
    $booking_info_html .= '<h3 class="listing-details-title">' . esc_html($reservation_listing_title) . '</h3>';
    $booking_info_html .= '<a href="' . esc_html($reservation_listing_permalink) . '">' . $reservation_listing_thumbnail . '</a>';
    $booking_info_html .= '<div class="booking-details-status" style="margin-top: 15px;"><b>Reservation Status:</b> ' . $status_label . '</div>';
    $booking_info_html .= '<div class="check-in-out-sec">';
    $booking_info_html .= '<div class="check-in-sec">';
    $booking_info_html .= '<div><b>Check-in</b></div>';
    $booking_info_html .= $formatted_date . '</br>' . date(homey_time_format(), strtotime($check_in));
    $booking_info_html .= '</div>';
    $booking_info_html .= '<div class="check-out-sec">';
    $booking_info_html .= '<div><b>Checkout</b></div>';
    $booking_info_html .= $formatted_date . '</br>' . date(homey_time_format(), strtotime($check_out));
    $booking_info_html .= '</div>';
    $booking_info_html .= '</div>';
    $booking_info_html .= '<div><b>Who\'s Coming:</b> ' . $guests . ' guests</div>';
    if (!empty($hours_total_price)) {
      $booking_info_html .= '<div style="margin-top: 15px;"><b>Amenity:</b> ' . $hours_total_price . '</div>';
    }
    if (!empty($total_extra_services)) {
      $booking_info_html .= '<div style="margin-top: 15px;"><b>Extra Services:</b> ' . $total_extra_services . '</div>';
    }
    if (!empty($additional_guests)) {
      $booking_info_html .= '<div style="margin-top: 15px;"><b>Additional Guests:</b> ' . $additional_guests_total_price . '</div>';
    }
    if (!empty($total_guide_price) && $total_guide_price != 0) {
      $booking_info_html .= '<div style="margin-top: 15px;"><b>Guided Service:</b> ' . homey_formatted_price($total_guide_price) . '</div>';
    }
    if (!empty($total_accomodation_price) && $total_accomodation_price != 0) {
      $booking_info_html .= '<div style="margin-top: 15px;"><b>Sleeping Accomodation:</b> ' . homey_formatted_price($total_accomodation_price) . '</div>';
    }
    if (!empty($additional_vehicles_fee) && $additional_vehicles_fee != 0) {
      $booking_info_html .= '<div style="margin-top: 15px;"><b>Additional Vehicles:</b> ' . $additional_vehicles_fee . '</div>';
    }
    if (!empty($coupon_discount) && $coupon_discount != 0) {
      $booking_info_html .= '<div style="margin-top: 15px;"><b>Coupon Discount: -$</b> ' . $coupon_discount . '</div>';
    }
    if (!empty($services_fee) && $services_fee != 0) {
      $booking_info_html .= '<div style="margin-top: 15px;"><b>Service Fee:</b> ' . homey_formatted_price($services_fee) . '</div>';
    }
    if (!empty($occ_tax_amount) && $occ_tax_amount != 0) {
      $booking_info_html .= '<div style="margin-top: 15px;"><b>Occupancy Tax:</b> ' . $occ_tax_amount . '</div>';
    }
    if (!empty($total_state_tax)) {
      $booking_info_html .= '<div style="margin-top: 15px;"><b>Sales Tax:</b> ' . $total_state_tax . '</div>';
    }
    $booking_info_html .= '<div style="margin-top: 15px;"><b>Total Price:</b> $' . number_format($total_price, 2) . '</div>';
    $booking_info_html .= '<a class="btn btn-primary" href="' . esc_html($booking_permalink) . '" style="margin-top: 15px;display:block;">See Reservation</a>';
    $booking_info_html .= '</div>';
    $booking_info_html .= '</div>';
  }

  $listing_info_html = '';
  $listing_id = null;

  foreach (array_reverse($messages) as $message) {
    if (!empty($message->listing_id)) {
      $listing_id = $message->listing_id;
      break;
    }
  }

  if ($listing_id) {
    $listing_title = get_the_title($listing_id);
    $listing_post = get_post($listing_id);
    $listing_bio = $listing_post->post_content;
    $listing_price = get_post_meta($listing_id, 'homey_hour_price', true);

    $listing = get_post($listing_id);
    $host_id = $listing->post_author;

    $username = get_the_author_meta('user_login', $host_id);
    $display_name_public = get_the_author_meta('display_name_public', $host_id);
    $host_name = empty($display_name_public) ? $username : $display_name_public;

    $homey_author = homey_get_author_by_id('50', '50', 'img-circle', $host_id);
    $user_image = $homey_author['photo'];

    $listing_weekend_price = get_post_meta($listing_id, 'homey_hourly_weekends_price', true);
    $amenity_price_type = get_field('amenity_price_type', $listing_id);
    $price_type_text = '';

    if ($amenity_price_type == 'price_per_hour') {
      $price_type_text = 'HR';
    } elseif ($amenity_price_type == 'price_per_day') {
      $price_type_text = 'DAY';
    } elseif ($amenity_price_type == 'price_per_half_day') {
      $price_type_text = 'HALF DAY';
    }
    $listing_types = get_the_terms($listing_id, 'listing_type');
    $subcategory = get_field('field_6479eb70249d7', $listing_id);
    $listing_permalink = get_permalink($listing_id);
    if (has_post_thumbnail($listing_id)) {
      $listing_thumbnail = get_the_post_thumbnail($listing_id, 'homey-listing-thumb', array('class' => 'img-responsive'));
    } else {
      $fallback_image_url = wp_get_attachment_image_url(4704);
      $listing_thumbnail = '<img src="' . esc_url($fallback_image_url) . '" class="img-responsive" />';
    }

    $listing_info_html .= '<div class="listing-details-block hide-listing-col">';
    $listing_info_html .= '<div class="message-user-header listing-details-header">';
    $listing_info_html .= '<h2 class="title">Listing Details</h2>';
    $listing_info_html .= '<a id="hide-listing-details" class="hide-listing-details">x</a>';
    $listing_info_html .= '</div>';
    $listing_info_html .= '<div class="listing-info">';
    $listing_info_html .= '<h3 class="listing-details-title">' . esc_html($listing_title) . '</h3>';
    $listing_info_html .= '<a href="' . esc_html($listing_permalink) . '">' . $listing_thumbnail . '</a>';
    if (!empty($listing_types)) {
      foreach ($listing_types as $listing_type) {
        $listing_info_html .= '<span style="display: block;margin-top: 15px;">';
        $listing_info_html .= '<strong>Unique Adventure:</strong> ' . esc_attr($listing_type->name);
        if (!empty($subcategory)) {
          $listing_info_html .= ' - ' . esc_attr($subcategory);
        }
        $listing_info_html .= '</span>';
      }
    }
    if (!empty($listing_price)) {
      $listing_info_html .= '<span style="display: block;"><strong>Price:</strong> $' . esc_html($listing_price) . '/' . esc_html($price_type_text) . '</span>';
    }
    if (!empty($listing_weekend_price)) {
      $listing_info_html .= '<span style="display: block;margin-bottom: 10px;"><strong>Weekend price:</strong> $' . esc_html($listing_weekend_price) . '/' . esc_html($price_type_text) . '</span>';
    }
    if (!empty($listing_bio)) {
      $listing_info_html .= '<span style="display: block;margin-top: 10px;"><strong>Bio:</strong><span style="display: block;">' . wpautop($listing_bio) . '</span></span>';
    }
    $listing_info_html .= '<div class="media">';
    $listing_info_html .= '<div class="media-left" style="padding-right: 10px;">';
    $listing_info_html .=  $user_image;
    $listing_info_html .= '</div>';
    $listing_info_html .= '<div class="media-body">';
    $listing_info_html .= '<strong>Adventure Hosted by:</strong> ' . esc_html($host_name);
    $listing_info_html .= '</div>';
    $listing_info_html .= '</div>';
    $listing_info_html .= '<a class="btn btn-primary" href="' . esc_html($listing_permalink) . '" style="margin-top: 15px;">Book This Adventure</a>';
    $listing_info_html .= '</div>';
    $listing_info_html .= '</div>';
  }

  if (user_can($other_user_id, 'administrator')) {
    $response_time_text = 'No Response Yet';
  } else {
    $average_response_time = calculate_average_response_time($other_user_id);
    $response_time_text = format_response_time($average_response_time);
  }

  $chat_html = '<div class="messaging-block">';
  $chat_html .= '<div class="message-details-block">';
  $chat_html .= '<div class="message-user-header message-details-header">';
  $chat_html .= '<div class="chat-header-left" style="flex:1">';
  $chat_html .= '<h2 class="title" style="margin-left: 9px;">' . esc_html($user_name) . '</h2>';
  $chat_html .= '<div class="response-time" style="margin-left: 9px;">' . esc_html($response_time_text) . '</div>';
  $chat_html .= '</div>';
  $chat_html .= '<input type="text" id="message-search" placeholder="Search..." style="margin-right: 9px; flex:1" />';
  $chat_html .= '</div>';
  $chat_html .= '<div class="all-messages-block">';

  $last_message_date = '';

  foreach ($messages as $message) {
    $message_date = date('M d, Y', strtotime($message->created_at));

    // Only show the date if it's different from the last message's date
    if ($message_date !== $last_message_date) {
      $chat_html .= '<div class="message-date">' . esc_html($message_date) . '</div>';
      $last_message_date = $message_date;
    }

    $sender_username = get_the_author_meta('user_login', $message->sender_id);
    $user_role = homey_get_user_role($message->sender_id);

    if (user_can($message->sender_id, 'administrator')) {
      $sender_name = 'Backyard Lease Platform';
      $profile_link = '#';
      $user_image = '<img src="' . esc_url(homey_option('custom_logo', false, 'url')) . '" class="img-circle" alt="Backyard Lease Platform" width="36" height="36">';
    } else {
      $sender_display_name_public = '';
      if ($user_role == 'homey_renter') {
        $sender_display_name_public = get_the_author_meta('display_name_public_guest', $message->sender_id);
      } else {
        $sender_display_name_public = get_the_author_meta('display_name_public', $message->sender_id);
      }
      $sender_name = empty($sender_display_name_public) ? $sender_username : $sender_display_name_public;
      $profile_link = get_author_posts_url($message->sender_id);
      $homey_author = homey_get_author_by_id('36', '36', 'img-circle', $message->sender_id);
      $user_image = $homey_author['photo'];
    }

    $sender_role = homey_get_user_role($message->sender_id);
    if ($sender_role === 'homey_host') {
      $sender_name .= ' (Host)';
    }
    // Format the message timestamp
    $message_time = date('h:i A', strtotime($message->created_at));

    $is_favorite = $wpdb->get_var($wpdb->prepare(
      "SELECT is_favorite 
       FROM {$wpdb->prefix}homey_message_favorites 
       WHERE user_id = %d AND message_id = %d",
      $current_user_id,
      $message->id
    ));
    $favorite_class = $is_favorite ? 'fas' : 'far';
    $favorite_data = 'data-message-id="' . esc_attr($message->id) . '"';
    $tooltip_text = $is_favorite ? 'Remove from Favorites' : 'Add to Favorites';

    $chat_html .= '<div class="message-user-info">';
    if (empty($message->sender_role) || $message->reservation_id != NULL) {
      $chat_html .= '<div class="message-user-content">';
      $chat_html .= '<div class="system-message">';
      $chat_html .= '<span class="message-time">' . esc_html($message_time) . '</span>';
      $chat_html .= '<span class="last-message last-message-id" data-message-id="' . esc_attr($message->id) . '">' . ($message->message) . '</span>';
      $chat_html .= '</div>';
      $chat_html .= '</div>';
      $chat_html .= '</div>';
    } else {
      $chat_html .= '<div class="message-user-img">';
      $chat_html .= $user_image;
      $chat_html .= '</div>';
      $chat_html .= '<div class="message-user-content">';
      $chat_html .= '<span class="user-name" data-sender-id="' . esc_attr($message->sender_id) . '"><a class="user-name-link" href="' . $profile_link . '">' . esc_html($sender_name) . '</a></span>';
      $chat_html .= '<span class="message-time">' . esc_html($message_time) . '</span>';
      if (
        isset($message->listing_id) && $message->listing_id != 0 &&
        $current_user_id == $message->receiver_id &&
        homey_get_user_role($current_user_id) == 'homey_host'
      ) {
        $chat_html .= '<span class="msg-adventure-title" style="display:block">Backyard Adventure: ' . get_the_title($message->listing_id) . '</span>';
      }
      $chat_html .= '<span class="last-message last-message-id" data-message-id="' . esc_attr($message->id) . '">' . ($message->message) . '</span>';
      if (
        isset($message->duplicate_inquiry) && $message->duplicate_inquiry == 1 &&
        $current_user_id == $message->receiver_id &&
        homey_get_user_role($current_user_id) == 'homey_host'
      ) {
        $chat_html .= '<span class="duplicate-inquiry-notice">This guest has previously inquired about that adventure.</span>';
      }
      $chat_html .= '</div>';
      $chat_html .= '<div class="favorite-icon-container">';
      $chat_html .= '<i class="' . esc_attr($favorite_class) . ' fa-heart favorite-icon" ' . $favorite_data . ' title="' . esc_attr($tooltip_text) . '"></i>';
      $chat_html .= '<span class="tooltip">' . esc_html($tooltip_text) . '</span>';
      $chat_html .= '</div>';
      $chat_html .= '</div>';
    }
  }
  $chat_html .= '</div>';
  $chat_html .= '<div class="message-input">';
  if (!empty($original_role) && $original_role !== $user_role) {
    $chat_html .= '<textarea id="chat-input" rows="1" placeholder="Type a message" disabled></textarea>';
    $chat_html .= '<button id="send-homey-message" class="btn btn-primary" disabled>Send</button>';
  } else {
    $chat_html .= '<textarea id="chat-input" rows="1" placeholder="Type a message"></textarea>';
    $chat_html .= '<button id="send-homey-message" class="btn btn-primary">Send</button>';
  }

  $chat_html .= '</div>';
  $chat_html .= '</div>';
  $chat_html .= $listing_info_html;
  $chat_html .= $booking_info_html;
  $chat_html .= '</div>';

  wp_send_json_success($chat_html);
}
add_action('wp_ajax_fetch_chat_messages', 'fetch_chat_messages');

// Handle sending chat messages
add_action('wp_ajax_send_chat_message', 'send_chat_message');
add_action('wp_ajax_nopriv_send_chat_message', 'send_chat_message');

function send_chat_message()
{
  if (!isset($_POST['message']) || !isset($_POST['recipient_id'])) {
    wp_send_json_error('Message or recipient not found.');
    return;
  }

  $message = sanitize_text_field($_POST['message']);
  $recipient_id = intval($_POST['recipient_id']);
  $sender_id = get_current_user_id();

  if (!$sender_id) {
    wp_send_json_error('You must be logged in to send messages.');
    return;
  }

  if (empty($message) || empty($recipient_id)) {
    wp_send_json_error('Empty message or invalid recipient.');
    return;
  }

  // Validate message content for prohibited information
  $validation_result = validate_message_content($message);
  if (!$validation_result['valid']) {
    wp_send_json_error($validation_result['message']);
    return;
  }

  // Check if the recipient has changed their role
  $recipient_role = homey_get_user_role($recipient_id);
  $original_role = get_user_meta($recipient_id, 'original_role', true);

  if (!empty($original_role)) {
    if ($recipient_role !== $original_role) {
      wp_send_json_error('This user has changed his role and cannot receive messages at this time.');
      return;
    }
  }

  global $wpdb;

  $sender_role = homey_get_user_role($sender_id);
  $receiver_role = homey_get_user_role($recipient_id);

  $response_time = null;

  $last_received_message = $wpdb->get_row($wpdb->prepare(
    "SELECT id, created_at FROM {$wpdb->prefix}homey_messages 
         WHERE sender_id = %d AND receiver_id = %d 
         ORDER BY created_at DESC LIMIT 1",
    $recipient_id,
    $sender_id
  ));

  if ($last_received_message) {
    $current_time = current_time('mysql');
    $response_time = strtotime($current_time) - strtotime($last_received_message->created_at);
  }

  $wpdb->insert(
    "{$wpdb->prefix}homey_messages",
    array(
      'sender_id' => $sender_id,
      'receiver_id' => $recipient_id,
      'sender_role' => $sender_role,
      'receiver_role' => $receiver_role,
      'message' => $message,
      'response_time' => $response_time,
      'created_at' => current_time('mysql')
    ),
    array('%d', '%d', '%s', '%s', '%s', '%d', '%s')
  );

  $message_id = $wpdb->insert_id;

  if ($message_id) {
    $last_message = $wpdb->get_row($wpdb->prepare(
      "SELECT created_at FROM {$wpdb->prefix}homey_messages WHERE id < %d AND ((sender_id = %d AND receiver_id = %d) OR (sender_id = %d AND receiver_id = %d)) ORDER BY created_at DESC LIMIT 1",
      $message_id,
      $sender_id,
      $recipient_id,
      $recipient_id,
      $sender_id
    ));

    $last_message_date = $last_message ? date('M d, Y', strtotime($last_message->created_at)) : '';

    $current_message_date = date('M d, Y', strtotime(current_time('mysql')));

    $message_html = '';
    if ($current_message_date !== $last_message_date) {
      $message_html .= '<div class="message-date">' . esc_html($current_message_date) . '</div>';
    }

    $sender_username = get_the_author_meta('user_login', $sender_id);
    $user_role = homey_get_user_role($sender_id);

    if (user_can($sender_id, 'administrator')) {
      $sender_name = 'Backyard Lease Platform';
      $profile_link = '#';
      $user_image = '<img src="' . esc_url(homey_option('custom_logo', false, 'url')) . '" class="img-circle" alt="Backyard Lease Platform" width="36" height="36">';
    } else {
      $sender_display_name_public = '';
      if ($user_role == 'homey_renter') {
        $sender_display_name_public = get_the_author_meta('display_name_public_guest', $sender_id);
      } else {
        $sender_display_name_public = get_the_author_meta('display_name_public', $sender_id);
      }
      $sender_name = empty($sender_display_name_public) ? $sender_username : $sender_display_name_public;
      $profile_link = get_author_posts_url($sender_id);
      $homey_author = homey_get_author_by_id('36', '36', 'img-circle', $sender_id);
      $user_image = $homey_author['photo'];
    }

    // Save the new message notification to the receiver
    $notification_title = 'New Message Received from ' . $sender_name;
    $notification_content = 'New Message Received from ' . $sender_name;
    $notification_link = home_url('/all-messages/?chat_with=' . $sender_id);
    save_booking_notification($recipient_id, $notification_title, $notification_content, $notification_link);

    $sender_role = homey_get_user_role($sender_id);
    if ($sender_role === 'homey_host') {
      $sender_name .= ' (Host)';
    }

    $message_time = date('h:i A', strtotime(current_time('mysql')));

    $is_favorite = $wpdb->get_var($wpdb->prepare(
      "SELECT is_favorite 
       FROM {$wpdb->prefix}homey_message_favorites 
       WHERE user_id = %d AND message_id = %d",
      $sender_id,
      $message_id
    ));
    $favorite_class = $is_favorite ? 'fas' : 'far';
    $favorite_data = 'data-message-id="' . esc_attr($message_id) . '"';
    $tooltip_text = $is_favorite ? 'Remove from Favorites' : 'Add to Favorites';

    $message_html .= '<div class="message-user-info">';
    $message_html .= '<div class="message-user-img">';
    $message_html .= $user_image;
    $message_html .= '</div>';
    $message_html .= '<div class="message-user-content">';
    $message_html .= '<span class="user-name" data-sender-id="' . esc_attr($sender_id) . '"><a class="user-name-link" href="' . $profile_link . '">' . esc_html($sender_name) . '</a></span>';
    $message_html .= '<span class="message-time">' . esc_html($message_time) . '</span>';
    $message_html .= '<span class="last-message last-message-id" data-message-id="' . esc_attr($message_id) . '">' . esc_html($message) . '</span>';
    $message_html .= '</div>';
    $message_html .= '<div class="favorite-icon-container">';
    $message_html .= '<i class="' . esc_attr($favorite_class) . ' fa-heart favorite-icon" ' . $favorite_data . ' title="' . esc_attr($tooltip_text) . '"></i>';
    $message_html .= '<span class="tooltip">' . esc_html($tooltip_text) . '</span>';
    $message_html .= '</div>';
    $message_html .= '</div>';

    wp_send_json_success($message_html);
  } else {
    wp_send_json_error('Could not send the message.');
  }
}

add_action('wp_ajax_toggle_favorite_message', 'toggle_favorite_message_ajax');
function toggle_favorite_message_ajax()
{
  if (!isset($_POST['message_id']) || !isset($_POST['is_favorite'])) {
    wp_send_json_error('Invalid request.');
    return;
  }

  $message_id = intval($_POST['message_id']);
  $is_favorite = intval($_POST['is_favorite']);
  $user_id = get_current_user_id();

  global $wpdb;
  $table_name = $wpdb->prefix . 'homey_message_favorites';

  // Check if the user has already marked this message as a favorite
  $existing_favorite = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table_name 
         WHERE user_id = %d AND message_id = %d",
    $user_id,
    $message_id
  ));

  if ($existing_favorite) {
    // Update the existing favorite status
    $updated = $wpdb->update(
      $table_name,
      array('is_favorite' => $is_favorite),
      array('user_id' => $user_id, 'message_id' => $message_id),
      array('%d'),
      array('%d', '%d')
    );
  } else {
    // Insert a new favorite record
    $updated = $wpdb->insert(
      $table_name,
      array(
        'user_id' => $user_id,
        'message_id' => $message_id,
        'is_favorite' => $is_favorite
      ),
      array('%d', '%d', '%d')
    );
  }

  if ($updated !== false) {
    wp_send_json_success('Favorite status updated.');
  } else {
    wp_send_json_error('Failed to update favorite status.');
  }
}

function calculate_average_response_time($user_id)
{
  global $wpdb;

  $response_times = $wpdb->get_col($wpdb->prepare(
    "SELECT response_time 
       FROM {$wpdb->prefix}homey_messages 
       WHERE sender_id = %d AND response_time IS NOT NULL",
    $user_id
  ));

  if (empty($response_times)) {
    return null;
  }

  $total_response_time = array_sum($response_times);
  $average_response_time = $total_response_time / count($response_times);

  return $average_response_time;
}

function format_response_time($average_response_time)
{
  if ($average_response_time === null) {
    return "No responses yet";
  }

  $seconds = $average_response_time;
  $minutes = floor($seconds / 60);
  $hours = floor($seconds / 3600);
  $days = floor($seconds / 86400);
  $months = floor($seconds / 2592000);

  if ($months > 0) {
    return "Response time: $months month" . ($months > 1 ? "s" : "");
  } elseif ($days > 0) {
    return "Response time: $days day" . ($days > 1 ? "s" : "");
  } elseif ($hours > 0) {
    return "Response time: $hours hour" . ($hours > 1 ? "s" : "");
  } elseif ($minutes > 0) {
    return "Response time: $minutes minute" . ($minutes > 1 ? "s" : "");
  } else {
    $seconds = round($seconds);
    return "Response time: $seconds second" . ($seconds > 1 ? "s" : "");
  }
}

// Report Spam
add_action('wp_ajax_report_conversation_as_spam', 'report_conversation_as_spam');
function report_conversation_as_spam()
{
  if (!isset($_POST['user_id'])) {
    wp_send_json_error('Invalid request.');
    return;
  }

  $user_id = intval($_POST['user_id']);
  $current_user_id = get_current_user_id();

  if (!$current_user_id) {
    wp_send_json_error('You must be logged in to report a conversation as spam.');
    return;
  }

  global $wpdb;
  $table_name = $wpdb->prefix . 'homey_message_spam';

  // Check if the conversation is already marked as spam
  $existing_spam = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table_name 
         WHERE user_id = %d AND reported_by = %d",
    $user_id,
    $current_user_id
  ));

  if ($existing_spam) {
    wp_send_json_error('This conversation is already marked as spam.');
    return;
  }

  // Insert the spam record
  $inserted = $wpdb->insert(
    $table_name,
    array(
      'user_id' => $user_id,
      'reported_by' => $current_user_id,
      'reported_at' => current_time('mysql'),
    ),
    array('%d', '%d', '%s')
  );

  if ($inserted) {
    wp_send_json_success('Conversation reported as spam.');
  } else {
    wp_send_json_error('Failed to report conversation as spam.');
  }
}

// Archive a conversation
add_action('wp_ajax_archive_conversation', 'archive_conversation');
function archive_conversation()
{
  if (!isset($_POST['user_id'])) {
    wp_send_json_error('Invalid request.');
    return;
  }

  $user_id = intval($_POST['user_id']);
  $current_user_id = get_current_user_id();

  if (!$current_user_id) {
    wp_send_json_error('You must be logged in to archive a conversation.');
    return;
  }

  global $wpdb;
  $table_name = $wpdb->prefix . 'homey_message_archive';

  // Check if the conversation is already archived
  $existing_archive = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table_name 
         WHERE user_id = %d AND archived_by = %d",
    $user_id,
    $current_user_id
  ));

  if ($existing_archive) {
    wp_send_json_error('This conversation is already archived.');
    return;
  }

  // Insert the archive record
  $inserted = $wpdb->insert(
    $table_name,
    array(
      'user_id' => $user_id,
      'archived_by' => $current_user_id,
      'archived_at' => current_time('mysql'),
    ),
    array('%d', '%d', '%s')
  );

  if ($inserted) {
    wp_send_json_success('Conversation archived.');
  } else {
    wp_send_json_error('Failed to archive conversation.');
  }
}

// Block a conversation
add_action('wp_ajax_block_conversation', 'block_conversation');
function block_conversation()
{
  if (!isset($_POST['user_id'])) {
    wp_send_json_error('Invalid request.');
    return;
  }

  $user_id = intval($_POST['user_id']);
  $current_user_id = get_current_user_id();

  if (!$current_user_id) {
    wp_send_json_error('You must be logged in to block a conversation.');
    return;
  }

  global $wpdb;
  $table_name = $wpdb->prefix . 'homey_message_blocked';

  // Check if the conversation is already blocked
  $existing_block = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table_name 
         WHERE user_id = %d AND blocked_by = %d",
    $user_id,
    $current_user_id
  ));

  if ($existing_block) {
    wp_send_json_error('This conversation is already blocked.');
    return;
  }

  // Insert the block record
  $inserted = $wpdb->insert(
    $table_name,
    array(
      'user_id' => $user_id,
      'blocked_by' => $current_user_id,
      'blocked_at' => current_time('mysql'),
    ),
    array('%d', '%d', '%s')
  );

  if ($inserted) {
    wp_send_json_success('Conversation blocked.');
  } else {
    wp_send_json_error('Failed to block conversation.');
  }
}

add_action('wp_ajax_move_conversation_to_inbox', 'move_conversation_to_inbox');
function move_conversation_to_inbox()
{
  if (!isset($_POST['user_id'])) {
    wp_send_json_error('Invalid request.');
    return;
  }

  $user_id = intval($_POST['user_id']);
  $current_user_id = get_current_user_id();

  if (!$current_user_id) {
    wp_send_json_error('You must be logged in to move a conversation to the inbox.');
    return;
  }

  global $wpdb;

  // Tables and their respective "by" columns
  $tables = [
    $wpdb->prefix . 'homey_message_spam' => 'reported_by',
    $wpdb->prefix . 'homey_message_archive' => 'archived_by',
    $wpdb->prefix . 'homey_message_blocked' => 'blocked_by',
  ];

  $success = false;

  // Loop through each table and delete the record if it exists
  foreach ($tables as $table_name => $by_column) {
    $deleted = $wpdb->delete(
      $table_name,
      array(
        'user_id' => $user_id,
        $by_column => $current_user_id,
      ),
      array('%d', '%d')
    );

    if ($deleted !== false) {
      $success = true;
    }
  }

  if ($success) {
    wp_send_json_success('Conversation moved to inbox.');
  } else {
    wp_send_json_error('Failed to move conversation to inbox.');
  }
}

add_action('wp_ajax_fetch_spam_conversations', 'fetch_spam_conversations');
function fetch_spam_conversations()
{
  global $wpdb;
  $current_user_id = get_current_user_id();

  // Fetch spam conversations
  $spam_conversations = $wpdb->get_results($wpdb->prepare(
    "
        SELECT DISTINCT sender_id, receiver_id 
        FROM {$wpdb->prefix}homey_messages 
        WHERE (
            (sender_id = %d AND sender_role = %s) 
            OR (receiver_id = %d AND receiver_role = %s)
        )
        AND (
            sender_id IN (
                SELECT user_id 
                FROM {$wpdb->prefix}homey_message_spam 
                WHERE reported_by = %d
            )
            OR receiver_id IN (
                SELECT user_id 
                FROM {$wpdb->prefix}homey_message_spam 
                WHERE reported_by = %d
            )
        )",
    $current_user_id,
    homey_get_user_role($current_user_id),
    $current_user_id,
    homey_get_user_role($current_user_id),
    $current_user_id,
    $current_user_id
  ));

  if (!$spam_conversations) {
    wp_send_json_success('<p>No spam conversation found.</p>');
    return;
  }

  $users_list = [];
  foreach ($spam_conversations as $user) {
    if ($user->sender_id != $current_user_id) {
      $users_list[$user->sender_id] = get_userdata($user->sender_id);
    }
    if ($user->receiver_id != $current_user_id) {
      $users_list[$user->receiver_id] = get_userdata($user->receiver_id);
    }
  }

  $html = '';
  foreach ($users_list as $user_id => $user_data) {
    $username = get_the_author_meta('user_login', $user_id);
    $user_role = homey_get_user_role($user_id);
    $display_name_public = $user_role == 'homey_renter' ? get_the_author_meta('display_name_public_guest', $user_id) : get_the_author_meta('display_name_public', $user_id);
    $user_name = empty($display_name_public) ? $username : $display_name_public;
    $city = '';
    $city = $user_role == 'homey_renter' ? get_the_author_meta('homey_city_guest', $user_id) : get_the_author_meta('homey_city', $user_id);
    if (!empty($city)) {
      $city_name = ' - ' . esc_html($city);
    } else {
      $city_name = '';
    }
    $homey_author = homey_get_author_by_id('50', '50', 'img-circle', $user_id);
    $user_image = $homey_author['photo'];
    $profile_link = get_author_posts_url($user_id);

    $last_message = $wpdb->get_row($wpdb->prepare(
      "
            SELECT message, created_at 
            FROM {$wpdb->prefix}homey_messages 
            WHERE (
                (sender_id = %d AND receiver_id = %d AND sender_role = %s) 
                OR (sender_id = %d AND receiver_id = %d AND receiver_role = %s)
            )
            ORDER BY created_at DESC
            LIMIT 1",
      $current_user_id,
      $user_id,
      homey_get_user_role($current_user_id),
      $user_id,
      $current_user_id,
      homey_get_user_role($current_user_id)
    ));

    $last_message_text = $last_message ? esc_html($last_message->message) : 'No messages yet.';
    $last_message_date = $last_message ? date_i18n('F j', strtotime($last_message->created_at)) : '';

    $html .= '<li class="message-user" data-user-id="' . esc_attr($user_id) . '">';
    $html .= '<div class="message-user-info">';
    $html .= '<div class="message-user-img">' . $user_image . '</div>';
    $html .= '<div class="message-user-content">';
    $html .= '<span class="user-name"><a href="' . $profile_link . '" class="user-name-link">' . esc_html($user_name) . '</a></span>';
    $html .= '<span class="user-name-city">' . $city_name . '</span>';
    $html .= '<span class="last-message">' . esc_html($last_message_text) . '</span>';
    if ($last_message_date) {
      $html .= '<span class="last-message-date">Last message sent on ' . esc_html($last_message_date) . '</span>';
    }
    $html .= '</div>';
    $html .= '<div class="move-to-inbox">';
    $html .= '<button class="btn btn-move-inbox msg-spam-button" data-user-id="' . esc_attr($user_id) . '">Move to Inbox</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</li>';
  }

  wp_send_json_success($html);
}

// Fetch Archive Conversations
add_action('wp_ajax_fetch_archive_conversations', 'fetch_archive_conversations');
function fetch_archive_conversations()
{
  global $wpdb;
  $current_user_id = get_current_user_id();

  // Fetch archived conversations
  $archive_conversations = $wpdb->get_results($wpdb->prepare(
    "
        SELECT DISTINCT sender_id, receiver_id 
        FROM {$wpdb->prefix}homey_messages 
        WHERE (
            (sender_id = %d AND sender_role = %s) 
            OR (receiver_id = %d AND receiver_role = %s)
        )
        AND (
            sender_id IN (
                SELECT user_id 
                FROM {$wpdb->prefix}homey_message_archive 
                WHERE archived_by = %d
            )
            OR receiver_id IN (
                SELECT user_id 
                FROM {$wpdb->prefix}homey_message_archive 
                WHERE archived_by = %d
            )
        )",
    $current_user_id,
    homey_get_user_role($current_user_id),
    $current_user_id,
    homey_get_user_role($current_user_id),
    $current_user_id,
    $current_user_id
  ));

  if (!$archive_conversations) {
    wp_send_json_success('<p>No archived conversation found.</p>');
    return;
  }

  $users_list = [];
  foreach ($archive_conversations as $user) {
    if ($user->sender_id != $current_user_id) {
      $users_list[$user->sender_id] = get_userdata($user->sender_id);
    }
    if ($user->receiver_id != $current_user_id) {
      $users_list[$user->receiver_id] = get_userdata($user->receiver_id);
    }
  }

  $html = '';
  foreach ($users_list as $user_id => $user_data) {
    $username = get_the_author_meta('user_login', $user_id);
    $user_role = homey_get_user_role($user_id);
    $display_name_public = $user_role == 'homey_renter' ? get_the_author_meta('display_name_public_guest', $user_id) : get_the_author_meta('display_name_public', $user_id);
    $user_name = empty($display_name_public) ? $username : $display_name_public;
    $city = '';
    $city = $user_role == 'homey_renter' ? get_the_author_meta('homey_city_guest', $user_id) : get_the_author_meta('homey_city', $user_id);
    if (!empty($city)) {
      $city_name = ' - ' . esc_html($city);
    } else {
      $city_name = '';
    }
    $homey_author = homey_get_author_by_id('50', '50', 'img-circle', $user_id);
    $user_image = $homey_author['photo'];
    $profile_link = get_author_posts_url($user_id);

    $last_message = $wpdb->get_row($wpdb->prepare(
      "
            SELECT message, created_at 
            FROM {$wpdb->prefix}homey_messages 
            WHERE (
                (sender_id = %d AND receiver_id = %d AND sender_role = %s) 
                OR (sender_id = %d AND receiver_id = %d AND receiver_role = %s)
            )
            ORDER BY created_at DESC
            LIMIT 1",
      $current_user_id,
      $user_id,
      homey_get_user_role($current_user_id),
      $user_id,
      $current_user_id,
      homey_get_user_role($current_user_id)
    ));

    $last_message_text = $last_message ? esc_html($last_message->message) : 'No messages yet.';
    $last_message_date = $last_message ? date_i18n('F j', strtotime($last_message->created_at)) : '';

    $html .= '<li class="message-user" data-user-id="' . esc_attr($user_id) . '">';
    $html .= '<div class="message-user-info">';
    $html .= '<div class="message-user-img">' . $user_image . '</div>';
    $html .= '<div class="message-user-content">';
    $html .= '<span class="user-name"><a href="' . $profile_link . '" class="user-name-link">' . esc_html($user_name) . '</a></span>';
    $html .= '<span class="user-name-city">' . $city_name . '</span>';
    $html .= '<span class="last-message">' . esc_html($last_message_text) . '</span>';
    if ($last_message_date) {
      $html .= '<span class="last-message-date">Last message sent on ' . esc_html($last_message_date) . '</span>';
    }
    $html .= '</div>';
    $html .= '<div class="move-to-inbox">';
    $html .= '<button class="btn btn-move-inbox msg-spam-button" data-user-id="' . esc_attr($user_id) . '">Move to Inbox</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</li>';
  }

  wp_send_json_success($html);
}

// Fetch Blocked Conversations
add_action('wp_ajax_fetch_blocked_conversations', 'fetch_blocked_conversations');
function fetch_blocked_conversations()
{
  global $wpdb;
  $current_user_id = get_current_user_id();

  // Fetch blocked conversations
  $blocked_conversations = $wpdb->get_results($wpdb->prepare(
    "
        SELECT DISTINCT sender_id, receiver_id 
        FROM {$wpdb->prefix}homey_messages 
        WHERE (
            (sender_id = %d AND sender_role = %s) 
            OR (receiver_id = %d AND receiver_role = %s)
        )
        AND (
            sender_id IN (
                SELECT user_id 
                FROM {$wpdb->prefix}homey_message_blocked 
                WHERE blocked_by = %d
            )
            OR receiver_id IN (
                SELECT user_id 
                FROM {$wpdb->prefix}homey_message_blocked 
                WHERE blocked_by = %d
            )
        )",
    $current_user_id,
    homey_get_user_role($current_user_id),
    $current_user_id,
    homey_get_user_role($current_user_id),
    $current_user_id,
    $current_user_id
  ));

  if (!$blocked_conversations) {
    wp_send_json_success('<p>No blocked conversation found.</p>');
    return;
  }

  $users_list = [];
  foreach ($blocked_conversations as $user) {
    if ($user->sender_id != $current_user_id) {
      $users_list[$user->sender_id] = get_userdata($user->sender_id);
    }
    if ($user->receiver_id != $current_user_id) {
      $users_list[$user->receiver_id] = get_userdata($user->receiver_id);
    }
  }

  $html = '';
  foreach ($users_list as $user_id => $user_data) {
    $username = get_the_author_meta('user_login', $user_id);
    $user_role = homey_get_user_role($user_id);
    $display_name_public = $user_role == 'homey_renter' ? get_the_author_meta('display_name_public_guest', $user_id) : get_the_author_meta('display_name_public', $user_id);
    $user_name = empty($display_name_public) ? $username : $display_name_public;
    $city = '';
    $city = $user_role == 'homey_renter' ? get_the_author_meta('homey_city_guest', $user_id) : get_the_author_meta('homey_city', $user_id);
    if (!empty($city)) {
      $city_name = ' - ' . esc_html($city);
    } else {
      $city_name = '';
    }
    $homey_author = homey_get_author_by_id('50', '50', 'img-circle', $user_id);
    $user_image = $homey_author['photo'];
    $profile_link = get_author_posts_url($user_id);

    $last_message = $wpdb->get_row($wpdb->prepare(
      "
            SELECT message, created_at 
            FROM {$wpdb->prefix}homey_messages 
            WHERE (
                (sender_id = %d AND receiver_id = %d AND sender_role = %s) 
                OR (sender_id = %d AND receiver_id = %d AND receiver_role = %s)
            )
            ORDER BY created_at DESC
            LIMIT 1",
      $current_user_id,
      $user_id,
      homey_get_user_role($current_user_id),
      $user_id,
      $current_user_id,
      homey_get_user_role($current_user_id)
    ));

    $last_message_text = $last_message ? esc_html($last_message->message) : 'No messages yet.';
    $last_message_date = $last_message ? date_i18n('F j', strtotime($last_message->created_at)) : '';

    $html .= '<li class="message-user" data-user-id="' . esc_attr($user_id) . '">';
    $html .= '<div class="message-user-info">';
    $html .= '<div class="message-user-img">' . $user_image . '</div>';
    $html .= '<div class="message-user-content">';
    $html .= '<span class="user-name"><a href="' . $profile_link . '" class="user-name-link">' . esc_html($user_name) . '</a></span>';
    $html .= '<span class="user-name-city">' . $city_name . '</span>';
    $html .= '<span class="last-message">' . esc_html($last_message_text) . '</span>';
    if ($last_message_date) {
      $html .= '<span class="last-message-date">Last message sent on ' . esc_html($last_message_date) . '</span>';
    }
    $html .= '</div>';
    $html .= '<div class="move-to-inbox">';
    $html .= '<button class="btn btn-move-inbox msg-spam-button" data-user-id="' . esc_attr($user_id) . '">Move to Inbox</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</li>';
  }

  wp_send_json_success($html);
}

add_action('wp_ajax_fetch_regular_conversations', 'fetch_regular_conversations');
function fetch_regular_conversations()
{
  global $wpdb;
  $current_user_id = get_current_user_id();

  // Fetch regular conversations (excluding spam)
  $users = $wpdb->get_results($wpdb->prepare(
    "
    SELECT DISTINCT sender_id, receiver_id 
    FROM {$wpdb->prefix}homey_messages 
    WHERE (
        (sender_id = %d AND sender_role = %s) 
        OR (receiver_id = %d AND receiver_role = %s)
    )
    AND sender_id NOT IN (
        SELECT user_id 
        FROM {$wpdb->prefix}homey_message_spam 
        WHERE reported_by = %d
    )
    AND sender_id NOT IN (
        SELECT user_id 
        FROM {$wpdb->prefix}homey_message_archive 
        WHERE archived_by = %d
    )
    AND sender_id NOT IN (
        SELECT user_id 
        FROM {$wpdb->prefix}homey_message_blocked 
        WHERE blocked_by = %d
    )
    AND receiver_id NOT IN (
        SELECT user_id 
        FROM {$wpdb->prefix}homey_message_spam 
        WHERE reported_by = %d
    )
    AND receiver_id NOT IN (
        SELECT user_id 
        FROM {$wpdb->prefix}homey_message_archive 
        WHERE archived_by = %d
    )
    AND receiver_id NOT IN (
        SELECT user_id 
        FROM {$wpdb->prefix}homey_message_blocked 
        WHERE blocked_by = %d
    )",
    $current_user_id,
    homey_get_user_role($current_user_id),
    $current_user_id,
    homey_get_user_role($current_user_id),
    $current_user_id,
    $current_user_id,
    $current_user_id,
    $current_user_id,
    $current_user_id,
    $current_user_id
  ));

  if (!$users) {
    wp_send_json_success('<p>No inbox conversation found.</p>');
    return;
  }

  $users_list = [];
  foreach ($users as $user) {
    if ($user->sender_id != $current_user_id) {
      $users_list[$user->sender_id] = get_userdata($user->sender_id);
    }
    if ($user->receiver_id != $current_user_id) {
      $users_list[$user->receiver_id] = get_userdata($user->receiver_id);
    }
  }

  $html = '';
  foreach ($users_list as $user_id => $user_data) {
    $username = get_the_author_meta('user_login', $user_id);
    $user_role = homey_get_user_role($user_id);
    $display_name_public = $user_role == 'homey_renter' ? get_the_author_meta('display_name_public_guest', $user_id) : get_the_author_meta('display_name_public', $user_id);
    $user_name = empty($display_name_public) ? $username : $display_name_public;
    $city = '';
    $city = $user_role == 'homey_renter' ? get_the_author_meta('homey_city_guest', $user_id) : get_the_author_meta('homey_city', $user_id);
    if (!empty($city)) {
      $city_name = ' - ' . esc_html($city);
    } else {
      $city_name = '';
    }
    $homey_author = homey_get_author_by_id('50', '50', 'img-circle', $user_id);
    $user_image = $homey_author['photo'];
    $profile_link = get_author_posts_url($user_id);

    $last_message = $wpdb->get_row($wpdb->prepare(
      "
            SELECT message, created_at 
            FROM {$wpdb->prefix}homey_messages 
            WHERE (
                (sender_id = %d AND receiver_id = %d AND sender_role = %s) 
                OR (sender_id = %d AND receiver_id = %d AND receiver_role = %s)
            )
            ORDER BY created_at DESC
            LIMIT 1",
      $current_user_id,
      $user_id,
      homey_get_user_role($current_user_id),
      $user_id,
      $current_user_id,
      homey_get_user_role($current_user_id)
    ));

    $last_message_text = $last_message ? esc_html($last_message->message) : 'No messages yet.';
    $last_message_date = $last_message ? date_i18n('F j', strtotime($last_message->created_at)) : '';

    $html .= '<li class="message-user" data-user-id="' . esc_attr($user_id) . '">';
    $html .= '<div class="message-user-info">';
    $html .= '<div class="message-user-img">' . $user_image . '</div>';
    $html .= '<div class="message-user-content">';
    $html .= '<span class="user-name"><a href="' . $profile_link . '" class="user-name-link">' . esc_html($user_name) . '</a></span>';
    $html .= '<span class="user-name-city">' . $city_name . '</span>';
    $html .= '<span class="last-message">' . esc_html($last_message_text) . '</span>';
    if ($last_message_date) {
      $html .= '<span class="last-message-date">Last message sent on ' . esc_html($last_message_date) . '</span>';
    }
    $html .= '</div>';
    $html .= '<div class="report-msg-buttons">';
    $html .= '<button class="btn btn-report-spam msg-spam-button" data-user-id="' . esc_attr($user_id) . '">Report as Spam</button>';
    $html .= '<button class="btn btn-report-archive msg-spam-button" data-user-id="' . esc_attr($user_id) . '">Archive Messages</button>';
    $html .= '<button class="btn btn-report-block msg-spam-button" data-user-id="' . esc_attr($user_id) . '">Block Messages</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</li>';
  }

  wp_send_json_success($html);
}

function send_booking_message($reservation_id, $sender_id, $receiver_id, $message)
{
  global $wpdb;
  $table_name = $wpdb->prefix . 'homey_messages';

  $sender_role = homey_get_user_role($sender_id);
  $receiver_role = homey_get_user_role($receiver_id);

  $wpdb->insert(
    $table_name,
    array(
      'sender_id' => $sender_id,
      'receiver_id' => $receiver_id,
      'sender_role' => $sender_role,
      'receiver_role' => $receiver_role,
      'message' => $message,
      'reservation_id' => $reservation_id,
      'created_at' => current_time('mysql'),
    ),
    array('%d', '%d', '%s', '%s', '%s', '%d', '%s')
  );
}

add_action('set_user_role', 'handle_role_change', 10, 3);

function handle_role_change($user_id, $new_role, $old_roles)
{
  global $wpdb;

  $original_role = get_user_meta($user_id, 'original_role', true);

  // If the user is switching back to their original role
  if ($new_role === $original_role) {
    $chats = $wpdb->get_results($wpdb->prepare(
      "SELECT DISTINCT sender_id 
       FROM {$wpdb->prefix}homey_messages 
       WHERE receiver_id = %d",
      $user_id
    ));

    if (!empty($chats)) {
      foreach ($chats as $chat) {
        $other_user_id = ($chat->sender_id == $user_id) ? $chat->receiver_id : $chat->sender_id;
        $receiver_role = homey_get_user_role($other_user_id);

        $wpdb->insert(
          "{$wpdb->prefix}homey_messages",
          array(
            'sender_id' => $user_id,
            'receiver_id' => $other_user_id,
            'message' => 'This user has switched back to that role. You can now send them messages again.',
            'receiver_role' => $receiver_role,
            'created_at' => current_time('mysql')
          )
        );
      }
    }

    delete_user_meta($user_id, 'original_role');
  } else {

    if (empty($original_role)) {
      $original_role = '';
      foreach ($old_roles as $role) {
        if (in_array($role, ['homey_host', 'homey_renter'])) {
          $original_role = $role;
          break;
        }
      }

      if (empty($original_role)) {
        $original_role = !empty($old_roles) ? $old_roles[0] : '';
      }

      update_user_meta($user_id, 'original_role', $original_role);
    }

    $chats = $wpdb->get_results($wpdb->prepare(
      "SELECT DISTINCT sender_id 
       FROM {$wpdb->prefix}homey_messages 
       WHERE receiver_id = %d",
      $user_id
    ));

    if (!empty($chats)) {
      foreach ($chats as $chat) {
        $other_user_id = ($chat->sender_id == $user_id) ? $chat->receiver_id : $chat->sender_id;
        $receiver_role = homey_get_user_role($other_user_id);

        $wpdb->insert(
          "{$wpdb->prefix}homey_messages",
          array(
            'sender_id' => $user_id,
            'receiver_id' => $other_user_id,
            'message' => 'This user has changed their role and cannot receive messages at the moment. You can wait until they switch back to that role again.',
            'receiver_role' => $receiver_role,
            'created_at' => current_time('mysql')
          )
        );
      }
    }
  }
}

add_action('wp_ajax_switch_user_role', 'handle_switch_user_role_ajax');

function handle_switch_user_role_ajax()
{

  $current_user_id = get_current_user_id();
  $current_role = homey_get_user_role($current_user_id);

  if (!$current_role) {
    wp_send_json_error('Primary role not found.');
  }

  $new_role = ($current_role == 'homey_host') ? 'homey_renter' : 'homey_host';

  $result = wp_update_user(['ID' => $current_user_id, 'role' => $new_role]);

  if (is_wp_error($result)) {
    wp_send_json_error('Failed to update user role.');
  }

  update_user_meta($current_user_id, 'primary_role', $new_role);

  wp_send_json_success([
    'new_role' => $new_role,
    'message' => 'Role switched successfully.'
  ]);
}


function jsl_edit_hourly_reservation()
{
  //error_reporting(E_ALL);
  //ini_set('display_errors', 1);
  if (!isset($_POST)) {
    echo json_encode(array('success' => false, 'message' => 'Invalid request.'));
    wp_die();
  }
  $allowded_html = array();

  $listing_id = $_POST['listing_id'];

  $listing_renter = get_post_meta($listing_id, 'listing_renter', true);
  $guest_phone_number = get_the_author_meta('homey_phone_number', $listing_renter);
  $notification_settings_guest = get_user_meta($listing_renter, 'notification_settings', true);

  $listing_owner = get_post_meta($listing_id, 'listing_owner', true);
  $username = get_the_author_meta('user_login', $listing_owner);
  $display_name_public = get_the_author_meta('display_name_public', $listing_owner);
  $host_name = empty($display_name_public) ? $username : $display_name_public;

  $check_in_date = wp_kses($_POST['check_in_date'], $allowded_html);
  $start_hour = wp_kses($_POST['start_hour'], $allowded_html);
  $end_hour = wp_kses($_POST['end_hour'], $allowded_html);

  $check_in_hour = $check_in_date . ' ' . $start_hour;
  $check_out_hour = $check_in_date . ' ' . $end_hour;

  $reservation_meta = get_post_meta($listing_id, 'reservation_meta', true);
  $reservation_meta['new_check_in_date'] = $check_in_date;
  $reservation_meta['new_check_in_hour'] = $check_in_hour;
  $reservation_meta['new_check_out_hour'] = $check_out_hour;
  $reservation_meta['new_start_hour'] = $start_hour;
  $reservation_meta['new_end_hour'] = $end_hour;
  $reservation_meta['time_change_requested'] = 1;

  $updated = update_post_meta($listing_id, 'reservation_meta', $reservation_meta);

  $strat_hour = date(homey_time_format(), strtotime($reservation_meta['start_hour']));
  $end_hour = date(homey_time_format(), strtotime($reservation_meta['end_hour']));
  $strat_new_hour = date(homey_time_format(), strtotime($reservation_meta['new_start_hour']));
  $end_new_hour = date(homey_time_format(), strtotime($reservation_meta['new_end_hour']));

  if ($updated) {

    $notification_title = $host_name . ' requested a time change in your reservation #' . $listing_id;
    $notification_content = $host_name . ' requested a time change in your reservation #' . $listing_id;
    $notification_link = home_url('/reservations/?reservation_detail=' . $listing_id);
    save_booking_notification($listing_renter, $notification_title, $notification_content, $notification_link);

    if (isset($notification_settings_guest['email']) && $notification_settings_guest['email']) {
      $user_email = get_the_author_meta('user_email', $listing_renter);
      $subject = $host_name . ' requested a time change in your reservation #' . $listing_id;
      $logo_url = wp_get_attachment_url(7179);
      $button_url = home_url('/reservations/?reservation_detail=' . $listing_id);

      $message = '
        <div style="font-family: \'Oswald\', sans-serif;text-align: left; padding: 20px; margin: 0 auto;">
            <!-- Image -->
            <div style="margin-bottom: 10px;">
                <img src="' . esc_url($logo_url) . '" width="100" height="100" alt="Coupon Image" style="max-width: 100%; height: auto;">
            </div>
            
            <p style="font-size: 24px; color: #3A3D32; font-weight: 800;">
              ' . $host_name . ' requested a time change in your reservation # ' . $listing_id . '
            </p>
  
            <p style="font-size: 14px; color: #3A3D32; font-weight: normal; margin-top: 10px;">
              Check out reservation #' . $listing_id . ' to see the time change request.
            </p>
  
            <div style="margin:30px">
            <p style="font-size: 14px; color: #222; font-weight: bold;">
              ORIGINAL TIME:
            </p>
            <p style="font-size: 14px; color: #222; font-weight: normal;">
              <s>' . $strat_hour . ' - ' . $end_hour . '</s>
            </p>
            <p style="font-size: 14px; color: #0072ff; font-weight: bold;">
              UPDATED TIME:
            </p>
            <p style="font-size: 14px; color: #0072ff; font-weight: normal;">
              ' . $strat_new_hour . ' - ' . $end_new_hour . '
            </p>
  
            <div style="margin-top: 15px;padding-top: 20px;text-align:center">
              <a href="' . $button_url . '" style="display: inline-block;padding: 8px 30px;font-size: 14px;color: #0072ff;background-color: transparent;text-decoration: none;font-weight: 600;border:1px solid #0072ff;border-radius:5px">
                See Time Change Request
              </a>
            </div>
            <hr>
            <p style="font-size: 14px; color: #3A3D32; font-weight: bold; margin-top: 30px;text-align:center;">
              Need hosting help<br>
              Reach out to us at <a href="mailto:info@backyardlease.com" style="color: #3A3D32; font-weight: bold;">info@backyardlease.com</a>
            </p>
            </div>
        </div>';
      $headers = array('Content-Type: text/html; charset=UTF-8');
      wp_mail($user_email, $subject, $message, $headers);
    }

    $reservation_url = home_url('/reservations/?reservation_detail=' . $listing_id);
    if (isset($notification_settings_guest['sms']) && $notification_settings_guest['sms']) {
      if (!empty($guest_phone_number)) {
        $guest_message = 'BACKYARD LEASE: ' . $host_name . ' requested a time change in your reservation #' . $listing_id . ". See details here\n" . $reservation_url;
        homey_send_sms($guest_phone_number, $guest_message);
      }
    }
    echo json_encode(array('success' => true, 'message' => 'Reservation time change requested.'));
  } else {
    echo json_encode(array('success' => false, 'message' => 'Failed to request a change in reservation time.'));
  }
  wp_die();
}
add_action('wp_ajax_jsl_edit_hourly_reservation', 'jsl_edit_hourly_reservation');

function jsl_confirm_update_reservation()
{
  //error_reporting(E_ALL);
  //ini_set('display_errors', 1);
  if (!isset($_POST)) {
    echo json_encode(array('success' => false, 'message' => 'Invalid request.'));
    wp_die();
  }

  $listing_id = $_POST['listing_id'];

  $listing_owner = get_post_meta($listing_id, 'listing_owner', true);
  $host_phone_number = get_the_author_meta('homey_phone_number', $listing_owner);
  $notification_settings_host = get_user_meta($listing_owner, 'notification_settings', true);

  $listing_renter = get_post_meta($listing_id, 'listing_renter', true);
  $username = get_the_author_meta('user_login', $listing_renter);
  $display_name_public = get_the_author_meta('display_name_public', $listing_renter);
  $guest_name = empty($display_name_public) ? $username : $display_name_public;

  $reservation_meta = get_post_meta($listing_id, 'reservation_meta', true);
  $strat_hour = date(homey_time_format(), strtotime($reservation_meta['start_hour']));
  $end_hour = date(homey_time_format(), strtotime($reservation_meta['end_hour']));
  $strat_new_hour = date(homey_time_format(), strtotime($reservation_meta['new_start_hour']));
  $end_new_hour = date(homey_time_format(), strtotime($reservation_meta['new_end_hour']));

  $reservation_meta['check_in_date'] = $reservation_meta['new_check_in_date'];
  $reservation_meta['check_in_hour'] = $reservation_meta['new_check_in_hour'];
  $reservation_meta['check_out_hour'] = $reservation_meta['new_check_out_hour'];
  $reservation_meta['start_hour'] = $reservation_meta['new_start_hour'];
  $reservation_meta['end_hour'] = $reservation_meta['new_end_hour'];
  $reservation_meta['time_change_requested'] = 0;

  $updated = update_post_meta($listing_id, 'reservation_meta', $reservation_meta);

  if ($updated) {

    $notification_title = $guest_name . ' accepted your time change request for booking #' . $listing_id;
    $notification_content = $guest_name . ' accepted your time change request for booking #' . $listing_id;
    $notification_link = home_url('/reservations/?reservation_detail=' . $listing_id);
    save_booking_notification($listing_owner, $notification_title, $notification_content, $notification_link);


    if (isset($notification_settings_host['email']) && $notification_settings_host['email']) {
      $user_email = get_the_author_meta('user_email', $listing_owner);
      $subject = $guest_name . ' accepted your changes to their booking';
      $logo_url = wp_get_attachment_url(7179);
      $button_url = home_url('/reservations/?reservation_detail=' . $listing_id);

      $message = '
        <div style="font-family: \'Oswald\', sans-serif;text-align: left; padding: 20px; margin: 0 auto;">
            <!-- Image -->
            <div style="margin-bottom: 10px;">
                <img src="' . esc_url($logo_url) . '" width="100" height="100" alt="Coupon Image" style="max-width: 100%; height: auto;">
            </div>
            
            <p style="font-size: 24px; color: #3A3D32; font-weight: 800;">
              ' . $guest_name . ' accepted your requested changes to their booking
            </p>
  
            <p style="font-size: 14px; color: #3A3D32; font-weight: normal; margin-top: 10px;">
              Check out booking #' . $listing_id . ' to see the updated reservation.
            </p>
  
            <div style="margin:30px">
            <p style="font-size: 14px; color: #222; font-weight: bold;">
              ORIGINAL TIME:
            </p>
            <p style="font-size: 14px; color: #222; font-weight: normal;">
              <s>' . $strat_hour . ' - ' . $end_hour . '</s>
            </p>
            <p style="font-size: 14px; color: #0072ff; font-weight: bold;">
              UPDATED TIME:
            </p>
            <p style="font-size: 14px; color: #0072ff; font-weight: normal;">
              ' . $strat_new_hour . ' - ' . $end_new_hour . '
            </p>
  
            <div style="margin-top: 15px;padding-top: 20px;text-align:center">
              <a href="' . $button_url . '" style="display: inline-block;padding: 8px 30px;font-size: 14px;color: #0072ff;background-color: transparent;text-decoration: none;font-weight: 600;border:1px solid #0072ff;border-radius:5px">
                See Updated Booking
              </a>
            </div>
            <hr>
            <p style="font-size: 14px; color: #3A3D32; font-weight: bold; margin-top: 30px;text-align:center;">
              Need hosting help<br>
              Reach out to us at <a href="mailto:info@backyardlease.com" style="color: #3A3D32; font-weight: bold;">info@backyardlease.com</a>
            </p>
            </div>
        </div>';
      $headers = array('Content-Type: text/html; charset=UTF-8');
      wp_mail($user_email, $subject, $message, $headers);
    }

    $reservation_url = home_url('/reservations/?reservation_detail=' . $listing_id);
    if (isset($notification_settings_host['sms']) && $notification_settings_host['sms']) {
      if (!empty($host_phone_number)) {
        $host_message = 'BACKYARD LEASE: ' . $guest_name . ' accepted your requested changes to their booking #' . $listing_id . ". See details here\n" . $reservation_url;
        homey_send_sms($host_phone_number, $host_message);
      }
    }
    echo json_encode(array('success' => true, 'message' => 'Reservation time change request confirmed.'));
  } else {
    echo json_encode(array('success' => false, 'message' => 'Failed to confirm a change in reservation time.'));
  }
  wp_die();
}
add_action('wp_ajax_jsl_confirm_update_reservation', 'jsl_confirm_update_reservation');

function jsl_reject_update_reservation()
{
  //error_reporting(E_ALL);
  //ini_set('display_errors', 1);
  if (!isset($_POST)) {
    echo json_encode(array('success' => false, 'message' => 'Invalid request.'));
    wp_die();
  }

  $listing_id = $_POST['listing_id'];

  $listing_owner = get_post_meta($listing_id, 'listing_owner', true);
  $host_phone_number = get_the_author_meta('homey_phone_number', $listing_owner);
  $notification_settings_host = get_user_meta($listing_owner, 'notification_settings', true);

  $listing_renter = get_post_meta($listing_id, 'listing_renter', true);
  $username = get_the_author_meta('user_login', $listing_renter);
  $display_name_public = get_the_author_meta('display_name_public', $listing_renter);
  $guest_name = empty($display_name_public) ? $username : $display_name_public;

  $reservation_meta = get_post_meta($listing_id, 'reservation_meta', true);
  $strat_hour = date(homey_time_format(), strtotime($reservation_meta['start_hour']));
  $end_hour = date(homey_time_format(), strtotime($reservation_meta['end_hour']));
  $strat_new_hour = date(homey_time_format(), strtotime($reservation_meta['new_start_hour']));
  $end_new_hour = date(homey_time_format(), strtotime($reservation_meta['new_end_hour']));
  $reservation_meta['time_change_requested'] = 0;

  $updated = update_post_meta($listing_id, 'reservation_meta', $reservation_meta);

  if ($updated) {
    $notification_title = $guest_name . ' denied your time change request for booking #' . $listing_id;
    $notification_content = $guest_name . ' denied your time change request for booking #' . $listing_id;
    $notification_link = home_url('/reservations/?reservation_detail=' . $listing_id);
    save_booking_notification($listing_owner, $notification_title, $notification_content, $notification_link);

    if (isset($notification_settings_host['email']) && $notification_settings_host['email']) {
      $user_email = get_the_author_meta('user_email', $listing_owner);
      $subject = $guest_name . ' denied your changes to their booking';
      $logo_url = wp_get_attachment_url(7179);
      $button_url = home_url('/reservations/?reservation_detail=' . $listing_id);

      $message = '
        <div style="font-family: \'Oswald\', sans-serif;text-align: left; padding: 20px; margin: 0 auto;">
            <!-- Image -->
            <div style="margin-bottom: 10px;">
                <img src="' . esc_url($logo_url) . '" width="100" height="100" alt="Coupon Image" style="max-width: 100%; height: auto;">
            </div>
            
            <p style="font-size: 24px; color: #3A3D32; font-weight: 800;">
              ' . $guest_name . ' denied your requested changes to their booking
            </p>
  
            <p style="font-size: 14px; color: #3A3D32; font-weight: normal; margin-top: 10px;">
              Check out booking #' . $listing_id . ' to see the updated reservation.
            </p>
  
            <div style="margin:30px">
            <p style="font-size: 14px; color: #222; font-weight: bold;">
              ORIGINAL TIME:
            </p>
            <p style="font-size: 14px; color: #222; font-weight: normal;">
              <s>' . $strat_hour . ' - ' . $end_hour . '</s>
            </p>
            <p style="font-size: 14px; color: #0072ff; font-weight: bold;">
              UPDATED TIME:
            </p>
            <p style="font-size: 14px; color: #0072ff; font-weight: normal;">
              ' . $strat_new_hour . ' - ' . $end_new_hour . '
            </p>
  
            <div style="margin-top: 15px;padding-top: 20px;text-align:center">
              <a href="' . $button_url . '" style="display: inline-block;padding: 8px 30px;font-size: 14px;color: #0072ff;background-color: transparent;text-decoration: none;font-weight: 600;border:1px solid #0072ff;border-radius:5px">
                See Updated Booking
              </a>
            </div>
            <hr>
            <p style="font-size: 14px; color: #3A3D32; font-weight: bold; margin-top: 30px;text-align:center;">
              Need hosting help<br>
              Reach out to us at <a href="mailto:info@backyardlease.com" style="color: #3A3D32; font-weight: bold;">info@backyardlease.com</a>
            </p>
            </div>
        </div>';
      $headers = array('Content-Type: text/html; charset=UTF-8');
      wp_mail($user_email, $subject, $message, $headers);
    }

    $reservation_url = home_url('/reservations/?reservation_detail=' . $listing_id);
    if (isset($notification_settings_host['sms']) && $notification_settings_host['sms']) {
      if (!empty($host_phone_number)) {
        $host_message = 'BACKYARD LEASE: ' . $guest_name . ' denied your requested changes to their booking #' . $listing_id . ". See details here\n" . $reservation_url;
        homey_send_sms($host_phone_number, $host_message);
      }
    }
    echo json_encode(array('success' => true, 'message' => 'Reservation time change request denied.'));
  } else {
    echo json_encode(array('success' => false, 'message' => 'Failed to deny a change in reservation time.'));
  }
  wp_die();
}
add_action('wp_ajax_jsl_reject_update_reservation', 'jsl_reject_update_reservation');

/*
if (!function_exists('homey_add_blackout_dates')) {
  function homey_add_blackout_dates()
  {
    global $current_user;

    $current_user = wp_get_current_user();
    $userID       = $current_user->ID;


    $current_user = wp_get_current_user();
    $userID       = $current_user->ID;

    $local = homey_get_localization();
    $allowded_html = array();
    $reservation_meta = array();

    $listing_id = intval($_POST['listing_id']);
    $listing_owner_id  =  get_post_field('post_author', $listing_id);
    $check_in_date     =  wp_kses($_POST['check_in_date'], $allowded_html);
    $check_out_date    =  wp_kses($_POST['check_out_date'], $allowded_html);
    $guests   =  intval($_POST['guests']);
    $title = 'BLACKOUT';

    $booking_type = homey_booking_type_by_id($listing_id);

    $owner = homey_usermeta($listing_owner_id);
    $owner_email = $owner['email'];

    if (!is_user_logged_in() || $userID === 0) {
      echo json_encode(
        array(
          'success' => false,
          'message' => $local['login_for_reservation']
        )
      );
      wp_die();
    }

    if ($userID == $listing_owner_id) {
      echo json_encode(
        array(
          'success' => false,
          'message' => $local['own_listing_error']
        )
      );
      wp_die();
    }

    if (strtotime($check_out_date) <= strtotime($check_in_date)) {
      echo json_encode(
        array(
          'success' => false,
          'message' => $local['dates_not_available']
        )
      );
      wp_die();
    }

    $check_availability = check_booking_availability($check_in_date, $check_out_date, $listing_id, $guests);
    $is_available = $check_availability['success'];
    $check_message = $check_availability['message'];

    if ($is_available) {


      $reservation_meta['no_of_days'] = $prices_array['days_count'] = $booking_type == 'per_day_date' ? $prices_array['days_count'] : $prices_array['days_count'];
      $reservation_meta['additional_guests'] = $prices_array['additional_guests'];

      $upfront_payment = $prices_array['upfront_payment'];
      $balance = $prices_array['balance'];
      $total_price = $prices_array['total_price'];
      $cleaning_fee = $prices_array['cleaning_fee'];
      $city_fee = $prices_array['city_fee'];
      $services_fee = $prices_array['services_fee'];
      $days_count = $prices_array['days_count'];
      $period_days = $prices_array['period_days'];
      $taxes = $prices_array['taxes'];
      $taxes_percent = $prices_array['taxes_percent'];
      $security_deposit = $prices_array['security_deposit'];
      $additional_guests = $prices_array['additional_guests'];
      $additional_guests_price = $prices_array['additional_guests_price'];
      $additional_guests_total_price = $prices_array['additional_guests_total_price'];
      $booking_has_weekend = $prices_array['booking_has_weekend'];
      $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];

      $reservation_meta['check_in_date'] = $check_in_date;
      $reservation_meta['check_out_date'] = $check_out_date;
      $reservation_meta['guests'] = $guests;
      $reservation_meta['listing_id'] = $listing_id;
      $reservation_meta['upfront'] = $upfront_payment;
      $reservation_meta['balance'] = $balance;
      $reservation_meta['total'] = $total_price;

      $reservation_meta['cleaning_fee'] = $cleaning_fee;
      $reservation_meta['city_fee'] = $city_fee;
      $reservation_meta['services_fee'] = $services_fee;
      $reservation_meta['period_days'] = $period_days;
      $reservation_meta['taxes'] = $taxes;
      $reservation_meta['taxes_percent'] = $taxes_percent;
      $reservation_meta['security_deposit'] = $security_deposit;
      $reservation_meta['additional_guests_price'] = $additional_guests_price;
      $reservation_meta['additional_guests_total_price'] = $additional_guests_total_price;
      $reservation_meta['booking_has_weekend'] = $booking_has_weekend;
      $reservation_meta['booking_has_custom_pricing'] = $booking_has_custom_pricing;

      $reservation = array(
        'post_title'    => $title,
        'post_status'   => 'publish',
        'post_type'     => 'homey_reservation',
        'post_author'   => $userID
      );
      $reservation_id =  wp_insert_post($reservation);

      $reservation_update = array(
        'ID'         => $reservation_id,
        'post_title' => $title . ' ' . $reservation_id
      );
      wp_update_post($reservation_update);

      update_post_meta($reservation_id, 'reservation_listing_id', $listing_id);
      update_post_meta($reservation_id, 'listing_owner', $listing_owner_id);
      update_post_meta($reservation_id, 'listing_renter', $userID);
      update_post_meta($reservation_id, 'reservation_checkin_date', $check_in_date);
      update_post_meta($reservation_id, 'reservation_checkout_date', $check_out_date);
      update_post_meta($reservation_id, 'reservation_guests', $guests);
      update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
      update_post_meta($reservation_id, 'reservation_status', 'under_review');
      update_post_meta($reservation_id, 'is_hourly', 'no');
      update_post_meta($reservation_id, 'extra_options', $extra_options);

      update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
      update_post_meta($reservation_id, 'reservation_balance', $balance);
      update_post_meta($reservation_id, 'reservation_total', $total_price);

      $pending_dates_array = homey_get_booking_pending_days($listing_id);
      update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

      echo json_encode(
        array(
          'success' => true,
          'message' => $local['request_sent']
        )
      );

      $message_link = homey_thread_link_after_reservation($reservation_id);
      $email_args = array(
        'reservation_detail_url' => reservation_detail_link($reservation_id),
        'guest_message' => $guest_message,
        'message_link' => $message_link
      );

      if (!empty(trim($guest_message))) {
        do_action('homey_create_messages_thread', $guest_message, $reservation_id);
      }

      homey_email_composer($owner_email, 'new_reservation', $email_args);

      if (isset($current_user->user_email)) {
        $reservation_page = homey_get_template_link_dash('template/dashboard-reservations2.php');
        $reservation_detail_link = add_query_arg('reservation_detail', $reservation_id, $reservation_page);
        $email_args = array('reservation_detail_url' => $reservation_detail_link);

        homey_email_composer($current_user->user_email, 'new_reservation_sent', $email_args);
      }

      wp_die();
    } else { // end $check_availability
      echo json_encode(
        array(
          'success' => false,
          'message' => $check_message
        )
      );
      wp_die();
    }
  }
}

add_action('wp_ajax_homey_add_blackout_dates', 'homey_add_blackout_dates'); */



// Add a custom user role
function create_guide_user_role()
{
  $role_slug = 'homey_guide'; // Replace with your desired role slug
  $role_name = 'Guide'; // Replace with your desired role name

  // Add the role
  add_role(
    $role_slug,
    $role_name,
    array(
      'read' => true, // Example capability
      'edit_posts' => true, // Example capability
      // Add more capabilities as needed
    )
  );
}
add_action('init', 'create_guide_user_role');

if (!function_exists('homey_is_guide')) {
  function homey_is_guide($user_id = null)
  {
    global $current_user;
    $current_user = wp_get_current_user();

    if (!empty($user_id)) {
      $current_user = get_userdata($user_id);
    }

    if (in_array('homey_guide', (array) $current_user->roles) || in_array('subscriber', (array) $current_user->roles)) {
      return true;
    }
    return false;
  }
}

// Schedule cron job to un feature listing
function unfeature_listings_function()
{
  $args = array(
    'post_type' => 'listing',
    'meta_query' => array(
      array(
        'key' => 'homey_featured',
        'value' => 1,
      ),
    ),
  );
  $featured_listings = new WP_Query($args);

  if ($featured_listings->have_posts()) {
    while ($featured_listings->have_posts()) {
      $featured_listings->the_post();
      $listing_id = get_the_ID();
      $featured_time = get_post_meta($listing_id, 'homey_featured_time', true);
      $expiry_time = get_post_meta($listing_id, 'homey_featured_expiry', true);

      if (!empty($expiry_time) && current_time('timestamp') > $expiry_time) {
        delete_post_meta($listing_id, 'homey_featured');
        delete_post_meta($listing_id, 'homey_featured_time');
        delete_post_meta($listing_id, 'homey_featured_expiry');
      }
    }
    wp_reset_postdata();
  }
}

add_action('init', 'unfeature_listings_schedule_cron_theme_activation');

function unfeature_listings_schedule_cron_theme_activation()
{
  if (!wp_next_scheduled('unfeature_listings_event')) {
    wp_schedule_event(time(), 'five_minutes', 'unfeature_listings_event');
  }
}

add_action('unfeature_listings_event', 'unfeature_listings_function');


add_filter('cron_schedules', 'custom_cron_schedule');

function custom_cron_schedule($schedules)
{
  $schedules['five_minutes'] = array(
    'interval' => 300,
    'display' => __('Every Five Minutes'),
  );
  return $schedules;
}

function get_featured_listings_count()
{
  $args = array(
    'post_type' => 'listing',
    'meta_query' => array(
      array(
        'key' => 'homey_featured',
        'value' => 1,
      ),
    ),
    'posts_per_page' => -1,
  );
  $featured_listings = new WP_Query($args);
  $count = $featured_listings->found_posts;
  wp_reset_postdata();
  return $count;
}

// Function to get the expiration time of the earliest expiring featured listing
function get_earliest_expiring_time()
{
  $args = array(
    'post_type' => 'listing',
    'meta_query' => array(
      array(
        'key' => 'homey_featured',
        'value' => 1,
      ),
    ),
    'orderby' => 'meta_value_num',
    'order' => 'ASC',
    'meta_key' => 'homey_featured_expiry',
    'posts_per_page' => 1,
  );
  $featured_listings = new WP_Query($args);

  $earliest_expiring_time = 0;
  if ($featured_listings->have_posts()) {
    $featured_listings->the_post();
    $expiry_time = get_post_meta(get_the_ID(), 'homey_featured_expiry', true);
    $earliest_expiring_time = $expiry_time;
  }

  wp_reset_postdata();

  return $earliest_expiring_time;
}

// Function to calculate the time difference between the current time and the earliest expiring time
function calculate_time_difference()
{
  $current_time = current_time('timestamp');
  $earliest_expiring_time = get_earliest_expiring_time();
  return $earliest_expiring_time - $current_time;
}

function make_listing_featured_event_callback($listing_id)
{
  update_post_meta($listing_id, 'homey_featured', 1);
  $current_time = current_time('timestamp');
  update_post_meta($listing_id, 'homey_featured_time', $current_time);

  // Calculate expiry time (5 days from now)
  $expiry_time = strtotime('+5 days', $current_time);
  update_post_meta($listing_id, 'homey_featured_expiry', $expiry_time);

  delete_post_meta($listing_id, 'homey_queued_featured');
  delete_post_meta($listing_id, 'homey_queued_featured_time');
  delete_post_meta($listing_id, 'homey_featured_queued_expiry');
}

// Schedule the event
add_action('make_listing_featured_event', 'make_listing_featured_event_callback', 10, 1);

function guest_booking_confirmed($listing_id, $current_user_id)
{
  $reservation_args = array(
    'post_type' => 'homey_reservation',

    'meta_query' => array(
      'relation' => 'AND',
      array(
        'key' => 'reservation_listing_id',
        'value' => $listing_id,
        'compare' => '=',
      ),
      array(
        'key' => 'listing_renter',
        'value' => $current_user_id,
        'compare' => '=',
      ),
      array(
        'key' => 'reservation_status',
        'value' => 'booked',
        'compare' => '=',
      ),
    ),
  );

  $reservation_query = new WP_Query($reservation_args);

  return $reservation_query;
}

function create_coupon_cpt()
{
  $labels = array(
    'name' => __('Coupons', 'homey'),
    'singular_name' => __('Coupon', 'homey'),
  );

  $args = array(
    'label' => __('Coupon', 'homey'),
    'labels' => $labels,
    'public' => true,
    'has_archive' => false,
    'supports' => array('title'),
    'rewrite' => array('slug' => 'coupons'),
  );

  register_post_type('host_coupon', $args);
}

add_action('init', 'create_coupon_cpt');

/* --------------------------------------------------------------------------
 * Coupon delete ajax
 * --------------------------------------------------------------------------- */
add_action('wp_ajax_nopriv_homey_delete_coupon', 'homey_delete_coupon');
add_action('wp_ajax_homey_delete_coupon', 'homey_delete_coupon');

if (!function_exists('homey_delete_coupon')) {
  function homey_delete_coupon()
  {
    if (!isset($_POST['coupon_id'])) {
      $ajax_response = array('success' => false, 'reason' => esc_html__('No coupon ID found', 'homey'));
      echo json_encode($ajax_response);
      die;
    }

    $coupon_id = intval($_POST['coupon_id']);
    $coupon = get_post($coupon_id);
    if (!$coupon || $coupon->post_type !== 'host_coupon') {
      $ajax_response = array('success' => false, 'reason' => esc_html__('Invalid coupon ID', 'homey'));
      echo json_encode($ajax_response);
      die;
    }

    $post_author = $coupon->post_author;
    $userID = get_current_user_id();

    if (($post_author == $userID) || homey_is_admin()) {
      wp_delete_post($coupon_id);
      $ajax_response = array('success' => true, 'reason' => esc_html__('Coupon deleted successfully!', 'homey'));
      echo json_encode($ajax_response);
      die;
    } else {
      $ajax_response = array('success' => false, 'reason' => esc_html__('Permission denied', 'homey'));
      echo json_encode($ajax_response);
      die;
    }
  }
}

/* --------------------------------------------------------------------------
 * Coupon Email Send ajax
 * --------------------------------------------------------------------------- */
add_action('wp_ajax_nopriv_homey_send_coupon_mail', 'homey_send_coupon_mail');
add_action('wp_ajax_homey_send_coupon_mail', 'homey_send_coupon_mail');

if (!function_exists('homey_send_coupon_mail')) {
  function homey_send_coupon_mail()
  {
    if (!isset($_POST['coupon_id'])) {
      $ajax_response = array('success' => false, 'reason' => esc_html__('No coupon ID found', 'homey'));
      echo json_encode($ajax_response);
      die;
    }

    $coupon_id = intval($_POST['coupon_id']);
    $coupon = get_post($coupon_id);
    if (!$coupon || $coupon->post_type !== 'host_coupon') {
      $ajax_response = array('success' => false, 'reason' => esc_html__('Invalid coupon ID', 'homey'));
      echo json_encode($ajax_response);
      die;
    }

    $post_author = $coupon->post_author;
    $userID = get_current_user_id();
    $host_name = get_user_meta($post_author, 'display_name_public', true);
    if (($post_author == $userID) || homey_is_admin()) {
      $guests = get_field('coupon_guests', $coupon_id);
      if ($guests) {
        foreach ($guests as $guest) {
          $user_id = $guest['ID'];
          $notification_settings_guest = get_user_meta($user_id, 'notification_settings', true);

          if (isset($notification_settings_guest['email']) && $notification_settings_guest['email']) {
            $user_email = $guest['user_email'];
            $subject = 'Your Coupon from ' . get_bloginfo('name');
            $coupon_discount = get_field('coupon_discount', $coupon_id);
            $logo_url = wp_get_attachment_url(7179);
            $image_url = wp_get_attachment_url(7182);
            $gradient_color = "linear-gradient(to right, #90EE90, #0072ff)";
            $button_url = home_url('/profile');

            $message = '
              <div style="font-family: \'Oswald\', sans-serif;text-align: center; padding: 20px; margin: 0 auto; max-width: 400px;">
                  <!-- Image -->
                  <div style="margin-bottom: 20px;">
                      <img src="' . esc_url($logo_url) . '" width="100" height="100" alt="Coupon Image" style="max-width: 100%; height: auto;">
                  </div>
                  
                  <!-- Bold text in sky blue color -->
                  <p style="font-size: 14px; color: #0072ff; font-weight: 600; margin-top: 20px;">
                      Say Whaaaaa! You just received a discount
                  </p>

                   <div style="margin-top: 10px;">
                      <img src="' . esc_url($image_url) . '" alt="Coupon Image" style="max-width: 100%; height: auto;">
                  </div>
                  
                  <div style="display: inline-block;padding: 20px;background: white;box-shadow: 2px 2px 3px rgba(0, 114, 255, 0.5);text-align: center;margin-top: 15px;">
                  <p style="font-size: 18px;font-weight:500;color:#222">
                      ENTER CODE
                  </p>
                  
                  <!-- Coupon code -->
                  <p style="font-size: 18px; font-weight: bold;color:#222;margin-top: 5px;">
                      ' . get_the_title($coupon_id) . '
                  </p>
                  
                  <!-- Additional text -->
                  <p style="font-size: 18px;font-weight:500;color:#222;margin-top: 5px;">
                      AT CHECKOUT
                  </p>
                  
                  <!-- Coupon discount with gradient color -->
                  <p style="font-size: 34px; font-weight: bold; background: ' . $gradient_color . '; color: transparent; background-clip: text; -webkit-background-clip: text; text-fill-color: transparent; -webkit-text-fill-color: transparent; padding: 10px; margin: 10px 0;">
                    <strong>' . $coupon_discount . '% OFF</strong>
                  </p>
                  
                  <!-- Final message -->
                  <p style="font-size: 14px; color: #0072ff; font-weight: 600; margin-top: 20px;">
                       Adventure is Closer Than You Think
                  </p>
                  </div>

                  <!-- Button -->
                  <div style="margin-top: 15px;background-color: #f5f5f5;padding: 20px;">
                    <a href="' . $button_url . '" style="display: inline-block;padding: 5px 30px;font-size: 14px;color: white;background-color: #000080;text-decoration: none;font-weight: 600;">
                      View Profile
                    </a>
                  </div>
                  <p style="font-size: 14px; color: #0072ff; font-weight: 600; margin-top: 10px;">
                       have FUN and save SOME!
                  </p>
              </div>';

            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail($user_email, $subject, $message, $headers);
          }
        }

        $ajax_response = array('success' => true, 'reason' => esc_html__('Email sent successfully!', 'homey'));
        echo json_encode($ajax_response);
        die;
      } else {
        $ajax_response = array('success' => false, 'reason' => esc_html__('No guest found for this coupon', 'homey'));
        echo json_encode($ajax_response);
        die;
      }
    } else {
      $ajax_response = array('success' => false, 'reason' => esc_html__('Permission denied', 'homey'));
      echo json_encode($ajax_response);
      die;
    }
  }
}

/* --------------------------------------------------------------------------
 * Coupon Message Send ajax
 * --------------------------------------------------------------------------- */
add_action('wp_ajax_nopriv_homey_send_coupon_message', 'homey_send_coupon_message');
add_action('wp_ajax_homey_send_coupon_message', 'homey_send_coupon_message');

if (!function_exists('homey_send_coupon_message')) {
  function homey_send_coupon_message()
  {
    if (!isset($_POST['coupon_id'])) {
      $ajax_response = array('success' => false, 'reason' => esc_html__('No coupon ID found', 'homey'));
      echo json_encode($ajax_response);
      die;
    }

    $coupon_id = intval($_POST['coupon_id']);
    $coupon = get_post($coupon_id);
    if (!$coupon || $coupon->post_type !== 'host_coupon') {
      $ajax_response = array('success' => false, 'reason' => esc_html__('Invalid coupon ID', 'homey'));
      echo json_encode($ajax_response);
      die;
    }

    $post_author = $coupon->post_author;
    $sender_id = get_current_user_id();
    $coupon_discount = get_field('coupon_discount', $coupon_id);

    if (($post_author == $sender_id) || homey_is_admin()) {
      $guests = get_field('coupon_guests', $coupon_id);

      if ($guests) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'homey_messages';

        // Build the message
        $message = "Say Whaaaaa! You just received a discount. ";
        $message .= "Enter code\n";
        $message .= get_the_title($coupon_id) . "\n";
        $message .= "at checkout and get\n";
        $message .= $coupon_discount . '% OFF. ' . "\n\n";
        $message .= "Adventure is Closer Than You Think.";

        $sender_role = homey_get_user_role($sender_id);
        $success_count = 0;

        foreach ($guests as $guest) {
          $receiver_id = $guest['ID'];
          $receiver_role = homey_get_user_role($receiver_id);

          $inserted = $wpdb->insert(
            $table_name,
            array(
              'sender_id' => $sender_id,
              'receiver_id' => $receiver_id,
              'sender_role' => $sender_role,
              'receiver_role' => $receiver_role,
              'message' => $message,
              'created_at' => current_time('mysql'),
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s')
          );

          if ($inserted) {
            $success_count++;
          }
        }

        if ($success_count > 0) {
          $ajax_response = array(
            'success' => true,
            'reason' => sprintf(esc_html__('Message sent to %d guests successfully!', 'homey'), $success_count)
          );
        } else {
          $ajax_response = array(
            'success' => false,
            'reason' => esc_html__('Failed to send messages to guests', 'homey')
          );
        }

        echo json_encode($ajax_response);
        die;
      } else {
        $ajax_response = array('success' => false, 'reason' => esc_html__('No guest found for this coupon', 'homey'));
        echo json_encode($ajax_response);
        die;
      }
    } else {
      $ajax_response = array('success' => false, 'reason' => esc_html__('Permission denied', 'homey'));
      echo json_encode($ajax_response);
      die;
    }
  }
}

// Create a custom table for reported profiles if it doesn't exist
function reported_profiles_table()
{
  global $wpdb;
  $table_name = $wpdb->prefix . 'reported_profiles';

  // Check if the table already exists
  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          reported_user_id bigint(20) NOT NULL,
          reported_by_user_id bigint(20) NOT NULL,
          report_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
          PRIMARY KEY (id)
      ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
}

add_action('admin_init', 'reported_profiles_table');

// Create an admin menu for reported profiles
function create_reported_profiles_menu()
{
  add_menu_page(
    'Reported Profiles',
    'Reported Profiles',
    'manage_options',
    'reported-profiles',
    'display_reported_profiles_page',
    'dashicons-warning',
    20
  );
}
add_action('admin_menu', 'create_reported_profiles_menu');

// Display reported profiles page
function display_reported_profiles_page()
{
  global $wpdb;
  $table_name = $wpdb->prefix . 'reported_profiles';
  $results = $wpdb->get_results("SELECT * FROM $table_name");

  echo '<div class="wrap">';
  echo '<h1>Reported Profiles</h1>';
  echo '<table class="widefat fixed" cellspacing="0">';
  echo '<thead><tr><th>ID</th><th>Reported User ID</th><th>Reported By User ID</th><th>Report Date</th></tr></thead>';
  echo '<tbody>';
  foreach ($results as $row) {
    echo '<tr>';
    echo '<td>' . esc_html($row->id) . '</td>';
    echo '<td>' . esc_html($row->reported_user_id) . '</td>';
    echo '<td>' . esc_html($row->reported_by_user_id) . '</td>';
    echo '<td>' . esc_html($row->report_date) . '</td>';
    echo '</tr>';
  }
  echo '</tbody>';
  echo '</table>';
  echo '</div>';
}

// AJAX handler to report profile
function report_profile_ajax_handler()
{
  if (!is_user_logged_in()) {
    $response = array('success' => false, 'message' => 'You need to login to report this profile.');
    echo json_encode($response);
    wp_die();
  }

  $reported_user_id = intval($_POST['report_profile_id']);
  $reported_by_user_id = get_current_user_id();

  global $wpdb;
  $table_name = $wpdb->prefix . 'reported_profiles';

  // Check if the user has already reported this profile
  $report_exists = $wpdb->get_var(
    $wpdb->prepare(
      "SELECT COUNT(*) FROM $table_name WHERE reported_user_id = %d AND reported_by_user_id = %d",
      $reported_user_id,
      $reported_by_user_id
    )
  );

  if ($report_exists) {
    $response = array('success' => false, 'message' => 'You have already reported this profile.');
    echo json_encode($response);
    wp_die();
  }

  $report_date = current_time('mysql');

  $wpdb->insert(
    $table_name,
    array(
      'reported_user_id' => $reported_user_id,
      'reported_by_user_id' => $reported_by_user_id,
      'report_date' => $report_date
    )
  );
  $response = array('success' => true, 'message' => 'Profile reported successfully!');
  echo json_encode($response);
  wp_die();
}

add_action('wp_ajax_nopriv_report_profile', 'report_profile_ajax_handler');
add_action('wp_ajax_report_profile', 'report_profile_ajax_handler');

// Add Stripe Card
add_action('wp_ajax_save_stripe_card', 'save_stripe_card');
function save_stripe_card()
{
  require_once(HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php');

  // Collect POST data
  $token = $_POST['token'];
  $user_id = $_POST['user_id'];

  \Stripe\Stripe::setApiKey(homey_option('stripe_secret_key'));

  try {
    // Retrieve or create customer
    $customer_id = get_user_meta($user_id, 'stripe_customer_id', true);
    if (!$customer_id) {
      $customer = \Stripe\Customer::create([
        'description' => 'Customer for user ID ' . $user_id,
        'email' => get_userdata($user_id)->user_email,
      ]);
      $customer_id = $customer->id;
      update_user_meta($user_id, 'stripe_customer_id', $customer_id);
    } else {
      $customer = \Stripe\Customer::retrieve($customer_id);
    }

    // Create payment method using the token
    $payment_method = \Stripe\PaymentMethod::create([
      'type' => 'card',
      'card' => [
        'token' => $token,
      ],
    ]);

    // Check for duplicate card before attaching and saving
    $saved_cards = get_user_meta($user_id, 'saved_stripe_cards', true);
    $saved_cards = $saved_cards ? json_decode($saved_cards, true) : [];

    foreach ($saved_cards as $card) {
      if ($card['last4'] == $payment_method->card->last4 && $card['brand'] == $payment_method->card->brand) {
        wp_send_json_error('This card is already saved.');
        return;
      }
    }

    // If no duplicate, attach the card to customer
    $payment_method->attach(['customer' => $customer_id]);

    // Save card details locally
    $saved_cards[] = [
      'id' => $payment_method->id,
      'last4' => $payment_method->card->last4,
      'brand' => $payment_method->card->brand,
      'exp_month' => $payment_method->card->exp_month,
      'exp_year' => $payment_method->card->exp_year
    ];

    update_user_meta($user_id, 'saved_stripe_cards', json_encode($saved_cards));
    verify_user_status($user_id);
    wp_send_json_success();
  } catch (\Stripe\Exception\ApiErrorException $e) {
    // Handle Stripe API error
    wp_send_json_error('Stripe API error: ' . $e->getMessage());
  } catch (Exception $e) {
    // Handle general error
    wp_send_json_error('An error occurred: ' . $e->getMessage());
  }
}

// Delete saved cards
add_action('wp_ajax_delete_stripe_card', 'delete_stripe_card');
function delete_stripe_card()
{
  require_once(HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php');
  \Stripe\Stripe::setApiKey(homey_option('stripe_secret_key'));

  $card_id = $_POST['card_id'];
  $user_id = $_POST['user_id'];

  $saved_cards = get_user_meta($user_id, 'saved_stripe_cards', true);
  $saved_cards = $saved_cards ? json_decode($saved_cards, true) : [];

  try {
    // Detach the payment method from the customer
    $payment_method = \Stripe\PaymentMethod::retrieve($card_id);
    $payment_method->detach();

    // Remove the card from the saved cards array
    foreach ($saved_cards as $key => $card) {
      if ($card['id'] == $card_id) {
        unset($saved_cards[$key]);
      }
    }

    // Update user meta with the remaining cards
    update_user_meta($user_id, 'saved_stripe_cards', json_encode(array_values($saved_cards)));

    wp_send_json_success();
  } catch (\Stripe\Exception\ApiErrorException $e) {
    wp_send_json_error('Stripe API error: ' . $e->getMessage());
  } catch (Exception $e) {
    wp_send_json_error('An error occurred: ' . $e->getMessage());
  }
}

//Stripe Connect Account
add_action('wp_ajax_create_stripe_connect_account', 'ajax_create_stripe_connect_account');

function ajax_create_stripe_connect_account()
{
  error_reporting(E_ALL);
  ini_set("display_errors", 1);
  if (!is_user_logged_in() || !current_user_can('homey_host')) {
    wp_send_json_error('Unauthorized');
  }

  $user_id = get_current_user_id();
  $result = create_or_retrieve_stripe_connect_account($user_id);

  if (isset($result['error'])) {
    wp_send_json_error($result['error']);
  }

  $account = $result['account'];
  $linkResult = create_stripe_account_link($account->id);

  if (isset($linkResult['error'])) {
    wp_send_json_error($linkResult['error']);
  }

  wp_send_json_success(['url' => $linkResult['url']]);
}


function create_or_retrieve_stripe_connect_account($user_id)
{
  require_once(HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php');
  \Stripe\Stripe::setApiKey(homey_option('stripe_secret_key'));

  $stripe_account_id = get_user_meta($user_id, 'stripe_account_id', true);

  if ($stripe_account_id) {
    // Retrieve existing account
    try {
      $account = \Stripe\Account::retrieve($stripe_account_id);
    } catch (\Stripe\Exception\ApiErrorException $e) {
      return ['error' => $e->getMessage()];
    }
  } else {
    // Create new account
    try {
      $user = get_userdata($user_id);
      $account = \Stripe\Account::create([
        'type' => 'express',
        'email' => $user->user_email,
        'capabilities' => [
          'transfers' => ['requested' => true],
        ],
      ]);
      update_user_meta($user_id, 'stripe_account_id', $account->id);
      verify_user_status($user_id);
    } catch (\Stripe\Exception\ApiErrorException $e) {
      return ['error' => $e->getMessage()];
    }
  }

  return ['account' => $account];
}

function create_stripe_account_link($account_id)
{
  require_once(HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php');
  \Stripe\Stripe::setApiKey(homey_option('stripe_secret_key'));
  try {
    $accountLink = \Stripe\AccountLink::create([
      'account' => $account_id,
      'refresh_url' => home_url('/profile/?dpage=payment-method'),
      'return_url' => home_url('/profile/?dpage=payment-method'),
      'type' => 'account_onboarding',
    ]);
    return ['url' => $accountLink->url];
  } catch (\Stripe\Exception\ApiErrorException $e) {
    return ['error' => $e->getMessage()];
  }
}

function check_stripe_account_status($account_id)
{
  require_once(HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php');
  \Stripe\Stripe::setApiKey(homey_option('stripe_secret_key'));
  try {
    $account = \Stripe\Account::retrieve($account_id);
    return [
      'status' => $account->requirements->disabled_reason,
      'account' => $account
    ];
  } catch (\Stripe\Exception\ApiErrorException $e) {
    return ['error' => $e->getMessage()];
  }
}

add_action('wp_ajax_generate_stripe_onboarding_link', 'ajax_generate_stripe_onboarding_link');

function ajax_generate_stripe_onboarding_link()
{
  //error_reporting(E_ALL);
  //ini_set("display_errors", 1);
  if (!is_user_logged_in() || !current_user_can('homey_host')) {
    wp_send_json_error('Unauthorized');
  }

  $user_id = get_current_user_id();
  $stripe_account_id = get_user_meta($user_id, 'stripe_account_id', true);

  if (!$stripe_account_id) {
    wp_send_json_error('Stripe account ID not found.');
  }

  $result = check_stripe_account_status($stripe_account_id);

  if (isset($result['error'])) {
    wp_send_json_error($result['error']);
  }

  $account_status = $result['status'];

  if ($account_status) {
    // Account is incomplete or requires more information
    $linkResult = create_stripe_account_link($stripe_account_id);

    if (isset($linkResult['error'])) {
      wp_send_json_error($linkResult['error']);
    }

    wp_send_json_success(['url' => $linkResult['url']]);
  } else {
    wp_send_json_error('Account is already complete.');
  }
}

if (!class_exists('Twilio\Rest\Client')) {
  require get_stylesheet_directory() . '/lib/twilio-php/src/Twilio/autoload.php';
}

if (!function_exists('homey_send_sms')) {
  function homey_send_sms($to, $message)
  {
    $sid = 'AC69ee577929fec5f7b755246515c61dea';
    $auth_token = '818f0f670a89a2f91b78722b036e786c';
    $twilio_number = '+19362562537';

    $client = new Twilio\Rest\Client($sid, $auth_token);

    $client->messages->create(
      $to,
      array(
        'from' => $twilio_number,
        'body' => $message
      )
    );
  }
}

function format_check_in_date($date_string)
{
  try {
    $date = new DateTime($date_string);
    return $date->format('D, M j, Y');
  } catch (Exception $e) {
    return $date_string;
  }
}

add_action('wp_ajax_save_notification_settings', 'save_notification_settings');

function save_notification_settings()
{

  if (is_user_logged_in()) {
    $user_id = get_current_user_id();

    $email = isset($_POST['email']) ? intval($_POST['email']) : 0;
    $sms = isset($_POST['sms']) ? intval($_POST['sms']) : 0;

    // Save settings in user meta
    $notification_settings = array(
      'email' => $email,
      'sms' => $sms,
    );

    update_user_meta($user_id, 'notification_settings', $notification_settings);

    wp_send_json_success('Settings saved successfully.');
  } else {
    wp_send_json_error('Settings not saved.');
  }
}

add_action('user_register', 'set_default_notification_settings_for_new_user');
function set_default_notification_settings_for_new_user($user_id)
{
  $default_notification_settings = array(
    'email' => 1,
    'sms' => 1,
  );
  update_user_meta($user_id, 'notification_settings', $default_notification_settings);
}

add_filter('display_post_states', 'custom_add_post_states', 10, 2);

function custom_add_post_states($post_states, $post)
{
  if ($post->post_type == 'listing' && $post->post_status == 'declined') {
    $post_states[] = '<span class="post-state">Disapproved</span>';
  }
  return $post_states;
}

function redirect_declined_listing($template)
{
  if (is_singular('listing')) {
    global $post;

    if ($post->post_status === 'declined') {

      if (!is_user_logged_in()) {
        wp_redirect(home_url());
        exit;
      }

      $current_user = wp_get_current_user();

      if (!in_array('administrator', $current_user->roles) && $current_user->ID != $post->post_author) {
        wp_redirect(home_url());
        exit;
      }
    }
  }
  return $template;
}
add_action('template_redirect', 'redirect_declined_listing');

//change reservations status from under review to cancelled
if (!wp_next_scheduled('check_under_review_reservations')) {
  wp_schedule_event(time(), 'five_minutes', 'check_under_review_reservations');
}

add_action('check_under_review_reservations', 'update_old_under_review_reservations');
function update_old_under_review_reservations()
{
  $current_time = current_time('timestamp', 0);
  $time_48_hours_ago = $current_time - (48 * 60 * 60);

  $args = array(
    'post_type' => 'homey_reservation',
    'meta_query' => array(
      array(
        'key' => 'reservation_status',
        'value' => 'under_review',
        'compare' => '='
      ),
      array(
        'key' => 'reservation_confirm_date_time',
        'value' => date('Y-m-d G:i:s', $time_48_hours_ago),
        'compare' => '<=',
        'type' => 'DATETIME'
      )
    ),
    'posts_per_page' => -1, // Get all matching posts
  );

  $reservations = new WP_Query($args);
  if ($reservations->have_posts()) {
    while ($reservations->have_posts()) {
      $reservations->the_post();
      $reservation_id = get_the_ID();
      $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);

      $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
      $strat_hour = date(homey_time_format(), strtotime($reservation_meta['start_hour']));
      $end_hour = date(homey_time_format(), strtotime($reservation_meta['end_hour']));
      $check_in_date = homey_format_date_simple($reservation_meta['check_in_date']);

      $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
      $username_host = get_the_author_meta('user_login', $listing_owner);
      $display_name_public_host = get_the_author_meta('display_name_public', $listing_owner);
      $host_name = empty($display_name_public_host) ? $username_host : $display_name_public_host;

      $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);
      $username = get_the_author_meta('user_login', $listing_renter);
      $display_name_public = get_the_author_meta('display_name_public', $listing_renter);
      $guest_name = empty($display_name_public) ? $username : $display_name_public;

      $notification_settings_host = get_user_meta($listing_owner, 'notification_settings', true);
      $notification_settings_guest = get_user_meta($listing_renter, 'notification_settings', true);

      $host_phone_number = get_the_author_meta('homey_phone_number', $listing_owner);
      $guest_phone_number = get_the_author_meta('homey_phone_number', $listing_renter);

      update_post_meta($reservation_id, 'reservation_status', 'cancelled');

      //Remove Pending Hours
      $pending_dates_array = homey_remove_booking_pending_hours($listing_id, $reservation_id);
      update_post_meta($listing_id, 'reservation_pending_hours', $pending_dates_array);

      //Remove Booked Hours
      $booked_dates_array = homey_remove_booked_hours($listing_id, $reservation_id);
      update_post_meta($listing_id, 'reservation_booked_hours', $booked_dates_array);

      //Remove Pending Dates
      $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id);
      update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

      //Remove Booked Dates
      $booked_dates_array = homey_remove_booking_booked_days($listing_id, $reservation_id);
      update_post_meta($listing_id, 'reservation_dates', $booked_dates_array);

      // Send message on booking cancellation
      $message = "Backyard Lease update: Reservation - Reservation ID: " . $reservation_id . " has been cancelled.";
      send_booking_message($reservation_id, $listing_owner, $listing_renter, $message);

      // Save the booking confirmed notification to the Host
      $notification_title = 'Booking Request: #' . $reservation_id . ' has been cancelled.';
      $notification_content = 'Booking Request: #' . $reservation_id . ' has been cancelled.';
      $notification_link = home_url('/reservations/?reservation_detail=' . $reservation_id);
      save_booking_notification($listing_owner, $notification_title, $notification_content, $notification_link);

      // Save the booking confirmed notification to the Guest
      $notification_title = 'Reservation Request: #' . $reservation_id . ' has been cancelled.';
      $notification_content = 'Reservation Request: #' . $reservation_id . ' has been cancelled.';
      $notification_link = home_url('/reservations/?reservation_detail=' . $reservation_id);
      save_booking_notification($listing_renter, $notification_title, $notification_content, $notification_link);

      $reservation_url = home_url('/reservations/?reservation_detail=' . $reservation_id);

      if (isset($notification_settings_guest['sms']) && $notification_settings_guest['sms']) {
        if (!empty($guest_phone_number)) {
          $guest_message = 'BACKYARD LEASE: Bummer! Your booking #' . $reservation_id . ' has been canceled.' . "\n" . $reservation_url;
          homey_send_sms($guest_phone_number, $guest_message);
        }
      }

      if (isset($notification_settings_host['sms']) && $notification_settings_host['sms']) {
        if (!empty($host_phone_number)) {
          $host_message = 'BACKYARD LEASE: Bummer! Your booking #' . $reservation_id . ' has been canceled.' . "\n" . $reservation_url;
          homey_send_sms($host_phone_number, $host_message);
        }
      }

      if (isset($notification_settings_host['email']) && $notification_settings_host['email']) {
        $user_email_host = get_the_author_meta('user_email', $listing_owner);
        $subject = $host_name . ' your booking has been canceled';
        $logo_url = wp_get_attachment_url(7179);
        $hand_url = wp_get_attachment_url(7183);
        $button_url = home_url('/listing');

        $message = '
                <div style="font-family: \'Oswald\', sans-serif;text-align: left; padding: 20px; margin: 0 auto;">
                    <!-- Image -->
                    <div style="margin-bottom: 10px;">
                        <img src="' . esc_url($logo_url) . '" width="100" height="100" alt="Coupon Image" style="max-width: 100%; height: auto;">
                    </div>
                    
                    <p style="font-size: 24px; color: #3A3D32; font-weight: 800;">
                      ' . $host_name . ' Bummer! your booking has been canceled
                    </p>
          
                    <p style="font-size: 14px; color: #3A3D32; font-weight: normal; margin-top: 10px;">
                      Check out booking #' . $reservation_id . ' to see the updated reservation.
                    </p>
          
                    <div style="margin:30px">
                     <img src="' . esc_url($hand_url) . '" width="100" height="100" alt="Coupon Image" style="max-width: 100%; height: auto;position: absolute;margin-bottom: -100px;">
                    <p style="font-size: 14px; color: #222; font-weight: bold;">
                      CHECK IN:
                    </p>
                    <p style="font-size: 14px; color: #222; font-weight: normal;">
                      ' . $check_in_date . ' at ' . $strat_hour . '
                    </p>
                    <p style="font-size: 14px; color: #222; font-weight: bold;">
                      CHECK OUT:
                    </p>
                    <p style="font-size: 14px; color: #222; font-weight: normal;">
                      ' . $check_in_date . ' at ' . $end_hour . '
                    </p>
          
                    <div style="margin-top: 15px;padding-top: 20px;text-align:center">
                      <a href="' . $button_url . '" style="display: inline-block;padding: 8px 30px;font-size: 14px;color: #0072ff;background-color: transparent;text-decoration: none;font-weight: 600;border:1px solid #0072ff;border-radius:5px">
                        Search more adventures
                      </a>
                    </div>
                    <hr>
                    <p style="font-size: 14px; color: #3A3D32; font-weight: bold; margin-top: 30px;text-align:center;">
                      Need hosting help<br>
                      Reach out to us at <a href="mailto:info@backyardlease.com" style="color: #3A3D32; font-weight: bold;">info@backyardlease.com</a>
                    </p>
                    </div>
                </div>';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($user_email_host, $subject, $message, $headers);
      }

      if (isset($notification_settings_guest['email']) && $notification_settings_guest['email']) {
        $user_email_guest = get_the_author_meta('user_email', $listing_renter);
        $subject = $guest_name . ' your booking has been canceled';
        $logo_url = wp_get_attachment_url(7179);
        $hand_url = wp_get_attachment_url(7183);
        $button_url = home_url('/listing');

        $message = '
                <div style="font-family: \'Oswald\', sans-serif;text-align: left; padding: 20px; margin: 0 auto;">
                    <!-- Image -->
                    <div style="margin-bottom: 10px;">
                        <img src="' . esc_url($logo_url) . '" width="100" height="100" alt="Coupon Image" style="max-width: 100%; height: auto;">
                    </div>
                    
                    <p style="font-size: 24px; color: #3A3D32; font-weight: 800;">
                      ' . $guest_name . ' Bummer! your booking has been canceled
                    </p>
          
                    <p style="font-size: 14px; color: #3A3D32; font-weight: normal; margin-top: 10px;">
                      Check out booking #' . $reservation_id . ' to see the updated reservation.
                    </p>
          
                    <div style="margin:30px">
                     <img src="' . esc_url($hand_url) . '" width="100" height="100" alt="Coupon Image" style="max-width: 100%; height: auto;position: absolute;margin-bottom: -100px;">
                    <p style="font-size: 14px; color: #222; font-weight: bold;">
                      CHECK IN:
                    </p>
                    <p style="font-size: 14px; color: #222; font-weight: normal;">
                      ' . $check_in_date . ' at ' . $strat_hour . '
                    </p>
                    <p style="font-size: 14px; color: #222; font-weight: bold;">
                      CHECK OUT:
                    </p>
                    <p style="font-size: 14px; color: #222; font-weight: normal;">
                      ' . $check_in_date . ' at ' . $end_hour . '
                    </p>
          
                    <div style="margin-top: 15px;padding-top: 20px;text-align:center">
                      <a href="' . $button_url . '" style="display: inline-block;padding: 8px 30px;font-size: 14px;color: #0072ff;background-color: transparent;text-decoration: none;font-weight: 600;border:1px solid #0072ff;border-radius:5px">
                        Search more adventures
                      </a>
                    </div>
                    <hr>
                    <p style="font-size: 14px; color: #3A3D32; font-weight: bold; margin-top: 30px;text-align:center;">
                      Need hosting help<br>
                      Reach out to us at <a href="mailto:info@backyardlease.com" style="color: #3A3D32; font-weight: bold;">info@backyardlease.com</a>
                    </p>
                    </div>
                </div>';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($user_email_guest, $subject, $message, $headers);
      }
    }
  }
  wp_reset_postdata();
}

function verify_user_status($user_id)
{
  $user = get_userdata($user_id);

  // Check if the user has the 'guest' role
  if (in_array('homey_renter', $user->roles)) {

    $saved_cards = get_user_meta($user_id, 'saved_stripe_cards', true);
    $saved_cards = $saved_cards ? json_decode($saved_cards, true) : [];
    $email_verified = get_user_meta($user_id, 'email_verified', true);
    $doc_verified = get_user_meta($user_id, 'doc_verified', true);

    if ($saved_cards && $email_verified && $doc_verified == 1) {
      update_user_meta($user_id, 'guest_user_verify_status', 'verified');
    } else {
      update_user_meta($user_id, 'guest_user_verify_status', 'unverified');
    }
  }

  // Check if the user has the 'Host' role
  if (in_array('homey_host', $user->roles)) {

    $stripe_account = get_user_meta($user_id, 'stripe_account_id', true);
    $doc_verified = get_user_meta($user_id, 'doc_verified', true);
    $email_verified = get_user_meta($user_id, 'email_verified', true);

    if (!empty($stripe_account) && $email_verified && $doc_verified == 1) {
      update_user_meta($user_id, 'host_user_verify_status', 'verified');
    } else {
      update_user_meta($user_id, 'host_user_verify_status', 'unverified');
    }
  }
}

function is_guest_verified($userID)
{
  $email_verified = get_user_meta($userID, 'email_verified', true);
  $saved_cards = get_user_meta($userID, 'saved_stripe_cards', true);
  $author_picture_id = get_the_author_meta('homey_author_picture_id', $userID);
  $is_doc_verified = get_the_author_meta('doc_verified', $userID);

  if (
    $email_verified &&
    !empty($saved_cards) &&
    !empty($author_picture_id) &&
    !empty($is_doc_verified)
  ) {
    return true;
  }

  return false;
}

function is_host_verified($userID)
{
  $email_verified = get_user_meta($userID, 'email_verified', true);
  $stripe_account_id = get_user_meta($userID, 'stripe_account_id', true);
  $result = check_stripe_account_status($stripe_account_id);
  $account_status = $result['status'];
  $author_picture_id = get_the_author_meta('homey_author_picture_id', $userID);
  $is_doc_verified = get_the_author_meta('doc_verified', $userID);

  if (
    $email_verified &&
    !empty($stripe_account_id) &&
    empty($account_status) &&
    !empty($author_picture_id) &&
    !empty($is_doc_verified)
  ) {
    return true;
  }

  return false;
}

// Verify Current Password
add_action('wp_ajax_homey_verify_current_password', 'homey_verify_current_password');
function homey_verify_current_password()
{
  check_ajax_referer('homey_verify_pass_nonce', 'security');

  $user = wp_get_current_user();
  $current_password = sanitize_text_field($_POST['current_password']);

  if (wp_check_password($current_password, $user->data->user_pass, $user->ID)) {
    wp_send_json_success();
  } else {
    wp_send_json_error(array('message' => esc_html__('The current password is incorrect.', 'homey')));
  }
}

// Shortcode to display the referral Host name
// function display_referring_user()
// {
//   if (isset($_GET['ref'])) {
//     $ref = sanitize_text_field($_GET['ref']);

//     $user_id = explode('_', $ref)[0];

//     $username = get_the_author_meta('user_login', $user_id);
//     $profile_url = home_url('/author/' . $username);
//     $display_name_public = get_the_author_meta('display_name_public', $user_id);
//     $host_name = empty($display_name_public) ? $username : $display_name_public;

//     if ($host_name) {
//       return 'You are being referred by <a href="' . esc_html($profile_url) . '"><strong>' . esc_html($host_name) . '</strong></a>';
//     }
//   }
// }
// add_shortcode('ref_user', 'display_referring_user');

function populate_acf_select_fields($field)
{
  $field['choices'] = array();

  $start_hour = strtotime('1:00');
  $end_hour = strtotime('24:00');

  for ($halfhour = $start_hour; $halfhour <= $end_hour; $halfhour = $halfhour + 30 * 60) {
    $time = date('H:i', $halfhour);
    $field['choices'][$time] = date(homey_time_format(), $halfhour);
  }

  return $field;
}

add_filter('acf/load_field/name=first_half_start_hour', 'populate_acf_select_fields');
add_filter('acf/load_field/name=first_half_end_hour', 'populate_acf_select_fields');
add_filter('acf/load_field/name=second_half_start_hour', 'populate_acf_select_fields');
add_filter('acf/load_field/name=second_half_end_hour', 'populate_acf_select_fields');

// Check Dates Settings
add_action('wp_ajax_nopriv_check_dates_settings', 'check_dates_settings');
add_action('wp_ajax_check_dates_settings', 'check_dates_settings');
if (!function_exists('check_dates_settings')) {
  function check_dates_settings()
  {
    $allowed_html = array();

    $listing_id = intval($_POST['listing_id']);
    $check_in_date = wp_kses($_POST['check_in_date'], $allowed_html);
    $start_hour = wp_kses($_POST['start_hour'], $allowed_html);
    $end_hour = wp_kses($_POST['end_hour'], $allowed_html);
    $instances = get_post_meta($listing_id, '_listing_calendar_instances', true);
    $amenity_price_type = get_field('amenity_price_type', $listing_id);

    $check_in_hour = $check_in_date . ' ' . $start_hour;
    $check_out_hour = $check_in_date . ' ' . $end_hour;

    $check_in_hour_obj = new DateTime($check_in_hour);
    $check_in_hour_unix = $check_in_hour_obj->getTimestamp();

    $check_out_hour_obj = new DateTime($check_out_hour);
    $check_out_hour_unix = $check_out_hour_obj->getTimestamp();

    $response = array(
      'amenity' => 'available',
      'sleeping' => 'available',
      'gservice' => 'available',
    );

    if (!empty($instances) && is_array($instances)) {
      foreach ($instances as $instance) {
        if ($amenity_price_type == 'price_per_hour') {
          if (!empty($start_hour) || !empty($end_hour)) {
            foreach ($instance['selected_time_slots'] as $time_slot) {
              $slot_time = $time_slot['timeUnix'];
              if ($slot_time == $check_in_hour_unix || $slot_time == $check_out_hour_unix) {
                $response['amenity'] = $instance['amenity'];
                $response['sleeping'] = $instance['sleeping'];
                $response['gservice'] = $instance['gservice'];
                break 2;
              }
            }
          }
        } else {
          $selected_dates = explode(',', $instance['selected_dates']);
          if (in_array($check_in_date, $selected_dates)) {
            $response['amenity'] = $instance['amenity'];
            $response['sleeping'] = $instance['sleeping'];
            $response['gservice'] = $instance['gservice'];
            break;
          }
        }
      }
    }

    wp_send_json($response);
  }
}

add_action('wp_ajax_update_tax_status', 'update_tax_status');

function update_tax_status()
{
  if (isset($_POST['reservation_id']) && isset($_POST['tax_status'])) {
    $reservation_id = intval($_POST['reservation_id']);
    $tax_status = sanitize_text_field($_POST['tax_status']);

    update_post_meta($reservation_id, 'tax_status', $tax_status);

    wp_send_json_success();
  } else {
    wp_send_json_error();
  }
}


add_action('wp_ajax_filter_reservations_by_search', 'filter_reservations_by_search');
add_action('wp_ajax_nopriv_filter_reservations_by_search', 'filter_reservations_by_search');

function filter_reservations_by_search()
{
  if (isset($_POST['search_term']) || isset($_POST['start_date']) || isset($_POST['end_date']) || isset($_POST['filters'])) {
    $search_term = sanitize_text_field($_POST['search_term']);
    $start_date = sanitize_text_field($_POST['start_date']);
    $end_date = sanitize_text_field($_POST['end_date']);
    $filters = isset($_POST['filters']) ? $_POST['filters'] : array();
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $posts_per_page = 9;

    $args = array(
      'post_type' => 'homey_reservation',
      'posts_per_page' => $posts_per_page,
      'paged' => $paged,
      'meta_query' => array(
        array(
          'key' => 'host_earning_status',
          'value' => 'transfered',
          'compare' => '='
        )
      )
    );

    if (!empty($search_term)) {
      add_filter('posts_join', function ($join) {
        global $wpdb;
        $join .= " INNER JOIN {$wpdb->postmeta} AS listing_meta ON {$wpdb->posts}.ID = listing_meta.post_id";
        $join .= " INNER JOIN {$wpdb->posts} AS listings ON listing_meta.meta_value = listings.ID";
        return $join;
      });

      add_filter('posts_where', function ($where) use ($search_term) {
        global $wpdb;
        $where .= $wpdb->prepare(
          " AND listing_meta.meta_key = 'reservation_listing_id' AND listings.post_title LIKE %s",
          '%' . $wpdb->esc_like($search_term) . '%'
        );
        return $where;
      });
    }

    if (!empty($start_date) && !empty($end_date)) {
      $args['date_query'] = array(
        array(
          'after' => $start_date,
          'before' => $end_date,
          'inclusive' => true
        )
      );
    }

    $reservations_query = new WP_Query($args);
    $output = '';

    if ($reservations_query->have_posts()) {
      while ($reservations_query->have_posts()) {
        $reservations_query->the_post();
        $reservation_id = get_the_ID();
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);

        $matches_filters = false;

        if (in_array('amenity', $filters) && !empty($reservation_meta['hours_total_price'])) {
          $matches_filters = true;
        }
        if (in_array('sleeping_accommodation', $filters) && !empty($reservation_meta['total_accomodation_fee'])) {
          $matches_filters = true;
        }
        if (in_array('guided_service', $filters)) {
          $guided_service_fields = ['total_guest_hourly', 'total_guest_fixed', 'total_group_hourly', 'total_group_fixed', 'total_flat_hourly'];
          foreach ($guided_service_fields as $field) {
            if (!empty($reservation_meta[$field])) {
              $matches_filters = true;
              break;
            }
          }
        }
        if (in_array('additional_vehicles', $filters) && !empty($reservation_meta['additional_vehicles_fee'])) {
          $matches_filters = true;
        }

        // If no filters are selected, include all reservations
        if (empty($filters)) {
          $matches_filters = true;
        }

        // If the reservation matches any filter, add it to the output
        if ($matches_filters) {
          $reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
          $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
          $hours_total_price = $reservation_meta['hours_total_price'];
          $additional_guests_total_price = doubleval($reservation_meta['additional_guests_total_price']);
          $amenity_price = $hours_total_price + $additional_guests_total_price;
          $cleaning_fee = $reservation_meta['cleaning_fee'];
          $accomodation_fee = $reservation_meta['total_accomodation_fee'];
          $total_acc_fee = $accomodation_fee + $cleaning_fee;
          $additional_vehicles_fee = $reservation_meta['additional_vehicles_fee'];
          $occ_tax_amount = homey_formatted_price($reservation_meta['occ_tax_amount']);
          $total_state_tax = homey_formatted_price($reservation_meta['total_state_tax']);
          $host_transferred_amount = get_post_meta($reservation_id, 'host_transferred_amount', true);
          $tax_status = get_post_meta($reservation_id, 'tax_status', true);
          $tax_status = empty($tax_status) ? 'unpaid' : $tax_status;

          $total_guided_price = 0;

          $total_non_participants_price = floatval($reservation_meta['total_non_participants_price']);
          if (!empty($total_non_participants_price)) {
            $total_guided_price += $total_non_participants_price;
          }

          $total_guest_hourly = floatval($reservation_meta['total_guest_hourly']);
          if (!empty($total_guest_hourly)) {
            $total_guided_price += $total_guest_hourly;
          }

          $total_guest_fixed = floatval($reservation_meta['total_guest_fixed']);
          if (!empty($total_guest_fixed)) {
            $total_guided_price += $total_guest_fixed;
          }

          $total_group_hourly = floatval($reservation_meta['total_group_hourly']);
          if (!empty($total_group_hourly)) {
            $total_guided_price += $total_group_hourly;
          }

          $total_group_fixed = floatval($reservation_meta['total_group_fixed']);
          if (!empty($total_group_fixed)) {
            $total_guided_price += $total_group_fixed;
          }

          $total_flat_hourly = floatval($reservation_meta['total_flat_hourly']);
          if (!empty($total_flat_hourly)) {
            $total_guided_price += $total_flat_hourly;
          }

          $total_equipments_price = floatval($reservation_meta['total_equipments_price']);
          if (!empty($total_equipments_price)) {
            $total_guided_price += $total_equipments_price;
          }

          $output .= '<tr>
                        <td data-label="ID">#' . esc_attr($reservation_id) . '</td>
                        <td data-label="Status"><span class="label label-success">' . esc_attr($reservation_status) . '</span></td>
                        <td data-label="Title"><a href="' . get_permalink($listing_id) . '"><strong>' . get_the_title($listing_id) . '</strong></a></td>
                        <td data-label="Date">' . get_the_time(homey_convert_date(homey_option('homey_date_format'))) . '<br>' . get_the_time(homey_time_format()) . '</td>
                        <td data-label="Amenity">' . homey_formatted_price($amenity_price) . '</td>
                        <td data-label="Guided Service">' . (!empty($total_guided_price) ? homey_formatted_price($total_guided_price) : 'x') . '</td>
                        <td data-label="Accommodation">' . (!empty($accomodation_fee) ? homey_formatted_price($total_acc_fee) : 'x') . '</td>
                        <td data-label="Additional Vehicles">' . (!empty($additional_vehicles_fee) ? homey_formatted_price($additional_vehicles_fee) : 'x') . '</td>
                        <td data-label="Occupancy Tax">' . (!empty($occ_tax_amount) ? $occ_tax_amount : 'x') . '</td>
                        <td data-label="Sales Tax">' . (!empty($total_state_tax) ? $total_state_tax : 'x') . '</td>
                        <td data-label="Tax Status" class="wallet-tax-toggle" data-reservation-id="' . esc_attr($reservation_id) . '">
                            <label class="switch">
                                <input type="checkbox" class="tax-status-toggle" data-reservation-id="' . esc_attr($reservation_id) . '" ' . ($tax_status == 'paid' ? 'checked' : '') . '>
                                <span class="slider round ' . ($tax_status == 'paid' ? 'paid' : 'unpaid') . '"></span>
                            </label>
                            <span class="tax-status-text">' . ucfirst($tax_status) . '</span>
                        </td>
                        <td data-label="Host Amount">' . homey_formatted_price($host_transferred_amount) . '</td>
                    </tr>';
        }
      }
      wp_reset_postdata();
    } else {
      $output = '<tr><td colspan="12">' . esc_html__('No reservations found.', 'homey') . '</td></tr>';
    }

    // Generate pagination using your custom function
    ob_start();
    homey_pagination($reservations_query->max_num_pages, $range = 2);
    $pagination = ob_get_clean();

    // Return both reservations and pagination
    wp_send_json_success(array(
      'reservations' => $output,
      'pagination' => $pagination,
    ));
  } else {
    wp_send_json_error();
  }
}

// AJAX handler for calculating earnings
add_action('wp_ajax_calculate_earnings', 'calculate_earnings');
add_action('wp_ajax_nopriv_calculate_earnings', 'calculate_earnings');

function calculate_earnings()
{
  if (!isset($_POST['earning_type']) || !isset($_POST['earning_start_date']) || !isset($_POST['earning_end_date'])) {
    wp_send_json_error('Invalid input.');
  }

  $earning_type = sanitize_text_field($_POST['earning_type']);
  $start_date = sanitize_text_field($_POST['earning_start_date']);
  $end_date = sanitize_text_field($_POST['earning_end_date']);
  $userID = get_current_user_id();

  if (empty($earning_type) || empty($start_date) || empty($end_date)) {
    wp_send_json_error('Please fill all the fields.');
  }

  $args = array(
    'post_type' => 'homey_reservation',
    'meta_query' => array(
      array(
        'key' => 'host_earning_status',
        'value' => 'transfered',
        'compare' => '='
      ),
      array(
        'key' => 'listing_owner',
        'value' => $userID,
        'compare' => '='
      )
    ),
    'date_query' => array(
      array(
        'after' => $start_date,
        'before' => $end_date,
        'inclusive' => true,
      ),
    ),
  );

  $query = new WP_Query($args);
  $total_earning = 0;

  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      $reservation_meta = get_post_meta(get_the_ID(), 'reservation_meta', true);

      $cleaning_fee = 0.0;
      if (!empty($reservation_meta['total_accomodation_fee']) || $reservation_meta['total_accomodation_fee'] != 0) {
        $cleaning_fee = floatval($reservation_meta['cleaning_fee']);
      }

      switch ($earning_type) {
        case 'amenity':
          $total_earning += floatval($reservation_meta['hours_total_price']) + floatval($reservation_meta['additional_guests_total_price']);
          break;
        case 'sleeping_accommodation':
          $total_earning += floatval($reservation_meta['total_accomodation_fee']) + $cleaning_fee;
          break;
        case 'guided_service':
          $total_earning += floatval($reservation_meta['total_non_participants_price']) + floatval($reservation_meta['total_guest_hourly']) + floatval($reservation_meta['total_guest_fixed']) + floatval($reservation_meta['total_group_hourly']) + floatval($reservation_meta['total_group_fixed']) + floatval($reservation_meta['total_flat_hourly']) + floatval($reservation_meta['total_equipments_price']);
          break;
        case 'additional_vehicles':
          $total_earning += floatval($reservation_meta['additional_vehicles_fee']);
          break;
        case 'occupancy_tax':
          $total_earning += floatval($reservation_meta['occ_tax_amount']);
          break;
        case 'sales_tax':
          $total_earning += floatval($reservation_meta['total_state_tax']);
          break;
      }
    }
  }

  wp_reset_postdata();

  $earning_type_display = ucwords(str_replace('_', ' ', $earning_type));

  $result = '<div class="alert alert-success">';
  $result .= esc_html__('Total Earnings for ', 'homey') . esc_html($earning_type_display) . esc_html__(' between ', 'homey') . esc_html($start_date) . esc_html__(' and ', 'homey') . esc_html($end_date) . esc_html__(': ', 'homey') . homey_formatted_price($total_earning);
  $result .= '</div>';

  wp_send_json_success($result);
}

// complete reservation status
add_action('wp_ajax_homey_set_reservation_complete', 'homey_set_reservation_complete');
if (!function_exists('homey_set_reservation_complete')) {
  function homey_set_reservation_complete()
  {
    $reservation_id = intval($_POST['reservation_id']);
    $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);

    $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
    $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);

    update_post_meta($reservation_id, 'reservation_status', 'completed');

    // Send message on booking complete
    $message = "Backyard Lease update: Reservation - Reservation ID: " . $reservation_id . " has been completed.";
    send_booking_message($reservation_id, $listing_owner, $listing_renter, $message);

    // Send message review Reminder with clickable link
    $review_url = home_url('/reservations/?reservation_detail=' . $reservation_id);
    $message = "Backyard Lease update: Reminder - <a href=\"" . esc_url($review_url) . "\" target=\"_blank\">Leave a review</a>.";
    send_booking_message($reservation_id, $listing_owner, $listing_renter, $message);

    //Remove Pending Dates
    $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id, true);
    update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

    echo json_encode(
      array(
        'success' => true,
        'message' => esc_html__('success', 'homey')
      )
    );
    wp_die();
  }
}
