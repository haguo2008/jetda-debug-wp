<?php
/*
 * File: /jetda-debug-wp/jetda-debug-wp.php
 * Plugin Name: Jetda Debug WP
 * Description: 专业的 WordPress 调试工具 – 记录 PHP 错误/警告，支持查看详情、单条删除，统一日志目录，内置 Dashicons 图标库。
 * Version:     1.5.0
 * Author:      Jetda
 * Text Domain: jetda-debug-wp
 */

defined( 'ABSPATH' ) || exit;

define( 'JETDA_DEBUG_PATH', plugin_dir_path( __FILE__ ) );
define( 'JETDA_DEBUG_URL',  plugin_dir_url( __FILE__ ) );
define( 'JETDA_DEBUG_LOG_DIR', WP_CONTENT_DIR . '/debug' );

// 尽早重定向 debug.log 到自定义目录
add_action( 'plugins_loaded', 'jetda_redirect_debug_log', 0 );
function jetda_redirect_debug_log() {
    if ( ! defined( 'WP_DEBUG_LOG' ) || WP_DEBUG_LOG === true ) {
        if ( ! is_dir( JETDA_DEBUG_LOG_DIR ) ) {
            wp_mkdir_p( JETDA_DEBUG_LOG_DIR );
        }
        $custom_log = JETDA_DEBUG_LOG_DIR . '/debug.log';
        if ( ! defined( 'WP_DEBUG_LOG' ) ) {
            define( 'WP_DEBUG_LOG', $custom_log );
        } elseif ( WP_DEBUG_LOG === true ) {
            ini_set( 'error_log', $custom_log );
        }
    }
}

// 引入核心类（新路径）
require_once JETDA_DEBUG_PATH . 'includes/class-jetda-debug.php';
Jetda_Debug_WP::init();

register_activation_hook( __FILE__, [ 'Jetda_Debug_WP', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'Jetda_Debug_WP', 'deactivate' ] );