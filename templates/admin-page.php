<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1><?php esc_html_e( 'Jetda Debug – PHP 错误列表', 'jetda-debug-wp' ); ?></h1>

    <div class="card" style="max-width:100%; margin-bottom:20px; background:#fafafa; border-left:4px solid <?php echo $all_correct ? '#46b450' : '#dc3232'; ?>;">
        <h2>🔧 调试常量配置向导</h2>
        <p>为了使插件能够正常记录 PHP 错误，请在 <code>wp-config.php</code> 中正确设置以下常量：</p>
        <table class="widefat" style="margin-bottom:15px; width:auto;">
            <thead><tr><th>常量名</th><th>推荐值</th><th>当前状态</th></tr></thead>
            <tbody>
                <tr><td><code>WP_DEBUG</code></td><td><code>true</code></td><td><?php echo Jetda_Debug_WP::format_constant_status( $wp_debug_val, true ); ?></td></tr>
                <tr><td><code>WP_DEBUG_LOG</code></td><td><code>true</code> 或路径</td><td><?php echo Jetda_Debug_WP::format_constant_status_log( $wp_debug_log_val ); ?></td></tr>
                <tr><td><code>WP_DEBUG_DISPLAY</code></td><td><code>false</code></td><td><?php echo Jetda_Debug_WP::format_constant_status( $wp_debug_display_val, false ); ?></td></tr>
            </tbody>
        </table>
        <?php if ( ! $all_correct ) : ?>
            <div class="notice notice-error inline"><p><strong>⚠️ 当前配置不符合要求，插件无法记录错误。</strong> 请复制以下代码并粘贴到 <code>wp-config.php</code>（放在 <code>&lt;?php</code> 之后）。</p></div>
            <pre id="jetda-config-code" style="background:#2c3e50;color:#ecf0f1;padding:12px;border-radius:4px;">
// Jetda Debug WP 所需配置
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);   // 日志将写入 /wp-content/debug/debug.log
define('WP_DEBUG_DISPLAY', false);
            </pre>
            <button id="jetda-copy-config" class="button button-primary">📋 一键复制代码</button>
            <p class="description">复制后粘贴到 <code>wp-config.php</code> 并保存，然后刷新本页面。</p>
        <?php else : ?>
            <div class="notice notice-success inline"><p>✅ 配置正确，插件已就绪。所有日志保存在 <code><?php echo JETDA_DEBUG_LOG_DIR; ?></code> 文件夹中。</p></div>
        <?php endif; ?>
    </div>

    <p><?php esc_html_e( '下面列出了所有捕获的 PHP 错误/警告，支持查看详情和单条删除。', 'jetda-debug-wp' ); ?></p>
    <div style="margin-bottom:15px;"><button id="jetda-clear-all-logs" class="button button-secondary">清空所有错误日志</button></div>

    <table class="wp-list-table widefat fixed striped" id="jetda-error-table">
        <thead>
            <tr><th>严重程度</th><th>类型</th><th>错误消息</th><th>时间</th><th>操作</th></tr>
        </thead>
        <tbody>
            <?php if ( empty( $errors ) ) : ?>
                <tr><td colspan="5">暂无任何错误记录。</td></tr>
            <?php else : ?>
                <?php foreach ( $errors as $err ) : ?>
                    <tr data-error-id="<?php echo esc_attr( $err['id'] ); ?>" data-file="<?php echo esc_attr( $err['file_path'] ); ?>" data-line-idx="<?php echo esc_attr( $err['line_index'] ); ?>">
                        <td><?php echo Jetda_Debug_WP::severity_icon( $err['severity'] ); ?></td>
                        <td><?php echo esc_html( $err['type_name'] ); ?></td>
                        <td><?php echo esc_html( Jetda_Debug_WP::shorten_message( $err['message'] ) ); ?></td>
                        <td><?php echo esc_html( $err['time'] ); ?></td>
                        <td>
                            <button class="button view-detail">详情</button>
                            <button class="button delete-error">删除</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ( ! empty( $page_links ) ) : ?>
        <div class="tablenav"><div class="tablenav-pages" style="margin: 10px 0;"><?php echo implode( '', $page_links ); ?></div></div>
    <?php endif; ?>
</div>