# dql-core — Lõi Đá Quý Lục Yên

Plugin chứa **dữ liệu và logic nghiệp vụ** của daquylucyen.net.vn.

## Vì sao là plugin, không nằm trong theme

WordPress coi Custom Post Type và shortcode là chức năng của theme đang bật. Để
từ điển thuật ngữ trong theme thì ngày nào đổi theme là nội dung biến mất khỏi
trang quản trị — dữ liệu vẫn trong database nhưng không còn cách nào nhìn thấy.

| Nơi | Chứa gì | Tuổi thọ |
|---|---|---|
| Theme `kadence-daquylucyen` | Giao diện | Đổi lúc nào cũng được |
| Plugin `dql-core` | Dữ liệu, logic | Sống lâu hơn theme |

## Công cụ tra đá hợp mệnh

**Cách dùng:** đã tự động hiện ở trang chủ. Muốn chèn vào bài hoặc trang khác thì
dùng shortcode `[dql_tra_menh]`.

Nếu tắt plugin này, khối tra mệnh trên trang chủ **tự ẩn đi**, không báo lỗi —
theme kiểm tra `function_exists()` trước khi gọi.

### Hai loại dữ liệu, đừng lẫn lộn

**`includes/data/nap-am.php` — dữ liệu chuẩn, đừng sửa.**
Bảng nạp âm 60 hoa giáp của lịch can chi. Đây là sự thật lịch pháp, không phải
quan điểm. Thứ tự trong mảng chính là thứ tự vòng lặp — sắp xếp lại là hỏng toàn
bộ phép tính.

**`includes/data/menh-goi-y.php` — quan điểm chuyên môn của bạn, hãy sửa.**
Màu hợp và danh sách đá theo từng mệnh. Đây là kiến thức phong thủy mà người đọc
tin để bỏ tiền mua đá, nên nó phải là quan điểm của bạn chứ không phải của người
viết code. Danh sách hiện tại dựa trên quy tắc ngũ hành tương sinh phổ biến, và
mỗi loại đá chỉ được liệt kê khi site **đã có bài viết** về nó.

Sửa file đó là công cụ đổi theo ngay, không có danh sách nào giấu ở chỗ khác.
Muốn chỉnh mà không đụng file gốc thì dùng bộ lọc `dql_menh_goi_y`.

### Cách tính

Năm 4 sau Công nguyên là Giáp Tý, khởi đầu vòng 60 năm. Lấy `(năm − 4) % 60` ra
vị trí trong vòng; `% 10` và `% 12` ra can và chi; mỗi nạp âm phủ 2 năm nên chỉ
số nạp âm là vị trí chia đôi.

Đã kiểm chứng: 10 năm mốc cho kết quả đúng, mỗi mệnh xuất hiện đúng 12 lần trong
một vòng 60 năm, chu kỳ lặp lại chính xác.

### Giới hạn đã biết, đã nói rõ với người đọc

Công cụ chỉ hỏi **năm sinh**, trong khi mốc đổi năm âm lịch là Tết Nguyên đán chứ
không phải 1/1 dương lịch. Người sinh tháng 1 hoặc đầu tháng 2 dương lịch thuộc
năm âm lịch liền trước.

Đây là đánh đổi có chủ ý: hỏi đủ ngày tháng sẽ cần bảng chuyển đổi âm dương lịch
hàng chục năm, làm công cụ nặng và phức tạp hơn nhiều. Thay vào đó, mỗi kết quả
đều kèm dòng nhắc người đọc thử trừ đi 1 năm nếu sinh trước Tết.

### Vì sao không dùng JavaScript

Chạy hoàn toàn phía máy chủ. Dùng JavaScript sẽ mượt hơn một chút nhưng buộc phải
viết logic tính mệnh ở **hai nơi** — một bản PHP cho công cụ tìm kiếm và người
không bật JS, một bản JS cho trình duyệt. Hai bản sao của cùng một logic chắc
chắn sẽ lệch nhau sau vài lần sửa. Một lần tải trang là cái giá rẻ hơn.

Form gửi bằng GET kèm neo `#dql-tra-menh` nên sau khi bấm, trang quay lại đúng
chỗ công cụ chứ không nhảy về đầu trang.

## Từ điển thuật ngữ

Mỗi thuật ngữ là một mục trong **Từ điển** ở menu quản trị. Trang tra cứu ở
`/tu-dien/`, sắp theo A–Z.

Trong bài viết, thuật ngữ được **tự nhận diện**: gạch chân đứt nét, di chuột hiện
giải nghĩa ngắn, bấm vào sang trang giải nghĩa đầy đủ.

### Ô "Cách viết khác"

Mỗi thuật ngữ có ô nhập các cách viết khác cùng nghĩa, ngăn cách bằng dấu phẩy —
ví dụ *Tỉ trọng* nhận thêm *tỷ trọng*, *trọng lượng riêng*. Bài viết dùng cách nào
cũng được nhận ra.

### Các giới hạn đã đặt có chủ ý

| Giới hạn | Vì sao |
|---|---|
| Tối đa **8 thuật ngữ** mỗi bài | Không giới hạn thì bài dài thành bãi mìn liên kết |
| Mỗi thuật ngữ chỉ đánh dấu ở lần xuất hiện **đầu tiên** | Đánh dấu mọi lần lặp gây rối mắt |
| Bỏ qua chữ trong tiêu đề, liên kết, chú thích ảnh, mã nguồn | Tránh liên kết lồng nhau và phá vỡ tiêu đề |

Đổi số 8 ở hằng số `TOI_DA_MOI_BAI` trong `class-dql-glossary.php`.

### Bẫy khi thêm thuật ngữ mới: từ quá ngắn

Thuật ngữ dưới hai âm tiết dễ khớp bừa. Đã gặp thật: thuật ngữ **"Ánh"** sẽ khớp
với "**ánh** sáng", "**ánh** nắng" trong bài và gắn nhầm giải nghĩa vào chỗ không
liên quan. Đã đổi thành **"Độ ánh"**.

Tương tự, đừng đưa viết tắt hai ký tự như *ct* vào ô "Cách viết khác".

### Vì sao dùng DOMDocument chứ không dùng regex

Quét thuật ngữ bằng biểu thức chính quy trên chuỗi HTML sẽ có ngày một thuật ngữ
trùng với chữ nằm trong `href` và phá vỡ liên kết. DOMDocument phân biệt được đâu
là chữ hiển thị, đâu là thuộc tính thẻ.

Chỉ mục thuật ngữ được nhớ tạm một ngày, tự xóa mỗi khi có thuật ngữ được lưu
hoặc xóa.

### Trang hiển thị

Giao diện trang `/tu-dien/` do theme cung cấp qua file
`archive-dql_thuat_ngu.php`. Dùng theme khác thì trang vẫn chạy, chỉ là theo bố
cục lưu trữ mặc định của theme đó.
