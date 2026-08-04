<?php
/**
 * Gợi ý theo từng mệnh: màu hợp, đá gợi ý, và bài viết chuyên sâu.
 *
 * ====================================================================
 *  FILE NÀY LÀ QUAN ĐIỂM CHUYÊN MÔN CỦA CHỦ SITE, KHÔNG PHẢI DỮ LIỆU
 *  CHUẨN NHƯ BẢNG NẠP ÂM. HÃY ĐỌC LẠI VÀ SỬA THEO Ý BẠN.
 * ====================================================================
 *
 * Phần tính mệnh từ năm sinh là toán học thuần túy, chạy sao cũng ra một kết
 * quả. Nhưng "mệnh này hợp đá nào" thì không — nó là kiến thức phong thủy, và
 * người đọc tin vào đó để bỏ tiền mua đá. Vì vậy:
 *
 *   - Danh sách đá dưới đây dựa trên quy tắc ngũ hành tương sinh phổ biến
 *     (hành sinh ra mệnh, và chính hành của mệnh), khớp với màu sắc quy ước.
 *   - Mỗi loại đá chỉ được liệt kê khi site ĐÃ CÓ bài viết về nó. Người đọc bấm
 *     vào là sang bài của bạn, nơi có kiến thức thật, chứ không dừng lại ở một
 *     cái tên trống rỗng.
 *   - Nếu bạn không đồng ý với gợi ý nào, xóa hoặc sửa ngay tại đây. Công cụ
 *     chỉ đọc file này, không có danh sách nào giấu ở chỗ khác.
 *
 * Khóa 'bai_chuyen_sau' trỏ tới bài phân tích riêng cho mệnh đó. Đây mới là
 * đích đến quan trọng nhất — công cụ chỉ làm nhiệm vụ dẫn người đọc tới đó.
 *
 * Trường 'slug' của mỗi viên đá là đường dẫn bài viết. Để trống thì công cụ
 * hiện tên đá mà không tạo liên kết. Slug trỏ tới bài không tồn tại cũng được
 * xử lý an toàn: tên đá vẫn hiện, chỉ là không có liên kết.
 *
 * --- Cách tính màu tự động ---
 *
 * Thay vì gõ tay mau_hop cho từng mệnh (dễ lệch), file này dùng 3 bảng gốc:
 *   $MAU_NGU_HANH — màu quy ước của mỗi hành.
 *   $SINH          — hành nào sinh ra hành nào.
 *   $KHAC          — hành nào khắc hành nào.
 *
 * Code loop qua 5 mệnh, tự suy ra:
 *   mau_ban_menh   — màu chính hành mệnh (mạnh nhất).
 *   mau_tuong_sinh — màu hành sinh ra mệnh (hỗ trợ).
 *   mau_hop        — gộp 2 mảng trên, giữ nguyên key cũ để frontend không vỡ.
 *   mau_ky         — màu hành khắc mệnh (nên tránh).
 *
 * Sửa một chỗ trong $MAU_NGU_HANH, cả 5 mệnh tự cập nhật đồng bộ.
 *
 * @package dql-core
 */

defined( 'ABSPATH' ) || exit;

/*
|--------------------------------------------------------------------------
| Bảng gốc — sửa ở đây, 5 mệnh tự cập nhật
|--------------------------------------------------------------------------
*/

// Màu quy ước cho mỗi hành.
$MAU_NGU_HANH = array(
	'Kim'  => array( 'Trắng', 'Xám', 'Ánh kim' ),
	'Mộc'  => array( 'Xanh lá', 'Xanh lục' ),
	'Thủy' => array( 'Đen', 'Xanh dương' ),
	'Hỏa'  => array( 'Đỏ', 'Hồng', 'Tím' ),
	'Thổ'  => array( 'Vàng', 'Nâu', 'Vàng đất' ),
);

// Hành sinh: key được sinh bởi value. (Thổ sinh Kim, Thủy sinh Mộc, …)
$SINH = array(
	'Kim'  => 'Thổ',
	'Mộc'  => 'Thủy',
	'Thủy' => 'Kim',
	'Hỏa'  => 'Mộc',
	'Thổ'  => 'Hỏa',
);

// Hành khắc: key bị khắc bởi value. (Hỏa khắc Kim, Kim khắc Mộc, …)
$KHAC = array(
	'Kim'  => 'Hỏa',
	'Mộc'  => 'Kim',
	'Thủy' => 'Thổ',
	'Hỏa'  => 'Thủy',
	'Thổ'  => 'Mộc',
);

/*
|--------------------------------------------------------------------------
| Dữ liệu riêng từng mệnh — chỉ cần ghi bai_chuyen_sau + danh sách đá
|--------------------------------------------------------------------------
*/

$du_lieu_menh = array(

	'Kim' => array(
		'bai_chuyen_sau' => 'nguoi-menh-kim-hop-da-gi',
		'da'             => array(
			array( 'ten' => 'Kim cương', 'slug' => 'kim-cuong-la-gi' ),
			array( 'ten' => 'Đá mặt trăng', 'slug' => 'da-mat-trang-va-y-nghia-phong-thuy' ),
			array( 'ten' => 'Thạch anh trắng', 'slug' => 'thach-anh' ),
			array( 'ten' => 'Đá Labradorite', 'slug' => 'da-labradorite-la-gi' ),
			array(
				'ten'     => 'Đá Onyx trắng',
				'slug'    => 'da-onyx-la-gi',
				'ghi_chu' => 'Onyx trắng (White Onyx) — khác với Onyx đen truyền thống thường thấy trong các bài viết. Bài viết trên site đề cập Onyx nói chung; biến thể trắng được chọn ở đây vì phù hợp màu hành Kim.',
			),
		),
	),

	'Mộc' => array(
		'bai_chuyen_sau' => 'menh-moc-hop-da-gi',
		'da'             => array(
			array( 'ten' => 'Đá Emerald', 'slug' => 'da-emerald-la-gi' ),
			array( 'ten' => 'Ngọc bích Nephrite', 'slug' => 'ngoc-bich-nephrite-jade-la-gi' ),
			array( 'ten' => 'Đá Peridot', 'slug' => 'da-peridot-la-gi' ),
			array( 'ten' => 'Đá Malachite', 'slug' => 'da-malachite-la-gi' ),
			array( 'ten' => 'Đá Prehnite', 'slug' => 'da-prehnite-la-gi' ),
		),
	),

	'Thủy' => array(
		'bai_chuyen_sau' => 'nguoi-menh-thuy-hop-da-gi',
		'da'             => array(
			array( 'ten' => 'Đá Sapphire', 'slug' => 'da-sapphire' ),
			array( 'ten' => 'Đá Aquamarine', 'slug' => 'aquamarine' ),
			array( 'ten' => 'Đá Lapis Lazuli', 'slug' => 'da-lapis-lazuli-la-gi' ),
			array( 'ten' => 'Đá Obsidian', 'slug' => 'da-obsidian-la-gi-ban-da-biet-gi-ve-obsidian' ),
			array( 'ten' => 'Đá Turquoise', 'slug' => 'da-turquoise-la-gi' ),
		),
	),

	'Hỏa' => array(
		'bai_chuyen_sau' => 'nguoi-menh-hoa-hop-da-gi',
		'da'             => array(
			array( 'ten' => 'Đá Ruby', 'slug' => 'da-ruby' ),
			array(
				'ten'     => 'Đá Garnet',
				'slug'    => 'garnet',
				'mau'     => 'đỏ',
				'ghi_chu' => 'Garnet có nhiều màu; phong thủy Hỏa chọn biến thể đỏ sẫm (Almandine/Pyrope).',
			),
			array(
				'ten'     => 'Đá Spinel',
				'slug'    => 'da-spinel',
				'mau'     => 'đỏ',
				'ghi_chu' => 'Spinel đỏ (gần Ruby), phù hợp hành Hỏa.',
			),
			array(
				'ten'     => 'Đá Tourmaline',
				'slug'    => 'da-tourmaline',
				'mau'     => 'hồng/đỏ',
				'ghi_chu' => 'Tourmaline đa sắc; ở đây chọn biến thể hồng-đỏ (Rubellite) hợp hành Hỏa.',
			),
			array( 'ten' => 'Đá Rhodochrosite', 'slug' => 'da-dao-hoa-rhodochrosite-la-gi' ),
		),
	),

	'Thổ' => array(
		'bai_chuyen_sau' => 'nguoi-menh-tho-hop-da-gi',
		'da'             => array(
			array( 'ten' => 'Đá Citrine', 'slug' => 'da-citrine' ),
			array(
				'ten'     => 'Đá mắt hổ',
				'slug'    => 'da-mat-ho',
				'mau'     => 'vàng nâu',
				'ghi_chu' => 'Tiger\'s Eye đặc trưng sắc vàng nâu, phù hợp hành Thổ.',
			),
			array( 'ten' => 'Hổ phách Amber', 'slug' => 'da-ho-phach-amber-la-gi' ),
			array(
				'ten'     => 'Thạch anh tóc',
				'slug'    => 'thach-anh-toc',
				'mau'     => 'vàng',
				'ghi_chu' => 'Rutilated Quartz biến thể vàng (tóc vàng/bạch kim), hợp hành Thổ.',
			),
			array( 'ten' => 'Đá Topaz vàng', 'slug' => 'da-topaz' ),
		),
	),
);

/*
|--------------------------------------------------------------------------
| Tự tính màu từ bảng gốc — KHÔNG sửa đoạn này
|--------------------------------------------------------------------------
*/

$ket_qua = array();

foreach ( $du_lieu_menh as $menh => $data ) {
	$mau_ban_menh   = $MAU_NGU_HANH[ $menh ];
	$mau_tuong_sinh = $MAU_NGU_HANH[ $SINH[ $menh ] ];
	$mau_ky         = $MAU_NGU_HANH[ $KHAC[ $menh ] ];

	$ket_qua[ $menh ] = array(
		'sinh_ra_boi'    => $SINH[ $menh ],
		'khac_boi'       => $KHAC[ $menh ],
		'mau_ban_menh'   => $mau_ban_menh,
		'mau_tuong_sinh' => $mau_tuong_sinh,
		'mau_hop'        => array_merge( $mau_ban_menh, $mau_tuong_sinh ),
		'mau_ky'         => $mau_ky,
		'bai_chuyen_sau' => $data['bai_chuyen_sau'],
		'da'             => $data['da'],
	);
}

return $ket_qua;
