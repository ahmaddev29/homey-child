<?php
global $homey_prefix, $homey_local, $listing_data;
$accomodation = get_post_meta($listing_data->ID, $homey_prefix . 'accomodation', true);
$class = '';
if (isset($_GET['tab']) && $_GET['tab'] == 'guided_service') {
  $class = 'in active';
}
$post_id = intval($_GET['edit_listing']);
$have_guided_service = get_field('have_guided_service', $post_id);
$guide_bio = get_field('guide_bio', $post_id);
$what_expect = get_field('what_expect', $post_id);
$price_type = get_field('price_type', $post_id);
$price_rate = get_field('price_rate', $post_id);
$guided_price = get_field('guided_price', $post_id);
$what_permitted = get_field('what_permitted', $post_id);
$what_not_permitted = get_field('what_not_permitted', $post_id);
$who_permitted = get_field('who_permitted', $post_id);
$who_not_permitted = get_field('who_not_permitted', $post_id);
$license_required = get_field('license_required', $post_id);
$guest_provide = get_field('guest_provide', $post_id);
$gears_list = get_field('gears_list', $post_id);
$gears_price = get_field('gears_price', $post_id);
$guest_wear = get_field('guest_wear', $post_id);
$maximum_guests = get_field('maximum_guests', $post_id);
$non_participants = get_field('non_participants', $post_id);
$non_participants_price = get_field('non_participants_price', $post_id);
$equipment_data = get_field('equipments_rows', $post_id);
?>

<div id="guided-service-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
  <div class="block-title visible-xs">
    <h3 class="title">Guided Service</h3>
  </div>
  <div class="block-body">
    <div class="row">
  <div class="col-sm-12 col-xs-12">
    <div class="form-group">
      <label for="have_guided_service">Does your Backyard Lease have Guided Service?</label>
	  <select id="have_guided_service" name="have_guided_service" class="selectpicker">
        <option value="no_guide_needed" <?php echo $have_guided_service == 'no_guide_needed' ? 'selected="selected"' : ''; ?>>No Guide Needed</option>
        <option value="guide_required" <?php echo $have_guided_service == 'guide_required' ? 'selected="selected"' : ''; ?>>Guide Required</option>
	    <option value="guide_is_optional" <?php echo $have_guided_service == 'guide_is_optional' ? 'selected="selected"' : ''; ?>>Guide is Optional</option>
      </select>
    </div>
  </div>
</div>

    <div id="guided_service">
                       
		<div class="row">
          <div class="col-sm-12 col-xs-12">
            <div class="form-group">
              <label for="guide_bio">Guide Bio (Include experience, history, and important information about yourself)</label>
              <?php
              // default settings - Kv_front_editor.php
              $content = $guide_bio;
              $editor_id = 'guide_bio';
              $settings =   array(
                'wpautop' => true, // use wpautop?
                'media_buttons' => false, // show insert/upload button(s)
                'textarea_name' => $editor_id, // set the textarea name to something different, square brackets [] can be used here
                'textarea_rows' => '10', // rows="..."
                'tabindex' => '',
                'editor_css' => '', //  extra styles for both visual and HTML editors buttons,
                'editor_class' => '', // add extra class(es) to the editor textarea
                'teeny' => false, // output the minimal editor config used in Press This
                'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
              );
              wp_editor($content, $editor_id, $settings); ?>
            </div>
          </div>
        </div>
		
		        <div class="row">
                    <div class="col-sm-12 col-sm-12">
                        <div class="form-group">
                            <label for="what_expect">What to expect on this adventure?</label>
                            <textarea name="what_expect" class="form-control" id="what_expect" rows="3" placeholder="What to expect on this adventure?"><?php echo esc_textarea($what_expect); ?></textarea>
                        </div>
                    </div>
                </div>
				
		<div class="row">
		<div class="col-sm-4 col-xs-12">
          <div class="form-group">
            <label for="price_type">Price Type</label>
            <select id="price_type" name="price_type" class="selectpicker">
              <option value="per_guest" <?php echo $price_type == 'per_guest' ? 'selected="selected"' : ''; ?>>Per Guest</option>
              <option value="per_group" <?php echo $price_type == 'per_group' ? 'selected="selected"' : ''; ?>>Per Group</option>
			  <option value="flat_fee" <?php echo $price_type == 'flat_fee' ? 'selected="selected"' : ''; ?>>Flat Fee</option>
            </select>
          </div>
        </div>
          <div class="col-sm-4 col-xs-12">
          <div class="form-group">
            <label for="price_rate">Price Rate</label>
            <select id="price_rate" name="price_rate" class="selectpicker">
              <option value="hourly_rate" <?php echo $price_rate == 'hourly_rate' ? 'selected="selected"' : ''; ?>>Hourly Rate</option>
              <option value="fixed_rate" <?php echo $price_rate == 'fixed_rate' ? 'selected="selected"' : ''; ?>>Fixed Rate</option>
            </select>
          </div>
        </div>
          <div class="col-sm-4 col-xs-12">
            <div class="form-group">
              <label for="guided_price">Price</label>
              <input type="number" name="guided_price" class="form-control" value="<?php echo esc_html($guided_price);?>" placeholder="Price" required>
            </div>
          </div>
        </div>
		
		<div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="what_permitted">What is permitted?</label>
                            <input type="text" name="what_permitted" class="form-control" id="what_permitted" value="<?php echo esc_html($what_permitted);?>" placeholder="What is permitted?">
                        </div>
                    </div>
              
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="what_not_permitted">What is not permitted?</label>
                            <input type="text" name="what_not_permitted" class="form-control" id="what_not_permitted" value="<?php echo esc_html($what_not_permitted);?>" placeholder="What is not permitted?">
                        </div>
                    </div>
                </div>
				
		<div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="who_permitted">Who is permitted?</label>
                            <input type="text" name="who_permitted" class="form-control" id="who_permitted" value="<?php echo esc_html($who_permitted);?>" placeholder="Who is permitted?">
                        </div>
                    </div>
                
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="who_not_permitted">Who is not permitted?</label>
                            <input type="text" name="who_not_permitted" class="form-control" id="who_not_permitted" value="<?php echo esc_html($who_not_permitted);?>" placeholder="Who is not permitted?">
                        </div>
                    </div>
                </div>
				
				<div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="license_required">License required? If so, what type?</label>
                            <input type="text" name="license_required" class="form-control" id="license_required" value="<?php echo esc_html($license_required);?>" placeholder="License required? If so, what type?">
                        </div>
                    </div>
                
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="guest_provide">What is the Guest required to bring or provide?</label>
                            <input type="text" name="guest_provide" class="form-control" id="guest_provide" value="<?php echo esc_html($guest_provide);?>" placeholder="What is the Guest required to bring or provide?">
                        </div>
                    </div>
                </div>
				
				<div class="row">
                    <div class="col-sm-12 col-sm-12">
                        <div class="form-group">
                            <label for="guest_wear">What should the Guest(s) wear?</label>
                            <textarea name="guest_wear" class="form-control" id="guest_wear" rows="3" placeholder="What should the Guest(s) wear?"><?php echo esc_textarea($guest_wear); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="homey-extra-prices">
<hr class="row-separator">
<h3 class="sub-title"><?php echo esc_html__('Setup Gears Rental Price', 'homey'); ?></h3>
<p>Add list of the gears, supplies, and equipments provided</p>
<div id="more_equipments_rows_main" class="custom-extra-prices">
    <?php
    // Check if there is data available
if ($equipment_data) {
    // Loop through each row of the repeater field
    foreach ($equipment_data as $row) {
        ?>
        <div class="more_extra_services_wrap">
            <div class="row equipment-row">
                <div class="col-sm-4 col-xs-12">
                    <div class="form-group">
                        <label for="equipment_name">Name</label>
                        <input type="text" name="equipment_name[]" class="form-control" placeholder="Enter Equipment Name" value="<?php echo esc_attr($row['equipment_name']); ?>">
                    </div>
                </div>
                <div class="col-sm-4 col-xs-12">
                    <div class="form-group">
                        <label for="equipment_price"> Price </label>
                        <input type="text" name="equipment_price[]" class="form-control" placeholder="Enter price - only digits" value="<?php echo esc_attr($row['equipment_price']); ?>">
                    </div>
                </div>
                <div class="col-sm-4 col-xs-12">
                    <div class="form-group">
                        <label for="equipment_type"> Type </label>
                        <select name="equipment_type[]" class="type-select-picker selectpicker" data-live-search="false" data-live-search-style="begins">
                            <option value="total_fee" <?php selected('total_fee', $row['equipment_type']); ?>>Total Fee</option>
                            <option value="per_guest_fee" <?php selected('per_guest_fee', $row['equipment_type']); ?>>Per Guest</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <button type="button" class="remove-equipment-row btn btn-primary btn-slim">Delete</button>
                </div>
            </div>
        </div>
        <?php
    }
}
?>

</div>
<div class="row">
    <div class="col-sm-12 col-xs-12 text-right">
        <button type="button" id="add_equipment_row" class="btn btn-primary btn-slim"><i class="fa fa-plus"></i> <?php echo esc_html__('Add More','homey'); ?></button>
    </div>
</div>
</div>
				
				<div class="row">
                    <div class="col-sm-4 col-xs-12">
                        <div class="form-group">
                            <label for="maximum_guests">Maximum Guest allowed</label>
                            <input type="number" name="maximum_guests" class="form-control" id="maximum_guests" value="<?php echo esc_html($maximum_guests);?>" placeholder="Maximum Guest allowed" required>
                        </div>
                    </div>

                    <div class="col-sm-4 col-xs-12">
                        <div class="form-group">
                            <label for="non_participants">Can Guest bring non-participants? If so, how many?</label>
                            <input type="number" name="non_participants" class="form-control" id="non_participants" value="<?php echo esc_html($non_participants);?>" placeholder="Can Guest bring non-participants? If so, how many?">
                        </div>
                    </div>
              
                    <div class="col-sm-4 col-xs-12">
                        <div class="form-group">
                            <label for="non_participants_price">Non-participants/Extra Guests price</label>
                            <input type="number" name="non_participants_price" class="form-control" id="non_participants_price" value="<?php echo esc_html($non_participants_price);?>" placeholder="Enter the price for 1">
                        </div>
                    </div>
                </div>			   
					   
    </div>
  </div>
</div>