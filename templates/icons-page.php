<?php
/*
 * File: /jetda-debug-wp/templates/icons-page.php
 * Dashicons 图标库展示页面
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1><?php esc_html_e( 'Dashicons 图标库', 'jetda-debug-wp' ); ?></h1>
    <p><?php esc_html_e( '点击任意图标即可复制其 CSS 类名。图标按功能分组显示。', 'jetda-debug-wp' ); ?></p>

    <?php
    // 将图标按分组整理
    $grouped_icons = [];
    foreach ( $icons as $icon ) {
        $group = $icon['group'];
        $group_title = $icon['group_title'];
        if ( ! isset( $grouped_icons[ $group ] ) ) {
            $grouped_icons[ $group ] = [
                'title' => $group_title,
                'icons' => []
            ];
        }
        $grouped_icons[ $group ]['icons'][] = $icon;
    }

    // 定义分组显示顺序（其他排最后）
    $group_order = [ 
        'welcome'    => '欢迎菜单',
		'admin_menu' => '菜单',
        'format'     => '格式',
        'media'      => '媒体',
        'image'      => '图像',
        'database'   => '数据库',
        'other'      => '其他',
    ];
    ?>

    <?php foreach ( $group_order as $group_key => $group_title ) : ?>
        <?php if ( isset( $grouped_icons[ $group_key ] ) ) : ?>
            <div class="jetda-icons-group">
                <h2 class="jetda-group-title"><?php echo esc_html( $grouped_icons[ $group_key ]['title'] ); ?></h2>
                <div class="jetda-icons-grid">
                    <?php foreach ( $grouped_icons[ $group_key ]['icons'] as $icon ) : ?>
                        <div class="jetda-icon-item" data-clipboard-text="<?php echo esc_attr( $icon['class'] ); ?>">
                            <span class="dashicons <?php echo esc_attr( $icon['class'] ); ?>"></span>
                            <code><?php echo esc_html( $icon['class'] ); ?></code>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>