<?php
/**
 * Ép mọi request REST API đi qua HTTPS.
 *
 * VÌ SAO CẦN LỚP CHẶN NÀY
 *
 * WordPress tắt hẳn Application Password khi request không có SSL:
 * wp_is_application_passwords_available() trả về đúng giá trị của is_ssl().
 * Hệ quả là một request tới http://.../wp-json/... mang theo đúng tên đăng nhập
 * và đúng app password vẫn bị trả 401 "rest_not_logged_in" — không phải sai mật
 * khẩu, mà là WordPress chưa xét tới mật khẩu.
 *
 * Trên daquylucyen.net.vn, trang thường có redirect 301 sang HTTPS nhưng riêng
 * /wp-json thì không: nó trả 200 ngay trên HTTP trần, và khối "authentication" ở
 * gốc REST rỗng. Dịch vụ ngoài nào lưu địa chỉ site thiếu chữ "s" — hoặc tự ghép
 * http:// vào tên miền trần — sẽ gặp đúng cái 401 vô duyên đó rồi báo "không kết
 * nối được", trong khi gọi qua HTTPS thì mọi thứ chạy trơn.
 *
 * ĐÁNG RA NÊN NẰM Ở .HTACCESS
 *
 * Chặn ở tầng máy chủ rẻ hơn vì request bị trả về trước khi PHP khởi động. Nhưng
 * .htaccess của production không nằm trong git và trình sửa file đã bị plugin bảo
 * mật khóa, nên đặt ở đây để còn theo WP Pusher mà lên. Ngày nào thêm được rule
 * vào .htaccess thì đoạn này thành lớp dự phòng — cứ để nguyên, không hại gì.
 *
 * CHẠY Ở plugins_loaded, KHÔNG CHẠY LÚC NẠP PLUGIN
 *
 * Bản 1.2.0 gọi thẳng lúc nạp plugin cho sớm, và nhận 500 trên production.
 * wp_safe_redirect() nằm trong wp-includes/pluggable.php, mà WordPress nạp file
 * đó SAU khi include các plugin — chính là để plugin có cơ hội định nghĩa đè lên.
 * Gọi lúc nạp plugin là gọi hàm chưa tồn tại: fatal error.
 *
 * pluggable.php được nạp xong trước khi plugins_loaded chạy, nên hook vào đó với
 * priority 0 là sớm nhất mà vẫn an toàn — và vẫn còn rất xa mới tới lúc REST
 * dispatch hay xác thực người gửi. Có thêm lớp dự phòng gọi header() nếu
 * wp_safe_redirect() vắng mặt, để cái bẫy này không bao giờ thành 500 lần nữa.
 *
 * VÌ SAO 308 CHỨ KHÔNG PHẢI 301
 *
 * 301 cho phép client đổi POST thành GET khi đi theo redirect, mất luôn nội dung
 * bài đang gửi. 308 buộc giữ nguyên method và body. GET/HEAD vẫn trả 301 vì mọi
 * client cũ đều hiểu; 308 chỉ dành cho các method có body, nơi mất dữ liệu là
 * cái giá không trả được.
 *
 * @package dql-core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ép HTTPS cho REST API.
 */
class DQL_Rest_Https {

	/**
	 * Gắn hook.
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'ep_https' ), 0 );
	}

	/**
	 * Kiểm tra và chuyển hướng nếu request REST đang đi bằng HTTP.
	 */
	public static function ep_https() {
		if ( ! self::la_request_rest() ) {
			return;
		}

		if ( self::da_bao_mat() ) {
			return;
		}

		// Site chưa chạy HTTPS thì không có đâu mà chuyển tới. Lớp chặn này
		// cũng là thứ giữ cho site Local ở máy dev (http://daquylucyen.local)
		// chạy bình thường — thiếu nó là vòng lặp redirect vô tận.
		if ( 'https' !== wp_parse_url( home_url(), PHP_URL_SCHEME ) ) {
			return;
		}

		$duong_dan = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';

		// Dựng địa chỉ đích từ home_url() chứ không từ HTTP_HOST: header Host do
		// client đặt, tin vào nó là mở cửa cho chuyển hướng sang tên miền lạ.
		$dich = set_url_scheme( home_url( $duong_dan ), 'https' );

		$method = isset( $_SERVER['REQUEST_METHOD'] )
			? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) )
			: 'GET';

		$ma = in_array( $method, array( 'GET', 'HEAD' ), true ) ? 301 : 308;

		// Địa chỉ đích đã dựng từ home_url() nên tự nó đã an toàn; dùng
		// wp_safe_redirect() khi có để còn tôn trọng filter wp_redirect, và rơi
		// về header() nếu chạy sớm hơn lúc pluggable.php được nạp.
		if ( function_exists( 'wp_safe_redirect' ) ) {
			wp_safe_redirect( $dich, $ma );
		} else {
			header( 'Location: ' . $dich, true, $ma );
		}

		exit;
	}

	/**
	 * Request này có phải đang gọi vào REST API không.
	 *
	 * Không dùng được hằng REST_REQUEST vì nó chỉ được đặt ở cuối quy trình,
	 * lúc đã quá muộn. Phải tự đọc đường dẫn.
	 *
	 * @return bool
	 */
	private static function la_request_rest() {
		// Dạng dự phòng khi đường dẫn tĩnh bị tắt: /index.php?rest_route=/wp/v2/...
		if ( isset( $_GET['rest_route'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		if ( '' === $uri ) {
			return false;
		}

		$duong_dan = trim( (string) wp_parse_url( $uri, PHP_URL_PATH ), '/' );

		// rest_get_url_prefix() có filter, nhưng ở thời điểm này chưa plugin nào
		// kịp gắn vào nên nó trả về giá trị mặc định "wp-json". Đúng với site này.
		$tien_to = trim( rest_get_url_prefix(), '/' );

		return $duong_dan === $tien_to || 0 === strpos( $duong_dan, $tien_to . '/' );
	}

	/**
	 * Request đã đi bằng đường mã hóa chưa.
	 *
	 * @return bool
	 */
	private static function da_bao_mat() {
		if ( is_ssl() ) {
			return true;
		}

		// Sau proxy hoặc CDN, is_ssl() chỉ nhìn chặng proxy→PHP nên trả false dù
		// người dùng vào bằng HTTPS. Đọc thêm mấy header này là cách duy nhất
		// biết được, và cũng là lớp chống vòng lặp redirect nếu mai này site
		// nằm sau Cloudflare.
		$header_https = array(
			'HTTP_X_FORWARDED_PROTO' => 'https',
			'HTTP_X_FORWARDED_SSL'   => 'on',
			'HTTP_FRONT_END_HTTPS'   => 'on',
		);

		foreach ( $header_https as $header => $gia_tri ) {
			if ( isset( $_SERVER[ $header ] ) && $gia_tri === strtolower( sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) ) ) {
				return true;
			}
		}

		if ( isset( $_SERVER['HTTP_CF_VISITOR'] ) && false !== strpos( strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_VISITOR'] ) ) ), 'https' ) ) {
			return true;
		}

		return false;
	}
}
