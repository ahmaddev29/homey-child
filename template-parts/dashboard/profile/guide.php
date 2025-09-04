<?php
$userID = get_current_user_id();
$type_of_service = get_field('type_of_service', 'user_' . $userID) ?: '';
$what_to_expect_on_this_adventure = get_field('what_to_expect_on_this_adventure', 'user_' . $userID);
$price = get_field('price', 'user_' . $userID) ?: '';
$per_hour_or_per_day = get_field('per_hour_or_per_day', 'user_' . $userID) ?: '';
$per_group_or_per_individual = get_field('per_group_or_per_individual', 'user_' . $userID) ?: '';
$what_is_permitted = get_field('what_is_permitted', 'user_' . $userID) ?: '';
$what_is_not_permitted = get_field('what_is_not_permitted', 'user_' . $userID) ?: '';
$what_type_of_license = get_field('what_type_of_license', 'user_' . $userID) ?: '';
$here_is_a_list_of_the_gear_supplies_and_equipment_provided = get_field('here_is_a_list_of_the_gear_supplies_and_equipment_provided', 'user_' . $userID) ?: '';
$what_is_the_guest_required_to_bring_or_provide = get_field('what_is_the_guest_required_to_bring_or_provide', 'user_' . $userID);
$what_should_the_guests_wear = get_field('what_should_the_guests_wear', 'user_' . $userID);
$maximum_guest_allowed = get_field('maximum_guest_allowed', 'user_' . $userID);
$can_the_guest_bring_non_participants_if_so_how_many = get_field('can_the_guest_bring_non_participants_if_so_how_many', 'user_' . $userID);
?>


<div class="block guide-profile">
  <div class="block-title">
    <div class="block-left">
      <h2 class="title">About your guided service</h2>
    </div>
  </div>
  <div class="block-body">
    <div class="row">
      <div class="col-sm-12">
        <div class="form-group">
          <label for="type_of_service">Type of Service</label>
          <input type="number" id="type_of_service" class="form-control" value="<?php echo esc_html($type_of_service); ?>" placeholder="Type of Service">
        </div>
      </div>
    </div>
    <div class="row">
      <fieldset>
        <label for="what-to-expect">What to expect on this adventure.</label>
        <?php
        // default settings - Kv_front_editor.php
        $content = $what_to_expect_on_this_adventure ?: '';
        $editor_id = 'what-to-expect';
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
      </fieldset>
    </div>


    <div class="row">
      <div class="col-sm-4">
        <div class="form-group">
          <label for="price">Price</label>
          <input type="number" id="price" class="form-control" value="<?php echo esc_html($price); ?>" placeholder="Price">
        </div>
      </div>

      <div class="col-sm-4">
        <div class="form-group">
          <label for="per_hour_or_per_day">Per Hour or Per Day</label>
          <select name="per_hour_or_per_day" id="per_hour_or_per_day">
            <option value="per-hour" <?php if ($per_hour_or_per_day == 'per-hour') {
                                        echo ' selected="selected"';
                                      }; ?>>Per Hour</option>
            <option value="per-day" <?php if ($per_hour_or_per_day == 'per-day') {
                                      echo ' selected="selected"';
                                    }; ?>>Per Day</option>
          </select>
        </div>
      </div>

      <div class="col-sm-4">
        <div class="form-group">
          <label for="per_group_or_per_individual">Per Group or Per Individual</label>
          <select name="per_group_or_per_individual" id="per_group_or_per_individual">
            <option value="per-group" <?php if ($per_group_or_per_individual == 'per-group') {
                                        echo ' selected="selected"';
                                      }; ?>>Per Group</option>
            <option value="per-individual" <?php if ($per_group_or_per_individual == 'per-individual') {
                                              echo ' selected="selected"';
                                            }; ?>>Per Individual</option>
          </select>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-6">
        <div class="form-group">
          <label for="what_is_permitted">What is Permitted?</label>
          <input type="text" id="what_is_permitted" class="form-control" value="<?php echo esc_html($what_is_permitted); ?>" placeholder="What is Permitted?">
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <label for="what_is_not_permitted">What is NOT Permitted?</label>
          <input type="text" id="what_is_not_permitted" class="form-control" value="<?php echo esc_html($what_is_not_permitted); ?>" placeholder="What is NOT Permitted?">
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-6">
        <div class="form-group">
          <label for="who_is_permitted">Who is Permitted?</label>
          <input type="text" id="who_is_permitted" class="form-control" value="<?php echo esc_html($who_is_permitted); ?>" placeholder="Who is Permitted?">
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <label for="who_is_not_permitted">Who is NOT Permitted?</label>
          <input type="text" id="who_is_not_permitted" class="form-control" value="<?php echo esc_html($who_is_not_permitted); ?>" placeholder="Who is NOT Permitted?">
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-12">
        <div class="form-group">
          <label for="what_type_of_license">License required? If so, what type?</label>
          <input type="text" id="what_type_of_license" class="form-control" value="<?php echo esc_html($what_type_of_license); ?>" placeholder="License required? If so, what type?">
        </div>
      </div>
    </div>

    <div class="row">
      <fieldset>
        <label for="list-of-gear">Here is a list of the gear, supplies, and equipment provided.</label>
        <?php
        // default settings - Kv_front_editor.php
        $content = $here_is_a_list_of_the_gear_supplies_and_equipment_provided;
        $editor_id = 'list-of-gear';
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
      </fieldset>
    </div>

    <div class="row">
      <fieldset>
        <label for="provided-equipment">What is the Guest required to bring or provide?</label>
        <?php
        // default settings - Kv_front_editor.php
        $content = $what_is_the_guest_required_to_bring_or_provide;
        $editor_id = 'provided-equipment';
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
      </fieldset>
    </div>

    <div class="row">
      <fieldset>
        <label for="what-should-guests-wear">What should the Guest(s) wear?</label>
        <?php
        // default settings - Kv_front_editor.php
        $content = $what_should_the_guests_wear;
        $editor_id = 'what-should-guests-wear';
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
      </fieldset>
    </div>

    <div class="row">
      <div class="col-sm-6">
        <div class="form-group">
          <label for="maximum_guest_allowed">Maximum Guest allowed</label>
          <input type="number" id="maximum_guest_allowed" class="form-control" value="<?php echo esc_html($maximum_guest_allowed); ?>" placeholder="Maximum Guest allowed">
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <label for="can_the_guest_bring_non_participants_if_so_how_many">Can the Guest bring non-participants? If so, how many?</label>
          <input type="number" id="can_the_guest_bring_non_participants_if_so_how_many" class="form-control" value="<?php echo esc_html($can_the_guest_bring_non_participants_if_so_how_many); ?>" placeholder="Can the Guest bring non-participants? If so, how many?">
        </div>
      </div>
    </div>
  </div>
</div><!-- block -->