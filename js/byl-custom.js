(($) => {
  // DOC READY.
  $(() => {
    var strengthIndicatorPage = $("#password-strength-page");
    strengthIndicatorPage
      .text(
        "Password should contain minimum 8 characters, capital letter and special    symbol"
      )
      .css("color", "gray");
    $("#register_pass_page").on("input", function () {
      var password = $(this).val();
      var length = password.length;
      var hasUpperCase = /[A-Z]/.test(password);
      var hasSpecialCharacter = /[!@#$%^&*(),.?":{}|<>]/.test(password);

      // Check password length and presence of uppercase letter and special character
      if (length >= 8 && hasUpperCase && hasSpecialCharacter) {
        strengthIndicatorPage
          .text("Very Strong Password")
          .css("color", "darkgreen");
      } else if (length >= 8 && (hasUpperCase || hasSpecialCharacter)) {
        strengthIndicatorPage.text("Strong Password").css("color", "green");
      } else if (length >= 8) {
        strengthIndicatorPage.text("Moderate Password").css("color", "orange");
      } else if (length > 0) {
        strengthIndicatorPage.text("Weak Password").css("color", "red");
      } else {
        // Password is empty, hide the strength indicator
        strengthIndicatorPage
          .text(
            "Password should contain minimum 8 characters, capital letter and special symbol"
          )
          .css("color", "gray");
      }
    });

    var strengthIndicatorModal = $("#password-strength-modal");
    strengthIndicatorModal
      .text(
        "Password should contain minimum 8 characters, capital letter and special symbol"
      )
      .css("color", "gray");
    $("#register_pass_modal").on("input", function () {
      var password = $(this).val();
      var length = password.length;
      var hasUpperCase = /[A-Z]/.test(password);
      var hasSpecialCharacter = /[!@#$%^&*(),.?":{}|<>]/.test(password);

      // Check password length and presence of uppercase letter and special character
      if (length >= 8 && hasUpperCase && hasSpecialCharacter) {
        strengthIndicatorModal
          .text("Very Strong Password")
          .css("color", "darkgreen");
      } else if (length >= 8 && (hasUpperCase || hasSpecialCharacter)) {
        strengthIndicatorModal.text("Strong Password").css("color", "green");
      } else if (length >= 8) {
        strengthIndicatorModal.text("Moderate Password").css("color", "orange");
      } else if (length > 0) {
        strengthIndicatorModal.text("Weak Password").css("color", "red");
      } else {
        // Password is empty, hide the strength indicator
        strengthIndicatorModal
          .text(
            "Password should contain minimum 8 characters, capital letter and special symbol"
          )
          .css("color", "gray");
      }
    });

    $(".approve-video").click(function (e) {
      e.preventDefault();
      let curnt = jQuery(this);
      let listID = jQuery(this).attr("data-listid");
      let approval = jQuery(this).attr("data-approve");
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";
      jQuery.ajax({
        type: "post",
        url: ajaxurl,
        dataType: "json",
        data: {
          action: "jsl_approve_video",
          listing_id: listID,
          approval: approval,
        },
        beforeSend: function () {
          curnt.append('<i class="fa fa-spinner fa-pulse fa-fw"></i>');
        },
        success: function (data) {
          curnt.children("i").remove();
          curnt.parent().parent().remove();
        },
        complete: function () {},
        error: function (xhr, status, error) {
          var err = eval("(" + xhr.responseText + ")");
          console.log(err.Message);
        },
      });
    });

    //Switch Role

    $("#switch-role-button").on("click", function (e) {
      e.preventDefault();
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";

      $(this).text("Switching...").prop("disabled", true);

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "switch_user_role",
        },
        success: function (response) {
          if (response.success) {
            window.location.reload();
          }
        },
        error: function () {},
        complete: function () {
          $("#switch-role-button").prop("disabled", false);
        },
      });
    });

    $("#contact_host").click(function (e) {
      e.preventDefault();

      var $this = $(this);
      let message = jQuery("#message").val();
      let listing_id = jQuery("#listing_id").val();
      let receiver_id = jQuery("#receiver_id").val();
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";
      var process_loader_spinner = HOMEY_ajax_vars.process_loader_spinner;
      var success_icon = HOMEY_ajax_vars.success_icon;

      jQuery("#message").siblings(".alert").remove();

      jQuery.ajax({
        type: "post",
        url: ajaxurl,
        dataType: "json",
        data: {
          action: "jsl_contact_host",
          receiver_id: receiver_id,
          listing_id: listing_id,
          message: message,
        },
        beforeSend: function () {
          $this
            .find("i")
            .removeClass("fa-paper-plane")
            .addClass(process_loader_spinner);
        },
        success: function (response) {
          jQuery("#message").siblings(".alert").remove();

          if (response.success) {
            jQuery("#message").before(
              "<p class='alert alert-success'>" + response.data + "</p>"
            );
            jQuery("#message").hide();
            setTimeout(() => {
              jQuery("#modal-contact-host").modal("hide");
            }, 1500);
          } else {
            jQuery("#message").before(
              "<p class='alert alert-danger'>" + response.data + "</p>"
            );
            $this
              .find("i")
              .removeClass(process_loader_spinner)
              .addClass("fa-paper-plane");
          }
        },
        complete: function () {
          $this
            .find("i")
            .removeClass(process_loader_spinner)
            .addClass(success_icon);
        },
        error: function (xhr, status, error) {
          jQuery("#message").siblings(".alert").remove();

          jQuery("#message").before(
            "<p class='alert alert-danger'>An error occurred. Please try again later.</p>"
          );
          console.log("Error: " + error);
        },
      });
    });

    // Send admin message site wide
    $("#send_admin_message").click(function (e) {
      e.preventDefault();

      var $this = $(this);
      let message = jQuery("#admin_message").val();
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";
      var process_loader_spinner = HOMEY_ajax_vars.process_loader_spinner;
      var success_icon = HOMEY_ajax_vars.success_icon;

      $this.siblings(".alert").remove();

      jQuery.ajax({
        type: "post",
        url: ajaxurl,
        dataType: "json",
        data: {
          action: "send_admin_message",
          message: message,
        },
        beforeSend: function () {
          $this
            .find("i")
            .removeClass("fa-paper-plane")
            .addClass(process_loader_spinner);
        },
        success: function (response) {
          $this.siblings(".alert").remove();

          if (response.success) {
            $this.after(
              "<p class='alert alert-success'>" + response.data + "</p>"
            );
          } else {
            $this.after(
              "<p class='alert alert-danger'>" + response.data + "</p>"
            );
            $this
              .find("i")
              .removeClass(process_loader_spinner)
              .addClass("fa-paper-plane");
          }
        },
        complete: function () {
          $this
            .find("i")
            .removeClass(process_loader_spinner)
            .addClass(success_icon);
        },
        error: function (xhr, status, error) {
          $this.siblings(".alert").remove();

          $this.after(
            "<p class='alert alert-danger'>An error occurred. Please try again later.</p>"
          );
          console.log("Error: " + error);
        },
      });
    });

    var lastMessageId = 0;
    var pollInterval = null;
    var isPollingActive = false;

    function pollMessages(user_id) {
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";

      if (!isPollingActive) {
        isPollingActive = true;

        pollInterval = setInterval(function () {
          jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            data: {
              action: "fetch_new_messages",
              user_id: user_id,
              last_message_id: lastMessageId,
            },
            success: function (response) {
              if (response.success) {
                jQuery(".all-messages-block").append(response.data);

                // Update the last message ID to the latest one
                lastMessageId = jQuery(
                  "#chat-messages .last-message-id:last"
                ).data("message-id");

                var chatMessages = jQuery(".all-messages-block");
                chatMessages.scrollTop(chatMessages[0].scrollHeight);
              }
            },
          });
        }, 5000);
      }
    }

    function stopPolling() {
      if (pollInterval) {
        clearInterval(pollInterval);
        isPollingActive = false;
      }
    }

    // Event handler for clicking on a user
    $(document).on("click", ".message-user", function (e) {
      e.preventDefault();
      var user_id = jQuery(this).data("user-id");
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";

      jQuery(".message-user").removeClass("selected");
      jQuery(this).addClass("selected");
      jQuery("#chat-messages").html(
        '<div class="message-loader"><div class="loader-spinner"></div></div>'
      );

      jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          action: "fetch_chat_messages",
          user_id: user_id,
        },
        success: function (response) {
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
        },
      });

      stopPolling();
      pollMessages(user_id);
    });

    $("#user-name-link").on("click", function (event) {
      event.stopPropagation();
    });

    // Event handler for sending a message
    $(document).on("click", "#send-homey-message", function (e) {
      e.preventDefault();
      var $this = $(this);
      var message = jQuery("#chat-input").val();
      var recipient_id = jQuery(".message-user.selected").data("user-id");
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";

      $(".send-chat-errors").remove();

      if (!message.trim()) {
        $this.after(
          "<p class='alert alert-danger send-chat-errors'>Please enter a message.</p>"
        );
        return;
      }

      stopPolling();

      jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          action: "send_chat_message",
          message: message,
          recipient_id: recipient_id,
        },
        success: function (response) {
          if (response.success) {
            jQuery(".all-messages-block").append(response.data);
            jQuery("#chat-input").val("");

            lastMessageId = jQuery("#chat-messages .last-message-id:last").data(
              "message-id"
            );

            pollMessages(recipient_id);
            var chatMessages = jQuery(".all-messages-block");
            chatMessages.scrollTop(chatMessages[0].scrollHeight);
          } else {
            $(".send-chat-errors").remove();
            $this.after(
              "<p class='alert alert-danger send-chat-errors'>" +
                response.data +
                "</p>"
            );
          }
        },
      });
    });

    $(document).on("click", "#hide-listing-details", function (e) {
      e.preventDefault();
      jQuery(".messaging-block .hide-listing-col").hide();
      jQuery(".messaging-block .message-details-block").css("width", "100%");
    });

    $(document).on("click", "#hide-booking-details", function (e) {
      e.preventDefault();
      jQuery(".messaging-block .hide-booking-col").hide();
      jQuery(".messaging-block .message-details-block").css("width", "100%");
    });

    $(document).on("input", "#message-search", function () {
      var searchTerm = jQuery(this).val().toLowerCase();

      jQuery(".all-messages-block").each(function () {
        var messagesFound = false;
        jQuery(this)
          .find(".message-user-info")
          .each(function () {
            var messageElement = jQuery(this).find(".last-message-id");
            var messageText = messageElement.text();
            var senderNameElement = jQuery(this).find(".user-name");
            var senderName = senderNameElement.text();
            var messageTimeElement = jQuery(this).find(".message-time");
            var messageTime = messageTimeElement.text();

            // Reset the message and sender name HTML
            messageElement.html(messageText);
            senderNameElement.html(senderName);
            messageTimeElement.html(messageTime);

            // Check for matches
            if (
              messageText.toLowerCase().includes(searchTerm) ||
              senderName.toLowerCase().includes(searchTerm) ||
              messageTime.toLowerCase().includes(searchTerm)
            ) {
              jQuery(this).show();
              messagesFound = true;

              // Highlight matching text in message
              if (searchTerm) {
                var regex = new RegExp("(" + searchTerm + ")", "gi");
                messageElement.html(
                  messageText.replace(
                    regex,
                    '<span class="searched-message-highlight">$1</span>'
                  )
                );
              }

              // Highlight matching text in sender name
              if (searchTerm) {
                var senderRegex = new RegExp("(" + searchTerm + ")", "gi");
                senderNameElement.html(
                  senderName.replace(
                    senderRegex,
                    '<span class="searched-message-highlight">$1</span>'
                  )
                );
              }

              // Highlight matching text in Time
              if (searchTerm) {
                var timeRegex = new RegExp("(" + searchTerm + ")", "gi");
                messageTimeElement.html(
                  messageTime.replace(
                    timeRegex,
                    '<span class="searched-message-highlight">$1</span>'
                  )
                );
              }
            } else {
              jQuery(this).hide();
            }
          });

        if (!messagesFound) {
          jQuery(this).find(".message-date").hide();
        } else {
          jQuery(this).find(".message-date").show();
        }
      });
    });

    //make message favourite

    $(document).on("click", ".favorite-icon", function (e) {
      e.preventDefault();

      var $this = jQuery(this);
      var message_id = $this.data("message-id");
      var is_favorite = $this.hasClass("fas") ? 0 : 1;
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";

      jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          action: "toggle_favorite_message",
          message_id: message_id,
          is_favorite: is_favorite,
        },
        success: function (response) {
          if (response.success) {
            // Toggle the heart icon
            if (is_favorite) {
              $this.removeClass("far").addClass("fas");
            } else {
              $this.removeClass("fas").addClass("far");
            }
          } else {
            alert("Failed to update favorite status.");
          }
        },
        error: function () {
          alert("An error occurred. Please try again.");
        },
      });
    });

    //Report as spam
    $(document).on("click", ".btn-report-spam", function (e) {
      e.preventDefault();
      e.stopPropagation();

      var $this = $(this);
      var user_id = $this.data("user-id");
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";

      jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          action: "report_conversation_as_spam",
          user_id: user_id,
        },
        success: function (response) {
          if (response.success) {
            if ($(".message-user.selected").data("user-id") == user_id) {
              $(".messaging-block").html(
                "<p>This conversation has been reported as spam.</p>"
              );
            }
            $(".message-user[data-user-id='" + user_id + "']").remove();
          }
        },
        error: function () {},
      });
    });

    // Archive a conversation
    $(document).on("click", ".btn-report-archive", function (e) {
      e.preventDefault();
      e.stopPropagation();

      var $this = $(this);
      var user_id = $this.data("user-id");
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";

      jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          action: "archive_conversation",
          user_id: user_id,
        },
        success: function (response) {
          if (response.success) {
            if ($(".message-user.selected").data("user-id") == user_id) {
              $(".messaging-block").html(
                "<p>This conversation has been moved to archive.</p>"
              );
            }
            $(".message-user[data-user-id='" + user_id + "']").remove();
          }
        },
        error: function () {},
      });
    });

    // Block a conversation
    $(document).on("click", ".btn-report-block", function (e) {
      e.preventDefault();
      e.stopPropagation();

      var $this = $(this);
      var user_id = $this.data("user-id");
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";

      jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          action: "block_conversation",
          user_id: user_id,
        },
        success: function (response) {
          if (response.success) {
            if ($(".message-user.selected").data("user-id") == user_id) {
              $(".messaging-block").html(
                "<p>This conversation has been moved to blocked.</p>"
              );
            }
            $(".message-user[data-user-id='" + user_id + "']").remove();
          }
        },
        error: function () {},
      });
    });

    // Move to Inbox
    $(document).on("click", ".btn-move-inbox", function (e) {
      e.preventDefault();
      e.stopPropagation();

      var $this = $(this);
      var user_id = $this.data("user-id");
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";

      jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          action: "move_conversation_to_inbox",
          user_id: user_id,
        },
        success: function (response) {
          if (response.success) {
            if ($(".message-user.selected").data("user-id") == user_id) {
              $("#chat-messages").html(
                "<p>This conversation has been moved to inbox.</p>"
              );
            }
            $(".message-user[data-user-id='" + user_id + "']").remove();
          } else {
            alert("Failed to move conversation to inbox.");
          }
        },
        error: function () {
          alert("An error occurred. Please try again.");
        },
      });
    });

    // Toggle msg buttons
    $(document).on("click", "#toggle-inbox-msg", function (e) {
      e.preventDefault();
      var $this = $(this);

      jQuery.ajax({
        url: HOMEY_ajax_vars.admin_url + "admin-ajax.php",
        type: "POST",
        dataType: "json",
        data: {
          action: "fetch_regular_conversations",
        },
        success: function (response) {
          if (response.success) {
            // Update the conversation list
            $(".message-users-list ul").html(response.data);
            $("#toggle-inbox-msg").hide();
            $("#toggle-spam, #toggle-archive-msg, #toggle-blocked-msg").show();
          }
        },
        error: function () {},
      });
    });

    $(document).on("click", "#toggle-spam", function (e) {
      e.preventDefault();
      var $this = $(this);

      jQuery.ajax({
        url: HOMEY_ajax_vars.admin_url + "admin-ajax.php",
        type: "POST",
        dataType: "json",
        data: {
          action: "fetch_spam_conversations",
        },
        success: function (response) {
          if (response.success) {
            // Update the conversation list
            $(".message-users-list ul").html(response.data);
            $("#toggle-spam").hide();
            $(
              "#toggle-inbox-msg, #toggle-archive-msg, #toggle-blocked-msg"
            ).show();
          }
        },
        error: function () {},
      });
    });

    // Toggle Archive Conversations
    $(document).on("click", "#toggle-archive-msg", function (e) {
      e.preventDefault();
      var $this = $(this);

      jQuery.ajax({
        url: HOMEY_ajax_vars.admin_url + "admin-ajax.php",
        type: "POST",
        dataType: "json",
        data: {
          action: "fetch_archive_conversations",
        },
        success: function (response) {
          if (response.success) {
            $(".message-users-list ul").html(response.data);
            $("#toggle-archive-msg").hide();
            $("#toggle-inbox-msg, #toggle-spam, #toggle-blocked-msg").show();
          }
        },
        error: function () {},
      });
    });

    // Toggle Blocked Conversations
    $(document).on("click", "#toggle-blocked-msg", function (e) {
      e.preventDefault();
      var $this = $(this);

      jQuery.ajax({
        url: HOMEY_ajax_vars.admin_url + "admin-ajax.php",
        type: "POST",
        dataType: "json",
        data: {
          action: "fetch_blocked_conversations",
        },
        success: function (response) {
          if (response.success) {
            $(".message-users-list ul").html(response.data);
            $("#toggle-blocked-msg").hide();
            $("#toggle-inbox-msg, #toggle-spam, #toggle-archive-msg").show();
          }
        },
        error: function () {},
      });
    });

    /* ------------------------------------------------------------------------ */
    /*  Complete Reservation
    /* ------------------------------------------------------------------------ */
    $(".set-reservation-complete").on("click", function (e) {
      e.preventDefault();

      var $this = $(this);
      var reservation_id = $this.data("reservation_id");
      var parentDIV = $this.parents(".user-dashboard-right");
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";
      var process_loader_spinner = HOMEY_ajax_vars.process_loader_spinner;

      $.ajax({
        type: "post",
        url: ajaxurl,
        dataType: "json",
        data: {
          action: "homey_set_reservation_complete",
          reservation_id: reservation_id,
        },
        beforeSend: function () {
          $this.children("i").remove();
          $this.prepend(
            '<i class="fa-left ' + process_loader_spinner + '"></i>'
          );
        },
        success: function (data) {
          parentDIV.find(".alert").remove();
          if (data.success) {
            $this.attr("disabled", true);
            window.location.reload();
          } else {
            parentDIV
              .find(".dashboard-area")
              .prepend(
                '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>' +
                  data.message +
                  "</div>"
              );
          }
        },
        error: function (xhr, status, error) {
          var err = eval("(" + xhr.responseText + ")");
          console.log(err.Message);
        },
        complete: function () {
          $this.children("i").removeClass(process_loader_spinner);
        },
      });
    });

    // Function to generate video preview HTML based on URL
    function getVideoPreview(url) {
      let videoPreviewHtml = "";

      // Check if URL is from YouTube
      const youtubeMatch = url.match(
        /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/
      );
      if (youtubeMatch && youtubeMatch[1]) {
        videoPreviewHtml =
          '<iframe width="560" height="315" src="https://www.youtube.com/embed/' +
          youtubeMatch[1] +
          '" frameborder="0" allowfullscreen></iframe>';
      }

      // Check if URL is from Vimeo
      const vimeoMatch = url.match(
        /(?:https?:\/\/)?(?:www\.)?(?:vimeo\.com\/)([0-9]+)/
      );
      if (vimeoMatch && vimeoMatch[1]) {
        videoPreviewHtml =
          '<iframe src="https://player.vimeo.com/video/' +
          vimeoMatch[1] +
          '" width="560" height="315" frameborder="0" allowfullscreen></iframe>';
      }

      // Check if URL is .mov or .swf
      const fileExtension = url.split(".").pop();
      if (fileExtension === "mov" || fileExtension === "swf") {
        videoPreviewHtml =
          '<video width="560" height="315" controls><source src="' +
          url +
          '" type="video/' +
          fileExtension +
          '"></video>';
      }

      return videoPreviewHtml;
    }

    // Event listener for input field change
    $("#video_url").on("input", function () {
      const videoUrl = $(this).val();
      const videoPreviewHtml = getVideoPreview(videoUrl);

      if (videoPreviewHtml) {
        $("#homey_video_url_preview").html(videoPreviewHtml).fadeIn();
      } else {
        $("#homey_video_url_preview").fadeOut().html("");
      }
    });

    $("#edit_reservation_time").click(function (e) {
      e.preventDefault();
      let res_id = $(this).data("id");
      $("#edit_reservation_calendar").toggle();
    });

    let homey_date_format = HOMEY_ajax_vars.homey_date_format;

    let homey_convert_date = function (date) {
      if (date == "") {
        return "";
      }

      var d_format, return_date;

      d_format = homey_date_format.toUpperCase();

      var changed_date_format = d_format.replace("YY", "YYYY");
      var return_date = moment(date, changed_date_format).format("YYYY-MM-DD");

      return return_date;
    };

    let arrive_filled = false;
    let arrive = $('input[name="arrive"]');
    let start_hour = $('select[name="start_hour"]');
    let end_hour = $('select[name="end_hour"]');
    let notify = $(".homey_notification");

    $(".hourly-calendar").click(function (e) {
      arrive_filled = true;
      if (
        arrive_filled == true &&
        start_hour.val() != "" &&
        end_hour.val() != ""
      ) {
        $("#update_reservation_time").prop("disabled", false);
      } else {
        $("#update_reservation_time").prop("diabled", true);
      }
    });

    start_hour.change(function (e) {
      if (
        arrive_filled == true &&
        e.target.value != "" &&
        end_hour.val() != ""
      ) {
        $("#update_reservation_time").prop("disabled", false);
      } else {
        $("#update_reservation_time").prop("diabled", true);
      }
    });

    end_hour.change(function (e) {
      console.log(arrive_filled);
      console.log(e.target.value);
      console.log(start_hour.val());
      if (
        arrive_filled == true &&
        e.target.value != "" &&
        start_hour.val() != ""
      ) {
        $("#update_reservation_time").prop("disabled", false);
      } else {
        $("#update_reservation_time").prop("diabled", true);
      }
    });

    $("#update_reservation_time").click(function (e) {
      e.preventDefault();
      var listing_id = $("#reservationID").val();
      var check_in_date = $('input[name="arrive"]').val();
      check_in_date = homey_convert_date(check_in_date);
      var start_hour = $('select[name="start_hour"]').val();
      var end_hour = $('select[name="end_hour"]').val();
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";
      // let process_loader_spinner = HOMEY_ajax_vars.process_loader_spinner;

      console.log(check_in_date);
      console.log(start_hour);
      console.log(end_hour);
      $.ajax({
        type: "post",
        url: ajaxurl,
        dataType: "json",
        data: {
          action: "jsl_edit_hourly_reservation",
          check_in_date: check_in_date,
          start_hour: start_hour,
          end_hour: end_hour,
          listing_id: listing_id,
        },
        beforeSend: function () {
          $(this).children("i").remove();
          // $(this).prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (data) {
          //location.reload();
          if (data.success) {
            $(".check_in_date, .check_out_date").val("");
            $(".homey_notification").prepend(
              '<div class="notify text-success text-center btn-success-outlined btn btn-full-width">' +
                data.message +
                "</div>"
            );
          } else {
            $(".homey_notification").prepend(
              '<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">' +
                data.message +
                "</div>"
            );
          }
        },
        error: function (xhr, status, error) {
          var err = eval("(" + xhr.responseText + ")");
        },
        complete: function () {
          // $this.children('i').removeClass(process_loader_spinner);
        },
      }); // end ajax
    });

    $("#confirm_update_reservation").click(function (e) {
      e.preventDefault();
      var listing_id = $("#reservationID").val();
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";
      // let process_loader_spinner = HOMEY_ajax_vars.process_loader_spinner;

      $.ajax({
        type: "post",
        url: ajaxurl,
        dataType: "json",
        data: {
          action: "jsl_confirm_update_reservation",
          listing_id: listing_id,
        },
        beforeSend: function () {
          $(this).children("i").remove();
          // $(this).prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (data) {
          if (data.success) {
            $(".time-change-msg").prepend(
              '<div class="notify text-success text-center btn-success-outlined btn btn-full-width">' +
                data.message +
                "</div>"
            );
          } else {
            $(".time-change-msg").prepend(
              '<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">' +
                data.message +
                "</div>"
            );
          }
        },
        error: function (xhr, status, error) {
          var err = eval("(" + xhr.responseText + ")");
        },
        complete: function () {
          // $this.children('i').removeClass(process_loader_spinner);
        },
      }); // end ajax
    });

    $("#reject_update_reservation").click(function (e) {
      e.preventDefault();
      var listing_id = $("#reservationID").val();
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";
      // let process_loader_spinner = HOMEY_ajax_vars.process_loader_spinner;

      $.ajax({
        type: "post",
        url: ajaxurl,
        dataType: "json",
        data: {
          action: "jsl_reject_update_reservation",
          listing_id: listing_id,
        },
        beforeSend: function () {
          $(this).children("i").remove();
          // $(this).prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (data) {
          if (data.success) {
            $(".time-change-msg").prepend(
              '<div class="notify text-success text-center btn-success-outlined btn btn-full-width">' +
                data.message +
                "</div>"
            );
          } else {
            $(".time-change-msg").prepend(
              '<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">' +
                data.message +
                "</div>"
            );
          }
        },
        error: function (xhr, status, error) {
          var err = eval("(" + xhr.responseText + ")");
        },
        complete: function () {
          // $this.children('i').removeClass(process_loader_spinner);
        },
      }); // end ajax
    });

    /* ------------------------------------------------------------------------ */
    /*  Special Features
     /* ------------------------------------------------------------------------ */

    $("#add_more_special_features").on("click", function (e) {
      e.preventDefault();

      var numVal = $(this).data("increment") + 1;
      $(this).data("increment", numVal);
      $(this).attr({
        "data-increment": numVal,
      });

      var newOption =
        "" +
        '<div class="more_special_features_wrap">' +
        '<div class="row">' +
        '<div class="col-sm-4 col-xs-12">' +
        '<div class="form-group">' +
        '<label for="name">' +
        Homey_Listing.ex_name +
        "</label>" +
        '<input type="text" name="special_feature[' +
        numVal +
        '][name]" class="form-control" placeholder="' +
        Homey_Listing.ex_name_plac +
        '">' +
        "</div>" +
        "</div>" +
        "</div>" +
        '<div class="row">' +
        '<div class="col-sm-12 col-xs-12">' +
        '<button type="button" data-remove="' +
        numVal +
        '" class="remove-special-features btn btn-primary btn-slim">' +
        Homey_Listing.delete_btn_text +
        "</button>" +
        "</div>" +
        "</div>" +
        "</div>";

      $("#more_special_features_main").append(newOption);
      // $('.type-select-picker').selectpicker('refresh');
      removeSpecialFeatures();
    });

    let removeSpecialFeatures = function () {
      $(".remove-special-features").on("click", function (event) {
        event.preventDefault();
        $(this).closest(".more_special_features_wrap").remove();
      });
    };
    removeSpecialFeatures();

    $("#security-cameras").change(function (e) {
      let val = e.target.value;
      if (val == "yes") {
        $("#location-camera-content").show();
      } else {
        $("#location-camera-content").hide();
      }
    });

    var initialValue = $("#have_sleeping_accommodations").val();

    toggleSleepingAccommodations(initialValue);

    $("#have_sleeping_accommodations").change(function () {
      toggleSleepingAccommodations($(this).val());
    });

    function toggleSleepingAccommodations(value) {
      if (value === "yes") {
        $("#sleeping_accommodations_qst").show();
        $("#sleeping_accommodations").hide();
      } else {
        $("#sleeping_accommodations_qst").hide();
        $("#sleeping_accommodations").hide();
      }
    }

    $(document).ready(function () {
      toggleCheckboxes();

      function toggleCheckboxes() {
        var agreedDisclaimer = $("#agreed_disclaimer").prop("checked");
        toggleAgreedDisclaimer(agreedDisclaimer);

        $("#agreed_disclaimer").change(function () {
          toggleAgreedDisclaimer($(this).prop("checked"));
        });

        var taxValue = $("#have_occupancy_tax").prop("checked");
        toggleOccupancyTax(taxValue);

        $("#have_occupancy_tax").change(function () {
          toggleOccupancyTax($(this).prop("checked"));
        });
      }

      function toggleAgreedDisclaimer(isChecked) {
        if (isChecked) {
          $("#sleeping_accommodations").show();
        } else {
          $("#sleeping_accommodations").hide();
        }
      }

      function toggleOccupancyTax(isChecked) {
        if (isChecked) {
          $("#sleeping_accommodations_qst_inner").hide();
          $("#sleeping_accommodations").show();
        } else {
          $("#sleeping_accommodations_qst_inner").show();
          $("#sleeping_accommodations").hide();
        }
      }

      $("#agreed_disclaimer").change();
    });

    var firstValue = $("#have_guided_service").val();

    toggleGuidedService(firstValue);

    $("#have_guided_service").change(function () {
      toggleGuidedService($(this).val());
    });

    function toggleGuidedService(value) {
      if (value === "guide_required" || value === "guide_is_optional") {
        $("#guided_service").show();
      } else {
        $("#guided_service").hide();
      }
    }

    /*--------------------------------------------------------------------------
     *  Delete Coupon
     * -------------------------------------------------------------------------*/
    $(".delete-coupon").on("click", function () {
      var $this = $(this);
      var coupon_id = $this.data("id");
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";

      $.ajax({
        type: "POST",
        dataType: "json",
        url: ajaxurl,
        data: {
          action: "homey_delete_coupon",
          coupon_id: coupon_id,
        },
        beforeSend: function () {
          $this.find("i").removeClass("fa-trash");
          $this.find("i").addClass("fa-spin fa-spinner");
        },
        success: function (data) {
          $this.find("i").removeClass("fa-spin fa-spinner");
          $this.find("i").addClass("fa-trash");

          if (data.success == true) {
            $("#coupon_message")
              .empty()
              .append(
                '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                  data.reason +
                  "</div>"
              );
            $("html,body").animate(
              {
                scrollTop: $(".user-dashboard-right").offset().top,
              },
              "slow"
            );

            $("#coupon-" + coupon_id).remove();
          } else {
            $("#coupon_message")
              .empty()
              .append(
                '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                  data.reason +
                  "</div>"
              );
            $("html,body").animate(
              {
                scrollTop: $(".user-dashboard-right").offset().top,
              },
              "slow"
            );
          }
        },
        error: function (errorThrown) {
          // Handle error
        },
      });
    });

    /*--------------------------------------------------------------------------
     *  Send Coupon Mail
     * -------------------------------------------------------------------------*/
    $(".send-coupon-mail").on("click", function () {
      var $this = $(this);
      var coupon_id = $this.data("id");
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";

      $.ajax({
        type: "POST",
        dataType: "json",
        url: ajaxurl,
        data: {
          action: "homey_send_coupon_mail",
          coupon_id: coupon_id,
        },
        beforeSend: function () {
          $this.find("i").removeClass("fa-envelope");
          $this.find("i").addClass("fa-spin fa-spinner");
        },
        success: function (data) {
          $this.find("i").removeClass("fa-spin fa-spinner");
          $this.find("i").addClass("fa-envelope");

          if (data.success == true) {
            $("#coupon_message")
              .empty()
              .append(
                '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                  data.reason +
                  "</div>"
              );
            $("html,body").animate(
              {
                scrollTop: $(".user-dashboard-right").offset().top,
              },
              "slow"
            );
          } else {
            $("#coupon_message")
              .empty()
              .append(
                '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                  data.reason +
                  "</div>"
              );
            $("html,body").animate(
              {
                scrollTop: $(".user-dashboard-right").offset().top,
              },
              "slow"
            );
          }
        },
        error: function (errorThrown) {
          // Handle error
        },
      });
    });

    /*--------------------------------------------------------------------------
     *  Send Coupon Message
     * -------------------------------------------------------------------------*/
    $(".send-coupon-message").on("click", function () {
      var $this = $(this);
      var coupon_id = $this.data("id");
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";

      $.ajax({
        type: "POST",
        dataType: "json",
        url: ajaxurl,
        data: {
          action: "homey_send_coupon_message",
          coupon_id: coupon_id,
        },
        beforeSend: function () {
          $this.find("i").removeClass("fa-comments-o");
          $this.find("i").addClass("fa-spin fa-spinner");
        },
        success: function (data) {
          $this.find("i").removeClass("fa-spin fa-spinner");
          $this.find("i").addClass("fa-comments-o");

          if (data.success == true) {
            $("#coupon_message")
              .empty()
              .append(
                '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                  data.reason +
                  "</div>"
              );
            $("html,body").animate(
              {
                scrollTop: $(".user-dashboard-right").offset().top,
              },
              "slow"
            );
          } else {
            $("#coupon_message")
              .empty()
              .append(
                '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                  data.reason +
                  "</div>"
              );
            $("html,body").animate(
              {
                scrollTop: $(".user-dashboard-right").offset().top,
              },
              "slow"
            );
          }
        },
        error: function (errorThrown) {
          // Handle error
        },
      });
    });

    /*--------------------------------------------------------------------------
     *  Report Profile
     * -------------------------------------------------------------------------*/
    $(".report-profile").on("click", function (e) {
      e.preventDefault();

      if (!confirm("Are you sure you want to report this profile?")) {
        return;
      }

      var report_profile_id = $(this).data("user-id");
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";

      $.ajax({
        type: "POST",
        dataType: "json",
        url: ajaxurl,
        data: {
          action: "report_profile",
          report_profile_id: report_profile_id,
        },
        success: function (data) {
          if (data.success) {
            $("#report_profile_message").html(
              '<div class="alert alert-success" style="margin-top: 10px;">' +
                data.message +
                "</div>"
            );
          } else {
            $("#report_profile_message").html(
              '<div class="alert alert-danger" style="margin-top: 10px;">' +
                data.message +
                "</div>"
            );
          }
        },
        error: function (errorThrown) {
          console.error(errorThrown);
          $("#report_profile_message").html(
            '<div class="alert alert-danger" style="margin-top: 10px;">An error occurred. Please try again later.</div>'
          );
        },
      });
    });

    /*--------------------------------------------------------------------------
     *  Stripe Connect Account
     * -------------------------------------------------------------------------*/
    $("#create-stripe-account").on("click", function () {
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "create_stripe_connect_account",
        },
        success: function (response) {
          if (response.success) {
            window.location.href = response.data.url;
          } else {
            alert("Error: " + response.data);
          }
        },
        error: function (xhr, status, error) {
          alert("An error occurred: " + error);
        },
      });
    });

    /*--------------------------------------------------------------------------
     *  Complete Connect Account
     * -------------------------------------------------------------------------*/
    $("#complete-stripe-account").on("click", function () {
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "generate_stripe_onboarding_link",
        },
        success: function (response) {
          if (response.success) {
            window.location.href = response.data.url;
          } else {
            alert("Error: " + response.data);
          }
        },
        error: function (xhr, status, error) {
          alert("An error occurred: " + error);
        },
      });
    });

    /*--------------------------------------------------------------------------
     *  Ajax request to check Dates
     * -------------------------------------------------------------------------*/

    function checkAvailability() {
      var check_in_date = $('input[name="arrive"]').val();
      var start_hour = $('select[name="start_hour"]').val();
      var end_hour = $('select[name="end_hour"]').val();
      check_in_date = homey_convert_date(check_in_date);
      var listing_id = $("#listing_id").val();

      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "check_dates_settings",
          check_in_date: check_in_date,
          start_hour: start_hour,
          end_hour: end_hour,
          listing_id: listing_id,
        },
        success: function (response) {
          if (response.amenity === "available") {
            $("#add-on-services").show();
          } else {
            $("#add-on-services").hide();
          }

          if (response.sleeping === "available") {
            $("#sleeping-accomodation-availability").show();
          } else {
            $("#sleeping-accomodation-availability").hide();
          }

          if (response.gservice === "available") {
            $("#guided-service-availability").show();
          } else {
            $("#guided-service-availability").hide();
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", error);
        },
      });
    }

    // First event handler
    $(".hourly-js-desktop ul li").on("click", checkAvailability);
    $("#start_hour").on("change", checkAvailability);
    $("#end_hour").on("change", checkAvailability);
    $("#start_hour_overlay").on("change", checkAvailability);
    $("#end_hour_overlay").on("change", checkAvailability);

    /*--------------------------------------------------------------------------
     *  Notification Settings
     * -------------------------------------------------------------------------*/
    $("#save-notification-settings").on("click", function () {
      var emailChecked = $(
        '#notification-settings-form input[name="email"]'
      ).is(":checked")
        ? 1
        : 0;
      var smsChecked = $('#notification-settings-form input[name="sms"]').is(
        ":checked"
      )
        ? 1
        : 0;
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";
      $("#notofication-loader").show();
      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
          action: "save_notification_settings",
          email: emailChecked,
          sms: smsChecked,
        },
        success: function (response) {
          $("#notofication-loader").hide();
          if (response.success) {
            $("#notification-settings-response").html(
              '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                response.data +
                "</div>"
            );
          } else {
            $("#notification-settings-response").html(
              '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                response.data +
                "</div>"
            );
          }
        },
        error: function () {
          $("#notofication-loader").hide();
          $("#notification-settings-response").html(
            '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>An error occurred. Please try again.</div>'
          );
        },
      });
    });

    /*--------------------------------------------------------------------------
     *  Price Type Divs
     * -------------------------------------------------------------------------*/

    function updateDivsAndPostfix() {
      var selectedValue = $("#amenity_price_type").val();
      var pricePostfix = $("#price-postfix");

      $("#price_per_hour_div").hide();
      $("#price_per_day_div").hide();
      $("#price_per_half_day_div").hide();

      if (selectedValue === "price_per_hour") {
        $("#price_per_hour_div").show();
        pricePostfix.text("Hour");
      } else if (selectedValue === "price_per_day") {
        $("#price_per_day_div").show();
        pricePostfix.text("Day");
      } else if (selectedValue === "price_per_half_day") {
        $("#price_per_half_day_div").show();
        pricePostfix.text("Half Day");
      }
    }

    function updateSpanText() {
      var hourPrice = $("#hour_price").val();
      var pricePlace = $("#price-place");
      pricePlace.text(hourPrice);
    }

    updateDivsAndPostfix();
    updateSpanText();

    $("#amenity_price_type").change(function () {
      updateDivsAndPostfix();
    });

    $("#hour_price").on("input", function () {
      updateSpanText();
    });

    let count = 0;
    $("#accommodationNumberIncrement").on("click", function () {
      count++;
      $("#accommodationNumberDisplay").text(count);
      $("#accommodation_number").val(count);
    });

    $("#accommodationNumberDecrement").on("click", function () {
      if (count > 0) {
        count--;
        $("#accommodationNumberDisplay").text(count);
        $("#accommodation_number").val(count);
      }
    });

    $(".first-half-day #start_hour").attr("name", "start_hour");
    $(".first-half-day #end_hour").attr("name", "end_hour");

    $(".half-day-btn").on("click", function () {
      $(".half-day-btn").removeClass("active");
      $(this).addClass("active");

      $(".half-day-hour").removeClass("active");
      $($(this).data("target")).addClass("active");

      if ($(this).data("target") === ".first-half-day") {
        $(".first-half-day #start_hour").attr("name", "start_hour");
        $(".first-half-day #end_hour").attr("name", "end_hour");
        $(".second-half-day #start_hour").removeAttr("name");
        $(".second-half-day #end_hour").removeAttr("name");
      } else {
        $(".second-half-day #start_hour").attr("name", "start_hour");
        $(".second-half-day #end_hour").attr("name", "end_hour");
        $(".first-half-day #start_hour").removeAttr("name");
        $(".first-half-day #end_hour").removeAttr("name");
      }
    });

    /*--------------------------------------------------------------------------
     *  Verify Password
     * -------------------------------------------------------------------------*/

    $("#homey_verify_pass").on("click", function () {
      var $this = $(this);
      var currentPassword = $("#currentpass").val();
      var security = $("#homey-security-verify-pass").val();
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";
      var process_loader_spinner = HOMEY_ajax_vars.process_loader_spinner;
      var success_icon = HOMEY_ajax_vars.success_icon;

      if (currentPassword == "") {
        $("#profile_message").html(
          '<div class="alert alert-warning alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Please enter your current password.</div>'
        );
        return;
      }

      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
          action: "homey_verify_current_password",
          current_password: currentPassword,
          security: security,
        },
        beforeSend: function () {
          $this.children("i").remove();
          $this.prepend(
            '<i class="fa-left ' + process_loader_spinner + '"></i>'
          );
        },
        success: function (response) {
          if (response.success) {
            $("#profile_message").html(
              '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Password verified successfully. You can now change your password.</div>'
            );
            $("#verify-password-form").hide();
            $("#change-password-form").show();
            $this.children("i").removeClass(process_loader_spinner);
            $this.children("i").addClass(success_icon);
          } else {
            $("#profile_message").html(
              '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                response.data.message +
                "</div>"
            );
            $this.children("i").removeClass(process_loader_spinner);
          }
        },
        error: function () {
          $("#profile_message").html(
            '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>An error occurred while verifying your password. Please try again.</div>'
          );
          $this.children("i").removeClass(process_loader_spinner);
        },
      });
    });

    /*--------------------------------------------------------------------------
     *  Wallet Toggle switch
     * -------------------------------------------------------------------------*/

    $(".tax-status-toggle").on("change", function () {
      var reservation_id = $(this).data("reservation-id");
      var tax_status = $(this).is(":checked") ? "paid" : "unpaid";
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "update_tax_status",
          reservation_id: reservation_id,
          tax_status: tax_status,
        },
        success: function (response) {
          if (response.success) {
            var slider = $(
              '.tax-status-toggle[data-reservation-id="' + reservation_id + '"]'
            ).siblings(".slider");
            var statusText = $(
              '.wallet-tax-toggle[data-reservation-id="' + reservation_id + '"]'
            ).find(".tax-status-text");

            slider.removeClass("paid unpaid").addClass(tax_status);
            statusText.text(
              tax_status.charAt(0).toUpperCase() + tax_status.slice(1)
            );
          }
        },
      });
    });

    /*--------------------------------------------------------------------------
     *  Wallet Filters
     * -------------------------------------------------------------------------*/

    // Initialize date pickers
    $(".datepicker").datepicker({
      dateFormat: "yy-mm-dd",
    });

    // Toggle dropdown on button click
    $(".wallet-filter-btn").on("click", function (e) {
      e.preventDefault();
      $(this).siblings(".wallet-filter-dropdown").toggleClass("show");
    });

    // Close dropdown when clicking outside
    $(document).on("click", function (e) {
      if (!$(e.target).closest(".wallet-filters").length) {
        $(".wallet-filter-dropdown").removeClass("show");
      }
    });

    // Handle checkbox changes
    $(".wallet-filter-checkbox").on("change", function () {
      var selectedFilters = [];
      $(".wallet-filter-checkbox:checked").each(function () {
        selectedFilters.push($(this).val());
      });

      // Call the filter function with selected filters
      filterReservations(1, selectedFilters);
    });

    // Function to filter reservations
    function filterReservations(page = 1, filters = []) {
      var search_term = $('input[name="wallet_reservations_search"]')
        .val()
        .trim();
      var start_date = $("#wallet_reservations_start_date").val();
      var end_date = $("#wallet_reservations_end_date").val();
      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "filter_reservations_by_search",
          search_term: search_term,
          start_date: start_date,
          end_date: end_date,
          paged: page, // Pass the current page number
          filters: filters, // Pass selected filters
        },
        success: function (response) {
          if (response.success) {
            // Replace the table body with the updated reservations
            $(".dashboard-withdraw-table tbody").html(
              response.data.reservations
            );

            // Update the pagination links
            $(".wallet-filters-pagination").html(response.data.pagination);

            // Update the active and disabled classes
            updatePaginationClasses(page);
          }
        },
      });
    }

    // Function to update pagination classes
    function updatePaginationClasses(currentPage) {
      var $pagination = $(".wallet-filters-pagination .pagination");
      var $paginationLinks = $pagination.find("a");

      // Remove active and disabled classes
      $pagination.find("li").removeClass("active disabled");

      // Add active class to the current page
      $paginationLinks.each(function () {
        var page = $(this).attr("data-homeypagi");
        if (page == currentPage) {
          $(this).parent("li").addClass("active");
        }
      });

      // Add disabled class to the first and last links if necessary
      if (currentPage == 1) {
        $pagination.find("li:first").addClass("disabled");
      }
      if (currentPage == $paginationLinks.length) {
        $pagination.find("li:last").addClass("disabled");
      }
    }

    // Handle search input
    $('input[name="wallet_reservations_search"]').on("input", function () {
      filterReservations(1);
    });

    // Handle date picker changes
    $(".datepicker").on("change", function () {
      filterReservations(1);
    });

    // Handle initial pagination clicks
    $(document).on(
      "click",
      ".wallet-filters-pagination .pagination a",
      function (e) {
        e.preventDefault();
        var page = $(this).attr("data-homeypagi");
        filterReservations(page);
      }
    );

    /*--------------------------------------------------------------------------
     *  Earning Calculator
     * -------------------------------------------------------------------------*/

    $("#earning-calculator-form").on("submit", function (e) {
      e.preventDefault();

      let ajaxurl = HOMEY_ajax_vars.admin_url + "admin-ajax.php";
      var formData = $(this).serialize();

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: formData + "&action=calculate_earnings",
        beforeSend: function () {
          $("#earning-calculator-result").html(
            '<div class="alert alert-info">Calculating...</div>'
          );
        },
        success: function (response) {
          $("#earning-calculator-result").html(response.data);
        },
        error: function () {
          $("#earning-calculator-result").html(
            '<div class="alert alert-danger"><?php esc_html_e("An error occurred. Please try again.", "homey"); ?></div>'
          );
        },
      });
    });

    /*--------------------------------------------------------------------------
     *  Some usefull small js codes
     * -------------------------------------------------------------------------*/

    $("#show-more-options").on("click", function (e) {
      e.preventDefault();
      $(".hidden-extra-price").slideDown();
      $(this).hide();
    });

    // Show the full bio when "Show more" is clicked
    $("#show-more-bio").on("click", function (e) {
      e.preventDefault();
      $("#guide-bio-short").hide();
      $("#guide-bio-full").fadeIn();
    });

    // Show the shortened bio when "Show less" is clicked
    $("#show-less-bio").on("click", function (e) {
      e.preventDefault();
      $("#guide-bio-full").hide();
      $("#guide-bio-short").fadeIn();
    });

    // Show the full policy when "Show more" is clicked
    $("#show-more-policy").on("click", function (e) {
      e.preventDefault();
      $("#can-policy-short").hide();
      $("#can-policy-full").fadeIn();
    });

    // Show the shortened policy when "Show less" is clicked
    $("#show-less-policy").on("click", function (e) {
      e.preventDefault();
      $("#can-policy-full").hide();
      $("#can-policy-short").fadeIn();
    });

    $(".review-read-more").on("click", function (e) {
      e.preventDefault();
      $(this).siblings(".review-short-content").hide();
      $(this).siblings(".review-full-content").slideDown();
      $(this).hide();
      $(this).siblings(".review-read-less").show();
    });

    $(".review-read-less").on("click", function (e) {
      e.preventDefault();
      $(this).siblings(".review-full-content").slideUp();
      $(this).siblings(".review-short-content").show();
      $(this).hide();
      $(this).siblings(".review-read-more").show();
    });

    // $('#submit-upload-button').on('click', function (e) {
    //   e.preventDefault();

    //   var formData = new FormData($('#image-upload-form')[0]);
    //   var postId = HOMEY_ajax_vars.post_id;

    //   formData.append('post_id', postId);
    //   formData.append('action', 'handle_special_details_image_upload');
    //   jQuery.ajax({
    //     url: HOMEY_ajax_vars.ajax_url,
    //     type: 'POST',
    //     data: formData,
    //     processData: false,
    //     contentType: false,
    //     success: function (response) {
    //       console.log(response)

    //     },
    //     error: function (jqXHR, textStatus, errorThrown) {
    //       console.error('Image upload failed:', errorThrown);
    //       // Handle error message or display an error notification
    //     }
    //   });
    // });
  });
})(jQuery);

var $ = jQuery;
jQuery(".bag-modal").attr("data-target", "#modal-register");
jQuery(".bag-modal").attr("data-toggle", "modal");
var is_addnew = 0;
jQuery(document).on("click", ".bag-modal", function () {
  is_addnew = 1;
});
$("#modal-register").on("shown.bs.modal", function (event) {
  if (is_addnew == 1) {
    const selectElement = document.querySelector('select[name="role"]');
    const optionToSelect = selectElement.querySelector(
      'option[value="homey_renter"]'
    );
    selectElement.value = optionToSelect.value;
    const changeEvent = new Event("change");
    selectElement.dispatchEvent(changeEvent);
  } else {
    const selectElement = document.querySelector('select[name="role"]');
    const optionToSelect = selectElement.querySelector('option[value=""]');
    selectElement.value = optionToSelect.value;
    const changeEvent = new Event("change");
    selectElement.dispatchEvent(changeEvent);
  }
  is_addnew = 0;
});
