<?php
/**
 * Công cụ tra đá hợp mệnh: form nhập năm sinh và phần kết quả.
 *
 * Chạy hoàn toàn phía máy chủ, không dùng JavaScript. Người dùng bấm "Xem kết
 * quả", trang tải lại kèm tham số ?dql_nam=... và nhảy tới đúng khối công cụ.
 *
 * Vì sao không làm bằng JavaScript cho mượt: nó sẽ buộc phải viết logic tính
 * mệnh và dựng markup ở HAI nơi — một bản PHP cho người không bật JS và công cụ
 * tìm kiếm, một bản JS cho trình duyệt. Hai bản sao của cùng một logic là thứ
 * chắc chắn sẽ lệch nhau sau vài lần sửa. Một lần tải trang là cái giá rẻ hơn.
 *
 * @package dql-core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Công cụ tra mệnh.
 */
class DQL_Menh_Tool {

	const THAM_SO = 'dql_nam';
	const NEO     = 'dql-tra-menh';

	/**
	 * Gắn các hook.
	 */
	public static function init() {
		add_shortcode( 'dql_tra_menh', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Nạp CSS khi trang có dùng công cụ.
	 */
	public static function enqueue() {
		if ( ! self::trang_co_dung_cong_cu() ) {
			return;
		}

		$file = DQL_CORE_DIR . 'assets/css/menh.css';

		wp_enqueue_style(
			'dql-menh',
			DQL_CORE_URL . 'assets/css/menh.css',
			array(),
			file_exists( $file ) ? (string) filemtime( $file ) : DQL_CORE_VERSION
		);
	}

	/**
	 * Trang hiện tại có hiển thị công cụ không.
	 *
	 * Trang chủ luôn có (theme gọi trực tiếp). Các trang khác chỉ khi nội dung
	 * chứa shortcode.
	 *
	 * @return bool
	 */
	private static function trang_co_dung_cong_cu() {
		if ( is_front_page() ) {
			return true;
		}

		if ( ! is_singular() ) {
			return false;
		}

		$post = get_post();

		return $post && has_shortcode( $post->post_content, 'dql_tra_menh' );
	}

	/**
	 * Xử lý shortcode.
	 *
	 * @return string
	 */
	public static function shortcode() {
		ob_start();
		self::render();

		return (string) ob_get_clean();
	}

	/**
	 * Năm người dùng vừa nhập, nếu có và hợp lệ.
	 *
	 * @return int|null
	 */
	private static function nam_da_nhap() {
		// Chỉ đọc, không thay đổi gì trên máy chủ, nên không cần nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET[ self::THAM_SO ] ) ) {
			return null;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$nam = absint( wp_unslash( $_GET[ self::THAM_SO ] ) );

		return $nam ? $nam : 0;
	}

	/**
	 * In toàn bộ công cụ: form và kết quả nếu có.
	 */
	public static function render() {
		$nam     = self::nam_da_nhap();
		$ket_qua = null;
		$loi     = '';

		if ( null !== $nam ) {
			if ( DQL_Menh::hop_le( $nam ) ) {
				$ket_qua = DQL_Menh::tra( $nam );
			} else {
				$loi = sprintf(
					/* translators: 1: năm nhỏ nhất, 2: năm lớn nhất. */
					__( 'Vui lòng nhập năm sinh trong khoảng %1$d đến %2$d.', 'dql-core' ),
					DQL_Menh::NAM_MIN,
					DQL_Menh::nam_max()
				);
			}
		}
		?>
		<div class="dql-menh-tool" id="<?php echo esc_attr( self::NEO ); ?>">

			<form class="dql-menh-form" method="get" action="<?php echo esc_url( self::url_form() ); ?>">
				<?php self::in_truong_an(); ?>

				<label class="dql-menh-form__label" for="dql-menh-nam">
					<?php esc_html_e( 'Năm sinh dương lịch', 'dql-core' ); ?>
				</label>

				<div class="dql-menh-form__row">
					<input
						type="number"
						id="dql-menh-nam"
						name="<?php echo esc_attr( self::THAM_SO ); ?>"
						class="dql-menh-form__input"
						inputmode="numeric"
						placeholder="<?php echo esc_attr( '1990' ); ?>"
						min="<?php echo esc_attr( DQL_Menh::NAM_MIN ); ?>"
						max="<?php echo esc_attr( DQL_Menh::nam_max() ); ?>"
						value="<?php echo $nam ? esc_attr( $nam ) : ''; ?>"
						required
					/>
					<button type="submit" class="dql-menh-form__submit">
						<?php esc_html_e( 'Xem kết quả', 'dql-core' ); ?>
					</button>
				</div>
			</form>

			<?php if ( $loi ) : ?>
				<p class="dql-menh-loi" role="alert"><?php echo esc_html( $loi ); ?></p>
			<?php endif; ?>

			<?php
			if ( $ket_qua ) {
				self::in_ket_qua( $ket_qua );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Địa chỉ form gửi tới: chính trang hiện tại, kèm neo tới khối công cụ.
	 *
	 * Không có neo thì sau khi bấm nút, trang tải lại và nhảy về đầu — người
	 * dùng phải tự cuộn xuống tìm kết quả của chính mình.
	 *
	 * @return string
	 */
	private static function url_form() {
		$base = home_url( add_query_arg( array() ) );
		$base = remove_query_arg( self::THAM_SO, $base );

		return strtok( $base, '?' ) . '#' . self::NEO;
	}

	/**
	 * Giữ lại các tham số URL khác dưới dạng trường ẩn.
	 *
	 * Form dùng phương thức GET nên trình duyệt sẽ xóa sạch mọi tham số đang có
	 * trên URL khi gửi. Trang chủ không có tham số nào, nhưng trang chuyên mục
	 * hay trang phân trang thì có — mất chúng là người dùng bị đá về trang 1.
	 */
	private static function in_truong_an() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$params = $_GET;

		unset( $params[ self::THAM_SO ] );

		foreach ( $params as $key => $value ) {
			if ( is_array( $value ) ) {
				continue;
			}

			printf(
				'<input type="hidden" name="%s" value="%s" />',
				esc_attr( sanitize_key( $key ) ),
				esc_attr( sanitize_text_field( wp_unslash( $value ) ) )
			);
		}
	}

	/**
	 * In khối kết quả.
	 *
	 * Bố cục ba phần: một dòng tóm tắt (mệnh, can chi, liên kết đọc sâu), một
	 * lưới các khối thông tin, và dòng lưu ý cuối. Trước đây mọi thứ xếp dọc
	 * thành một cột duy nhất — trên màn hình rộng nó kéo khối kết quả dài gấp
	 * mấy lần phần giới thiệu bên cạnh, nhìn lệch hẳn một bên.
	 *
	 * @param array $kq Kết quả tra cứu.
	 */
	private static function in_ket_qua( $kq ) {
		$ct  = $kq['chi_tiet'];
		$bai = ( $ct && ! empty( $ct['bai_chuyen_sau'] ) ) ? get_page_by_path( $ct['bai_chuyen_sau'], OBJECT, 'post' ) : null;
		?>
		<div class="dql-menh-ketqua" role="status">

			<div class="dql-menh-ketqua__dau">
				<div class="dql-menh-ketqua__danh">
					<p class="dql-menh-ketqua__menh">
						<?php
						printf(
							/* translators: 1: năm sinh, 2: tên mệnh ngũ hành. */
							esc_html__( 'Sinh năm %1$s, mệnh %2$s', 'dql-core' ),
							'<span class="dql-menh-ketqua__nam">' . esc_html( $kq['nam'] ) . '</span>',
							'<span class="dql-menh-ketqua__ten">' . esc_html( $kq['menh'] ) . '</span>'
						);
						?>
					</p>

					<?php
					/*
					 * Nói rõ "năm âm lịch" ngay tại đây.
					 *
					 * Ô nhập hỏi năm dương lịch, còn dòng lưu ý cuối khối lại nói kết quả
					 * tính theo âm lịch — người đọc gặp hai câu đó mà không có gì nối ở
					 * giữa thì tưởng công cụ tự mâu thuẫn. Đây chính là chỗ nối: 1990 của
					 * bạn ứng với năm âm lịch Canh Ngọ, và mệnh đọc ra từ đó.
					 */
					?>
					<p class="dql-menh-ketqua__canchi">
						<?php
						printf(
							/* translators: 1: can chi, ví dụ "Canh Ngọ". 2: nạp âm, ví dụ "Lộ Bàng Thổ". */
							esc_html__( 'Năm âm lịch %1$s · nạp âm %2$s', 'dql-core' ),
							esc_html( $kq['can_chi'] ),
							esc_html( $kq['nap_am'] )
						);
						?>
					</p>
				</div>

				<?php if ( $bai ) : ?>
					<a class="dql-menh-bai" href="<?php echo esc_url( get_permalink( $bai ) ); ?>">
						<?php
						printf(
							/* translators: %s: tên mệnh. */
							esc_html__( 'Đọc bài phân tích đầy đủ cho mệnh %s', 'dql-core' ),
							esc_html( $kq['menh'] )
						);
						?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( $ct ) : ?>
				<div class="dql-menh-ketqua__luoi">

					<?php if ( ! empty( $ct['mau_hop'] ) || ! empty( $ct['mau_ky'] ) ) : ?>
						<?php /* Hai khối màu nằm chung một hộp để chúng dính vào nhau; nếu thả thẳng vào lưới, khối đá cao bên cạnh sẽ kéo giãn hàng và đẩy chúng ra xa. */ ?>
						<div class="dql-menh-ketqua__cot">
							<?php if ( ! empty( $ct['mau_hop'] ) ) : ?>
								<div class="dql-menh-khoi dql-menh-khoi--mau">
									<h4 class="dql-menh-khoi__tieude"><?php esc_html_e( 'Màu hợp', 'dql-core' ); ?></h4>
									<p class="dql-menh-mau"><?php echo esc_html( implode( ' · ', $ct['mau_hop'] ) ); ?></p>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $ct['mau_ky'] ) ) : ?>
								<div class="dql-menh-khoi dql-menh-khoi--ky">
									<h4 class="dql-menh-khoi__tieude"><?php esc_html_e( 'Màu nên tránh', 'dql-core' ); ?></h4>
									<p class="dql-menh-mau dql-menh-mau--ky"><?php echo esc_html( implode( ' · ', $ct['mau_ky'] ) ); ?></p>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $ct['da'] ) ) : ?>
						<div class="dql-menh-khoi dql-menh-khoi--da">
							<h4 class="dql-menh-khoi__tieude"><?php esc_html_e( 'Các loại đá thường được chọn', 'dql-core' ); ?></h4>
							<ul class="dql-menh-da">
								<?php foreach ( $ct['da'] as $da ) : ?>
									<li><?php self::in_ten_da( $da ); ?></li>
								<?php endforeach; ?>
							</ul>

							<?php
							/*
							 * Ghi chú biến thể đá.
							 *
							 * In thành dòng riêng bên dưới thay vì nhét vào thuộc tính title:
							 * title không hiện trên điện thoại, mà đây đúng là thông tin ngăn
							 * người đọc mua nhầm — ví dụ Garnet có nhiều màu, chỉ biến thể đỏ
							 * sẫm mới hợp mệnh Hỏa.
							 */
							$ghi_chu = array_filter(
								$ct['da'],
								function ( $d ) {
									return ! empty( $d['ghi_chu'] );
								}
							);

							if ( $ghi_chu ) :
								?>
								<ul class="dql-menh-ghichu">
									<?php foreach ( $ghi_chu as $d ) : ?>
										<li>
											<strong><?php echo esc_html( $d['ten'] ); ?></strong>
											<?php echo esc_html( $d['ghi_chu'] ); ?>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					<?php endif; ?>

				</div>
			<?php endif; ?>

			<?php
			/*
			 * Giải thích, không chỉ cảnh báo.
			 *
			 * Bản cũ chỉ nói trống không "kết quả tính theo năm âm lịch" ngay dưới một
			 * ô hỏi năm dương lịch. Người đọc không có cách nào biết đó là cố ý hay là
			 * lỗi. Nói ra lý do — mệnh gắn với can chi, can chi chạy theo âm lịch, công
			 * cụ tự quy đổi — thì trường hợp ngoại lệ ở câu cuối mới có chỗ đứng.
			 */
			?>
			<p class="dql-menh-luuy">
				<strong><?php esc_html_e( 'Vì sao nhập năm dương lịch mà kết quả tính theo âm lịch:', 'dql-core' ); ?></strong>
				<?php esc_html_e( 'mệnh ngũ hành đọc ra từ can chi, mà can chi chạy theo năm âm lịch — công cụ tự quy đổi giúp bạn. Hai lịch chỉ lệch nhau khoảng một tháng nên với hầu hết mọi người, nhập năm dương lịch là ra đúng năm âm lịch của mình. Riêng người sinh trong tháng 1 hoặc đầu tháng 2 dương lịch, trước Tết Nguyên đán, thì vẫn thuộc năm âm lịch liền trước — hãy nhập năm sinh trừ đi 1.', 'dql-core' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * In tên một loại đá, kèm liên kết nếu bài viết tồn tại.
	 *
	 * @param array $da Mảng có 'ten' và 'slug'.
	 */
	private static function in_ten_da( $da ) {
		$ten  = isset( $da['ten'] ) ? $da['ten'] : '';
		$slug = isset( $da['slug'] ) ? $da['slug'] : '';
		$mau  = isset( $da['mau'] ) ? $da['mau'] : '';

		if ( ! $ten ) {
			return;
		}

		// Biến thể màu, ví dụ "Đá Garnet · đỏ". Không phải loại đá nào cũng có.
		$nhan = esc_html( $ten );

		if ( $mau ) {
			$nhan .= ' <span class="dql-menh-da__mau">· ' . esc_html( $mau ) . '</span>';
		}

		$bai = $slug ? get_page_by_path( $slug, OBJECT, 'post' ) : null;

		if ( ! $bai ) {
			// Slug sai hoặc bài đã xóa: vẫn hiện tên, chỉ không có liên kết.
			echo $nhan; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Đã thoát từng phần ở trên.
			return;
		}

		printf(
			'<a href="%s">%s</a>',
			esc_url( get_permalink( $bai ) ),
			$nhan // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Đã thoát từng phần ở trên.
		);
	}
}

/**
 * Hàm theme gọi để in công cụ ra trang chủ.
 *
 * Theme kiểm tra function_exists() trước khi gọi, nên khi plugin bị tắt thì
 * khối tra mệnh trên trang chủ tự ẩn đi thay vì báo lỗi.
 */
function dql_core_menh_form() {
	DQL_Menh_Tool::render();
}
