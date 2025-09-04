<?php
global $post, $homey_local, $homey_prefix;
$room_type_pre = $bedrooms = $rooms = '';
if(isset($_GET['room_size'])) {
	$room_type_pre = $_GET['room_size'];
}
if(isset($_GET['bedrooms'])) {
	$bedrooms = $_GET['bedrooms'];
}
if(isset($_GET['rooms'])) {
	$rooms = $_GET['rooms'];
}
$search_hide_fields = homey_option('search_hide_fields');
?>
<div class="search-filter">
	
	<div class="search-filter-wrap">

		<?php 
		if($search_hide_fields['bedrooms'] != 1 || $search_hide_fields['rooms'] != 1 || $search_hide_fields['room_type'] != 1) { ?>
		<div class="filters-wrap">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
					<div class="filters">
						<strong><?php echo esc_attr(homey_option('srh_size')); ?></strong>
					</div>
				</div>

				<?php 
				if($search_hide_fields['bedrooms'] != 1) { ?>
				<div class="col-xs-12 col-sm-4 col-md-3 col-lg-3">
					<select name="bedrooms" class="selectpicker" title="<?php echo esc_attr(homey_option('srh_bedrooms')); ?>">
						<option value=""><?php echo esc_attr(homey_option('srh_bedrooms')); ?></option>
						<?php for($i = 1; $i <= 20; $i++) { ?>
							<option <?php selected($bedrooms, $i, true);?> value="<?php echo esc_attr($i);?>"><?php echo esc_attr($i);?></option>
						<?php } ?>
					</select>
				</div>
				<?php } ?>

				<?php 
				if($search_hide_fields['rooms'] != 1) { ?>
				<div class="col-xs-12 col-sm-4 col-md-3 col-lg-3">
					<select name="rooms" class="selectpicker" title="<?php echo esc_attr(homey_option('srh_rooms')); ?>">
						<option value=""><?php echo esc_attr(homey_option('srh_rooms')); ?></option>
						<?php for($i = 1; $i <= 20; $i++) { ?>
							<option <?php selected($rooms, $i, true);?> value="<?php echo esc_attr($i);?>"><?php echo esc_attr($i);?></option>
						<?php } ?>
					</select>
				</div>
				<?php } ?>

				<?php 
				if($search_hide_fields['room_type'] != 1) { ?>
				<div class="col-xs-12 col-sm-4 col-md-3 col-lg-3">
					<select name="room_size" class="selectpicker" title="<?php echo esc_attr(homey_option('srh_room_type')); ?>">
						<?php
                        // All Option
                        echo '<option value="">'.esc_attr(homey_option('srh_room_type')).'</option>';

                        $room_type = get_terms (
                            array(
                                "room_type"
                            ),
                            array(
                                'orderby' => 'name',
                                'order' => 'ASC',
                                'hide_empty' => false,
                                'parent' => 0
                            )
                        );
                        homey_hirarchical_options('room_type', $room_type, $room_type_pre );
                        ?>
					</select>
				</div>
				<?php } ?>

			</div>
		</div><!-- .filters-wrap -->
		<?php } ?>

		<?php 
		if($search_hide_fields['search_price'] != 1) { ?>
		<div class="filters-wrap">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
					<div class="filters">
						<strong><?php echo esc_html__(esc_attr(homey_option('srh_price')), 'homey'); ?></strong>
					</div>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
					<select name="min-price" class="selectpicker" data-live-search="true" data-live-search-style="begins" title="<?php echo esc_html__(esc_attr($homey_local['search_min']),'homey'); ?>">
						<?php homey_adv_searches_min_price(); ?>
					</select>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
					<select name="max-price" class="selectpicker" data-live-search="true" data-live-search-style="begins" title="<?php echo esc_html__(esc_attr($homey_local['search_max']), 'homey'); ?>">
						<?php homey_adv_searches_max_price(); ?>
					</select>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
                    <input class="form-control search-keyword" type="text" name="search_keyword" placeholder="Search by keyword" value="<?php echo isset($_GET['search_keyword']) ? esc_attr($_GET['search_keyword']) : ''; ?>">
                </div>
			</div>
		</div><!-- .filters-wrap -->
		<?php } ?>

		<?php 
		if($search_hide_fields['search_amenities'] != 1) {
			get_template_part('template-parts/search/amenities'); 
		}
		?>

		<?php 
		if($search_hide_fields['search_facilities'] != 1) {
			get_template_part('template-parts/search/facilities'); 
		}
		?>


   <div class="filters-wrap">
    <div class="row">		
	    <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
			<div class="filters">
				<strong><?php echo esc_attr__('Sleep Accommodations', 'homey'); ?></strong>
			</div>
		</div>
        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
            <select name="sleeping_accommodation_search" class="selectpicker">
                <option value="" <?php echo (!isset($_GET['sleeping_accommodation_search']) || empty($_GET['sleeping_accommodation_search'])) ? 'selected' : ''; ?>><?php echo esc_attr__('Select', 'homey'); ?></option>
                <option value="yes" <?php echo (isset($_GET['sleeping_accommodation_search']) && $_GET['sleeping_accommodation_search'] == 'yes') ? 'selected' : ''; ?>><?php echo esc_attr__('Yes', 'homey'); ?></option>
                <option value="no" <?php echo (isset($_GET['sleeping_accommodation_search']) && $_GET['sleeping_accommodation_search'] == 'no') ? 'selected' : ''; ?>><?php echo esc_attr__('No', 'homey'); ?></option>
            </select>
        </div>
		<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
			<div class="filters">
				<strong><?php echo esc_attr__('Guided Services', 'homey'); ?></strong>
			</div>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
            <select name="guided_services_search" class="selectpicker">
                <option value="" <?php echo (!isset($_GET['guided_services_search']) || empty($_GET['guided_services_search'])) ? 'selected' : ''; ?>><?php echo esc_attr__('Select', 'homey'); ?></option>
                <option value="no_guide_needed" <?php echo (isset($_GET['guided_services_search']) && $_GET['guided_services_search'] == 'no_guide_needed') ? 'selected' : ''; ?>><?php echo esc_attr__('No Guide', 'homey'); ?></option>
                <option value="guide_required" <?php echo (isset($_GET['guided_services_search']) && $_GET['guided_services_search'] == 'guide_required') ? 'selected' : ''; ?>><?php echo esc_attr__('Guide Required', 'homey'); ?></option>
				<option value="guide_is_optional" <?php echo (isset($_GET['guided_services_search']) && $_GET['guided_services_search'] == 'guide_is_optional') ? 'selected' : ''; ?>><?php echo esc_attr__('Guide Optional', 'homey'); ?></option>
            </select>
        </div>
    </div>
   </div>

   <div class="filters-wrap">
    <div class="row">
	    <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3" style="padding-top: 10px;">
		    <input type="checkbox" name="featured_listings_only" value="1" style="width: 15%;float: left;" <?php echo isset($_GET['featured_listings_only']) && $_GET['featured_listings_only'] == '1' ? 'checked' : ''; ?>>
            <label for="featured_listings_only" style="width: 85%;text-align: left;"><strong><?php echo esc_html__('Featured Listings', 'homey'); ?></strong></label>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3" style="padding-top: 10px;">
		    <input type="checkbox" name="allowed_pets_listings" value="1" style="width: 15%;float: left;" <?php echo isset($_GET['allowed_pets_listings']) && $_GET['allowed_pets_listings'] == '1' ? 'checked' : ''; ?>>
            <label for="allowed_pets_listings" style="width: 85%;text-align: left;"><strong><?php echo esc_html__('Pets Allowed', 'homey'); ?></strong></label>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3" style="padding-top: 10px;">
		    <input type="checkbox" name="allowed_parties_listings" value="1" style="width: 15%;float: left;" <?php echo isset($_GET['allowed_parties_listings']) && $_GET['allowed_parties_listings'] == '1' ? 'checked' : ''; ?>>
            <label for="allowed_parties_listings" style="width: 85%;text-align: left;"><strong><?php echo esc_html__('Parties Allowed', 'homey'); ?></strong></label>
		</div>
        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3" style="padding-top: 10px;">
		    <input type="checkbox" name="allowed_cancellations_listings" value="1" style="width: 15%;float: left;" <?php echo isset($_GET['allowed_cancellations_listings']) && $_GET['allowed_cancellations_listings'] == '1' ? 'checked' : ''; ?>>
            <label for="allowed_cancellations_listings" style="width: 85%;text-align: left;"><strong><?php echo esc_html__('Cancellation Allowed', 'homey'); ?></strong></label>
		</div>	    
    </div>
   </div>	   

		<div class="search-filter-footer text-right">
			<button type="submit" class="btn btn btn-grey-outlined search-reset-btn"><?php echo esc_attr($homey_local['search_reset']); ?></button>
			<button type="submit" class="btn btn-primary search-apply-filters homey_half_map_search_btn"><?php echo esc_attr($homey_local['search_apply']); ?></button>
		</div><!-- .search-filter-footer -->

	</div><!-- .search-filter-wrap -->
	
</div><!-- search-filter -->