<?php
global $homey_local, $hide_fields, $listing_data;
$class = '';
if (isset($_GET['tab']) && $_GET['tab'] == 'features') {
  $class = 'in active';
}
$special_features = get_field('field_6479250ae9990', $listing_data->ID);
$day_of_booking = get_field('day_of_booking', $listing_data->ID);
$more_enjoyable_experience = get_field('more_enjoyable_experience', $listing_data->ID);
$included_with_booking = get_field('included_with_booking', $listing_data->ID);
$restroom_access = get_field('restroom_access', $listing_data->ID);
$how_private = get_field('how_private', $listing_data->ID);
$car_amount = get_field('car_amount', $listing_data->ID);
$extra_car_allow = get_field('extra_car_allow', $listing_data->ID);
$cost_per_additional_car = get_field('cost_per_additional_car', $listing_data->ID);
$special_details_about_the_booking = get_field('field_6479ea3662296', $listing_data->ID);
$present_on_property = get_field('present_on_property', $listing_data->ID);
$security_cameras = get_field('security_cameras', $listing_data->ID);
$camera_amount = get_field('camera_amount', $listing_data->ID);
$location_of_cameras = get_field('location_of_cameras', $listing_data->ID);
$how_much_notice = get_field('how_much_notice', $listing_data->ID);
$how_far_in_advance = get_field('how_far_in_advance', $listing_data->ID);
$how_much_time = get_field('how_much_time', $listing_data->ID);
$special_detail_images = get_field('special_detail_images', $listing_data->ID);
?>

<div id="additional-questions-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
  <div class="block-title visible-xs">
    <h3 class="title">Any Special Features</h3>
  </div>
  <div class="block-body">
    <p>Include any unique and special features your Backyard Lease may have. Examples: Feed raccoons from the bush, Feed
      the ducks, Watch fireworks at midnight. Heated jacuzzi, hitting machine for batting cage, bring items for
      S’mores…etc</p>

    <div id="more_special_features_main" class="custom-extra-prices">
      <?php
      $count = 0;
      if (!empty($special_features)):
        foreach ($special_features as $key => $feature): ?>
          <div class="more_special_features_wrap">
            <div class="row">
              <div class="col-sm-4 col-xs-12">
                <div class="form-group">
                  <label for="name">Special Feature</label>
                  <input type="text" name="special_feature[<?php echo esc_attr($count - 1); ?>][name]" class="form-control"
                    value="<?php echo esc_html($feature['feature']); ?>" placeholder="Enter Special Feature">
                </div>
              </div>

            </div>
            <div class="row">
              <div class="col-sm-12 col-xs-12">
                <button type="button" data-remove="<?php echo esc_attr($count - 1); ?>"
                  class="remove-special-features  btn btn-primary btn-slim"><?php esc_html_e('Delete', 'homey'); ?></button>
              </div>
            </div>
          </div><?php
                $count++;
              endforeach;
            endif; ?>
    </div>
    <div class="row">
      <div class="col-sm-12 col-xs-12 text-right">
        <button type="button" id="add_more_special_features" data-increment="0" class="btn btn-primary btn-slim"><i
            class="fa fa-plus"></i> <?php echo esc_html__('Add More', 'homey'); ?></button>
      </div>
    </div>



    <div class="questions">
      <div class="row">
        <fieldset>
          <label for="day-of-booking">Does your Guest need to bring anything the day of booking?</label>
          <input type="text" name="day-of-booking" class="form-control"
            value="<?php echo esc_html($day_of_booking); ?>">
        </fieldset>
      </div>
      <div class="row">
        <fieldset>
          <label for="more-enjoyable-experience">What does your Guest need to bring to have a more enjoyable
            experience?</label>
          <input type="text" name="more-enjoyable-experience" class="form-control"
            value="<?php echo esc_html($more_enjoyable_experience); ?>">
          <p>Pool towels, sunscreen, lunch, fishing gear, bait, life jackets…etc.</p>
        </fieldset>
      </div>
      <div class="row">
        <fieldset>
          <label for="included-with-booking">What will be included with your booking?</label>
          <input type="text" name="included-with-booking" class="form-control"
            value="<?php echo esc_html($included_with_booking); ?>">
          <p>These items will all be included in the booking price. Grill, towels, fishing pole. Chairs. Balls, wifi,
            speakers, firepit …etc</p>
        </fieldset>
      </div>

      <div class="row">
        <div class="col-sm-6 col-xs-12">
          <fieldset>
            <label for="restroom-access">What kind of restroom access will your guest have?</label>
            <select name="restroom-access" class="form-control">
              <option value="primary-residence" <?php echo $restroom_access == 'primary-residence' ? ' selected="selected"' : ''; ?>>Primary residence restroom</option>
              <option value="private" <?php echo $restroom_access == 'private' ? ' selected="selected"' : ''; ?>>Private
                restroom</option>
              <option value="portable" <?php echo $restroom_access == 'portable' ? ' selected="selected"' : ''; ?>>
                Portable restroom</option>
              <option value="none" <?php echo $restroom_access == 'none' ? ' selected="selected"' : ''; ?>>No restroom
                available</option>
            </select>
          </fieldset>
        </div>
        <div class="col-sm-6 col-xs-12">
          <fieldset>
            <label for="how-private">How Private is your Backyard Experience?</label>
            <select name="how-private" class="form-control">
              <option value="completely-secluded" <?php echo $how_private == 'completely-secluded' ? ' selected="selected"' : ''; ?>>Completely secluded. (Completely hidden from view)</option>
              <option value="private" <?php echo $how_private == 'private' ? ' selected="selected"' : ''; ?>>Semi
                secluded. ( there are obstructions that hide my fun from full view)</option>
              <option value="not-seculuded" <?php echo $how_private == 'not-secluded' ? ' selected="selected"' : ''; ?>>
                Not secluded. ( I can, for the most, be easily seen.)</option>
            </select>
          </fieldset>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-6 col-xs-12">
          <fieldset>
            <label for="car-amount">How many cars are allowed at the property?</label>
            <input type="number" name="car-amount" class="form-control" value="<?php echo esc_html($car_amount); ?>">
          </fieldset>
        </div>
        <div class="col-sm-6 col-xs-12">
          <fieldset>
            <label for="extra-car-allow">How many extra cars are permitted?</label>
            <input type="number" name="extra-car-allow" class="form-control"
              value="<?php echo esc_html($extra_car_allow); ?>">
          </fieldset>
        </div>
        <div class="col-sm-6 col-xs-12">
          <fieldset>
            <label for="cost-per-additional-car">How much would you charge for any additional car over the specified
              amount?</label>
            <input type="number" name="cost-per-additional-car" class="form-control"
              value="<?php echo esc_html($cost_per_additional_car); ?>">
          </fieldset>
        </div>
      </div>
      <div class="row">
        <fieldset>
          <label for="special-details-booking">Special details about the booking?</label>
          <p>No worries! This information will only be shared once the booking has been confirmed). Where to park,
            special directions. gate code...etc</p>
          <?php
          // default settings - Kv_front_editor.php
          $content = $special_details_about_the_booking;
          $editor_id = 'special-details-booking';
          $settings = array(
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
        <?php if (isset($special_detail_images) && false != $special_detail_images): ?>
          <div class="special-thumbs">
            <?php foreach ($special_detail_images as $image): ?>
              <div class="thumb">
                <img src="<?php echo esc_url($image['url']); ?>" alt="">
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <fieldset>
          <label for="gallery_images[]">If neccesary add images about your special details.</label>
          <input class="file-input" type="file" name="gallery_images[]" multiple>
        </fieldset>
      </div>


      <div class="row">
        <div class="col-sm-6 col-xs-12">
          <fieldset>
            <label for="present-on-property">Will you be present on the property?</label>
            <select name="present-on-property" class="form-control">
              <option value="yes" <?php echo $present_on_property == 'yes' ? ' selected="selected"' : ''; ?>>Yes</option>
              <option value="no" <?php echo $present_on_property == 'no' ? ' selected="selected"' : ''; ?>>No</option>
              <option value="greet-and-go" <?php echo $present_on_property == 'greet-and-go' ? ' selected="selected"' : ''; ?>>Greet & Go</option>
              <option value="not-sure" <?php echo $present_on_property == 'not-sure' ? ' selected="selected"' : ''; ?>>Not
                Sure</option>
            </select>
          </fieldset>
        </div>
        <div class="col-sm-6 col-xs-12">
          <fieldset>
            <label for="security-cameras">Does your Backyard Lease have Security Cameras?</label>
            <select id="security-cameras" name="security-cameras" class="form-control">
              <option value="yes" <?php echo $security_cameras == 'not-sure' ? ' selected="selected"' : ''; ?>>Yes
              </option>
              <option value="no" <?php echo $security_cameras == 'no' ? ' selected="selected"' : ''; ?>>No</option>
            </select>
          </fieldset>
        </div>

      </div>

      <div id="location-camera-content" class="row">

        <fieldset>
          <label for="camera-amount">How many total (both working and non-working cameras)?</label>
          <input type="number" name="camera-amount" class="form-control"
            value="<?php echo esc_html($camera_amount); ?>">
        </fieldset>

        <fieldset id="camera-locations">
          <label for="camera-locations">Location of cameras</label>
          <input type="text" name="camera-locations" class="form-control"
            value="<?php echo esc_html($location_of_cameras); ?>">
        </fieldset>

      </div>

      <div class="row">
        <div class="col-sm-6 col-xs-12">

          <fieldset>
            <label for="how-much-notice">How much notice do you need for a guest to book?</label>
            <select id="how-much-notice" name="how-much-notice" class="form-control">
              <option value="1" <?php echo $how_much_notice == '1' ? ' selected="selected"' : ''; ?>>1 Hour</option>
              <option value="12" <?php echo $how_much_notice == '12' ? ' selected="selected"' : ''; ?>>12 Hours</option>
              <option value="24" <?php echo $how_much_notice == '24' ? ' selected="selected"' : ''; ?>>24 Hours</option>
              <option value="48" <?php echo $how_much_notice == '48' ? ' selected="selected"' : ''; ?>>48 Hours</option>
            </select>
          </fieldset>
        </div>
        <div class="col-sm-6 col-xs-12">
          <fieldset>
            <label for="how-far-in-advance">How far in advance can a guest book?</label>
            <select id="how-far-in-advance" name="how-far-in-advance" class="form-control">
              <option value="1" <?php echo $how_far_in_advance == '1' ? ' selected="selected"' : ''; ?>>1 Week</option>
              <option value="4" <?php echo $how_far_in_advance == '4' ? ' selected="selected"' : ''; ?>>1 Month</option>
              <option value="12" <?php echo $how_far_in_advance == '12' ? ' selected="selected"' : ''; ?>>3 Months
              </option>
              <option value="24" <?php echo $how_far_in_advance == '24' ? ' selected="selected"' : ''; ?>>6 Months
              </option>
              <option value="36" <?php echo $how_far_in_advance == '36' ? ' selected="selected"' : ''; ?>>9 Months
              </option>
              <option value="48" <?php echo $how_far_in_advance == '48' ? ' selected="selected"' : ''; ?>>1 Year</option>
            </select>
          </fieldset>
        </div>
      </div>

      <fieldset>
        <label for="how-much-time">How much time do you need in between bookings?</label>
        <select id="how-much-time" name="how-much-time" class="form-control">
          <option value="30" <?php echo $how_much_time == '30' ? ' selected="selected"' : ''; ?>>30 min</option>
          <option value="60" <?php echo $how_much_time == '60' ? ' selected="selected"' : ''; ?>>1 Hour</option>
          <option value="120" <?php echo $how_much_time == '120' ? ' selected="selected"' : ''; ?>>2 Hours</option>
          <option value="180" <?php echo $how_much_time == '180' ? ' selected="selected"' : ''; ?>>3 Hours</option>
        </select>
      </fieldset>

    </div>

  </div>
</div>