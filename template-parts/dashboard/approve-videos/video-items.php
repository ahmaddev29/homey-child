<?php
$video_url = homey_get_listing_data('video_url');
?>

<tr>
  <td><a href="<?php echo get_permalink($post->ID); ?>"><strong><?php echo get_the_title($post->ID); ?></strong></a></td>
  <td><a href="<?php echo esc_url( $video_url ); ?>" target="_BLANK"><?php echo esc_html( $video_url ); ?></a></td>
  <td><button class="approve-video btn btn-primary" data-listID="<?php echo $post->ID; ?>" data-approve="1">Approve</button></td>
  <td><button class="approve-video btn btn-danger" data-listID="<?php echo $post->ID; ?>" data-approve="0">Decline</button></td>
</tr>
