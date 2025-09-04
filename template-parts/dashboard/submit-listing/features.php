<?php
global $homey_local, $hide_fields;
?>
<div class="form-step" data-step="features">
  <!--step information-->
  <div class="block">
    <div class="block-title">
      <div class="block-left">
        <h2 class="title">Additional Questions</h2>
      </div><!-- block-left -->
    </div>
    <div class="block-body">
      <h3>Any Special Features</h3>
      <p>Include any unique and special features your Backyard Lease may have. Examples: Feed raccoons from the bush,
        Feed the ducks, Watch fireworks at midnight. Heated jacuzzi, hitting machine for batting cage, bring items for
        S’mores…etc</p>

      <div id="more_special_features_main" class="custom-extra-prices">
        <div class="more_special_features_wrap">
          <div class="row">
            <div class="col-sm-4 col-xs-12">
              <div class="form-group">
                <label for="name">Special Feature</label>
                <input type="text" name="special_feature[0][name]" class="form-control"
                  placeholder="Enter Special Feature">
              </div>
            </div>

          </div>
          <div class="row">
            <div class="col-sm-12 col-xs-12">
              <button type="button" data-remove="0"
                class="remove-special-features  btn btn-primary btn-slim"><?php esc_html_e('Delete', 'homey'); ?></button>
            </div>
          </div>
        </div>
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
            <input type="text" name="day-of-booking" class="form-control" required>
          </fieldset>
        </div>
        <div class="row">
          <fieldset>
            <label for="more-enjoyable-experience">What does your Guest need to bring to have a more enjoyable
              experience?</label>
            <input type="text" name="more-enjoyable-experience" class="form-control" required>
            <p>Pool towels, sunscreen, lunch, fishing gear, bait, life jackets…etc.</p>
          </fieldset>
        </div>
        <div class="row">
          <fieldset>
            <label for="included-with-booking">What will be included with your booking?</label>
            <input type="text" name="included-with-booking" class="form-control" required>
            <p>These items will all be included in the booking price. Grill, towels, fishing pole. Chairs. Balls, wifi,
              speakers, firepit …etc</p>
          </fieldset>
        </div>


        <div class="row">
          <div class="col-sm-6 col-xs-12">
            <fieldset>
              <label for="restroom-access">What kind of restroom access will your guest have?</label>
              <select name="restroom-access" class="form-control" required>
                <option value="primary-residence">Primary residence restroom</option>
                <option value="private">Private restroom</option>
                <option value="portable">Portable restroom</option>
                <option value="none">No restroom available</option>
              </select>
            </fieldset>
          </div>
          <div class="col-sm-6 col-xs-12">
            <fieldset>
              <label for="how-private">How Private is your Backyard Experience?</label>
              <select name="how-private" class="form-control" required>
                <option value="completely-secluded">Completely secluded. (Completely hidden from view)</option>
                <option value="semi-secluded">Semi secluded. ( there are obstructions that hide my fun from full view)
                </option>
                <option value="not-secluded">Not secluded. ( I can, for the most, be easily seen.)</option>
              </select>
            </fieldset>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-6 col-xs-12">
            <fieldset>
              <label for="car-amount">How many cars are allowed at the property?</label>
              <input type="number" name="car-amount" class="form-control" required>
            </fieldset>
          </div>
          <div class="col-sm-6 col-xs-12">
            <fieldset>
              <label for="extra-car-allow">How many extra cars are permitted?</label>
              <input type="number" name="extra-car-allow" class="form-control">
            </fieldset>
          </div>
          <div class="col-sm-6 col-xs-12">
            <fieldset>
              <label for="cost-per-additional-car">How much would you charge for any additional car over the specified
                amount?</label>
              <input type="number" name="cost-per-additional-car" class="form-control">
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
            $content = '';
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
          <fieldset>
            <label for="gallery_images[]">If neccesary add images about your special details.</label>
            <input class="file-input" type="file" name="gallery_images[]" multiple>
          </fieldset>
        </div>

        <div class="row">
          <div class="col-sm-6 col-xs-12">
            <fieldset>
              <label for="present-on-property">Will you be present on the property?</label>
              <select name="present-on-property" class="form-control" required>
                <option value="yes">Yes</option>
                <option value="no">No</option>
                <option value="greet-and-go">Greet & Go</option>
                <option value="not-sure">Not Sure</option>
              </select>
            </fieldset>
          </div>
          <div class="col-sm-6 col-xs-12">
            <fieldset>
              <label for="security-cameras">Does your Backyard Lease have Security Cameras?</label>
              <select id="security-cameras" name="security-cameras" class="form-control" required>
                <option value="yes">Yes</option>
                <option selected value="no">No</option>
              </select>
            </fieldset>
          </div>

        </div>

        <div id="location-camera-content" class="row">

          <fieldset>
            <label for="camera-amount">How many total (both working and non-working cameras)?</label>
            <input type="number" name="camera-amount" class="form-control">
          </fieldset>

          <fieldset id="camera-locations">
            <label for="camera-locations">Location of cameras</label>
            <input type="text" name="camera-locations" class="form-control">
          </fieldset>

        </div>

        <div class="row">
          <div class="col-sm-6 col-xs-12">

            <fieldset>
              <label for="how-much-notice">How much notice do you need for a guest to book?</label>
              <select id="how-much-notice" name="how-much-notice" class="form-control" required>
                <option value="1">1 Hour</option>
                <option value="12">12 Hours</option>
                <option value="24">24 Hours</option>
                <option value="48">48 Hours</option>
              </select>
            </fieldset>
          </div>
          <div class="col-sm-6 col-xs-12">
            <fieldset>
              <label for="how-far-in-advance">How far in advance can a guest book?</label>
              <select id="how-far-in-advance" name="how-far-in-advance" class="form-control" required>
                <option value="1">1 Week</option>
                <option value="4">1 Month</option>
                <option value="12">3 Months</option>
                <option value="24">6 Months</option>
                <option value="36">9 Months</option>
                <option value="48">1 Year</option>
              </select>
            </fieldset>
          </div>
        </div>

        <fieldset>
          <label for="how-much-time">How much time do you need in between bookings?</label>
          <select id="how-much-time" name="how-much-time" class="form-control" required>
            <option value="30">30 min</option>
            <option value="60">1 Hour</option>
            <option value="120">2 Hours</option>
            <option value="180">3 Hours</option>
          </select>
        </fieldset>

      </div>

      <hr class="row-separator" />
      <div class="row">
        <div class="col-sm-12 col-xs-12">
          <!-- <h3 class="sub-title"><?php echo esc_html__('Read before you submit your listing', 'homey'); ?></h3> -->
        </div>

        <div class="col-sm-6 col-xs-12">
          <p>Everybody deserves the best of you and kindness should never take a day off. Remember, you
            will be reviewed! So, you are not just serving your current guest but every future guest that is
            wanting to know about all the past Backyard amenity experiences on your property. Take some
            time to learn from your initial guests and see what you can improve on to make their
            experience a more memorable one. The better you serve your guest the better your chances
            are that they will return, and even better, tell their friends! Even if your amenity is not what the
            guest was hoping for the likelihood of them leaving a positive review will be based more on
            their experience with you rather than their experience in your backyard.
          </p>
        </div>
        <div class="col-sm-6 col-xs-12">
          <img src="/wp-content/uploads/2023/01/BYLhunting-scaled-29.jpeg" alt="">
        </div>

      </div>


    </div>
  </div>
</div>