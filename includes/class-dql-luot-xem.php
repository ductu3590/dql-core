<?php
/**
 * Đếm lượt xem bài viết.
 *
 * VÌ SAO ĐẾM BẰNG MỘT REQUEST RIÊNG, KHÔNG ĐẾM LÚC DỰNG TRANG
 *
 * Cách hay gặp nhất là tăng biến đếm ngay trong lúc PHP dựng trang. Cách đó hỏng
 * theo hai chiều ngược nhau, và không có chiều nào chấp nhận được:
 *
 *   - Có bộ nhớ đệm toàn trang: PHP không chạy ở lượt xem thứ hai trở đi, nên
 *     con số đứng im. Càng nhiều người đọc, số càng sai.
 *   - Tắt bộ nhớ đệm để đếm cho đúng: mỗi lượt tải trang thành một lệnh ghi
 *     database. Đây đúng là thứ đã bị loại từ đầu trong docs/thiet-ke.md của
 *     theme.
 *
 * Ở đây trang vẫn được đệm bình thường, còn việc đếm do một request nhỏ riêng
 * lo, gửi sau khi trang đã hiện xong. Người đọc không phải chờ nó, và bộ nhớ
 * đệm không bị đụng tới.
 *
 * Ba lớp chặn đếm trùng, từ rẻ tới đắt:
 *   1. sessionStorage ở trình duyệt — chặn bấm F5 liên tục, không tốn request.
 *   2. Khóa tạm phía máy chủ trong 6 giờ — chặn request gửi thẳng vào endpoint.
 *   3. Bỏ qua người có quyền sửa bài — chủ site đọc lại bài của mình không tính.
 *
 * @package dql-core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lượt xem bài viết.
 */
class DQL_Luot_Xem {

	const META      = '_dql_luot_xem';
	const NAMESPACE = 'dql/v1';
	const CACHE_KEY = 'dql_top_luot_xem';

	/**
	 * Một người xem lại cùng một bài trong bao lâu thì không tính thêm.
	 */
	const KHOA_GIAY = 6 * HOUR_IN_SECONDS;

	/**
	 * Gắn hook.
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'dang_ky_route' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );

		// Cột "Lượt xem" trong danh sách bài ở trang quản trị.
		add_filter( 'manage_post_posts_columns', array( __CLASS__, 'them_cot' ) );
		add_action( 'manage_post_posts_custom_column', array( __CLASS__, 'in_cot' ), 10, 2 );
		add_filter( 'manage_edit-post_sortable_columns', array( __CLASS__, 'cot_sap_xep' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'xu_ly_sap_xep' ) );

		// Xóa bảng xếp hạng đã nhớ tạm khi bài bị xóa hoặc đổi trạng thái.
		add_action( 'transition_post_status', array( __CLASS__, 'xoa_cache' ) );
		add_action( 'deleted_post', array( __CLASS__, 'xoa_cache' ) );
	}

	/* =======================================================================
	 * Ghi nhận lượt xem
	 * ===================================================================== */

	/**
	 * Đăng ký endpoint nhận lượt xem.
	 */
	public static function dang_ky_route() {
		register_rest_route(
			self::NAMESPACE,
			'/luot-xem/(?P<id>\d+)',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'nhan_luot_xem' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'sanitize_callback' => 'absint',
						'validate_callback' => function ( $value ) {
							return absint( $value ) > 0;
						},
					),
				),
			)
		);
	}

	/**
	 * Xử lý một lượt xem gửi lên.
	 *
	 * Luôn trả về 200, kể cả khi không đếm. Đây là một tín hiệu gửi đi rồi quên,
	 * trình duyệt không đọc kết quả — trả mã lỗi chỉ làm bẩn console của người
	 * đọc mà không sửa được gì.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function nhan_luot_xem( $request ) {
		$id = absint( $request['id'] );

		$tra_ve = function ( $da_dem ) {
			return new WP_REST_Response( array( 'counted' => (bool) $da_dem ), 200 );
		};

		if ( 'post' !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) ) {
			return $tra_ve( false );
		}

		// Chủ site đọc lại bài của mình không phải là một lượt đọc.
		if ( current_user_can( 'edit_posts' ) ) {
			return $tra_ve( false );
		}

		$khoa = self::khoa_nguoi_xem( $id );

		if ( get_transient( $khoa ) ) {
			return $tra_ve( false );
		}

		set_transient( $khoa, 1, self::KHOA_GIAY );

		// Cộng dồn ở tầng database, không đọc-rồi-ghi trong PHP. Hai người xem
		// cùng lúc mà đọc-rồi-ghi thì một lượt bị nuốt.
		global $wpdb;

		$so_dong = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = %s",
				$id,
				self::META
			)
		);

		if ( ! $so_dong ) {
			// Bài chưa có dòng meta nào. add_post_meta với $unique = true để hai
			// request cùng lúc không tạo ra hai dòng.
			add_post_meta( $id, self::META, 1, true );
		}

		wp_cache_delete( $id, 'post_meta' );

		/*
		 * Bài chưa nằm trong bảng xếp hạng đang nhớ tạm thì xóa bảng đó đi.
		 *
		 * Không có bước này, bảng xếp hạng chỉ đổi mỗi giờ một lần — nghe thì
		 * chấp nhận được, nhưng lúc site vừa bật tính năng thì bảng đang rỗng, và
		 * rỗng suốt một giờ kể cả khi lượt xem đã đổ về. Người sửa code sẽ đi tìm
		 * lỗi ở chỗ đếm, nơi không có lỗi nào. Đã sập bẫy này một lần.
		 *
		 * Chỉ xóa khi bài CHƯA có trong bảng. Bài đã nằm sẵn trong đó thì thứ
		 * hạng xê dịch một chút không đáng để chạy lại truy vấn nặng — mà đó lại
		 * là phần lớn lượt xem.
		 */
		$bang = get_transient( self::CACHE_KEY );

		if ( ! is_array( $bang ) || ! in_array( $id, $bang, true ) ) {
			self::xoa_cache();
		}

		return $tra_ve( true );
	}

	/**
	 * Khóa nhận diện một người xem cho một bài.
	 *
	 * Địa chỉ IP được băm một chiều cùng muối của site và KHÔNG được lưu lại ở
	 * dạng đọc được. Thứ nằm trong database chỉ là một chuỗi băm, tự hết hạn sau
	 * 6 giờ — đủ để chặn đếm trùng, không đủ để lần ra ai.
	 *
	 * @param int $id ID bài viết.
	 * @return string
	 */
	private static function khoa_nguoi_xem( $id ) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
			: '';

		return 'dql_lx_' . md5( $ip . '|' . $id . '|' . wp_salt( 'nonce' ) );
	}

	/**
	 * Nạp script gửi tín hiệu, chỉ ở trang bài viết đơn.
	 */
	public static function enqueue() {
		if ( ! is_singular( 'post' ) ) {
			return;
		}

		// Người có quyền sửa bài không được đếm, nên cũng không cần tải script.
		if ( current_user_can( 'edit_posts' ) ) {
			return;
		}

		$file = DQL_CORE_DIR . 'assets/js/luot-xem.js';

		wp_enqueue_script(
			'dql-luot-xem',
			DQL_CORE_URL . 'assets/js/luot-xem.js',
			array(),
			file_exists( $file ) ? (string) filemtime( $file ) : DQL_CORE_VERSION,
			true
		);

		wp_localize_script(
			'dql-luot-xem',
			'dqlLuotXem',
			array(
				'url' => rest_url( self::NAMESPACE . '/luot-xem/' . get_the_ID() ),
				'id'  => (string) get_the_ID(),
			)
		);
	}

	/* =======================================================================
	 * Đọc số liệu
	 * ===================================================================== */

	/**
	 * Số lượt xem của một bài.
	 *
	 * @param int|WP_Post|null $post Bài viết.
	 * @return int
	 */
	public static function so_luot( $post = null ) {
		$post = get_post( $post );

		return $post ? (int) get_post_meta( $post->ID, self::META, true ) : 0;
	}

	/**
	 * Các bài được đọc nhiều nhất.
	 *
	 * Trả về ID chứ không trả về WP_Post: nơi gọi thường cần chạy một WP_Query
	 * riêng để lấy đúng thứ tự và đúng bộ lọc của mình.
	 *
	 * Giới hạn đã biết: xếp hạng theo TỔNG lượt xem từ trước tới nay, nên bài cũ
	 * có lợi thế tích lũy. Với một blog đang lớn dần thì đây là đánh đổi đúng —
	 * xếp hạng theo cửa sổ 30 ngày cần lưu số liệu theo từng ngày cho từng bài,
	 * đắt hơn hẳn và chưa xứng ở quy mô này. Đổi cách xếp hạng bằng bộ lọc
	 * 'dql_top_luot_xem_args'.
	 *
	 * @param int $so_luong Số bài cần lấy.
	 * @return int[]
	 */
	public static function top( $so_luong = 5 ) {
		$so_luong = max( 1, (int) $so_luong );
		$cache    = get_transient( self::CACHE_KEY );

		if ( is_array( $cache ) ) {
			return array_slice( $cache, 0, $so_luong );
		}

		/*
		 * Lấy dư ra rồi cắt, để nhiều nơi gọi với số lượng khác nhau vẫn dùng
		 * chung một lần truy vấn.
		 */
		$args = apply_filters(
			'dql_top_luot_xem_args',
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => 20,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'fields'              => 'ids',
				'meta_key'            => self::META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'orderby'             => 'meta_value_num',
				'order'               => 'DESC',
			)
		);

		$q   = new WP_Query( $args );
		$ids = array_map( 'intval', $q->posts );

		/*
		 * Nhớ tạm một giờ. Truy vấn sắp xếp theo meta là loại nặng, và bảng xếp
		 * hạng bài đọc nhiều thì chậm một giờ cũng không ai nhận ra.
		 *
		 * Kết quả rỗng thì chỉ nhớ 5 phút. Lớp bảo vệ thứ hai cho trường hợp site
		 * vừa bật tính năng: nhan_luot_xem() đã tự xóa bảng khi có bài mới lọt
		 * vào, nhưng nếu vì lý do nào đó việc xóa không chạy thì cái rỗng cũng
		 * chỉ sống 5 phút chứ không phải một giờ.
		 */
		set_transient( self::CACHE_KEY, $ids, $ids ? HOUR_IN_SECONDS : 5 * MINUTE_IN_SECONDS );

		return array_slice( $ids, 0, $so_luong );
	}

	/**
	 * Xóa bảng xếp hạng đã nhớ tạm.
	 */
	public static function xoa_cache() {
		delete_transient( self::CACHE_KEY );
	}

	/* =======================================================================
	 * Cột trong trang quản trị
	 *
	 * Không có cột này thì việc đếm hoàn toàn vô hình: không có cách nào biết nó
	 * đang chạy hay đã hỏng từ tuần trước.
	 * ===================================================================== */

	/**
	 * Thêm cột "Lượt xem".
	 *
	 * @param array $cols Các cột đang có.
	 * @return array
	 */
	public static function them_cot( $cols ) {
		$cols['dql_luot_xem'] = __( 'Lượt xem', 'dql-core' );

		return $cols;
	}

	/**
	 * In nội dung cột.
	 *
	 * @param string $col Mã cột.
	 * @param int    $id  ID bài.
	 */
	public static function in_cot( $col, $id ) {
		if ( 'dql_luot_xem' !== $col ) {
			return;
		}

		echo esc_html( number_format_i18n( self::so_luot( $id ) ) );
	}

	/**
	 * Cho phép sắp xếp theo cột.
	 *
	 * @param array $cols Các cột sắp xếp được.
	 * @return array
	 */
	public static function cot_sap_xep( $cols ) {
		$cols['dql_luot_xem'] = 'dql_luot_xem';

		return $cols;
	}

	/**
	 * Thực hiện việc sắp xếp khi bấm vào tiêu đề cột.
	 *
	 * Khai báo cột sắp-xếp-được ở trên mới chỉ làm tiêu đề thành liên kết bấm
	 * được. Không có hàm này thì bấm vào không có gì xảy ra — WordPress không
	 * biết 'dql_luot_xem' ứng với trường nào.
	 *
	 * meta_query với 'NOT EXISTS' để bài chưa có lượt xem nào vẫn nằm trong danh
	 * sách, xếp cuối, thay vì biến mất.
	 *
	 * @param WP_Query $query Truy vấn.
	 */
	public static function xu_ly_sap_xep( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'dql_luot_xem' !== $query->get( 'orderby' ) ) {
			return;
		}

		$query->set( 'orderby', 'meta_value_num' );
		$query->set(
			'meta_query', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'relation' => 'OR',
				array(
					'key'     => self::META,
					'compare' => 'EXISTS',
				),
				array(
					'key'     => self::META,
					'compare' => 'NOT EXISTS',
				),
			)
		);
	}
}
