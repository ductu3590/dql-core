<?php
/**
 * Plugin Name: Đá Quý Lục Yên — Lõi
 * Plugin URI:  https://daquylucyen.net.vn
 * Description: Dữ liệu và công cụ riêng của daquylucyen.net.vn: tra đá hợp mệnh theo năm sinh, từ điển thuật ngữ đá quý, đếm lượt xem bài viết.
 * Version:     1.2.0
 * Author:      daquylucyen.net.vn
 * License:     GPL-2.0-or-later
 * Text Domain: dql-core
 * Requires PHP: 7.4
 *
 * VÌ SAO LÀ PLUGIN CHỨ KHÔNG NẰM TRONG THEME
 *
 * WordPress coi Custom Post Type và shortcode là chức năng của theme đang bật.
 * Nếu để từ điển thuật ngữ trong theme, ngày nào đổi theme là toàn bộ nội dung
 * biến mất khỏi trang quản trị — dữ liệu vẫn nằm trong database nhưng không còn
 * cách nào nhìn thấy hay sửa.
 *
 * Nguyên tắc phân chia:
 *   Theme  → giao diện. Đổi được bất cứ lúc nào.
 *   Plugin → dữ liệu và logic nghiệp vụ. Sống lâu hơn theme.
 *
 * @package dql-core
 */

defined( 'ABSPATH' ) || exit;

define( 'DQL_CORE_VERSION', '1.2.0' );
define( 'DQL_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'DQL_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once DQL_CORE_DIR . 'includes/class-dql-menh.php';
require_once DQL_CORE_DIR . 'includes/class-dql-menh-tool.php';
require_once DQL_CORE_DIR . 'includes/class-dql-glossary.php';
require_once DQL_CORE_DIR . 'includes/class-dql-luot-xem.php';
require_once DQL_CORE_DIR . 'includes/class-dql-rest-https.php';

/*
 * Ép HTTPS cho REST API — chạy ngay, không chờ hook.
 *
 * Mọi thứ khác trong plugin này khởi động ở plugins_loaded. Riêng việc này
 * phải xong sớm nhất có thể: nó quyết định request có được đi tiếp hay bị
 * trả về, nên càng ít code chạy trước nó càng tốt. Hàm tự kiểm tra điều
 * kiện và không làm gì cả với request thường.
 */
DQL_Rest_Https::ep_https();

/**
 * Khởi động plugin.
 */
function dql_core_init() {
	DQL_Menh_Tool::init();
	DQL_Glossary::init();
	DQL_Luot_Xem::init();
}
add_action( 'plugins_loaded', 'dql_core_init' );

/**
 * Nạp lại quy tắc đường dẫn khi bật plugin.
 *
 * Không có bước này thì /tu-dien/ trả về lỗi 404 cho tới khi ai đó vào
 * Cài đặt → Đường dẫn tĩnh và bấm Lưu — một cái bẫy im lặng rất hay gặp.
 */
function dql_core_activate() {
	DQL_Glossary::dang_ky_cpt();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'dql_core_activate' );

/**
 * Dọn quy tắc đường dẫn khi tắt plugin.
 */
function dql_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'dql_core_deactivate' );
