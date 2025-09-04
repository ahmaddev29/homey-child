<?php
global $homey_prefix, $homey_local, $listing_data;
$accomodation = get_post_meta($listing_data->ID, $homey_prefix . 'accomodation', true);
$class = '';
if (isset($_GET['tab']) && $_GET['tab'] == 'bedrooms') {
  $class = 'in active';
}
$post_id = intval($_GET['edit_listing']);
$have_sleeping_accommodations = get_field('field_6479eb9f0208c', $post_id);
$include_backyard_amenity = get_field('include_backyard_amenity', $post_id);
$how_many_bedrooms = get_field('how_many_bedrooms', $post_id);
$how_many_beds = get_field('field_6479ebf2fc2e3', $post_id);
$how_many_bathrooms = get_field('field_6479ec0f536c1', $post_id);
$number_of_guests = get_field('field_6479ec324f322', $post_id);
$features_sleeping = get_field('field_6479ec5012841', $post_id);
$price_per_night = get_field('field_6479ec8dfe126', $post_id);
$cleaning_fee = get_field('field_6479ecbf61005', $post_id);
$accommodations_desc = get_field('field_6479ecf2f88fb', $post_id);
$smoking_value = get_field('smoking_allowed', $post_id);
$pets_value = get_field('pets_allowed', $post_id);
$prohibited_things = get_field('prohibited_things', $post_id);
$sleep_acc_imgs = get_field('sleep_acc_imgs', $post_id);
$have_occupancy_tax = get_field('have_occupancy_tax', $post_id);
$agreed_disclaimer = get_field('agreed_disclaimer', $post_id);
$business_tax_id = get_field('business_tax_id', $post_id);
$accommodation_tax_number = get_field('accommodation_tax_number', $post_id);
$legal_ein_name = get_field('legal_ein_name', $post_id);
$current_occupancy_state = get_field('current_occupancy_state', $post_id);
$current_occupancy_city = get_field('current_occupancy_city', $post_id);
$occupancy_tax_rate = get_field('occupancy_tax_rate', $post_id);
$not_apply = get_field('not_apply', $post_id);
?>

<div id="bedrooms-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
  <div class="block-title visible-xs">
    <h3 class="title">Sleeping Accommodations</h3>
    <p> List any House, Private Room, Treehouse, Cabin at an additional cost</p>
  </div>
  <div class="block-body">
    <div class="row">
      <div class="col-sm-12 col-xs-12">
        <div class="form-group">
          <label for="have_sleeping_accommodations">Does your Backyard Lease have Sleeping Accommodations?</label>
          <select id="have_sleeping_accommodations" name="have_sleeping_accommodations" class="selectpicker">
            <option value="yes" <?php echo $have_sleeping_accommodations == 'yes' ? 'selected="selected"' : ''; ?>>Yes</option>
            <option value="no" <?php echo $have_sleeping_accommodations == 'no' ? 'selected="selected"' : ''; ?>>No</option>
          </select>
        </div>
      </div>
    </div>

    <div id="sleeping_accommodations_qst">
      <div class="row">
        <div class="col-sm-12 col-xs-12">
          <div class="form-group">
            <label for="include_backyard_amenity">Does your sleeping accommodation include the Backyard Amenity?</label>
            <select id="include_backyard_amenity" name="include_backyard_amenity" class="selectpicker">
              <option value="yes" <?php echo $include_backyard_amenity == 'yes' ? 'selected="selected"' : ''; ?>>Yes</option>
              <option value="no" <?php echo $include_backyard_amenity == 'no' ? 'selected="selected"' : ''; ?>>No</option>
            </select>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-12 col-xs-12">
          <div class="form-group">
            <input type="checkbox" id="have_occupancy_tax" name="have_occupancy_tax" style="width: 5%;float: left;" <?php echo $have_occupancy_tax ? 'checked' : ''; ?>>
            <label for="have_occupancy_tax" style="width: 95%;">Check box if no occupancy tax or any additional occupancy related taxes in your city/country are required beyond the normal Sales Tax.</label>
          </div>
        </div>
      </div>

      <div id="sleeping_accommodations_qst_inner">
        <div class="row">
          <div class="col-sm-12 col-xs-12">
            <div class="form-group">
              <label for="business_tax_id">Business tax ID / EIN</label>
              <p>A tax ID for a business, commonly referred to as an Employer Identification Number (EIN), is a unique number (9 digits) relating directly to the business itself.
                An EIN number can also be obtained, typically at no charge, through the IRS under a sole proprietorship. This ID number may be used to identify your business
                for multiple purposes, not just taxes. You can find this number on tax or business documents you’ve received</p>
              <input type="text" name="business_tax_id" class="form-control" placeholder="Business tax ID / EIN" value="<?php echo esc_html($business_tax_id); ?>" required>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12 col-xs-12">
            <div class="form-group">
              <label for="acc_tax_num">Accommodation Tax Registration Number</label>
              <p>Many local tax offices in the US issue an accommodations tax registration number, which is the unique number you were assigned by your local taxing office.
                Depending on the office and location, this may be an accommodations tax-specific registration number or a local business registration or account number.
                Your accommodations tax registration number will vary based on the specific tax you are collecting in addition to the jurisdiction.</p>
              <input type="text" name="acc_tax_num" class="form-control" placeholder="Accommodation Tax Registration Number" value="<?php echo esc_html($accommodation_tax_number); ?>" required>
              <input type="checkbox" id="not_apply" name="not_apply" style="width: 5%;float: left;margin-top:15px;" <?php echo $not_apply ? 'checked' : ''; ?>>
              <label for="not_apply" style="width: 95%;margin-top:10px;">Does not apply</label>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-6 col-xs-12">
            <div class="form-group">
              <label for="legal_ein_name">Legal name associated with the (EIN)</label>
              <input type="text" name="legal_ein_name" class="form-control" placeholder="Legal name associated with the (EIN)" value="<?php echo esc_html($legal_ein_name); ?>" required>
            </div>
          </div>
          <div class="col-sm-6 col-xs-12">
            <div class="form-group">
              <label for="occ_state">Current State of Lodging/Occupancy</label>
              <select id="occ_state" name="occ_state" class="selectpicker">
                <option value="alabama" <?php echo $current_occupancy_state == 'alabama' ? 'selected="selected"' : ''; ?>>Alabama</option>
                <option value="alaska" <?php echo $current_occupancy_state == 'alaska' ? 'selected="selected"' : ''; ?>>Alaska</option>
                <option value="arizona" <?php echo $current_occupancy_state == 'arizona' ? 'selected="selected"' : ''; ?>>Arizona</option>
                <option value="arkansas" <?php echo $current_occupancy_state == 'arkansas' ? 'selected="selected"' : ''; ?>>Arkansas</option>
                <option value="california" <?php echo $current_occupancy_state == 'california' ? 'selected="selected"' : ''; ?>>California</option>
                <option value="colorado" <?php echo $current_occupancy_state == 'colorado' ? 'selected="selected"' : ''; ?>>Colorado</option>
                <option value="connecticut" <?php echo $current_occupancy_state == 'connecticut' ? 'selected="selected"' : ''; ?>>Connecticut</option>
                <option value="delaware" <?php echo $current_occupancy_state == 'delaware' ? 'selected="selected"' : ''; ?>>Delaware</option>
                <option value="florida" <?php echo $current_occupancy_state == 'florida' ? 'selected="selected"' : ''; ?>>Florida</option>
                <option value="georgia" <?php echo $current_occupancy_state == 'georgia' ? 'selected="selected"' : ''; ?>>Georgia</option>
                <option value="hawaii" <?php echo $current_occupancy_state == 'hawaii' ? 'selected="selected"' : ''; ?>>Hawaii</option>
                <option value="idaho" <?php echo $current_occupancy_state == 'idaho' ? 'selected="selected"' : ''; ?>>Idaho</option>
                <option value="illinois" <?php echo $current_occupancy_state == 'illinois' ? 'selected="selected"' : ''; ?>>Illinois</option>
                <option value="indiana" <?php echo $current_occupancy_state == 'indiana' ? 'selected="selected"' : ''; ?>>Indiana</option>
                <option value="iowa" <?php echo $current_occupancy_state == 'iowa' ? 'selected="selected"' : ''; ?>>Iowa</option>
                <option value="kansas" <?php echo $current_occupancy_state == 'kansas' ? 'selected="selected"' : ''; ?>>Kansas</option>
                <option value="kentucky" <?php echo $current_occupancy_state == 'kentucky' ? 'selected="selected"' : ''; ?>>Kentucky</option>
                <option value="louisiana" <?php echo $current_occupancy_state == 'louisiana' ? 'selected="selected"' : ''; ?>>Louisiana</option>
                <option value="maine" <?php echo $current_occupancy_state == 'maine' ? 'selected="selected"' : ''; ?>>Maine</option>
                <option value="maryland" <?php echo $current_occupancy_state == 'maryland' ? 'selected="selected"' : ''; ?>>Maryland</option>
                <option value="massachusetts" <?php echo $current_occupancy_state == 'massachusetts' ? 'selected="selected"' : ''; ?>>Massachusetts</option>
                <option value="michigan" <?php echo $current_occupancy_state == 'michigan' ? 'selected="selected"' : ''; ?>>Michigan</option>
                <option value="minnesota" <?php echo $current_occupancy_state == 'minnesota' ? 'selected="selected"' : ''; ?>>Minnesota</option>
                <option value="mississippi" <?php echo $current_occupancy_state == 'mississippi' ? 'selected="selected"' : ''; ?>>Mississippi</option>
                <option value="missouri" <?php echo $current_occupancy_state == 'missouri' ? 'selected="selected"' : ''; ?>>Missouri</option>
                <option value="montana" <?php echo $current_occupancy_state == 'montana' ? 'selected="selected"' : ''; ?>>Montana</option>
                <option value="nebraska" <?php echo $current_occupancy_state == 'nebraska' ? 'selected="selected"' : ''; ?>>Nebraska</option>
                <option value="nevada" <?php echo $current_occupancy_state == 'nevada' ? 'selected="selected"' : ''; ?>>Nevada</option>
                <option value="new-hampshire" <?php echo $current_occupancy_state == 'new-hampshire' ? 'selected="selected"' : ''; ?>>New Hampshire</option>
                <option value="new-jersey" <?php echo $current_occupancy_state == 'new-jersey' ? 'selected="selected"' : ''; ?>>New Jersey</option>
                <option value="new-mexico" <?php echo $current_occupancy_state == 'new-mexico' ? 'selected="selected"' : ''; ?>>New Mexico</option>
                <option value="new-york" <?php echo $current_occupancy_state == 'new-york' ? 'selected="selected"' : ''; ?>>New York</option>
                <option value="north-carolina" <?php echo $current_occupancy_state == 'north-carolina' ? 'selected="selected"' : ''; ?>>North Carolina</option>
                <option value="north-dakota" <?php echo $current_occupancy_state == 'north-dakota' ? 'selected="selected"' : ''; ?>>North Dakota</option>
                <option value="ohio" <?php echo $current_occupancy_state == 'ohio' ? 'selected="selected"' : ''; ?>>Ohio</option>
                <option value="oklahoma" <?php echo $current_occupancy_state == 'oklahoma' ? 'selected="selected"' : ''; ?>>Oklahoma</option>
                <option value="oregon" <?php echo $current_occupancy_state == 'oregon' ? 'selected="selected"' : ''; ?>>Oregon</option>
                <option value="pennsylvania" <?php echo $current_occupancy_state == 'pennsylvania' ? 'selected="selected"' : ''; ?>>Pennsylvania</option>
                <option value="rhode-island" <?php echo $current_occupancy_state == 'rhode-island' ? 'selected="selected"' : ''; ?>>Rhode Island</option>
                <option value="south-carolina" <?php echo $current_occupancy_state == 'south-carolina' ? 'selected="selected"' : ''; ?>>South Carolina</option>
                <option value="south-dakota" <?php echo $current_occupancy_state == 'south-dakota' ? 'selected="selected"' : ''; ?>>South Dakota</option>
                <option value="tennessee" <?php echo $current_occupancy_state == 'tennessee' ? 'selected="selected"' : ''; ?>>Tennessee</option>
                <option value="texas" <?php echo $current_occupancy_state == 'texas' ? 'selected="selected"' : ''; ?>>Texas</option>
                <option value="utah" <?php echo $current_occupancy_state == 'utah' ? 'selected="selected"' : ''; ?>>Utah</option>
                <option value="vermont" <?php echo $current_occupancy_state == 'vermont' ? 'selected="selected"' : ''; ?>>Vermont</option>
                <option value="virginia" <?php echo $current_occupancy_state == 'virginia' ? 'selected="selected"' : ''; ?>>Virginia</option>
                <option value="washington" <?php echo $current_occupancy_state == 'washington' ? 'selected="selected"' : ''; ?>>Washington</option>
                <option value="west-virginia" <?php echo $current_occupancy_state == 'west-virginia' ? 'selected="selected"' : ''; ?>>West Virginia</option>
                <option value="wisconsin" <?php echo $current_occupancy_state == 'wisconsin' ? 'selected="selected"' : ''; ?>>Wisconsin</option>
                <option value="wyoming" <?php echo $current_occupancy_state == 'wyoming' ? 'selected="selected"' : ''; ?>>Wyoming</option>
              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-12 col-xs-12">
            <div class="form-group">
              <label for="occ_city">Current City of Lodging/Occupancy</label>
              <input type="text" name="occ_city" class="form-control" placeholder="Current City of Lodging/Occupancy" value="<?php echo esc_html($current_occupancy_city); ?>" required>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12 col-xs-12">
            <div class="form-group">
              <label for="occ_tax_rate">Tax Rate</label>
              <p>Important! You’re responsible for providing the tax amount you want to be collected. Do not include sales tax. Sales tax has already been calculated automatically
                with your listing. This is for any additional tax that you are obligated to pay beyond sales tax. You are required to manually input the appropriate state hotel
                occupancy tax or any appropriate additional local taxes that certain cities and certain counties require for short term rental/occupancy. For example: Your state
                hotel occupancy tax rate may be 6 percent (.06) and your additional local tax is 1 percent (.01). So, your input would be 7 percent (.07) which you are responsible
                for submitting, paying, and reporting all taxes related to your bookings to the relevant tax authorities. Backyard Lease is not responsible for remitting any taxes
                associated with the Backyard Lease listings.</p>
              <input type="number" name="occ_tax_rate" class="form-control" placeholder="Tax Rate in Percentage" value="<?php echo esc_html($occupancy_tax_rate); ?>" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-12 col-xs-12">
            <div class="form-group">
              <input type="checkbox" id="agreed_disclaimer" name="agreed_disclaimer" style="width: 5%;float: left;" <?php echo $agreed_disclaimer ? 'checked' : ''; ?>>
              <label for="agreed_disclaimer" style="width: 95%;">By continuing to list a sleeping accommodation you are acknowledging and agreeing to this disclaimer
                that you (the host) are fully aware that Backyard Lease will not report or remit any of the occupancy taxes or taxes associated with the occupancy/sleeping
                accommodation booking(s). The host is obligated to pay the appropriate taxes due to its state and local tax authorities. By listing a sleeping accommodation,
                you are also acknowledging and agreeing that all the appropriate licenses, permits, and legal documents are current and in good standing before listing a
                sleeping accommodation. You are also agreeing that the rates you are manually entering are accurate and true. You, as the host, have done your due diligence
                and have collected all the necessary information and have gathered all the up-to-date rates to provide to your potential guests.</label>
            </div>
          </div>
        </div>

      </div>
    </div>

    <div id="sleeping_accommodations">
      <div class="row">
        <div class="col-sm-6 col-xs-12">
          <div class="form-group">
            <label for="how_many_bedrooms"> How many bedrooms </label>
            <input type="number" name="how_many_bedrooms" class="form-control" value="<?php echo esc_html($how_many_bedrooms); ?>">
          </div>
        </div>
        <div class="col-sm-6 col-xs-12">
          <div class="form-group">
            <label for="how_many_beds">How many beds </label>
            <input type="number" name="how_many_beds" class="form-control" value="<?php echo esc_html($how_many_beds); ?>">
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-6 col-xs-12">
          <div class="form-group">
            <label for="how_many_bathrooms"> How many bathrooms </label>
            <input type="number" name="how_many_bathrooms" class="form-control" value="<?php echo esc_html($how_many_bathrooms); ?>">
          </div>
        </div>
        <div class="col-sm-6 col-xs-12">
          <div class="form-group">
            <label for="number_of_guests">How many guests </label>
            <input type="number" name="number_of_guests" class="form-control" value="<?php echo esc_html($number_of_guests); ?>">
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-12 col-xs-12">
          <div class="form-group">
            <label for="features_sleeping">Features (cable, wifi, garage..etc)</label>
            <input type="text" name="features_sleeping" class="form-control" value="<?php echo esc_html($features_sleeping); ?>">
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-6 col-xs-12">
          <div class="form-group">
            <label for="price_per_night">Price (per night)</label>
            <input type="number" name="price_per_night" class="form-control" value="<?php echo esc_html($price_per_night); ?>" required>
          </div>
        </div>
        <div class="col-sm-6 col-xs-12">
          <div class="form-group">
            <label for="cleaning_fee">Cleaning fee</label>
            <input type="number" name="cleaning_fee" class="form-control" value="<?php echo esc_html($cleaning_fee); ?>">
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-12 col-xs-12">
          <div class="form-group">
            <label for="accommodations_desc">Special Details about the sleeping accommodation?</label>
            <?php
            // default settings - Kv_front_editor.php
            $content = $accommodations_desc;
            $editor_id = 'accommodations_desc';
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
        <div class="col-sm-6 col-xs-12">
          <?php if (isset($sleep_acc_imgs) && false != $sleep_acc_imgs) : ?>
            <div class="special-thumbs">
              <?php foreach ($sleep_acc_imgs as $image) : ?>
                <div class="thumb">
                  <img src="<?php echo esc_url($image['url']); ?>" alt="">
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <fieldset>
            <label for="acc_images[]">Add images about sleeping accommodation.</label>
            <input class="file-input" type="file" name="acc_images[]" multiple>
          </fieldset>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-6 col-xs-12">
          <label class="label-condition">Smoking allowed?</label>
        </div>
        <div class="col-sm-6 col-xs-12">
          <div class="row">
            <div class="col-sm-6 col-xs-6">
              <div class="form-group">
                <label class="control control--radio radio-tab">
                  <input name="acc_smoke" value="1" <?php echo ($smoking_value == 1) ? 'checked="checked"' : ''; ?> type="radio">
                  <span class="control-text">Yes</span>
                  <span class="control__indicator"></span>
                  <span class="radio-tab-inner"></span>
                </label>
              </div>
            </div>
            <div class="col-sm-6 col-xs-6">
              <div class="form-group">
                <label class="control control--radio radio-tab">
                  <input name="acc_smoke" value="0" <?php echo ($smoking_value == 0) ? 'checked="checked"' : ''; ?> type="radio">
                  <span class="control-text">No</span>
                  <span class="control__indicator"></span>
                  <span class="radio-tab-inner"></span>
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-xs-12">
          <label class="label-condition">Pets allowed?</label>
        </div>
        <div class="col-sm-6 col-xs-12">
          <div class="row">
            <div class="col-sm-6 col-xs-6">
              <div class="form-group">
                <label class="control control--radio radio-tab">
                  <input name="acc_pets" value="1" <?php echo ($pets_value == 1) ? 'checked="checked"' : ''; ?> type="radio">
                  <span class="control-text">Yes</span>
                  <span class="control__indicator"></span>
                  <span class="radio-tab-inner"></span>
                </label>
              </div>
            </div>
            <div class="col-sm-6 col-xs-6">
              <div class="form-group">
                <label class="control control--radio radio-tab">
                  <input name="acc_pets" value="0" <?php echo ($pets_value == 0) ? 'checked="checked"' : ''; ?> type="radio">
                  <span class="control-text">No</span>
                  <span class="control__indicator"></span>
                  <span class="radio-tab-inner"></span>
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-12 col-sm-12">
          <div class="form-group">
            <label for="additional_rules">What is prohibited?</label>
            <textarea name="acc_rules" class="form-control" id="acc_rules" rows="3"><?php echo esc_textarea($prohibited_things); ?></textarea>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<script>
  var jQuery = jQuery.noConflict();

  jQuery(document).ready(function() {
    var notApply = jQuery('input[name="not_apply"]').prop('checked');
    toggleNotApply(notApply);

    jQuery('input[name="not_apply"]').change(function() {
      toggleNotApply(jQuery(this).prop('checked'));
    });

    function toggleNotApply(isChecked) {
      if (isChecked) {
        jQuery('input[name="acc_tax_num"]').hide();
      } else {
        jQuery('input[name="acc_tax_num"]').show();
      }
    }
  });
</script>