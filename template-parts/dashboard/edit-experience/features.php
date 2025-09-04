<?php
global $homey_local, $hide_fields, $listing_data;
$class = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'features') {
    $class = 'in active';
}
$special_features = get_field('special_features', $listing_data->ID);
?>

<div id="additional-questions-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
        <h3 class="title">Additional Questions</h3>
    </div>
    <div class="block-body">
      <h4>Any Special Features</h4>
      <p>Include any unique and special features your Backyard Lease may have. Examples: Feed raccoons from the bush, Feed the ducks, Watch fireworks at midnight. Heated jacuzzi, hitting machine for batting cage, bring items for S’mores…etc</p>

        <div id="more_special_features_main" class="custom-extra-prices">
            <?php
            $count = 0;
            if ( ! empty( $special_features ) ) :
                foreach ( $special_features as $key => $feature ) : ?>
                    <div class="more_special_features_wrap">
                        <div class="row">
                            <div class="col-sm-4 col-xs-12">
                                <div class="form-group">
                                    <label for="name">Special Feature</label>
                                    <input type="text" name="special_feature[<?php echo esc_attr( $count-1 ); ?>][name]" class="form-control" value="<?php echo esc_html( $feature['feature'] ); ?>" placeholder="Enter Special Feature">
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-xs-12">
                                <button type="button" data-remove="<?php echo esc_attr( $count-1 ); ?>" class="remove-special-features  btn btn-primary btn-slim"><?php esc_html_e('Delete', 'homey'); ?></button>
                            </div>
                        </div>
                    </div><?php
                    $count++;
                endforeach;
            endif; ?>
        </div>
        <div class="row">
            <div class="col-sm-12 col-xs-12 text-right">
                <button type="button" id="add_more_special_features" data-increment="0" class="btn btn-primary btn-slim"><i class="fa fa-plus"></i> <?php echo esc_html__('Add More','homey'); ?></button>
            </div>
        </div>

    </div>
</div>