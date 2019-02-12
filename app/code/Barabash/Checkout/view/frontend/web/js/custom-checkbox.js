define([
    'jquery'
], function ($) {

    var $comment        = $('textarea[name="custom_attributes[customer_comment]"]');
    var $checkboxLabel  = $(document.body).find('.custom-comment-checkbox').closest('div.field').find('label.label > span');
    var currentUrl      = location.href;
    const LABEL_ENABLE  = 'Enable';
    const LABEL_DISABLE = 'Disable';

    if ($comment.val().length === 0) {
        $(document.body).find('.custom-comment-checkbox').prop('checked', false);
        $checkboxLabel.text(LABEL_ENABLE);
        $comment.closest('div.field').hide();
    } else {
        $checkboxLabel.text(LABEL_DISABLE);
    }
    $comment.closest('div.field').addClass('_required');

    $(document.body).on('click', '.custom-comment-checkbox', function() {
        if ($(this).is(':checked')) {
            $comment.closest('div.field').show();
            $comment.removeClass('hidden').addClass('visible').show();
            $checkboxLabel.text(LABEL_DISABLE);
            $comment.closest('div.field').addClass('_required');
        } else {
            $comment.closest('div.field').hide();
            $comment.removeClass('visible').addClass('hidden').hide();
            $checkboxLabel.text(LABEL_ENABLE);
            $comment.val('');
        }
    });

    $comment.on('keyup', function() {
        $comment.css('border', '');
        $comment.closest('div').find('.field-error').remove();
    });

    $('form').on('submit', function(e) {
        if ($(document.body).find('.field-error').length > 0) {
            e.preventDefault();
            location.href = currentUrl;
        }
    });

    $('button[type=submit]').on('click', function(e) {
        if ($comment.hasClass('visible') && $comment.val().length === 0) {
            $comment.css('border', '1px solid red');
            if ($comment.closest('div').find('.field-error').length > 0) {
                return false;
            }
            $comment.closest('div').append($('<span />').addClass('field-error').text('This is a required field.'));
        }
    });
});
