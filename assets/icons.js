jQuery(document).ready(function($) {
    $('.jetda-icon-item').on('click', function() {
        var text = $(this).data('clipboard-text');
        if (text) {
            navigator.clipboard.writeText(text).then(function() {
                showToast('已复制: ' + text);
            }).catch(function() {
                var textarea = $('<textarea>').val(text).appendTo('body');
                textarea.select();
                document.execCommand('copy');
                textarea.remove();
                showToast('已复制: ' + text);
            });
        }
    });
    function showToast(msg) {
        var $toast = $('<div class="copy-toast">' + msg + '</div>');
        $('body').append($toast);
        setTimeout(function() {
            $toast.fadeOut(300, function() { $(this).remove(); });
        }, 1500);
    }
});