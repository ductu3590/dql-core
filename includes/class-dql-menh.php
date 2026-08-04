<?php
/**
 * Tính mệnh ngũ hành từ năm sinh dương lịch.
 *
 * @package dql-core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Phép tính can chi và nạp âm.
 */
class DQL_Menh {

	/**
	 * Năm nhỏ nhất chấp nhận. Trước mốc này gần như không có người dùng thật,
	 * và cũng là ranh giới an toàn cho phép chia lấy dư.
	 */
	const NAM_MIN = 1900;

	/**
	 * Bảng nạp âm, nạp một lần cho mỗi lần tải trang.
	 *
	 * @var array|null
	 */
	private static $bang = null;

	/**
	 * Gợi ý theo mệnh, nạp một lần cho mỗi lần tải trang.
	 *
	 * @var array|null
	 */
	private static $goi_y = null;

	/**
	 * Đọc bảng nạp âm.
	 *
	 * @return array
	 */
	public static function bang() {
		if ( null === self::$bang ) {
			self::$bang = require DQL_CORE_DIR . 'includes/data/nap-am.php';
		}

		return self::$bang;
	}

	/**
	 * Đọc bảng gợi ý theo mệnh.
	 *
	 * Cho phép lọc để có thể chỉnh từ theme hoặc plugin khác mà không phải sửa
	 * file dữ liệu — hữu ích khi muốn thử nghiệm mà vẫn giữ nguyên bản gốc.
	 *
	 * @return array
	 */
	public static function goi_y() {
		if ( null === self::$goi_y ) {
			self::$goi_y = apply_filters(
				'dql_menh_goi_y',
				require DQL_CORE_DIR . 'includes/data/menh-goi-y.php'
			);
		}

		return self::$goi_y;
	}

	/**
	 * Năm lớn nhất chấp nhận.
	 *
	 * @return int
	 */
	public static function nam_max() {
		return (int) current_time( 'Y' );
	}

	/**
	 * Năm sinh có nằm trong khoảng hợp lệ không.
	 *
	 * @param mixed $nam Năm cần kiểm tra.
	 * @return bool
	 */
	public static function hop_le( $nam ) {
		$nam = (int) $nam;

		return $nam >= self::NAM_MIN && $nam <= self::nam_max();
	}

	/**
	 * Tra can chi và nạp âm của một năm.
	 *
	 * Cách tính: năm 4 sau Công nguyên là Giáp Tý, khởi đầu vòng 60 năm. Lấy
	 * ( năm − 4 ) chia lấy dư 60 ra vị trí trong vòng; chia lấy dư 10 và 12 ra
	 * can và chi. Mỗi nạp âm phủ hai năm liền nhau nên chỉ số nạp âm là vị trí
	 * chia đôi.
	 *
	 * @param int $nam Năm sinh dương lịch.
	 * @return array|null Mảng thông tin, hoặc null nếu năm không hợp lệ.
	 */
	public static function tra( $nam ) {
		$nam = (int) $nam;

		if ( ! self::hop_le( $nam ) ) {
			return null;
		}

		$bang = self::bang();

		$vi_tri = ( $nam - 4 ) % 60;
		$can    = $bang['can'][ $vi_tri % 10 ];
		$chi    = $bang['chi'][ $vi_tri % 12 ];
		$cap    = $bang['nap_am'][ intdiv( $vi_tri, 2 ) ];

		$goi_y = self::goi_y();
		$menh  = $cap[1];

		return array(
			'nam'      => $nam,
			'can_chi'  => $can . ' ' . $chi,
			'nap_am'   => $cap[0],
			'menh'     => $menh,
			'chi_tiet' => isset( $goi_y[ $menh ] ) ? $goi_y[ $menh ] : null,
		);
	}
}
