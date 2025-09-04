<?php
global $userID, $homey_prefix, $homey_local;
$street_address  =  get_the_author_meta($homey_prefix . 'street_address', $userID);
$country  =  get_the_author_meta($homey_prefix . 'country', $userID);
$neighborhood  =  get_the_author_meta($homey_prefix . 'neighborhood', $userID);
$zipcode  =  get_the_author_meta($homey_prefix . 'zipcode', $userID);
$state  =  get_the_author_meta($homey_prefix . 'state', $userID);
$city  =  get_the_author_meta($homey_prefix . 'city', $userID);
$apt_suit  =  get_the_author_meta($homey_prefix . 'apt_suit', $userID);

$street_address_guest  =  get_the_author_meta($homey_prefix . 'street_address_guest', $userID);
$country_guest  =  get_the_author_meta($homey_prefix . 'country_guest', $userID);
$neighborhood_guest  =  get_the_author_meta($homey_prefix . 'neighborhood_guest', $userID);
$zipcode_guest  =  get_the_author_meta($homey_prefix . 'zipcode_guest', $userID);
$state_guest  =  get_the_author_meta($homey_prefix . 'state_guest', $userID);
$city_guest  =  get_the_author_meta($homey_prefix . 'city_guest', $userID);
$apt_suit_guest  =  get_the_author_meta($homey_prefix . 'apt_suit_guest', $userID);

?>
<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php echo esc_html__('Address', 'homey'); ?></h2>
            <?php if (homey_is_host()) { ?>
                <p><?php echo esc_html__('Your address will remain private. This field is for verification purposes only. Once your listing is booked you will have the opportunity to provide the address and other detailed information to the guest.', 'homey'); ?></p>
            <?php } ?>

            <?php if (homey_is_renter()) { ?>
                <p><?php echo esc_html__('Your address will remain private. This field is for verification purposes only. Hosts will not be able to see this information.', 'homey'); ?></p>
            <?php } ?>
        </div>
    </div>
    <div class="block-body">
        <div class="row">
            <?php if (homey_is_renter()) { ?>
                <div class="col-sm-9">
                    <div class="form-group">
                        <label for="street_address_guest"><?php echo esc_html__('Street Address', 'homey'); ?></label>
                        <input type="text" id="street_address_guest" class="form-control" value="<?php echo esc_attr($street_address_guest); ?>" placeholder="<?php echo esc_attr__('Enter street address', 'homey'); ?>">
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="apt_suit_guest"> <?php echo esc_html__('Apt, Suite', 'homey'); ?> </label>
                        <input type="text" id="apt_suit_guest" class="form-control" value="<?php echo esc_attr($apt_suit_guest); ?>" placeholder=" <?php echo esc_attr__('Ex. #123', 'homey'); ?> ">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="city_guest"><?php echo esc_html__('City', 'homey'); ?></label>
                        <input type="text" id="city_guest" class="form-control" value="<?php echo esc_attr($city_guest); ?>" placeholder="<?php echo esc_attr__('Enter your city', 'homey'); ?>">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="state_guest"><?php echo esc_html__('State', 'homey'); ?></label>
                        <input type="text" id="state_guest" class="form-control" value="<?php echo esc_attr($state_guest); ?>" placeholder="<?php echo esc_attr__('Enter your state', 'homey'); ?>">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="zipcode_guest"><?php echo esc_html__('Zip Code', 'homey'); ?></label>
                        <input type="text" id="zipcode_guest" class="form-control" value="<?php echo esc_attr($zipcode_guest); ?>" placeholder="<?php echo esc_attr__('Enter zip code', 'homey'); ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="neighborhood_guest"><?php echo esc_html__('Neighborhood', 'homey'); ?></label>
                        <input type="text" id="neighborhood_guest" class="form-control" value="<?php echo esc_attr($neighborhood_guest); ?>" placeholder="<?php echo esc_attr__('Neighborhood', 'homey'); ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="country_guest"><?php echo esc_html__('Country', 'homey'); ?></label>
                        <input type="text" id="country_guest" class="form-control" value="<?php echo esc_attr($country_guest); ?>" placeholder="<?php echo esc_attr__('country', 'homey'); ?>">
                    </div>
                </div>
            <?php } else { ?>
                <div class="col-sm-9">
                    <div class="form-group">
                        <label for="street_address"><?php echo esc_html__('Street Address', 'homey'); ?></label>
                        <input type="text" id="street_address" class="form-control" value="<?php echo esc_attr($street_address); ?>" placeholder="<?php echo esc_attr__('Enter street address', 'homey'); ?>">
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="apt_suit"> <?php echo esc_html__('Apt, Suite', 'homey'); ?> </label>
                        <input type="text" id="apt_suit" class="form-control" value="<?php echo esc_attr($apt_suit); ?>" placeholder=" <?php echo esc_attr__('Ex. #123', 'homey'); ?> ">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="city"><?php echo esc_html__('City', 'homey'); ?></label>
                        <input type="text" id="city" class="form-control" value="<?php echo esc_attr($city); ?>" placeholder="<?php echo esc_attr__('Enter your city', 'homey'); ?>">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="state"><?php echo esc_html__('State', 'homey'); ?></label>
                        <input type="text" id="state" class="form-control" value="<?php echo esc_attr($state); ?>" placeholder="<?php echo esc_attr__('Enter your state', 'homey'); ?>">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="zipcode"><?php echo esc_html__('Zip Code', 'homey'); ?></label>
                        <input type="text" id="zipcode" class="form-control" value="<?php echo esc_attr($zipcode); ?>" placeholder="<?php echo esc_attr__('Enter zip code', 'homey'); ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="neighborhood"><?php echo esc_html__('Neighborhood', 'homey'); ?></label>
                        <input type="text" id="neighborhood" class="form-control" value="<?php echo esc_attr($neighborhood); ?>" placeholder="<?php echo esc_attr__('Neighborhood', 'homey'); ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="country"><?php echo esc_html__('Country', 'homey'); ?></label>
                        <input type="text" id="country" class="form-control" value="<?php echo esc_attr($country); ?>" placeholder="<?php echo esc_attr__('country', 'homey'); ?>">
                    </div>
                </div>
            <?php } ?>
            <div class="col-sm-12 text-right">
                <button type="submit" class="homey_profile_save btn btn-success btn-xs-full-width"><?php echo esc_attr($homey_local['save_btn']); ?></button>
            </div>
        </div>
    </div>
</div><!-- block -->