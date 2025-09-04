<?php
global $post;
$listing_id = $post->ID;
$extra_prices = get_post_meta($listing_id, 'homey_extra_prices', true);

$homey_booking_type = homey_booking_type();
if ($homey_booking_type == 'per_hour') {
    $per_night_text = esc_html__('hr', 'homey');
    $per_nightguest_text = esc_html__('per hr per guest', 'homey');
} else {
    $per_night_text = esc_html__('per night', 'homey');
    $per_nightguest_text = esc_html__('per night per guest', 'homey');
}

if (!empty($extra_prices)) {
    $total_options = count($extra_prices);
    $max_visible = 5;
    ?>

    <div class="block-body">
        <div>
            <h3 class="title"><?php echo esc_html__('Optional Add-Ons', 'homey'); ?></h3>
        </div><!-- block-left -->
        <div>
            <div class="optional-addons-cols">

                <?php
                if (is_array($extra_prices)) {
                    $count = 0;
                    foreach ($extra_prices as $key => $option) {
                        $type_text = '';
                        $type = $option['type'];
                        if ($type == 'single_fee') {
                            $type_text = esc_html__('single fee', 'homey');
                        } elseif ($type == 'per_night') {
                            $type_text = $per_night_text;
                        } elseif ($type == 'per_guest') {
                            $type_text = esc_html__('per guest', 'homey');
                        } elseif ($type == 'per_night_per_guest') {
                            $type_text = $per_nightguest_text;
                        }
                        $count++;
                        $hidden_class = '';
                        if ($count > $max_visible) {
                            $hidden_class = 'hidden-extra-price';
                        }

                        ?>
                        <div class="addons-box <?php echo esc_attr($hidden_class); ?>">
                            <div class="addons-box-price">
                                <strong><?php echo homey_formatted_price($option['price'], true); ?></strong>/<?php echo esc_attr($type_text); ?>
                            </div>
                            <div class="addons-box-title"><strong><?php echo esc_attr($option['name']); ?></strong></div>
                        </div>
                        <?php
                    }
                } ?>

            </div>

            <?php if ($total_options > $max_visible) { ?>
                <button id="show-more-options" class="btn show-more-options">
                    <?php echo sprintf(esc_html__('Show all %d amenities', 'homey'), $total_options); ?>
                </button>
            <?php } ?>

        </div><!-- block-right -->
    </div><!-- block-body -->

    <?php
}
?>