<?php
/**
 * Bảng nạp âm 60 hoa giáp.
 *
 * Đây là dữ liệu chuẩn của lịch can chi, không phải quan điểm cá nhân — 60 năm
 * lặp lại một vòng, mỗi CẶP năm liền nhau mang một nạp âm và một hành.
 *
 * Thứ tự trong mảng chính là thứ tự vòng lặp, bắt đầu từ Giáp Tý. Không được
 * sắp xếp lại: mã tính toán lấy phần tử theo chỉ số.
 *
 * @package dql-core
 */

defined( 'ABSPATH' ) || exit;

return array(
	'can' => array( 'Giáp', 'Ất', 'Bính', 'Đinh', 'Mậu', 'Kỷ', 'Canh', 'Tân', 'Nhâm', 'Quý' ),
	'chi' => array( 'Tý', 'Sửu', 'Dần', 'Mão', 'Thìn', 'Tỵ', 'Ngọ', 'Mùi', 'Thân', 'Dậu', 'Tuất', 'Hợi' ),

	// 30 cặp, mỗi cặp phủ 2 năm liên tiếp.
	'nap_am' => array(
		array( 'Hải Trung Kim', 'Kim' ),   // Giáp Tý  – Ất Sửu
		array( 'Lư Trung Hỏa', 'Hỏa' ),    // Bính Dần – Đinh Mão
		array( 'Đại Lâm Mộc', 'Mộc' ),     // Mậu Thìn – Kỷ Tỵ
		array( 'Lộ Bàng Thổ', 'Thổ' ),     // Canh Ngọ – Tân Mùi
		array( 'Kiếm Phong Kim', 'Kim' ),  // Nhâm Thân – Quý Dậu
		array( 'Sơn Đầu Hỏa', 'Hỏa' ),     // Giáp Tuất – Ất Hợi
		array( 'Giản Hạ Thủy', 'Thủy' ),   // Bính Tý  – Đinh Sửu
		array( 'Thành Đầu Thổ', 'Thổ' ),   // Mậu Dần  – Kỷ Mão
		array( 'Bạch Lạp Kim', 'Kim' ),    // Canh Thìn – Tân Tỵ
		array( 'Dương Liễu Mộc', 'Mộc' ),  // Nhâm Ngọ – Quý Mùi
		array( 'Tuyền Trung Thủy', 'Thủy' ), // Giáp Thân – Ất Dậu
		array( 'Ốc Thượng Thổ', 'Thổ' ),   // Bính Tuất – Đinh Hợi
		array( 'Tích Lịch Hỏa', 'Hỏa' ),   // Mậu Tý   – Kỷ Sửu
		array( 'Tùng Bách Mộc', 'Mộc' ),   // Canh Dần – Tân Mão
		array( 'Trường Lưu Thủy', 'Thủy' ), // Nhâm Thìn – Quý Tỵ
		array( 'Sa Trung Kim', 'Kim' ),    // Giáp Ngọ – Ất Mùi
		array( 'Sơn Hạ Hỏa', 'Hỏa' ),      // Bính Thân – Đinh Dậu
		array( 'Bình Địa Mộc', 'Mộc' ),    // Mậu Tuất – Kỷ Hợi
		array( 'Bích Thượng Thổ', 'Thổ' ), // Canh Tý  – Tân Sửu
		array( 'Kim Bạch Kim', 'Kim' ),    // Nhâm Dần – Quý Mão
		array( 'Phú Đăng Hỏa', 'Hỏa' ),    // Giáp Thìn – Ất Tỵ
		array( 'Thiên Hà Thủy', 'Thủy' ),  // Bính Ngọ – Đinh Mùi
		array( 'Đại Trạch Thổ', 'Thổ' ),   // Mậu Thân – Kỷ Dậu
		array( 'Thoa Xuyến Kim', 'Kim' ),  // Canh Tuất – Tân Hợi
		array( 'Tang Đố Mộc', 'Mộc' ),     // Nhâm Tý  – Quý Sửu
		array( 'Đại Khê Thủy', 'Thủy' ),   // Giáp Dần – Ất Mão
		array( 'Sa Trung Thổ', 'Thổ' ),    // Bính Thìn – Đinh Tỵ
		array( 'Thiên Thượng Hỏa', 'Hỏa' ), // Mậu Ngọ – Kỷ Mùi
		array( 'Thạch Lựu Mộc', 'Mộc' ),   // Canh Thân – Tân Dậu
		array( 'Đại Hải Thủy', 'Thủy' ),   // Nhâm Tuất – Quý Hợi
	),
);
