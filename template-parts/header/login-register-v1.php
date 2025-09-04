<?php
global $homey_local;
$nav_login = esc_html__(homey_option('nav_login'), 'homey');
$nav_register = esc_html__(homey_option('nav_register'), 'homey');
$become_host_btn = esc_html__(homey_option('become_host_btn'), 'homey');
$become_host_link = homey_option('become_host_link');
$become_host_label = esc_html__(homey_option('become_host_label'), 'homey');
$home_url = home_url();
$support_url = $home_url . '/support/';

$separater = '';

?>
<div class="account-login">

	<a href="#" class="btn btn-add-new-listing bag-modal" data-toggle="modal" data-target="#modal-register">Become a Guest</a>
	<?php if ($become_host_btn) { ?>
		<a href="<?php echo get_permalink($become_host_link); ?>"
			class="btn btn-add-new-listing"><?php echo esc_attr($become_host_label); ?></a>
	<?php } ?>

	<div class="svg-container">
		<svg id="Group_98" data-name="Group 98" xmlns="http://www.w3.org/2000/svg"
			xmlns:xlink="http://www.w3.org/1999/xlink" width="40" height="40" viewBox="0 0 40 40">
			<defs>
				<clipPath id="clip-path">
					<rect id="Rectangle_34" data-name="Rectangle 34" width="40" height="40" />
				</clipPath>
			</defs>
			<g id="Group_97" data-name="Group 97" clip-path="url(#clip-path)">
				<path id="Path_113" data-name="Path 113"
					d="M20,0A20,20,0,1,0,40,20,20.023,20.023,0,0,0,20,0m9.233,33.453C29,27.579,24.949,22.9,19.981,22.9s-9.01,4.664-9.251,10.526a16.324,16.324,0,1,1,18.5.026" />
				<path id="Path_114" data-name="Path 114"
					d="M43.811,24.947a6.131,6.131,0,1,0,6.131,6.131,6.131,6.131,0,0,0-6.131-6.131"
					transform="translate(-23.831 -15.778)" />
			</g>
		</svg>
	</div>

	<div class="hover-box">
		<?php if ($nav_login || $nav_register) { ?>

			<?php if ($nav_login) { ?>
				<a href="#" data-toggle="modal" data-target="#modal-login">Log In</a>
			<?php } ?>

			<?php if ($nav_register) { ?>
				<a href="#" data-toggle="modal" data-target="#modal-register">Sign Up</a>
			<?php } ?>

		<?php } ?>
		<hr>
		<a href="<?php echo esc_url($support_url); ?>">Help</a>
	</div>
</div>