<?php
add_action('wp_ajax_nopriv_homey_add_hourly_reservation', 'homey_add_hourly_reservation');
add_action('wp_ajax_homey_add_hourly_reservation', 'homey_add_hourly_reservation');
if (!function_exists('homey_add_hourly_reservation')) {
    function homey_add_hourly_reservation()
    {
        global $current_user;


        $admin_email = get_option('admin_email');

        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $username = get_the_author_meta('user_login', $userID);
        $display_name_public = get_the_author_meta('display_name_public', $userID);
        $guest_name = empty($display_name_public) ? $username : $display_name_public;
        $guest_phone_number = get_the_author_meta('homey_phone_number', $userID);
        $saved_cards = get_user_meta($userID, 'saved_stripe_cards', true);
        $saved_cards = $saved_cards ? json_decode($saved_cards, true) : [];
        $author = homey_get_author_by_id('70', '70', 'img-circle media-object avatar', $userID);
        $doc_verified = $author['doc_verified'];
        $verified = false;
        if ($doc_verified) {
            $verified = true;
        }

        $local = homey_get_localization();

        //check security
        //        $nonce = $_REQUEST['security'];
        //        if ( ! wp_verify_nonce( $nonce, 'reservation-security-nonce' ) ) {
        //
        //            echo json_encode(
        //                array(
        //                    'success' => false,
        //                    'message' => $local['security_check_text']
        //                )
        //            );
        //            wp_die();
        //        }


        $allowded_html = array();
        $reservation_meta = array();

        $listing_id = intval($_POST['listing_id']);
        $listing_title = get_the_title($listing_id);
        $listing_owner_id = get_post_field('post_author', $listing_id);
        $host_phone_number = get_the_author_meta('homey_phone_number', $listing_owner_id);
        $notification_settings_host = get_user_meta($listing_owner_id, 'notification_settings', true);
        $check_in_date = wp_kses($_POST['check_in_date'], $allowded_html);
        $booking_date = format_check_in_date($check_in_date);
        $start_hour = wp_kses($_POST['start_hour'], $allowded_html);
        $booking_start_time = date(homey_time_format(), strtotime($start_hour));
        $end_hour = wp_kses($_POST['end_hour'], $allowded_html);
        $booking_end_time = date(homey_time_format(), strtotime($end_hour));
        $guests = intval($_POST['guests']);

        $have_guided_service = get_field('have_guided_service', $listing_id);
        $amenity_price_type = get_field('amenity_price_type', $listing_id);

        $have_sleeping_accommodations = get_field('field_6479eb9f0208c', $listing_id);
        $include_backyard_amenity = get_field('include_backyard_amenity', $listing_id);

        $choose_guided_service = $_POST['choose_guided_service'];
        $accommodation_number = intval($_POST['accommodation_number']);
        $guests_participating = intval($_POST['guests_participating']);
        $extra_participants = intval($_POST['extra_participants']);
        $guests_gears = $_POST['guests_gears'];
        $guests_ages = $_POST['guests_ages'];
        $health_conditions = $_POST['health_conditions'];
        $experience_level = $_POST['experience_level'];
        $first_timers = $_POST['first_timers'];

        $current_user_id = get_current_user_id();
        $coupon_code = sanitize_text_field($_POST['coupon_code']);

        $selected_card_id = $_POST['selected_card_id'];
        $extra_options = $_POST['extra_options'];
        $extra_equipments = $_POST['extra_equipments'];
        $additional_vehicles = $_POST['additional_vehicles'];
        $guest_message = stripslashes($_POST['guest_message']);
        $title = $local['reservation_text'];

        $check_in_hour = $check_in_date . ' ' . $start_hour;
        $check_out_hour = $check_in_date . ' ' . $end_hour;

        $owner = homey_usermeta($listing_owner_id);
        $owner_email = $owner['email'];
        $booking_hide_fields = homey_option('booking_hide_fields');

        $no_login_needed_for_booking = homey_option('no_login_needed_for_booking');

        $date = date('Y-m-d G:i:s', current_time('timestamp', 0));

        if ($current_user->ID == 0 && $no_login_needed_for_booking == "yes" && isset($_REQUEST['new_reser_request_user_email'])) {
            $email = trim($_REQUEST['new_reser_request_user_email']);
            if (empty($email)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => esc_html__('Enter email address', 'homey')
                    )
                );
                wp_die();
            }


            $user = get_user_by('email', $email);

            if (isset($user->ID)) {
                add_filter('authenticate', 'for_reservation_nop_auto_login', 3, 10);
                for_reservation_nop_auto_login($user);
            } else { //create user from email
                $user_login = $email;
                $user_email = $email;
                $user_pass = wp_generate_password(8, false);
                $userdata = compact('user_login', 'user_email', 'user_pass');
                $new_user_id = wp_insert_user($userdata);

                if ($new_user_id > 0) {
                    homey_wp_new_user_notification($new_user_id, $user_pass);
                }

                update_user_meta($new_user_id, 'viaphp', 1);

                // log in automatically
                if (!is_user_logged_in()) {
                    $user = get_user_by('email', $email);

                    add_filter('authenticate', 'for_reservation_nop_auto_login', 3, 10);
                    for_reservation_nop_auto_login($user);
                }
            }
        }

        $current_user = wp_get_current_user();
        $userID = $current_user->ID;

        if ((empty($guests) || $guests === 0) && $booking_hide_fields['guests'] != 1) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['choose_guests']
                )
            );
            wp_die();
        }

        if ($no_login_needed_for_booking == 'no' && (!is_user_logged_in() || $userID === 0)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['login_for_reservation']
                )
            );
            wp_die();
        }

        if ($no_login_needed_for_booking == 'no' && $userID == $listing_owner_id) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['own_listing_error']
                )
            );
            wp_die();
        }

        if (!homey_is_renter()) {
            $current_user = wp_get_current_user();
            $current_user->set_role('homey_renter');
            echo json_encode(
                array(
                    'success' => false,
                    'message' => 'Trying to book a listing? No problem, Switching over to Adventurer.',
                    'reload' => true
                )
            );
            wp_die();
        }

        if ($choose_guided_service == 'on' || $have_guided_service == 'guide_required') {

            if (empty($guests_gears)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => 'Will you be bringing any of your own personal gear, supplies, or equipment on this Guided Service?'
                    )
                );
                wp_die();
            }

            if (empty($guests_participating)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => 'Enter number of guests for this Guided Service'
                    )
                );
                wp_die();
            }

            if (empty($guests_ages)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => 'Enter ages of guests for this Guided Service'
                    )
                );
                wp_die();
            }

            if (empty($health_conditions)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => 'Enter health conditions of guests for this Guided Service'
                    )
                );
                wp_die();
            }

            if (empty($experience_level)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => 'Enter your level of experience for this Guided Service'
                    )
                );
                wp_die();
            }

            if (empty($first_timers)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => 'Any first timers on this Guided Service?'
                    )
                );
                wp_die();
            }
        }

        if (empty($guest_message)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => 'Enter reason for booking this adventure'
                )
            );
            wp_die();
        }

        if (empty($saved_cards) && $verified == false) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => 'We’re sorry, you must complete your profile/verification process and add atleast one payment method before requesting a booking.'
                )
            );
            wp_die();
        }

        if (empty($selected_card_id)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => 'Please select payemnt method.'
                )
            );
            wp_die();
        }

        $coupon_id = homey_validate_coupon($coupon_code, $listing_id, $current_user_id);

        $check_availability = check_hourly_booking_availability($check_in_date, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $listing_id, $guests);
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if ($is_available) {
            $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests, $extra_options, $accommodation_number, $guests_participating, $extra_participants, $choose_guided_service, $extra_equipments, $additional_vehicles, $coupon_id);

            $reservation_meta['no_of_hours'] = $prices_array['hours_count'];
            $reservation_meta['additional_guests'] = $prices_array['additional_guests'];

            $upfront_payment = $prices_array['upfront_payment'];
            $balance = $prices_array['balance'];
            $total_price = $prices_array['total_price'];

            $reservation_meta['check_in_date'] = $check_in_date;
            $reservation_meta['guest_message'] = $guest_message;
            $reservation_meta['check_in_hour'] = $check_in_hour;
            $reservation_meta['check_out_hour'] = $check_out_hour;
            $reservation_meta['start_hour'] = $start_hour;
            $reservation_meta['end_hour'] = $end_hour;
            $reservation_meta['guests'] = $guests;
            $reservation_meta['listing_id'] = $listing_id;

            $reservation_meta['price_per_hour'] = $prices_array['price_per_hour'];
            $reservation_meta['hours_total_price'] = $prices_array['hours_total_price']; //$hours_total_price;

            $reservation_meta['cleaning_fee'] = $prices_array['cleaning_fee'];
            $reservation_meta['accomodation_fee'] = $prices_array['accomodation_fee'];
            $reservation_meta['total_accomodation_fee'] = $prices_array['total_accomodation_fee'];
            $reservation_meta['accommodation_number'] = $accommodation_number;

            $reservation_meta['have_sleeping_accommodations'] = $have_sleeping_accommodations;
            $reservation_meta['include_backyard_amenity'] = $include_backyard_amenity;

            $reservation_meta['guests_participating'] = $guests_participating;
            $reservation_meta['extra_participants'] = $extra_participants;
            $reservation_meta['choose_guided_service'] = $choose_guided_service;
            $reservation_meta['guests_gears'] = $guests_gears;
            $reservation_meta['guests_ages'] = $guests_ages;
            $reservation_meta['health_conditions'] = $health_conditions;
            $reservation_meta['experience_level'] = $experience_level;
            $reservation_meta['first_timers'] = $first_timers;
            $reservation_meta['guided_fee'] = $prices_array['guided_fee'];
            $reservation_meta['total_guest_hourly'] = $prices_array['total_guest_hourly'];
            $reservation_meta['total_guest_fixed'] = $prices_array['total_guest_fixed'];
            $reservation_meta['total_group_hourly'] = $prices_array['total_group_hourly'];
            $reservation_meta['total_group_fixed'] = $prices_array['total_group_fixed'];
            $reservation_meta['total_flat_hourly'] = $prices_array['total_flat_hourly'];
            $reservation_meta['total_flat_fixed'] = $prices_array['total_flat_fixed'];
            $reservation_meta['gears_price'] = $prices_array['gears_price'];
            $reservation_meta['total_gears_price'] = $prices_array['total_gears_price'];
            $reservation_meta['non_participants_price'] = $prices_array['non_participants_price'];
            $reservation_meta['total_non_participants_price'] = $prices_array['total_non_participants_price'];
            $reservation_meta['total_participants'] = $prices_array['total_participants'];
            $reservation_meta['flag'] = $prices_array['flag'];
            $reservation_meta['occ_tax_amount'] = $prices_array['occ_tax_amount'];
            $reservation_meta['total_state_tax'] = $prices_array['total_state_tax'];

            $reservation_meta['city_fee'] = $prices_array['city_fee'];
            $reservation_meta['services_fee'] = $prices_array['services_fee'];

            $reservation_meta['amenity_value'] = $prices_array['amenity_value'];

            $reservation_meta['amenity_price_type'] = $amenity_price_type;

            $reservation_meta['coupon_discount'] = $prices_array['coupon_discount'];
            if (!empty($prices_array['coupon_discount']) && $prices_array['coupon_discount'] != 0) {
                $guests = get_post_meta($coupon_id, 'coupon_guests_ids', true);
                if ($guests && in_array($current_user_id, $guests)) {
                    $updated_guests = array_diff($guests, array($current_user_id));
                    update_post_meta($coupon_id, 'coupon_guests_ids', $updated_guests);
                }
            }

            $reservation_meta['extra_equipments_html'] = $prices_array['extra_equipments_html'];
            $reservation_meta['total_equipments_price'] = $prices_array['total_equipments_price'];

            $reservation_meta['cost_per_additional_car'] = $prices_array['cost_per_additional_car'];
            $reservation_meta['additional_vehicles'] = $prices_array['additional_vehicles'];
            $reservation_meta['additional_vehicles_fee'] = $prices_array['additional_vehicles_fee'];

            $reservation_meta['taxes'] = $prices_array['taxes'];
            $reservation_meta['taxes_percent'] = $prices_array['taxes_percent'];
            $reservation_meta['security_deposit'] = $prices_array['security_deposit'];

            $reservation_meta['additional_guests_price'] = $prices_array['additional_guests_price'];
            $reservation_meta['additional_guests_total_price'] = $prices_array['additional_guests_total_price'];
            $reservation_meta['booking_has_weekend'] = $prices_array['booking_has_weekend'];
            $reservation_meta['booking_has_custom_pricing'] = $prices_array['booking_has_custom_pricing'];
            $reservation_meta['upfront'] = $upfront_payment;
            $reservation_meta['balance'] = $balance;
            $reservation_meta['total_extra_services'] = $prices_array['total_extra_services'];;
            $reservation_meta['total'] = $total_price;

            // Calculate Host Earning
            $total_amount = floatval($total_price);
            $services_fee = doubleval($prices_array['services_fee']);
            $occ_tax = floatval($prices_array['occ_tax_amount']);
            $state_tax = floatval($prices_array['total_state_tax']);

            $host_amount = $total_amount;
            $host_amount -= $services_fee;
            if (!empty($occ_tax) && $occ_tax > 0) {
                $host_amount -= $occ_tax;
            }
            if (!empty($state_tax) && $state_tax > 0) {
                $host_amount -= $state_tax;
            }

            $host_fee = floatval($host_amount * 0.15);
            $total_host_amount = $host_amount - $host_fee;

            if (!empty($occ_tax) && $occ_tax > 0) {
                $total_host_amount += $occ_tax;
            }
            if (!empty($state_tax) && $state_tax > 0) {
                $total_host_amount += $state_tax;
            }

            $total_earning_pending = $total_host_amount;

            $reservation = array(
                'post_title' => $title,
                'post_status' => 'publish',
                'post_type' => 'homey_reservation',
                'post_author' => $userID
            );
            $reservation_id = wp_insert_post($reservation);

            $reservation_update = array(
                'ID' => $reservation_id,
                'post_title' => $title . ' ' . $reservation_id
            );
            wp_update_post($reservation_update);

            update_post_meta($reservation_id, 'reservation_listing_id', $listing_id);
            update_post_meta($reservation_id, 'selected_card_id', $selected_card_id);
            update_post_meta($reservation_id, 'reservation_confirm_date_time', $date);
            update_post_meta($reservation_id, 'listing_owner', $listing_owner_id);
            update_post_meta($reservation_id, 'listing_renter', $userID);
            update_post_meta($reservation_id, 'reservation_checkin_hour', $check_in_hour);
            update_post_meta($reservation_id, 'reservation_checkout_hour', $check_out_hour);
            update_post_meta($reservation_id, 'reservation_guests', $guests);
            update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
            update_post_meta($reservation_id, 'reservation_status', 'under_review');
            update_post_meta($reservation_id, 'is_hourly', 'yes');
            update_post_meta($reservation_id, 'extra_options', $extra_options);
            update_post_meta($reservation_id, 'extra_equipments', $extra_equipments);

            update_post_meta($reservation_id, 'total_earning_pending', $total_earning_pending);

            update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
            update_post_meta($reservation_id, 'reservation_balance', $balance);
            update_post_meta($reservation_id, 'reservation_total', $total_price);

            $pending_dates_array = homey_get_booking_pending_hours($listing_id);
            update_post_meta($listing_id, 'reservation_pending_hours', $pending_dates_array);
            $message_link = homey_thread_link_after_reservation($reservation_id);

            // Save the booking request notification to the host
            $notification_title = 'New Booking Request: #' . $reservation_id;
            $notification_content = 'A new booking request has been received for reservation #' . $reservation_id;
            $notification_link = home_url('/reservations/?reservation_detail=' . $reservation_id);
            save_booking_notification($listing_owner_id, $notification_title, $notification_content, $notification_link);

            // Save the booking request notification to the guest
            // $notification_title = 'New Reservation Request: #' . $reservation_id;
            // $notification_content = 'A new reservation request has been made for reservation #' . $reservation_id;
            // save_booking_notification($userID, $notification_title, $notification_content);

            echo json_encode(
                array(
                    'success' => true,
                    'message' => $local['request_sent']
                )
            );


            // Send message on booking request
            $message = "Backyard Lease update: Reservation - " . $listing_title . " has a booking request!. Reservation ID: " . $reservation_id;
            send_booking_message($reservation_id, $userID, $listing_owner_id, $message);

            /*
            if (isset($current_user->user_email)) {
                $reservation_page = homey_get_template_link_dash('template/dashboard-reservations2.php');
                $reservation_detail_link = add_query_arg('reservation_detail', $reservation_id, $reservation_page);
                $email_args = array(
                    'guest_message' => $guest_message,
                    'message_link' => $message_link,
                    'reservation_detail_url' => $reservation_detail_link
                );

                homey_email_composer($current_user->user_email, 'new_reservation_sent', $email_args);
            }

            $email_args = array(
                'reservation_detail_url' => reservation_detail_link($reservation_id),
                'guest_message' => $guest_message,
                'message_link' => $message_link
            );

            if (!empty(trim($guest_message))) {
                do_action('homey_create_messages_thread', $guest_message, $reservation_id);
            }

            homey_email_composer($owner_email, 'new_reservation', $email_args);
            homey_email_composer($admin_email, 'admin_booked_reservation', $email_args); */

            // Send SMS to host
            $reservation_url = home_url('/reservations/?reservation_detail=' . $reservation_id);
            if (isset($notification_settings_host['sms']) && $notification_settings_host['sms']) {
                if (!empty($host_phone_number)) {
                    $host_message = 'BACKYARD LEASE: ' . $listing_title . ' has a booking request! ' . $guest_name . ' would like to book for ' . $booking_date . ' from ' . $booking_start_time . ' to ' . $booking_end_time . ". See details here\n" . $reservation_url;
                    homey_send_sms($host_phone_number, $host_message);
                }
            }

            // Send Email to host
            if (isset($notification_settings_host['email']) && $notification_settings_host['email']) {
                $user_email = get_the_author_meta('user_email', $listing_owner_id);
                $subject = $guest_name . ' just requested your Backyard Adventure!';
                $logo_url = wp_get_attachment_url(7179);
                $image_url = wp_get_attachment_url(7187);
                $calendar_url = wp_get_attachment_url(7191);
                $guests_url = wp_get_attachment_url(7192);
                $button_url = home_url('/reservations/?reservation_detail=' . $reservation_id);

                $message = '
              <div style="font-family: \'Oswald\', sans-serif;text-align: center; padding: 20px; margin: 0 auto; max-width: 400px;">
                  <!-- Image -->
                  <div style="margin-bottom: 20px;">
                      <img src="' . esc_url($logo_url) . '" width="100" height="100" alt="Coupon Image" style="max-width: 100%; height: auto;">
                  </div>
                  
                  <!-- Bold text in sky blue color -->
                  <p style="font-size: 14px; color: #0072ff; font-weight: 600; margin-top: 20px;">
                    ' . $guest_name . ' just requested your</br>Backyard Adventure!
                  </p>
                  
                   <div style="margin-top: 20px;">
                      <img src="' . esc_url($image_url) . '" alt="Coupon Image" style="max-width: 100%; height: auto;width: 100%;">
                  </div>

                  <div style="display: flex;gap: 20px;align-items: center;justify-content: center;margin-top: -65px;margin-bottom: 30px;">
                    <p style="font-size: 16px; color: #0072ff; font-weight: 600;">
                        Potential Mula
                    </p>
                    <p style="font-size: 24px; color: #0072ff; font-weight: 800;">
                        ' . homey_formatted_price($total_price) . '
                    </p>
                  </div>
                  
                  <div style="display: inline-block;padding: 15px;background: white;box-shadow: rgba(0, 114, 255, 0.5) 2px 2px 4px;text-align: left;margin: 20px;">

                    <p style="font-size: 14px;font-weight:600;color:#262626">
                      Booking Details for " ' . $listing_title . '
                    </p>
                  
                    <p style="font-size: 14px;font-weight:600;color:#262626;margin-top: 10px;">
                      Booking # ' . $reservation_id . '
                    </p>

                    <div style="padding:15px;margin-top: 10px;">
                        <div style="display:flex;gap:10px">
                            <img src="' . esc_url($calendar_url) . '" alt="Coupon Image" style="max-width: 100%;height: 40px;">
                            <p style="font-size: 14px; color: #0072ff; font-weight: 600;">
                                Let the adventure begin</br>
                                <span style="font-size: 10px; color: #262626; font-weight: 500;">
                                    ' . $booking_date . '</br>
                                    ' . $booking_start_time . ' - ' . $booking_end_time . '
                                </span>
                            </p>
                        </div>
                        <div style="display: flex;gap: 10px;margin-top: 20px;">
                            <img src="' . esc_url($guests_url) . '" alt="Coupon Image" style="max-width: 100%;height: 40px;">
                            <p style="font-size: 14px; color: #0072ff; font-weight: 600;">
                                Who is coming</br>
                                <span style="font-size: 10px; color: #262626; font-weight: 500;">
                                    ' . $guests . ' Guests
                                </span>
                            </p>
                        </div>
                    </div>

                    <p style="font-size: 10px;font-weight:600;color:#262626;margin-top: 20px;margin-bottom: 20px;">
                      Keep the ball in the backyard</br>
                      <span style="font-weight:500;">
                      * Please do not use this platform to post advertisements or sell anything other than what has been designated as a Backyard Lease or Service. You may not use the Backyard lease platform for free advertising or to redirect users to another site or platform.
                      </span>
                    </p>
                  
                  </div>

                  <!-- Button -->
                  <div style="background-color: #f5f5f5;padding: 20px;">
                    <a href="' . $button_url . '" style="display: inline-block;padding: 5px 30px;font-size: 14px;color: white;background-color: #000080;text-decoration: none;font-weight: 600;">
                      View request
                    </a>
                  </div>
              </div>';

                $headers = array('Content-Type: text/html; charset=UTF-8');
                wp_mail($user_email, $subject, $message, $headers);
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

add_action('wp_ajax_nopriv_homey_pay_for_reservation', 'homey_pay_for_reservation');
add_action('wp_ajax_homey_pay_for_reservation', 'homey_pay_for_reservation');
if (!function_exists('homey_pay_for_reservation')) {
    function homey_pay_for_reservation()
    {
        global $current_user;
        $admin_email = get_option('admin_email');

        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $username = get_the_author_meta('user_login', $userID);
        $display_name_public = get_the_author_meta('display_name_public', $userID);
        $guest_name = empty($display_name_public) ? $username : $display_name_public;
        $guest_phone_number = get_the_author_meta('homey_phone_number', $userID);
        $saved_cards = get_user_meta($userID, 'saved_stripe_cards', true);
        $saved_cards = $saved_cards ? json_decode($saved_cards, true) : [];
        $author = homey_get_author_by_id('70', '70', 'img-circle media-object avatar', $userID);
        $doc_verified = $author['doc_verified'];
        $verified = false;
        if ($doc_verified) {
            $verified = true;
        }

        $local = homey_get_localization();

        //check security
        //        $nonce = $_REQUEST['security'];
        //        if ( ! wp_verify_nonce( $nonce, 'reservation-security-nonce' ) ) {
        //
        //            echo json_encode(
        //                array(
        //                    'success' => false,
        //                    'message' => $local['security_check_text']
        //                )
        //            );
        //            wp_die();
        //        }


        $allowded_html = array();
        $reservation_meta = array();

        $listing_id = intval($_POST['listing_id']);
        $listing_title = get_the_title($listing_id);
        $listing_owner_id = get_post_field('post_author', $listing_id);
        $host_phone_number = get_the_author_meta('homey_phone_number', $listing_owner_id);
        $notification_settings_host = get_user_meta($listing_owner_id, 'notification_settings', true);
        $check_in_date = wp_kses($_POST['check_in_date'], $allowded_html);
        $booking_date = format_check_in_date($check_in_date);
        $start_hour = wp_kses($_POST['start_hour'], $allowded_html);
        $booking_start_time = date(homey_time_format(), strtotime($start_hour));
        $end_hour = wp_kses($_POST['end_hour'], $allowded_html);
        $booking_end_time = date(homey_time_format(), strtotime($end_hour));
        $guests = intval($_POST['guests']);

        $have_guided_service = get_field('have_guided_service', $listing_id);

        $choose_guided_service = $_POST['choose_guided_service'];
        $accommodation_number = intval($_POST['accommodation_number']);
        $guests_participating = intval($_POST['guests_participating']);
        $extra_participants = intval($_POST['extra_participants']);
        $guests_gears = $_POST['guests_gears'];
        $guests_ages = $_POST['guests_ages'];
        $health_conditions = $_POST['health_conditions'];
        $experience_level = $_POST['experience_level'];
        $first_timers = $_POST['first_timers'];

        $current_user_id = get_current_user_id();
        $coupon_code = sanitize_text_field($_POST['coupon_code']);

        $selected_card_id = $_POST['selected_card_id'];
        $extra_options = $_POST['extra_options'];
        $extra_equipments = $_POST['extra_equipments'];
        $additional_vehicles = $_POST['additional_vehicles'];
        $guest_message = stripslashes($_POST['guest_message']);
        $title = $local['reservation_text'];

        $check_in_hour = $check_in_date . ' ' . $start_hour;
        $check_out_hour = $check_in_date . ' ' . $end_hour;

        $owner = homey_usermeta($listing_owner_id);
        $owner_email = $owner['email'];
        $booking_hide_fields = homey_option('booking_hide_fields');

        $no_login_needed_for_booking = homey_option('no_login_needed_for_booking');

        $date = date('Y-m-d G:i:s', current_time('timestamp', 0));

        if ($current_user->ID == 0 && $no_login_needed_for_booking == "yes" && isset($_REQUEST['new_reser_request_user_email'])) {
            $email = trim($_REQUEST['new_reser_request_user_email']);
            if (empty($email)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => esc_html__('Enter email address', 'homey')
                    )
                );
                wp_die();
            }


            $user = get_user_by('email', $email);

            if (isset($user->ID)) {
                add_filter('authenticate', 'for_reservation_nop_auto_login', 3, 10);
                for_reservation_nop_auto_login($user);
            } else { //create user from email
                $user_login = $email;
                $user_email = $email;
                $user_pass = wp_generate_password(8, false);
                $userdata = compact('user_login', 'user_email', 'user_pass');
                $new_user_id = wp_insert_user($userdata);

                if ($new_user_id > 0) {
                    homey_wp_new_user_notification($new_user_id, $user_pass);
                }

                update_user_meta($new_user_id, 'viaphp', 1);

                // log in automatically
                if (!is_user_logged_in()) {
                    $user = get_user_by('email', $email);

                    add_filter('authenticate', 'for_reservation_nop_auto_login', 3, 10);
                    for_reservation_nop_auto_login($user);
                }
            }
        }

        $current_user = wp_get_current_user();
        $userID = $current_user->ID;


        if ($no_login_needed_for_booking == 'no' && (!is_user_logged_in() || $userID === 0)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['login_for_reservation']
                )
            );
            wp_die();
        }

        if ($no_login_needed_for_booking == 'no' && $userID == $listing_owner_id) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['own_listing_error']
                )
            );
            wp_die();
        }

        if (!homey_is_renter()) {
            $current_user = wp_get_current_user();
            $current_user->set_role('homey_renter');
            echo json_encode(
                array(
                    'success' => false,
                    'message' => 'Trying to book a listing? No problem, Switching over to Adventurer.',
                    'reload' => true
                )
            );
            wp_die();
        }

        if (empty($saved_cards) && $verified == false) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => 'We’re sorry, you must complete your profile/verification process and add atleast one payment method before requesting a booking.'
                )
            );
            wp_die();
        }

        if (empty($selected_card_id)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => 'Please select payemnt method.'
                )
            );
            wp_die();
        }

        $coupon_id = homey_validate_coupon($coupon_code, $listing_id, $current_user_id);

        $check_availability = check_hourly_booking_availability($check_in_date, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $listing_id, $guests);
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if ($is_available) {
            $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests, $extra_options, $accommodation_number, $guests_participating, $extra_participants, $choose_guided_service, $extra_equipments, $additional_vehicles, $coupon_id);

            $reservation_meta['no_of_hours'] = $prices_array['hours_count'];
            $reservation_meta['additional_guests'] = $prices_array['additional_guests'];

            $upfront_payment = $prices_array['upfront_payment'];
            $balance = $prices_array['balance'];
            $total_price = $prices_array['total_price'];

            $reservation_meta['check_in_date'] = $check_in_date;
            $reservation_meta['check_in_hour'] = $check_in_hour;
            $reservation_meta['check_out_hour'] = $check_out_hour;
            $reservation_meta['start_hour'] = $start_hour;
            $reservation_meta['end_hour'] = $end_hour;
            $reservation_meta['guests'] = $guests;
            $reservation_meta['listing_id'] = $listing_id;

            $reservation_meta['price_per_hour'] = $prices_array['price_per_hour'];
            $reservation_meta['hours_total_price'] = $prices_array['hours_total_price']; //$hours_total_price;

            $reservation_meta['cleaning_fee'] = $prices_array['cleaning_fee'];
            $reservation_meta['accomodation_fee'] = $prices_array['accomodation_fee'];
            $reservation_meta['total_accomodation_fee'] = $prices_array['total_accomodation_fee'];
            $reservation_meta['accommodation_number'] = $accommodation_number;

            $reservation_meta['guests_participating'] = $guests_participating;
            $reservation_meta['extra_participants'] = $extra_participants;
            $reservation_meta['choose_guided_service'] = $choose_guided_service;
            $reservation_meta['guests_gears'] = $guests_gears;
            $reservation_meta['guests_ages'] = $guests_ages;
            $reservation_meta['health_conditions'] = $health_conditions;
            $reservation_meta['experience_level'] = $experience_level;
            $reservation_meta['first_timers'] = $first_timers;
            $reservation_meta['guided_fee'] = $prices_array['guided_fee'];
            $reservation_meta['total_guest_hourly'] = $prices_array['total_guest_hourly'];
            $reservation_meta['total_guest_fixed'] = $prices_array['total_guest_fixed'];
            $reservation_meta['total_group_hourly'] = $prices_array['total_group_hourly'];
            $reservation_meta['total_group_fixed'] = $prices_array['total_group_fixed'];
            $reservation_meta['total_flat_hourly'] = $prices_array['total_flat_hourly'];
            $reservation_meta['total_flat_fixed'] = $prices_array['total_flat_fixed'];
            $reservation_meta['gears_price'] = $prices_array['gears_price'];
            $reservation_meta['total_gears_price'] = $prices_array['total_gears_price'];
            $reservation_meta['non_participants_price'] = $prices_array['non_participants_price'];
            $reservation_meta['total_non_participants_price'] = $prices_array['total_non_participants_price'];
            $reservation_meta['total_participants'] = $prices_array['total_participants'];
            $reservation_meta['flag'] = $prices_array['flag'];
            $reservation_meta['occ_tax_amount'] = $prices_array['occ_tax_amount'];
            $reservation_meta['total_state_tax'] = $prices_array['total_state_tax'];

            $reservation_meta['city_fee'] = $prices_array['city_fee'];
            $reservation_meta['services_fee'] = $prices_array['services_fee'];

            $reservation_meta['coupon_discount'] = $prices_array['coupon_discount'];
            if (!empty($prices_array['coupon_discount']) && $prices_array['coupon_discount'] != 0) {
                $guests = get_post_meta($coupon_id, 'coupon_guests_ids', true);
                if ($guests && in_array($current_user_id, $guests)) {
                    $updated_guests = array_diff($guests, array($current_user_id));
                    update_post_meta($coupon_id, 'coupon_guests_ids', $updated_guests);
                }
            }

            $reservation_meta['extra_equipments_html'] = $prices_array['extra_equipments_html'];
            $reservation_meta['total_equipments_price'] = $prices_array['total_equipments_price'];

            $reservation_meta['cost_per_additional_car'] = $prices_array['cost_per_additional_car'];
            $reservation_meta['additional_vehicles'] = $prices_array['additional_vehicles'];
            $reservation_meta['additional_vehicles_fee'] = $prices_array['additional_vehicles_fee'];

            $reservation_meta['taxes'] = $prices_array['taxes'];
            $reservation_meta['taxes_percent'] = $prices_array['taxes_percent'];
            $reservation_meta['security_deposit'] = $prices_array['security_deposit'];

            $reservation_meta['additional_guests_price'] = $prices_array['additional_guests_price'];
            $reservation_meta['additional_guests_total_price'] = $prices_array['additional_guests_total_price'];
            $reservation_meta['booking_has_weekend'] = $prices_array['booking_has_weekend'];
            $reservation_meta['booking_has_custom_pricing'] = $prices_array['booking_has_custom_pricing'];
            $reservation_meta['upfront'] = $upfront_payment;
            $reservation_meta['balance'] = $balance;
            $reservation_meta['total'] = $total_price;

            $total_amount = floatval($total_price);
            $total_amount_cents = intval(round($total_amount * 100));

            $services_fee = doubleval($prices_array['services_fee']);

            $occ_tax = floatval($prices_array['occ_tax_amount']);
            $state_tax = floatval($prices_array['total_state_tax']);

            $host_stripe_account_id = get_user_meta($listing_owner_id, 'stripe_account_id', true);
            $card_id = $selected_card_id;
            $customer_id = get_user_meta($userID, 'stripe_customer_id', true);
            $total_earning_array = get_user_meta($listing_owner_id, 'host_total_earning', true);

            $total_earning = 0.0;
            if (is_array($total_earning_array) && isset($total_earning_array[0])) {
                $total_earning = floatval($total_earning_array[0]);
            } elseif (is_numeric($total_earning_array)) {
                $total_earning = floatval($total_earning_array);
            }

            $host_amount = $total_amount;
            $host_amount -= $services_fee;
            if (!empty($occ_tax) && $occ_tax > 0) {
                $host_amount -= $occ_tax;
            }
            if (!empty($state_tax) && $state_tax > 0) {
                $host_amount -= $state_tax;
            }

            $host_fee = intval(round($host_amount * 0.15));
            $total_host_amount = $host_amount - $host_fee;

            // Add back taxes if they were subtracted
            if (!empty($occ_tax) && $occ_tax > 0) {
                $total_host_amount += $occ_tax;
            }
            if (!empty($state_tax) && $state_tax > 0) {
                $total_host_amount += $state_tax;
            }

            //$total_earning += $total_host_amount;
            $total_host_amount_cents = intval(round($total_host_amount * 100));

            $stripe_secret_key = homey_option('stripe_secret_key');
            require_once(HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php');
            \Stripe\Stripe::setApiKey($stripe_secret_key);

            try {
                $payment_intent = \Stripe\PaymentIntent::create([
                    'amount' => $total_amount_cents,
                    'currency' => 'usd',
                    'payment_method' => $card_id,
                    'confirm' => true,
                    'customer' => $customer_id,
                    'description' => 'Booking payment for reservation #' . $reservation_id,
                ]);
                //print_r($payment_intent);

                // Create a Transfer to move funds to the host's account
                $payment_transfer = \Stripe\Transfer::create([
                    'amount' => $total_host_amount_cents,
                    'currency' => 'usd',
                    'destination' => $host_stripe_account_id,
                    'source_transaction' => $payment_intent->charges->data[0]->id,
                    'description' => 'Host payout for reservation #' . $reservation_id,
                ]);
                //print_r($payment_transfer);
                //wp_die();

                $transferred_amount_cents = $payment_transfer->amount;
                $transferred_amount = $transferred_amount_cents / 100;
                $total_earning += $transferred_amount;
            } catch (\Stripe\Exception\CardException $e) {
                // Card was declined
                echo json_encode(array('success' => false, 'message' => $e->getError()->message, 'position' => 1));
                wp_die();
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                // Invalid parameters were supplied to Stripe's API
                echo json_encode(array('success' => false, 'message' => $e->getError()->message, 'position' => 2));
                wp_die();
            } catch (\Stripe\Exception\AuthenticationException $e) {
                // Authentication with Stripe's API failed
                echo json_encode(array('success' => false, 'message' => $e->getError()->message, 'position' => 3));
                wp_die();
            } catch (\Stripe\Exception\ApiConnectionException $e) {
                // Network communication with Stripe failed
                echo json_encode(array('success' => false, 'message' => $e->getError()->message, 'position' => 4));
                wp_die();
            } catch (\Stripe\Exception\ApiErrorException $e) {
                // Generic error for other Stripe-related issues
                echo json_encode(array('success' => false, 'message' => $e->getError()->message, 'position' => 5));
                wp_die();
            } catch (Exception $e) {
                // Other errors not specific to Stripe
                echo json_encode(array('success' => false, 'message' => 'Error processing payment', 'position' => 6));
                wp_die();
            }

            $reservation = array(
                'post_title' => $title,
                'post_status' => 'publish',
                'post_type' => 'homey_reservation',
                'post_author' => $userID
            );
            $reservation_id = wp_insert_post($reservation);

            $reservation_update = array(
                'ID' => $reservation_id,
                'post_title' => $title . ' ' . $reservation_id
            );
            wp_update_post($reservation_update);

            update_post_meta($reservation_id, 'reservation_listing_id', $listing_id);
            update_post_meta($reservation_id, 'selected_card_id', $selected_card_id);
            update_post_meta($reservation_id, 'reservation_confirm_date_time', $date);
            update_post_meta($reservation_id, 'listing_owner', $listing_owner_id);
            update_post_meta($reservation_id, 'listing_renter', $userID);
            update_post_meta($reservation_id, 'reservation_checkin_hour', $check_in_hour);
            update_post_meta($reservation_id, 'reservation_checkout_hour', $check_out_hour);
            update_post_meta($reservation_id, 'reservation_guests', $guests);
            update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
            update_post_meta($reservation_id, 'reservation_status', 'booked');
            update_post_meta($reservation_id, 'is_hourly', 'yes');
            update_post_meta($reservation_id, 'extra_options', $extra_options);
            update_post_meta($reservation_id, 'extra_equipments', $extra_equipments);

            // Set host earning transfered
            update_post_meta($reservation_id, 'host_earning_status', 'transfered');
            update_post_meta($reservation_id, 'host_transferred_amount', $transferred_amount);
            update_user_meta($listing_owner_id, 'host_total_earning', $total_earning);

            update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
            update_post_meta($reservation_id, 'reservation_balance', $balance);
            update_post_meta($reservation_id, 'reservation_total', $total_price);

            $pending_dates_array = homey_get_booking_pending_hours($listing_id);
            update_post_meta($listing_id, 'reservation_pending_hours', $pending_dates_array);
            $message_link = homey_thread_link_after_reservation($reservation_id);


            echo json_encode(
                array(
                    'success' => true,
                    'message' => 'Reservation booked successfully',
                    'redirect_url' => home_url('/reservations/'),
                )
            );

            // Send SMS to host
            $reservation_url = home_url('/reservations/?reservation_detail=' . $reservation_id);
            if (isset($notification_settings_host['sms']) && $notification_settings_host['sms']) {
                if (!empty($host_phone_number)) {
                    $host_message = 'BACKYARD LEASE: ' . $listing_title . ' is now booked! ' . $guest_name . ' is now confirmed for ' . $booking_date . ' from ' . $booking_start_time . ' to ' . $booking_end_time . ". See details here\n" . $reservation_url;
                    homey_send_sms($host_phone_number, $host_message);
                }
            }

            // Send Email to host
            if (isset($notification_settings_host['email']) && $notification_settings_host['email']) {
                $user_email = get_the_author_meta('user_email', $listing_owner_id);
                $subject = $guest_name . ' just reserved your Backyard Adventure!';
                $logo_url = wp_get_attachment_url(7179);
                $image_url = wp_get_attachment_url(7187);
                $calendar_url = wp_get_attachment_url(7191);
                $guests_url = wp_get_attachment_url(7192);
                $button_url = home_url('/reservations/?reservation_detail=' . $reservation_id);

                $message = '
              <div style="font-family: \'Oswald\', sans-serif;text-align: center; padding: 20px; margin: 0 auto; max-width: 400px;">
                  <!-- Image -->
                  <div style="margin-bottom: 20px;">
                      <img src="' . esc_url($logo_url) . '" width="100" height="100" alt="Coupon Image" style="max-width: 100%; height: auto;">
                  </div>
                  
                  <!-- Bold text in sky blue color -->
                  <p style="font-size: 14px; color: #0072ff; font-weight: 600; margin-top: 20px;">
                    ' . $guest_name . ' just reserved your</br>Backyard Adventure!
                  </p>
                  
                   <div style="margin-top: 20px;">
                      <img src="' . esc_url($image_url) . '" alt="Coupon Image" style="max-width: 100%; height: auto;width: 100%;">
                  </div>

                  <div style="display: flex;gap: 20px;align-items: center;justify-content: center;margin-top: -65px;margin-bottom: 30px;">
                    <p style="font-size: 16px; color: #0072ff; font-weight: 600;">
                        Your Mula
                    </p>
                    <p style="font-size: 24px; color: #0072ff; font-weight: 800;">
                        ' . homey_formatted_price($total_price) . '
                    </p>
                  </div>
                  
                  <div style="display: inline-block;padding: 15px;background: white;box-shadow: rgba(0, 114, 255, 0.5) 2px 2px 4px;text-align: left;margin: 20px;">

                    <p style="font-size: 14px;font-weight:600;color:#262626">
                      Booking Details for " ' . $listing_title . '
                    </p>
                  
                    <p style="font-size: 14px;font-weight:600;color:#262626;margin-top: 10px;">
                      Booking # ' . $reservation_id . '
                    </p>

                    <div style="padding:15px;margin-top: 10px;">
                        <div style="display:flex;gap:10px">
                            <img src="' . esc_url($calendar_url) . '" alt="Coupon Image" style="max-width: 100%;height: 40px;">
                            <p style="font-size: 14px; color: #0072ff; font-weight: 600;">
                                Let the adventure begin</br>
                                <span style="font-size: 10px; color: #262626; font-weight: 500;">
                                    ' . $booking_date . '</br>
                                    ' . $booking_start_time . ' - ' . $booking_end_time . '
                                </span>
                            </p>
                        </div>
                        <div style="display: flex;gap: 10px;margin-top: 20px;">
                            <img src="' . esc_url($guests_url) . '" alt="Coupon Image" style="max-width: 100%;height: 40px;">
                            <p style="font-size: 14px; color: #0072ff; font-weight: 600;">
                                Who is coming</br>
                                <span style="font-size: 10px; color: #262626; font-weight: 500;">
                                    ' . $guests . ' Guests
                                </span>
                            </p>
                        </div>
                    </div>

                    <p style="font-size: 10px;font-weight:600;color:#262626;margin-top: 20px;margin-bottom: 20px;">
                      Keep the ball in the backyard</br>
                      <span style="font-weight:500;">
                      * Please do not use this platform to post advertisements or sell anything other than what has been designated as a Backyard Lease or Service. You may not use the Backyard lease platform for free advertising or to redirect users to another site or platform.
                      </span>
                    </p>
                  
                  </div>

                  <!-- Button -->
                  <div style="background-color: #f5f5f5;padding: 20px;">
                    <a href="' . $button_url . '" style="display: inline-block;padding: 5px 30px;font-size: 14px;color: white;background-color: #000080;text-decoration: none;font-weight: 600;">
                      View reservation
                    </a>
                  </div>
              </div>';

                $headers = array('Content-Type: text/html; charset=UTF-8');
                wp_mail($user_email, $subject, $message, $headers);
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

if (!function_exists('homey_add_hourly_instance_booking')) {
    function homey_add_hourly_instance_booking($listing_id, $check_in_date, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $guests, $renter_message, $extra_options, $user_id = null)
    {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;

        if (!empty($user_id)) {
            $userID = $user_id;
        }

        $local = homey_get_localization();
        $allowded_html = array();
        $reservation_meta = array();

        $listing_owner_id = get_post_field('post_author', $listing_id);
        $title = $local['reservation_text'];

        $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests, $extra_options);

        $reservation_meta['no_of_hours'] = $prices_array['hours_count'];
        $reservation_meta['additional_guests'] = $prices_array['additional_guests'];

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        $reservation_meta['check_in_date'] = $check_in_date;
        $reservation_meta['check_in_hour'] = $check_in_hour;
        $reservation_meta['check_out_hour'] = $check_out_hour;
        $reservation_meta['start_hour'] = $start_hour;
        $reservation_meta['end_hour'] = $end_hour;
        $reservation_meta['guests'] = $guests;
        $reservation_meta['listing_id'] = $listing_id;

        $reservation_meta['price_per_hour'] = $prices_array['price_per_hour'];
        $reservation_meta['hours_total_price'] = $prices_array['hours_total_price']; //$hours_total_price;

        $reservation_meta['cleaning_fee'] = $prices_array['cleaning_fee'];
        $reservation_meta['city_fee'] = $prices_array['city_fee'];
        $reservation_meta['services_fee'] = $prices_array['services_fee'];

        $reservation_meta['taxes'] = $prices_array['taxes'];
        $reservation_meta['taxes_percent'] = $prices_array['taxes_percent'];
        $reservation_meta['security_deposit'] = $prices_array['security_deposit'];

        $reservation_meta['additional_guests_price'] = $prices_array['additional_guests_price'];
        $reservation_meta['additional_guests_total_price'] = $prices_array['additional_guests_total_price'];
        $reservation_meta['booking_has_weekend'] = $prices_array['booking_has_weekend'];
        $reservation_meta['booking_has_custom_pricing'] = $prices_array['booking_has_custom_pricing'];
        $reservation_meta['upfront'] = $upfront_payment;
        $reservation_meta['balance'] = $balance;
        $reservation_meta['total'] = $total_price;

        $reservation = array(
            'post_title' => $title,
            'post_status' => 'publish',
            'post_type' => 'homey_reservation',
            'post_author' => $userID
        );
        $reservation_id = wp_insert_post($reservation);

        $reservation_update = array(
            'ID' => $reservation_id,
            'post_title' => $title . ' ' . $reservation_id
        );
        wp_update_post($reservation_update);

        update_post_meta($reservation_id, 'reservation_listing_id', $listing_id);
        update_post_meta($reservation_id, 'listing_owner', $listing_owner_id);
        update_post_meta($reservation_id, 'listing_renter', $userID);
        update_post_meta($reservation_id, 'reservation_checkin_hour', $check_in_hour);
        update_post_meta($reservation_id, 'reservation_checkout_hour', $check_out_hour);
        update_post_meta($reservation_id, 'reservation_guests', $guests);
        update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
        update_post_meta($reservation_id, 'reservation_status', 'booked');
        update_post_meta($reservation_id, 'is_hourly', 'yes');

        update_post_meta($reservation_id, 'extra_options', $extra_options);

        update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
        update_post_meta($reservation_id, 'reservation_balance', $balance);
        update_post_meta($reservation_id, 'reservation_total', $total_price);

        //Book dates
        $booked_days_array = homey_make_hours_booked($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_booked_hours', $booked_days_array);

        do_action('homey_create_messages_thread', $renter_message, $reservation_id);

        return $reservation_id;
    }
}

if (!function_exists('check_hourly_booking_availability')) {
    function check_hourly_booking_availability($check_in_date, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $listing_id, $guests)
    {
        $return_array = array();
        $local = homey_get_localization();
        $booking_proceed = true;

        $min_book_hours = get_post_meta($listing_id, 'homey_min_book_hours', true);

        $booking_hide_fields = homey_option('booking_hide_fields');

        $homey_allow_additional_guests = get_post_meta($listing_id, 'homey_allow_additional_guests', true);
        $allowed_guests = get_post_meta($listing_id, 'homey_guests', true);


        if (!empty($allowed_guests)) {
            if (($homey_allow_additional_guests != 'yes') && ($guests > $allowed_guests)) {
                $return_array['success'] = false;
                $return_array['message'] = $local['guest_allowed'] . ' ' . $allowed_guests;
                return $return_array;
            }
        }

        if (strtotime($check_out_hour) <= strtotime($check_in_hour)) {
            $booking_proceed = false;
        }

        if (empty($check_in_date) && empty($check_in_hour) && empty($check_out_hour)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['fill_all_fields'];
            return $return_array;
        }

        if (empty($check_in_date)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['choose_checkin'];
            return $return_array;
        }

        if (empty($check_in_hour) || empty($start_hour)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['choose_start_hour'];
            return $return_array;
        }

        if (empty($check_out_hour) || empty($end_hour)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['choose_end_hour'];
            return $return_array;
        }


        $time_difference = abs(strtotime($check_in_hour) - strtotime($check_out_hour));
        $hours_count = $time_difference / 3600;
        $hours_count = floatval($hours_count);

        if ($hours_count < $min_book_hours) {
            $return_array['success'] = false;
            $return_array['message'] = $local['min_book_hours_error'] . ' ' . $min_book_hours;
            return $return_array;
        }

        if (empty($guests) && $booking_hide_fields['guests'] != 1) {
            $return_array['success'] = false;
            $return_array['message'] = $local['choose_guests'];
            return $return_array;
        }

        if (!$booking_proceed) {
            $return_array['success'] = false;
            $return_array['message'] = $local['ins_hourly_book_proceed'];
            return $return_array;
        }

        $reservation_booked_array = get_post_meta($listing_id, 'reservation_booked_hours', true);
        if (empty($reservation_booked_array)) {
            $reservation_booked_array = homey_get_booked_hours($listing_id);
        }

        $reservation_pending_array = get_post_meta($listing_id, 'reservation_pending_hours', true);
        if (empty($reservation_pending_array)) {
            $reservation_pending_array = homey_get_booking_pending_hours($listing_id);
        }

        $check_in_hour = new DateTime($check_in_hour);
        $check_in_hour_unix = $check_in_hour->getTimestamp();

        $check_out_hour = new DateTime($check_out_hour);
        $check_out_hour->modify('-30 minutes');
        $check_out_hour_unix = $check_out_hour->getTimestamp();

        while ($check_in_hour_unix <= $check_out_hour_unix) {

            //echo $start_hour_unix.' ===== <br/>';
            if (array_key_exists($check_in_hour_unix, $reservation_booked_array) || array_key_exists($check_in_hour_unix, $reservation_pending_array)) {

                $return_array['success'] = false;
                $return_array['message'] = $local['hours_not_available'];
                if (homey_is_instance_page()) {
                    $return_array['message'] = $local['hour_ins_unavailable'];
                }
                return $return_array;
            }
            $check_in_hour->modify('+30 minutes');
            $check_in_hour_unix = $check_in_hour->getTimestamp();
        }

        //dates are available
        $return_array['success'] = true;
        $return_array['message'] = $local['hours_available'];
        return $return_array;
    }
}

add_action('wp_ajax_nopriv_check_booking_availability_on_hour_change', 'check_booking_availability_on_hour_change');
add_action('wp_ajax_check_booking_availability_on_hour_change', 'check_booking_availability_on_hour_change');
if (!function_exists('check_booking_availability_on_hour_change')) {
    function check_booking_availability_on_hour_change()
    {
        $local = homey_get_localization();
        $allowded_html = array();
        $booking_proceed = true;

        $listing_id = intval($_POST['listing_id']);
        $check_in_date = wp_kses($_POST['check_in_date'], $allowded_html);
        $start_hour = wp_kses($_POST['start_hour'], $allowded_html);
        $end_hour = wp_kses($_POST['end_hour'], $allowded_html);

        $check_in_hour = $check_in_date . ' ' . $start_hour;
        $check_out_hour = $check_in_date . ' ' . $end_hour;

        $min_book_hours = get_post_meta($listing_id, 'homey_min_book_hours', true);
        $time_between_bookings = get_field('how_much_time', $listing_id);

        if (empty($check_in_date)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['choose_checkin']
                )
            );
            wp_die();
        }
        if (empty($start_hour)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['choose_start_hour']
                )
            );
            wp_die();
        }

        if (empty($end_hour)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['choose_end_hour']
                )
            );
            wp_die();
        }

        if (strtotime($check_out_hour) <= strtotime($check_in_hour)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['ins_hourly_book_proceed']
                )
            );
            wp_die();
        }

        $time_difference = abs(strtotime($check_in_hour) - strtotime($check_out_hour));
        $hours_count = $time_difference / 3600;
        $hours_count = floatval($hours_count);

        if ($hours_count < $min_book_hours) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['min_book_hours_error'] . ' ' . $min_book_hours
                )
            );
            wp_die();
        }

        $reservation_booked_array = get_post_meta($listing_id, 'reservation_booked_hours', true);
        if (empty($reservation_booked_array)) {
            $reservation_booked_array = homey_get_booked_hours($listing_id);
        }

        $reservation_pending_array = get_post_meta($listing_id, 'reservation_pending_hours', true);
        if (empty($reservation_pending_array)) {
            $reservation_pending_array = homey_get_booking_pending_hours($listing_id);
        }

        $check_in_hour_obj = new DateTime($check_in_hour);
        $check_in_hour_unix = $check_in_hour_obj->getTimestamp();

        $check_out_hour_obj = new DateTime($check_out_hour);
        $check_out_hour_obj->modify('-30 minutes');
        $check_out_hour_unix = $check_out_hour_obj->getTimestamp();

        // Check if the selected time conflicts with existing bookings considering the buffer time
        foreach ($reservation_booked_array as $booked_time => $value) {
            $booked_end_time = $booked_time + (30 * 60); // Assuming each booking slot is 30 minutes

            // Add buffer time after each booking
            $booked_end_time_with_buffer = $booked_end_time + ($time_between_bookings * 60);

            if (($check_in_hour_unix >= $booked_time && $check_in_hour_unix < $booked_end_time_with_buffer) ||
                ($check_out_hour_unix > $booked_time && $check_out_hour_unix <= $booked_end_time_with_buffer) ||
                ($check_in_hour_unix <= $booked_time && $check_out_hour_unix >= $booked_end_time_with_buffer)
            ) {

                echo json_encode(
                    array(
                        'success' => false,
                        'message' => $local['hours_not_available']
                    )
                );
                wp_die();
            }
        }

        // Also check pending reservations
        foreach ($reservation_pending_array as $pending_time => $value) {
            $pending_end_time = $pending_time + (30 * 60); // Assuming each booking slot is 30 minutes

            // Add buffer time after each booking
            $pending_end_time_with_buffer = $pending_end_time + ($time_between_bookings * 60);

            if (($check_in_hour_unix >= $pending_time && $check_in_hour_unix < $pending_end_time_with_buffer) ||
                ($check_out_hour_unix > $pending_time && $check_out_hour_unix <= $pending_end_time_with_buffer) ||
                ($check_in_hour_unix <= $pending_time && $check_out_hour_unix >= $pending_end_time_with_buffer)
            ) {

                echo json_encode(
                    array(
                        'success' => false,
                        'message' => $local['hours_not_available']
                    )
                );
                wp_die();
            }
        }

        echo json_encode(
            array(
                'success' => true,
                'message' => $local['hours_available']
            )
        );
        wp_die();
    }
}


if (!function_exists('homey_get_booked_hours')) {
    function homey_get_booked_hours($listing_id)
    {
        $now = time();
        //$daysAgo = $now-3*24*60*60;
        $daysAgo = $now - 1 * 24 * 60 * 60;

        $args = array(
            'post_type' => 'homey_reservation',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'reservation_listing_id',
                    'value' => $listing_id,
                    'type' => 'NUMERIC',
                    'compare' => '='
                ),
                array(
                    'key' => 'reservation_status',
                    'value' => 'booked',
                    'type' => 'CHAR',
                    'compare' => '='
                )
            )
        );

        $booked_hours_array = get_post_meta($listing_id, 'reservation_booked_hours', true);

        if (!is_array($booked_hours_array) || empty($booked_hours_array)) {
            $booked_hours_array = array();
        }

        $wpQry = new WP_Query($args);

        if ($wpQry->have_posts()) {

            while ($wpQry->have_posts()):
                $wpQry->the_post();

                $resID = get_the_ID();

                $check_in_date = get_post_meta($resID, 'reservation_checkin_hour', true);
                $check_out_date = get_post_meta($resID, 'reservation_checkout_hour', true);

                $unix_time_start = strtotime($check_in_date);

                if ($unix_time_start > $daysAgo) {

                    $check_in = new DateTime($check_in_date);
                    $check_in_unix = $check_in->getTimestamp();
                    $check_out = new DateTime($check_out_date);
                    $check_out_unix = $check_out->getTimestamp();


                    $booked_hours_array[$check_in_unix] = $resID;

                    $check_in_unix = $check_in->getTimestamp();

                    while ($check_in_unix < $check_out_unix) {

                        $booked_hours_array[$check_in_unix] = $resID;

                        //$check_in->modify('+1 hour');
                        $check_in->modify('+30 minutes');
                        $check_in_unix = $check_in->getTimestamp();
                    }
                }
            endwhile;
            wp_reset_postdata();
        }

        return $booked_hours_array;
    }
}


if (!function_exists('homey_get_booking_pending_hours')) {
    function homey_get_booking_pending_hours($listing_id)
    {
        $now = time();
        //$daysAgo = $now-3*24*60*60;
        $daysAgo = $now - 1 * 24 * 60 * 60;

        $args = array(
            'post_type' => 'homey_reservation',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'reservation_listing_id',
                    'value' => $listing_id,
                    'type' => 'NUMERIC',
                    'compare' => '='
                ),
                array(
                    'key' => 'reservation_status',
                    'value' => 'declined',
                    'type' => 'CHAR',
                    'compare' => '!='
                ),
                array(
                    'key' => 'reservation_status',
                    'value' => 'cancelled',
                    'type' => 'CHAR',
                    'compare' => '!='
                ),
                array(
                    'key' => 'reservation_status',
                    'value' => 'completed',
                    'type' => 'CHAR',
                    'compare' => '!='
                )
            )
        );

        $pending_dates_array = get_post_meta($listing_id, 'reservation_pending_hours', true);

        if (!is_array($pending_dates_array) || empty($pending_dates_array)) {
            $pending_dates_array = array();
        }

        $wpQry = new WP_Query($args);

        if ($wpQry->have_posts()) {

            while ($wpQry->have_posts()):
                $wpQry->the_post();

                $resID = get_the_ID();

                $check_in_date = get_post_meta($resID, 'reservation_checkin_hour', true);
                $check_out_date = get_post_meta($resID, 'reservation_checkout_hour', true);

                $unix_time_start = strtotime($check_in_date);

                if ($unix_time_start > $daysAgo) {

                    $check_in = new DateTime($check_in_date);
                    $check_in_unix = $check_in->getTimestamp();
                    $check_out = new DateTime($check_out_date);
                    $check_out_unix = $check_out->getTimestamp();


                    $pending_dates_array[$check_in_unix] = $resID;

                    $check_in_unix = $check_in->getTimestamp();

                    while ($check_in_unix < $check_out_unix) {

                        $pending_dates_array[$check_in_unix] = $resID;

                        //$check_in->modify('+1 hour');
                        $check_in->modify('+30 minutes');
                        $check_in_unix = $check_in->getTimestamp();
                    }
                }
            endwhile;
            wp_reset_postdata();
        }

        return $pending_dates_array;
    }
}

if (!function_exists('homey_get_hourly_prices')) {
    function homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests, $extra_options = null, $accommodation_number = null, $guests_participating = null, $extra_participants = null, $choose_guided_service = null, $extra_equipments = null, $additional_vehicles = null, $coupon_id = null)
    {

        $prefix = 'homey_';

        $enable_services_fee = homey_option('enable_services_fee');
        $enable_taxes = homey_option('enable_taxes');
        $offsite_payment = homey_option('off-site-payment');
        $reservation_payment_type = homey_option('reservation_payment');
        $booking_percent = homey_option('booking_percent');
        $tax_type = homey_option('tax_type');
        $apply_taxes_on_service_fee = homey_option('apply_taxes_on_service_fee');
        $taxes_percent_global = homey_option('taxes_percent');
        $single_listing_tax = get_post_meta($listing_id, 'homey_tax_rate', true);

        $period_price = get_post_meta($listing_id, 'homey_hourly_custom_period', true);
        if (empty($period_price)) {
            $period_price = array();
        }

        $total_accomodation_fee = 0;
        $total_extra_services = 0;
        $total_extra_equipments = 0;
        $additional_vehicles_fee = 0;
        $extra_prices_html = '';
        $extra_equipments_html = '';
        $taxes_final = 0;
        $taxes_percent = 0;
        $total_price = 0;
        $taxable_amount = 0;
        $total_guests_price = 0;
        $upfront_payment = 0;
        $hours_total_price = 0;
        $booking_has_weekend = 0;
        $booking_has_custom_pricing = 0;
        $balance = 0;
        $period_hours = 0;
        $security_deposit = '';
        $additional_guests = '';
        $additional_guests_total_price = '';
        $services_fee_final = '';
        $taxes_fee_final = '';
        $prices_array = array();

        $listing_guests = floatval(get_post_meta($listing_id, $prefix . 'guests', true));
        $hourly_price = floatval(get_post_meta($listing_id, $prefix . 'hour_price', true));
        $price_per_hour = $hourly_price;

        $amenity_price_type = get_field('amenity_price_type', $listing_id);

        $weekends_price = floatval(get_post_meta($listing_id, $prefix . 'hourly_weekends_price', true));
        $weekends_days = get_post_meta($listing_id, $prefix . 'weekends_days', true);
        //$priceWeek               = floatval( get_post_meta($listing_id, $prefix.'priceWeek', true) ); // 7 hours
        //$priceMonthly            = floatval( get_post_meta($listing_id, $prefix.'priceMonthly', true) );  // 30 hours
        $security_deposit = floatval(get_post_meta($listing_id, $prefix . 'security_deposit', true));

        $cleaning_fee = floatval(get_post_meta($listing_id, $prefix . 'cleaning_fee', true));
        $cleaning_fee_type = get_post_meta($listing_id, $prefix . 'cleaning_fee_type', true);

        $cost_per_additional_car = get_field('cost_per_additional_car', $listing_id);

        $have_sleeping_accommodations = get_field('field_6479eb9f0208c', $listing_id);
        $accomodation_fee = floatval(get_field('field_6479ec8dfe126', $listing_id));

        $have_occupancy_tax = get_field('have_occupancy_tax', $listing_id);
        $occupancy_tax_rate = get_field('occupancy_tax_rate', $listing_id);

        $have_guided_service = get_field('have_guided_service', $listing_id);
        $price_type = get_field('price_type', $listing_id);
        $price_rate = get_field('price_rate', $listing_id);
        $guided_fee = floatval(get_field('guided_price', $listing_id));

        $include_backyard_amenity = get_field('include_backyard_amenity', $listing_id);

        $gears_price = floatval(get_field('gears_price', $listing_id));
        $non_participants_price = floatval(get_field('non_participants_price', $listing_id));

        $coupon_discount = get_field('coupon_discount', $coupon_id);

        $tour_state = homey_get_taxonomy_title($listing_id, 'listing_state');
        $state_option_name = 'state_tax_rate_' . sanitize_title($tour_state);
        $state_tax_rate = get_option($state_option_name);

        $city_fee = floatval(get_post_meta($listing_id, $prefix . 'city_fee', true));
        $city_fee_type = get_post_meta($listing_id, $prefix . 'city_fee_type', true);

        $extra_guests_price = floatval(get_post_meta($listing_id, $prefix . 'additional_guests_price', true));
        $additional_guests_price = $extra_guests_price;

        $allow_additional_guests = get_post_meta($listing_id, $prefix . 'allow_additional_guests', true);

        $instances = get_post_meta($listing_id, '_listing_calendar_instances', true);
        $checked_in_date = date('Y-m-d', strtotime($check_in_hour));
        $amenity_value = 'available';

        foreach ($instances as $instance) {
            $selected_dates = explode(',', $instance['selected_dates']);
            if (in_array($checked_in_date, $selected_dates)) {
                $amenity_value = $instance['amenity'];
                break;
            }
        }

        $check_in = new DateTime($check_in_hour);
        $check_in_unix = $check_in->getTimestamp();
        $check_in_unix_first_day = $check_in->getTimestamp();
        $check_out = new DateTime($check_out_hour);
        $check_out_unix = $check_out->getTimestamp();

        $time_difference = abs(strtotime($check_in_hour) - strtotime($check_out_hour));
        $hours_count = $time_difference / 3600;
        $hours_count = floatval($hours_count);

        if ($amenity_value === 'available') {
            if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                $total_price = 0;
            } else {
                if ($amenity_price_type == 'price_per_hour') {
                    $total_price = $price_per_hour * $hours_count;
                } elseif ($amenity_price_type == 'price_per_day') {
                    $total_price = $price_per_hour;
                } elseif ($amenity_price_type == 'price_per_half_day') {
                    $total_price = $price_per_hour;
                }
            }
        }

        if (isset($period_price[$check_in_unix]) && isset($period_price[$check_in_unix]['hour_price']) && $period_price[$check_in_unix]['hour_price'] != 0) {
            $price_per_hour = $period_price[$check_in_unix]['hour_price'];

            $booking_has_custom_pricing = 1;
            $period_hours = $period_hours + 1;
        }

        // Check additional guests price
        if ($allow_additional_guests == 'yes' && $guests > 0 && !empty($guests)) {
            if ($guests > $listing_guests) {
                $additional_guests = $guests - $listing_guests;

                $guests_price_return = homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price);

                $total_guests_price = $total_guests_price + $guests_price_return;
            }
        }

        $check_in_unix = $check_in->getTimestamp();

        $weekday = date('N', $check_in_unix);
        if ($amenity_value === 'available') {
            if (homey_check_weekend($weekday, $weekends_days, $weekends_price)) {
                $booking_has_weekend = 1;
                if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                    $total_price = 0;
                } else {
                    $price_per_hour = $weekends_price;
                    if ($amenity_price_type == 'price_per_hour') {
                        $total_price = $weekends_price * $hours_count;
                    } elseif ($amenity_price_type == 'price_per_day') {
                        $total_price = $weekends_price;
                    } elseif ($amenity_price_type == 'price_per_half_day') {
                        $total_price = $weekends_price;
                    }
                }
            }
        }

        if ($have_sleeping_accommodations == 'yes') {
            $total_accomodation_fee = $accommodation_number * $accomodation_fee;
            $total_price += $total_accomodation_fee;
        }

        $flag = 0;
        if ($have_guided_service == 'guide_required') {
            $flag = 1;
        } else {
            if ($have_guided_service == 'guide_is_optional') {
                if (isset($choose_guided_service) && $choose_guided_service == 'on') {
                    $flag = 1;
                }
            }
        }


        if ($flag == 1) {

            if ($price_type == 'per_guest' && $price_rate == 'hourly_rate' && $guests_participating > 0) {
                if ($extra_participants > 0) {
                    $total_participants = $guests_participating + $extra_participants;
                } else {
                    $total_participants = $guests_participating;
                }
                $total_guest_hourly = $guided_fee * $hours_count * $total_participants;
                $total_price += $total_guest_hourly;
            }

            if ($price_type == 'per_guest' && $price_rate == 'fixed_rate' && $guests_participating > 0) {
                if ($extra_participants > 0) {
                    $total_participants = $guests_participating + $extra_participants;
                } else {
                    $total_participants = $guests_participating;
                }
                $total_guest_fixed = $guided_fee * $total_participants;
                $total_price += $total_guest_fixed;
            }

            if ($price_type == 'per_group' && $price_rate == 'hourly_rate') {
                $total_group_hourly = $guided_fee * $hours_count;
                $total_price += $total_group_hourly;
            }

            if ($price_type == 'per_group' && $price_rate == 'fixed_rate') {
                $total_group_fixed = $guided_fee;
                $total_price += $total_group_fixed;
            }

            if ($price_type == 'flat_fee' && $price_rate == 'hourly_rate') {
                $total_flat_hourly = $guided_fee * $hours_count;
                $total_price += $total_flat_hourly;
            }

            if ($price_type == 'flat_fee' && $price_rate == 'fixed_rate') {
                $total_flat_fixed = $guided_fee;
                $total_price += $total_flat_fixed;
            }


            if (isset($non_participants_price) && $non_participants_price != 0) {
                if (isset($extra_participants) && $extra_participants != 0) {
                    $total_non_participants_price = $non_participants_price * $extra_participants;
                    $total_price += $total_non_participants_price;
                }
            }
        }


        if ($have_sleeping_accommodations == 'yes' && $total_accomodation_fee != 0) {
            if ($cleaning_fee_type == 'daily') {
                $cleaning_fee = $cleaning_fee * $hours_count;
                $total_price = $total_price + $cleaning_fee;
            } else {
                $total_price = $total_price + $cleaning_fee;
            }
        }


        //Extra prices =======================================
        if ($extra_options != '') {

            $extra_prices_output = '';
            foreach ($extra_options as $extra_price) {
                $ex_single_price = explode('|', $extra_price);

                $ex_name = $ex_single_price[0];
                $ex_price = $ex_single_price[1];
                $ex_type = $ex_single_price[2];

                if ($ex_type == 'single_fee') {
                    $ex_price = $ex_price;
                } elseif ($ex_type == 'per_night') {
                    $ex_price = $ex_price * $hours_count;
                } elseif ($ex_type == 'per_guest') {
                    $ex_price = $ex_price * $guests;
                } elseif ($ex_type == 'per_night_per_guest') {
                    $ex_price = $ex_price * $hours_count * $guests;
                }

                $total_extra_services = $total_extra_services + $ex_price;

                $extra_prices_output .= '<li>' . esc_attr($ex_name) . '<span>' . homey_formatted_price($ex_price) . '</span></li>';
            }

            $total_price = $total_price + $total_extra_services;
            $extra_prices_html = $extra_prices_output;
        }

        //Extra Equipments =======================================
        if ($extra_equipments != '') {

            $extra_equipments_output = '';
            foreach ($extra_equipments as $extra_equipment) {
                $ex_single_equip = explode('|', $extra_equipment);

                $ex_equip_name = $ex_single_equip[0];
                $ex_equip_price = $ex_single_equip[1];
                $ex_equip_type = $ex_single_equip[2];

                if ($ex_equip_type == 'total_fee') {
                    $ex_equip_price = $ex_equip_price;
                } elseif ($ex_equip_type == 'per_guest_fee') {
                    if ($extra_participants > 0) {
                        $total_participants = $guests_participating + $extra_participants;
                    } else {
                        $total_participants = $guests_participating;
                    }
                    $ex_equip_price = $ex_equip_price * $total_participants;
                }

                $total_extra_equipments = $total_extra_equipments + $ex_equip_price;

                $extra_equipments_output .= '<li>' . esc_attr($ex_equip_name) . '<span>' . homey_formatted_price($ex_equip_price) . '</span></li>';
            }

            $total_price = $total_price + $total_extra_equipments;
            $extra_equipments_html = $extra_equipments_output;
        }


        //Calculate taxes based of original price (Excluding city, security deposit etc)
        if ($enable_taxes == 1) {

            if ($tax_type == 'global_tax') {
                $taxes_percent = $taxes_percent_global;
            } else {
                if (!empty($single_listing_tax)) {
                    $taxes_percent = $single_listing_tax;
                }
            }

            $taxable_amount = $total_price + $total_guests_price;
            $taxes_final = homey_calculate_taxes($taxes_percent, $taxable_amount);
            $total_price = $total_price + $taxes_final;
        }

        if ($additional_vehicles > 0) {
            $additional_vehicles_fee = $additional_vehicles * $cost_per_additional_car;
            $total_price += $additional_vehicles_fee;
        }

        if ($coupon_discount != 0) {
            $discount_amount = ($total_price * $coupon_discount) / 100;
            $total_price = $total_price - $discount_amount;
        }

        //Calculate sevices fee based of original price (Excluding cleaning, city, sevices fee etc)
        if ($enable_services_fee == 1 && $offsite_payment != 1) {
            $services_fee_type = homey_option('services_fee_type');
            $services_fee = homey_option('services_fee');
            $price_for_services_fee = $total_price + $total_guests_price;
            $services_fee_final = homey_calculate_services_fee($services_fee_type, $services_fee, $price_for_services_fee);
            $total_price = $total_price + $services_fee_final;
        }

        if (!$have_occupancy_tax && !empty($occupancy_tax_rate) && !empty($total_accomodation_fee)) {
            $total_acc_price = $total_accomodation_fee + $cleaning_fee;
            $occ_tax_amount = ($total_acc_price * $occupancy_tax_rate) / 100;
            $total_price += $occ_tax_amount;
        }

        if ($city_fee_type == 'daily') {
            $city_fee = $city_fee * $hours_count;
            $total_price = $total_price + $city_fee;
        } else {
            $total_price = $total_price + $city_fee;
        }

        if (!empty($security_deposit) && $security_deposit != 0) {
            $total_price = $total_price + $security_deposit;
        }

        if ($total_guests_price != 0) {
            $total_price = $total_price + $total_guests_price;
        }

        if (!empty($state_tax_rate)) {
            $total_state_tax = ($total_price * $state_tax_rate) / 100;
            $total_price += $total_state_tax;
        }

        $offsite_payment = homey_option('off-site-payment');
        $listing_host_id = get_post_field('post_author', $listing_id);
        $host_reservation_payment_type = get_user_meta($listing_host_id, 'host_reservation_payment', true);
        $host_booking_percent = get_user_meta($listing_host_id, 'host_booking_percent', true);

        if ($offsite_payment == 1 && !empty($host_reservation_payment_type)) {

            if ($host_reservation_payment_type == 'percent') {
                if (!empty($host_booking_percent) && $host_booking_percent != 0) {
                    $upfront_payment = round($host_booking_percent * $total_price / 100, 2);
                }
            } elseif ($host_reservation_payment_type == 'full') {
                $upfront_payment = $total_price;
            } elseif ($host_reservation_payment_type == 'only_security') {
                $upfront_payment = $security_deposit;
            } elseif ($host_reservation_payment_type == 'only_services') {
                $upfront_payment = $services_fee_final;
            } elseif ($host_reservation_payment_type == 'services_security') {
                $upfront_payment = $security_deposit + $services_fee_final;
            }
        } else {

            if ($reservation_payment_type == 'percent') {
                if (!empty($booking_percent) && $booking_percent != 0) {
                    $upfront_payment = round($booking_percent * $total_price / 100, 2);
                }
            } elseif ($reservation_payment_type == 'full') {
                $upfront_payment = $total_price;
            } elseif ($reservation_payment_type == 'only_security') {
                $upfront_payment = $security_deposit;
            } elseif ($reservation_payment_type == 'only_services') {
                $upfront_payment = $services_fee_final;
            } elseif ($reservation_payment_type == 'services_security') {
                $upfront_payment = $security_deposit + $services_fee_final;
            }
        }

        $balance = $total_price - $upfront_payment;

        $prices_array['price_per_hour'] = $price_per_hour;
        if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
            $prices_array['hours_total_price'] = 0;
        } else {
            if ($amenity_price_type == 'price_per_hour') {
                $prices_array['hours_total_price'] = $price_per_hour * $hours_count;
            } elseif ($amenity_price_type == 'price_per_day') {
                $prices_array['hours_total_price'] = $price_per_hour;
            } elseif ($amenity_price_type == 'price_per_half_day') {
                $prices_array['hours_total_price'] = $price_per_hour;
            }
        }
        $prices_array['total_price'] = $total_price;
        $prices_array['total_extra_services'] = $total_extra_services;
        $prices_array['check_in_hour'] = $check_in_hour;
        $prices_array['check_out_hour'] = $check_out_hour;
        $prices_array['cleaning_fee'] = $cleaning_fee;
        $prices_array['accomodation_fee'] = $accomodation_fee;
        $prices_array['total_accomodation_fee'] = $total_accomodation_fee;
        $prices_array['guided_fee'] = $guided_fee;
        $prices_array['total_guest_hourly'] = $total_guest_hourly;
        $prices_array['total_guest_fixed'] = $total_guest_fixed;
        $prices_array['total_group_hourly'] = $total_group_hourly;
        $prices_array['total_group_fixed'] = $total_group_fixed;
        $prices_array['total_flat_hourly'] = $total_flat_hourly;
        $prices_array['total_flat_fixed'] = $total_flat_fixed;
        $prices_array['non_participants_price'] = $non_participants_price;
        $prices_array['total_non_participants_price'] = $total_non_participants_price;
        $prices_array['total_participants'] = $total_participants;
        $prices_array['flag'] = $flag;
        $prices_array['occ_tax_amount'] = $occ_tax_amount;
        $prices_array['total_state_tax'] = $total_state_tax;
        $prices_array['city_fee'] = $city_fee;
        $prices_array['services_fee'] = $services_fee_final;
        $prices_array['hours_count'] = $hours_count;
        //$prices_array['period_hours']      = $period_hours;
        $prices_array['taxes'] = $taxes_final;
        $prices_array['taxes_percent'] = $taxes_percent;
        $prices_array['security_deposit'] = $security_deposit;
        $prices_array['additional_guests'] = $additional_guests;
        $prices_array['additional_guests_price'] = $additional_guests_price;
        $prices_array['additional_guests_total_price'] = $total_guests_price;
        $prices_array['booking_has_weekend'] = $booking_has_weekend;
        $prices_array['extra_prices_html'] = $extra_prices_html;
        $prices_array['extra_equipments_html'] = $extra_equipments_html;
        $prices_array['total_equipments_price'] = $total_extra_equipments;
        $prices_array['booking_has_custom_pricing'] = $booking_has_custom_pricing;
        $prices_array['balance'] = $balance;
        $prices_array['upfront_payment'] = $upfront_payment;
        $prices_array['cost_per_additional_car'] = $cost_per_additional_car;
        $prices_array['additional_vehicles'] = $additional_vehicles;
        $prices_array['additional_vehicles_fee'] = $additional_vehicles_fee;
        $prices_array['coupon_discount'] = $discount_amount;
        $prices_array['amenity_value'] = $amenity_value;

        return $prices_array;
    }
}



if (!function_exists('homey_check_hourly_weekend')) {
    function homey_check_hourly_weekend($weekday, $weekends_days, $weekends_price)
    {

        if (empty($weekends_price) && $weekends_price == 0) {
            return false;
        } else {

            if ($weekends_days == 'sat_sun' && ($weekday == 6 || $weekday == 7)) {
                return true;
            } elseif ($weekends_days == 'fri_sat' && ($weekday == 5 || $weekday == 6)) {
                return true;
            } elseif ($weekends_days == 'fri_sat_sun' && ($weekday == 5 || $weekday == 6 || $weekday == 7)) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }
}

if (!function_exists('homey_cal_hourly_weekend_price')) {
    function homey_cal_hourly_weekend_price($check_in_unix, $weekends_price, $price_per_hour, $weekends_days, $period_price)
    {
        $weekday = date('N', $check_in_unix);

        $return_array = array();

        if ($weekends_days == 'sat_sun' && ($weekday == 6 || $weekday == 7)) {
            $return_price = homey_get_hourly_weekend_price($check_in_unix, $weekends_price, $price_per_hour, $weekends_days, $period_price);

            $return_array['weekend'] = 'yes';
            $return_array['weekend_price'] = $return_price;
        } elseif ($weekends_days == 'fri_sat' && ($weekday == 5 || $weekday == 6)) {
            $return_price = homey_get_hourly_weekend_price($check_in_unix, $weekends_price, $price_per_hour, $weekends_days, $period_price);

            $return_array['weekend'] = 'yes';
            $return_array['weekend_price'] = $return_price;
        } elseif ($weekends_days == 'fri_sat_sun' && ($weekday == 5 || $weekday == 6 || $weekday == 7)) {
            $return_price = homey_get_hourly_weekend_price($check_in_unix, $weekends_price, $price_per_hour, $weekends_days, $period_price);

            $return_array['weekend'] = 'yes';
            $return_array['weekend_price'] = $return_price;
        } else {
            $return_array['weekend'] = 'no';
            $return_array['weekend_price'] = '';
        }

        return $return_array;
    }
}


if (!function_exists('homey_get_hourly_weekend_price')) {
    function homey_get_hourly_weekend_price($check_in_unix, $weekends_price, $price_per_hour, $weekends_days, $period_price)
    {
        if (isset($period_price[$check_in_unix]) && isset($period_price[$check_in_unix]['weekend_price']) && $period_price[$check_in_unix]['weekend_price'] != 0) {

            $return_price = $period_price[$check_in_unix]['weekend_price'];
        } elseif (!empty($weekends_price) && $weekends_price != 0) {
            $return_price = $weekends_price;
        } else {
            $return_price = $price_per_hour;
        }

        return $return_price;
    }
}

function homey_validate_coupon($coupon_code, $listing_id, $current_user_id)
{
    // Ensure the coupon code is provided
    if (empty($coupon_code)) {
        return false;
    }

    // Perform the query to find the coupon with the exact title (coupon code)
    $coupon_query = new WP_Query(
        array(
            'post_type' => 'host_coupon',
            'title' => $coupon_code,
            'author' => get_post_field('post_author', $listing_id),
            'posts_per_page' => 1,
            'exact' => true // Ensure an exact match for the title
        )
    );

    if ($coupon_query->have_posts()) {
        $coupon_query->the_post();
        $coupon_id = get_the_ID();
        $guests = get_post_meta($coupon_id, 'coupon_guests_ids', true);

        if ($guests && in_array($current_user_id, $guests)) {
            // Optional: Update the eligible guests list (if necessary)
            // $updated_guests = array_diff($guests, array($current_user_id));
            // update_post_meta($coupon_id, 'coupon_guests_ids', $updated_guests);
            return $coupon_id;
        }
    }

    return false;
}


add_action('wp_ajax_validate_coupon', 'validate_coupon');
add_action('wp_ajax_nopriv_validate_coupon', 'validate_coupon');

function validate_coupon()
{
    $coupon_code = sanitize_text_field($_POST['coupon_code']);
    $listing_id = intval($_POST['listing_id']);
    $current_user_id = get_current_user_id();

    $coupon_id = homey_validate_coupon($coupon_code, $listing_id, $current_user_id);
    if ($coupon_id) {
        $response = array('success' => true, 'message' => 'Coupon applied.');
    } else {
        $response = array('success' => false, 'message' => 'Coupon not exist.');
    }

    echo json_encode($response);
    wp_die();
}


add_action('wp_ajax_nopriv_homey_calculate_hourly_booking_cost', 'homey_calculate_hourly_booking_cost_ajax');
add_action('wp_ajax_homey_calculate_hourly_booking_cost', 'homey_calculate_hourly_booking_cost_ajax');

if (!function_exists('homey_calculate_hourly_booking_cost_ajax')) {
    function homey_calculate_hourly_booking_cost_ajax()
    {
        // error_reporting(E_ALL);
        // ini_set("display_errors", 1);
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $listing_id = intval($_POST['listing_id']);
        $check_in_date = wp_kses($_POST['check_in_date'], $allowded_html);
        $start_hour = wp_kses($_POST['start_hour'], $allowded_html);
        $end_hour = wp_kses($_POST['end_hour'], $allowded_html);
        $guests = intval($_POST['guests']);
        $extra_options = $_POST['extra_options'];
        $extra_equipments = $_POST['extra_equipments'];
        $additional_vehicles = $_POST['additional_vehicles'];
        $choose_guided_service = $_POST['choose_guided_service'];
        $accommodation_number = intval($_POST['accommodation_number']);
        $guests_participating = intval($_POST['guests_participating']);
        $extra_participants = intval($_POST['extra_participants']);
        $have_sleeping_accommodations = get_field('field_6479eb9f0208c', $listing_id);
        $include_backyard_amenity = get_field('include_backyard_amenity', $listing_id);
        $have_guided_service = get_field('have_guided_service', $listing_id);
        $price_type = get_field('price_type', $listing_id);
        $price_rate = get_field('price_rate', $listing_id);
        $occupancy_tax_rate = get_field('occupancy_tax_rate', $listing_id);
        $current_occupancy_state = get_field('current_occupancy_state', $listing_id);
        $tour_state = homey_get_taxonomy_title($listing_id, 'listing_state');
        $current_user_id = get_current_user_id();
        $coupon_code = sanitize_text_field($_POST['coupon_code']);

        $amenity_price_type = get_field('amenity_price_type', $listing_id);

        $flag = 0;
        if ($have_guided_service == 'guide_required') {
            $flag = 1;
        } else {
            if ($have_guided_service == 'guide_is_optional') {
                if (isset($choose_guided_service) && $choose_guided_service == 'on') {
                    $flag = 1;
                }
            }
        }

        $check_in_hour = $check_in_date . ' ' . $start_hour;
        $check_out_hour = $check_in_date . ' ' . $end_hour;

        $coupon_id = homey_validate_coupon($coupon_code, $listing_id, $current_user_id);

        $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests, $extra_options, $accommodation_number, $guests_participating, $extra_participants, $choose_guided_service, $extra_equipments, $additional_vehicles, $coupon_id);

        $price_per_hour = homey_formatted_price($prices_array['price_per_hour'], true);
        $no_of_hours = $prices_array['hours_count'];

        $hours_total_price = homey_formatted_price($prices_array['hours_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $accomodation_fee = homey_formatted_price($prices_array['accomodation_fee'], true);
        $total_accomodation_fee = homey_formatted_price($prices_array['total_accomodation_fee']);
        $guided_fee = homey_formatted_price($prices_array['guided_fee'], true);
        $gears_price = homey_formatted_price($prices_array['gears_price'], true);
        $total_gears_price = homey_formatted_price($prices_array['total_gears_price']);
        $total_guest_hourly = homey_formatted_price($prices_array['total_guest_hourly']);
        $total_guest_fixed = homey_formatted_price($prices_array['total_guest_fixed']);
        $total_group_hourly = homey_formatted_price($prices_array['total_group_hourly']);
        $total_group_fixed = homey_formatted_price($prices_array['total_group_fixed']);
        $total_flat_hourly = homey_formatted_price($prices_array['total_flat_hourly']);
        $total_flat_fixed = homey_formatted_price($prices_array['total_flat_fixed']);
        $non_participants_price = homey_formatted_price($prices_array['non_participants_price'], true);
        $total_non_participants_price = homey_formatted_price($prices_array['total_non_participants_price']);
        $occ_tax_amount = homey_formatted_price($prices_array['occ_tax_amount']);
        $total_state_tax = homey_formatted_price($prices_array['total_state_tax']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $amenity_value = $prices_array['amenity_value'];

        $cost_per_additional_car = homey_formatted_price($prices_array['cost_per_additional_car']);
        $additional_vehicles = $prices_array['additional_vehicles'];
        $additional_vehicles_fee = $prices_array['additional_vehicles_fee'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];
        $extra_prices_html = $prices_array['extra_prices_html'];
        $extra_equipments_html = $prices_array['extra_equipments_html'];

        if ($no_of_hours > 1) {
            $hour_label = $local['hours_label'];
        } else {
            $hour_label = $local['hour_label'];
        }

        if ($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $output = '<div class="payment-list-price-detail clearfix">';
        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">' . esc_attr($local['cs_total']) . '</div>';
        $output .= '<div class="payment-list-price-detail-note">' . esc_attr($local['cs_tax_fees']) . '</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">' . homey_formatted_price($total_price) . '</div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">' . esc_attr($local['cs_view_details']) . '</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="collapse collapseExample" id="collapseExample">';
        $output .= '<ul>';

        if ($amenity_value === 'available') {
            $output .= '<strong>AMENITY</strong>';

            if ($amenity_price_type == 'price_per_hour') {
                if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($local['with_custom_period_and_weekend_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . ' x ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($with_weekend_label) . ') <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_custom_pricing == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($local['with_custom_period_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } else {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . ' x ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                }
            } elseif ($amenity_price_type == 'price_per_day') {
                if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure (' . esc_attr($local['with_custom_period_and_weekend_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . '/Day (' . esc_attr($with_weekend_label) . ') <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_custom_pricing == 1) {
                    $output .= '<li>Backyard Adventure (' . esc_attr($local['with_custom_period_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } else {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . '/Day <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                }
            } elseif ($amenity_price_type == 'price_per_half_day') {
                if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure (' . esc_attr($local['with_custom_period_and_weekend_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . '/Half Day (' . esc_attr($with_weekend_label) . ') <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_custom_pricing == 1) {
                    $output .= '<li>Backyard Adventure (' . esc_attr($local['with_custom_period_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } else {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . '/Half Day <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                }
            } else {
                if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($local['with_custom_period_and_weekend_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . ' x ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($with_weekend_label) . ') <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_custom_pricing == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($local['with_custom_period_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } else {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . ' x ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                }
            }
        }

        if (!empty($extra_prices_html)) {
            $output .= '<strong style="font-size: 16px;">Add-On Services</strong>';
            $output .= $extra_prices_html;
        }

        if (!empty($additional_guests)) {
            $output .= '<li>Extra Guests ' . esc_attr($additional_guests) . ' ' . esc_attr($add_guest_label) . ' <span>' . homey_formatted_price($additional_guests_total_price) . '</span></li>';
        }

        if ($flag == 1) {
            $output .= '<strong>GUIDED SERVICE</br></strong>';
            if (!empty($total_guest_hourly) && $total_guest_hourly != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . ' x ' . $no_of_hours . ' hours x ' . $prices_array['total_participants'] . ' guests  </div><div class="details-right">' . esc_attr($total_guest_hourly) . '</div></li>';
            }
            if (!empty($total_guest_fixed) && $total_guest_fixed != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . ' x ' . $prices_array['total_participants'] . ' guests  </div><div class="details-right">' . esc_attr($total_guest_fixed) . '</div></li>';
            }
            if (!empty($total_group_hourly) && $total_group_hourly != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . ' x ' . $no_of_hours . ' hours </div><div class="details-right">' . esc_attr($total_group_hourly) . '</div></li>';
            }
            if (!empty($total_group_fixed) && $total_group_fixed != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . ' per group  </div><div class="details-right">' . esc_attr($total_group_fixed) . '</div></li>';
            }
            if (!empty($total_flat_hourly) && $total_flat_hourly != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . ' x ' . $no_of_hours . ' hours  </div><div class="details-right">' . esc_attr($total_flat_hourly) . '</div></li>';
            }
            if (!empty($total_flat_fixed) && $total_flat_fixed != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . '</div><div class="details-right">' . esc_attr($total_flat_fixed) . '</div></li>';
            }

            if ($prices_array['total_non_participants_price'] != 0) {
                $output .= '<li> Extra Guests ' . $non_participants_price . ' x ' . $extra_participants . ' guests <span>' . esc_attr($total_non_participants_price) . '</span></li>';
            }

            if (!empty($extra_equipments_html)) {
                $output .= '<strong style="font-size: 16px;">Equipments For Rental</strong>';
                $output .= $extra_equipments_html;
            }
        }

        if (!empty($prices_array['total_accomodation_fee']) && $prices_array['total_accomodation_fee'] != 0 && $have_sleeping_accommodations == 'yes') {
            $output .= '<strong>SLEEPING ACCOMMODATION</strong>';
            $output .= '<li> Lodging ' . $accommodation_number . ' x night <span>' . esc_attr($total_accomodation_fee) . '</span></li>';
        }

        if (!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0 && $prices_array['total_accomodation_fee'] != 0 && $have_sleeping_accommodations == 'yes') {
            $output .= '<li>' . esc_attr($local['cs_cleaning_fee']) . ' <span>' . esc_attr($cleaning_fee) . '</span></li>';
        }

        if (!empty($additional_vehicles_fee) && $additional_vehicles_fee != 0) {
            $output .= '<strong>ADDITIONAL VEHICLES</strong>';
            $output .= '<li>' . $additional_vehicles . ' Vehicles x ' . $cost_per_additional_car . '<span>' . homey_formatted_price($additional_vehicles_fee) . '</span></li>';
        }


        if (!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>' . esc_attr($local['cs_city_fee']) . ' <span>' . esc_attr($city_fee) . '</span></li>';
        }

        if (!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>' . esc_attr($local['cs_sec_deposit']) . ' <span>' . homey_formatted_price($security_deposit) . '</span></li>';
        }

        if (!empty($prices_array['coupon_discount']) && $prices_array['coupon_discount'] != 0) {
            $output .= '<li>Coupon Discount<span>-$' . esc_attr($prices_array['coupon_discount']) . '</span></li>';
        }

        if (!empty($services_fee) && $services_fee != 0) {
            $output .= '<li>Backyard Lease Service Fee <span>' . homey_formatted_price($services_fee) . '</span></li>';
        }

        if (!empty($occ_tax_amount) && $occ_tax_amount != 0) {
            $output .= '<li><strong>Occupancy Tax ' . $occupancy_tax_rate . '%  (' . ucwords($current_occupancy_state) . ')</strong> <span>' . $occ_tax_amount . '</span></li>';
        }

        if (!empty($total_state_tax)) {
            $output .= '<li><strong>Sales Tax (' . ucwords($tour_state) . ')</strong> <span>' . $total_state_tax . '</span></li>';
        }

        if (!empty($taxes) && $taxes != 0) {
            $output .= '<li>' . esc_attr($local['cs_taxes']) . ' ' . esc_attr($taxes_percent) . '% <span>' . homey_formatted_price($taxes) . '</span></li>';
        }

        if (!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">TOTAL: <span>' . homey_formatted_price($upfront_payment) . '</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="' . $upfront_payment . '">';
        }

        $output .= '</ul>';
        $output .= '</div>';

        // This variable has been safely escaped in same file: Line: 1071 - 1128
        $output_escaped = $output;
        print '' . $output_escaped;

        wp_die();
    }
}

if (!function_exists('homey_calculate_hourly_booking_cost')) {
    function homey_calculate_hourly_booking_cost($reservation_id, $collapse = false)
    {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if (empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);

        $listing_id = intval($reservation_meta['listing_id']);
        $check_in_date = wp_kses($reservation_meta['check_in_date'], $allowded_html);
        $check_in_hour = wp_kses($reservation_meta['check_in_hour'], $allowded_html);
        $check_out_hour = wp_kses($reservation_meta['check_out_hour'], $allowded_html);
        $guests = intval($reservation_meta['guests']);

        $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests);

        $price_per_hour = homey_formatted_price($prices_array['price_per_hour'], true);
        $no_of_hours = $prices_array['hours_count'];

        $hours_total_price = homey_formatted_price($prices_array['hours_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if ($no_of_hours > 1) {
            $hour_label = $local['hours_label'];
        } else {
            $hour_label = $local['hour_label'];
        }

        if ($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $start_div = '<div class="payment-list">';

        if ($collapse) {
            $output = '<div class="payment-list-price-detail clearfix">';
            $output .= '<div class="pull-left">';
            $output .= '<div class="payment-list-price-detail-total-price">' . $local['cs_total'] . '</div>';
            $output .= '<div class="payment-list-price-detail-note">' . $local['cs_tax_fees'] . '</div>';
            $output .= '</div>';

            $output .= '<div class="pull-right text-right">';
            $output .= '<div class="payment-list-price-detail-total-price">' . homey_formatted_price($total_price) . '</div>';
            $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">' . $local['cs_view_details'] . '</a>';
            $output .= '</div>';
            $output .= '</div>';

            $start_div = '<div class="collapse collapseExample" id="collapseExample">';
        }


        $output .= $start_div;
        $output .= '<ul>';

        if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li>' . $no_of_hours . ' ' . $hour_label . ' (' . $local['with_custom_period_and_weekend_label'] . ') <span>' . $hours_total_price . '</span></li>';
        } elseif ($booking_has_weekend == 1) {
            $output .= '<li>' . esc_attr($price_per_hour) . ' x ' . $no_of_hours . ' ' . $hour_label . ' (' . $with_weekend_label . ') <span>' . $hours_total_price . '</span></li>';
        } elseif ($booking_has_custom_pricing == 1) {
            $output .= '<li>' . $no_of_hours . ' ' . $hour_label . ' (' . $local['with_custom_period_label'] . ') <span>' . $hours_total_price . '</span></li>';
        } else {
            $output .= '<li>' . $price_per_hour . ' x ' . $no_of_hours . ' ' . $hour_label . ' <span>' . $hours_total_price . '</span></li>';
        }

        if (!empty($additional_guests)) {
            $output .= '<li>' . $additional_guests . ' ' . $add_guest_label . ' <span>' . homey_formatted_price($additional_guests_total_price) . '</span></li>';
        }

        if (!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>' . $local['cs_cleaning_fee'] . ' <span>' . $cleaning_fee . '</span></li>';
        }

        if (!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>' . $local['cs_city_fee'] . ' <span>' . $city_fee . '</span></li>';
        }

        if (!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>' . $local['cs_sec_deposit'] . ' <span>' . homey_formatted_price($security_deposit) . '</span></li>';
        }

        if (!empty($services_fee) && $services_fee != 0) {
            $output .= '<li>' . $local['cs_services_fee'] . ' <span>' . homey_formatted_price($services_fee) . '</span></li>';
        }

        if (!empty($taxes) && $taxes != 0) {
            $output .= '<li>' . $local['cs_taxes'] . ' ' . $taxes_percent . '% <span>' . homey_formatted_price($taxes) . '</span></li>';
        }

        if (!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">' . $local['cs_payment_due'] . ' <span>' . homey_formatted_price($upfront_payment) . '</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="' . $upfront_payment . '">';
        }

        if (!empty($balance) && $balance != 0) {
            $output .= '<li><i class="fa fa-info-circle"></i> ' . $local['cs_pay_rest_1'] . ' ' . homey_formatted_price($balance) . ' ' . $local['cs_pay_rest_2'] . '</li>';
        }

        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

add_action('wp_ajax_homey_cancel_guided_service', 'homey_cancel_guided_service');
function homey_cancel_guided_service()
{
    if (isset($_POST['reservation_id'])) {
        $reservation_id = intval($_POST['reservation_id']);

        // Set the is_guided_service_canceled meta field
        update_post_meta($reservation_id, 'is_guided_service_canceled', true);

        // Return success or failure response
        echo json_encode(array('success' => true, 'message' => 'Guided service canceled successfully'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Invalid reservation ID'));
    }

    wp_die();
}


if (!function_exists('homey_calculate_hourly_reservation_cost')) {
    function homey_calculate_hourly_reservation_cost($reservation_id, $collapse = false)
    {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if (empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);
        //echo '<pre>';print_r($reservation_meta);
        $listing_id = intval($reservation_meta['listing_id']);
        $check_in_date = wp_kses($reservation_meta['check_in_date'], $allowded_html);
        $check_in_hour = wp_kses($reservation_meta['check_in_hour'], $allowded_html);
        $check_out_hour = wp_kses($reservation_meta['check_out_hour'], $allowded_html);
        $guests = intval($reservation_meta['guests']);

        $have_guided_service = get_field('have_guided_service', $listing_id);
        $reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
        $is_guided_service_canceled = get_post_meta($reservation_id, 'is_guided_service_canceled', true);
        $occupancy_tax_rate = get_field('occupancy_tax_rate', $listing_id);
        $current_occupancy_state = get_field('current_occupancy_state', $listing_id);

        $amenity_price_type = $reservation_meta['amenity_price_type'];

        $amenity_value = $reservation_meta['amenity_value'];

        $price_per_hour = homey_formatted_price($reservation_meta['price_per_hour'], true);
        $no_of_hours = $reservation_meta['no_of_hours'];

        $cost_per_additional_car = homey_formatted_price($reservation_meta['cost_per_additional_car'], true);
        $additional_vehicles = $reservation_meta['additional_vehicles'];
        $additional_vehicles_fee = $reservation_meta['additional_vehicles_fee'];

        $hours_total_price = homey_formatted_price($reservation_meta['hours_total_price'], false);

        $cleaning_fee = homey_formatted_price($reservation_meta['cleaning_fee']);
        $total_accomodation_fee = homey_formatted_price($reservation_meta['total_accomodation_fee']);
        $accomodation_fee = homey_formatted_price($reservation_meta['accomodation_fee']);
        $accommodation_number = $reservation_meta['accommodation_number'];

        $have_sleeping_accommodations = $reservation_meta['have_sleeping_accommodations'];
        $include_backyard_amenity = $reservation_meta['include_backyard_amenity'];

        $guests_participating = $reservation_meta['guests_participating'];
        $extra_participants = $reservation_meta['extra_participants'];
        $choose_guided_service = $reservation_meta['choose_guided_service'];
        $guided_fee = homey_formatted_price($reservation_meta['guided_fee'], true);
        $total_guest_hourly = homey_formatted_price($reservation_meta['total_guest_hourly']);
        $total_guest_fixed = homey_formatted_price($reservation_meta['total_guest_fixed']);
        $total_group_hourly = homey_formatted_price($reservation_meta['total_group_hourly']);
        $total_group_fixed = homey_formatted_price($reservation_meta['total_group_fixed']);
        $total_flat_hourly = homey_formatted_price($reservation_meta['total_flat_hourly']);
        $total_flat_fixed = homey_formatted_price($reservation_meta['total_flat_fixed']);
        $total_guest_hourlys = doubleval($reservation_meta['total_guest_hourly']);
        $total_guest_fixeds = doubleval($reservation_meta['total_guest_fixed']);
        $total_group_hourlys = doubleval($reservation_meta['total_group_hourly']);
        $total_group_fixeds = doubleval($reservation_meta['total_group_fixed']);
        $total_flat_hourlys = doubleval($reservation_meta['total_flat_hourly']);
        $total_flat_fixeds = doubleval($reservation_meta['total_flat_fixed']);
        $gears_price = homey_formatted_price($reservation_meta['gears_price'], true);
        $total_gears_price = homey_formatted_price($reservation_meta['total_gears_price']);
        $total_gears_prices = doubleval($reservation_meta['total_gears_price']);
        $non_participants_price = homey_formatted_price($reservation_meta['non_participants_price'], true);
        $total_non_participants_price = homey_formatted_price($reservation_meta['total_non_participants_price']);
        $total_non_participants_prices = doubleval($reservation_meta['total_non_participants_price']);
        $total_participants = $reservation_meta['total_participants'];
        $flag = $reservation_meta['flag'];
        $occ_tax_amount = homey_formatted_price($reservation_meta['occ_tax_amount']);
        $tour_state = homey_get_taxonomy_title($listing_id, 'listing_state');
        $total_state_tax = homey_formatted_price($reservation_meta['total_state_tax']);

        $coupon_discount = $reservation_meta['coupon_discount'];

        $extra_equipments_html = $reservation_meta['extra_equipments_html'];
        $total_equipments_price = $reservation_meta['total_equipments_price'];

        $services_fee = doubleval($reservation_meta['services_fee']);
        $taxes = doubleval($reservation_meta['taxes']);
        $taxes_percent = $reservation_meta['taxes_percent'];
        $city_fee = homey_formatted_price($reservation_meta['city_fee']);
        $security_deposit = $reservation_meta['security_deposit'];
        $additional_guests = $reservation_meta['additional_guests'];
        $additional_guests_price = doubleval($reservation_meta['additional_guests_price']);
        $additional_guests_total_price = doubleval($reservation_meta['additional_guests_total_price']);

        $upfront_payment = doubleval($reservation_meta['upfront']);
        $balance = doubleval($reservation_meta['balance']);
        $total_price = doubleval($reservation_meta['total']);

        $booking_has_weekend = $reservation_meta['booking_has_weekend'];
        $booking_has_custom_pricing = $reservation_meta['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if ($no_of_hours > 1) {
            $hour_label = $local['hours_label'];
        } else {
            $hour_label = $local['hour_label'];
        }

        if ($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $invoice_id = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : '';
        $reservation_detail_id = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
        $is_host = false;
        $homey_invoice_buyer = get_post_meta($reservation_id, 'listing_renter', true);

        if ((!empty($invoice_id) || !empty($reservation_detail_id)) && (homey_is_host() && $homey_invoice_buyer != get_current_user_id())) {
            $is_host = true;
        }

        $extra_prices = homey_get_extra_prices($extra_options, $no_of_hours, $guests);

        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        if (!empty($extra_expenses)) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $total_price = $total_price + $expenses_total_price;
            $upfront_payment += $expenses_total_price;
            //            $balance = $balance + $expenses_total_price; //just to exclude from payment to local
        }

        if (!empty($extra_discount)) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $total_price = $total_price - $discount_total_price;
            $upfront_payment -= $discount_total_price;
            //$balance = $balance - $discount_total_price;//just to exclude from payment to local
        }

        if ($is_guided_service_canceled == 1) {
            if (!empty($total_guest_hourly) && $total_guest_hourly != 0) {
                $total_guided_price = $total_guest_hourlys + $total_non_participants_prices;
            }
            if (!empty($total_guest_fixed) && $total_guest_fixed != 0) {
                $total_guided_price = $total_guest_fixeds + $total_non_participants_prices;
            }
            if (!empty($total_group_hourly) && $total_group_hourly != 0) {
                $total_guided_price = $total_group_hourlys + $total_non_participants_prices;
            }
            if (!empty($total_group_fixed) && $total_group_fixed != 0) {
                $total_guided_price = $total_group_fixeds + $total_non_participants_prices;
            }
            if (!empty($total_flat_hourly) && $total_flat_hourly != 0) {
                $total_guided_price = $total_flat_hourlys + $total_non_participants_prices;
            }
            if (!empty($total_flat_fixed) && $total_flat_fixed != 0) {
                $total_guided_price = $total_flat_fixeds + $total_non_participants_prices;
            }
            $total_price = $total_price - $total_guided_price;
            $total_price = $total_price - $total_equipments_price;
            $upfront_payment -= $total_guided_price;
            $upfront_payment -= $total_equipments_price;
            $reservation_meta['total'] = $total_price;
            $reservation_meta['flag'] = 0;
            update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
            update_post_meta($reservation_id, 'is_guided_service_canceled', 0);
        }

        $flag = $reservation_meta['flag'];
        $upfront_payment = $total_price;

        $start_div = '<div class="payment-list">';

        if ($collapse) {
            $output = '<div class="payment-list-price-detail clearfix">';
            $output .= '<div class="pull-left">';
            $output .= '<div class="payment-list-price-detail-total-price">' . $local['cs_total'] . '</div>';
            $output .= '<div class="payment-list-price-detail-note">' . $local['cs_tax_fees'] . '</div>';
            $output .= '</div>';

            $output .= '<div class="pull-right text-right">';
            $output .= '<div class="payment-list-price-detail-total-price">' . homey_formatted_price($total_price) . '</div>';
            $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">' . $local['cs_view_details'] . '</a>';
            $output .= '</div>';
            $output .= '</div>';

            $start_div = '<div class="collapse collapseExample" id="collapseExample">';
        }


        $output .= $start_div;
        $output .= '<ul>';

        if ($amenity_value === 'available') {
            $output .= '<strong>AMENITY</strong>';


            if ($amenity_price_type == 'price_per_hour') {
                if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($local['with_custom_period_and_weekend_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . ' x ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($with_weekend_label) . ') <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_custom_pricing == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($local['with_custom_period_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } else {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . ' x ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                }
            } elseif ($amenity_price_type == 'price_per_day') {
                if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure (' . esc_attr($local['with_custom_period_and_weekend_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . '/Day (' . esc_attr($with_weekend_label) . ') <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_custom_pricing == 1) {
                    $output .= '<li>Backyard Adventure (' . esc_attr($local['with_custom_period_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } else {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . '/Day <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                }
            } elseif ($amenity_price_type == 'price_per_half_day') {
                if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure (' . esc_attr($local['with_custom_period_and_weekend_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . '/Half Day (' . esc_attr($with_weekend_label) . ') <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_custom_pricing == 1) {
                    $output .= '<li>Backyard Adventure (' . esc_attr($local['with_custom_period_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } else {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . '/Half Day <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                }
            } else {
                if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($local['with_custom_period_and_weekend_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . ' x ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($with_weekend_label) . ') <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_custom_pricing == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($local['with_custom_period_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } else {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . ' x ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                }
            }
        }

        if (!empty($extra_prices)) {
            $output .= '<span>Extra Services</span>';
            $output .= $extra_prices['extra_html'];
        }

        if (!empty($additional_guests)) {
            $output .= '<li>Extra Guests ' . $additional_guests . ' ' . $add_guest_label . ' <span>' . homey_formatted_price($additional_guests_total_price) . '</span></li>';
        }

        if ($flag == 1) {
            $output .= '<strong>GUIDED SERVICE</strong>';
            if (!empty($total_guest_hourly) && $total_guest_hourly != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . ' x ' . $no_of_hours . ' hours x ' . $guests_participating . ' guests  </div><div class="details-right">' . esc_attr($total_guest_hourly) . '</div></li>';
            }
            if (!empty($total_guest_fixed) && $total_guest_fixed != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . ' x ' . $guests_participating . ' guests  </div><div class="details-right">' . esc_attr($total_guest_fixed) . '</div></li>';
            }
            if (!empty($total_group_hourly) && $total_group_hourly != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . ' x ' . $no_of_hours . ' hours </div><div class="details-right">' . esc_attr($total_group_hourly) . '</div></li>';
            }
            if (!empty($total_group_fixed) && $total_group_fixed != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . ' per group  </div><div class="details-right">' . esc_attr($total_group_fixed) . '</div></li>';
            }
            if (!empty($total_flat_hourly) && $total_flat_hourly != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . ' x ' . $no_of_hours . ' hours  </div><div class="details-right">' . esc_attr($total_flat_hourly) . '</div></li>';
            }
            if (!empty($total_flat_fixed) && $total_flat_fixed != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . '</div><div class="details-right">' . esc_attr($total_flat_fixed) . '</div></li>';
            }

            if ($total_non_participants_price != 0) {
                $output .= '<li> Extra Guests ' . $non_participants_price . ' x ' . $extra_participants . ' guests <span>' . esc_attr($total_non_participants_price) . '</span></li>';
            }

            if (!empty($extra_equipments_html)) {
                $output .= '<strong style="font-size: 13px;">Equipments For Rental</strong>';
                $output .= $extra_equipments_html;
            }

            if ($have_guided_service == 'guide_is_optional' && homey_is_renter() && $reservation_status == 'under_review') {
                $output .= '<li><button id="cancelGuidedServiceButton" data-reservation-id="' . $reservation_id . '">Cancel Guided Service</button></li>';
            }
        }

        if (!empty($reservation_meta['total_accomodation_fee']) && $reservation_meta['total_accomodation_fee'] != 0) {
            $output .= '<strong>SLEEPING ACCOMODATION</strong>';
            $output .= '<li>Lodging (' . $accommodation_number . ' x ' . $accomodation_fee . ') <span>' . esc_attr($total_accomodation_fee) . '</span></li>';
        }

        if (!empty($reservation_meta['cleaning_fee']) && $reservation_meta['cleaning_fee'] != 0 && $reservation_meta['total_accomodation_fee'] != 0) {
            $output .= '<li>' . $local['cs_cleaning_fee'] . ' <span>' . $cleaning_fee . '</span></li>';
        }

        if (!empty($additional_vehicles_fee) && $additional_vehicles_fee != 0) {
            $output .= '<strong>ADDITIONAL VEHICLES</strong>';
            $output .= '<li>' . $additional_vehicles . ' Vehicles x ' . $cost_per_additional_car . '<span>' . homey_formatted_price($additional_vehicles_fee) . '</span></li>';
        }

        if (!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
            $output .= '<li>' . $local['cs_city_fee'] . ' <span>' . $city_fee . '</span></li>';
        }

        if (!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>' . $local['cs_sec_deposit'] . ' <span>' . homey_formatted_price($security_deposit) . '</span></li>';
        }

        if (!empty($extra_expenses)) {
            $output .= $extra_expenses['expenses_html'];
        }

        if (!empty($extra_discount)) {
            $output .= $extra_discount['discount_html'];
        }

        if (!empty($coupon_discount) && $coupon_discount != 0) {
            $output .= '<li>Coupon Discount<span>-$' . esc_attr($coupon_discount) . '</span></li>';
        }

        if (!empty($services_fee) && $services_fee != 0) {
            $output .= '<li>Backyard Lease Service Fee <span>' . homey_formatted_price($services_fee) . '</span></li>';
        }

        if (!empty($occ_tax_amount) && $occ_tax_amount != 0) {
            $output .= '<li><strong>Occupancy Tax ' . $occupancy_tax_rate . '%  (' . ucwords($current_occupancy_state) . ')</strong> <span>' . $occ_tax_amount . '</span></li>';
        }

        if (!empty($total_state_tax)) {
            $output .= '<li><strong>Sales Tax (' . ucwords($tour_state) . ')</strong> <span>' . $total_state_tax . '</span></li>';
        }

        if (!empty($taxes) && $taxes != 0) {
            $output .= '<li>' . $local['cs_taxes'] . ' ' . $taxes_percent . '% <span>' . homey_formatted_price($taxes) . '</span></li>';
        }

        if (!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">TOTAL <span>' . homey_formatted_price($upfront_payment > 0 ? $upfront_payment : 0) . '</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="' . $upfront_payment . '">';
        } else {
            $output .= '<li class="payment-due">TOTAL <span>' . homey_formatted_price(0) . '</span></li>';
        }

        if (!empty($balance) && $balance > 0) {
            $output .= '<li><i class="fa fa-info-circle"></i> ' . $local['cs_pay_rest_1'] . ' ' . homey_formatted_price($balance > 0 ? $balance : 0) . ' ' . $local['cs_pay_rest_2'] . '</li>';
        }

        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

if (!function_exists('homey_calculate_hourly_booking_cost_admin')) {
    function homey_calculate_hourly_booking_cost_admin($reservation_id, $collapse = false)
    {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if (empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);

        $listing_id = intval($reservation_meta['listing_id']);
        $check_in_date = wp_kses($reservation_meta['check_in_date'], $allowded_html);
        $check_in_hour = wp_kses($reservation_meta['check_in_hour'], $allowded_html);
        $check_out_hour = wp_kses($reservation_meta['check_out_hour'], $allowded_html);
        $guests = intval($reservation_meta['guests']);

        $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests);

        $price_per_hour = homey_formatted_price($prices_array['price_per_hour'], true);
        $no_of_hours = $prices_array['hours_count'];

        $hours_total_price = homey_formatted_price($prices_array['hours_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if ($no_of_hours > 1) {
            $hour_label = $local['hours_label'];
        } else {
            $hour_label = $local['hour_label'];
        }

        if ($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<tr>
                    <td class="manage-column">' . $no_of_hours . ' ' . $hour_label . ' (' . $local['with_custom_period_and_weekend_label'] . ')</td>
                    <td>' . $hours_total_price . '</td>
                    </tr>';
        } elseif ($booking_has_weekend == 1) {
            $output .= '<tr>
                <td class="manage-column">' . esc_attr($price_per_hour) . ' x ' . $no_of_hours . ' ' . $hour_label . ' (' . $with_weekend_label . ') </td>
                <td>' . $hours_total_price . '</td>
                </tr>';
        } elseif ($booking_has_custom_pricing == 1) {
            $output .= '<tr>
                <td class="manage-column">' . $no_of_hours . ' ' . $hour_label . ' (' . $local['with_custom_period_label'] . ') </td>
                <td>' . $hours_total_price . '</td>
                </tr>';
        } else {
            $output .= '<tr>
                <td class="manage-column">' . $price_per_hour . ' x ' . $no_of_hours . ' ' . $hour_label . ' </td>
                <td>' . $hours_total_price . '</td>
                </tr>';
        }

        if (!empty($additional_guests)) {
            $output .= '<tr><td class="manage-column">' . $additional_guests . ' ' . $add_guest_label . '</td> <td>' . homey_formatted_price($additional_guests_total_price) . '</td></tr>';
        }

        $output .= '<tr><td class="manage-column">' . $local['cs_cleaning_fee'] . '</td> <td>' . $cleaning_fee . '</td></tr>';
        $output .= '<tr><td class="manage-column">' . $local['cs_city_fee'] . '</td> <td>' . $city_fee . '</td></tr>';

        if (!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<tr><td class="manage-column">' . $local['cs_sec_deposit'] . '</td> <td>' . homey_formatted_price($security_deposit) . '</td></tr>';
        }

        if (!empty($services_fee) && $services_fee != 0) {
            $output .= '<tr><td class="manage-column">' . $local['cs_services_fee'] . '</td> <td>' . homey_formatted_price($services_fee) . '</td></tr>';
        }

        if (!empty($taxes) && $taxes != 0) {
            $output .= '<tr><td class="manage-column">' . $local['cs_taxes'] . ' ' . $taxes_percent . '%</td> <td>' . homey_formatted_price($taxes) . '</td></tr>';
        }


        $output .= '<tr class="payment-due"><td class="manage-column"><strong>' . $local['cs_total'] . '</strong></td> <td><strong>' . homey_formatted_price($total_price) . '</strong></td></tr>';


        if (!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<tr class="payment-due"><td class="manage-column"><strong>' . $local['cs_payment_due'] . '</strong></td> <td><strong>' . homey_formatted_price($upfront_payment) . '</strong></td></tr>';
        }

        if (!empty($balance) && $balance != 0) {
            $output .= '<tr><td class="manage-column"><i class="fa fa-info-circle"></i> ' . $local['cs_pay_rest_1'] . ' <strong>' . homey_formatted_price($balance) . '</strong> ' . $local['cs_pay_rest_2'] . '</td></tr>';
        }



        return $output;
    }
}

if (!function_exists('homey_calculate_hourly_reservation_cost_admin')) {
    function homey_calculate_hourly_reservation_cost_admin($reservation_id, $collapse = false)
    {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if (empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);

        $listing_id = intval($reservation_meta['listing_id']);
        $check_in_date = wp_kses($reservation_meta['check_in_date'], $allowded_html);
        $check_in_hour = wp_kses($reservation_meta['check_in_hour'], $allowded_html);
        $check_out_hour = wp_kses($reservation_meta['check_out_hour'], $allowded_html);
        $guests = intval($reservation_meta['guests']);

        $price_per_hour = homey_formatted_price($reservation_meta['price_per_hour'], true);
        $no_of_hours = $reservation_meta['no_of_hours'];

        $hours_total_price = homey_formatted_price($reservation_meta['hours_total_price'], false);

        $cleaning_fee = homey_formatted_price($reservation_meta['cleaning_fee']);
        $services_fee = $reservation_meta['services_fee'];
        $taxes = $reservation_meta['taxes'];
        $taxes_percent = $reservation_meta['taxes_percent'];
        $city_fee = homey_formatted_price($reservation_meta['city_fee']);
        $security_deposit = $reservation_meta['security_deposit'];
        $additional_guests = $reservation_meta['additional_guests'];
        $additional_guests_price = $reservation_meta['additional_guests_price'];
        $additional_guests_total_price = $reservation_meta['additional_guests_total_price'];

        $upfront_payment = $reservation_meta['upfront'];
        $balance = $reservation_meta['balance'];
        $total_price = $reservation_meta['total'];

        $booking_has_weekend = $reservation_meta['booking_has_weekend'];
        $booking_has_custom_pricing = $reservation_meta['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if ($no_of_hours > 1) {
            $hour_label = $local['hours_label'];
        } else {
            $hour_label = $local['hour_label'];
        }

        if ($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<tr>
                    <td class="manage-column">' . $no_of_hours . ' ' . $hour_label . ' (' . $local['with_custom_period_and_weekend_label'] . ')</td>
                    <td>' . $hours_total_price . '</td>
                    </tr>';
        } elseif ($booking_has_weekend == 1) {
            $output .= '<tr>
                <td class="manage-column">' . esc_attr($price_per_hour) . ' x ' . $no_of_hours . ' ' . $hour_label . ' (' . $with_weekend_label . ') </td>
                <td>' . $hours_total_price . '</td>
                </tr>';
        } elseif ($booking_has_custom_pricing == 1) {
            $output .= '<tr>
                <td class="manage-column">' . $no_of_hours . ' ' . $hour_label . ' (' . $local['with_custom_period_label'] . ') </td>
                <td>' . $hours_total_price . '</td>
                </tr>';
        } else {
            $output .= '<tr>
                <td class="manage-column">' . $price_per_hour . ' x ' . $no_of_hours . ' ' . $hour_label . ' </td>
                <td>' . $hours_total_price . '</td>
                </tr>';
        }

        if (!empty($additional_guests)) {
            $output .= '<tr><td class="manage-column">' . $additional_guests . ' ' . $add_guest_label . '</td> <td>' . homey_formatted_price($additional_guests_total_price) . '</td></tr>';
        }

        $output .= '<tr><td class="manage-column">' . $local['cs_cleaning_fee'] . '</td> <td>' . $cleaning_fee . '</td></tr>';
        $output .= '<tr><td class="manage-column">' . $local['cs_city_fee'] . '</td> <td>' . $city_fee . '</td></tr>';

        if (!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<tr><td class="manage-column">' . $local['cs_sec_deposit'] . '</td> <td>' . homey_formatted_price($security_deposit) . '</td></tr>';
        }

        if (!empty($services_fee) && $services_fee != 0) {
            $output .= '<tr><td class="manage-column">' . $local['cs_services_fee'] . '</td> <td>' . homey_formatted_price($services_fee) . '</td></tr>';
        }

        if (!empty($taxes) && $taxes != 0) {
            $output .= '<tr><td class="manage-column">' . $local['cs_taxes'] . ' ' . $taxes_percent . '%</td> <td>' . homey_formatted_price($taxes) . '</td></tr>';
        }


        $output .= '<tr class="payment-due"><td class="manage-column"><strong>' . $local['cs_total'] . '</strong></td> <td><strong>' . homey_formatted_price($total_price) . '</strong></td></tr>';


        if (!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<tr class="payment-due"><td class="manage-column"><strong>' . $local['cs_payment_due'] . '</strong></td> <td><strong>' . homey_formatted_price($upfront_payment) . '</strong></td></tr>';
        }

        if (!empty($balance) && $balance != 0) {
            $output .= '<tr><td class="manage-column"><i class="fa fa-info-circle"></i> ' . $local['cs_pay_rest_1'] . ' <strong>' . homey_formatted_price($balance) . '</strong> ' . $local['cs_pay_rest_2'] . '</td></tr>';
        }



        return $output;
    }
}

if (!function_exists('homey_calculate_hourly_booking_cost_instance')) {
    function homey_calculate_hourly_booking_cost_instance()
    {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $listing_id = intval($_GET['listing_id']);
        $check_in_date = wp_kses($_GET['check_in'], $allowded_html);
        $start_hour = wp_kses($_GET['start_hour'], $allowded_html);
        $end_hour = wp_kses($_GET['end_hour'], $allowded_html);
        $guests = intval($_GET['guest']);
        $extra_options = isset($_GET['extra_options']) ? $_GET['extra_options'] : '';
        $accommodation_number = isset($_GET['accommodation_number']) ? $_GET['accommodation_number'] : '';
        $guests_participating = isset($_GET['guests_participating']) ? $_GET['guests_participating'] : '';
        $extra_participants = isset($_GET['extra_participants']) ? $_GET['extra_participants'] : '';
        $choose_guided_service = isset($_GET['choose_guided_service']) ? $_GET['choose_guided_service'] : '';
        $extra_equipments = isset($_GET['extra_equipments']) ? $_GET['extra_equipments'] : '';
        $additional_vehicles = isset($_GET['additional_vehicles']) ? $_GET['additional_vehicles'] : '';
        $coupon_code = isset($_GET['coupon_code']) ? $_GET['coupon_code'] : '';

        $have_sleeping_accommodations = get_field('field_6479eb9f0208c', $listing_id);
        $include_backyard_amenity = get_field('include_backyard_amenity', $listing_id);

        $amenity_price_type = get_field('amenity_price_type', $listing_id);

        $occupancy_tax_rate = get_field('occupancy_tax_rate', $listing_id);
        $current_occupancy_state = get_field('current_occupancy_state', $listing_id);
        $tour_state = homey_get_taxonomy_title($listing_id, 'listing_state');
        $have_guided_service = get_field('have_guided_service', $listing_id);
        $flag = 0;
        if ($have_guided_service == 'guide_required') {
            $flag = 1;
        } else {
            if ($have_guided_service == 'guide_is_optional') {
                if (isset($choose_guided_service) && $choose_guided_service == 'on') {
                    $flag = 1;
                }
            }
        }

        $check_in_hour = $check_in_date . ' ' . $start_hour;
        $check_out_hour = $check_in_date . ' ' . $end_hour;

        $current_user_id = get_current_user_id();
        $coupon_id = homey_validate_coupon($coupon_code, $listing_id, $current_user_id);

        $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests, $extra_options, $accommodation_number, $guests_participating, $extra_participants, $choose_guided_service, $extra_equipments, $additional_vehicles, $coupon_id);

        $price_per_hour = homey_formatted_price($prices_array['price_per_hour'], true);
        $no_of_hours = $prices_array['hours_count'];

        $hours_total_price = homey_formatted_price($prices_array['hours_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $total_accomodation_fee = homey_formatted_price($prices_array['total_accomodation_fee']);
        $guided_fee = homey_formatted_price($prices_array['guided_fee'], true);
        $gears_price = homey_formatted_price($prices_array['gears_price'], true);
        $total_gears_price = homey_formatted_price($prices_array['total_gears_price']);
        $total_guest_hourly = homey_formatted_price($prices_array['total_guest_hourly']);
        $total_guest_fixed = homey_formatted_price($prices_array['total_guest_fixed']);
        $total_group_hourly = homey_formatted_price($prices_array['total_group_hourly']);
        $total_group_fixed = homey_formatted_price($prices_array['total_group_fixed']);
        $total_flat_hourly = homey_formatted_price($prices_array['total_flat_hourly']);
        $total_flat_fixed = homey_formatted_price($prices_array['total_flat_fixed']);
        $non_participants_price = homey_formatted_price($prices_array['non_participants_price'], true);
        $total_non_participants_price = homey_formatted_price($prices_array['total_non_participants_price']);
        $occ_tax_amount = homey_formatted_price($prices_array['occ_tax_amount']);
        $total_state_tax = homey_formatted_price($prices_array['total_state_tax']);
        $cost_per_additional_car = homey_formatted_price($prices_array['cost_per_additional_car']);
        $additional_vehicles = $prices_array['additional_vehicles'];
        $additional_vehicles_fee = $prices_array['additional_vehicles_fee'];
        $extra_equipments_html = $prices_array['extra_equipments_html'];

        $amenity_value = $prices_array['amenity_value'];


        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        $extra_prices_html = $prices_array['extra_prices_html'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if ($no_of_hours > 1) {
            $hour_label = $local['hours_label'];
        } else {
            $hour_label = $local['hour_label'];
        }

        if ($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $output = '<div class="payment-list-price-detail clearfix">';
        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">Total (USD)</div>';
        // $output .= '<div class="payment-list-price-detail-note">' . $local['cs_tax_fees'] . '</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">' . homey_formatted_price($total_price) . '</div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">' . $local['cs_view_details'] . '</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="collapse collapseExample" id="collapseExample">';
        $output .= '<ul>';

        if ($amenity_value === 'available') {
            $output .= '<strong>AMENITY</strong>';

            if ($amenity_price_type == 'price_per_hour') {
                if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($local['with_custom_period_and_weekend_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . ' x ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($with_weekend_label) . ') <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_custom_pricing == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($local['with_custom_period_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } else {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . ' x ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                }
            } elseif ($amenity_price_type == 'price_per_day') {
                if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure (' . esc_attr($local['with_custom_period_and_weekend_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . '/Day (' . esc_attr($with_weekend_label) . ') <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_custom_pricing == 1) {
                    $output .= '<li>Backyard Adventure (' . esc_attr($local['with_custom_period_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } else {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . '/Day <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                }
            } elseif ($amenity_price_type == 'price_per_half_day') {
                if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure (' . esc_attr($local['with_custom_period_and_weekend_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . '/Half Day (' . esc_attr($with_weekend_label) . ') <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_custom_pricing == 1) {
                    $output .= '<li>Backyard Adventure (' . esc_attr($local['with_custom_period_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } else {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . '/Half Day <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                }
            } else {
                if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($local['with_custom_period_and_weekend_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_weekend == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . ' x ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($with_weekend_label) . ') <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } elseif ($booking_has_custom_pricing == 1) {
                    $output .= '<li>Backyard Adventure ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' (' . esc_attr($local['with_custom_period_label']) . ') <span>' . esc_attr($hours_total_price) . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                } else {
                    $output .= '<li>Backyard Adventure ' . esc_attr($price_per_hour) . ' x ' . esc_attr($no_of_hours) . ' ' . esc_attr($hour_label) . ' <span>' . $hours_total_price . '</span></li>';
                    if ($have_sleeping_accommodations == 'yes' && $include_backyard_amenity == 'yes' && $accommodation_number != 0) {
                        $output .= '<span style="display:block;padding-bottom: 5px;">(Amenity is included with your sleeping accommodation)</span>';
                    }
                }
            }
        }

        if (!empty($extra_prices_html)) {
            $output .= '<strong style="font-size: 16px;">Add-On Services</strong>';
            $output .= $extra_prices_html;
        }

        if (!empty($additional_guests)) {
            $output .= '<li>Extra Guests ' . esc_attr($additional_guests) . ' ' . esc_attr($add_guest_label) . ' <span>' . homey_formatted_price($additional_guests_total_price) . '</span></li>';
        }

        if ($flag == 1) {
            $output .= '<strong>GUIDED SERVICE</br></strong>';
            if (!empty($total_guest_hourly) && $total_guest_hourly != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . ' x ' . $no_of_hours . ' hours x ' . $prices_array['total_participants'] . ' guests  </div><div class="details-right">' . esc_attr($total_guest_hourly) . '</div></li>';
            }
            if (!empty($total_guest_fixed) && $total_guest_fixed != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . ' x ' . $prices_array['total_participants'] . ' guests  </div><div class="details-right">' . esc_attr($total_guest_fixed) . '</div></li>';
            }
            if (!empty($total_group_hourly) && $total_group_hourly != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . ' x ' . $no_of_hours . ' hours </div><div class="details-right">' . esc_attr($total_group_hourly) . '</div></li>';
            }
            if (!empty($total_group_fixed) && $total_group_fixed != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . ' per group  </div><div class="details-right">' . esc_attr($total_group_fixed) . '</div></li>';
            }
            if (!empty($total_flat_hourly) && $total_flat_hourly != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . ' x ' . $no_of_hours . ' hours  </div><div class="details-right">' . esc_attr($total_flat_hourly) . '</div></li>';
            }
            if (!empty($total_flat_fixed) && $total_flat_fixed != 0) {
                $output .= '<li><div class="details-left"> Expert Guide ' . $guided_fee . '</div><div class="details-right">' . esc_attr($total_flat_fixed) . '</div></li>';
            }

            if ($prices_array['total_non_participants_price'] != 0) {
                $output .= '<li> Extra Guests ' . $non_participants_price . ' x ' . $extra_participants . ' guests <span>' . esc_attr($total_non_participants_price) . '</span></li>';
            }

            if (!empty($extra_equipments_html)) {
                $output .= '<strong style="font-size: 16px;">Equipments For Rental</strong>';
                $output .= $extra_equipments_html;
            }
        }

        if (!empty($prices_array['total_accomodation_fee']) && $prices_array['total_accomodation_fee'] != 0) {
            $output .= '<strong>SLEEPING ACCOMMODATION</strong>';
            $output .= '<li> Lodging ' . $accommodation_number . ' x night <span>' . esc_attr($total_accomodation_fee) . '</span></li>';

            if (!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
                $output .= '<li>' . esc_attr($local['cs_cleaning_fee']) . ' <span>' . esc_attr($cleaning_fee) . '</span></li>';
            }
        }


        if (!empty($additional_vehicles_fee) && $additional_vehicles_fee != 0) {
            $output .= '<strong>ADDITIONAL VEHICLES</strong>';
            $output .= '<li>' . $additional_vehicles . ' Vehicles x ' . $cost_per_additional_car . '<span>' . homey_formatted_price($additional_vehicles_fee) . '</span></li>';
        }

        if (!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>' . $local['cs_city_fee'] . ' <span>' . $city_fee . '</span></li>';
        }

        if (!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>' . $local['cs_sec_deposit'] . ' <span>' . homey_formatted_price($security_deposit) . '</span></li>';
        }

        if (!empty($services_fee) && $services_fee != 0) {
            $output .= '<li>' . $local['cs_services_fee'] . ' <span>' . homey_formatted_price($services_fee) . '</span></li>';
        }

        if (!empty($occ_tax_amount) && $occ_tax_amount != 0) {
            $output .= '<li><strong>Occupancy Tax ' . $occupancy_tax_rate . '%  (' . ucwords($current_occupancy_state) . ')</strong> <span>' . $occ_tax_amount . '</span></li>';
        }

        if (!empty($total_state_tax)) {
            $output .= '<li><strong>Sales Tax (' . ucwords($tour_state) . ')</strong> <span>' . $total_state_tax . '</span></li>';
        }

        if (!empty($taxes) && $taxes != 0) {
            $output .= '<li>' . $local['cs_taxes'] . ' ' . $taxes_percent . '% <span>' . homey_formatted_price($taxes) . '</span></li>';
        }

        if (!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="' . $upfront_payment . '">';
        }

        if (!empty($balance) && $balance != 0) {
            $output .= '<li><i class="fa fa-info-circle"></i> ' . $local['cs_pay_rest_1'] . ' ' . homey_formatted_price($balance) . ' ' . $local['cs_pay_rest_2'] . '</li>';
        }
        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

/* -----------------------------------------------------------------------------------------------------------
*  Stripe Form
-------------------------------------------------------------------------------------------------------------*/
if (!function_exists('homey_hourly_stripe_payment')) {
    function homey_hourly_stripe_payment($reservation_id)
    {

        $allowded_html = array();

        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $user_email = $current_user->user_email;

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);


        $listing_id = intval($reservation_meta['listing_id']);
        $check_in_hour = wp_kses($reservation_meta['check_in_hour'], $allowded_html);
        $check_out_hour = wp_kses($reservation_meta['check_out_hour'], $allowded_html);
        $guests = intval($reservation_meta['guests']);

        $upfront_payment = floatval($reservation_meta['upfront']);

        if ($upfront_payment < .5) {
            echo $minimum_amount_error = esc_html__("You can't pay using Stripe because minimum amount limit is 0.5", 'homey');
            return $minimum_amount_error;
        }

        require_once(HOMEY_PLUGIN_PATH . '/classes/class-stripe.php');

        $description = esc_html__('Reservation ID', 'homey') . ' ' . $reservation_id;

        $stripe_payments = new Homey_Stripe();

        print '<div class="stripe-wrapper" id="homey_stripe_simple"> ';
        $metadata = array(
            'userID' => $userID,
            'reservation_id_for_stripe' => $reservation_id,
            'is_hourly' => 1,
            'is_instance_booking' => 0,
            'extra_options' => 0,
            'payment_type' => 'reservation_fee',
            'message' => esc_html__('Reservation Payment', 'homey')
        );

        $stripe_payments->homey_stripe_form($upfront_payment, $metadata, $description);
        print '
        </div>';
    }
}

if (!function_exists('homey_hourly_stripe_payment_old')) {
    function homey_hourly_stripe_payment_old($reservation_id)
    {

        require_once(HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php');
        $stripe_secret_key = homey_option('stripe_secret_key');
        $stripe_publishable_key = homey_option('stripe_publishable_key');
        $allowded_html = array();

        $stripe = array(
            "secret_key" => $stripe_secret_key,
            "publishable_key" => $stripe_publishable_key
        );

        \Stripe\Stripe::setApiKey($stripe['secret_key']);
        $submission_currency = homey_option('payment_currency');
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $user_email = $current_user->user_email;

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);


        $listing_id = intval($reservation_meta['listing_id']);
        $check_in_hour = wp_kses($reservation_meta['check_in_hour'], $allowded_html);
        $check_out_hour = wp_kses($reservation_meta['check_out_hour'], $allowded_html);
        $guests = intval($reservation_meta['guests']);

        //$prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests);

        $upfront_payment = floatval($reservation_meta['upfront']);

        if ($submission_currency == 'JPY') {
            $upfront_payment = $upfront_payment;
        } else {
            $upfront_payment = $upfront_payment * 100;
        }


        print '
        <div class="homey_stripe_simple">
            <script src="https://checkout.stripe.com/checkout.js"
            class="stripe-button"
            data-key="' . $stripe_publishable_key . '"
            data-amount="' . $upfront_payment . '"
            data-email="' . $user_email . '"
            data-zip-code="true"
            data-billing-address="true"
            data-locale="' . get_locale() . '"
            data-currency="' . $submission_currency . '"
            data-label="' . esc_html__('Pay with Credit Card', 'homey') . '"
            data-description="' . esc_html__('Reservation Payment', 'homey') . '">
            </script>
        </div>
        <input type="hidden" id="reservation_id_for_stripe" name="reservation_id_for_stripe" value="' . $reservation_id . '">
        <input type="hidden" id="reservation_pay" name="reservation_pay" value="1">
        <input type="hidden" id="is_hourly" name="is_hourly" value="1">
        <input type="hidden" id="is_instance_booking" name="is_instance_booking" value="0">
        <input type="hidden" name="extra_options" value="0">
        <input type="hidden" name="userID" value="' . $userID . '">
        <input type="hidden" id="pay_ammout" name="pay_ammout" value="' . $upfront_payment . '">
        ';
    }
}

/* -----------------------------------------------------------------------------------------------------------
*  Stripe Form instance
-------------------------------------------------------------------------------------------------------------*/
if (!function_exists('homey_hourly_stripe_payment_instance')) {
    function homey_hourly_stripe_payment_instance($listing_id, $check_in, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $guests)
    {

        $allowded_html = array();
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $user_email = $current_user->user_email;

        $listing_id = intval($listing_id);
        $check_in_date = wp_kses($check_in, $allowded_html);
        $renter_message = '';
        $guests = intval($guests);

        $check_availability = check_hourly_booking_availability($check_in_date, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $listing_id, $guests);
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        $extra_options = isset($_GET['extra_options']) ? $_GET['extra_options'] : '';

        update_user_meta($userID, 'extra_prices', $extra_options);

        if (!empty($extra_options)) {
            $extra_prices = 1;
        } else {
            $extra_prices = 0;
        }

        if (!$is_available) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $check_message,
                    'payment_execute_url' => ''
                )
            );
            wp_die();
        } else {

            $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests, $extra_options);
            $upfront_payment = floatval($prices_array['upfront_payment']);
        }

        if ($upfront_payment < .5) {
            echo $minimum_amount_error = esc_html__("You can't pay using Stripe because minimum amount limit is 0.5", 'homey');
            return $minimum_amount_error;
        }

        require_once(HOMEY_PLUGIN_PATH . '/classes/class-stripe.php');

        $stripe_payments = new Homey_Stripe();

        $description = esc_html__('Instant Reservation, Listing ID', 'homey') . ' ' . $listing_id;

        print '<div class="stripe-wrapper" id="homey_stripe_simple"> ';
        $metadata = array(
            'userID' => $userID,
            'listing_id' => $listing_id,
            'check_in_date' => $check_in_date,
            'check_in_hour' => $check_in_hour,
            'check_out_hour' => $check_out_hour,
            'start_hour' => $start_hour,
            'end_hour' => $end_hour,
            'guests' => $guests,
            'extra_options' => $extra_prices,
            'is_hourly' => 1,
            'is_instance_booking' => 1,
            'extra_options' => 0,
            'payment_type' => 'reservation_fee',
            'guest_message' => $renter_message,
            'reservation_id_for_stripe' => 0,
            'message' => esc_html__('Reservation Payment', 'homey')
        );

        $stripe_payments->homey_stripe_form($upfront_payment, $metadata, $description);
        print '
        </div>';
    }
}

if (!function_exists('homey_memberships_stripe_payment_instance')) {
    function homey_memberships_stripe_payment_instance($stripe_package_id)
    {

        $allowded_html = array();
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $user_email = $current_user->user_email;

        if (!$stripe_package_id) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => "Please select proper package for membership.",
                    'payment_execute_url' => ''
                )
            );
            wp_die();
        }

        require_once(HOMEY_PLUGIN_PATH . '/classes/class-stripe.php');
        $stripe_payments = new Homey_Stripe();
        $description = esc_html__('Membership Payment, Plan ID', 'homey') . ' ' . $stripe_package_id;

        print '<div class="stripe-wrapper" id="homey_stripe_simple"> ';
        $metadata = array(
            'userID' => $userID,
            'stripe_package_id' => $stripe_package_id,
            'is_recurring' => 1,
            'payment_type' => 'membership_fee',
            'redirect_type' => 'typ_membership_fee',
            'message' => esc_html__('Membership Payment', 'homey')
        );

        $stripe_payments->homey_stripe_membership_form($stripe_package_id, $metadata);
        print '
        </div>';
    }
}

if (!function_exists('homey_hourly_stripe_payment_instance_old')) {
    function homey_hourly_stripe_payment_instance_old($listing_id, $check_in, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $guests)
    {

        require_once(HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php');
        $stripe_secret_key = homey_option('stripe_secret_key');
        $stripe_publishable_key = homey_option('stripe_publishable_key');
        $allowded_html = array();

        $stripe = array(
            "secret_key" => $stripe_secret_key,
            "publishable_key" => $stripe_publishable_key
        );

        \Stripe\Stripe::setApiKey($stripe['secret_key']);
        $submission_currency = homey_option('payment_currency');
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $user_email = $current_user->user_email;

        $listing_id = intval($listing_id);
        $check_in_date = wp_kses($check_in, $allowded_html);
        $renter_message = '';
        $guests = intval($guests);

        $check_availability = check_hourly_booking_availability($check_in_date, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $listing_id, $guests);
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        $extra_options = isset($_GET['extra_options']) ? $_GET['extra_options'] : '';

        update_user_meta($userID, 'extra_prices', $extra_options);

        if (!empty($extra_options)) {
            $extra_prices = 1;
        } else {
            $extra_prices = 0;
        }

        if (!$is_available) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $check_message,
                    'payment_execute_url' => ''
                )
            );
            wp_die();
        } else {

            $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests, $extra_options);
            $upfront_payment = floatval($prices_array['upfront_payment']);
        }

        if ($submission_currency == 'JPY') {
            $upfront_payment = $upfront_payment;
        } else {
            $upfront_payment = $upfront_payment * 100;
        }


        print '
        <div class="homey_stripe_simple">
            <script src="https://checkout.stripe.com/checkout.js"
            class="stripe-button"
            data-key="' . $stripe_publishable_key . '"
            data-amount="' . $upfront_payment . '"
            data-email="' . $user_email . '"
            data-zip-code="true"
            data-billing-address="true"
            data-locale="' . get_locale() . '"
            data-currency="' . $submission_currency . '"
            data-label="' . esc_html__('Pay with Credit Card', 'homey') . '"
            data-description="' . esc_html__('Reservation Payment', 'homey') . '">
            </script>
        </div>
        <input type="hidden" id="reservation_id_for_stripe" name="reservation_id_for_stripe" value="0">
        <input type="hidden" id="reservation_pay" name="reservation_pay" value="1">
        <input type="hidden" id="is_instance_booking" name="is_instance_booking" value="1">
        <input type="hidden" name="check_in_date" value="' . $check_in_date . '">
        <input type="hidden" name="check_in_hour" value="' . $check_in_hour . '">
        <input type="hidden" name="check_out_hour" value="' . $check_out_hour . '">
        <input type="hidden" name="start_hour" value="' . $start_hour . '">
        <input type="hidden" name="end_hour" value="' . $end_hour . '">
        <input type="hidden" name="guests" value="' . $guests . '">
        <input type="hidden" name="extra_options" value="' . $extra_prices . '">
        <input type="hidden" name="listing_id" value="' . $listing_id . '">
        <input type="hidden" id="is_hourly" name="is_hourly" value="1">
        <input type="hidden" id="guest_message" name="guest_message" value="' . $renter_message . '">
        <input type="hidden" name="userID" value="' . $userID . '">
        <input type="hidden" id="pay_ammout" name="pay_ammout" value="' . $upfront_payment . '">
        ';
    }
}

add_action('wp_ajax_homey_hourly_booking_paypal_payment', 'homey_hourly_booking_paypal_payment');
if (!function_exists('homey_hourly_booking_paypal_payment')) {
    function homey_hourly_booking_paypal_payment()
    {
        global $current_user;
        $allowded_html = array();
        $blogInfo = esc_url(home_url('/'));
        wp_get_current_user();
        $userID = $current_user->ID;
        $local = homey_get_localization();
        $reservation_id = intval($_POST['reservation_id']);

        //check security
        $nonce = $_REQUEST['security'];
        if (!wp_verify_nonce($nonce, 'checkout-security-nonce')) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['security_check_text']
                )
            );
            wp_die();
        }

        if (empty($reservation_id)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['something_went_wrong']
                )
            );
            wp_die();
        }

        $reservation = get_post($reservation_id);

        if ($reservation->post_author != $userID) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['belong_to']
                )
            );
            wp_die();
        }
        $reservation_status = get_post_meta($reservation_id, 'reservation_status', true);

        if ($reservation_status != 'available') {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['something_went_wrong']
                )
            );
            wp_die();
        }


        $currency = homey_option('payment_currency');
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);

        $listing_id = intval($reservation_meta['listing_id']);
        $check_in_hour = wp_kses($reservation_meta['check_in_hour'], $allowded_html);
        $check_out_hour = wp_kses($reservation_meta['check_out_hour'], $allowded_html);
        $guests = intval($reservation_meta['guests']);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests, $extra_options);


        $is_paypal_live = homey_option('paypal_api');
        $host = 'https://api.sandbox.paypal.com';
        $upfront_payment = floatval($reservation_meta['upfront']);
        $submission_curency = esc_html($currency);
        $payment_description = esc_html__('Reservation payment on ', 'homey') . $blogInfo;

        $total_price = number_format($upfront_payment, 2, '.', '');

        // Check if payal live
        if ($is_paypal_live == 'live') {
            $host = 'https://api.paypal.com';
        }

        $url = $host . '/v1/oauth2/token';
        $postArgs = 'grant_type=client_credentials';

        // Get Access token
        $paypal_token = homey_get_paypal_access_token($url, $postArgs);
        $url = $host . '/v1/payments/payment';

        $payment_page_link = homey_get_template_link_2('template/dashboard-payment.php');
        $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');

        $cancel_link = add_query_arg(array('reservation_id' => $reservation_id), $payment_page_link);
        $return_link = add_query_arg('reservation_detail', $reservation_id, $reservation_page_link);

        $payment = array(
            'intent' => 'sale',
            "redirect_urls" => array(
                "return_url" => $return_link,
                "cancel_url" => $cancel_link
            ),
            'payer' => array("payment_method" => "paypal"),
        );

        /* Prepare basic payment details
         *--------------------------------------*/
        $payment['transactions'][0] = array(
            'amount' => array(
                'total' => $total_price,
                'currency' => $submission_curency,
                'details' => array(
                    'subtotal' => $total_price,
                    'tax' => '0.00',
                    'shipping' => '0.00'
                )
            ),
            'description' => $payment_description
        );


        /* Prepare individual items
         *--------------------------------------*/
        $payment['transactions'][0]['item_list']['items'][] = array(
            'quantity' => '1',
            'name' => esc_html__('Reservation ID', 'homey') . ' ' . $reservation_id . ' ' . esc_html__('Listing ID', 'homey') . ' ' . $listing_id,
            'price' => $total_price,
            'currency' => $submission_curency,
            'sku' => 'Paid Reservation',
        );

        /* Convert PHP array into json format
         *--------------------------------------*/
        $jsonEncode = json_encode($payment);
        $json_response = homey_execute_paypal_request($url, $jsonEncode, $paypal_token);

        //print_r($json_response);
        foreach ($json_response['links'] as $link) {
            if ($link['rel'] == 'execute') {
                $payment_execute_url = $link['href'];
            } else if ($link['rel'] == 'approval_url') {
                $payment_approval_url = $link['href'];
            }
        }

        // Save data in database for further use on processor page
        $output['payment_execute_url'] = $payment_execute_url;
        $output['paypal_token'] = $paypal_token;
        $output['reservation_id'] = $reservation_id;

        $output['listing_id'] = '';
        $output['check_in_date'] = '';
        $output['check_in_hour'] = '';
        $output['check_out_hour'] = '';
        $output['guests'] = '';
        $output['extra_options'] = '';
        $output['renter_message'] = '';
        $output['is_instance_booking'] = 0;
        $output['is_hourly'] = 1;

        $save_output[$userID] = $output;
        update_option('homey_paypal_transfer', $save_output);

        echo json_encode(
            array(
                'success' => true,
                'message' => 'success',
                'payment_execute_url' => $payment_approval_url
            )
        );

        wp_die();
    }
}

add_action('wp_ajax_homey_hourly_instance_booking_paypal_payment', 'homey_hourly_instance_booking_paypal_payment');
if (!function_exists('homey_hourly_instance_booking_paypal_payment')) {
    function homey_hourly_instance_booking_paypal_payment()
    {
        global $current_user;
        $allowded_html = array();
        $blogInfo = esc_url(home_url('/'));
        wp_get_current_user();
        $userID = $current_user->ID;
        $local = homey_get_localization();

        //check security
        $nonce = $_REQUEST['security'];
        //        if ( ! wp_verify_nonce( $nonce, 'checkout-security-nonce' ) ) {
        //
        //            echo json_encode(
        //                array(
        //                    'success' => false,
        //                    'message' => $local['security_check_text']
        //                )
        //            );
        //            wp_die();
        //        }

        $currency = homey_option('payment_currency');

        $listing_id = intval($_POST['listing_id']);
        $check_in_date = wp_kses($_POST['check_in'], $allowded_html);
        $renter_message = wp_kses($_POST['renter_message'], $allowded_html);
        $guests = intval($_POST['guests']);
        $extra_options = $_POST['extra_options'];

        $check_in_hour = wp_kses($_POST['check_in_hour'], $allowded_html);
        $check_out_hour = wp_kses($_POST['check_out_hour'], $allowded_html);
        $start_hour = wp_kses($_POST['start_hour'], $allowded_html);
        $end_hour = wp_kses($_POST['end_hour'], $allowded_html);

        $check_availability = check_hourly_booking_availability($check_in_date, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $listing_id, $guests);

        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if (!$is_available) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $check_message,
                    'payment_execute_url' => ''
                )
            );
            wp_die();
        } else {

            $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests, $extra_options);

            $is_paypal_live = homey_option('paypal_api');
            $host = 'https://api.sandbox.paypal.com';
            $upfront_payment = floatval($prices_array['upfront_payment']);
            $submission_curency = esc_html($currency);
            $payment_description = esc_html__('Reservation payment on ', 'homey') . $blogInfo;

            $total_price = number_format($upfront_payment, 2, '.', '');

            // Check if payal live
            if ($is_paypal_live == 'live') {
                $host = 'https://api.paypal.com';
            }

            $url = $host . '/v1/oauth2/token';
            $postArgs = 'grant_type=client_credentials';

            // Get Access token
            $paypal_token = homey_get_paypal_access_token($url, $postArgs);
            $url = $host . '/v1/payments/payment';

            $instance_payment_page_link = homey_get_template_link_2('template/template-instance-booking.php');
            $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');

            $cancel_link = add_query_arg(
                array(
                    'check_in' => $check_in_date,
                    'start_hour' => $start_hour,
                    'end_hour' => $end_hour,
                    'guest' => $guests,
                    'listing_id' => $listing_id,
                ),
                $instance_payment_page_link
            );

            $return_link = add_query_arg('reservation_detail', $reservation_id, $reservation_page_link);

            $payment = array(
                'intent' => 'sale',
                "redirect_urls" => array(
                    "return_url" => $return_link,
                    "cancel_url" => $cancel_link
                ),
                'payer' => array("payment_method" => "paypal"),
            );

            /* Prepare basic payment details
             *--------------------------------------*/
            $payment['transactions'][0] = array(
                'amount' => array(
                    'total' => $total_price,
                    'currency' => $submission_curency,
                    'details' => array(
                        'subtotal' => $total_price,
                        'tax' => '0.00',
                        'shipping' => '0.00'
                    )
                ),
                'description' => $payment_description
            );


            /* Prepare individual items
             *--------------------------------------*/
            $payment['transactions'][0]['item_list']['items'][] = array(
                'quantity' => '1',
                'name' => esc_html__('Reservation Payment', 'homey'),
                'price' => $total_price,
                'currency' => $submission_curency,
                'sku' => 'Paid Reservation',
            );

            /* Convert PHP array into json format
             *--------------------------------------*/
            $jsonEncode = json_encode($payment);
            $json_response = homey_execute_paypal_request($url, $jsonEncode, $paypal_token);

            //print_r($json_response);
            foreach ($json_response['links'] as $link) {
                if ($link['rel'] == 'execute') {
                    $payment_execute_url = $link['href'];
                } else if ($link['rel'] == 'approval_url') {
                    $payment_approval_url = $link['href'];
                }
            }

            // Save data in database for further use on processor page
            $output['payment_execute_url'] = $payment_execute_url;
            $output['paypal_token'] = $paypal_token;
            $output['reservation_id'] = '';
            $output['listing_id'] = $listing_id;
            $output['check_in_date'] = $check_in_date;
            $output['check_in_hour'] = $check_in_hour;
            $output['check_out_hour'] = $check_out_hour;
            $output['start_hour'] = $start_hour;
            $output['end_hour'] = $end_hour;
            $output['extra_options'] = $extra_options;
            $output['guests'] = $guests;
            $output['renter_message'] = $renter_message;
            $output['is_instance_booking'] = 1;
            $output['is_hourly'] = 1;

            $save_output[$userID] = $output;
            update_option('homey_paypal_transfer', $save_output);

            echo json_encode(
                array(
                    'success' => true,
                    'message' => $local['processing_text'],
                    'payment_execute_url' => $payment_approval_url
                )
            );
            wp_die();
        }
    }
}

add_action('wp_ajax_homey_membership_paypal_payment', 'wp_ajax_homey_membership_paypal_payment');
if (!function_exists('wp_ajax_homey_membership_paypal_payment')) {
    function wp_ajax_homey_membership_paypal_payment()
    {
        global $current_user;

        $allowded_html = array();
        $blogInfo = esc_url(home_url('/'));
        wp_get_current_user();
        $userID = $current_user->ID;
        $local = homey_get_localization();

        //check security
        $nonce = $_REQUEST['security'];
        if (!wp_verify_nonce($nonce, 'checkout-security-nonce')) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['security_check_text']
                )
            );
            wp_die();
        }

        $currency = homey_option('payment_currency');

        $listing_id = intval($_POST['listing_id']);
        $check_in_date = wp_kses($_POST['check_in'], $allowded_html);
        $renter_message = wp_kses($_POST['renter_message'], $allowded_html);
        $guests = intval($_POST['guests']);
        $extra_options = $_POST['extra_options'];

        $check_in_hour = wp_kses($_POST['check_in_hour'], $allowded_html);
        $check_out_hour = wp_kses($_POST['check_out_hour'], $allowded_html);
        $start_hour = wp_kses($_POST['start_hour'], $allowded_html);
        $end_hour = wp_kses($_POST['end_hour'], $allowded_html);

        $check_availability = check_hourly_booking_availability($check_in_date, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $listing_id, $guests);

        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if (!$is_available) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $check_message,
                    'payment_execute_url' => ''
                )
            );
            wp_die();
        } else {

            $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests, $extra_options);

            $is_paypal_live = homey_option('paypal_api');
            $host = 'https://api.sandbox.paypal.com';
            $upfront_payment = floatval($prices_array['upfront_payment']);
            $submission_curency = esc_html($currency);
            $payment_description = esc_html__('Reservation payment on ', 'homey') . $blogInfo;

            $total_price = number_format($upfront_payment, 2, '.', '');

            // Check if payal live
            if ($is_paypal_live == 'live') {
                $host = 'https://api.paypal.com';
            }

            $url = $host . '/v1/oauth2/token';
            $postArgs = 'grant_type=client_credentials';

            // Get Access token
            $paypal_token = homey_get_paypal_access_token($url, $postArgs);
            $url = $host . '/v1/payments/payment';

            $instance_payment_page_link = homey_get_template_link_2('template/template-instance-booking.php');
            $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');

            $cancel_link = add_query_arg(
                array(
                    'check_in' => $check_in_date,
                    'start_hour' => $start_hour,
                    'end_hour' => $end_hour,
                    'guest' => $guests,
                    'listing_id' => $listing_id,
                ),
                $instance_payment_page_link
            );

            $return_link = add_query_arg('reservation_detail', $reservation_id, $reservation_page_link);

            $payment = array(
                'intent' => 'sale',
                "redirect_urls" => array(
                    "return_url" => $return_link,
                    "cancel_url" => $cancel_link
                ),
                'payer' => array("payment_method" => "paypal"),
            );

            /* Prepare basic payment details
             *--------------------------------------*/
            $payment['transactions'][0] = array(
                'amount' => array(
                    'total' => $total_price,
                    'currency' => $submission_curency,
                    'details' => array(
                        'subtotal' => $total_price,
                        'tax' => '0.00',
                        'shipping' => '0.00'
                    )
                ),
                'description' => $payment_description
            );


            /* Prepare individual items
             *--------------------------------------*/
            $payment['transactions'][0]['item_list']['items'][] = array(
                'quantity' => '1',
                'name' => esc_html__('Reservation Payment', 'homey'),
                'price' => $total_price,
                'currency' => $submission_curency,
                'sku' => 'Paid Reservation',
            );

            /* Convert PHP array into json format
             *--------------------------------------*/
            $jsonEncode = json_encode($payment);
            $json_response = homey_execute_paypal_request($url, $jsonEncode, $paypal_token);

            //print_r($json_response);
            foreach ($json_response['links'] as $link) {
                if ($link['rel'] == 'execute') {
                    $payment_execute_url = $link['href'];
                } else if ($link['rel'] == 'approval_url') {
                    $payment_approval_url = $link['href'];
                }
            }

            // Save data in database for further use on processor page
            $output['payment_execute_url'] = $payment_execute_url;
            $output['paypal_token'] = $paypal_token;
            $output['reservation_id'] = '';
            $output['listing_id'] = $listing_id;
            $output['check_in_date'] = $check_in_date;
            $output['check_in_hour'] = $check_in_hour;
            $output['check_out_hour'] = $check_out_hour;
            $output['start_hour'] = $start_hour;
            $output['end_hour'] = $end_hour;
            $output['extra_options'] = $extra_options;
            $output['guests'] = $guests;
            $output['renter_message'] = $renter_message;
            $output['is_instance_booking'] = 1;
            $output['is_hourly'] = 1;

            $save_output[$userID] = $output;
            update_option('homey_paypal_transfer', $save_output);

            echo json_encode(
                array(
                    'success' => true,
                    'message' => $local['processing_text'],
                    'payment_execute_url' => $payment_approval_url
                )
            );
            wp_die();
        }
    }
}

add_action('wp_ajax_nopriv_homey_instance_hourly_booking', 'homey_instance_hourly_booking');
add_action('wp_ajax_homey_instance_hourly_booking', 'homey_instance_hourly_booking');
if (!function_exists('homey_instance_hourly_booking')) {
    function homey_instance_hourly_booking()
    {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();
        $instace_page_link = homey_get_template_link_2('template/template-instance-booking-addons.php');

        $no_login_needed_for_booking = homey_option('no_login_needed_for_booking');


        if ($no_login_needed_for_booking == 'no' && (!is_user_logged_in() || $userID === 0)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['login_for_reservation']
                )
            );
            wp_die();
        }

        if (empty($instace_page_link)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['instance_booking_page']
                )
            );
            wp_die();
        }

        //check security
        $nonce = $_REQUEST['security'];
        if (!wp_verify_nonce($nonce, 'reservation-security-nonce')) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['security_check_text']
                )
            );
            wp_die();
        }

        $listing_id = intval($_POST['listing_id']);
        $listing_owner_id = get_post_field('post_author', $listing_id);
        $check_in_date = wp_kses($_POST['check_in_date'], $allowded_html);
        $start_hour = wp_kses($_POST['start_hour'], $allowded_html);
        $end_hour = wp_kses($_POST['end_hour'], $allowded_html);
        $guests = intval($_POST['guests']);
        $guest_message = $_POST['guest_message'];
        $security = $_REQUEST['security'];
        $extra_options = $_POST['extra_options'];
        $accommodation_number = $_POST['accommodation_number'];
        $choose_guided_service = $_POST['choose_guided_service'];
        $guests_participating = $_POST['guests_participating'];
        $extra_participants = $_POST['extra_participants'];
        $guests_gears = $_POST['guests_gears'];
        $guests_ages = $_POST['guests_ages'];
        $health_conditions = $_POST['health_conditions'];
        $experience_level = $_POST['experience_level'];
        $first_timers = $_POST['first_timers'];
        $extra_equipments = $_POST['extra_equipments'];
        $additional_vehicles = $_POST['additional_vehicles'];
        $coupon_code = $_POST['coupon_code'];

        $have_guided_service = get_field('have_guided_service', $listing_id);


        if ($no_login_needed_for_booking == 'no' && $userID == $listing_owner_id) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['own_listing_error']
                )
            );
            wp_die();
        }

        if (!homey_is_renter()) {
            $current_user = wp_get_current_user();
            $current_user->set_role('homey_renter');
            echo json_encode(
                array(
                    'success' => false,
                    'message' => 'Trying to book a listing? No problem, Switching over to Adventurer.',
                    'reload' => true
                )
            );
            wp_die();
        }

        $booking_hide_fields = homey_option('booking_hide_fields');
        if ((empty($guests) || $guests === 0) && $booking_hide_fields['guests'] != 1) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['choose_guests']
                )
            );
            wp_die();
        }

        if ($choose_guided_service == 'on' || $have_guided_service == 'guide_required') {

            if (empty($guests_gears)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => 'Will you be bringing any of your own personal gear, supplies, or equipment on this Guided Service?'
                    )
                );
                wp_die();
            }

            if (empty($guests_participating)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => 'Enter number of guests for this Guided Service'
                    )
                );
                wp_die();
            }

            if (empty($guests_ages)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => 'Enter ages of guests for this Guided Service'
                    )
                );
                wp_die();
            }

            if (empty($health_conditions)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => 'Enter health conditions of guests for this Guided Service'
                    )
                );
                wp_die();
            }

            if (empty($experience_level)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => 'Enter your level of experience for this Guided Service'
                    )
                );
                wp_die();
            }

            if (empty($first_timers)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => 'Any first timers on this Guided Service?'
                    )
                );
                wp_die();
            }
        }

        if (empty($guest_message)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => 'Enter reason for booking this adventure'
                )
            );
            wp_die();
        }

        $instance_page = add_query_arg(
            array(
                'check_in' => $check_in_date,
                'start_hour' => $start_hour,
                'end_hour' => $end_hour,
                'guest' => $guests,
                'extra_options' => $extra_options,
                'guest_message' => $guest_message,
                'security' => $security,
                'listing_id' => $listing_id,
                'accommodation_number' => $accommodation_number,
                'choose_guided_service' => $choose_guided_service,
                'guests_participating' => $guests_participating,
                'extra_participants' => $extra_participants,
                'guests_gears' => $guests_gears,
                'guests_ages' => $guests_ages,
                'health_conditions' => $health_conditions,
                'experience_level' => $experience_level,
                'first_timers' => $first_timers,
                'extra_equipments' => $extra_equipments,
                'additional_vehicles' => $additional_vehicles,
                'coupon_code' => $coupon_code,

            ),
            $instace_page_link
        );

        echo json_encode(
            array(
                'success' => true,
                'message' => __('Submitting, Please wait...', 'homey'),
                'instance_url' => $instance_page
            )
        );
        wp_die();
    }
}

if (!function_exists('homey_get_booked_hours')) {
    function homey_get_booked_hours($listing_id)
    {
        $now = time();
        //$daysAgo = $now-3*24*60*60;
        $daysAgo = $now - 1 * 24 * 60 * 60;

        $args = array(
            'post_type' => 'homey_reservation',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'reservation_listing_id',
                    'value' => $listing_id,
                    'type' => 'NUMERIC',
                    'compare' => '='
                ),
                array(
                    'key' => 'reservation_status',
                    'value' => 'booked',
                    'type' => 'CHAR',
                    'compare' => '='
                )
            )
        );

        $booked_hours_array = get_post_meta($listing_id, 'reservation_booked_hours', true);

        if (!is_array($booked_hours_array) || empty($booked_hours_array)) {
            $booked_hours_array = array();
        }

        $wpQry = new WP_Query($args);

        if ($wpQry->have_posts()) {

            while ($wpQry->have_posts()):
                $wpQry->the_post();

                $resID = get_the_ID();

                $check_in_date = get_post_meta($resID, 'reservation_checkin_hour', true);
                $check_out_date = get_post_meta($resID, 'reservation_checkout_hour', true);

                $unix_time_start = strtotime($check_in_date);

                if ($unix_time_start > $daysAgo) {

                    $check_in = new DateTime($check_in_date);
                    $check_in_unix = $check_in->getTimestamp();
                    $check_out = new DateTime($check_out_date);
                    $check_out_unix = $check_out->getTimestamp();


                    $booked_hours_array[$check_in_unix] = $resID;

                    $check_in_unix = $check_in->getTimestamp();

                    while ($check_in_unix < $check_out_unix) {

                        $booked_hours_array[$check_in_unix] = $resID;

                        //$check_in->modify('+1 hour');
                        $check_in->modify('+30 minutes');
                        $check_in_unix = $check_in->getTimestamp();
                    }
                }
            endwhile;
            wp_reset_postdata();
        }

        return $booked_hours_array;
    }
}


if (!function_exists('homey_get_booking_pending_hours')) {
    function homey_get_booking_pending_hours($listing_id)
    {
        $now = time();
        //$daysAgo = $now-3*24*60*60;
        $daysAgo = $now - 1 * 24 * 60 * 60;

        $args = array(
            'post_type' => 'homey_reservation',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'reservation_listing_id',
                    'value' => $listing_id,
                    'type' => 'NUMERIC',
                    'compare' => '='
                ),
                array(
                    'key' => 'reservation_status',
                    'value' => 'declined',
                    'type' => 'CHAR',
                    'compare' => '!='
                )
            )
        );

        $pending_dates_array = get_post_meta($listing_id, 'reservation_pending_hours', true);

        if (!is_array($pending_dates_array) || empty($pending_dates_array)) {
            $pending_dates_array = array();
        }

        $wpQry = new WP_Query($args);

        if ($wpQry->have_posts()) {

            while ($wpQry->have_posts()):
                $wpQry->the_post();

                $resID = get_the_ID();

                $check_in_date = get_post_meta($resID, 'reservation_checkin_hour', true);
                $check_out_date = get_post_meta($resID, 'reservation_checkout_hour', true);

                $unix_time_start = strtotime($check_in_date);

                if ($unix_time_start > $daysAgo) {

                    $check_in = new DateTime($check_in_date);
                    $check_in_unix = $check_in->getTimestamp();
                    $check_out = new DateTime($check_out_date);
                    $check_out_unix = $check_out->getTimestamp();


                    $pending_dates_array[$check_in_unix] = $resID;

                    $check_in_unix = $check_in->getTimestamp();

                    while ($check_in_unix < $check_out_unix) {

                        $pending_dates_array[$check_in_unix] = $resID;

                        //$check_in->modify('+1 hour');
                        $check_in->modify('+30 minutes');
                        $check_in_unix = $check_in->getTimestamp();
                    }
                }
            endwhile;
            wp_reset_postdata();
        }

        return $pending_dates_array;
    }
}

if (!function_exists("homey_make_hours_booked")) {
    function homey_make_hours_booked($listing_id, $resID)
    {
        $now = time();
        $daysAgo = $now - 3 * 24 * 60 * 60;

        $check_in_date = get_post_meta($resID, 'reservation_checkin_hour', true);
        $check_out_date = get_post_meta($resID, 'reservation_checkout_hour', true);

        $reservation_dates_array = get_post_meta($listing_id, 'reservation_booked_hours', true);

        if (!is_array($reservation_dates_array) || empty($reservation_dates_array)) {
            $reservation_dates_array = array();
        }

        $unix_time_start = strtotime($check_in_date);

        if ($unix_time_start > $daysAgo) {
            $check_in = new DateTime($check_in_date);
            $check_in_unix = $check_in->getTimestamp();
            $check_out = new DateTime($check_out_date);
            $check_out_unix = $check_out->getTimestamp();

            $check_in_unix = $check_in->getTimestamp();

            while ($check_in_unix < $check_out_unix) {

                $reservation_dates_array[$check_in_unix] = $resID;

                $check_in->modify('+30 minutes');
                $check_in_unix = $check_in->getTimestamp();
            }
        }

        return $reservation_dates_array;
    }
}

if (!function_exists("homey_remove_booking_pending_hours")) {
    function homey_remove_booking_pending_hours($listing_id, $resID)
    {
        $now = time();
        $daysAgo = $now - 3 * 24 * 60 * 60;

        $check_in_date = get_post_meta($resID, 'reservation_checkin_hour', true);
        $check_out_date = get_post_meta($resID, 'reservation_checkout_hour', true);

        $pending_dates_array = get_post_meta($listing_id, 'reservation_pending_hours', true);

        if (!is_array($pending_dates_array) || empty($pending_dates_array)) {
            $pending_dates_array = array();
        }

        $unix_time_start = strtotime($check_in_date);

        if ($unix_time_start > $daysAgo) {
            $check_in = new DateTime($check_in_date);
            $check_in_unix = $check_in->getTimestamp();
            $check_out = new DateTime($check_out_date);
            $check_out_unix = $check_out->getTimestamp();

            $check_in_unix = $check_in->getTimestamp();

            while ($check_in_unix < $check_out_unix) {

                unset($pending_dates_array[$check_in_unix]);

                $check_in->modify('+30 minutes');
                $check_in_unix = $check_in->getTimestamp();
            }
        }

        return $pending_dates_array;
    }
}

if (!function_exists("homey_remove_booked_hours")) {
    function homey_remove_booked_hours($listing_id, $resID)
    {
        $now = time();
        $daysAgo = $now - 3 * 24 * 60 * 60;

        $check_in_date = get_post_meta($resID, 'reservation_checkin_hour', true);
        $check_out_date = get_post_meta($resID, 'reservation_checkout_hour', true);

        $pending_dates_array = get_post_meta($listing_id, 'reservation_booked_hours', true);

        if (!is_array($pending_dates_array) || empty($pending_dates_array)) {
            $pending_dates_array = array();
        }

        $unix_time_start = strtotime($check_in_date);

        if ($unix_time_start > $daysAgo) {
            $check_in = new DateTime($check_in_date);
            $check_in_unix = $check_in->getTimestamp();
            $check_out = new DateTime($check_out_date);
            $check_out_unix = $check_out->getTimestamp();

            $check_in_unix = $check_in->getTimestamp();

            while ($check_in_unix < $check_out_unix) {

                unset($pending_dates_array[$check_in_unix]);

                $check_in->modify('+30 minutes');
                $check_in_unix = $check_in->getTimestamp();
            }
        }

        return $pending_dates_array;
    }
}

if (!function_exists('homey_get_booked_hours_slots')) {
    function homey_get_booked_hours_slots($listing_id)
    {
        $args = array(
            'post_type' => 'homey_reservation',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'reservation_listing_id',
                    'value' => $listing_id,
                    'type' => 'NUMERIC',
                    'compare' => '='
                ),
                array(
                    'key' => 'reservation_status',
                    'value' => 'booked',
                    'type' => 'CHAR',
                    'compare' => '='
                )
            )
        );

        $booked_array = array();

        $wpQry = new WP_Query($args);

        if ($wpQry->have_posts()) {

            while ($wpQry->have_posts()):
                $wpQry->the_post();

                $resID = get_the_ID();

                $check_in_date = get_post_meta($resID, 'reservation_checkin_hour', true);
                $check_out_date = get_post_meta($resID, 'reservation_checkout_hour', true);

                $check_in_date = strtotime($check_in_date);
                $check_out_date = strtotime($check_out_date);

                $booked_array[$check_in_date] = $check_out_date;

            endwhile;
            wp_reset_postdata();
        }

        return $booked_array;
    }
}

if (!function_exists('homey_get_completed_hours_slots')) {
    function homey_get_completed_hours_slots($listing_id)
    {
        $args = array(
            'post_type' => 'homey_reservation',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'reservation_listing_id',
                    'value' => $listing_id,
                    'type' => 'NUMERIC',
                    'compare' => '='
                ),
                array(
                    'key' => 'reservation_status',
                    'value' => 'completed',
                    'type' => 'CHAR',
                    'compare' => '='
                )
            )
        );

        $booked_array = array();

        $wpQry = new WP_Query($args);

        if ($wpQry->have_posts()) {

            while ($wpQry->have_posts()):
                $wpQry->the_post();

                $resID = get_the_ID();

                $check_in_date = get_post_meta($resID, 'reservation_checkin_hour', true);
                $check_out_date = get_post_meta($resID, 'reservation_checkout_hour', true);

                $check_in_date = strtotime($check_in_date);
                $check_out_date = strtotime($check_out_date);

                $booked_array[$check_in_date] = $check_out_date;

            endwhile;
            wp_reset_postdata();
        }

        return $booked_array;
    }
}

if (!function_exists('homey_get_pending_hours_slots')) {
    function homey_get_pending_hours_slots($listing_id)
    {
        $args = array(
            'post_type' => 'homey_reservation',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'reservation_listing_id',
                    'value' => $listing_id,
                    'type' => 'NUMERIC',
                    'compare' => '='
                ),
                array(
                    'key' => 'reservation_status',
                    'value' => 'declined',
                    'type' => 'CHAR',
                    'compare' => '!='
                ),
                array(
                    'key' => 'reservation_status',
                    'value' => 'booked',
                    'type' => 'CHAR',
                    'compare' => '!='
                ),
                array(
                    'key' => 'reservation_status',
                    'value' => 'completed',
                    'type' => 'CHAR',
                    'compare' => '!='
                ),
                array(
                    'key' => 'reservation_status',
                    'value' => 'cancelled',
                    'type' => 'CHAR',
                    'compare' => '!='
                )
            )
        );

        $pending_array = array();

        $wpQry = new WP_Query($args);

        if ($wpQry->have_posts()) {

            while ($wpQry->have_posts()):
                $wpQry->the_post();

                $resID = get_the_ID();

                $check_in_date = get_post_meta($resID, 'reservation_checkin_hour', true);
                $check_out_date = get_post_meta($resID, 'reservation_checkout_hour', true);

                $check_in_date = strtotime($check_in_date);
                $check_out_date = strtotime($check_out_date);

                $pending_array[$check_in_date] = $check_out_date;

            endwhile;
            wp_reset_postdata();
        }

        return $pending_array;
    }
}

add_action('wp_ajax_homey_decline_hourly_reservation', 'homey_decline_hourly_reservation');
if (!function_exists('homey_decline_hourly_reservation')) {
    function homey_decline_hourly_reservation()
    {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $local = homey_get_localization();

        $reservation_id = intval($_POST['reservation_id']);

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $strat_hour = date(homey_time_format(), strtotime($reservation_meta['start_hour']));
        $end_hour = date(homey_time_format(), strtotime($reservation_meta['end_hour']));
        $check_in_date = homey_format_date_simple($reservation_meta['check_in_date']);

        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
        $reason = sanitize_text_field($_POST['reason']);

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

        $renter = homey_usermeta($listing_renter);
        $renter_email = $renter['email'];

        if ($listing_owner != $userID && !homey_is_admin()) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['listing_owner_text']
                )
            );
            wp_die();
        }

        // Set reservation status from under_review to declined
        update_post_meta($reservation_id, 'reservation_status', 'declined');
        update_post_meta($reservation_id, 'res_decline_reason', $reason);

        //Remove Pending Dates
        $pending_dates_array = homey_remove_booking_pending_hours($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_pending_hours', $pending_dates_array);

        echo json_encode(
            array(
                'success' => true,
                'message' => esc_html__('success', 'homey')
            )
        );

        $notification_title = 'Booking Request: #' . $reservation_id . ' has been declined.';
        $notification_content = 'Booking request #' . $reservation_id . ' has been declined.';
        $notification_link = home_url('/reservations/?reservation_detail=' . $reservation_id);
        save_booking_notification($listing_owner, $notification_title, $notification_content, $notification_link);

        $notification_title = 'Reservation Request: #' . $reservation_id . ' has been declined.';
        $notification_content = 'Reservation request #' . $reservation_id . ' has been declined.';
        $notification_link = home_url('/reservations/?reservation_detail=' . $reservation_id);
        save_booking_notification($listing_renter, $notification_title, $notification_content, $notification_link);

        $reservation_url = home_url('/reservations/?reservation_detail=' . $reservation_id);
        if (isset($notification_settings_guest['sms']) && $notification_settings_guest['sms']) {
            if (!empty($guest_phone_number)) {
                $guest_message = 'BACKYARD LEASE: Bummer! Your booking #' . $reservation_id . ' has been declined.' . "\n" . $reservation_url;
                homey_send_sms($guest_phone_number, $guest_message);
            }
        }

        if (isset($notification_settings_host['sms']) && $notification_settings_host['sms']) {
            if (!empty($host_phone_number)) {
                $host_message = 'BACKYARD LEASE: Bummer! Your booking #' . $reservation_id . ' has been declined.' . "\n" . $reservation_url;
                homey_send_sms($host_phone_number, $host_message);
            }
        }

        if (isset($notification_settings_host['email']) && $notification_settings_host['email']) {
            $user_email_host = get_the_author_meta('user_email', $listing_owner);
            $subject = $host_name . ' your booking has been declined';
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
                    ' . $host_name . ' Bummer! your booking has been declined
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
            $subject = $guest_name . ' your booking has been declined';
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
                    ' . $guest_name . ' Bummer! your booking has been declined
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

        //        $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id));
        //        homey_email_composer($renter_email, 'declined_reservation', $email_args);
        //        $admin_email = get_option( 'admin_email' );
        //        homey_email_composer( $admin_email, 'declined_reservation', $email_args );
        wp_die();
    }
}

add_action('wp_ajax_homey_cancelled_hourly_reservation', 'homey_cancelled_hourly_reservation');
if (!function_exists('homey_cancelled_hourly_reservation')) {
    function homey_cancelled_hourly_reservation()
    {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $local = homey_get_localization();

        $reservation_id = intval($_POST['reservation_id']);

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $strat_hour = date(homey_time_format(), strtotime($reservation_meta['start_hour']));
        $end_hour = date(homey_time_format(), strtotime($reservation_meta['end_hour']));
        $check_in_date = homey_format_date_simple($reservation_meta['check_in_date']);

        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
        $reason = sanitize_text_field($_POST['reason']);
        $host_cancel = sanitize_text_field($_POST['host_cancel']);

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

        if (($listing_renter != $userID) && ($listing_owner != $userID)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['listing_renter_text']
                )
            );
            wp_die();
        }

        if (empty($reason)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['reason_text_req']
                )
            );
            wp_die();
        }

        // Set reservation status from under_review to available
        update_post_meta($reservation_id, 'reservation_status', 'cancelled');
        update_post_meta($reservation_id, 'res_cancel_reason', $reason);

        //Remove Pending Dates
        $pending_dates_array = homey_remove_booking_pending_hours($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_pending_hours', $pending_dates_array);

        //Remove Booked Dates
        $booked_dates_array = homey_remove_booked_hours($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_booked_hours', $booked_dates_array);

        echo json_encode(
            array(
                'success' => true,
                'message' => esc_html__('success', 'homey')
            )
        );

        $notification_title = 'Booking Request: #' . $reservation_id . ' has been cancelled.';
        $notification_content = 'Booking request #' . $reservation_id . ' has been canceled.';
        $notification_link = home_url('/reservations/?reservation_detail=' . $reservation_id);
        save_booking_notification($listing_owner, $notification_title, $notification_content, $notification_link);

        $notification_title = 'Reservation Request: #' . $reservation_id . ' has been cancelled.';
        $notification_content = 'Reservation request #' . $reservation_id . ' has been canceled.';
        $notification_link = home_url('/reservations/?reservation_detail=' . $reservation_id);
        save_booking_notification($listing_renter, $notification_title, $notification_content, $notification_link);

        $reservation_url = home_url('/reservations/?reservation_detail=' . $reservation_id);

        if (isset($notification_settings_guest['sms']) && $notification_settings_guest['sms']) {
            if (!empty($guest_phone_number)) {
                $guest_message = 'BACKYARD LEASE: Bummer! Your reservation #' . $reservation_id . ' has been canceled.' . "\n" . $reservation_url;
                homey_send_sms($guest_phone_number, $guest_message);
            }
        }

        if (isset($notification_settings_host['sms']) && $notification_settings_host['sms']) {
            if (!empty($host_phone_number)) {
                $host_message = 'BACKYARD LEASE: Bummer! Your booking #' . $reservation_id . ' has been canceled.' . "\n" . $reservation_url;
                homey_send_sms($host_phone_number, $host_message);
            }
        }

        if ($host_cancel == 'cancelled_by_host') {
            $renter = homey_usermeta($listing_renter);
            $to_email = $renter['email'];
        } else {
            $owner = homey_usermeta($listing_owner);
            $to_email = $owner['email'];
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
            $subject = $guest_name . ' your reservation has been canceled';
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

        //        $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id));
        //        homey_email_composer($to_email, 'cancelled_reservation', $email_args);
        //        $admin_email = get_option( 'admin_email' );
        //        homey_email_composer( $admin_email, 'cancelled_reservation', $email_args );
        wp_die();
    }
}

if (!function_exists('homey_hourly_booking_with_no_upfront')) {
    function homey_hourly_booking_with_no_upfront($reservation_id)
    {
        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
        $admin_email = get_option('admin_email');

        //Book days
        $booked_days_array = homey_make_hours_booked($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_booked_hours', $booked_days_array);

        //Remove Pending Dates
        $pending_dates_array = homey_remove_booking_pending_hours($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_pending_hours', $pending_dates_array);

        // Update reservation status
        update_post_meta($reservation_id, 'reservation_status', 'booked');

        // Emails
        $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
        $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);

        $renter = homey_usermeta($listing_renter);
        $renter_email = $renter['email'];

        $owner = homey_usermeta($listing_owner);
        $owner_email = $owner['email'];

        $message_link = homey_thread_link_after_reservation($reservation_id);
        $reservation_page = homey_get_template_link_dash('template/dashboard-reservations2.php');
        $reservation_detail_link = add_query_arg('reservation_detail', $reservation_id, $reservation_page);

        $email_args = array(
            //  'guest_message' => $guest_message,
            'message_link' => $message_link,
            'reservation_detail_url' => $reservation_detail_link
        );
        homey_email_composer($renter_email, 'booked_reservation', $email_args);

        $email_args = array(
            //  'guest_message' => $guest_message,
            'message_link' => $message_link,
            'reservation_detail_url' => reservation_detail_link($reservation_id)
        );
        homey_email_composer($admin_email, 'admin_booked_reservation', $email_args);

        return true;
    }
}
