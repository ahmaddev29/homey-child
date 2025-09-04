<?php
global $current_user, $homey_local, $homey_prefix, $reservationID, $owner_info, $renter_info, $renter_id, $owner_id;
$blogInfo = esc_url(home_url('/'));
wp_get_current_user();
$userID = $current_user->ID;

$back_to_list = homey_get_template_link_2('template/dashboard-reservations.php');
$messages_page = homey_get_template_link_2('template/dashboard-messages.php');
$booking_hide_fields = homey_option('booking_hide_fields');

$reservationID = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
$reservation_status = $notification = $status_label = $notification = '';
$upfront_payment = $check_in = $check_out = $guests = $pets = $renter_msg = '';
$payment_link = '';
$reservation_meta = get_post_meta($reservationID, 'reservation_meta', true);
$flag = $reservation_meta['flag'];

$booking_detail_hide_fields = homey_option('booking_detail_hide_fields');


if (!empty($reservationID)) {

  $post = get_post($reservationID);

  $current_date = date('Y-m-d', current_time('timestamp', 0));
  $current_date_unix = strtotime($current_date);

  $reservation_status = get_post_meta($reservationID, 'reservation_status', true);
  $upfront_payment = get_post_meta($reservationID, 'reservation_upfront', true);

  $extra_expenses = homey_get_extra_expenses($reservationID);
  $extra_discount = homey_get_extra_discount($reservationID);

  $upfront_payment += isset($extra_expenses['expenses_total_price']) ? $extra_expenses['expenses_total_price'] : 0;
  $upfront_payment -= isset($extra_discount['discount_total_price']) ? $extra_discount['discount_total_price'] : 0;

  $upfront_payment = homey_formatted_price($upfront_payment);

  $payment_link = homey_get_template_link_2('template/dashboard-payment.php');

  $reservation_meta = get_post_meta($reservationID, 'reservation_meta', true);
  $check_in_date = $reservation_meta['check_in_date'];
  $check_in = $reservation_meta['start_hour'];
  $check_out = $reservation_meta['end_hour'];

  $new_check_in_date = $reservation_meta['new_check_in_date'];
  $new_check_in = $reservation_meta['new_start_hour'];
  $new_check_out = $reservation_meta['new_end_hour'];


  $guests = get_post_meta($reservationID, 'reservation_guests', true);
  $listing_id = get_post_meta($reservationID, 'reservation_listing_id', true);
  $pets = get_post_meta($listing_id, $homey_prefix . 'pets', true);
  $res_meta = get_post_meta($reservationID, 'reservation_meta', true);

  $amenity_price_type = $reservation_meta['amenity_price_type'];

  $start_hour = get_post_meta($listing_id, $homey_prefix . 'start_hour', true);
  $end_hour = get_post_meta($listing_id, $homey_prefix . 'end_hour', true);

  $day_start_hour = get_post_meta($listing_id, $homey_prefix . 'checkin_after', true);
  $day_end_hour = get_post_meta($listing_id, $homey_prefix . 'checkout_before', true);

  if (empty($day_start_hour)) {
    $day_start_hour = '01:00';
  }

  if (empty($day_end_hour)) {
    $day_end_hour = '24:00';
  }

  $day_start_hour = strtotime($day_start_hour);
  $day_end_hour = strtotime($day_end_hour);

  $first_half_start_hour = get_field('first_half_start_hour', $listing_id);
  $first_half_end_hour = get_field('first_half_end_hour', $listing_id);

  if (empty($first_half_start_hour)) {
    $first_half_start_hour = '01:00';
  }

  if (empty($first_half_end_hour)) {
    $first_half_end_hour = '24:00';
  }

  $first_half_start_hour = strtotime($first_half_start_hour);
  $first_half_end_hour = strtotime($first_half_end_hour);

  $second_half_start_hour = get_field('second_half_start_hour', $listing_id);
  $second_half_end_hour = get_field('second_half_end_hour', $listing_id);

  if (empty($second_half_start_hour)) {
    $second_half_start_hour = '01:00';
  }

  if (empty($second_half_end_hour)) {
    $second_half_end_hour = '24:00';
  }

  $second_half_start_hour = strtotime($second_half_start_hour);
  $second_half_end_hour = strtotime($second_half_end_hour);


  $time_change_requested = $reservation_meta['time_change_requested'];

  $renter_msg = isset($res_meta['renter_msg']) ? $res_meta['renter_msg'] : '';

  $renter_id = get_post_meta($reservationID, 'listing_renter', true);
  $renter_info = homey_get_author_by_id('60', '60', 'reserve-detail-avatar img-circle', $renter_id);

  $renter_name_while_booking = get_user_meta($renter_id, 'first_name', true);
  $renter_name_while_booking .= ' ' . get_user_meta($renter_id, 'last_name', true);
  $renter_phone = get_user_meta($renter_id, 'phone', true);

  $owner_id = get_post_meta($reservationID, 'listing_owner', true);
  $owner_info = homey_get_author_by_id('60', '60', 'reserve-detail-avatar img-circle', $owner_id);

  $payment_link = add_query_arg(
    array(
      'reservation_id' => $reservationID,
    ),
    $payment_link
  );

  $chcek_reservation_thread = homey_chcek_reservation_thread($reservationID);

  if ($chcek_reservation_thread != '') {
    $messages_page_link = add_query_arg(
      array(
        'thread_id' => $chcek_reservation_thread
      ),
      $messages_page
    );
  } else {
    $messages_page_link = add_query_arg(
      array(
        'reservation_id' => $reservationID,
        'message' => 'new',
      ),
      $messages_page
    );
  }

  $guests_label = homey_option('cmn_guest_label');
  if ($guests > 1) {
    $guests_label = homey_option('cmn_guests_label');
  }


  $start_hour = get_post_meta($listing_id, $homey_prefix . 'start_hour', true);
  $end_hour = get_post_meta($listing_id, $homey_prefix . 'end_hour', true);
  $prefilled = homey_get_dates_for_booking();
  $pre_start_hour = $prefilled['start'];
  $pre_end_hour = $prefilled['end'];
  $start_hours_list = '';
  $end_hours_list = '';
  $start_hour = strtotime($start_hour);
  $end_hour = strtotime($end_hour);
  for ($halfhour = $start_hour; $halfhour <= $end_hour; $halfhour = $halfhour + 30 * 60) {
    $start_hours_list .= '<option ' . selected($pre_start_hour, date('H:i', $halfhour), false) . ' value="' . date('H:i', $halfhour) . '">' . date(homey_time_format(), $halfhour) . '</option>';
    $end_hours_list .= '<option ' . selected($pre_end_hour, date('H:i', $halfhour), false) . ' value="' . date('H:i', $halfhour) . '">' . date(homey_time_format(), $halfhour) . '</option>';
  }
}
if (($post->post_author != $userID) && homey_is_renter()) {
  echo ('Are you kidding?');
} else {
?>
  <div class="user-dashboard-right dashboard-with-sidebar">
    <!-- <?php var_dump($prefilled); ?> -->
    <div class="dashboard-content-area">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="dashboard-area">

              <?php homey_reservation_notification($reservation_status); ?>
              <div class="block">
                <div class="block-head">
                  <div class="block-left">
                    <h2 class="title"><?php echo esc_attr($homey_local['reservation_label']); ?>
                      <?php $wc_order_id = get_wc_order_id(get_the_ID());
                      $wc_order_id_txt = $wc_order_id > 0 ? ', wc#' . $wc_order_id . ' ' : ' '; ?>
                      <?php echo '#' . $reservationID . $wc_order_id_txt . ' ' . homey_get_reservation_label($reservation_status); ?>
                    </h2>
                  </div><!-- block-left -->
                  <div class="block-right">
                    <div class="custom-actions">
                      <?php if ($reservation_status == 'booked' || $reservation_status == 'completed' && homey_is_renter()) { ?>
                        <button class="btn-action" data-toggle="collapse" data-target="#review-form" aria-expanded="false"
                          aria-controls="collapseExample" data-toggle="tooltip" data-placement="top"
                          data-original-title="<?php echo esc_attr($homey_local['review_btn']); ?>">
                          <i class="fa fa-pencil"></i>
                        </button>
                        <?php if ($flag == 1 && homey_is_renter()) { ?>
                          <button class="btn-action" data-toggle="collapse" data-target="#guided-review-form"
                            aria-expanded="false" aria-controls="collapseExample" data-toggle="tooltip" data-placement="top"
                            data-original-title="<?php echo esc_attr($homey_local['review_btn']); ?>">
                            <i class="fa fa-pencil"></i>
                          </button>
                        <?php } ?>
                      <?php } ?>

                      <button onclick="window.print();" class="btn-action" data-toggle="tooltip" data-placement="top"
                        data-original-title="<?php echo esc_attr($homey_local['print_btn']); ?>"><i
                          class="fa fa-print"></i></button>

                      <a href="<?php echo esc_url($messages_page_link); ?>" class="btn-action" data-toggle="tooltip"
                        data-placement="top"
                        data-original-title="<?php echo esc_attr($homey_local['msg_send_btn']); ?>"><i
                          class="fa fa-envelope-open-o"></i></a>

                      <!-- <a href="#" class="reservation-delete btn-action" data-id="<?php echo esc_attr($reservationID); ?>"
                        data-toggle="tooltip" data-placement="top"
                        data-original-title="<?php echo esc_html__('Delete', 'homey'); ?>"><i class="fa fa-trash"></i></a> -->

                      <a href="<?php echo esc_url($back_to_list); ?>" class="btn-action" data-toggle="tooltip"
                        data-placement="top" data-original-title="<?php echo esc_attr($homey_local['back_btn']); ?>"><i
                          class="fa fa-mail-reply"></i></a>
                    </div><!-- custom-actions -->
                  </div><!-- block-right -->
                </div><!-- block-head -->


                <?php
                if ($reservation_status == 'booked' || $reservation_status == 'completed' && homey_is_renter()) {
                  get_template_part('template-parts/dashboard/reservation/review-form');
                } elseif ($reservation_status == 'booked') {
                  get_template_part('template-parts/dashboard/reservation/review-host');
                }
                ?>

                <?php
                get_template_part('template-parts/dashboard/reservation/add-extra-expenses');
                get_template_part('template-parts/dashboard/reservation/discount');

                if ($reservation_status == 'declined') {
                  get_template_part('template-parts/dashboard/reservation/declined');
                } elseif ($reservation_status == 'cancelled') {
                  get_template_part('template-parts/dashboard/reservation/cancelled');
                } else {

                  if (homey_is_renter()) {
                    get_template_part('template-parts/dashboard/reservation/cancel-hourly-form');
                  } else {
                    get_template_part('template-parts/dashboard/reservation/decline-hourly-form');
                  }
                }

                if ($res_meta['no_of_hours'] > 1) {
                  $hour_label = esc_html__('Hours', 'homey');
                } else {
                  $hour_label = esc_html__('Hour', 'homey');
                }

                ?>

                <div class="block-section">
                  <div class="block-body">
                    <div class="block-left">
                      <ul class="detail-list">
                        <li><strong><?php echo esc_attr($homey_local['date_label']); ?>:</strong></li>
                        <li><?php echo esc_attr(get_the_date(get_option('date_format'), $reservationID)); ?>
                          <br>
                          <?php echo esc_attr(get_the_date(homey_time_format(), $reservationID)); ?>
                        </li>
                        <li><strong>Reason for booking this adventure:</strong></li>
                        <li><?php echo esc_attr($res_meta['guest_message']); ?></li>
                      </ul>
                    </div><!-- block-left -->
                    <div class="block-right">
                      <ul class="detail-list">
                        <li><strong><?php esc_html_e('From', 'homey'); ?>:</strong>
                          <?php if (!empty($renter_info['photo'])) {
                            echo '<a href="' . esc_url($renter_info['link']) . '" target="_blank">' . $renter_info['photo'] . '</a>';
                          } ?>
                          <a href="<?php echo esc_url($renter_info['link']); ?>" target="_blank">
                            <?php echo esc_attr($renter_info['name']); ?>
                          </a>
                        </li>
                        <?php if ($booking_detail_hide_fields['renter_information_on_detail'] == 0) { ?>
                          <li>
                            <strong><?php esc_html_e('Renter Detail', 'homey'); ?>:&nbsp;</strong><?php echo esc_attr($renter_name_while_booking) . ' <a title="' . esc_html__('Click to call', 'homey') . '" href="tel:' . $renter_phone . '">' . $renter_phone; ?></a>
                          </li>
                        <?php } ?>
                        <li>
                          <strong><?php esc_html_e('Listing Name', 'homey'); ?>:&nbsp;</strong><?php echo get_the_title($listing_id); ?>
                        </li>
                      </ul>
                    </div><!-- block-right -->
                  </div><!-- block-body -->
                </div><!-- block-section -->

                <div class="block-section">
                  <div class="block-body">
                    <div class="block-left">
                      <h2 class="title"><?php esc_html_e('Details', 'homey'); ?></h2>
                    </div><!-- block-left -->
                    <div class="block-right">
                      <ul class="detail-list detail-list-2-cols">
                        <li>
                          <?php echo esc_attr($homey_local['check_In']); ?>:
                          <strong>
                            <?php echo homey_format_date_simple($check_in_date); ?>
                            <?php echo esc_html__('at', 'homey'); ?>
                            <?php echo date(homey_time_format(), strtotime($check_in)); ?>
                          </strong>
                        </li>
                        <li>
                          <?php echo esc_attr($homey_local['check_Out']); ?>:
                          <strong>
                            <?php echo homey_format_date_simple($check_in_date); ?>
                            <?php echo esc_html__('at', 'homey'); ?>
                            <?php echo date(homey_time_format(), strtotime($check_out)); ?>
                          </strong>
                        </li>

                        <?php if ($flag == 1) { ?>
                          <?php if (!empty($res_meta['guests_participating'])) { ?>
                            <li>
                              Guided Service Guests:
                              <strong><?php echo esc_attr($res_meta['guests_participating']); ?></strong>
                            </li>
                          <?php } ?>

                          <?php if (!empty($res_meta['guests_ages'])) { ?>
                            <li>
                              Ages Of Guests:
                              <strong><?php echo esc_attr($res_meta['guests_ages']); ?></strong>
                            </li>
                          <?php } ?>

                          <?php if (!empty($res_meta['experience_level'])) { ?>
                            <li>
                              Experience Level:
                              <strong><?php echo esc_attr($res_meta['experience_level']); ?></strong>
                            </li>
                          <?php } ?>
                        <?php } ?>

                        <li>
                          <?php echo esc_attr($hour_label); ?>:
                          <strong><?php echo esc_attr($res_meta['no_of_hours']); ?></strong>
                        </li>
                        <?php if ($booking_hide_fields['guests'] != 1) { ?>
                          <li>
                            <?php echo esc_attr($guests_label); ?>:
                            <strong><?php echo esc_attr($guests); ?></strong>
                          </li>
                        <?php } ?>

                        <?php if (!empty($res_meta['additional_guests'])) { ?>
                          <li>
                            Additional Guests:
                            <strong><?php echo esc_attr($res_meta['additional_guests']); ?></strong>
                          </li>
                        <?php } ?>

                        <?php if ($flag == 1) { ?>

                          <?php if (!empty($res_meta['guests_gears'])) { ?>
                            <li>
                              Personal Gear?:
                              <strong><?php echo esc_attr($res_meta['guests_gears']); ?></strong>
                            </li>
                          <?php } ?>

                          <?php if (!empty($res_meta['extra_participants'])) { ?>
                            <li>
                              Guided Service Extra Guests:
                              <strong><?php echo esc_attr($res_meta['extra_participants']); ?></strong>
                            </li>
                          <?php } ?>

                          <?php if (!empty($res_meta['health_conditions'])) { ?>
                            <li>
                              Health Conditions:
                              <strong><?php echo esc_attr($res_meta['health_conditions']); ?></strong>
                            </li>
                          <?php } ?>

                          <?php if (!empty($res_meta['first_timers'])) { ?>
                            <li>
                              Any First Timers:
                              <strong><?php echo esc_attr($res_meta['first_timers']); ?></strong>
                            </li>
                          <?php } ?>
                        <?php } ?>

                      </ul>
                      <?php if (homey_is_host()) { ?>
                        <a id="edit_reservation_time" href="#" data-id="<?php echo $reservationID; ?>">Edit Reservation
                          Times</a>
                      <?php } ?>
                      <div id="edit_reservation_calendar" style="display: none;">
                        <div class="homey_notification">
                          <div id="single-listing-date-range" class="search-date-range">
                            <div class="search-date-range-arrive search-date-hourly-arrive">
                              <input id="hourly_check_inn" name="arrive"
                                value="<?php echo esc_attr($prefilled['arrive']); ?>" readonly type="text"
                                class="form-control check_in_date" autocomplete="off"
                                placeholder="<?php echo esc_attr(homey_option('srh_arrive_label')); ?>">
                            </div>

                            <div id="single-booking-search-calendar"
                              class="search-calendar search-calendar-single clearfix single-listing-booking-calendar-js hourly-js-desktop clearfix"
                              style="display: none;">
                              <?php homeyHourlyAvailabilityCalendar(); ?>

                              <div class="calendar-navigation custom-actions">
                                <button class="listing-cal-prev btn btn-action pull-left disabled"><i
                                    class="fa fa-chevron-left" aria-hidden="true"></i></button>
                                <button class="listing-cal-next btn btn-action pull-right"><i class="fa fa-chevron-right"
                                    aria-hidden="true"></i></button>
                              </div><!-- calendar-navigation -->
                            </div>
                          </div>

                          <?php if ($amenity_price_type == 'price_per_hour') { ?>
                            <div class="search-hours-range clearfix">
                              <div class="search-hours-range-left">
                                <select name="start_hour" id="start_hour" class="selectpicker start_hour"
                                  data-live-search="true" title="<?php echo homey_option('srh_starts_label'); ?>">
                                  <option value=""><?php echo homey_option('srh_starts_label'); ?></option>
                                  <?php echo '' . $start_hours_list; ?>
                                </select>
                              </div>
                              <div class="search-hours-range-right">
                                <select name="end_hour" id="end_hour" class="selectpicker end_hour" data-live-search="true"
                                  title="<?php echo homey_option('srh_ends_label'); ?>">
                                  <option value=""><?php echo homey_option('srh_ends_label'); ?></option>
                                  <?php echo '' . $end_hours_list; ?>
                                </select>
                              </div>
                            </div>
                          <?php } ?>

                          <?php if ($amenity_price_type == 'price_per_day') { ?>
                            <div class="search-hours-range clearfix" style="margin-bottom: 35px;">
                              <div class="search-hours-range-left">
                                <div class="day-hours-range-text">Day Starts</div>
                                <select name="start_hour" id="start_hour" class="selectpicker start_hour"
                                  data-live-search="true" title="<?php echo homey_option('srh_starts_label'); ?>" disabled>
                                  <option value=""><?php echo homey_option('srh_starts_label'); ?></option>
                                  <?php
                                  for ($halfhour = $day_start_hour; $halfhour <= $day_end_hour; $halfhour += 30 * 60) {
                                    $formatted_time = date('H:i', $halfhour);
                                    $selected = selected($formatted_time, date('H:i', $day_start_hour), false);
                                    echo '<option ' . $selected . ' value="' . $formatted_time . '">' . date(homey_time_format(), $halfhour) . '</option>';
                                  }
                                  ?>
                                </select>
                              </div>
                              <div class="search-hours-range-right">
                                <div class="day-hours-range-text">Day Ends</div>
                                <select name="end_hour" id="end_hour" class="selectpicker end_hour" data-live-search="true"
                                  title="<?php echo homey_option('srh_ends_label'); ?>" disabled>
                                  <option value=""><?php echo homey_option('srh_ends_label'); ?></option>
                                  <?php
                                  for ($halfhour = $day_start_hour; $halfhour <= $day_end_hour; $halfhour += 30 * 60) {
                                    $formatted_time = date('H:i', $halfhour);
                                    $selected = selected($formatted_time, date('H:i', $day_end_hour), false);
                                    echo '<option ' . $selected . ' value="' . $formatted_time . '">' . date(homey_time_format(), $halfhour) . '</option>';
                                  }
                                  ?>
                                </select>
                              </div>
                            </div>
                          <?php } ?>

                          <?php if ($amenity_price_type == 'price_per_half_day') { ?>
                            <div class="half-day-buttons">
                              <button type="button" class="btn half-day-btn active" data-target=".first-half-day">First Half</button>
                              <button type="button" class="btn half-day-btn" data-target=".second-half-day">Second Half</button>
                            </div>

                            <div class="search-hours-range clearfix half-day-hour first-half-day active" style="margin-bottom: 40px;">
                              <div class="search-hours-range-left">
                                <div class="hours-range-text">First Half Starts</div>
                                <select name="start_hour" id="start_hour" class="selectpicker start_hour"
                                  data-live-search="true" title="<?php echo homey_option('srh_starts_label'); ?>" disabled>
                                  <option value=""><?php echo homey_option('srh_starts_label'); ?></option>
                                  <?php
                                  for ($halfhour = $first_half_start_hour; $halfhour <= $first_half_end_hour; $halfhour += 30 * 60) {
                                    $formatted_time = date('H:i', $halfhour);
                                    $selected = selected($formatted_time, date('H:i', $first_half_start_hour), false);
                                    echo '<option ' . $selected . ' value="' . $formatted_time . '">' . date(homey_time_format(), $halfhour) . '</option>';
                                  }
                                  ?>
                                </select>
                              </div>
                              <div class="search-hours-range-right">
                                <div class="hours-range-text">First Half Ends</div>
                                <select name="end_hour" id="end_hour" class="selectpicker end_hour" data-live-search="true"
                                  title="<?php echo homey_option('srh_ends_label'); ?>" disabled>
                                  <option value=""><?php echo homey_option('srh_ends_label'); ?></option>
                                  <?php
                                  for ($halfhour = $first_half_start_hour; $halfhour <= $first_half_end_hour; $halfhour += 30 * 60) {
                                    $formatted_time = date('H:i', $halfhour);
                                    $selected = selected($formatted_time, date('H:i', $first_half_end_hour), false);
                                    echo '<option ' . $selected . ' value="' . $formatted_time . '">' . date(homey_time_format(), $halfhour) . '</option>';
                                  }
                                  ?>
                                </select>
                              </div>
                            </div>

                            <div class="search-hours-range clearfix half-day-hour second-half-day" style="margin-bottom: 40px;">
                              <div class="search-hours-range-left">
                                <div class="hours-range-text">Second Half Starts</div>
                                <select id="start_hour" class="selectpicker start_hour"
                                  data-live-search="true" title="<?php echo homey_option('srh_starts_label'); ?>" disabled>
                                  <option value=""><?php echo homey_option('srh_starts_label'); ?></option>
                                  <?php
                                  for ($halfhour = $second_half_start_hour; $halfhour <= $second_half_end_hour; $halfhour += 30 * 60) {
                                    $formatted_time = date('H:i', $halfhour);
                                    $selected = selected($formatted_time, date('H:i', $second_half_start_hour), false);
                                    echo '<option ' . $selected . ' value="' . $formatted_time . '">' . date(homey_time_format(), $halfhour) . '</option>';
                                  }
                                  ?>
                                </select>
                              </div>
                              <div class="search-hours-range-right">
                                <div class="hours-range-text">Second Half Ends</div>
                                <select id="end_hour" class="selectpicker end_hour" data-live-search="true"
                                  title="<?php echo homey_option('srh_ends_label'); ?>" disabled>
                                  <option value=""><?php echo homey_option('srh_ends_label'); ?></option>
                                  <?php
                                  for ($halfhour = $second_half_start_hour; $halfhour <= $second_half_end_hour; $halfhour += 30 * 60) {
                                    $formatted_time = date('H:i', $halfhour);
                                    $selected = selected($formatted_time, date('H:i', $second_half_end_hour), false);
                                    echo '<option ' . $selected . ' value="' . $formatted_time . '">' . date(homey_time_format(), $halfhour) . '</option>';
                                  }
                                  ?>
                                </select>
                              </div>
                            </div>
                          <?php } ?>

                        </div>
                        <input type="hidden" name="listing_id" id="listing_id" value="<?php echo intval($reservationID); ?>">

                        <button id="update_reservation_time" class="btn btn-primary"
                          data-id="<?php echo intval($reservationID); ?>" disabled>Update Reservation Request</button>
                      </div>
                    </div><!-- block-right -->
                  </div><!-- block-body -->
                </div><!-- block-section -->
                <?php if ($time_change_requested == 1) { ?>
                  <div class="block-section">
                    <div class="block-body">
                      <div class="block-left">
                        <h2 class="title"><?php esc_html_e('Requested time change', 'homey'); ?></h2>
                      </div><!-- block-left -->
                      <div class="block-right time-change-msg">
                        <ul class="detail-list detail-list-1-cols">
                          <li>
                            <?php echo esc_attr($homey_local['check_In']); ?>:
                            <strong>
                              <?php echo homey_format_date_simple($new_check_in_date); ?>
                              <?php echo esc_html__('at', 'homey'); ?>
                              <?php echo date(homey_time_format(), strtotime($new_check_in)); ?>
                            </strong>
                          </li>
                          <li>
                            <?php echo esc_attr($homey_local['check_Out']); ?>:
                            <strong>
                              <?php echo homey_format_date_simple($new_check_in_date); ?>
                              <?php echo esc_html__('at', 'homey'); ?>
                              <?php echo date(homey_time_format(), strtotime($new_check_out)); ?>
                            </strong>
                          </li>
                        </ul>
                        <?php if (homey_is_renter()) { ?>
                          <div id="confirm_buttons">
                            <button id="confirm_update_reservation" class="btn btn-success"
                              data-id="<?php echo intval($reservationID); ?>">Confirm</button>
                            <button id="reject_update_reservation" class="btn btn-danger">Reject</button>
                          </div>
                        <?php } ?>
                      </div><!-- block-right -->
                    </div><!-- block-body -->
                  </div><!-- block-section -->
                <?php } ?>

                <?php if (!empty($renter_msg)) { ?>
                  <div class="block-section">
                    <div class="block-body">
                      <div class="block-left">
                        <h2 class="title"><?php esc_html_e('Notes', 'homey'); ?></h2>
                      </div><!-- block-left -->
                      <div class="block-right">
                        <p><?php echo esc_attr($renter_msg); ?></p>
                      </div><!-- block-right -->
                    </div><!-- block-body -->
                  </div><!-- block-section -->
                <?php } ?>

                <div class="block-section">
                  <div class="block-body">
                    <div class="block-left">
                      <h2 class="title"><?php echo esc_attr($homey_local['payment_label']); ?></h2>
                    </div><!-- block-left -->
                    <div class="block-right">
                      <?php echo homey_calculate_hourly_reservation_cost($reservationID); ?>
                    </div><!-- block-right -->
                  </div><!-- block-body -->
                </div><!-- block-section -->
              </div><!-- .block -->
              <div class="payment-buttons">
                <?php homey_reservation_action($reservation_status, $upfront_payment, $payment_link, $reservationID, 'btn-half-width'); ?>
              </div>
            </div><!-- .dashboard-area -->
          </div><!-- col-lg-12 col-md-12 col-sm-12 -->
        </div>
      </div><!-- .container-fluid -->
    </div><!-- .dashboard-content-area -->
    <aside class="dashboard-sidebar">
      <?php get_template_part('template-parts/dashboard/reservation/payment-sidebar-hourly'); ?>

      <?php homey_reservation_action($reservation_status, $upfront_payment, $payment_link, $reservationID, 'btn-full-width'); ?>

    </aside><!-- .dashboard-sidebar -->
  </div><!-- .user-dashboard-right -->
  <?php get_template_part('template-parts/dashboard/reservation/message'); ?>
<?php } ?>