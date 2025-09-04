<?php
global $post;
$listing_id = $post->ID;
$special_features = get_field('field_6479250ae9990', $listing_id);

if( !empty($special_features) ) { ?>
<div class="block-body">
    <div class="block-left">
        <h3 class="title"><?php echo esc_html__('Special Features', 'homey'); ?></h3>
    </div><!-- block-left -->
    <div class="block-right">
        <ul class="detail-list detail-list-2-cols">         
        	<?php foreach ($special_features as $key => $feature) { ?>
	            <li>
	                <i class="fa fa-angle-right" aria-hidden="true"></i> 
	                <strong><?php echo esc_html($feature['feature']); ?></strong>
	            </li>
            <?php } ?>          
        </ul>
    </div><!-- block-right -->
</div><!-- block-body -->
<?php } ?>