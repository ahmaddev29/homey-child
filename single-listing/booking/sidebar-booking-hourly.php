<?php global $post, $current_user, $homey_prefix, $homey_local;
wp_get_current_user();
$listing_id = $post->ID;
$price_per_night = get_post_meta($listing_id, $homey_prefix . 'night_price', true);
$instant_booking = get_post_meta($listing_id, $homey_prefix . 'instant_booking', true);

$amenity_price_type = get_field('amenity_price_type', $listing_id);
$price_type_text = '';

if ($amenity_price_type == 'price_per_hour') {
	$price_type_text = 'HR';
} elseif ($amenity_price_type == 'price_per_day') {
	$price_type_text = 'DAY';
} elseif ($amenity_price_type == 'price_per_half_day') {
	$price_type_text = 'HALF DAY';
}

$start_hour = get_post_meta($listing_id, $homey_prefix . 'start_hour', true);
$end_hour = get_post_meta($listing_id, $homey_prefix . 'end_hour', true);

$day_start_hour = get_post_meta($listing_id, $homey_prefix . 'checkin_after', true);
$day_end_hour = get_post_meta($listing_id, $homey_prefix . 'checkout_before', true);

$instances = get_post_meta($listing_id, '_listing_calendar_instances', true);

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

$offsite_payment = homey_option('off-site-payment');
$have_sleeping_accommodation = get_field('field_6479eb9f0208c', $listing_id);
$have_guided_service = get_field('have_guided_service', $listing_id);
$non_participants = get_field('non_participants', $listing_id);
$cost_per_additional_car = get_field('cost_per_additional_car', $listing_id);
$extra_car_allow = get_field('extra_car_allow', $listing_id);
if (empty($non_participants)) {
	$non_participants = 0;
}
$maximum_guests = get_field('maximum_guests', $listing_id);
if (empty($maximum_guests)) {
	$maximum_guests = 0;
}
$accomodation_fee = floatval(get_field('field_6479ec8dfe126', $listing_id));
$price_rate = get_field('price_rate', $listing_id);
$guided_fee = floatval(get_field('guided_price', $listing_id));
$equipment_data = get_field('equipments_rows', $listing_id);
$key = '';

$userID = $current_user->ID;
$saved_cards = get_user_meta($userID, 'saved_stripe_cards', true);
$saved_cards = $saved_cards ? json_decode($saved_cards, true) : [];

$fav_option = 'homey_favorites-' . $userID;
$fav_option = get_option($fav_option);
if (!empty($fav_option)) {
	$key = array_search($post->ID, $fav_option);
}
///homey_hourly_weekends_price
$listing_meta = get_post_meta($post->ID);
$post_id = get_the_ID();
$listing_owner_id = get_post_field('post_author', $post_id);
$user_listings = get_posts(
	array(
		'post_type' => 'listing',
		'posts_per_page' => -1,
		'author' => $listing_owner_id,
	)
);
$author_username = get_the_author_meta('user_login', $listing_owner_id);

$price_separator = homey_option('currency_separator');

if ($key != false || $key != '') {
	$favorite = $homey_local['remove_favorite'];
	$heart = 'fa-heart';
} else {
	$favorite = $homey_local['add_favorite'];
	$heart = 'fa-heart-o';
}
$listing_price = homey_get_price();

if (empty($start_hour)) {
	$start_hour = '01:00';
}

if (empty($end_hour)) {
	$end_hour = '24:00';
}


$prefilled = homey_get_dates_for_booking();
$pre_start_hour = $prefilled['start'];
$pre_end_hour = $prefilled['end'];

$start_hours_list = '';
$end_hours_list = '';
$start_hour = strtotime($start_hour);
$end_hour = strtotime($end_hour);

if ($amenity_price_type == 'price_per_hour') {
	for ($halfhour = $start_hour; $halfhour <= $end_hour; $halfhour = $halfhour + 30 * 60) {
		$start_hours_list
			.= '<option ' . selected($pre_start_hour, date('H:i', $halfhour), false) . ' value="' . date('H:i', $halfhour)
			. '">' . date(homey_time_format(), $halfhour) . '</option>';
		$end_hours_list .= '<option ' .
			selected($pre_end_hour, date('H:i', $halfhour), false) . ' value="' . date('H:i', $halfhour) . '">' .
			date(homey_time_format(), $halfhour) . '</option>';
	}
}
$no_login_needed_for_booking = homey_option('no_login_needed_for_booking'); ?>
<div id="homey_remove_on_mobile" class="sidebar-booking-module">
	<div class="block">
		<div class="sidebar-booking-module-header">
			<div class="block-body-sidebar">

				<?php
				if (!empty($listing_price)) { ?>

					<span class="item-price">
						<sup>$</sup><?php echo $listing_price; ?><sup>/<?php echo $price_type_text; ?></sup>
					</span>

					<span class="weekend-price">
						<?php if ($listing_meta['homey_hourly_weekends_price'][0]) {
							echo 'Weekend price: ' . '$' . $listing_meta['homey_hourly_weekends_price'][0] . '<sup>/' . $price_type_text . '</sup>';
						} ?>
					</span>

				<?php } else {
					echo '<span class="item-price free">' . esc_html__('Free', 'homey') . '</span>';
				} ?>

			</div><!-- block-body-sidebar -->
		</div><!-- sidebar-booking-module-header -->
		<div class="sidebar-booking-module-body">
			<div class="homey_notification block-body-sidebar">
				<?php
				if (homey_affiliate_booking_link()) { ?>

					<a href="<?php echo homey_affiliate_booking_link(); ?>" target="_blank"
						class="btn btn-full-width btn-primary"><?php echo esc_html__('Book Now', 'homey'); ?></a>

				<?php
				} else { ?>
					<div id="single-listing-date-range" class="search-date-range">
						<div class="search-date-range-arrive search-date-hourly-arrive">
							<input id="hourly_check_inn" name="arrive" value="<?php echo esc_attr($prefilled['arrive']); ?>"
								readonly type="text" class="form-control check_in_date" autocomplete="off"
								placeholder="Day of adventure">
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



					<?php get_template_part('single-listing/booking/guests'); ?>

					<?php get_template_part('single-listing/booking/extra-prices'); ?>

					<?php if ($have_sleeping_accommodation == 'yes') { ?>
						<div class="search-extra-services" id="sleeping-accomodation-availability">
							<strong>Sleeping Accommodation</strong>
							<ul class="extra-services-list list-unstyled clearfix">
								<li>
									<label class="need_sleeping_accomodation control control--checkbox">
										<input type="checkbox" name="choose_accommodation" id="choose_accommodation" />
										<span class="control-text">How Many Nights</span>
										<span class="control__indicator"></span>
									</label>
									<span class="inc-dec-sec">
										<div class="accommodation-number-display" id="accommodationNumberDisplay">0</div>
										<div>
											<button class="increment-btn" id="accommodationNumberIncrement"></button>
											<button class="decrement-btn" id="accommodationNumberDecrement"></button>
										</div>
									</span>
								</li>
							</ul>

							<div id="accommodation_number_field" style="display:none;margin-bottom: 15px;">
								<div class="flex_set" style="display: flex; gap: 10px;">
									<input class="form-control" type="hidden" name="accommodation_number"
										id="accommodation_number" value="0" />
									<button class="btn btn-primary" type="button"
										id="apply_accommodation"><?php esc_html_e('Apply', 'homey'); ?></button>
								</div>
							</div>
						</div>
					<?php } ?>

					<?php if ($have_guided_service == 'guide_required' || $have_guided_service == 'guide_is_optional') { ?>
						<div class="search-extra-services" id="guided-service-availability">
							<strong>Guided Service</strong>
							<?php if ($have_guided_service == 'guide_is_optional') { ?>
								<ul class="extra-services-list list-unstyled clearfix">
									<li>
										<label class="need_guided_service control control--checkbox">
											<input type="checkbox" name="choose_guided_service" id="choose_guided_service" />
											<span class="control-text">Need Guided Service</span>
											<span class="control__indicator"></span>
										</label>
										<!--
										<?php if ($price_rate == 'hourly_rate') { ?>
											<span>$<?php echo esc_html($guided_fee); ?>/HR</span>
										<?php } else { ?>
											<span>$<?php echo esc_html($guided_fee); ?></span>
										<?php } ?>
										-->
									</li>
								</ul>
							<?php } ?>

							<div id="guided_service_number_field"
								style="<?php echo ($have_guided_service == 'guide_is_optional') ? 'display: none;' : ''; ?>">

								<?php if ($equipment_data) { ?>
									<strong style="font-size: 16px;">Equipment / Gear Rentals </strong>
									<?php
									echo '<ul class="extra-services-list list-unstyled clearfix">';
									foreach ($equipment_data as $row) {
									?>
										<li>
											<label class="homey_extra_equipments control control--checkbox">
												<input type="checkbox" name="extra_equipments[]"
													data-name="<?php echo esc_attr($row['equipment_name']); ?>"
													data-price="<?php echo esc_attr($row['equipment_price']); ?>"
													data-type="<?php echo esc_attr($row['equipment_type']); ?>">
												<span class="control-text"><?php echo esc_html($row['equipment_name']); ?></span>
												<span class="control__indicator"></span>
											</label>
											<span>$<?php echo esc_html($row['equipment_price']); ?>/<?php echo esc_html($row['equipment_type'] === 'total_fee' ? 'Total' : 'Guest'); ?></span>
										</li>
								<?php }
									echo '</ul>';
								} ?>

								<strong style="font-size: 16px;">Required Data</strong>
								<div class="required-data" style="margin-top: 8px; margin-bottom: 8px;">
									<textarea class="form-control" name="guests_gears" rows="3" id="guests_gears"
										placeholder="Will you be bringing any of your own personal gear, supplies, or equipment on this Guided Service? If so, please explain"
										required></textarea>
									<input class="form-control" type="number" name="guests_participating"
										id="guests_participating"
										placeholder="Number of Guests (Maximum <?php echo esc_html($maximum_guests) ?> allowed)"
										required />
									<?php if (isset($non_participants) && $non_participants > 0) { ?>
										<input class="form-control" type="number" name="extra_participants"
											id="extra_participants_number"
											placeholder="Extra Guests? If yes (Maximum <?php echo esc_html($non_participants) ?> allowed)" />
									<?php } ?>
									<input class="form-control" type="text" name="guests_ages" id="guests_ages"
										placeholder="Ages of Guests" />
									<input class="form-control" type="text" name="health_conditions" id="health_conditions"
										placeholder="Any known health conditions?" required />
									<input class="form-control" type="text" name="experience_level" id="experience_level"
										placeholder="What is your level of experience?" required />
									<input class="form-control" type="text" name="first_timers" id="first_timers"
										placeholder="Any first timers?" required />
									<button class="btn btn-primary" type="button"
										id="apply_guided_service"><?php esc_html_e('Apply', 'homey'); ?></button>
								</div>
							</div>
						</div>
					<?php }
					if (!empty($cost_per_additional_car) && !empty($extra_car_allow)) { ?>
						<div class="search-extra-services" style="padding-bottom: 10px !important;">
							<strong>Additional Vehicles</strong>
							<ul class="extra-services-list list-unstyled clearfix">
								<li>
									<label class="need_additional_vehicles control control--checkbox">
										<input type="checkbox" name="choose_vehicles" id="choose_vehicles" />
										<span class="control-text">Number of Additional Vehicles</span>
										<span class="control__indicator"></span>
									</label>
									<span class="inc-dec-sec">
										<div class="additional-vehicles-display" id="additionalVehiclesDisplay">0</div>
										<div>
											<button class="increment-btn" id="additionalVehiclesIncrement"></button>
											<button class="decrement-btn" id="additionalVehiclesDecrement"></button>
										</div>
									</span>
								</li>
							</ul>

							<div id="additional_vehicles_field" style="display:none;">
								<div class="flex_set" style="display: flex; gap: 10px;">
									<input class="form-control" type="hidden" name="additional_vehicles"
										id="additional_vehicles" value="0" />
									<button class="btn btn-primary" type="button"
										id="apply_vehicles"><?php esc_html_e('Apply', 'homey'); ?></button>
								</div>
							</div>
						</div>
					<?php } ?>


					<?php if ($offsite_payment == 0) { ?>
						<div class="search-message">
							<textarea name="guest_message" class="form-control" rows="3"
								placeholder="<?php echo esc_html__('What is your reason for booking this adventure?', 'homey'); ?>"><?php echo @$_GET['guest_message']; ?></textarea>
						</div>
					<?php } ?>

					<div class="coupon-service" id="coupon_application" style="margin: 20px 0px 15px 0px;">
						<div class="flex_set" style="display: flex; gap: 10px;">
							<input class="form-control" type="text" name="coupon_code" id="coupon_code"
								placeholder="Enter Coupon">
							<button class="btn btn-primary" type="button"
								id="apply_coupon"><?php esc_html_e('Apply', 'homey'); ?></button>
						</div>
						<div id="coupon_message"></div>
					</div>

					<div class="homey_preloader">
						<?php get_template_part('template-parts/spinner'); ?>
					</div>
					<div id="homey_booking_cost" class="payment-list"></div>

					<input type="hidden" name="listing_id" id="listing_id" value="<?php echo intval($listing_id); ?>">
					<input type="hidden" name="reservation-security" id="reservation-security"
						value="<?php echo wp_create_nonce('reservation-security-nonce'); ?>" />

					<?php if ($instant_booking && $offsite_payment == 0) { ?>
						<button id="instance_hourly_reservation" type="button"
							class="btn btn-full-width btn-primary"><?php echo esc_html__('Instant Booking', 'homey'); ?></button>
					<?php } else { ?>

						<?php if (!is_user_logged_in() && $no_login_needed_for_booking == 'yes') { ?>
							<div class="new_reser_request_user_email ">
								<input id="new_reser_request_user_email" name="new_reser_request_user_email" required="required"
									value="<?php echo esc_attr($prefilled['new_reser_request_user_email']); ?>" type="email"
									class="form-control new_reser_request_user_email"
									placeholder="<?php echo esc_html__('Your email', 'homey'); ?>">
							</div>
						<?php } ?>

						<div class="search-extra-services" style="margin-bottom:20px;">
							<strong>Select Payment Method</strong>
							<?php if ($saved_cards) { ?>
								<?php foreach ($saved_cards as $card): ?>
									<div class="stripe-payment-methods"
										style="display: grid;grid-template-columns: 10% 90%;grid-gap: .75rem 1rem;">
										<input type="radio" name="payment_method" value="<?php echo esc_attr($card['id']); ?>">
										<div>
											<?php echo esc_html('**** **** **** ' . $card['last4'] . ' (' . $card['brand'] . ')'); ?>
										</div>
									</div>
								<?php endforeach; ?>
							<?php } else { ?>
								<p style="color: #3A3D32;">No payment method found.</p>
							<?php } ?>
						</div>

						<button id="request_hourly_reservation" type="button"
							class="btn btn-full-width btn-primary"><?php echo esc_html__('Request to Book', 'homey'); ?></button>
						<div class="text-center text-small"><i class="fa fa-info-circle"></i>
							<?php echo esc_html__("You won't be charged yet", 'homey'); ?></div>
					<?php } ?>


				<?php } ?>
			</div><!-- block-body-sidebar -->
		</div><!-- sidebar-booking-module-body -->

	</div><!-- block -->
</div><!-- sidebar-booking-module -->
<div class="sidebar-booking-module-footer">
	<div class="block-body-sidebar">

		<?php if (homey_option('detail_favorite') != 0) { ?>
			<button type="button" data-listid="<?php echo intval($post->ID); ?>"
				class="add_fav btn btn-full-width btn-grey-outlined"><i class="fa <?php echo esc_attr($heart); ?>"
					aria-hidden="true"></i> <?php echo esc_attr($favorite); ?></button>
		<?php } ?>

		<?php if (homey_option('detail_contact_form') != 0 && homey_option('hide-host-contact') != 1) { ?>
			<button type="button" data-toggle="modal" data-target="#modal-contact-host"
				class="btn btn-full-width btn-grey-outlined"><?php echo esc_attr($homey_local['pr_cont_host']); ?></button>
		<?php } ?>

		<?php if ($user_listings && count($user_listings) > 1) { ?>
			<button type="button" id="otherListingsButton" class="btn btn-full-width btn-grey-outlined">Check out my
				other
				listings</button>
		<?php } ?>

		<?php if (homey_option('print_button') != 0) { ?>
			<button type="button" id="homey-print" class="btn btn-full-width btn-blank"
				data-listing-id="<?php echo intval($listing_id); ?>">
				<i class="fa fa-print" aria-hidden="true"></i> <?php echo esc_attr($homey_local['print_label']); ?>
			</button>
		<?php } ?>
	</div><!-- block-body-sidebar -->

	<?php
	if (homey_option('detail_share') != 0) {
		get_template_part('single-listing/share');
	}
	?>
</div><!-- sidebar-booking-module-footer -->
<script>
	document.addEventListener('DOMContentLoaded', function() {

		var otherListingsButton = document.getElementById('otherListingsButton');
		if (otherListingsButton) {
			otherListingsButton.addEventListener('click', function() {
				var authorUsername = '<?php echo esc_js($author_username); ?>';
				var authorListingsUrl = 'https://backyardlease.flywheelsites.com/author/' + authorUsername + '/';
				window.location.href = authorListingsUrl;
			});
		}
	});

	document.addEventListener('DOMContentLoaded', function() {

		var chooseAccommodationCheckbox = document.getElementById('choose_accommodation');
		var accommodationNumberField = document.getElementById('accommodation_number_field');

		if (chooseAccommodationCheckbox) {
			chooseAccommodationCheckbox.addEventListener('change', function() {
				accommodationNumberField.style.display = this.checked ? 'block' : 'none';
			});
		}

		var chooseVehiclesCheckbox = document.getElementById('choose_vehicles');
		var additionalVehiclesField = document.getElementById('additional_vehicles_field');

		if (chooseVehiclesCheckbox) {
			chooseVehiclesCheckbox.addEventListener('change', function() {
				additionalVehiclesField.style.display = this.checked ? 'block' : 'none';
			});
		}

		var checkbox = document.getElementById('choose_guided_service');
		var guidedServiceNumberField = document.getElementById('guided_service_number_field');

		if (checkbox) {
			guidedServiceNumberField.style.display = checkbox.checked && '<?php echo $have_guided_service; ?>' === 'guide_is_optional' ? 'block' : 'none';

			checkbox.addEventListener('change', function() {
				guidedServiceNumberField.style.display = checkbox.checked && '<?php echo $have_guided_service; ?>' === 'guide_is_optional' ? 'block' : 'none';
			});
		}

		var guestsParticipatingField = document.getElementById('guests_participating');
		var allowedGuests = <?php echo $maximum_guests; ?>;

		if (guestsParticipatingField) {
			guestsParticipatingField.addEventListener('change', function() {
				var enteredValue = parseInt(guestsParticipatingField.value);

				if (enteredValue > allowedGuests || enteredValue <= 0) {
					guestsParticipatingField.value = Math.max(0, allowedGuests);
				}
			});
		}

		var guestsNonParticipatingField = document.getElementById('extra_participants_number');
		var extraGuests = <?php echo $non_participants; ?>;

		if (guestsNonParticipatingField) {
			guestsNonParticipatingField.addEventListener('change', function() {
				var extraValue = parseInt(guestsNonParticipatingField.value);

				if (extraValue > extraGuests || extraValue <= 0) {
					guestsNonParticipatingField.value = Math.max(0, extraGuests);
				}
			});
		}

	});

	jQuery(document).ready(function($) {
		let countVeh = 0;
		const extraCarAllow = <?php echo json_encode($extra_car_allow); ?>;
		$("#additionalVehiclesIncrement").on("click", function() {
			if (countVeh < extraCarAllow) {
				countVeh++;
				$("#additionalVehiclesDisplay").text(countVeh);
				$("#additional_vehicles").val(countVeh);
			}
		});

		$("#additionalVehiclesDecrement").on("click", function() {
			if (countVeh > 0) {
				countVeh--;
				$("#additionalVehiclesDisplay").text(countVeh);
				$("#additional_vehicles").val(countVeh);
			}
		});
	});
</script>