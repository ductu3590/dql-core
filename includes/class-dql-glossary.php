<?php
/**
 * Từ điển thuật ngữ đá quý.
 *
 * Hai phần:
 *   1. Loại nội dung "Từ điển" — mỗi thuật ngữ là một mục, sửa được trong admin.
 *   2. Tự nhận diện thuật ngữ trong bài viết, gắn liên kết và giải nghĩa.
 *
 * @package dql-core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Từ điển thuật ngữ.
 */
class DQL_Glossary {

	const CPT       = 'dql_thuat_ngu';
	const META      = '_dql_dong_nghia';
	const CACHE_KEY = 'dql_glossary_index';

	/**
	 * Số thuật ngữ tối đa được đánh dấu trong một bài.
	 *
	 * Không giới hạn thì một bài dài về thạch anh sẽ có hàng chục từ gạch chân,
	 * biến bài viết thành bãi mìn liên kết và làm người đọc mất tập trung. Mỗi
	 * thuật ngữ cũng chỉ được đánh dấu ở lần xuất hiện ĐẦU TIÊN.
	 */
	const TOI_DA_MOI_BAI = 8;

	/**
	 * Gắn hook.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'dang_ky_cpt' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'them_o_dong_nghia' ) );
		add_action( 'save_post_' . self::CPT, array( __CLASS__, 'luu_dong_nghia' ) );

		// Xóa bộ nhớ đệm khi thuật ngữ thay đổi.
		add_action( 'save_post_' . self::CPT, array( __CLASS__, 'xoa_cache' ) );
		add_action( 'deleted_post', array( __CLASS__, 'xoa_cache' ) );

		// Chạy sau mục lục (ưu tiên 20) để không đụng vào việc gắn id tiêu đề.
		add_filter( 'the_content', array( __CLASS__, 'danh_dau_thuat_ngu' ), 30 );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Đăng ký loại nội dung Từ điển.
	 */
	public static function dang_ky_cpt() {
		register_post_type(
			self::CPT,
			array(
				'labels'        => array(
					'name'               => __( 'Từ điển', 'dql-core' ),
					'singular_name'      => __( 'Thuật ngữ', 'dql-core' ),
					'add_new'            => __( 'Thêm thuật ngữ', 'dql-core' ),
					'add_new_item'       => __( 'Thêm thuật ngữ mới', 'dql-core' ),
					'edit_item'          => __( 'Sửa thuật ngữ', 'dql-core' ),
					'search_items'       => __( 'Tìm thuật ngữ', 'dql-core' ),
					'not_found'          => __( 'Chưa có thuật ngữ nào', 'dql-core' ),
					'menu_name'          => __( 'Từ điển', 'dql-core' ),
				),
				'public'        => true,
				'has_archive'   => 'tu-dien',
				'rewrite'       => array( 'slug' => 'tu-dien' ),
				'menu_icon'     => 'dashicons-book-alt',
				'menu_position' => 21,
				'supports'      => array( 'title', 'editor', 'revisions' ),
				'show_in_rest'  => true,
			)
		);
	}

	/**
	 * Ô nhập từ đồng nghĩa trong màn hình sửa thuật ngữ.
	 */
	public static function them_o_dong_nghia() {
		add_meta_box(
			'dql-dong-nghia',
			__( 'Cách viết khác', 'dql-core' ),
			array( __CLASS__, 'in_o_dong_nghia' ),
			self::CPT,
			'side'
		);
	}

	/**
	 * In ô nhập từ đồng nghĩa.
	 *
	 * @param WP_Post $post Bài đang sửa.
	 */
	public static function in_o_dong_nghia( $post ) {
		$gia_tri = get_post_meta( $post->ID, self::META, true );

		wp_nonce_field( 'dql_dong_nghia', 'dql_dong_nghia_nonce' );
		?>
		<p>
			<label for="dql-dong-nghia-input">
				<?php esc_html_e( 'Các cách viết khác cùng nghĩa, ngăn cách bằng dấu phẩy.', 'dql-core' ); ?>
			</label>
		</p>
		<textarea
			id="dql-dong-nghia-input"
			name="dql_dong_nghia"
			rows="3"
			style="width:100%"
			placeholder="<?php esc_attr_e( 'tỷ trọng, trọng lượng riêng', 'dql-core' ); ?>"
		><?php echo esc_textarea( $gia_tri ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Bài viết dùng cách viết nào trong số này cũng sẽ được nhận ra.', 'dql-core' ); ?>
		</p>
		<?php
	}

	/**
	 * Lưu từ đồng nghĩa.
	 *
	 * @param int $post_id ID bài.
	 */
	public static function luu_dong_nghia( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['dql_dong_nghia_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['dql_dong_nghia_nonce'] ) );

		if ( ! wp_verify_nonce( $nonce, 'dql_dong_nghia' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$gia_tri = isset( $_POST['dql_dong_nghia'] )
			? sanitize_text_field( wp_unslash( $_POST['dql_dong_nghia'] ) )
			: '';

		update_post_meta( $post_id, self::META, $gia_tri );
	}

	/**
	 * Xóa bộ nhớ đệm chỉ mục thuật ngữ.
	 */
	public static function xoa_cache() {
		delete_transient( self::CACHE_KEY );
	}

	/**
	 * Chỉ mục thuật ngữ: mọi cách viết → thông tin thuật ngữ.
	 *
	 * Có nhớ tạm vì hàm này chạy trên mọi lần xem bài viết. Bộ nhớ đệm bị xóa
	 * mỗi khi có thuật ngữ được lưu hoặc xóa.
	 *
	 * @return array
	 */
	public static function chi_muc() {
		$cache = get_transient( self::CACHE_KEY );

		if ( is_array( $cache ) ) {
			return $cache;
		}

		$posts = get_posts(
			array(
				'post_type'      => self::CPT,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$chi_muc = array();

		foreach ( $posts as $p ) {
			$thong_tin = array(
				'id'    => $p->ID,
				'tu'    => $p->post_title,
				'link'  => get_permalink( $p ),
				'nghia' => wp_trim_words( wp_strip_all_tags( $p->post_content ), 32, '…' ),
			);

			$cach_viet = array( $p->post_title );
			$dong_nghia = (string) get_post_meta( $p->ID, self::META, true );

			foreach ( explode( ',', $dong_nghia ) as $dn ) {
				$dn = trim( $dn );

				if ( '' !== $dn ) {
					$cach_viet[] = $dn;
				}
			}

			foreach ( $cach_viet as $cv ) {
				// Khóa chuẩn hóa để "Mohs" và "mohs" gộp làm một.
				$chi_muc[ mb_strtolower( $cv, 'UTF-8' ) ] = $thong_tin;
			}
		}

		set_transient( self::CACHE_KEY, $chi_muc, DAY_IN_SECONDS );

		return $chi_muc;
	}

	/**
	 * Nạp CSS khi trang có thể chứa thuật ngữ được đánh dấu.
	 */
	public static function enqueue() {
		if ( ! is_singular( 'post' ) && ! is_singular( self::CPT ) && ! is_post_type_archive( self::CPT ) ) {
			return;
		}

		$file = DQL_CORE_DIR . 'assets/css/glossary.css';

		wp_enqueue_style(
			'dql-glossary',
			DQL_CORE_URL . 'assets/css/glossary.css',
			array(),
			file_exists( $file ) ? (string) filemtime( $file ) : DQL_CORE_VERSION
		);
	}

	/**
	 * Đánh dấu thuật ngữ trong nội dung bài viết.
	 *
	 * Dùng DOMDocument chứ không dùng biểu thức chính quy trên chuỗi HTML: regex
	 * không phân biệt được đâu là chữ hiển thị, đâu là thuộc tính thẻ. Thay bằng
	 * regex sẽ có ngày một thuật ngữ trùng với chữ trong href và phá vỡ liên kết.
	 *
	 * @param string $content Nội dung bài.
	 * @return string
	 */
	public static function danh_dau_thuat_ngu( $content ) {
		if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		if ( '' === trim( $content ) ) {
			return $content;
		}

		$chi_muc = self::chi_muc();

		if ( ! $chi_muc ) {
			return $content;
		}

		// Cách viết dài đứng trước, để "Độ cứng Mohs" được nhận trước "Mohs".
		$cach_viet = array_keys( $chi_muc );
		usort(
			$cach_viet,
			function ( $a, $b ) {
				return mb_strlen( $b, 'UTF-8' ) - mb_strlen( $a, 'UTF-8' );
			}
		);

		$mau = '/(?<![\p{L}\p{N}])('
			. implode( '|', array_map( 'preg_quote', $cach_viet ) )
			. ')(?![\p{L}\p{N}])/iu';

		$dom = new DOMDocument();
		$cu  = libxml_use_internal_errors( true );

		$nap = $dom->loadHTML(
			'<?xml encoding="UTF-8">' . '<div id="dql-root">' . $content . '</div>',
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);

		libxml_clear_errors();
		libxml_use_internal_errors( $cu );

		if ( ! $nap ) {
			return $content;
		}

		$xpath = new DOMXPath( $dom );

		// Bỏ qua chữ nằm trong liên kết, tiêu đề, mã nguồn, và trong chính khối
		// thuật ngữ đã đánh dấu — tránh lồng nhau.
		$loai_tru = 'ancestor::a or ancestor::h1 or ancestor::h2 or ancestor::h3'
			. ' or ancestor::h4 or ancestor::h5 or ancestor::h6'
			. ' or ancestor::code or ancestor::pre or ancestor::script or ancestor::style'
			. ' or ancestor::figcaption'
			. ' or ancestor::*[contains(concat(" ", normalize-space(@class), " "), " dql-tn ")]';

		$nodes = $xpath->query( '//text()[not(' . $loai_tru . ')]' );

		if ( ! $nodes ) {
			return $content;
		}

		$da_dung = array();
		$so_luot = 0;

		foreach ( $nodes as $node ) {
			if ( $so_luot >= self::TOI_DA_MOI_BAI ) {
				break;
			}

			$moi = self::xu_ly_mot_doan( $node->nodeValue, $mau, $chi_muc, $da_dung, $so_luot );

			if ( null === $moi ) {
				continue;
			}

			$frag = $dom->createDocumentFragment();

			if ( ! @$frag->appendXML( $moi ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				continue;
			}

			$node->parentNode->replaceChild( $frag, $node );
		}

		$root = $dom->getElementById( 'dql-root' );

		if ( ! $root ) {
			return $content;
		}

		$ket_qua = '';

		foreach ( $root->childNodes as $con ) {
			$ket_qua .= $dom->saveHTML( $con );
		}

		return $ket_qua;
	}

	/**
	 * Xử lý một đoạn văn bản thuần, trả về HTML mới hoặc null nếu không đổi.
	 *
	 * @param string $text    Văn bản gốc.
	 * @param string $mau     Biểu thức chính quy khớp thuật ngữ.
	 * @param array  $chi_muc Chỉ mục thuật ngữ.
	 * @param array  $da_dung Các thuật ngữ đã đánh dấu, truyền theo tham chiếu.
	 * @param int    $so_luot Số lần đã đánh dấu, truyền theo tham chiếu.
	 * @return string|null
	 */
	private static function xu_ly_mot_doan( $text, $mau, $chi_muc, &$da_dung, &$so_luot ) {
		if ( ! preg_match( $mau, $text ) ) {
			return null;
		}

		$ra      = '';
		$vi_tri  = 0;
		$doi     = false;

		while ( $so_luot < self::TOI_DA_MOI_BAI
			&& preg_match( $mau, $text, $m, PREG_OFFSET_CAPTURE, $vi_tri ) ) {

			$tu   = $m[1][0];
			$bat  = $m[1][1];
			$khoa = mb_strtolower( $tu, 'UTF-8' );

			/*
			 * Chống trùng theo ID THUẬT NGỮ, không theo cách viết.
			 *
			 * Lỗi đã mắc: một bài đánh dấu cả "độ cứng Mohs" lẫn "Mohs", và cả
			 * "độ trong" lẫn "độ tinh khiết" — đó là cùng một thuật ngữ với hai
			 * cách viết, nên người đọc thấy cùng một khái niệm được giải nghĩa
			 * hai lần trong cùng một bài.
			 */
			$tt = isset( $chi_muc[ $khoa ] ) ? $chi_muc[ $khoa ] : null;

			if ( ! $tt || isset( $da_dung[ $tt['id'] ] ) ) {
				$ra    .= self::thoat( substr( $text, $vi_tri, $bat + strlen( $tu ) - $vi_tri ) );
				$vi_tri = $bat + strlen( $tu );
				continue;
			}

			$ra .= self::thoat( substr( $text, $vi_tri, $bat - $vi_tri ) );
			$ra .= sprintf(
				'<span class="dql-tn"><a class="dql-tn__tu" href="%s">%s</a>'
				. '<span class="dql-tn__giai" role="tooltip"><strong>%s</strong> %s</span></span>',
				esc_url( $tt['link'] ),
				self::thoat( $tu ),
				self::thoat( $tt['tu'] ),
				self::thoat( $tt['nghia'] )
			);

			$da_dung[ $tt['id'] ] = true;
			$so_luot++;
			$doi    = true;
			$vi_tri = $bat + strlen( $tu );
		}

		if ( ! $doi ) {
			return null;
		}

		$ra .= self::thoat( substr( $text, $vi_tri ) );

		return $ra;
	}

	/**
	 * Thoát ký tự cho nội dung nằm trong XML fragment.
	 *
	 * appendXML() yêu cầu XML hợp lệ, nên phải thoát cả dấu nháy — nếu không một
	 * dấu & trong bài viết sẽ làm hỏng cả đoạn.
	 *
	 * @param string $s Chuỗi cần thoát.
	 * @return string
	 */
	private static function thoat( $s ) {
		return htmlspecialchars( $s, ENT_XML1 | ENT_QUOTES, 'UTF-8' );
	}
}
