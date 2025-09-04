<?php
global $post, $listing_founds, $homey_local;
$sortby = $what_to_show = get_post_meta( $post->ID, 'homey_listings_sort', true );
if( isset( $_GET['sortby'] ) ) {
    $sortby = $_GET['sortby'];
}else{
    $sortby = 'x_price';
}

$rental_text = $homey_local['rental_label'];
if($listing_founds > 1) {
    $rental_text = $homey_local['rentals_label'];
}
?>
<div class="sort-wrap clearfix">
    <div class="pull-left">

    </div>
    <div class="pull-right">
    </div>
</div><!-- sort-wrap clearfix -->