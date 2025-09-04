<?php
global $homey_local;
?>
<div class="form-step" data-step="bedrooms">
  <!--step information-->
  <div class="block">
    <div class="block-title">
      <div class="block-left">
        <h3 class="title">Sleeping Accommodations</h3>
        <p> List any House, Private Room, Treehouse, Cabin at an additional cost</p>
      </div><!-- block-left -->
    </div>
    <div class="block-body">
      <div class="row">
        <div class="col-sm-12 col-xs-12">
          <div class="form-group">
            <label for="have_sleeping_accommodations">Does your Backyard Lease have Sleeping Accommodations?</label>
            <select id="have_sleeping_accommodations" name="have_sleeping_accommodations" class="selectpicker">
              <option value="yes">Yes</option>
              <option value="no" selected>No</option>
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
                <option value="yes">Yes</option>
                <option value="no" selected>No</option>
              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-12 col-xs-12">
            <div class="form-group">
              <input type="checkbox" id="have_occupancy_tax" name="have_occupancy_tax" style="width: 5%;float: left;">
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
                <input type="text" name="business_tax_id" class="form-control" placeholder="Business tax ID / EIN" required>
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
                <input type="text" name="acc_tax_num" class="form-control" placeholder="Accommodation Tax Registration Number" required>
                <input type="checkbox" id="not_apply" name="not_apply" style="width: 5%;float: left;margin-top:15px;">
                <label for="not_apply" style="width: 95%;margin-top:10px;">Does not apply</label>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-6 col-xs-12">
              <div class="form-group">
                <label for="legal_ein_name">Legal name associated with the (EIN)</label>
                <input type="text" name="legal_ein_name" class="form-control" placeholder="Legal name associated with the (EIN)" required>
              </div>
            </div>
            <div class="col-sm-6 col-xs-12">
              <div class="form-group">
                <label for="occ_state">Current State of Lodging/Occupancy</label>
                <select id="occ_state" name="occ_state" class="selectpicker">
                  <option value="alabama" selected>Alabama</option>
                  <option value="alaska">Alaska</option>
                  <option value="arizona">Arizona</option>
                  <option value="arkansas">Arkansas</option>
                  <option value="california">California</option>
                  <option value="colorado">Colorado</option>
                  <option value="connecticut">Connecticut</option>
                  <option value="delaware">Delaware</option>
                  <option value="florida">Florida</option>
                  <option value="georgia">Georgia</option>
                  <option value="hawaii">Hawaii</option>
                  <option value="idaho">Idaho</option>
                  <option value="illinois">Illinois</option>
                  <option value="indiana">Indiana</option>
                  <option value="iowa">Iowa</option>
                  <option value="kansas">Kansas</option>
                  <option value="kentucky">Kentucky</option>
                  <option value="louisiana">Louisiana</option>
                  <option value="maine">Maine</option>
                  <option value="maryland">Maryland</option>
                  <option value="massachusetts">Massachusetts</option>
                  <option value="michigan">Michigan</option>
                  <option value="minnesota">Minnesota</option>
                  <option value="mississippi">Mississippi</option>
                  <option value="missouri">Missouri</option>
                  <option value="montana">Montana</option>
                  <option value="nebraska">Nebraska</option>
                  <option value="nevada">Nevada</option>
                  <option value="new-hampshire">New Hampshire</option>
                  <option value="new-jersey">New Jersey</option>
                  <option value="new-mexico">New Mexico</option>
                  <option value="new-york">New York</option>
                  <option value="north-carolina">North Carolina</option>
                  <option value="north-dakota">North Dakota</option>
                  <option value="ohio">Ohio</option>
                  <option value="oklahoma">Oklahoma</option>
                  <option value="oregon">Oregon</option>
                  <option value="pennsylvania">Pennsylvania</option>
                  <option value="rhode-island">Rhode Island</option>
                  <option value="south-carolina">South Carolina</option>
                  <option value="south-dakota">South Dakota</option>
                  <option value="tennessee">Tennessee</option>
                  <option value="texas">Texas</option>
                  <option value="utah">Utah</option>
                  <option value="vermont">Vermont</option>
                  <option value="virginia">Virginia</option>
                  <option value="washington">Washington</option>
                  <option value="west-virginia">West Virginia</option>
                  <option value="wisconsin">Wisconsin</option>
                  <option value="wyoming">Wyoming</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-12 col-xs-12">
              <div class="form-group">
                <label for="occ_city">Current City of Lodging/Occupancy</label>
                <input type="text" name="occ_city" class="form-control" placeholder="Current City of Lodging/Occupancy" required>
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
                <input type="number" name="occ_tax_rate" class="form-control" placeholder="Tax Rate in Percentage" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-12 col-xs-12">
              <div class="form-group">
                <input type="checkbox" id="agreed_disclaimer" name="agreed_disclaimer" style="width: 5%;float: left;">
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
              <input type="number" name="how_many_bedrooms" class="form-control" placeholder="How many bedrooms">
            </div>
          </div>
          <div class="col-sm-6 col-xs-12">
            <div class="form-group">
              <label for="how_many_beds">How many beds </label>
              <input type="number" name="how_many_beds" class="form-control" placeholder="How many beds">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-6 col-xs-12">
            <div class="form-group">
              <label for="how_many_bathrooms"> How many bathrooms </label>
              <input type="number" name="how_many_bathrooms" class="form-control" placeholder="How many bathrooms">
            </div>
          </div>
          <div class="col-sm-6 col-xs-12">
            <div class="form-group">
              <label for="number_of_guests">How many guests </label>
              <input type="number" name="number_of_guests" class="form-control" placeholder="How many guests">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-12 col-xs-12">
            <div class="form-group">
              <label for="features_sleeping">Features (cable, wifi, garage..etc)</label>
              <input type="text" name="features_sleeping" class="form-control" placeholder="Features (cable, wifi, garage..etc)">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-6 col-xs-12">
            <div class="form-group">
              <label for="price_per_night">Price (per night)</label>
              <input type="number" name="price_per_night" class="form-control" placeholder="Price (per night)" required>
            </div>
          </div>
          <div class="col-sm-6 col-xs-12">
            <div class="form-group">
              <label for="cleaning_fee">Cleaning fee</label>
              <input type="number" name="cleaning_fee" class="form-control" placeholder="Cleaning fee">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-12 col-xs-12">
            <div class="form-group">
              <label for="accommodations_desc">Special Details about the sleeping accommodation?</label>
              <?php
              // default settings - Kv_front_editor.php
              $content = '';
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
          <fieldset>
            <label for="acc_images[]">Add images about sleeping accommodation.</label>
            <input class="file-input" type="file" name="acc_images[]" multiple>
          </fieldset>
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
                    <input name="acc_smoke" value="1" checked="checked" type="radio">
                    <span class="control-text">Yes</span>
                    <span class="control__indicator"></span>
                    <span class="radio-tab-inner"></span>
                  </label>
                </div>
              </div>
              <div class="col-sm-6 col-xs-6">
                <div class="form-group">
                  <label class="control control--radio radio-tab">
                    <input name="acc_smoke" value="0" type="radio">
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
                    <input name="acc_pets" value="1" checked="checked" type="radio">
                    <span class="control-text">Yes</span>
                    <span class="control__indicator"></span>
                    <span class="radio-tab-inner"></span>
                  </label>
                </div>
              </div>
              <div class="col-sm-6 col-xs-6">
                <div class="form-group">
                  <label class="control control--radio radio-tab">
                    <input name="acc_pets" value="0" type="radio">
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
              <textarea name="acc_rules" class="form-control" id="acc_rules" rows="3" placeholder="What is prohibited?"></textarea>
            </div>
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