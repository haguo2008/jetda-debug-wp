# Jetda Debug WP – 专业 WordPress 调试工具

**版本**：1.5.0  
**作者**：Jetda  
**授权**：GPL v2  
**最低 WordPress 版本**：5.8  
**最低 PHP 版本**：7.4

## 简介

Jetda Debug WP 是一款专门为 WordPress 开发者设计的高效调试插件。它能够自动捕获 PHP 错误、警告、通知，并以友好的后台界面展示，支持单条删除、一键清空、每周自动清理。此外，内置了完整的 Dashicons 图标库，支持按分组浏览和一键复制 CSS 类名，极大提升开发效率。

## 主要功能

- 🔍 **错误捕获与管理**
  - 自动记录所有 PHP 错误类型（包括致命错误），以 JSON Lines 格式存储，每条错误独立。
  - 统一日志目录：将 WordPress 原生 debug.log 重定向到 `/wp-content/debug/`，便于集中管理。
  - 后台表格展示：按时间倒序排列，支持分页（每页20条），直观查看错误消息、类型、严重程度、时间等。
  - 严重程度图标：使用 🚨（严重错误）、⚠️（警告）、🔔（注意）快速识别问题等级。
  - 详情模态框：点击“详情”使用 WordPress 原生 ThickBox 弹出完整错误信息（包括文件、行号、URL、来源页、POST 数据、调用栈）。
  - AJAX 单条删除：直接删除某条错误，表格行淡出，若详情框打开则自动关闭。
  - 一键清空所有日志：删除整个 `/wp-content/debug/` 下的所有 `.log` 和 `.jsonl` 文件。

- 🛠 **配置向导**
  - 自动检测 `wp-config.php` 中 `WP_DEBUG`、`WP_DEBUG_LOG`、`WP_DEBUG_DISPLAY` 常量的定义与值。
  - 若配置缺失或错误，显示红色警告并提供一键复制正确配置代码的功能。
  - 引导用户正确开启调试模式，确保插件正常记录错误。

- 📅 **自动清理机制**
  - 注册每周定时任务（周日 03:00），自动删除所有日志文件，避免磁盘占满。
  - 停用插件时自动清理 Cron 任务。

- 🎨 **Dashicons 图标库**
  - 子菜单页完整展示 WordPress 官方 Dashicons 图标库。
  - 自动解析 `/wp-includes/css/dashicons.min.css` 获取所有图标，无需手动维护。
  - 图标按功能分组：菜单、欢迎菜单、格式、媒体、图像、数据库、其他。
  - 点击图标即可复制 CSS 类名（如 `dashicons-admin-home`），并显示提示。
  - 分组顺序可定制，“其他”组排在最后。

- 🚀 **高性能设计**
  - 错误捕获仅在 `WP_DEBUG` 为 true 时注册钩子，不影响生产环境性能。
  - 后台列表使用分页，避免一次性加载海量日志。
  - 日志文件按天分割，方便归档和清理。

## 安装方法

### 手动安装

1. 下载插件 zip 压缩包，解压得到 `jetda-debug-wp` 文件夹。
2. 通过 FTP 或主机管理面板，将文件夹上传到 `/wp-content/plugins/` 目录。
3. 登录 WordPress 后台，进入 **插件** 页面，找到 **Jetda Debug WP**，点击 **启用**。

> 自动安装（后续支持 WordPress 官方库）

### 首次配置

为了让插件能够正常记录错误，您需要在 `wp-config.php` 中添加或修改以下常量：

```php
// 请放在 <?php 之后，/* That's all, stop editing! */ 之前
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);