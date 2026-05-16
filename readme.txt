Jetda Debug WP – 专业 WordPress 调试工具
版本：1.5.0
作者：Jetda
授权：GPL v2
最低 WordPress 版本：5.8
最低 PHP 版本：7.4

简介
Jetda Debug WP 是一款专门为 WordPress 开发者设计的高效调试插件。它能够自动捕获 PHP 错误、警告、通知，并以友好的后台界面展示，支持单条删除、一键清空、每周自动清理。此外，内置了完整的 Dashicons 图标库，支持按分组浏览和一键复制 CSS 类名，极大提升开发效率。

主要功能
?? 错误捕获与管理
自动记录所有 PHP 错误类型（包括致命错误），以 JSON Lines 格式存储，每条错误独立。

统一日志目录：将 WordPress 原生 debug.log 重定向到 /wp-content/debug/，便于集中管理。

后台表格展示：按时间倒序排列，支持分页（每页20条），直观查看错误消息、类型、严重程度、时间等。

严重程度图标：使用 ??（严重错误）、??（警告）、??（注意）快速识别问题等级。

详情模态框：点击“详情”使用 WordPress 原生 ThickBox 弹出完整错误信息（包括文件、行号、URL、来源页、POST 数据、调用栈）。

AJAX 单条删除：直接删除某条错误，表格行淡出，若详情框打开则自动关闭。

一键清空所有日志：删除整个 /wp-content/debug/ 下的所有 .log 和 .jsonl 文件。

?? 配置向导
自动检测 wp-config.php 中 WP_DEBUG、WP_DEBUG_LOG、WP_DEBUG_DISPLAY 常量的定义与值。

若配置缺失或错误，显示红色警告并提供一键复制正确配置代码的功能。

引导用户正确开启调试模式，确保插件正常记录错误。

?? 自动清理机制
注册每周定时任务（周日 03:00），自动删除所有日志文件，避免磁盘占满。

停用插件时自动清理 Cron 任务。

?? Dashicons 图标库
子菜单页完整展示 WordPress 官方 Dashicons 图标库。

自动解析 /wp-includes/css/dashicons.min.css 获取所有图标，无需手动维护。

图标按功能分组：菜单、欢迎菜单、格式、媒体、图像、数据库、其他。

点击图标即可复制 CSS 类名（如 dashicons-admin-home），并显示提示。

分组顺序可定制，“其他”组排在最后。

?? 高性能设计
错误捕获仅在 WP_DEBUG 为 true 时注册钩子，不影响生产环境性能。

后台列表使用分页，避免一次性加载海量日志。

日志文件按天分割，方便归档和清理。

安装方法
手动安装
下载插件 zip 压缩包，解压得到 jetda-debug-wp 文件夹。

通过 FTP 或主机管理面板，将文件夹上传到 /wp-content/plugins/ 目录。

登录 WordPress 后台，进入 插件 页面，找到 Jetda Debug WP，点击 启用。

自动安装（后续支持 WordPress 官方库）
首次配置
为了让插件能够正常记录错误，您需要在 wp-config.php 中添加或修改以下常量：

php
// 请放在 <?php 之后，/* That's all, stop editing! */ 之前
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
说明：

WP_DEBUG：开启调试模式。

WP_DEBUG_LOG：将错误记录到日志文件。插件会自动将日志路径设为 /wp-content/debug/debug.log。

WP_DEBUG_DISPLAY：禁止直接在页面输出错误，避免破坏前端布局。

保存 wp-config.php 后，刷新后台 Debug WP 页面，顶部会显示配置状态。如果配置正确，会看到绿色成功提示；否则会给出红色警告和一键复制按钮。

注意：插件激活时会自动创建 /wp-content/debug/ 目录，如果因权限问题无法创建，请手动创建并确保可写。

使用指南
查看错误列表
进入后台 → Debug WP 菜单。

表格展示所有错误，按时间倒序排列。

您可以通过表格底部的分页导航浏览更多错误。

查看错误详情
点击任意错误行的 详情 按钮。

弹出模态框，显示完整的错误信息，包括：

消息内容

文件和行号

触发错误的 URL

来源页面（Referer）

POST 数据（如有）

调用栈（wp_debug_backtrace_summary）

发生时间

删除单条错误
点击错误行的 删除 按钮。

确认删除后，该行会淡出消失，0.8 秒后若详情框打开则自动关闭。

删除操作不可恢复，请谨慎使用。

清空所有日志
点击表格上方的 清空所有错误日志 按钮。

确认后，整个 /wp-content/debug/ 目录下的所有 .log 和 .jsonl 文件将被永久删除。

刷新页面后表格为空。

浏览 Dashicons 图标库
进入 Debug WP → Dashicons 图标库 子菜单。

图标按分组显示（菜单、欢迎菜单、格式、媒体、图像、数据库、其他）。

点击任意图标，其 CSS 类名会自动复制到剪贴板，并出现“已复制”提示。

便于开发者在主题或插件中快速使用官方图标。

定时清理
插件会在每周日 03:00 自动执行一次日志清理，删除所有 .log 和 .jsonl 文件。

无需人工干预。

常见问题（FAQ）
1. 启用插件后，表格显示“暂无任何错误记录”？
请检查 wp-config.php 中的调试常量是否配置正确（见上文“首次配置”）。

确认 /wp-content/debug/ 目录存在且可写。

如果网站没有任何 PHP 错误/警告，表格自然为空。可以故意触发一个错误测试（如访问一个不存在的函数）。

2. 删除第一条错误时报错“错误行不存在”？
该问题已在 1.5.0 版本修复。如果仍出现，请检查日志文件是否被其他程序锁定或文件开头含有 BOM 头。建议停用插件后重新激活，或手动删除 /wp-content/debug/php_errors_*.jsonl 文件。

3. 清空所有日志后，debug.log 中仍有插件的调试信息？
1.5.0 版本已移除所有 error_log 输出。如果仍有残留，请手动删除 debug.log 文件，之后不会再产生。

4. 图标库页面图标显示为方框？
确保 WordPress 核心文件完整，Dashicons 字体文件位于 /wp-includes/fonts/。部分主题可能禁用 Dashicons，但后台管理页面默认是启用的。

5. 如何修改每周清理的时间？
停用插件，编辑 includes/class-jetda-debug.php 中 activate() 方法的 strtotime( 'next Sunday 03:00:00' ) 参数，改为您需要的时间，然后重新激活插件。或者使用 WP Crontrol 插件修改现有 Cron 任务。

6. 插件会影响网站速度吗？
不会。错误捕获仅在 WP_DEBUG 开启时注册钩子，且记录操作极轻量。后台列表使用分页，定时清理任务由 WordPress Cron 触发，对性能影响极小。

7. 我可以将日志目录改为其他位置吗？
可以。在 wp-config.php 中定义常量：

php
define( 'JETDA_DEBUG_LOG_DIR', WP_CONTENT_DIR . '/my-logs' );
必须在插件加载之前定义（即放在 wp-config.php 中）。注意目录需可写。

升级指南
备份旧版插件目录。

删除旧版文件夹，上传新版 jetda-debug-wp 文件夹。

在 WordPress 后台 插件 页面停用并重新激活插件。

刷新 Debug WP 页面，新功能自动生效。

插件文件结构
text
jetda-debug-wp/
├── jetda-debug-wp.php                // 插件入口
├── includes/
│   └── class-jetda-debug.php         // 核心类
├── templates/
│   ├── admin-page.php                // 错误列表页模板
│   └── icons-page.php                // 图标库页模板
└── assets/
    ├── icons.css                     // 图标库样式
    ├── icons.js                      // 图标库复制脚本
    └── js/
        └── admin.js                  // 错误管理页 AJAX 脚本
更新日志
1.5.0 (2026-05-17)
重构代码结构：核心类移至 includes/ 目录，便于扩展。

修复删除首条错误时的索引问题。

移除所有调试输出，避免污染日志文件。

优化图标库分组顺序，“其他”组移至最后。

完善配置向导，支持字符串 'true' / 'false' 常量值。

统一日志目录，自动重定向 debug.log。

1.4.0
增加分页功能，每页显示 20 条错误。

改进表格样式，更符合 WordPress 原生风格。

1.3.0
将 debug.log 重定向到 /wp-content/debug/ 目录。

简化后台界面，移除冗余的 debug.log 管理卡片。

1.2.0
添加配置向导：检测调试常量，一键复制配置代码。

优化常量检测逻辑，支持字符串布尔值。

1.1.0
添加手动清空按钮和每周自动清理功能。

支持 AJAX 删除单条错误并自动关闭模态框。

1.0.0
初始版本：基础错误捕获与后台表格展示。

贡献与反馈
如果您有任何建议或遇到问题，欢迎通过电子邮件或 GitHub 提交 Issue。本插件遵循 GPL v2 许可证，可自由修改和分发，但请保留版权信息。

感谢使用 Jetda Debug WP！ ??
让调试变得更简单、更高效。

本回答由 AI 生成，内容仅供参考，请仔细甄别。

