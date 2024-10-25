(function ($) {
  $(function () {
    $(document).on('heartbeat-send', function (event, data) {
      data.conversation_nonce = mje_heartbeat.conversation_nonce;
      data.notification_nonce = mje_heartbeat.notification_nonce;
    });

    $(document).on('heartbeat-tick', function (event, data) {
      //fix bug notification not real
      if(data.unread_notification && data.unread_notification.notification_count > 0)
      {
        var noti_parent = $('#et-header').find('.notification-icon');
        noti_parent.find('.alert-sign').remove();
        noti_parent.find('.link-message').prepend('<span class="alert-sign">'+ data.unread_notification.notification_count+'</span>');
      }
      //end fix

      if (!!data.unread_conversations 
        && typeof data.unread_conversations.count !== 'undefined'
        && typeof data.unread_conversations.dropdown_html !== 'undefined'
      ) {
        var parent = $('#et-header').find('.message-icon');
        parent.find('.alert-sign').remove();
        parent.find('.link-message').prepend('<span class="alert-sign">'+ data.unread_conversations.count +'</span>');
        parent.find('.list-message-box-header .unread-message-count').text(data.unread_conversations.count);
        parent.find('.list-message-box-body').html(data.unread_conversations.dropdown_html);
      }
    });
  });
})(jQuery);