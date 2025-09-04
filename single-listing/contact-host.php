<?php
global $post, $homey_local, $listing_author;
$host_email = get_the_author_meta('email');
$enable_forms_gdpr = homey_option('enable_forms_gdpr');
$forms_gdpr_text = homey_option('forms_gdpr_text');
$form_type = homey_option('form_type');
$single_listing_host_contact = homey_option('single_listing_host_contact');

$listing_id = $post->ID;

$hostID = get_the_author_meta('ID');
$username = get_the_author_meta('user_login', $hostID);
$display_name_public = get_the_author_meta('display_name_public', $hostID);
$host_name = empty($display_name_public) ? $username : $display_name_public;

$user_id = get_current_user_id();
$guest_user_verify_status = is_guest_verified($user_id);

$average_response_time = calculate_average_response_time($hostID);
$response_time_text = format_response_time($average_response_time);
?>

<div class="modal fade custom-modal-contact-host" id="modal-contact-host" tabindex="-1" role="dialog">
  <div class="modal-dialog clearfix" role="document">

    <div class="modal-body">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
              aria-hidden="true">&times;</span></button>
          <div style="float: right;width: 50px;"><?php echo '' . $listing_author['photo']; ?></div>
          <h4 class="modal-title">Contact <?php echo esc_html($host_name); ?></h4>
          <p class="response-time"><?php echo esc_html($response_time_text) ?></p>
        </div>
        <div class="modal-body host-contact-wrap">
          <div class="modal-contact-host-form">

            <?php if (is_user_logged_in() == true && $guest_user_verify_status && homey_is_renter()) { ?>
              <input id="target_email" type="hidden" name="target_email" value="<?php echo antispambot($host_email); ?>">
              <input id="listing_id" type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>" />
              <input id="receiver_id" type="hidden" name="receiver_id" value="<?php echo esc_attr($hostID); ?>" />

              <div class="form-group">
                <textarea id="message" name="message" class="form-control"
                  placeholder="<?php echo esc_attr($homey_local['con_message']); ?>" rows="5"></textarea>
              </div>

              <div class="custom-actions">
                <button id="contact_host" type="submit" class="btn-full-width btn-action" data-toggle="tooltip"
                  data-placement="top" data-original-title="Send"><i class="fa fa-paper-plane"
                    style="font-size:18px;"></i></button>
              </div>
            <?php } else { ?>
              <p style="color: #c31b1b;">You must be a registered guest with verified payment information to contact a Host. Please switch to Adventurer on your dashboard.</p>
            <?php } ?>
          </div>
          <p style="padding:15px;margin-top:10px !important;"><img draggable="false" role="img" class="emoji" alt="🚫"
              src="https://s.w.org/images/core/emoji/14.0.0/svg/1f6ab.svg"> Note: Trust and safety is always our main
            concern. Please be aware that accounts and messages are monitored and flagged because
            we DO NOT allow the following exchange:<br>Email addresses, Phone numbers, Web addresses, Social media
            links, Third party payments, such as:
            Zelle, Wire, Cashapp, Venmo ...etc. Any and all methods of redirecting users off the Backyard Lease
            Platform.<br>
            Let’s adventure the right way and build a trustworthy community together!<br>

            Sincerely,<br>
            The Backyard Lease Trust and Safety Team
          </p>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div>
  </div>
</div><!-- /.modal -->