jQuery(document).ready(function($) {
    // 查看详情（ThickBox）
    $(document).on('click', '.view-detail', function() {
        var $row = $(this).closest('tr');
        var file = $row.data('file');
        var lineIdx = $row.data('line-idx');
        $.post(jetdaDebug.ajaxurl, {
            action: 'jetda_get_error_detail',
            nonce: jetdaDebug.nonce,
            file: file,
            line_idx: lineIdx
        }, function(res) {
            if (res.success) {
                $('#jetda-temp-detail').remove();
                $('<div id="jetda-temp-detail" style="display:none;">' + res.data.html + '</div>').appendTo('body');
                tb_show('错误详情', '#TB_inline?inlineId=jetda-temp-detail&height=500&width=800');
                $('#jetda-close-tb').on('click', function() { tb_remove(); });
            } else {
                alert('获取详情失败');
            }
        }, 'json');
    });

    // 删除单条错误
    $(document).on('click', '.delete-error', function() {
        if (!confirm(jetdaDebug.deleteConfirm)) return;
        var $row = $(this).closest('tr');
        var file = $row.data('file');
        var lineIdx = $row.data('line-idx');
        $.post(jetdaDebug.ajaxurl, {
            action: 'jetda_delete_error',
            nonce: jetdaDebug.nonce,
            file: file,
            line_idx: lineIdx
        }, function(res) {
            if (res.success) {
                $row.fadeOut(300, function() {
                    $(this).remove();
                    if ($('#jetda-error-table tbody tr').length === 0) {
                        $('#jetda-error-table tbody').html('<tr><td colspan="5">暂无任何错误记录。</td></tr>');
                    }
                });
                setTimeout(function() { tb_remove(); }, 800);
            } else {
                alert('删除失败：' + (res.data?.message || '未知错误'));
            }
        }, 'json');
    });

    // 清空所有日志
    $('#jetda-clear-all-logs').on('click', function() {
        if (confirm('⚠️ 确定要清空所有错误日志吗？此操作不可恢复！')) {
            $.post(jetdaDebug.ajaxurl, {
                action: 'jetda_clear_all_logs',
                nonce: jetdaDebug.nonce
            }, function(res) {
                if (res.success) {
                    alert(res.data.message);
                    location.reload();
                } else {
                    alert('清空失败：' + (res.data?.message || '未知错误'));
                }
            }, 'json');
        }
    });

    // 复制配置代码
    $(document).on('click', '#jetda-copy-config', function() {
        var codeText = $('#jetda-config-code').text();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(codeText).then(function() {
                alert('✅ 配置代码已复制，请粘贴到 wp-config.php 文件中。');
            }).catch(function() { fallbackCopy(codeText); });
        } else {
            fallbackCopy(codeText);
        }
    });
    function fallbackCopy(text) {
        var textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('✅ 配置代码已复制（使用备用方法），请粘贴到 wp-config.php 文件中。');
    }
});