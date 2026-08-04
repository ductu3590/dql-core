/*
 * Gửi tín hiệu "đã đọc bài này" sau khi trang hiện xong.
 *
 * Chạy sau sự kiện load, không chạy lúc dựng trang: người đọc không phải chờ
 * một request mà họ chẳng thấy kết quả, và trang vẫn được bộ nhớ đệm phục vụ
 * bình thường.
 *
 * sendBeacon là đường đi ưu tiên — trình duyệt tự lo việc gửi ngay cả khi người
 * đọc đóng tab giữa chừng. fetch với keepalive là đường lùi.
 */
( function () {
	'use strict';

	if ( typeof window.dqlLuotXem === 'undefined' || ! window.dqlLuotXem.url ) {
		return;
	}

	var cauHinh = window.dqlLuotXem;
	var khoa = 'dql-lx-' + cauHinh.id;

	/*
	 * Chặn đếm trùng ngay ở trình duyệt.
	 *
	 * Bấm F5 mười lần vẫn chỉ là một người đọc một bài. Máy chủ cũng có khóa
	 * riêng, nhưng chặn được ở đây thì đỡ hẳn mười request vô ích.
	 *
	 * sessionStorage có thể ném lỗi ở chế độ riêng tư của một vài trình duyệt.
	 * Hỏng phần này thì vẫn đếm, chỉ là mất lớp chặn thứ nhất.
	 */
	try {
		if ( window.sessionStorage.getItem( khoa ) ) {
			return;
		}
		window.sessionStorage.setItem( khoa, '1' );
	} catch ( e ) {}

	function gui() {
		if ( navigator.sendBeacon ) {
			navigator.sendBeacon( cauHinh.url );
			return;
		}

		if ( window.fetch ) {
			window.fetch( cauHinh.url, {
				method: 'POST',
				credentials: 'same-origin',
				keepalive: true,
			} ).catch( function () {} );
		}
	}

	if ( 'complete' === document.readyState ) {
		gui();
	} else {
		window.addEventListener( 'load', gui, { once: true } );
	}
} )();
