<?php
/*
 * File: /jetda-debug-wp/includes/class-jetda-debug.php
 * Core debug class – error handling, logging, admin UI, AJAX, cron cleanup, config check, Dashicons library.
 * Version: 1.5.0
 */

class Jetda_Debug_WP {

    private static $instance = null;
    private static $log_file = '';

    public static function init() {
        if ( self::$instance ) return self::$instance;
        self::$instance = new self();
        self::$log_file = self::get_log_file_path();

        if ( self::is_debug_enabled() ) {
            set_error_handler( [ self::class, 'error_handler' ] );
            register_shutdown_function( [ self::class, 'shutdown_handler' ] );
        }

        add_action( 'admin_menu', [ self::class, 'add_admin_menu' ] );
        add_action( 'admin_menu', [ self::class, 'add_submenu_pages' ] );

        add_action( 'wp_ajax_jetda_delete_error', [ self::class, 'ajax_delete_error' ] );
        add_action( 'wp_ajax_jetda_get_error_detail', [ self::class, 'ajax_get_error_detail' ] );
        add_action( 'wp_ajax_jetda_clear_all_logs', [ self::class, 'ajax_clear_all_logs' ] );

        add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_scripts' ] );

        add_action( 'jetda_weekly_cleanup', [ self::class, 'weekly_cleanup' ] );
        add_filter( 'cron_schedules', [ self::class, 'add_weekly_schedule' ] );
    }

    private static function is_debug_enabled() {
        $wp_debug        = self::get_constant_bool( 'WP_DEBUG' );
        $wp_debug_log    = self::get_constant_bool( 'WP_DEBUG_LOG' );
        $wp_debug_display = self::get_constant_bool( 'WP_DEBUG_DISPLAY' );
        return $wp_debug && $wp_debug_log !== false && !$wp_debug_display;
    }

    private static function get_constant_bool( $constant ) {
        if ( ! defined( $constant ) ) return null;
        $value = constant( $constant );
        if ( is_bool( $value ) ) return $value;
        if ( is_string( $value ) ) {
            $lower = strtolower( trim( $value ) );
            if ( $lower === 'true' ) return true;
            if ( $lower === 'false' ) return false;
            return $value;
        }
        return (bool) $value;
    }

    private static function get_log_file_path() {
        $today = current_time( 'Ymd' );
        $dir = JETDA_DEBUG_LOG_DIR;
        if ( ! is_dir( $dir ) ) wp_mkdir_p( $dir );
        return trailingslashit( $dir ) . "php_errors_{$today}.jsonl";
    }

    public static function error_handler( $type, $message, $file, $line ) {
        if ( $type === E_STRICT ) return true;
        if ( $type === E_NOTICE && strpos( $message, 'Undefined index:' ) !== false ) return true;
        if ( $type === E_WARNING && strpos( $message, 'fopen(' ) !== false ) return true;

        $error_data = [
            'type'      => $type,
            'type_name' => self::get_error_type_string( $type ),
            'message'   => $message,
            'file'      => $file,
            'line'      => $line,
            'time'      => current_time( 'mysql' ),
            'url'       => self::get_current_url(),
            'referer'   => $_SERVER['HTTP_REFERER'] ?? '',
            'post'      => self::get_post_data(),
            'caller'    => wp_debug_backtrace_summary(),
            'severity'  => self::get_severity_level( $type )
        ];
        $json_line = json_encode( $error_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        if ( $json_line !== false ) {
            file_put_contents( self::$log_file, $json_line . "\n", FILE_APPEND | LOCK_EX );
        }
        return true;
    }

    public static function shutdown_handler() {
        $error = error_get_last();
        if ( $error && in_array( $error['type'], [ E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ] ) ) {
            self::error_handler( $error['type'], $error['message'], $error['file'], $error['line'] );
        }
    }

    private static function get_error_type_string( $type ) {
        switch ( $type ) {
            case E_ERROR: return 'Fatal Error';
            case E_WARNING: return 'Warning';
            case E_PARSE: return 'Parse Error';
            case E_NOTICE: return 'Notice';
            case E_CORE_ERROR: return 'Core Error';
            case E_COMPILE_ERROR: return 'Compile Error';
            case E_USER_ERROR: return 'User Error';
            case E_USER_WARNING: return 'User Warning';
            case E_USER_NOTICE: return 'User Notice';
            case E_STRICT: return 'Strict Standards';
            case E_RECOVERABLE_ERROR: return 'Recoverable Error';
            case E_DEPRECATED: return 'Deprecated';
            case E_USER_DEPRECATED: return 'User Deprecated';
            default: return 'Unknown Error';
        }
    }

    private static function get_severity_level( $type ) {
        if ( in_array( $type, [ E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR ] ) ) {
            return 'critical';
        }
        if ( in_array( $type, [ E_WARNING, E_USER_WARNING, E_RECOVERABLE_ERROR ] ) ) {
            return 'warning';
        }
        if ( in_array( $type, [ E_NOTICE, E_USER_NOTICE, E_STRICT, E_DEPRECATED, E_USER_DEPRECATED ] ) ) {
            return 'notice';
        }
        return 'info';
    }

    private static function get_post_data() {
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) return '';
        if ( ! empty( $_POST ) ) {
            $safe_post = array_map( function( $v ) {
                return is_scalar( $v ) ? $v : '[complex]';
            }, $_POST );
            return var_export( $safe_post, true );
        }
        $input = file_get_contents( 'php://input' );
        return $input ?: '';
    }

    private static function get_current_url() {
        $ssl      = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' );
        $protocol = $ssl ? 'https' : 'http';
        $host     = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        $port     = ( $ssl ? 443 : 80 ) != ( $_SERVER['SERVER_PORT'] ?? 80 ) ? ':' . $_SERVER['SERVER_PORT'] : '';
        return $protocol . '://' . $host . $port . ( $_SERVER['REQUEST_URI'] ?? '' );
    }

    public static function add_admin_menu() {
        add_menu_page(
            __( 'Jetda Debug', 'jetda-debug-wp' ),
            __( 'Debug WP', 'jetda-debug-wp' ),
            'manage_options',
            'jetda-debug',
            [ self::class, 'render_admin_page' ],
            'dashicons-buddicons-replies',
            100
        );
    }

    public static function add_submenu_pages() {
        add_submenu_page(
            'jetda-debug',
            __( 'Dashicons 图标库', 'jetda-debug-wp' ),
            __( 'Dashicons 图标库', 'jetda-debug-wp' ),
            'manage_options',
            'jetda-debug-icons',
            [ self::class, 'render_icons_page' ]
        );
    }

    public static function render_admin_page() {
        $all_errors = self::get_all_errors();
        $total = count( $all_errors );
        $per_page = 20;
        $current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $offset = ( $current_page - 1 ) * $per_page;
        $errors = array_slice( $all_errors, $offset, $per_page );

        $pagination_args = array(
            'base'      => add_query_arg( 'paged', '%#%' ),
            'format'    => '',
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
            'total'     => ceil( $total / $per_page ),
            'current'   => $current_page,
            'type'      => 'array',
        );
        $page_links = paginate_links( $pagination_args );

        $wp_debug_val = self::get_constant_bool('WP_DEBUG');
        $wp_debug_log_val = self::get_constant_bool('WP_DEBUG_LOG');
        $wp_debug_display_val = self::get_constant_bool('WP_DEBUG_DISPLAY');
        $all_correct = ( $wp_debug_val === true && $wp_debug_log_val !== false && $wp_debug_display_val === false );

        include JETDA_DEBUG_PATH . 'templates/admin-page.php';
    }

    public static function render_icons_page() {
        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style( 'jetda-icons', JETDA_DEBUG_URL . 'assets/icons.css', [], '1.5.0' );
        wp_enqueue_script( 'jetda-icons', JETDA_DEBUG_URL . 'assets/icons.js', [ 'jquery' ], '1.5.0', true );
        $icons = self::get_dashicons_list();
        include JETDA_DEBUG_PATH . 'templates/icons-page.php';
    }

    public static function get_dashicons_list() {
		$css_file = ABSPATH . WPINC . '/css/dashicons.min.css';
		if ( ! file_exists( $css_file ) ) return [];
		$content = file_get_contents( $css_file );
		preg_match_all( '/\.(dashicons-[a-zA-Z0-9\-]+):before\s*\{\s*content:\s*"\\\\([a-f0-9]+)"/', $content, $matches );
		$icons = [];
		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $index => $class ) {
				// 根据类名确定分组
				$group = 'other';
				$group_title = __( '其他', 'jetda-debug-wp' );

				if ( strpos( $class, '-admin' ) !== false || strpos( $class, '-menu' ) !== false ) {
					$group = 'admin_menu';
					$group_title = __( '菜单', 'jetda-debug-wp' );
				} elseif ( strpos( $class, '-welcome' ) !== false ) {
					$group = 'welcome';
					$group_title = __( '欢迎菜单', 'jetda-debug-wp' );
				} elseif ( strpos( $class, '-format' ) !== false ) {
					$group = 'format';
					$group_title = __( '格式', 'jetda-debug-wp' );
				} elseif ( strpos( $class, '-media' ) !== false ) {
					$group = 'media';
					$group_title = __( '媒体', 'jetda-debug-wp' );
				} elseif ( strpos( $class, '-image' ) !== false ) {
					$group = 'image';
					$group_title = __( '图像', 'jetda-debug-wp' );
				} elseif ( strpos( $class, '-database' ) !== false ) {
					$group = 'database';
					$group_title = __( '数据库', 'jetda-debug-wp' );
				}

				$icons[] = [
					'class'       => $class,
					'code'        => $matches[2][$index] ?? '',
					'group'       => $group,
					'group_title' => $group_title,
				];
			}
		}
		// 去重
		$icons = array_unique( $icons, SORT_REGULAR );
		// 按类名字母排序
		usort( $icons, function( $a, $b ) {
			return strcmp( $a['class'], $b['class'] );
		});
		return $icons;
	}

    public static function format_constant_status( $value, $required ) {
        if ( $value === null ) return '<span style="color:#dc3232;">❌ 未定义</span>';
        $is_correct = ( $value === $required );
        $status_text = $value ? 'true' : 'false';
        return $is_correct ? '<span style="color:#46b450;">✅ ' . $status_text . '</span>' : '<span style="color:#dc3232;">❌ ' . $status_text . '（应为 ' . ($required ? 'true' : 'false') . '）</span>';
    }

    public static function format_constant_status_log( $value ) {
        if ( $value === null ) return '<span style="color:#dc3232;">❌ 未定义</span>';
        if ( is_string( $value ) ) return '<span style="color:#46b450;">✅ 路径：' . esc_html( $value ) . '</span>';
        if ( $value === true ) return '<span style="color:#46b450;">✅ true（插件会自动重定向）</span>';
        return '<span style="color:#dc3232;">❌ false（日志已禁用）</span>';
    }

    public static function severity_icon( $level ) {
        $icons = [
            'critical' => '🚨 <span style="color:#d63638;">严重错误</span>',
            'warning'  => '⚠️ <span style="color:#f0a000;">警告</span>',
            'notice'   => '🔔 <span style="color:#1d4ed8;">注意</span>',
            'info'     => 'ℹ️ 信息'
        ];
        return $icons[ $level ] ?? '•';
    }

    public static function shorten_message( $msg, $len = 120 ) {
        if ( mb_strlen( $msg ) <= $len ) return $msg;
        return mb_substr( $msg, 0, $len ) . '…';
    }

    private static function get_all_errors() {
        $dir = JETDA_DEBUG_LOG_DIR;
        if ( ! is_dir( $dir ) ) return [];
        $errors = [];
        $files = glob( $dir . '/php_errors_*.jsonl' );
        foreach ( $files as $file ) {
            $lines = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
            if ( ! $lines ) continue;
            foreach ( $lines as $line_idx => $line ) {
                $data = json_decode( $line, true );
                if ( ! is_array( $data ) ) continue;
                $data['id'] = md5( $file . '_' . $line_idx );
                $data['file_path'] = $file;
                $data['line_index'] = $line_idx;
                $errors[] = $data;
            }
        }
        usort( $errors, function( $a, $b ) {
            return strtotime( $b['time'] ) - strtotime( $a['time'] );
        });
        return $errors;
    }

    public static function ajax_get_error_detail() {
        check_ajax_referer( 'jetda_debug_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( '权限不足' );

        $file = $_POST['file'] ?? '';
        $line_idx = intval( $_POST['line_idx'] ?? -1 );
        if ( ! $file || ! file_exists( $file ) || $line_idx < 0 ) wp_die( '无效参数' );

        $lines = file( $file, FILE_IGNORE_NEW_LINES );
        if ( ! isset( $lines[ $line_idx ] ) ) wp_die( '未找到该错误' );

        $error = json_decode( $lines[ $line_idx ], true );
        if ( ! $error ) wp_die( '错误数据损坏' );

        $html = '<div style="max-width:800px; padding:10px;">';
        $html .= '<h3>' . esc_html( $error['type_name'] ) . '</h3>';
        $html .= '<p><strong>消息：</strong><br>' . nl2br( esc_html( $error['message'] ) ) . '</p>';
        $html .= '<p><strong>文件：</strong> ' . esc_html( $error['file'] ) . ' : ' . intval( $error['line'] ) . '</p>';
        $html .= '<p><strong>URL：</strong> ' . esc_url( $error['url'] ) . '</p>';
        if ( ! empty( $error['referer'] ) ) {
            $html .= '<p><strong>来源页：</strong> ' . esc_url( $error['referer'] ) . '</p>';
        }
        if ( ! empty( $error['post'] ) ) {
            $html .= '<p><strong>POST 数据：</strong><br><pre>' . esc_html( $error['post'] ) . '</pre></p>';
        }
        $html .= '<p><strong>调用栈：</strong><br><pre>' . esc_html( $error['caller'] ) . '</pre></p>';
        $html .= '<p><strong>时间：</strong> ' . esc_html( $error['time'] ) . '</p>';
        $html .= '<p><button id="jetda-close-tb" class="button">关闭</button></p>';
        $html .= '</div>';
        wp_send_json_success( [ 'html' => $html ] );
    }

    public static function ajax_delete_error() {
        check_ajax_referer( 'jetda_debug_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( '权限不足' );

        $file = $_POST['file'] ?? '';
        $line_idx = intval( $_POST['line_idx'] ?? -1 );
        if ( ! $file || ! file_exists( $file ) || $line_idx < 0 ) {
            wp_send_json_error( '无效参数' );
        }

        $lines = file( $file, FILE_IGNORE_NEW_LINES );
        if ( $lines === false ) wp_send_json_error( '无法读取日志文件' );
        if ( ! isset( $lines[ $line_idx ] ) ) wp_send_json_error( '错误行不存在' );

        unset( $lines[ $line_idx ] );
        $new_lines = array_values( $lines );
        $new_content = implode( "\n", $new_lines );
        if ( ! empty( $new_lines ) ) $new_content .= "\n";
        if ( file_put_contents( $file, $new_content, LOCK_EX ) === false ) {
            $error = error_get_last();
            wp_send_json_error( '写入文件失败：' . ( $error['message'] ?? '未知错误' ) );
        }
        if ( empty( $new_lines ) ) @unlink( $file );
        wp_send_json_success( [ 'message' => '删除成功' ] );
    }

    public static function ajax_clear_all_logs() {
        check_ajax_referer( 'jetda_debug_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( '权限不足' );

        $dir = JETDA_DEBUG_LOG_DIR;
        if ( ! is_dir( $dir ) ) wp_send_json_success( [ 'message' => '没有日志目录，无需清理' ] );
        $files = glob( $dir . '/*.{log,jsonl}', GLOB_BRACE );
        $deleted = 0;
        foreach ( $files as $file ) {
            if ( unlink( $file ) ) $deleted++;
        }
        wp_send_json_success( [ 'message' => "已删除 {$deleted} 个日志文件。" ] );
    }

    public static function weekly_cleanup() {
        $dir = JETDA_DEBUG_LOG_DIR;
        if ( ! is_dir( $dir ) ) return;
        $files = glob( $dir . '/*.{log,jsonl}', GLOB_BRACE );
        foreach ( $files as $file ) @unlink( $file );
    }

    public static function add_weekly_schedule( $schedules ) {
        if ( ! isset( $schedules['weekly'] ) ) {
            $schedules['weekly'] = [
                'interval' => WEEK_IN_SECONDS,
                'display'  => __( '每周一次', 'jetda-debug-wp' )
            ];
        }
        return $schedules;
    }

    public static function enqueue_scripts( $hook ) {
        if ( $hook !== 'toplevel_page_jetda-debug' ) return;
        wp_enqueue_script( 'thickbox' );
        wp_enqueue_style( 'thickbox' );
        wp_enqueue_script( 'jetda-debug-admin', JETDA_DEBUG_URL . 'assets/js/admin.js', [ 'jquery', 'thickbox' ], '1.5.0', true );
        wp_localize_script( 'jetda-debug-admin', 'jetdaDebug', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'jetda_debug_nonce' ),
            'loading' => __( '加载中...', 'jetda-debug-wp' ),
            'deleteConfirm' => __( '确定要删除这条错误记录吗？', 'jetda-debug-wp' )
        ] );
    }

    public static function activate() {
        if ( ! is_dir( JETDA_DEBUG_LOG_DIR ) ) wp_mkdir_p( JETDA_DEBUG_LOG_DIR );
        add_filter( 'cron_schedules', [ self::class, 'add_weekly_schedule' ] );
        if ( ! wp_next_scheduled( 'jetda_weekly_cleanup' ) ) {
            wp_schedule_event( strtotime( 'next Sunday 03:00:00' ), 'weekly', 'jetda_weekly_cleanup' );
        }
    }

    public static function deactivate() {
        wp_clear_scheduled_hook( 'jetda_weekly_cleanup' );
    }
}