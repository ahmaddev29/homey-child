<?php
global $homey_local;
?>
<div class="form-step" data-step="guidedService">
    <!--step information-->
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h3 class="title">Guided Service</h3>
            </div><!-- block-left -->
        </div>
        <div class="block-body">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="form-group">
                        <label for="have_guided_service">Does your Backyard Lease have Guided Service?</label>
                        <select id="have_guided_service" name="have_guided_service" class="selectpicker">
                            <option value="no_guide_needed" selected>No Guide Needed</option>
                            <option value="guide_required">Guide Required</option>
                            <option value="guide_is_optional">Guide is Optional</option>
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
                            $content = '';
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
                            <textarea name="what_expect" class="form-control" id="what_expect" rows="3" placeholder="What to expect on this adventure?"></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-4 col-xs-12">
                        <div class="form-group">
                            <label for="price_type">Price Type</label>
                            <select id="price_type" name="price_type" class="selectpicker">
                                <option value="per_guest" selected>Per Guest</option>
                                <option value="per_group">Per Group</option>
                                <option value="flat_fee">Flat Fee</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4 col-xs-12">
                        <div class="form-group">
                            <label for="price_rate">Price Rate</label>
                            <select id="price_rate" name="price_rate" class="selectpicker">
                                <option value="hourly_rate" selected>Hourly Rate</option>
                                <option value="fixed_rate">Fixed Rate</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4 col-xs-12">
                        <div class="form-group">
                            <label for="guided_price">Price</label>
                            <input type="number" name="guided_price" class="form-control" placeholder="Price" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="what_permitted">What is permitted?</label>
                            <input type="text" name="what_permitted" class="form-control" id="what_permitted" placeholder="What is permitted?">
                        </div>
                    </div>

                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="what_not_permitted">What is not permitted?</label>
                            <input type="text" name="what_not_permitted" class="form-control" id="what_not_permitted" placeholder="What is not permitted?">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="who_permitted">Who is permitted?</label>
                            <input type="text" name="who_permitted" class="form-control" id="who_permitted" placeholder="Who is permitted?">
                        </div>
                    </div>

                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="who_not_permitted">Who is not permitted?</label>
                            <input type="text" name="who_not_permitted" class="form-control" id="who_not_permitted" placeholder="Who is not permitted?">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="license_required">License required? If so, what type?</label>
                            <input type="text" name="license_required" class="form-control" id="license_required" placeholder="License required? If so, what type?">
                        </div>
                    </div>

                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="guest_provide">What is the Guest required to bring or provide?</label>
                            <input type="text" name="guest_provide" class="form-control" id="guest_provide" placeholder="What is the Guest required to bring or provide?">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-sm-12">
                        <div class="form-group">
                            <label for="guest_wear">What should the Guest(s) wear?</label>
                            <textarea name="guest_wear" class="form-control" id="guest_wear" rows="3" placeholder="What should the Guest(s) wear?"></textarea>
                        </div>
                    </div>
                </div>

                <div class="homey-extra-prices">
                    <hr class="row-separator">
                    <h3 class="sub-title"><?php echo esc_html__('Setup Gears Rental Price', 'homey'); ?></h3>
                    <p>Add list of the gears, supplies, and equipments provided</p>
                    <div id="more_equipments_rows_main" class="custom-extra-prices">
                        <div class="more_extra_services_wrap">
                            <div class="row">
                                <div class="col-sm-4 col-xs-12">
                                    <div class="form-group">
                                        <label for="equipment_name"><?php echo esc_attr($homey_local['ex_name']); ?></label>
                                        <input type="text" name="equipment_name[]" class="form-control" placeholder="Enter Equipment Name">
                                    </div>
                                </div>
                                <div class="col-sm-4 col-xs-12">
                                    <div class="form-group">
                                        <label for="equipment_price"> <?php echo esc_attr($homey_local['ex_price']); ?> </label>
                                        <input type="text" name="equipment_price[]" class="form-control" placeholder="<?php echo esc_attr($homey_local['ex_price_plac']); ?>">
                                    </div>
                                </div>
                                <div class="col-sm-4 col-xs-12">
                                    <div class="form-group">
                                        <label for="equipment_type"> <?php echo esc_attr($homey_local['ex_type']); ?> </label>

                                        <select name="equipment_type[]" class="selectpicker" data-live-search="false" data-live-search-style="begins">
                                            <option value="total_fee">Total Fee</option>
                                            <option value="per_guest_fee"><?php echo esc_attr($homey_local['ex_per_guest']); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-xs-12">
                                    <button type="button" class="remove-equipment-row btn btn-primary btn-slim"><?php esc_html_e('Delete', 'homey'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-xs-12 text-right">
                            <button type="button" id="add_equipment_row" class="btn btn-primary btn-slim"><i class="fa fa-plus"></i> <?php echo esc_html__('Add More', 'homey'); ?></button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-4 col-xs-12">
                        <div class="form-group">
                            <label for="maximum_guests">Maximum Guest allowed</label>
                            <input type="number" name="maximum_guests" class="form-control" id="maximum_guests" placeholder="Maximum Guest allowed" required>
                        </div>
                    </div>

                    <div class="col-sm-4 col-xs-12">
                        <div class="form-group">
                            <label for="non_participants">Can Guest bring non-participants? If so, how many?</label>
                            <input type="number" name="non_participants" class="form-control" id="non_participants" placeholder="Can Guest bring non-participants? If so, how many?">
                        </div>
                    </div>

                    <div class="col-sm-4 col-xs-12">
                        <div class="form-group">
                            <label for="non_participants_price">Non-participants/Extra Guests price</label>
                            <input type="number" name="non_participants_price" class="form-control" id="non_participants_price" placeholder="Enter the price for 1">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>