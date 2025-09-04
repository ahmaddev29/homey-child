<?php

/**
 * Template Name: Dashboard Chat
 */
if (!is_user_logged_in()) {
    wp_redirect(home_url('/'));
    exit;
}

get_header();

$current_user_id = get_current_user_id();
$current_user_role = homey_get_user_role($current_user_id);
global $wpdb;
$open_chat_with = isset($_GET['chat_with']) ? intval($_GET['chat_with']) : 0;
?>

<section id="body-area">

    <div class="dashboard-page-title">
        <h1><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
    </div>

    <?php get_template_part('template-parts/dashboard/side-menu'); ?>
    <div class="user-dashboard-right dashboard-without-sidebar">
        <div class="dashboard-content-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="dashboard-area messages-page">
                            <?php if (current_user_can('administrator')) : ?>
                                <div class="block">
                                    <div class="block-title">
                                        <div class="block-left">
                                            <h2 class="title">Send Message</h2>
                                        </div>
                                    </div>
                                    <div class="block-body" style="padding: 40px !important;">
                                        <div class="form-group">
                                            <textarea id="admin_message" name="admin-message" class="form-control"
                                                placeholder="Message" rows="5"></textarea>
                                        </div>
                                        <div class="custom-actions msg-alerts">
                                            <button id="send_admin_message" type="submit" class="btn-full-width btn-action" data-toggle="tooltip"
                                                data-placement="top" data-original-title="Send"><i class="fa fa-paper-plane"
                                                    style="font-size:18px;"></i></button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="block">
                                <div class="block-body">
                                    <p class="disclaimer-note">Remember, to ensure coverage and the safety of you and
                                        your
                                        guest(s) always
                                        communicate through this platform and messaging system. You may not provide or
                                        request any phone number or email address prior to accepting a Guest(s) booking.
                                        Our
                                        messaging systems are monitored to ensure compliance with our policies. By
                                        creating
                                        an account and continuing with our messaging system, you have agreed to never
                                        redirect, avoid fees, or collect payment(s) of any sort directly from the users
                                        on
                                        the Backyard Lease platform.</p>
                                    <?php if ($current_user_role == 'homey_host') { ?>
                                        <p class="disclaimer-note">“All Host Messages” (the current host messaging system is designated for the host to view and receive messages as the host. To view messages that were received as a guest please switch over to your adventurer/guest account.)</p>

                                    <?php } else { ?>
                                        <p class="disclaimer-note">“All Guest Messages” (the current guest messaging system is designated for the guest to view and receive messages as the guest. To view messages that were received as a host please switch over to your host account.)</p>
                                    <?php } ?>
                                    <div class="message-dashboard">
                                        <div class="message-users-list">
                                            <div class="block-title">
                                                <div class="msg-user-header">
                                                    <h2 class="title">Messages</h2>
                                                    <div class="msg-action-btns">
                                                        <button id="toggle-inbox-msg" class="btn btn-spam" style="display:none;">Inbox</button>
                                                        <button id="toggle-spam" class="btn btn-spam">Spam</button>
                                                        <button id="toggle-archive-msg" class="btn btn-spam">Archive</button>
                                                        <button id="toggle-blocked-msg" class="btn btn-spam">Blocked</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <ul>
                                                <?php
                                                // Fetch conversations
                                                $users = $wpdb->get_results($wpdb->prepare(
                                                    "
        SELECT DISTINCT sender_id, receiver_id 
        FROM {$wpdb->prefix}homey_messages 
        WHERE (
            (sender_id = %d AND sender_role = %s) 
            OR (receiver_id = %d AND receiver_role = %s)
        )
        AND sender_id NOT IN (
            SELECT user_id 
            FROM {$wpdb->prefix}homey_message_spam 
            WHERE reported_by = %d
        )
        AND sender_id NOT IN (
            SELECT user_id 
            FROM {$wpdb->prefix}homey_message_archive 
            WHERE archived_by = %d
        )
        AND sender_id NOT IN (
            SELECT user_id 
            FROM {$wpdb->prefix}homey_message_blocked 
            WHERE blocked_by = %d
        )
        AND receiver_id NOT IN (
            SELECT user_id 
            FROM {$wpdb->prefix}homey_message_spam 
            WHERE reported_by = %d
        )
        AND receiver_id NOT IN (
            SELECT user_id 
            FROM {$wpdb->prefix}homey_message_archive 
            WHERE archived_by = %d
        )
        AND receiver_id NOT IN (
            SELECT user_id 
            FROM {$wpdb->prefix}homey_message_blocked 
            WHERE blocked_by = %d
        )",
                                                    $current_user_id,
                                                    $current_user_role,
                                                    $current_user_id,
                                                    $current_user_role,
                                                    $current_user_id,
                                                    $current_user_id,
                                                    $current_user_id,
                                                    $current_user_id,
                                                    $current_user_id,
                                                    $current_user_id
                                                ));

                                                if (!$users) {
                                                    echo "<p>No messages available.</p>";
                                                } else {
                                                    $users_list = [];
                                                    foreach ($users as $user) {
                                                        if ($user->sender_id != $current_user_id) {
                                                            $users_list[$user->sender_id] = get_userdata($user->sender_id);
                                                        }
                                                        if ($user->receiver_id != $current_user_id) {
                                                            $users_list[$user->receiver_id] = get_userdata($user->receiver_id);
                                                        }
                                                    }

                                                    foreach ($users_list as $user_id => $user_data):
                                                        $username = get_the_author_meta('user_login', $user_id);

                                                        $user_role = homey_get_user_role($user_id);

                                                        if (user_can($user_id, 'administrator')) {
                                                            $user_name = 'Backyard Lease Platform';
                                                            $profile_link = '#';
                                                            $user_image = '<img src="' . esc_url(homey_option('custom_logo', false, 'url')) . '" class="img-circle" alt="Backyard Lease Platform" width="50" height="50">';
                                                        } else {
                                                            $display_name_public = '';
                                                            if ($user_role == 'homey_renter') {
                                                                $display_name_public = get_the_author_meta('display_name_public_guest', $user_id);
                                                            } else {
                                                                $display_name_public = get_the_author_meta('display_name_public', $user_id);
                                                            }
                                                            $user_name = empty($display_name_public) ? $username : $display_name_public;
                                                            $homey_author = homey_get_author_by_id('50', '50', 'img-circle', $user_id);
                                                            $user_image = $homey_author['photo'];
                                                            $profile_link = get_author_posts_url($user_id);
                                                        }
                                                        $city = '';
                                                        if ($user_role == 'homey_renter') {
                                                            $city = get_the_author_meta($homey_prefix . 'city_guest', $user_id);
                                                        } else {
                                                            $city = get_the_author_meta($homey_prefix . 'city', $user_id);
                                                        }
                                                        if (!empty($city)) {
                                                            $city_name = ' - ' . esc_html($city);
                                                        } else {
                                                            $city_name = '';
                                                        }

                                                        // Fetch the last message between the current user and this user
                                                        $last_message = $wpdb->get_row($wpdb->prepare(
                                                            "
                                                            SELECT message, created_at 
                                                            FROM {$wpdb->prefix}homey_messages 
                                                            WHERE (
                                                                (sender_id = %d AND receiver_id = %d AND sender_role = %s) 
                                                                OR (sender_id = %d AND receiver_id = %d AND receiver_role = %s)
                                                            )
                                                            ORDER BY created_at DESC
                                                            LIMIT 1",
                                                            $current_user_id,
                                                            $user_id,
                                                            $current_user_role,
                                                            $user_id,
                                                            $current_user_id,
                                                            $current_user_role
                                                        ));

                                                        $last_message_text = '';
                                                        $last_message_date = '';
                                                        if ($last_message) {
                                                            $last_message_text = $last_message->message;
                                                            $last_message_date = date_i18n('F j', strtotime($last_message->created_at));
                                                        } else {
                                                            $last_message_text = 'No messages yet.';
                                                            $last_message_date = '';
                                                        }

                                                        $wordLimit = 12;
                                                        $words = explode(' ', $last_message_text);

                                                        if (count($words) > $wordLimit) {
                                                            $words = array_slice($words, 0, $wordLimit);
                                                            $last_message_text = implode(' ', $words) . '...';
                                                        }
                                                ?>
                                                        <li class="message-user"
                                                            data-user-id="<?php echo esc_attr($user_id); ?>">
                                                            <div class="message-user-info">
                                                                <div class="message-user-img"><?php echo '' . $user_image; ?>
                                                                </div>
                                                                <div class="message-user-content">
                                                                    <span class="user-name"><a
                                                                            href="<?php echo ($profile_link); ?>"
                                                                            class="user-name-link"
                                                                            id="user-name-link"><?php echo esc_html($user_name); ?></a></span>
                                                                    <span class="user-name-city">
                                                                        <?php echo esc_html($city_name); ?>
                                                                    </span>
                                                                    <span class="last-message">
                                                                        <?php echo $last_message_text; ?>
                                                                    </span>
                                                                    <?php if ($last_message_date): ?>
                                                                        <span class="last-message-date">Last message sent on
                                                                            <?php echo esc_html($last_message_date); ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="report-msg-buttons">
                                                                    <button class="btn btn-report-spam msg-spam-button" data-user-id="<?php echo esc_attr($user_id); ?>">Report as Spam</button>
                                                                    <button class="btn btn-report-archive msg-spam-button" data-user-id="<?php echo esc_attr($user_id); ?>">Archive Messages</button>
                                                                    <button class="btn btn-report-block msg-spam-button" data-user-id="<?php echo esc_attr($user_id); ?>">Block Messages</button>
                                                                </div>
                                                            </div>
                                                        </li>
                                                <?php endforeach;
                                                }
                                                ?>
                                            </ul>
                                        </div>

                                        <div class="message-box">
                                            <div id="chat-messages" class="chat-messages"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    jQuery(document).ready(function($) {
        // Check if we have a user ID to open chat with
        var openChatWith = <?php echo $open_chat_with; ?>;

        if (openChatWith > 0) {
            // Find the user in the list and trigger click
            var $userElement = $('.message-user[data-user-id="' + openChatWith + '"]');
            if ($userElement.length) {
                // Scroll to the user in the list
                $('.message-users-list').animate({
                    scrollTop: $userElement.offset().top - $('.message-users-list').offset().top + $('.message-users-list').scrollTop()
                }, 500);

                // Highlight the user
                $('.message-user').removeClass('selected');
                $userElement.addClass('selected');

                // Load the messages (you'll need to call whatever function you use to load messages)
                loadMessages(openChatWith);
            }
        }

        // Your existing loadMessages function
        function loadMessages(userId) {
            jQuery("#chat-messages").html('<div class="message-loader"><div class="loader-spinner"></div></div>');

            // Your existing AJAX call to load messages
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'fetch_chat_messages',
                    user_id: userId,
                },
                success: function(response) {
                    if (response.success) {
                        jQuery("#chat-messages").html(response.data);
                        var chatMessages = jQuery(".all-messages-block");
                        chatMessages.scrollTop(chatMessages[0].scrollHeight);
                        lastMessageId = jQuery("#chat-messages .last-message-id:last").data(
                            "message-id"
                        );
                    } else {
                        jQuery("#chat-messages").html(
                            "<p>No messages found with this user.</p>"
                        );
                    }
                }
            });
        }
    });
</script>

<?php get_footer(); ?>