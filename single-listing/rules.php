<?php global $post, $homey_prefix, $homey_local, $hide_labels;
$smoke = homey_get_listing_data('smoke');
$pets = homey_get_listing_data('pets');
$party = homey_get_listing_data('party');
$children = homey_get_listing_data('children');
$additional_rules = homey_get_listing_data('additional_rules');
$cancellation_policy = homey_get_listing_data('cancellation_policy');
$select_cancellation = get_field('select_cancellation', $post->ID);
$cancellation_options = array(
    'no_cancellations' => 'No Cancellations.',
    'no_refunds_24' => 'No refunds within 24 hours of reservation.',
    'no_refunds_48' => 'No refunds within 48 hours of reservation.'
);

if ($select_cancellation == 'no_cancellations') {
    $cancellation_allow = 'fa fa-times';
} else {
    $cancellation_allow = 'fa fa-check';
}

if ($smoke != 1) {
    $smoke_allow = 'fa fa-times';
    $smoke_text = esc_html__(homey_option('sn_text_no'), 'homey');
} else {
    $smoke_allow = 'fa fa-check';
    $smoke_text = esc_html__(homey_option('sn_text_yes'), 'homey');
}

if ($pets != 1) {
    $pets_allow = 'fa fa-times';
    $pets_text = esc_html__(homey_option('sn_text_no'), 'homey');
} else {
    $pets_allow = 'fa fa-check';
    $pets_text = esc_html__(homey_option('sn_text_yes'), 'homey');
}

if ($party != 1) {
    $party_allow = 'fa fa-times';
    $party_text = esc_html__(homey_option('sn_text_no'), 'homey');
} else {
    $party_allow = 'fa fa-check';
    $party_text = esc_html__(homey_option('sn_text_yes'), 'homey');
}

if ($children != 1) {
    $children_allow = 'fa fa-times';
    $children_text = esc_html__(homey_option('sn_text_no'), 'homey');
} else {
    $children_allow = 'fa fa-check';
    $children_text = esc_html__(homey_option('sn_text_yes'), 'homey');
}
?>
<div id="rules-section" class="rules-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title">Rules & Policies</h3>
                </div><!-- block-left -->
                <div class="block-right">
                    <div class="row">
                        <div class="col-sm-4 col-xs-12">
                            <strong>Terms & rules</strong>
                            <p>
                                <?php if ($hide_labels['sn_smoking_allowed'] != 1) { ?>
                                    <span style="display: block;">
                                        <i class="<?php echo esc_attr($smoke_allow); ?>"></i>
                                        <?php echo esc_attr(homey_option('sn_smoking_allowed')); ?>:
                                        <?php echo esc_attr($smoke_text); ?></span>
                                <?php } ?>
                                <?php if ($hide_labels['sn_pets_allowed'] != 1) { ?>
                                    <span style="display: block;">
                                        <i class="<?php echo esc_attr($pets_allow); ?>"></i>
                                        <?php echo esc_attr(homey_option('sn_pets_allowed')); ?>:
                                        <?php echo esc_attr($pets_text); ?></span>
                                <?php } ?>
                                <?php if ($hide_labels['sn_party_allowed'] != 1) { ?>
                                    <span style="display: block;">
                                        <i class="<?php echo esc_attr($party_allow); ?>"></i>
                                        <?php echo esc_attr(homey_option('sn_party_allowed')); ?>:
                                        <?php echo esc_attr($party_text); ?>
                                    </span>
                                <?php } ?>
                                <?php if ($hide_labels['sn_children_allowed'] != 1) { ?>
                                    <span style="display: block;">
                                        <i class="<?php echo esc_attr($children_allow); ?>"></i>
                                        <?php echo esc_attr(homey_option('sn_children_allowed')); ?>:
                                        <?php echo esc_attr($children_text); ?></span>
                                <?php } ?>
                            </p>
                        </div>
                        <div class="col-sm-8 col-xs-12">
                            <?php if ((!empty($additional_rules) && $hide_labels['sn_add_rules_info'] != 1)) { ?>
                                <strong>Additional Terms & Rules</strong>
                                <p><span style="display: block;"><?php echo '' . ($additional_rules); ?></span></p>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <strong>Cancellation Policy</strong>
                            <p>
                                <?php if (!empty($select_cancellation)) { ?>
                                    <span style="display: block;">
                                        <i class="fa-solid fa-paste"></i>
                                        <?php echo isset($cancellation_options[$select_cancellation]) ? $cancellation_options[$select_cancellation] : ''; ?></span>
                                <?php } ?>

                                <?php if (!empty($cancellation_policy)) {
                                    // Strip HTML tags and then truncate the content
                                    $clean_policy = strip_tags($cancellation_policy);
                                    $truncated_policy = substr($clean_policy, 0, 275);
                                    $policy_length = strlen($clean_policy);
                                ?>
                                    <span id="can-policy-short">
                                        <?php echo esc_html($truncated_policy); ?>
                                        <?php if ($policy_length > 275) { ?>
                                            ... <a href="#" id="show-more-policy">Show more</a>
                                        <?php } ?>
                                    </span>

                                    <?php if ($policy_length > 275) { ?>
                                        <span id="can-policy-full" style="display: none;">
                                            <?php echo esc_html($clean_policy); ?>
                                            <a href="#" id="show-less-policy">Show less</a>
                                        </span>
                                    <?php } ?>
                                <?php } ?>

                            </p>
                        </div>
                    </div>

                </div><!-- block-right -->
            </div><!-- block-body -->
        </div><!-- block-section -->
    </div><!-- block -->
</div>