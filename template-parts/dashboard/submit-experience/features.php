<?php
global $homey_local, $hide_fields;
?>
<div class="form-step">
    <!--step information-->
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title">Any Special Features</h2>
            </div><!-- block-left -->
        </div>
        <div class="block-body">
          <p>Include any unique and special features your Backyard Lease may have. Examples: Feed raccoons from the bush, Feed the ducks, Watch fireworks at midnight. Heated jacuzzi, hitting machine for batting cage, bring items for S’mores…etc</p>

            <div id="more_special_features_main" class="custom-extra-prices">
              <div class="more_special_features_wrap">
                  <div class="row">
                      <div class="col-sm-4 col-xs-12">
                          <div class="form-group">
                              <label for="name">Special Feature</label>
                              <input type="text" name="special_feature[0][name]" class="form-control" placeholder="Enter Special Feature">
                          </div>
                      </div>

                  </div>
                  <div class="row">
                      <div class="col-sm-12 col-xs-12">
                          <button type="button" data-remove="0" class="remove-special-features  btn btn-primary btn-slim"><?php esc_html_e('Delete', 'homey'); ?></button>
                      </div>
                  </div>
              </div>
          </div>
          <div class="row">
              <div class="col-sm-12 col-xs-12 text-right">
                  <button type="button" id="add_more_special_features" data-increment="0" class="btn btn-primary btn-slim"><i class="fa fa-plus"></i> <?php echo esc_html__('Add More','homey'); ?></button>
              </div>
          </div>

        </div>
    </div>
</div>