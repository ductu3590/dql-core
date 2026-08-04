<?php
/**
 * Bộ thuật ngữ khởi tạo cho từ điển.
 *
 * ====================================================================
 *  ĐỌC LẠI VÀ SỬA TRƯỚC KHI CÔNG BỐ.
 *  Định nghĩa dưới đây là kiến thức đá quý phổ thông, viết ngắn gọn cho
 *  người mới. Bạn là người có chuyên môn — hãy sửa cho đúng với cách bạn
 *  vẫn giải thích cho khách.
 * ====================================================================
 *
 * File này CHỈ dùng một lần để nhập dữ liệu ban đầu. Sau khi nhập, mỗi thuật
 * ngữ là một bài trong mục "Từ điển" ở trang quản trị — sửa ở đó, không sửa ở
 * đây nữa. Chạy lại việc nhập cũng không tạo bản trùng: script bỏ qua thuật ngữ
 * đã tồn tại.
 *
 * Danh sách được chọn theo tần suất xuất hiện thật trong 79 bài viết của site,
 * không phải theo cảm tính. Thuật ngữ không bài nào nhắc tới thì không đưa vào.
 *
 * Khóa 'dong_nghia' là các cách viết khác cùng nghĩa. Công cụ sẽ nhận ra chúng
 * trong bài viết và gắn cùng một giải nghĩa — ví dụ "tỉ trọng" và "tỷ trọng".
 *
 * @package dql-core
 */

defined( 'ABSPATH' ) || exit;

return array(

	array(
		'tu'         => 'Độ cứng Mohs',
		'dong_nghia' => array( 'thang Mohs', 'Mohs' ),
		'nghia'      => 'Thang đo khả năng chống trầy xước của khoáng vật, từ 1 (mềm nhất, như bột talc) đến 10 (cứng nhất, là kim cương). Đá có độ cứng cao hơn sẽ làm xước đá có độ cứng thấp hơn. Đá dùng làm trang sức đeo hằng ngày nên đạt từ 7 trở lên.',
	),

	array(
		'tu'         => 'Chiết suất',
		'dong_nghia' => array( 'chỉ số khúc xạ' ),
		'nghia'      => 'Mức độ ánh sáng bị bẻ cong khi đi vào viên đá. Mỗi loại đá có khoảng chiết suất riêng, nên đây là một trong những chỉ số đáng tin để phân biệt các loại đá trông giống nhau bằng mắt thường.',
	),

	array(
		'tu'         => 'Tỉ trọng',
		'dong_nghia' => array( 'tỷ trọng', 'trọng lượng riêng' ),
		'nghia'      => 'Khối lượng của viên đá so với khối lượng nước cùng thể tích. Hai viên đá trông giống hệt nhau nhưng tỉ trọng khác nhau thì chắc chắn không cùng loại — vì vậy đây là phép thử đơn giản mà hiệu quả.',
	),

	array(
		'tu'         => 'Carat',
		// Không đưa "ct" vào: viết tắt hai ký tự sẽ khớp bừa với chữ khác.
		'dong_nghia' => array(),
		'nghia'      => 'Đơn vị khối lượng của đá quý, 1 carat bằng 0,2 gam. Đừng nhầm với "karat" dùng cho vàng — karat đo độ tinh khiết của vàng, không đo khối lượng.',
	),

	array(
		'tu'         => 'Corundum',
		'dong_nghia' => array( 'corindon' ),
		'nghia'      => 'Khoáng vật nhôm oxit, độ cứng 9 trên thang Mohs. Đây là "họ" của hai loại đá quý nổi tiếng nhất: corundum màu đỏ gọi là ruby, mọi màu còn lại gọi là sapphire.',
	),

	array(
		'tu'         => 'Tạp chất',
		'dong_nghia' => array( 'bao thể', 'inclusion' ),
		'nghia'      => 'Vật chất lẫn bên trong viên đá, hình thành cùng lúc với đá trong lòng đất. Nhiều người mới coi tạp chất là khuyết điểm, nhưng chúng thường là bằng chứng cho thấy đá có nguồn gốc tự nhiên — đá nhân tạo thường trong vắt bất thường.',
	),

	array(
		'tu'         => 'Hệ tinh thể',
		'dong_nghia' => array(),
		'nghia'      => 'Cách các nguyên tử sắp xếp có quy luật bên trong khoáng vật, quyết định hình dạng tinh thể khi đá phát triển tự do. Có bảy hệ, ví dụ ruby và sapphire thuộc hệ ba phương.',
	),

	array(
		'tu'         => 'Hiệu ứng sao',
		'dong_nghia' => array( 'sao sáu cánh', 'asterism' ),
		'nghia'      => 'Vệt sáng hình ngôi sao hiện trên mặt đá khi có ánh sáng chiếu vào, do các sợi tạp chất mảnh nằm song song phản chiếu ánh sáng. Thường thấy ở ruby sao và sapphire sao, và chỉ hiện rõ khi đá được mài dạng vòm.',
	),

	array(
		'tu'         => 'Mắt mèo',
		'dong_nghia' => array( 'hiệu ứng mắt mèo', 'chatoyancy' ),
		'nghia'      => 'Vệt sáng hẹp chạy dọc mặt đá và di chuyển khi xoay viên đá, trông như con ngươi mắt mèo. Nguyên nhân giống hiệu ứng sao nhưng các sợi tạp chất chỉ nằm song song theo một hướng.',
	),

	array(
		'tu'         => 'Rutile',
		'dong_nghia' => array( 'sợi rutile' ),
		'nghia'      => 'Khoáng vật titan dioxit, thường nằm trong đá khác dưới dạng sợi mảnh như tơ. Chính các sợi rutile tạo ra hiệu ứng sao và hiệu ứng mắt mèo, và sự có mặt của chúng là dấu hiệu đá tự nhiên.',
	),

	array(
		'tu'         => 'Xử lý nhiệt',
		'dong_nghia' => array( 'nung nhiệt', 'nhiệt luyện' ),
		'nghia'      => 'Nung đá ở nhiệt độ cao để làm màu đậm hơn hoặc trong hơn. Đây là cách xử lý được chấp nhận rộng rãi trong ngành, nhưng đá đã xử lý có giá thấp hơn đá tự nhiên không xử lý, nên người bán phải công bố rõ.',
	),

	array(
		'tu'         => 'Đá tổng hợp',
		'dong_nghia' => array( 'đá nhân tạo', 'đá nuôi' ),
		'nghia'      => 'Đá được tạo ra trong phòng thí nghiệm, có thành phần hóa học và cấu trúc giống hệt đá tự nhiên. Khác với đá giả (thủy tinh, nhựa) ở chỗ nó là đá thật — chỉ khác nơi hình thành, và vì vậy giá thấp hơn nhiều.',
	),

	array(
		'tu'         => 'Kiểm định',
		'dong_nghia' => array( 'giám định đá quý' ),
		'nghia'      => 'Quá trình xác định loại đá, nguồn gốc tự nhiên hay tổng hợp, và các phương pháp xử lý đã dùng. Giấy kiểm định chỉ có giá trị khi do đơn vị độc lập cấp — giấy do chính người bán tự in không chứng minh được điều gì.',
	),

	array(
		'tu'         => 'Đa sắc',
		'dong_nghia' => array( 'lưỡng sắc', 'pleochroism' ),
		'nghia'      => 'Hiện tượng viên đá cho thấy màu khác nhau khi nhìn từ các hướng khác nhau. Đây là đặc tính của nhiều loại đá tự nhiên và là một cách phân biệt với thủy tinh nhuộm màu, vốn cho cùng một màu ở mọi hướng.',
	),

	array(
		'tu'         => 'Huỳnh quang',
		'dong_nghia' => array( 'phát quang' ),
		'nghia'      => 'Khả năng phát sáng của viên đá khi chiếu tia cực tím. Mỗi loại đá có phản ứng huỳnh quang đặc trưng, nên đèn UV là công cụ sàng lọc nhanh trước khi kiểm định bằng thiết bị chuyên sâu.',
	),

	array(
		'tu'         => 'Độ trong',
		'dong_nghia' => array( 'độ tinh khiết' ),
		'nghia'      => 'Mức độ ít tạp chất và vết nứt bên trong viên đá. Độ trong càng cao thì giá càng cao, nhưng trong tuyệt đối ở một viên đá tự nhiên là chuyện rất hiếm — nên "trong vắt mà giá rẻ" là dấu hiệu đáng ngờ.',
	),

	array(
		'tu'         => 'Ngọc bích Nephrite',
		'dong_nghia' => array( 'nephrite' ),
		'nghia'      => 'Một trong hai loại khoáng vật được gọi là ngọc bích. Nephrite dai và bền hơn, sắc xanh thường trầm; loại còn lại là jadeite, hiếm hơn và có giá cao hơn nhiều. Nhiều người mua nhầm hai loại này với nhau.',
	),

	array(
		'tu'         => 'Jadeite',
		'dong_nghia' => array( 'ngọc phỉ thúy' ),
		'nghia'      => 'Loại ngọc bích quý hơn nephrite, màu xanh lục tươi và trong hơn. Jadeite chất lượng cao có giá cao hơn nephrite nhiều lần, nên đây là chỗ hay bị đánh tráo khi mua bán.',
	),

	array(
		// Đặt tên là "Độ ánh" chứ không phải "Ánh".
		//
		// "Ánh" đứng một mình quá ngắn và quá phổ biến: nó sẽ khớp với "ánh
		// sáng", "ánh nắng", "ánh mặt trời" trong bài và gắn nhầm giải nghĩa
		// vào những chỗ chẳng liên quan gì tới đá quý.
		//
		// Bài học chung: thuật ngữ dưới hai âm tiết cần cân nhắc kỹ trước khi
		// đưa vào từ điển tự động nhận diện.
		'tu'         => 'Độ ánh',
		'dong_nghia' => array( 'lustre' ),
		'nghia'      => 'Cách bề mặt viên đá phản chiếu ánh sáng. Mỗi loại đá có kiểu ánh đặc trưng — ánh thủy tinh, ánh kim, ánh ngọc trai — và người có kinh nghiệm nhận ra loại đá qua độ ánh trước cả khi dùng dụng cụ.',
	),

	array(
		'tu'         => 'Khúc xạ kép',
		'dong_nghia' => array( 'lưỡng chiết' ),
		'nghia'      => 'Hiện tượng tia sáng bị tách làm hai khi đi vào viên đá, khiến nhìn qua đá thấy các cạnh phía sau bị nhân đôi. Kim cương không có hiện tượng này, nên đây là cách nhanh để loại trừ các loại đá giả kim cương.',
	),
);
