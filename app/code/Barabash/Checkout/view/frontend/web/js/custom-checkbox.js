define([
    'jquery'
], function ($) {

    var $comment = $('textarea[name="custom_attributes[customer_comment]"]');
    var $checkboxLabel = $(document.body).find('.custom-comment-checkbox').closest('.field').find('label.label > span');

    if ($comment.val().length === 0) {
        $(document.body).find('.custom-comment-checkbox').prop('checked', false);
        $checkboxLabel.text('Enable');
        $comment.closest('div.field').hide();
    } else {
        $checkboxLabel.text('Disable');
    }
    $comment.closest('.field').addClass('_required');

    $(document.body).on('click', '.custom-comment-checkbox', function() {
        if ($(this).is(':checked')) {
            $comment.closest('div.field').show();
            $comment.removeClass('hidden').addClass('visible').show();
            $checkboxLabel.text('Disable');
            $comment.closest('.field').addClass('_required');
        } else {
            $comment.closest('div.field').hide();
            $comment.removeClass('visible').addClass('hidden').hide();
            $checkboxLabel.text('Enable');
            $comment.val('');
        }
    });

    $comment.on('keyup', function() {
        $comment.css('border', '');
        $comment.closest('div').find('.field-error').remove();
    });

    $('form').on('submit', function(e) {
        if ($comment.hasClass('visible') && $comment.val().length === 0) {
            commentNeedToBeFilled($comment);
            e.preventDefault();
        }
    });

    $('button[type=submit]').on('click', function(e) {
        if ($comment.hasClass('visible') && $comment.val().length === 0) {
            commentNeedToBeFilled($comment);
            e.preventDefault();
        }
    });

    function commentNeedToBeFilled($comment) {
        $comment.css('border', '1px solid red');
        $comment.closest('div').append($('<span />').addClass('field-error').text('This is a required field.'));
    }
});
