# KHUNG LUẬN VĂN ĐỒ ÁN TỐT NGHIỆP

Đề tài: Xây dựng website theo dõi giá sản phẩm của các sàn thương mại điện tử

Ghi chú: File này là bản khung nháp. Nội dung trong từng mục mới dừng ở mức định hướng cần viết, chưa triển khai thành đoạn văn hoàn chỉnh.

---

# PHẦN MỞ ĐẦU

## 1. Lý do chọn đề tài

Trong những năm gần đây, mua sắm trực tuyến trở thành một hình thức tiêu dùng quen thuộc đối với nhiều người dùng Internet. Các sàn thương mại điện tử như Shopee, Tiki và Lazada cung cấp số lượng sản phẩm lớn, nhiều chương trình khuyến mãi và nhiều lựa chọn nhà bán khác nhau. Sự phát triển này giúp người dùng có thêm cơ hội mua hàng với mức giá phù hợp, nhưng đồng thời cũng làm cho việc theo dõi và so sánh giá trở nên phức tạp hơn. Nhận định về sự phát triển của thương mại điện tử cần được bổ sung bằng số liệu hoặc báo cáo thị trường phù hợp [CẦN BỔ SUNG TÀI LIỆU THAM KHẢO].

Trong thực tế, cùng một sản phẩm có thể xuất hiện trên nhiều sàn, có nhiều mức giá khác nhau hoặc thay đổi giá theo từng thời điểm. Người dùng thường phải mở từng trang sản phẩm, ghi nhớ mức giá, kiểm tra lại khi có khuyến mãi và tự đánh giá xem giá hiện tại có thật sự tốt hay không. Cách làm thủ công này mất thời gian, dễ bỏ sót thông tin và không thuận tiện khi cần theo dõi nhiều sản phẩm trong thời gian dài.

Từ nhu cầu trên, đề tài “Xây dựng website theo dõi giá sản phẩm của các sàn thương mại điện tử” được lựa chọn nhằm xây dựng một hệ thống hỗ trợ người dùng tra cứu, so sánh và theo dõi biến động giá sản phẩm trên ba sàn Shopee, Tiki và Lazada. Đề tài không hướng đến việc thay thế chức năng mua hàng của các sàn thương mại điện tử, mà tập trung vào việc tổng hợp dữ liệu giá, lịch sử giá và cảnh báo giá để hỗ trợ quá trình ra quyết định mua sắm.

## 2. Mục tiêu của đề tài

Mục tiêu tổng quát của đề tài là xây dựng một website có khả năng quản lý, hiển thị và cập nhật dữ liệu giá sản phẩm từ Shopee, Tiki và Lazada. Hệ thống cần hỗ trợ người dùng tìm kiếm sản phẩm, xem thông tin chi tiết, so sánh giá giữa các sàn, theo dõi lịch sử giá và thiết lập cảnh báo khi giá đạt mức mong muốn.

Bên cạnh các chức năng dành cho người dùng, đề tài cũng hướng đến việc xây dựng khu vực quản trị để quản lý danh mục, sản phẩm, liên kết sản phẩm theo từng sàn, trạng thái thu thập dữ liệu và quá trình vận hành bot. Phần thu thập dữ liệu được thiết kế để cập nhật giá theo các link sản phẩm đã lưu, ghi nhận lịch sử giá và phản ánh trạng thái xử lý khi link sản phẩm phát sinh lỗi.

Về mặt kỹ thuật, đề tài đặt mục tiêu vận dụng mô hình MVC trong xây dựng website PHP/MySQL, kết hợp các chương trình crawler bằng Python để thu thập dữ liệu từ các nguồn khác nhau. Hệ thống cần có cơ sở dữ liệu đủ linh hoạt để lưu sản phẩm chung, link theo từng sàn, lịch sử giá, thông số sản phẩm nếu có, cảnh báo giá và thông báo cho người dùng.

## 3. Đối tượng và phạm vi nghiên cứu

Đối tượng nghiên cứu của đề tài là bài toán theo dõi và so sánh giá sản phẩm trên các sàn thương mại điện tử. Các dữ liệu chính được quan tâm gồm tên sản phẩm, giá hiện tại, giá gốc nếu có, hình ảnh đại diện, link sản phẩm, sàn bán hàng, trạng thái link, lịch sử giá, thông tin chi tiết sản phẩm và ngưỡng cảnh báo giá do người dùng thiết lập.

Phạm vi nghiên cứu của đề tài giới hạn trong ba sàn Shopee, Tiki và Lazada. Đây là các sàn có lượng sản phẩm phong phú, cách tổ chức dữ liệu khác nhau và phù hợp để khảo sát các phương pháp thu thập dữ liệu trong phạm vi đồ án. Đề tài không nghiên cứu các chức năng đặt hàng, thanh toán, vận chuyển, hoàn tiền hoặc xử lý giao dịch mua bán. Hệ thống chỉ đóng vai trò hỗ trợ theo dõi thông tin giá và dẫn người dùng đến link sản phẩm gốc khi cần xem thêm trên sàn.

Về phạm vi triển khai, hệ thống được xây dựng trong môi trường web chạy trên XAMPP với cơ sở dữ liệu MySQL và các chương trình thu thập dữ liệu bằng Python. Kết quả triển khai tập trung vào việc chứng minh quy trình quản lý sản phẩm, cập nhật giá, lưu lịch sử giá, cảnh báo giá và hỗ trợ quản trị dữ liệu.

## 4. Phương pháp thực hiện

Quá trình thực hiện đề tài bắt đầu từ việc khảo sát nhu cầu theo dõi giá sản phẩm và cách người dùng thường so sánh giá giữa nhiều sàn thương mại điện tử. Trên cơ sở đó, đề tài xác định các nhóm chức năng cần có đối với người dùng, quản trị viên và bot thu thập dữ liệu. Các yêu cầu này được dùng làm cơ sở để thiết kế cơ sở dữ liệu, kiến trúc hệ thống và giao diện sử dụng.

Sau bước phân tích yêu cầu, đề tài tiến hành khảo sát đặc điểm hiển thị dữ liệu sản phẩm trên Shopee, Tiki và Lazada ở mức phục vụ thiết kế hệ thống. Mỗi sàn có cách trình bày thông tin và cách tải dữ liệu khác nhau, vì vậy hệ thống không sử dụng một phương pháp thu thập duy nhất cho tất cả các nguồn. Việc lựa chọn phương pháp thu thập dữ liệu cho từng sàn được trình bày ở các chương sau, trong đó chú trọng đến khả năng ghi nhận trạng thái, xử lý lỗi truy cập và cập nhật dữ liệu theo từng link sản phẩm.

Phần website được xây dựng theo hướng tách biệt giữa xử lý dữ liệu, điều khiển luồng chức năng và giao diện hiển thị. Phần cơ sở dữ liệu được thiết kế để lưu thông tin sản phẩm, link theo từng sàn, lịch sử giá, cảnh báo và thông báo. Sau khi xây dựng các chức năng chính, hệ thống được kiểm tra bằng các tình huống sử dụng như tìm kiếm sản phẩm, xem chi tiết sản phẩm, đặt cảnh báo giá, cập nhật giá từ crawler và quản lý trạng thái link trong khu vực quản trị.

## 5. Ý nghĩa khoa học và thực tiễn

Về ý nghĩa thực tiễn, đề tài góp phần giải quyết một nhu cầu cụ thể trong mua sắm trực tuyến: người dùng muốn biết sản phẩm đang được bán ở đâu, giá hiện tại như thế nào và mức giá đó thay đổi ra sao theo thời gian. Thay vì kiểm tra thủ công từng sàn, người dùng có thể theo dõi thông tin tập trung hơn thông qua một website hỗ trợ tìm kiếm, so sánh và cảnh báo giá.

Về ý nghĩa học tập và nghiên cứu, đề tài là cơ hội vận dụng tổng hợp nhiều nội dung đã học trong quá trình đào tạo, bao gồm phân tích thiết kế hệ thống thông tin, thiết kế cơ sở dữ liệu, lập trình web, xử lý dữ liệu và tự động hóa một số tác vụ thu thập dữ liệu. Đề tài cũng giúp làm rõ những khó khăn thực tế khi xây dựng một hệ thống phụ thuộc vào dữ liệu từ nhiều website khác nhau, chẳng hạn như dữ liệu không đồng nhất, link sản phẩm thay đổi trạng thái, giao diện trang nguồn thay đổi hoặc có thể phát sinh yêu cầu xác minh hoặc lỗi truy cập.

## 6. Bố cục luận văn

Luận văn được tổ chức thành năm chương chính, ngoài phần mở đầu, tài liệu tham khảo và phụ lục.

Chương 1 trình bày tổng quan về vấn đề nghiên cứu, bao gồm bối cảnh thương mại điện tử, nhu cầu theo dõi và so sánh giá, khảo sát hiện trạng ba sàn Shopee, Tiki, Lazada, nhận xét về các giải pháp liên quan và định hướng giải quyết của đề tài.

Chương 2 trình bày cơ sở lý thuyết và công nghệ sử dụng. Nội dung chương tập trung vào bài toán thu thập dữ liệu giá sản phẩm, các khái niệm web scraping, web crawling, các phương pháp thu thập dữ liệu từ website thương mại điện tử, cơ sở lựa chọn phương pháp cho từng sàn và các công nghệ chính được sử dụng trong hệ thống.

Chương 3 trình bày phân tích và thiết kế hệ thống, bao gồm yêu cầu chức năng, yêu cầu phi chức năng, tác nhân, use case, luồng xử lý chính, thiết kế cơ sở dữ liệu, kiến trúc hệ thống và định hướng thiết kế giao diện.

Chương 4 trình bày quá trình nghiên cứu kỹ thuật và triển khai hệ thống. Đây là chương tập trung vào quá trình xây dựng website, tổ chức mã nguồn, triển khai crawler cho từng sàn, chuẩn hóa dữ liệu, quản lý trạng thái link, xử lý lỗi, cơ chế chạy định kỳ và các vấn đề phát sinh trong quá trình thực hiện.

Chương 5 trình bày kết quả thực hiện, kiểm thử, đánh giá và kiến nghị. Chương này tổng hợp kết quả giao diện người dùng, giao diện quản trị, kết quả chạy crawler, kiểm thử chức năng, ưu điểm, hạn chế, kết luận và hướng phát triển tiếp theo.

---

# Chương 1. TỔNG QUAN VỀ VẤN ĐỀ NGHIÊN CỨU

## 1.1 Bối cảnh thương mại điện tử

Thương mại điện tử đang thay đổi cách người dùng tiếp cận và lựa chọn sản phẩm. Thay vì phải đến cửa hàng trực tiếp, người dùng có thể tìm kiếm sản phẩm, xem thông tin, đọc đánh giá và so sánh nhiều lựa chọn ngay trên thiết bị có kết nối Internet. Các sàn thương mại điện tử đóng vai trò trung gian giữa người bán và người mua, cung cấp hạ tầng hiển thị sản phẩm, khuyến mãi, thanh toán, vận chuyển và chăm sóc sau bán hàng. Những nhận định tổng quan về sự phát triển của thương mại điện tử cần được bổ sung bằng báo cáo hoặc tài liệu thống kê phù hợp [CẦN BỔ SUNG TÀI LIỆU THAM KHẢO].

Trong môi trường mua sắm trực tuyến, giá sản phẩm không còn là một thông tin cố định. Giá có thể thay đổi theo chương trình khuyến mãi, thời điểm bán, chính sách của nhà bán, số lượng tồn kho hoặc các ưu đãi riêng của từng sàn. Một sản phẩm giống nhau hoặc tương tự nhau có thể xuất hiện đồng thời trên nhiều sàn với các mức giá khác nhau. Điều này tạo ra lợi ích cho người mua vì có thêm lựa chọn, nhưng cũng làm tăng chi phí thời gian khi người dùng muốn kiểm tra đâu là lựa chọn phù hợp nhất.

Đối với các sản phẩm có giá trị cao hoặc thường xuyên biến động giá, việc theo dõi giá tại một thời điểm là chưa đủ. Người dùng cần biết giá hiện tại so với các lần cập nhật trước đó để đánh giá xu hướng tăng, giảm hoặc nhận biết thời điểm giá đang ở mức tốt hơn bình thường. Đây là cơ sở hình thành nhu cầu xây dựng một hệ thống có khả năng lưu lịch sử giá và hỗ trợ cảnh báo khi giá đạt ngưỡng mong muốn.

## 1.2 Nhu cầu theo dõi và so sánh giá sản phẩm

Nhu cầu so sánh giá xuất phát từ thực tế người dùng thường không muốn mua ngay tại nơi đầu tiên nhìn thấy sản phẩm. Trước khi quyết định, người dùng có xu hướng kiểm tra thêm các sàn khác để xem sản phẩm có giá thấp hơn, khuyến mãi tốt hơn hoặc nhà bán phù hợp hơn hay không. Tuy nhiên, nếu thực hiện thủ công, người dùng phải tìm kiếm cùng một sản phẩm trên nhiều website, mở nhiều tab, so sánh tên sản phẩm, giá bán, phí liên quan và trạng thái còn hàng.

Việc theo dõi giá theo thời gian cũng gặp nhiều khó khăn. Người dùng có thể nhớ giá gần nhất của một sản phẩm, nhưng khó theo dõi chính xác nhiều sản phẩm cùng lúc trong nhiều ngày hoặc nhiều tuần. Khi giá thay đổi, người dùng không phải lúc nào cũng biết kịp thời. Vì vậy, chức năng cảnh báo giá có ý nghĩa thực tiễn: người dùng đặt một mức giá mong muốn và hệ thống thông báo khi dữ liệu cập nhật cho thấy sản phẩm đạt hoặc thấp hơn mức đó.

Đối với hệ thống quản trị, nhu cầu không chỉ dừng lại ở hiển thị giá. Quản trị viên cần quản lý danh mục sản phẩm, liên kết sản phẩm tương ứng trên từng sàn, trạng thái hoạt động của link, lịch sử cập nhật và các lỗi phát sinh khi thu thập dữ liệu. Nếu không có cơ chế quản lý tập trung, dữ liệu từ nhiều sàn dễ bị trùng lặp, thiếu nhất quán hoặc khó kiểm tra khi một link sản phẩm không còn truy cập được.

## 1.3 Khảo sát hiện trạng Shopee, Tiki, Lazada

### 1.3.1 Shopee

Shopee là một trong các sàn thương mại điện tử có số lượng sản phẩm và nhà bán lớn, phù hợp với nhu cầu khảo sát giá trên nhiều ngành hàng. Đối với người dùng, ưu điểm của Shopee là sự đa dạng về lựa chọn, nhiều mức giá và nhiều chương trình khuyến mãi. Tuy nhiên, chính sự đa dạng này cũng làm cho việc xác định sản phẩm tương ứng giữa các sàn trở nên khó hơn, vì cùng một mặt hàng có thể được đặt tên khác nhau, có nhiều biến thể hoặc được bán bởi nhiều nhà bán.

Ở góc độ đề tài, Shopee là nguồn dữ liệu cần được theo dõi nhưng cũng là nguồn có khả năng phát sinh nhiều trạng thái không ổn định. Trang sản phẩm có thể thay đổi cách hiển thị theo thời điểm, dữ liệu có thể tải động và quá trình truy cập có thể phát sinh yêu cầu xác minh hoặc lỗi truy cập. Vì vậy, khi thiết kế hệ thống, đề tài cần tính đến khả năng ghi nhận trạng thái link, lưu thông tin lỗi và tạm hoãn lượt quét trong các trường hợp không thể cập nhật dữ liệu bình thường.

### 1.3.2 Tiki

Tiki là sàn thương mại điện tử có cách trình bày sản phẩm tương đối rõ ràng, đặc biệt đối với các thông tin như tên sản phẩm, giá, hình ảnh, mô tả và thông số kỹ thuật. Với phạm vi của đề tài, Tiki được xem là một nguồn dữ liệu phù hợp để khai thác thông tin sản phẩm, ảnh đại diện và một số thông tin chi tiết nhằm bổ sung cho trang chi tiết sản phẩm trong hệ thống.

Đối với nhu cầu theo dõi giá, Tiki có vai trò là một trong ba nguồn so sánh chính cùng với Shopee và Lazada. Khi một sản phẩm có link tương ứng trên Tiki, hệ thống có thể lưu lại giá hiện tại và lịch sử giá để người dùng đối chiếu với các sàn còn lại. Việc khảo sát Tiki trong Chương 1 chỉ dừng ở mức hiện trạng và nhu cầu sử dụng dữ liệu; phần phân tích phương pháp thu thập cụ thể sẽ được trình bày ở các chương sau.

### 1.3.3 Lazada

Lazada cũng là một sàn thương mại điện tử phổ biến với nhiều nhóm sản phẩm, nhiều nhà bán và nhiều chương trình ưu đãi. Tương tự Shopee, sản phẩm trên Lazada có thể có nhiều biến thể, nhiều mức giá và trạng thái bán hàng khác nhau. Người dùng khi so sánh giá thường cần mở trang sản phẩm trên Lazada để đối chiếu với thông tin từ các sàn khác.

Trong phạm vi đề tài, Lazada được lựa chọn để tăng tính đa nguồn cho hệ thống theo dõi giá. Tuy nhiên, dữ liệu trên trang sản phẩm có thể phụ thuộc vào quá trình tải nội dung của website, giao diện có thể thay đổi và link sản phẩm có thể phát sinh lỗi trong quá trình truy cập. Do đó, hệ thống cần có cách lưu trạng thái link và phản ánh kết quả cập nhật để quản trị viên biết link nào đang hoạt động bình thường, link nào cần kiểm tra lại.

## 1.4 Khảo sát một số giải pháp liên quan

Hiện nay, người dùng có thể so sánh giá theo nhiều cách khác nhau. Cách đơn giản nhất là kiểm tra trực tiếp trên từng sàn thương mại điện tử. Người dùng nhập tên sản phẩm vào ô tìm kiếm của từng sàn, mở các kết quả phù hợp và tự so sánh giá. Cách này không cần công cụ hỗ trợ nhưng phụ thuộc hoàn toàn vào thao tác thủ công, khó lưu lại lịch sử và không phù hợp khi cần theo dõi nhiều sản phẩm.

Một nhóm giải pháp khác là các website hoặc công cụ hỗ trợ so sánh giá. Các công cụ này thường tổng hợp dữ liệu từ nhiều nguồn và giúp người dùng xem nhanh nơi bán hoặc mức giá tham khảo. Tuy nhiên, để trình bày chính xác về từng công cụ cụ thể, cần khảo sát và trích dẫn nguồn tương ứng thay vì nêu tên hoặc đánh giá theo cảm tính [CẦN BỔ SUNG TÀI LIỆU THAM KHẢO].

Ngoài ra, một số người dùng có thể tự theo dõi giá bằng bảng tính, ghi chú cá nhân hoặc lưu link sản phẩm để kiểm tra lại sau. Cách làm này phù hợp với số lượng ít sản phẩm nhưng không có khả năng tự cập nhật, không tự phát hiện thay đổi giá và không tạo được cảnh báo tự động. Đây là nhóm giải pháp đơn giản nhưng chưa đáp ứng tốt nhu cầu theo dõi giá thường xuyên.

## 1.5 Nhận xét hạn chế của các giải pháp hiện có

Qua khảo sát ở mức tổng quan, có thể nhận thấy các cách theo dõi giá phổ biến vẫn còn một số hạn chế đối với mục tiêu của đề tài. Thứ nhất, việc kiểm tra trực tiếp trên từng sàn yêu cầu nhiều thao tác lặp lại. Khi số lượng sản phẩm tăng lên, người dùng phải dành nhiều thời gian để mở link, ghi nhớ giá và tự so sánh. Cách làm này không tạo được dữ liệu lịch sử có hệ thống.

Thứ hai, các giải pháp so sánh giá có sẵn nếu được sử dụng thì thường phục vụ mục tiêu tra cứu nhanh cho người dùng cuối, trong khi đề tài cần một hệ thống có thể quản lý sản phẩm, quản lý link theo từng sàn, theo dõi trạng thái cập nhật và phục vụ cả khu vực quản trị. Một số giải pháp có thể không tập trung đúng phạm vi ba sàn Shopee, Tiki và Lazada, hoặc không cho phép tùy chỉnh dữ liệu theo cấu trúc riêng của đề tài.

Thứ ba, bài toán theo dõi giá không chỉ là lấy giá hiện tại. Hệ thống còn cần lưu lịch sử giá, phát hiện thay đổi qua các lần cập nhật, hỗ trợ cảnh báo giá và ghi nhận trạng thái khi link sản phẩm không thể cập nhật bình thường. Nếu không có phần quản lý trạng thái và lịch sử, dữ liệu giá chỉ phản ánh một thời điểm riêng lẻ, chưa đủ để hỗ trợ người dùng đánh giá xu hướng giá.

Từ các hạn chế trên, có thể thấy cần xây dựng một hệ thống riêng phù hợp với mục tiêu đồ án: dữ liệu tập trung vào ba sàn đã chọn, có cơ sở dữ liệu phục vụ lưu trữ lâu dài, có khu vực quản trị để kiểm soát nguồn dữ liệu và có cơ chế cập nhật giá theo từng link sản phẩm.

## 1.6 Định hướng giải quyết của đề tài

Định hướng của đề tài là xây dựng một website theo dõi giá sản phẩm với hai nhóm chức năng chính: nhóm chức năng dành cho người dùng và nhóm chức năng dành cho quản trị viên. Người dùng có thể tìm kiếm sản phẩm, xem thông tin chi tiết, so sánh giá giữa các sàn, xem lịch sử giá và thiết lập cảnh báo giá. Quản trị viên có thể quản lý danh mục, sản phẩm, link sản phẩm theo từng sàn, theo dõi trạng thái link và kiểm tra quá trình cập nhật dữ liệu.

Về dữ liệu, hệ thống tổ chức sản phẩm theo hướng có một bản ghi sản phẩm chung và nhiều link tương ứng trên các sàn khác nhau. Cách tổ chức này giúp tách biệt thông tin sản phẩm nội bộ với dữ liệu cụ thể của từng sàn, đồng thời thuận tiện cho việc lưu giá hiện tại và lịch sử giá theo từng link. Khi một link phát sinh lỗi, hệ thống có thể ghi nhận trạng thái để quản trị viên kiểm tra mà không ảnh hưởng đến toàn bộ sản phẩm.

Về thu thập dữ liệu, đề tài định hướng sử dụng các chương trình crawler để cập nhật thông tin giá từ các link đã được quản lý trong hệ thống. Do đặc điểm mỗi sàn khác nhau, phương pháp thu thập dữ liệu không được xem là một khối đồng nhất, mà cần lựa chọn phù hợp với từng nguồn. Khi phát sinh yêu cầu xác minh hoặc lỗi truy cập, hệ thống không xem đó là một trường hợp cập nhật thành công, mà ghi nhận trạng thái, lưu thông tin phục vụ kiểm tra và tạm hoãn lượt quét nếu cần.

Về giao diện, hệ thống hướng đến cách trình bày rõ ràng để người dùng dễ tìm kiếm, dễ xem giá giữa các sàn và dễ nhận biết lịch sử biến động giá. Khu vực quản trị cần ưu tiên tính dễ kiểm soát, giúp quản trị viên biết dữ liệu nào đang hoạt động ổn định, dữ liệu nào cần xử lý lại và quá trình cập nhật giá đang diễn ra như thế nào. Những định hướng này là cơ sở cho phần cơ sở lý thuyết, phân tích thiết kế và triển khai kỹ thuật ở các chương tiếp theo.

---

# Chương 2. CƠ SỞ LÝ THUYẾT VÀ CÔNG NGHỆ SỬ DỤNG

## 2.1 Tổng quan về bài toán thu thập dữ liệu giá sản phẩm

Bài toán thu thập dữ liệu giá sản phẩm trên các sàn thương mại điện tử không chỉ dừng ở việc lấy một con số giá tại một thời điểm. Trong thực tế, dữ liệu sản phẩm thường bao gồm nhiều thành phần như tên sản phẩm, giá hiện tại, giá gốc nếu có, hình ảnh đại diện, đường dẫn sản phẩm, thông số kỹ thuật, trạng thái còn bán hoặc hết hàng, đánh giá, lượt đánh giá và lượt bán nếu có. Các thành phần này có thể thay đổi theo thời gian, theo chương trình khuyến mãi, theo nhà bán hoặc theo chính sách hiển thị riêng của từng sàn.

Đối với hệ thống theo dõi giá, dữ liệu quan trọng nhất là giá hiện tại và lịch sử biến động giá. Giá hiện tại giúp người dùng biết mức giá đang được ghi nhận ở thời điểm gần nhất, còn lịch sử giá giúp đánh giá xu hướng tăng giảm qua nhiều lần cập nhật. Nếu hệ thống chỉ lưu giá mới nhất mà không lưu lịch sử, người dùng khó xác định mức giá hiện tại có thật sự tốt hơn so với các lần trước hay không.

Một khó khăn khác của bài toán này là cùng một sản phẩm có thể xuất hiện ở nhiều sàn với cách đặt tên, cách trình bày thông tin và cấu trúc đường dẫn khác nhau. Chẳng hạn, một sản phẩm điện tử có thể có nhiều phiên bản bộ nhớ, màu sắc hoặc nhà bán khác nhau. Vì vậy, hệ thống cần có cơ chế tổ chức dữ liệu sao cho vừa lưu được sản phẩm chung, vừa lưu được các link cụ thể trên từng sàn để phục vụ so sánh giá.

Trong phạm vi đề tài, dữ liệu được thu thập từ ba sàn Shopee, Tiki và Lazada. Mỗi sàn có đặc điểm tải dữ liệu khác nhau, do đó không thể áp dụng một phương pháp duy nhất cho toàn bộ hệ thống. Việc lựa chọn phương pháp thu thập cần dựa trên đặc điểm của từng nguồn dữ liệu, mức độ ổn định, khả năng truy xuất thông tin và cách xử lý khi phát sinh lỗi truy cập.

## 2.2 Khái niệm Web Scraping và Web Crawling

### 2.2.1 Web Scraping

Web Scraping là kỹ thuật trích xuất dữ liệu từ các trang web hoặc nguồn dữ liệu web để chuyển thành dạng có thể lưu trữ, xử lý và phân tích được [CẦN BỔ SUNG TÀI LIỆU THAM KHẢO]. Dữ liệu được lấy có thể là văn bản, giá bán, hình ảnh, thông số sản phẩm hoặc các thuộc tính khác hiển thị trên trang. Trong hệ thống của đề tài, Web Scraping được hiểu theo nghĩa thực tế là quá trình lấy thông tin cần thiết từ từng link sản phẩm đã được quản trị viên lưu trong hệ thống.

Kỹ thuật này phù hợp với bài toán theo dõi giá vì dữ liệu giá sản phẩm thường đã được hiển thị trên website hoặc được tải về để phục vụ giao diện người dùng. Sau khi lấy được dữ liệu, hệ thống cần chuẩn hóa giá, kiểm tra trạng thái sản phẩm, cập nhật giá hiện tại và ghi nhận lịch sử giá. Như vậy, Web Scraping trong đề tài không phải là một chức năng độc lập, mà là một phần trong quy trình cập nhật dữ liệu cho website theo dõi giá.

### 2.2.2 Web Crawling

Web Crawling là quá trình quét nhiều trang hoặc nhiều URL theo một quy tắc, danh sách hoặc lịch chạy nhất định [CẦN BỔ SUNG TÀI LIỆU THAM KHẢO]. Nếu Web Scraping nhấn mạnh vào việc trích xuất dữ liệu từ một trang cụ thể, thì Web Crawling nhấn mạnh vào việc duyệt qua nhiều nguồn dữ liệu để thu thập thông tin một cách có tổ chức.

Trong hệ thống của đề tài, crawling được áp dụng ở mức quét danh sách link sản phẩm đã được quản lý trong cơ sở dữ liệu. Bot không tự do quét toàn bộ website thương mại điện tử, mà lấy các link đã lưu, xác định sàn tương ứng, gọi crawler phù hợp và cập nhật lại kết quả vào hệ thống. Cách tiếp cận này giúp phạm vi thu thập dữ liệu rõ ràng hơn, dễ kiểm soát hơn và phù hợp với mục tiêu học tập, nghiên cứu của đồ án.

### 2.2.3 Rủi ro khi thu thập dữ liệu web

Thu thập dữ liệu web luôn có rủi ro vì hệ thống phụ thuộc vào nguồn dữ liệu bên ngoài. Một thay đổi nhỏ trong giao diện, cấu trúc HTML hoặc cách tải dữ liệu của website nguồn cũng có thể làm cho crawler không còn lấy được thông tin như trước. Ngoài ra, dữ liệu giá có thể được tải bằng JavaScript sau khi trang mở xong, khiến phương pháp lấy HTML ban đầu không đủ để đọc được giá.

Các lỗi kỹ thuật thường gặp gồm lỗi mạng, timeout, trang tải chậm, link sản phẩm không tồn tại, sản phẩm hết hàng, dữ liệu thiếu trường hoặc website phát sinh yêu cầu đăng nhập, xác minh hoặc lỗi truy cập. Bên cạnh đó, dữ liệu giữa các sàn không đồng nhất: cùng là giá sản phẩm nhưng có sàn hiển thị giá gốc, giá khuyến mãi, khoảng giá theo biến thể hoặc giá theo nhà bán. Vì vậy, hệ thống cần có bước chuẩn hóa dữ liệu sau khi thu thập.

Đối với đề tài này, các rủi ro trên được xem là một phần cần thiết trong thiết kế hệ thống. Crawler không chỉ cần trả về giá khi thành công, mà còn cần trả về trạng thái xử lý khi thất bại để hệ thống biết link nào cần thử lại, link nào cần tạm hoãn và link nào cần quản trị viên kiểm tra thủ công.

### 2.2.4 Nguyên tắc thu thập dữ liệu có trách nhiệm

Hệ thống trong đề tài được xây dựng phục vụ mục đích học tập và nghiên cứu. Vì vậy, việc thu thập dữ liệu cần được giới hạn trong phạm vi các link sản phẩm đã được quản lý, không triển khai theo hướng quét tràn lan hoặc gây tải không cần thiết cho website nguồn. Dữ liệu thu thập được sử dụng để minh họa chức năng theo dõi, so sánh và cảnh báo giá trong phạm vi đồ án tốt nghiệp.

Khi website nguồn phát sinh yêu cầu xác minh hoặc lỗi truy cập, hệ thống ghi nhận trạng thái, lưu log/debug và tạm hoãn lượt quét. Cách xử lý này giúp hệ thống phản ánh đúng tình trạng cập nhật dữ liệu mà không trình bày theo hướng can thiệp vào yêu cầu xác minh của website nguồn. Trong trường hợp chạy thủ công, quản trị viên có thể kiểm tra trạng thái link và quyết định xử lý tiếp theo.

Nguyên tắc này cũng ảnh hưởng đến cách thiết kế crawler. Thay vì chỉ cố gắng lấy được dữ liệu trong mọi trường hợp, crawler cần có khả năng dừng đúng lúc, trả về thông báo rõ ràng và không làm sai lệch dữ liệu trong cơ sở dữ liệu. Một lần quét không thành công phải được ghi nhận là không thành công, không được cập nhật giá bằng dữ liệu thiếu hoặc dữ liệu không chắc chắn.

## 2.3 Các phương pháp thu thập dữ liệu từ website thương mại điện tử

### 2.3.1 Thu thập dữ liệu qua API

API là giao diện cho phép các hệ thống phần mềm trao đổi dữ liệu với nhau theo một cấu trúc xác định [CẦN BỔ SUNG TÀI LIỆU THAM KHẢO]. Trong bài toán thu thập dữ liệu sản phẩm, nếu website có endpoint trả về dữ liệu sản phẩm dưới dạng có cấu trúc, crawler có thể gửi request đến endpoint đó, nhận dữ liệu trả về và trích xuất các trường cần thiết như tên sản phẩm, giá, hình ảnh hoặc thông số.

Ưu điểm của hướng thu thập qua API là tốc độ nhanh, dữ liệu thường rõ ràng hơn so với việc đọc giao diện HTML và ít phụ thuộc vào thay đổi bố cục hiển thị. Tuy nhiên, phương pháp này phụ thuộc vào việc xác định đúng endpoint, tham số truy vấn, mã sản phẩm và cấu trúc dữ liệu trả về. Nếu endpoint thay đổi hoặc không còn trả dữ liệu như trước, crawler cũng cần được điều chỉnh.

Trong đề tài, Tiki phù hợp hơn với hướng thu thập qua request/API vì dữ liệu sản phẩm có thể được tổ chức tương đối rõ ràng theo mã sản phẩm. Do đó, hệ thống ưu tiên sử dụng Python requests cho Tiki để lấy các thông tin như giá, ảnh đại diện và thông số sản phẩm khi dữ liệu khả dụng.

### 2.3.2 Thu thập dữ liệu từ HTML tĩnh

Thu thập dữ liệu từ HTML tĩnh là phương pháp gửi request đến một URL, nhận nội dung HTML trả về và trích xuất dữ liệu từ đó. Phương pháp này phù hợp với các trang mà thông tin cần lấy đã có sẵn trong HTML ban đầu. Khi đó, crawler có thể dùng parser để tìm các thẻ, thuộc tính hoặc đoạn dữ liệu chứa giá, tên sản phẩm và các thông tin liên quan.

Ưu điểm của phương pháp này là đơn giản, nhẹ hơn so với việc mở trình duyệt thật và dễ triển khai với các thư viện xử lý HTTP. Tuy nhiên, hạn chế lớn là nhiều website thương mại điện tử hiện nay tải dữ liệu bằng JavaScript sau khi trang đã được mở. Trong trường hợp đó, HTML ban đầu có thể không chứa giá hoặc chỉ chứa khung giao diện, khiến crawler không lấy được dữ liệu chính xác.

Trong hệ thống của đề tài, phương pháp đọc HTML tĩnh được xem là hướng tham khảo và có thể dùng trong một số trường hợp dữ liệu xuất hiện sẵn. Tuy nhiên, với các sàn có dữ liệu động như Shopee và Lazada, phương pháp này không phải lựa chọn chính.

### 2.3.3 Thu thập dữ liệu từ trang web động bằng trình duyệt tự động

Đối với các website có nội dung được render bằng JavaScript, crawler cần mô phỏng quá trình mở trang gần giống trình duyệt của người dùng. Selenium/ChromeDriver là một công cụ thường được sử dụng để điều khiển trình duyệt tự động, mở URL, chờ phần tử xuất hiện và trích xuất dữ liệu từ DOM sau khi trang tải xong [CẦN BỔ SUNG TÀI LIỆU THAM KHẢO].

Ưu điểm của phương pháp này là xử lý được các trang web động, nơi dữ liệu không có sẵn trong HTML ban đầu. Crawler có thể chờ giá, tên sản phẩm hoặc vùng thông tin cần thiết xuất hiện rồi mới đọc dữ liệu. Nhược điểm là tốc độ chậm hơn, tiêu tốn tài nguyên hơn và dễ bị ảnh hưởng bởi thay đổi giao diện, popup, lỗi tải trang hoặc yêu cầu xác minh.

Trong đề tài, Shopee và Lazada phù hợp hơn với hướng sử dụng trình duyệt tự động vì trang sản phẩm có thể tải dữ liệu sau khi JavaScript thực thi. Crawler cần mở trang, chờ nội dung cần thiết và trích xuất thông tin từ DOM hoặc văn bản hiển thị. Khi không thể lấy dữ liệu do lỗi truy cập hoặc yêu cầu xác minh, hệ thống ghi trạng thái, lưu log/debug và tạm hoãn lượt quét thay vì cập nhật dữ liệu không chắc chắn.

### 2.3.4 So sánh các phương pháp thu thập dữ liệu

Bảng 2.1. So sánh các phương pháp thu thập dữ liệu từ website thương mại điện tử

| Phương pháp | Ưu điểm | Hạn chế | Sàn phù hợp trong đề tài |
| --- | --- | --- | --- |
| API | Nhanh, dữ liệu có cấu trúc, dễ lấy trường thông tin cụ thể | Phụ thuộc endpoint, tham số và mã sản phẩm | Tiki |
| HTML tĩnh | Đơn giản, nhẹ, không cần mở trình duyệt | Không phù hợp nếu dữ liệu tải bằng JavaScript hoặc HTML thay đổi nhiều | Dùng tham khảo |
| Trình duyệt tự động | Xử lý được trang động, đọc được dữ liệu sau khi giao diện tải xong | Chậm hơn, tốn tài nguyên hơn, cần xử lý timeout, popup, lỗi truy cập và yêu cầu xác minh | Shopee, Lazada |

Từ bảng so sánh có thể thấy mỗi phương pháp phù hợp với một nhóm website khác nhau. Nếu dữ liệu đã có dạng có cấu trúc, phương pháp API là lựa chọn hiệu quả hơn. Nếu dữ liệu hiển thị sẵn trong HTML, việc parse HTML có thể đáp ứng được yêu cầu. Đối với các trang động, trình duyệt tự động giúp crawler quan sát được nội dung sau khi JavaScript tải xong, nhưng cần bổ sung cơ chế kiểm soát lỗi và trạng thái quét.

## 2.4 Đặc điểm cấu trúc dữ liệu của website thương mại điện tử

Dữ liệu sản phẩm trên website thương mại điện tử thường có cấu trúc phức tạp hơn dữ liệu sản phẩm trong một hệ thống bán hàng nội bộ. Một URL sản phẩm có thể chứa mã sản phẩm, mã nhà bán, mã biến thể hoặc các tham số phục vụ quảng cáo và theo dõi chiến dịch. Vì vậy, khi lưu link sản phẩm, hệ thống cần quan tâm đến việc chuẩn hóa URL để hạn chế trùng lặp do các tham số không cần thiết.

Thông tin giá cũng không phải lúc nào chỉ có một giá trị. Một sản phẩm có thể có giá hiện tại, giá gốc, phần trăm giảm giá, khoảng giá theo biến thể hoặc giá thay đổi theo nhà bán. Ngoài ra, sản phẩm có thể có nhiều phiên bản như màu sắc, dung lượng, kích thước hoặc gói bán khác nhau. Nếu hệ thống không xác định rõ đang theo dõi link nào và biến thể nào, dữ liệu giá có thể bị hiểu sai.

Các thông tin bổ sung như ảnh, thông số kỹ thuật, đánh giá, lượt đánh giá và lượt bán nếu có thường nằm ở nhiều vị trí khác nhau trong trang. Một số thông tin hiển thị trực tiếp, một số thông tin tải sau hoặc nằm trong dữ liệu có cấu trúc. Trạng thái sản phẩm cũng có thể thay đổi theo thời gian, chẳng hạn còn bán, hết hàng, ngừng kinh doanh, link không tồn tại hoặc trang phát sinh lỗi truy cập.

Những đặc điểm trên cho thấy hệ thống cần tách dữ liệu thành nhiều nhóm: sản phẩm chung, link theo từng sàn, giá hiện tại, lịch sử giá, trạng thái link và thông tin chi tiết nếu có. Cách tổ chức này giúp việc cập nhật dữ liệu linh hoạt hơn và hạn chế ảnh hưởng khi một link sản phẩm cụ thể gặp lỗi.

## 2.5 Phân tích đặc điểm thu thập dữ liệu theo từng sàn

### 2.5.1 Tiki

Đối với Tiki, dữ liệu sản phẩm có thể được tiếp cận ổn định hơn thông qua hướng request/API. URL sản phẩm thường có thể giúp xác định mã sản phẩm cần truy vấn, từ đó crawler gửi request để lấy dữ liệu có cấu trúc. Cách làm này giúp giảm phụ thuộc vào giao diện hiển thị và thuận lợi hơn khi cần lấy các trường như tên sản phẩm, giá, ảnh và thông số.

Trong hệ thống của đề tài, Tiki được ưu tiên xử lý bằng Python requests. Sau khi lấy được dữ liệu, crawler chuẩn hóa giá, kiểm tra trường ảnh, đọc thông số nếu có và trả kết quả về để cập nhật vào cơ sở dữ liệu. Cách tiếp cận này phù hợp với mục tiêu ổn định dữ liệu Tiki và bổ sung thông tin chi tiết cho trang sản phẩm.

### 2.5.2 Shopee

Shopee có lượng sản phẩm lớn và giá có thể biến động nhanh theo chương trình khuyến mãi, nhà bán hoặc biến thể sản phẩm. Tuy nhiên, dữ liệu trên trang sản phẩm có thể được tải động, nghĩa là thông tin giá không nhất thiết xuất hiện đầy đủ trong HTML ban đầu. Ngoài ra, trong quá trình truy cập có thể phát sinh yêu cầu xác minh hoặc lỗi truy cập.

Vì đặc điểm này, hệ thống sử dụng hướng trình duyệt tự động cho Shopee và tổ chức quá trình quét theo batch nhỏ. Việc chia nhỏ lượt quét giúp dễ kiểm soát thời gian chạy, giảm khả năng một lỗi làm ảnh hưởng đến toàn bộ danh sách link và thuận tiện cho việc tạm hoãn các link đang có vấn đề. Khi crawler không lấy được dữ liệu chắc chắn, hệ thống ghi trạng thái, lưu log/debug và không cập nhật giá bằng kết quả không hợp lệ.

Trong phạm vi Chương 2, Shopee được phân tích ở mức cơ sở lựa chọn phương pháp. Chi tiết quá trình triển khai, thử nghiệm, lỗi phát sinh và cách xử lý cụ thể sẽ được trình bày ở Chương 4.

### 2.5.3 Lazada

Lazada cũng có đặc điểm dữ liệu động tương tự nhiều website thương mại điện tử hiện đại. Giá và một số thông tin sản phẩm có thể chỉ xuất hiện sau khi trang tải xong và JavaScript thực thi. Bên cạnh đó, giao diện trang sản phẩm có thể thay đổi theo thời điểm, theo loại sản phẩm hoặc theo trạng thái hiển thị của nhà bán.

Trong hệ thống của đề tài, Lazada được xử lý theo hướng dùng trình duyệt tự động, chờ nội dung cần thiết xuất hiện và trích xuất dữ liệu từ DOM hoặc phần văn bản hiển thị. Phương pháp này chậm hơn so với request/API, nhưng phù hợp hơn khi dữ liệu không có sẵn trong HTML tĩnh. Khi link sản phẩm lỗi, hết hàng hoặc không lấy được dữ liệu, crawler cần trả về trạng thái rõ ràng để hệ thống quản trị biết link cần được kiểm tra.

## 2.6 Cơ sở lựa chọn công nghệ thu thập dữ liệu

Việc lựa chọn công nghệ thu thập dữ liệu trong đề tài dựa trên đặc điểm của từng website thay vì chọn một công nghệ duy nhất cho tất cả các sàn. Cách tiếp cận này giúp hệ thống tận dụng phương pháp nhẹ và ổn định khi dữ liệu có cấu trúc, đồng thời vẫn có khả năng xử lý các trang web động khi cần.

Bảng 2.2. Cơ sở lựa chọn công nghệ thu thập dữ liệu theo đặc điểm website

| Đặc điểm website | Phương pháp phù hợp | Công nghệ áp dụng | Liên hệ trong hệ thống |
| --- | --- | --- | --- |
| Có dữ liệu trả về rõ ràng qua endpoint | Gửi request trực tiếp | Python requests | Ưu tiên cho Tiki |
| Dữ liệu nằm sẵn trong HTML | Lấy HTML và parse dữ liệu | requests kết hợp parser nếu cần | Chỉ dùng khi phù hợp |
| Dữ liệu render bằng JavaScript | Mở trình duyệt tự động và chờ DOM | Selenium/ChromeDriver | Áp dụng cho Shopee và Lazada |
| Website có thể phát sinh lỗi truy cập hoặc yêu cầu xác minh | Batch nhỏ, retry, cooldown, ghi trạng thái | Python crawler kết hợp trạng thái link | Quan trọng với Shopee và các link không ổn định |
| Cần cập nhật theo lịch | Chạy script theo lịch | File script kết hợp công cụ lập lịch của hệ điều hành | Dùng cho luồng cập nhật định kỳ |

Từ cơ sở trên, Tiki được ưu tiên dùng requests/API vì phù hợp với dữ liệu có cấu trúc. Shopee và Lazada được xử lý bằng trình duyệt tự động vì dữ liệu có thể phụ thuộc vào quá trình tải trang. Với các nguồn có khả năng phát sinh lỗi truy cập, hệ thống cần lưu trạng thái link, thời điểm thử lại và log/debug để quản trị viên có căn cứ kiểm tra.

## 2.7 Chuẩn hóa và so khớp dữ liệu sản phẩm

Chuẩn hóa dữ liệu là bước cần thiết sau khi thu thập thông tin từ nhiều sàn thương mại điện tử. Dữ liệu lấy từ Shopee, Tiki và Lazada có thể khác nhau về cách đặt tên, định dạng giá, đường dẫn, hình ảnh và trạng thái sản phẩm. Nếu lưu trực tiếp mà không chuẩn hóa, hệ thống dễ gặp lỗi trùng link, khó so sánh giá và khó xác định sản phẩm tương ứng giữa các sàn.

Đối với URL, hệ thống cần chuẩn hóa để loại bỏ hoặc hạn chế ảnh hưởng của các tham số không cần thiết như tracking, campaign hoặc tham số chia sẻ. Việc tạo mã băm URL giúp kiểm tra nhanh một link đã tồn tại trong hệ thống hay chưa. Ngoài URL, hệ thống cũng cần tách mã sản phẩm theo từng nền tảng khi có thể, vì mỗi sàn có cách định danh sản phẩm riêng.

Đối với tên sản phẩm, chuẩn hóa giúp hỗ trợ tìm kiếm và phát hiện các sản phẩm có khả năng giống nhau. Tuy nhiên, tên giống hoặc gần giống không đủ để kết luận hai sản phẩm chắc chắn trùng nhau, vì sản phẩm có thể khác phiên bản, dung lượng, màu sắc, cấu hình hoặc nhà bán. Do đó, hệ thống chỉ nên dùng mức độ giống tên để cảnh báo quản trị viên kiểm tra, không tự động gộp sản phẩm nếu chưa có căn cứ chắc chắn.

Bảng 2.3. Phân biệt kiểm tra trùng link và nghi trùng sản phẩm theo tên

| Tiêu chí | Trùng link sản phẩm | Nghi trùng theo tên sản phẩm |
| --- | --- | --- |
| Cơ sở kiểm tra | URL đã chuẩn hóa, mã băm URL, mã sản phẩm theo sàn nếu có | Tên sản phẩm sau khi chuẩn hóa |
| Mức độ chắc chắn | Cao hơn vì dựa trên cùng một link hoặc cùng định danh nền tảng | Chỉ mang tính gợi ý |
| Cách xử lý phù hợp | Ngăn thêm link trùng hoặc cập nhật link đã có | Cảnh báo quản trị viên kiểm tra |
| Rủi ro nếu xử lý sai | Có thể lưu trùng dữ liệu giá của cùng một link | Có thể gộp nhầm các phiên bản sản phẩm khác nhau |

Như vậy, chuẩn hóa và so khớp dữ liệu trong đề tài cần được thực hiện theo hướng thận trọng. Link trùng có thể kiểm tra bằng tiêu chí kỹ thuật tương đối rõ, còn sản phẩm nghi trùng theo tên chỉ nên hỗ trợ quyết định của quản trị viên.

## 2.8 Cơ sở dữ liệu cho hệ thống theo dõi giá

Cơ sở dữ liệu quan hệ là mô hình lưu trữ dữ liệu bằng các bảng có quan hệ với nhau thông qua khóa chính và khóa ngoại [CẦN BỔ SUNG TÀI LIỆU THAM KHẢO]. Mô hình này phù hợp với hệ thống theo dõi giá vì dữ liệu có nhiều nhóm liên quan: sản phẩm, danh mục, link theo từng sàn, lịch sử giá, người dùng, cảnh báo và thông báo.

Trong phạm vi Chương 2, cơ sở dữ liệu được xem xét ở mức nhóm dữ liệu cần lưu. Hệ thống cần lưu nhóm sản phẩm chung để đại diện cho sản phẩm trong website, nhóm link sàn để biết sản phẩm đó có mặt trên Shopee, Tiki hoặc Lazada, nhóm giá hiện tại để hiển thị nhanh, nhóm lịch sử giá để theo dõi biến động, nhóm người dùng để phục vụ đăng nhập và cảnh báo, nhóm cảnh báo giá để lưu ngưỡng mong muốn, nhóm thông báo để phản hồi cho người dùng, nhóm trạng thái link để quản trị viên biết kết quả cập nhật và nhóm thông số chi tiết nếu có.

Việc tách các nhóm dữ liệu như trên giúp hệ thống không phụ thuộc vào một link duy nhất. Một sản phẩm có thể có nhiều link ở nhiều sàn; mỗi link có thể có trạng thái và lịch sử giá riêng. Thiết kế chi tiết các bảng, khóa chính, khóa ngoại và quan hệ dữ liệu sẽ được trình bày ở Chương 3.

## 2.9 Mô hình MVC và công nghệ xây dựng website

### 2.9.1 Mô hình MVC

MVC là mô hình tổ chức ứng dụng thành ba thành phần chính: Model, View và Controller [CẦN BỔ SUNG TÀI LIỆU THAM KHẢO]. Model chịu trách nhiệm làm việc với dữ liệu và quy tắc xử lý liên quan; View hiển thị giao diện cho người dùng; Controller tiếp nhận yêu cầu, gọi Model xử lý và chọn View phù hợp để trả kết quả.

Mô hình MVC phù hợp với đề tài vì hệ thống có nhiều nhóm chức năng khác nhau như trang người dùng, trang quản trị, đăng nhập/đăng ký, sản phẩm, danh mục, cảnh báo giá và quản lý bot. Nếu không tách rõ vai trò, mã nguồn dễ bị lẫn giữa truy vấn dữ liệu, xử lý nghiệp vụ và giao diện HTML. Việc tổ chức theo MVC giúp quá trình phát triển và bảo trì thuận tiện hơn, đặc biệt khi hệ thống cần mở rộng thêm chức năng quản trị hoặc thay đổi giao diện.

### 2.9.2 Công nghệ xây dựng website

Phần website của hệ thống sử dụng các công nghệ quen thuộc trong môi trường phát triển web PHP/MySQL. Nội dung mục này chỉ trình bày ngắn vai trò của từng công nghệ, vì trọng tâm của Chương 2 vẫn là cơ sở lựa chọn phương pháp thu thập dữ liệu và xử lý dữ liệu giá sản phẩm.

Bảng 2.4. Công nghệ sử dụng trong phần website

| Công nghệ | Vai trò trong hệ thống |
| --- | --- |
| PHP | Xử lý backend, controller, model và các chức năng phía máy chủ |
| MySQL | Lưu trữ dữ liệu sản phẩm, link sàn, lịch sử giá, người dùng, cảnh báo và thông báo |
| HTML/CSS/JavaScript | Xây dựng giao diện và xử lý tương tác phía trình duyệt |
| Bootstrap | Hỗ trợ bố cục và thành phần giao diện responsive |
| Chart.js | Hiển thị biểu đồ lịch sử giá sản phẩm |
| PHPMailer | Hỗ trợ gửi email, OTP và thông báo liên quan nếu được cấu hình |
| Python | Xây dựng các crawler thu thập và cập nhật dữ liệu giá |
| Selenium/ChromeDriver | Điều khiển trình duyệt tự động để xử lý các trang động |
| Python requests | Gửi request đến nguồn dữ liệu phù hợp, đặc biệt với Tiki |

Trong hệ thống, PHP và MySQL đảm nhận phần lõi của website, bao gồm quản lý dữ liệu, xử lý yêu cầu từ người dùng và quản trị viên. JavaScript được sử dụng để cải thiện tương tác trên giao diện, chẳng hạn cập nhật dữ liệu bằng AJAX/fetch ở một số chức năng. Chart.js hỗ trợ biểu diễn lịch sử giá dưới dạng trực quan, giúp người dùng dễ theo dõi biến động giá hơn. PHPMailer được sử dụng cho các chức năng liên quan đến email như OTP hoặc thông báo cảnh báo giá khi hệ thống được cấu hình phù hợp.

Các crawler được xây dựng bằng Python để tách riêng quá trình thu thập dữ liệu khỏi phần website PHP. Cách tách này giúp hệ thống dễ bảo trì hơn: website tập trung vào hiển thị và quản lý dữ liệu, còn crawler tập trung vào cập nhật dữ liệu từ các sàn. Việc kết hợp PHP/MySQL với Python crawler phù hợp với đặc điểm của đề tài, vì hệ thống vừa cần giao diện web quản trị, vừa cần các tiến trình xử lý dữ liệu theo link sản phẩm.

---

# Chương 3. PHÂN TÍCH VÀ THIẾT KẾ HỆ THỐNG

## 3.1 Khảo sát và phân tích yêu cầu hệ thống

Trong phạm vi đề tài, việc xác định yêu cầu hệ thống không thực hiện bằng khảo sát định lượng qua phiếu hỏi người dùng. Đề tài không xây dựng số liệu khảo sát mẫu, không thống kê tỷ lệ lựa chọn và không đưa ra kết luận dựa trên kết quả khảo sát định lượng. Thay vào đó, yêu cầu hệ thống được xác định dựa trên quá trình phân tích bài toán theo dõi và so sánh giá sản phẩm, khảo sát hiện trạng ba sàn thương mại điện tử Shopee, Tiki, Lazada, tham khảo nhóm giải pháp liên quan đã trình bày ở Chương 1, đồng thời đối chiếu với nhu cầu thao tác của các tác nhân trong hệ thống và các chức năng thực tế đã triển khai trong project.

Bài toán chính của hệ thống là giúp người dùng theo dõi giá sản phẩm trên nhiều sàn thương mại điện tử. Từ bài toán này, hệ thống cần có khả năng lưu sản phẩm chung, lưu các link tương ứng trên từng sàn, cập nhật giá theo từng link, lưu lịch sử giá và hỗ trợ cảnh báo khi giá đạt mức người dùng mong muốn. Ngoài ra, do dữ liệu được lấy từ nhiều nguồn khác nhau, hệ thống cũng cần có khu vực quản trị để kiểm soát danh mục, sản phẩm, link sàn, trạng thái link và quá trình chạy crawler.

Yêu cầu của người dùng chưa đăng nhập tập trung vào khả năng truy cập thông tin công khai như xem trang chủ, tìm kiếm, lọc, sắp xếp sản phẩm và xem chi tiết sản phẩm. Nhóm người dùng này cũng có thể đăng ký tài khoản và đăng nhập để sử dụng các chức năng cá nhân hóa.

Yêu cầu của người dùng đã đăng nhập mở rộng thêm các thao tác liên quan đến theo dõi giá, bao gồm đặt hoặc cập nhật mức giá mong muốn, hủy cảnh báo giá, xem danh sách sản phẩm đang theo dõi và xem thông báo khi hệ thống ghi nhận sản phẩm đạt mức giá phù hợp.

Yêu cầu của quản trị viên tập trung vào quản lý dữ liệu nền cho hệ thống. Quản trị viên cần quản lý danh mục, sản phẩm, link sản phẩm theo từng sàn, kiểm tra trùng link, xử lý cảnh báo nghi trùng sản phẩm, theo dõi trạng thái link, chạy bot/crawler khi cần và xem danh sách cảnh báo giá của người dùng. Project hiện chưa có module quản lý người dùng dạng CRUD đầy đủ, vì vậy nội dung Chương 3 không xem quản lý người dùng là một chức năng quản trị chính; dashboard chỉ ghi nhận thống kê số người dùng đã xác thực.

Yêu cầu của bot thu thập dữ liệu là lấy danh sách link cần quét, cập nhật giá hiện tại, lưu lịch sử giá, cập nhật trạng thái link, xử lý retry/cooldown khi phát sinh lỗi và ghi log/lock file để hạn chế việc nhiều tiến trình crawler chạy chồng lên nhau. Khi gặp yêu cầu xác minh hoặc lỗi truy cập, hệ thống ghi nhận trạng thái, lưu log/debug và tạm hoãn lượt quét; khi chạy ở chế độ thủ công, quản trị viên có thể kiểm tra và xử lý theo tình huống thực tế.

Yêu cầu của hệ thống gửi email/thông báo gồm gửi OTP khi đăng ký tài khoản, gửi email cảnh báo giá khi cấu hình email hoạt động và tạo thông báo web để người dùng có thể xem trong giao diện.

## 3.2 Tác nhân hệ thống

Bảng 3.1. Các tác nhân chính của hệ thống

| Tác nhân | Mô tả | Vai trò trong hệ thống |
| --- | --- | --- |
| Người dùng chưa đăng nhập | Người truy cập website nhưng chưa xác thực tài khoản | Xem trang chủ, tìm kiếm/lọc/sắp xếp sản phẩm, xem chi tiết sản phẩm, đăng ký, đăng nhập |
| Người dùng đã đăng nhập | Người dùng đã có tài khoản và đăng nhập thành công | Sử dụng các chức năng công khai, đặt/cập nhật/hủy cảnh báo giá, xem danh sách theo dõi, xem/đọc thông báo |
| Quản trị viên | Tài khoản có vai trò `admin` trong bảng `users` | Quản lý danh mục, sản phẩm, link sàn, trạng thái link, bot/crawler và cảnh báo giá |
| Bot thu thập dữ liệu | Các script Python trong thư mục `crawlers/` | Lấy link cần quét, cập nhật giá, lưu lịch sử giá, cập nhật trạng thái link và ghi log/lock file |
| Hệ thống gửi email/thông báo | MailService, cron gửi cảnh báo và bảng thông báo web | Gửi OTP, gửi email cảnh báo giá khi cấu hình hoạt động và tạo thông báo web cho người dùng |

## 3.3 Sơ đồ ngữ cảnh của hệ thống

[Hình 3.1. Sơ đồ ngữ cảnh của hệ thống]

Sơ đồ ngữ cảnh xem toàn bộ website theo dõi giá là một hệ thống duy nhất. Ở mức này, hệ thống tương tác với các tác nhân và nguồn dữ liệu bên ngoài gồm người dùng, quản trị viên, các sàn thương mại điện tử Shopee/Tiki/Lazada, bot/scheduler và hệ thống email/thông báo.

Người dùng gửi các yêu cầu như tìm kiếm sản phẩm, lọc sản phẩm, xem chi tiết, xem lịch sử giá và đặt/hủy cảnh báo giá. Hệ thống trả về danh sách sản phẩm, thông tin giá theo sàn, biểu đồ lịch sử giá, trạng thái theo dõi và thông báo liên quan.

Quản trị viên gửi yêu cầu quản lý danh mục, sản phẩm, link sàn, trạng thái link và bot/crawler. Hệ thống phản hồi bằng dữ liệu quản trị, danh sách sản phẩm, danh sách link, trạng thái cập nhật, thông báo lỗi và kết quả xử lý.

Các sàn thương mại điện tử Shopee, Tiki và Lazada là nguồn dữ liệu sản phẩm bên ngoài. Crawler chỉ làm việc với các link đã được lưu trong hệ thống để lấy giá, ảnh, thông tin sản phẩm và trạng thái khả dụng nếu dữ liệu có thể truy cập được.

Bot/Scheduler kích hoạt quá trình cập nhật dữ liệu theo yêu cầu thủ công hoặc theo lịch chạy. Kết quả chạy được ghi nhận lại thông qua giá hiện tại, lịch sử giá, trạng thái link, thời điểm quét tiếp theo và log/debug khi có lỗi.

Hệ thống email/thông báo hỗ trợ gửi OTP khi đăng ký, gửi email cảnh báo giá khi cấu hình email hoạt động và tạo thông báo web sau khi phát hiện sản phẩm đạt mức giá người dùng đã đặt.

## 3.4 Yêu cầu chức năng

Bảng 3.2. Danh sách yêu cầu chức năng của hệ thống

| Mã yêu cầu | Nhóm chức năng | Nội dung yêu cầu | Ghi chú triển khai thực tế |
| --- | --- | --- | --- |
| ND-01 | Người dùng | Đăng ký tài khoản bằng email và mật khẩu | Có trong `AuthController::postRegister()` |
| ND-02 | Người dùng | Xác thực OTP và gửi lại OTP | Có trong `verify`, `postVerify`, `resendOTP`; OTP gửi qua `MailService` |
| ND-03 | Người dùng | Đăng nhập và đăng xuất | Có phân biệt tài khoản user/admin theo trường `role` |
| ND-04 | Người dùng | Xem trang chủ và danh mục sản phẩm | Trang chủ lấy danh mục, sản phẩm mới, sản phẩm nổi bật và top deal |
| ND-05 | Người dùng | Tìm kiếm, gợi ý tìm kiếm, lọc và sắp xếp sản phẩm | Có lọc theo danh mục, sàn, khoảng giá và sắp xếp theo giá/mới nhất |
| ND-06 | Người dùng | Xem chi tiết sản phẩm | Hiển thị thông tin sản phẩm, link sàn, thông số nếu có, sản phẩm liên quan |
| ND-07 | Người dùng | So sánh giá theo sàn | Lấy dữ liệu từ `platform_links` và sắp xếp link có giá hợp lệ |
| ND-08 | Người dùng | Xem lịch sử giá | Lấy dữ liệu từ `price_history`, hiển thị bằng biểu đồ |
| ND-09 | Người dùng | Đặt/cập nhật/hủy cảnh báo giá | Lưu trong `price_alerts`, hỗ trợ thao tác bằng form và AJAX/fetch |
| ND-10 | Người dùng | Xem danh sách theo dõi và thông báo | Có trang `my_alerts` và danh sách thông báo từ `notifications` |
| QT-01 | Quản trị | Xem dashboard tổng quan | Thống kê sản phẩm, danh mục, cảnh báo giá và người dùng đã xác thực |
| QT-02 | Quản trị | Quản lý danh mục | Thêm, sửa, xóa danh mục và icon |
| QT-03 | Quản trị | Quản lý sản phẩm | Thêm, sửa, xóa sản phẩm, gắn danh mục và mô tả |
| QT-04 | Quản trị | Kiểm tra trùng link sản phẩm | Dựa trên mã sản phẩm theo sàn, URL chuẩn hóa và `url_hash` |
| QT-05 | Quản trị | Cảnh báo nghi trùng sản phẩm | Dựa trên tên chuẩn hóa và điểm tương đồng; quản trị viên quyết định xử lý |
| QT-06 | Quản trị | Quản lý link sàn | Thêm, sửa, xóa, bật/tắt link Tiki/Shopee/Lazada |
| QT-07 | Quản trị | Theo dõi trạng thái link | Có trang tổng quan link sàn, lọc theo sàn và trạng thái khả dụng |
| QT-08 | Quản trị | Quản lý bot/crawler | Chạy Tiki, Shopee, Lazada và matcher từ giao diện admin nếu được cấu hình |
| QT-09 | Quản trị | Theo dõi cảnh báo giá | Xem danh sách cảnh báo giá của người dùng trong trang admin |
| BOT-01 | Bot/crawler | Lấy danh sách link cần quét | Truy vấn `platform_links` theo sàn, `is_active`, `next_scrape_at`, `blocked_until` |
| BOT-02 | Bot/crawler | Cập nhật giá hiện tại | Ghi vào `current_price`, `original_price` nếu có |
| BOT-03 | Bot/crawler | Lưu lịch sử giá | Thêm dữ liệu vào `price_history` khi lấy giá thành công |
| BOT-04 | Bot/crawler | Cập nhật trạng thái link | Cập nhật `status`, `availability_status`, `error_message`, `last_checked_at` |
| BOT-05 | Bot/crawler | Retry/cooldown khi lỗi | Sử dụng `retry_count`, `consecutive_failures`, `next_scrape_at`, `blocked_until` |
| BOT-06 | Bot/crawler | Ghi log/lock file | Dùng `storage/bot_locks` và log/debug để kiểm soát tiến trình |
| TB-01 | Email/thông báo | Gửi OTP | Gửi qua `MailService::sendOTP()` khi email được cấu hình |
| TB-02 | Email/thông báo | Gửi email cảnh báo giá | Thực hiện qua `cron_send_alerts.php` và `MailService::sendPriceAlert()` nếu cấu hình email hoạt động |
| TB-03 | Email/thông báo | Tạo thông báo web | Ghi vào bảng `notifications` khi cảnh báo giá được xử lý |

## 3.5 Sơ đồ phân rã chức năng của hệ thống

[Hình 3.2. Sơ đồ phân rã chức năng của hệ thống]

Sơ đồ phân rã chức năng chia hệ thống thành bốn nhóm chức năng chính: chức năng người dùng, chức năng quản trị, chức năng bot/crawler và chức năng email/thông báo. Cách phân nhóm này bám theo các tác nhân thực tế trong hệ thống và tránh đưa quá nhiều chức năng nhỏ vào một sơ đồ duy nhất.

Nhóm chức năng người dùng gồm đăng ký, xác thực OTP, đăng nhập, đăng xuất, tìm kiếm/lọc/sắp xếp sản phẩm, xem chi tiết sản phẩm, so sánh giá theo sàn, xem lịch sử giá, đặt/hủy cảnh báo giá, xem thông báo và danh sách theo dõi.

Nhóm chức năng quản trị gồm quản lý danh mục, quản lý sản phẩm, kiểm tra trùng link, cảnh báo nghi trùng sản phẩm, quản lý link sàn, theo dõi trạng thái link, quản lý bot/crawler và theo dõi cảnh báo giá. Hệ thống không trình bày quản lý người dùng như một chức năng CRUD chính vì project hiện chưa có module quản lý người dùng đầy đủ.

Nhóm chức năng bot/crawler gồm lấy danh sách link cần quét, cập nhật giá hiện tại, lưu lịch sử giá, cập nhật trạng thái link, retry/cooldown khi lỗi và ghi log/lock file. Nhóm này hoạt động tách biệt với giao diện người dùng nhưng ghi kết quả về cơ sở dữ liệu để website hiển thị.

Nhóm chức năng email/thông báo gồm gửi OTP, gửi email cảnh báo giá khi cấu hình email hoạt động và tạo thông báo web để người dùng xem trong hệ thống.

## 3.6 Yêu cầu phi chức năng

Bảng 3.3. Danh sách yêu cầu phi chức năng của hệ thống

| Yêu cầu | Nội dung | Liên hệ với hệ thống |
| --- | --- | --- |
| Dễ sử dụng | Giao diện cần rõ ràng, thao tác tìm kiếm, xem chi tiết và đặt cảnh báo không phức tạp | Trang chủ, tìm kiếm, chi tiết sản phẩm và danh sách theo dõi được tổ chức theo các khu vực chức năng cụ thể |
| Chính xác | Dữ liệu giá phải được cập nhật từ link tương ứng và chỉ xem là hợp lệ khi link ở trạng thái thành công, còn hoạt động | Các truy vấn hiển thị giá dùng điều kiện `status = 1`, `availability_status = 'active'`, `current_price > 0` |
| Bảo mật | Chức năng cá nhân và quản trị phải kiểm tra đăng nhập, mật khẩu cần được lưu an toàn | Mật khẩu dùng `password_hash`; controller admin kiểm tra `user_role = admin` |
| Ổn định | Hệ thống cần xử lý được lỗi link, lỗi truy cập, timeout hoặc dữ liệu không hợp lệ | `platform_links` lưu `status`, `availability_status`, `error_message`, `retry_count`, `blocked_until` |
| Dễ bảo trì | Mã nguồn cần tách theo vai trò xử lý để dễ sửa đổi | Project tổ chức theo controller, model, view, service, helper và thư mục `crawlers/` |
| Khả năng mở rộng | Có thể bổ sung sàn hoặc trường dữ liệu mới nếu cần | Dữ liệu link được tách theo `platform_links`; crawler theo từng sàn được tổ chức thành script riêng |
| Kiểm soát lỗi crawler | Khi crawler lỗi, hệ thống cần ghi nhận trạng thái thay vì cập nhật dữ liệu không chắc chắn | Crawler có retry/cooldown, lock file, log/debug và trạng thái link |
| Khả năng vận hành định kỳ | Hệ thống cần hỗ trợ chạy crawler và kiểm tra cảnh báo theo lịch | Có file `.bat`, Windows Task Scheduler và `cron_send_alerts.php` |

## 3.7 Sơ đồ use case của hệ thống

### 3.7.1 Sơ đồ use case tổng quát

[Hình 3.3. Sơ đồ use case tổng quát của hệ thống]

Sơ đồ use case tổng quát thể hiện các tác nhân chính và các nhóm chức năng lớn của hệ thống. Người dùng chưa đăng nhập có thể xem thông tin công khai và thực hiện đăng ký/đăng nhập. Người dùng đã đăng nhập có thêm các chức năng theo dõi giá và thông báo. Quản trị viên quản lý dữ liệu nền và bot/crawler. Bot thu thập dữ liệu cập nhật giá, lịch sử giá và trạng thái link. Hệ thống gửi email/thông báo xử lý OTP và cảnh báo giá.

### 3.7.2 Sơ đồ use case nhóm chức năng người dùng

[Hình 3.4. Sơ đồ use case nhóm chức năng người dùng]

Sơ đồ use case nhóm chức năng người dùng được tách riêng để tránh làm sơ đồ tổng quát quá rối. Người dùng chưa đăng nhập có thể xem trang chủ, tìm kiếm/lọc/sắp xếp sản phẩm, xem chi tiết sản phẩm, đăng ký và đăng nhập. Sau khi đăng nhập, người dùng có thể đăng xuất, đặt hoặc cập nhật cảnh báo giá, hủy cảnh báo giá, xem danh sách theo dõi và xem/đọc thông báo.

### 3.7.3 Sơ đồ use case nhóm chức năng quản trị

[Hình 3.5. Sơ đồ use case nhóm chức năng quản trị]

Sơ đồ use case nhóm chức năng quản trị thể hiện các thao tác của quản trị viên trong khu vực admin. Quản trị viên có thể đăng nhập trang quản trị, xem dashboard, quản lý danh mục, quản lý sản phẩm, kiểm tra trùng link, xử lý cảnh báo nghi trùng sản phẩm, quản lý link sàn, theo dõi trạng thái link, quản lý bot/crawler và theo dõi cảnh báo giá của người dùng.

## 3.8 Luồng xử lý chính của hệ thống

Luồng đăng ký và xác thực OTP bắt đầu khi người dùng nhập email, mật khẩu và xác nhận mật khẩu. Hệ thống kiểm tra định dạng email, kiểm tra mật khẩu nhập lại, tạo tài khoản ở trạng thái chưa xác thực và sinh mã OTP. Nếu cấu hình email hoạt động, hệ thống gửi OTP đến email người dùng. Người dùng nhập OTP, hệ thống kiểm tra mã và thời hạn, sau đó cập nhật trạng thái tài khoản thành đã xác thực.

Luồng đăng nhập bắt đầu khi người dùng nhập email và mật khẩu. Hệ thống kiểm tra thông tin đăng nhập, kiểm tra trạng thái xác thực và lưu thông tin người dùng vào session nếu hợp lệ. Nếu tài khoản có vai trò `admin`, người dùng được chuyển đến khu vực quản trị; nếu là tài khoản thường, người dùng được chuyển về trang người dùng.

Luồng tìm kiếm, lọc và sắp xếp sản phẩm bắt đầu từ form tìm kiếm trên trang chủ hoặc trang kết quả. Người dùng có thể nhập từ khóa, chọn danh mục, chọn sàn, nhập khoảng giá và chọn kiểu sắp xếp. Controller nhận tham số, gọi model truy vấn dữ liệu sản phẩm và trả về danh sách sản phẩm phù hợp. Với một số thao tác lọc, giao diện sử dụng AJAX/fetch để cập nhật kết quả mà không cần tải lại toàn bộ trang.

Luồng xem chi tiết và so sánh giá bắt đầu khi người dùng chọn một sản phẩm. Hệ thống lấy thông tin sản phẩm, danh sách link sàn đang hoạt động, thông số sản phẩm nếu có, lịch sử giá, thống kê giá và sản phẩm liên quan. Các link sàn được sắp xếp theo mức giá hợp lệ để người dùng dễ nhận biết nơi có giá thấp hơn. Lịch sử giá được hiển thị bằng biểu đồ.

Luồng đặt cảnh báo giá yêu cầu người dùng đã đăng nhập. Người dùng nhập mức giá mong muốn tại trang chi tiết sản phẩm. Hệ thống lưu hoặc cập nhật bản ghi trong `price_alerts`, đặt `is_notified = 0` để cảnh báo có thể được xử lý lại khi có giá mới phù hợp. Người dùng có thể hủy cảnh báo từ trang chi tiết hoặc danh sách theo dõi.

Luồng quản trị thêm sản phẩm và link sàn bắt đầu khi quản trị viên nhập tên sản phẩm, mô tả, danh mục và các URL Tiki/Shopee/Lazada nếu có. Hệ thống chuẩn hóa tên sản phẩm, kiểm tra trùng link bằng mã sản phẩm theo sàn, URL chuẩn hóa và `url_hash`. Nếu phát hiện link đã tồn tại, hệ thống hiển thị cảnh báo để tránh lưu trùng. Nếu tên sản phẩm có khả năng giống sản phẩm đã có, hệ thống hiển thị danh sách nghi trùng để quản trị viên quyết định gắn link vào sản phẩm cũ hoặc tiếp tục tạo sản phẩm mới.

Luồng crawler cập nhật giá bắt đầu khi bot được chạy từ giao diện admin, file `.bat` hoặc lịch chạy của hệ điều hành. Crawler lấy danh sách link đủ điều kiện từ `platform_links`, thực hiện thu thập dữ liệu theo phương pháp phù hợp với từng sàn, sau đó cập nhật giá hiện tại và trạng thái link. Khi lấy giá thành công, hệ thống thêm dữ liệu vào `price_history`; khi gặp lỗi, hệ thống ghi `error_message`, tăng số lần lỗi và hẹn thời điểm thử lại.

Luồng gửi cảnh báo giá được xử lý tách riêng với luồng crawler. Sau khi dữ liệu giá đã được cập nhật, `cron_send_alerts.php` kiểm tra các cảnh báo chưa gửi trong `price_alerts`. Nếu có link đang hoạt động, giá hợp lệ và giá hiện tại nhỏ hơn hoặc bằng mức người dùng đặt, hệ thống gửi email cảnh báo khi cấu hình email hoạt động, cập nhật `is_notified = 1` và tạo thông báo web trong bảng `notifications`.

## 3.9 Thiết kế cơ sở dữ liệu

[Hình 3.6. Sơ đồ cơ sở dữ liệu của hệ thống]

Cơ sở dữ liệu của hệ thống được thiết kế theo hướng tách sản phẩm chung khỏi link sản phẩm trên từng sàn. Bảng `products` lưu thông tin đại diện cho sản phẩm trong website, trong khi bảng `platform_links` lưu từng nguồn dữ liệu cụ thể trên Tiki, Shopee hoặc Lazada. Cách thiết kế này phù hợp với yêu cầu một sản phẩm có thể có nhiều link bán ở nhiều sàn khác nhau và mỗi link có lịch sử giá, trạng thái cập nhật riêng.

Bảng 3.4. Mô tả các bảng dữ liệu chính của hệ thống

| Bảng | Vai trò | Trường quan trọng | Quan hệ chính/Ghi chú |
| --- | --- | --- | --- |
| `categories` | Lưu danh mục sản phẩm | `id`, `name`, `icon`, `created_at` | Một danh mục có thể có nhiều sản phẩm |
| `products` | Lưu sản phẩm chung trong website | `id`, `name`, `normalized_name`, `description`, `category_id`, `thumbnail_url`, `created_at` | Thuộc `categories`; có nhiều link sàn, thông số, cảnh báo và thông báo |
| `product_specifications` | Lưu thông số chi tiết sản phẩm dạng nhiều dòng | `product_id`, `group_name`, `spec_name`, `spec_value`, `display_order`, `source_platform` | Thuộc `products`; phục vụ trang chi tiết sản phẩm |
| `users` | Lưu tài khoản người dùng và quản trị viên | `email`, `password_hash`, `role`, `is_verified`, `otp_code`, `otp_expires_at` | Có nhiều cảnh báo giá và thông báo |
| `platform_links` | Lưu link sản phẩm theo từng sàn | `product_id`, `platform_name`, `product_url`, `platform_product_id`, `normalized_url`, `url_hash`, `current_price`, `status`, `availability_status`, `is_active` | Thuộc `products`; có nhiều lịch sử giá |
| `price_history` | Lưu lịch sử giá theo từng link sàn | `link_id`, `price`, `scraped_at` | Thuộc `platform_links` |
| `price_alerts` | Lưu mức giá mong muốn của người dùng | `user_id`, `product_id`, `target_price`, `is_notified`, `created_at` | Liên kết `users` và `products`; mỗi user có một cảnh báo cho một sản phẩm |
| `notifications` | Lưu thông báo web cho người dùng | `user_id`, `product_id`, `message`, `is_read`, `created_at` | Liên kết `users` và `products` |
| `product_duplicate_overrides` | Ghi nhận trường hợp quản trị viên vẫn tạo sản phẩm khi có cảnh báo nghi trùng | `admin_user_id`, `product_id`, `product_name`, `normalized_name`, `candidate_product_ids`, `reason`, `created_at` | Bảng log bổ sung qua migration, không phải bảng lõi; cần xác nhận trong bản database cuối nếu cập nhật lại schema chính |

Các quan hệ chính trong cơ sở dữ liệu gồm: `products.category_id` tham chiếu `categories.id`; `platform_links.product_id` tham chiếu `products.id`; `price_history.link_id` tham chiếu `platform_links.id`; `product_specifications.product_id` tham chiếu `products.id`; `price_alerts.user_id` tham chiếu `users.id`; `price_alerts.product_id` tham chiếu `products.id`; `notifications.user_id` tham chiếu `users.id`; `notifications.product_id` tham chiếu `products.id`. Các quan hệ này giúp hệ thống đảm bảo khi sản phẩm hoặc link bị xóa thì dữ liệu phụ thuộc có thể được xử lý theo ràng buộc khóa ngoại.

Bảng 3.5. Các trường trạng thái quan trọng của bảng `platform_links`

| Trường | Vai trò | Ghi chú |
| --- | --- | --- |
| `status` | Trạng thái kỹ thuật của lần cập nhật | Gồm các giá trị như chờ xử lý, thành công, không có giá, lỗi, yêu cầu xác minh/đăng nhập |
| `availability_status` | Trạng thái nghiệp vụ của link sản phẩm | Có các giá trị như `unknown`, `active`, `out_of_stock`, `temporarily_unavailable`, `discontinued`, `invalid_url`, `fetch_error`, `blocked_or_captcha`; mã `blocked_or_captcha` được diễn giải trong luận văn là yêu cầu xác minh hoặc lỗi truy cập |
| `error_message` | Lưu mô tả lỗi ngắn | Giúp quản trị viên biết nguyên nhân link chưa cập nhật được |
| `is_active` | Cho biết link có được bot quét hay không | Nếu tắt, bot bỏ qua link này |
| `last_scraped_at` | Thời điểm cập nhật giá gần nhất | Dùng để theo dõi lần quét thành công hoặc lần chạy gần nhất tùy crawler cập nhật |
| `last_checked_at` | Thời điểm kiểm tra link gần nhất | Phục vụ trang tổng quan trạng thái link |
| `next_scrape_at` | Thời điểm dự kiến quét tiếp theo | Giúp bot chọn link đủ điều kiện chạy |
| `next_check_at` | Thời điểm dự kiến kiểm tra lại trạng thái | Phục vụ theo dõi link cần kiểm tra |
| `blocked_until` | Thời điểm tạm hoãn quét khi gặp yêu cầu xác minh hoặc lỗi truy cập | Bot bỏ qua link cho đến khi hết thời gian tạm hoãn |
| `retry_count` | Số lần thử lại sau lỗi | Hỗ trợ tăng thời gian chờ giữa các lần quét |
| `consecutive_failures` | Số lần lỗi liên tiếp | Hỗ trợ xác định link có vấn đề kéo dài |
| `scrape_priority` | Độ ưu tiên khi lấy link cần quét | Giá trị nhỏ hơn có thể được ưu tiên hơn tùy truy vấn crawler |

Việc thiết kế các trường trạng thái trong `platform_links` giúp hệ thống không chỉ lưu giá hiện tại, mà còn phản ánh được chất lượng của từng nguồn dữ liệu. Nhờ đó, giao diện người dùng có thể chỉ hiển thị giá hợp lệ, còn khu vực quản trị có thể theo dõi link nào đang hoạt động, link nào bị lỗi hoặc cần kiểm tra lại.

## 3.10 Thiết kế kiến trúc hệ thống

[Hình 3.7. Kiến trúc tổng quát của hệ thống]

Hệ thống được xây dựng theo kiến trúc web PHP kết hợp mô hình MVC ở mức project. File `index.php` đóng vai trò router, nhận tham số `role`, `controller`, `action`, kiểm tra action hợp lệ, nạp controller tương ứng và truyền kết nối cơ sở dữ liệu cho controller.

Tầng Controller xử lý yêu cầu từ người dùng và quản trị viên. Nhóm controller người dùng gồm `ProductController` và `AuthController`, phụ trách trang chủ, tìm kiếm, chi tiết sản phẩm, cảnh báo giá, thông báo, đăng ký, OTP và đăng nhập. Nhóm controller quản trị gồm `DashboardController`, `AdminCategoryController`, `AdminProductController`, `AdminPlatformController` và `BotController`, phụ trách dashboard, danh mục, sản phẩm, link sàn, trạng thái link, cảnh báo giá và chạy bot.

Tầng Model làm việc với cơ sở dữ liệu. `ProductModel` xử lý dữ liệu sản phẩm, link sàn, giá, lịch sử giá, thông số, cảnh báo giá và chống trùng. `CategoryModel` xử lý danh mục. `UserModel` xử lý tài khoản, OTP, thông báo và danh sách theo dõi. Các helper như `ProductMatchHelper` hỗ trợ chuẩn hóa tên sản phẩm, chuẩn hóa URL, trích mã sản phẩm theo sàn, tạo `url_hash` và tính điểm tương đồng tên sản phẩm.

Tầng View chứa giao diện người dùng và quản trị. Các view người dùng gồm trang chủ, kết quả tìm kiếm, chi tiết sản phẩm, đăng nhập, đăng ký, xác thực OTP và danh sách theo dõi. Các view quản trị gồm dashboard, danh mục, sản phẩm, cảnh báo nghi trùng, quản lý link sàn, tổng quan link sàn, bot và cảnh báo giá.

Cơ sở dữ liệu MySQL là nơi lưu dữ liệu trung tâm của hệ thống. Website PHP đọc dữ liệu từ MySQL để hiển thị cho người dùng và quản trị viên. Các crawler Python cũng kết nối đến MySQL để lấy danh sách link cần quét và cập nhật kết quả sau khi thu thập dữ liệu.

Các crawler Python được đặt trong thư mục `crawlers/`. `tiki_scraper.py` ưu tiên hướng request/API cho Tiki; `shopee_crawler.py` và `lazada_crawler.py` dùng trình duyệt tự động để xử lý trang động; `multi_platform_matcher.py` hỗ trợ tìm/gắn link còn thiếu theo mức độ phù hợp. `app_config.py` đọc cấu hình môi trường, còn `bot_lock.py` cung cấp cơ chế lock file để hạn chế chạy trùng tiến trình.

Phần vận hành định kỳ được tách thành hai nhóm. Nhóm file `.bat` và Windows Task Scheduler dùng để kích hoạt crawler theo lịch, cập nhật giá, trạng thái link và lịch sử giá. Riêng `cron_send_alerts.php` dùng để kiểm tra cảnh báo giá sau khi dữ liệu đã được cập nhật, gửi email cảnh báo khi cấu hình email hoạt động và tạo thông báo web trong hệ thống. Việc tách hai phần này giúp phân biệt rõ nhiệm vụ thu thập dữ liệu và nhiệm vụ gửi cảnh báo.

## 3.11 Thiết kế giao diện mức khái quát

Thiết kế giao diện người dùng hướng đến việc giúp người dùng nhanh chóng tìm được sản phẩm, xem mức giá theo từng sàn và theo dõi biến động giá. Trang chủ hiển thị ô tìm kiếm, danh mục, các nhóm sản phẩm và lối vào danh sách theo dõi. Trang tìm kiếm cho phép lọc theo danh mục, sàn, khoảng giá và sắp xếp kết quả. Trang chi tiết sản phẩm tập trung vào thông tin sản phẩm, bảng giá theo sàn, biểu đồ lịch sử giá, thông số sản phẩm nếu có, chức năng đặt cảnh báo giá và sản phẩm liên quan.

Trang danh sách theo dõi dành cho người dùng đã đăng nhập, hiển thị các sản phẩm người dùng đã đặt cảnh báo giá, mức giá mục tiêu, giá hiện tại thấp nhất và trạng thái đã đạt hay chưa đạt mức mong muốn. Người dùng có thể truy cập nhanh trang chi tiết sản phẩm hoặc hủy theo dõi từ giao diện này.

Giao diện quản trị được thiết kế theo hướng tập trung vào kiểm soát dữ liệu. Dashboard hiển thị số liệu tổng quan và các lối tắt đến chức năng chính. Trang quản lý danh mục cho phép thêm, sửa, xóa danh mục. Trang quản lý sản phẩm cho phép thêm, sửa, xóa sản phẩm và mở trang quản lý link sàn của từng sản phẩm.

Trang quản lý link sàn hiển thị các link Tiki, Shopee, Lazada của một sản phẩm, trạng thái hoạt động, giá hiện tại, thời điểm quét, thời điểm kiểm tra và lỗi nếu có. Trang tổng quan link sàn cho phép quản trị viên lọc theo sàn và trạng thái khả dụng để phát hiện nhóm link cần kiểm tra. Trang quản lý bot hiển thị các crawler có thể chạy từ giao diện admin và kết quả log sau khi chạy. Trang cảnh báo giá giúp quản trị viên theo dõi các cảnh báo đã được người dùng thiết lập.

Ở Chương 3, giao diện chỉ được mô tả ở mức khái quát để làm rõ cách hệ thống đáp ứng yêu cầu chức năng. Các ảnh chụp giao diện chi tiết sẽ được trình bày ở Chương 5 khi đánh giá kết quả thực hiện.

---

# Chương 4. NGHIÊN CỨU KỸ THUẬT VÀ TRIỂN KHAI HỆ THỐNG

Ghi chú chung: Đây là chương trọng tâm. Chương này trình bày quá trình triển khai thật, thử nghiệm thật, lỗi thật và cách xử lý; không lặp lại lý thuyết đã nêu ở Chương 2.

## 4.1 Môi trường triển khai

[Bảng 4.1. Môi trường và công cụ triển khai hệ thống]

Ghi chú cần viết: Trình bày XAMPP/Apache, PHP, MySQL, Python, Chrome/ChromeDriver, Windows Task Scheduler và các thư viện Python.

## 4.2 Cấu trúc mã nguồn sau khi tổ chức lại

[Bảng 4.2. Cấu trúc thư mục mã nguồn của hệ thống]

Ghi chú cần viết: Trình bày các thư mục `config`, `controllers`, `models`, `views`, `helpers`, `services`, `crawlers`, `database`, `scripts`, `storage`; nhấn mạnh crawler đã được chuyển vào `crawlers/`.

## 4.3 Quá trình xây dựng website theo mô hình MVC

Ghi chú cần viết: Trình bày cách xây dựng router, controller, model, view, phân quyền user/admin và lý do tổ chức lại theo MVC giúp dễ bảo trì.

## 4.4 Nghiên cứu và triển khai crawler Tiki

Ghi chú cần viết: Trình bày vấn đề cần giải quyết, các cách đã thử, lý do chọn API, cách lấy giá/ảnh/thông số và kết quả cải thiện.

## 4.5 Nghiên cứu và triển khai crawler Shopee

Ghi chú cần viết: Trình bày vấn đề dữ liệu động, các cách đã thử, khó khăn khi có yêu cầu xác minh; cách xử lý gồm ghi trạng thái, log/debug, cooldown, batch nhỏ và kết quả sau xử lý.

## 4.6 Nghiên cứu và triển khai crawler Lazada

Ghi chú cần viết: Trình bày vấn đề dữ liệu động, cách dùng trình duyệt tự động, cách xử lý sản phẩm hết hàng/tạm ngừng/lỗi link và kết quả cải thiện.

## 4.7 Cơ chế chuẩn hóa dữ liệu và chống trùng sản phẩm

Ghi chú cần viết: Trình bày chuẩn hóa tên sản phẩm, URL, tách mã sản phẩm, tạo mã băm URL, kiểm tra link trùng và cảnh báo sản phẩm tương tự.

## 4.8 Cơ chế quản lý trạng thái link sản phẩm

Ghi chú cần viết: Trình bày vai trò của `status`, `availability_status`, `error_message`, `last_scraped_at`, `next_scrape_at`, `blocked_until`, `retry_count`, `consecutive_failures`.

## 4.9 Cơ chế xử lý lỗi crawler, retry, cooldown và lock file

Ghi chú cần viết: Trình bày retry, cooldown, ghi log, error message và lock file để tránh nhiều bot cùng loại chạy đồng thời.

## 4.10 Cơ chế chạy bot định kỳ

Ghi chú cần viết: Trình bày file `.bat`, Windows Task Scheduler, log trong `storage/bot_logs` và cách kiểm tra scheduled flow.

## 4.11 Cải tiến trải nghiệm người dùng bằng AJAX/fetch

Ghi chú cần viết: Trình bày tìm kiếm/lọc sản phẩm, đặt cảnh báo giá, hủy cảnh báo giá và quản lý link sàn nếu có AJAX/fetch.

## 4.12 Kiểm tra sau khi chuyển crawler vào thư mục `crawlers/`

Ghi chú cần viết: Trình bày việc cập nhật đường dẫn trong BotController, file `.bat`, import Python, app_config, log và lock file.

## 4.13 Các vấn đề đã gặp và cách xử lý

[Bảng 4.3. Các vấn đề trong quá trình triển khai và cách xử lý]

Ghi chú cần viết: Trình bày các vấn đề như link trùng, sản phẩm nhập trùng, Tiki cần lấy thông số, Shopee phát sinh yêu cầu xác minh, bot chạy trùng, chuyển crawler vào thư mục mới và cách xử lý tương ứng.

---

# Chương 5. KẾT QUẢ THỰC HIỆN, KIỂM THỬ, ĐÁNH GIÁ VÀ KIẾN NGHỊ

## 5.1 Tổng quan kết quả thực hiện

Ghi chú cần viết: Tóm tắt các phần đã hoàn thành gồm website người dùng, website quản trị, crawler ba sàn, cảnh báo giá, lịch sử giá, quản lý trạng thái link và chạy bot theo lịch.

## 5.2 Kết quả giao diện người dùng

[Hình 5.1. Giao diện trang chủ]

[Hình 5.2. Giao diện tìm kiếm sản phẩm]

[Hình 5.3. Giao diện chi tiết sản phẩm]

[Hình 5.4. Giao diện biểu đồ lịch sử giá]

[Hình 5.5. Giao diện danh sách theo dõi giá]

Ghi chú cần viết: Mô tả ngắn chức năng thể hiện trong từng hình.

## 5.3 Kết quả giao diện quản trị

[Hình 5.6. Giao diện dashboard quản trị]

[Hình 5.7. Giao diện quản lý danh mục]

[Hình 5.8. Giao diện quản lý sản phẩm]

[Hình 5.9. Giao diện cảnh báo sản phẩm tương tự]

[Hình 5.10. Giao diện quản lý link sàn]

[Hình 5.11. Giao diện tổng quan link sàn]

[Hình 5.12. Giao diện quản lý bot]

Ghi chú cần viết: Mô tả ngắn chức năng thể hiện trong từng hình.

## 5.4 Kiểm thử chức năng

[Bảng 5.1. Bảng kiểm thử chức năng chính của hệ thống]

Ghi chú cần viết: Trình bày các test case cho đăng ký, xác thực OTP, đăng nhập, tìm kiếm, xem chi tiết, đặt cảnh báo, quản lý sản phẩm, quản lý link sàn, crawler, scheduled flow và cảnh báo giá.

## 5.5 Kết quả chạy crawler

Ghi chú cần viết: Trình bày kết quả theo từng sàn Tiki, Shopee, Lazada; nêu rõ trường hợp Shopee có thể ghi trạng thái lỗi/xác minh thay vì cập nhật giá.

## 5.6 Kết quả chạy scheduled flow

Ghi chú cần viết: Trình bày kết quả file `.bat`, Windows Task Scheduler, log và lock file.

## 5.7 Đánh giá ưu điểm

Ghi chú cần viết: Nhận xét hệ thống có theo dõi/so sánh giá, lịch sử giá, cảnh báo giá, crawler ba sàn, quản lý trạng thái link và giao diện user/admin.

## 5.8 Hạn chế

Ghi chú cần viết: Nêu các hạn chế như Shopee có thể yêu cầu xác minh, dữ liệu phụ thuộc cấu trúc website/API của sàn, chưa triển khai server thật, chưa hỗ trợ nhiều sàn khác, chưa có dự đoán xu hướng giá.

## 5.9 Kết luận

Ghi chú cần viết: Tổng kết kết quả chính của đồ án và mức độ đáp ứng mục tiêu ban đầu.

## 5.10 Kiến nghị và hướng phát triển

Ghi chú cần viết: Đề xuất tối ưu crawler, bổ sung sàn, thêm kênh cảnh báo, gợi ý thời điểm mua, triển khai server thật và bổ sung thống kê nâng cao.

---

# TÀI LIỆU THAM KHẢO

Ghi chú: Chỉ bổ sung tài liệu thật sự được trích dẫn trong nội dung luận văn. Không tự thêm nguồn nếu chưa sử dụng hoặc chưa kiểm chứng.

Nhóm tài liệu cần bổ sung sau:

- [CẦN BỔ SUNG TÀI LIỆU THAM KHẢO] Web Scraping và Web Crawling.
- [CẦN BỔ SUNG TÀI LIỆU THAM KHẢO] API và HTTP request nếu có mô tả lý thuyết.
- [CẦN BỔ SUNG TÀI LIỆU THAM KHẢO] Selenium/ChromeDriver hoặc tài liệu chính thức tương ứng.
- [CẦN BỔ SUNG TÀI LIỆU THAM KHẢO] Mô hình MVC.
- [CẦN BỔ SUNG TÀI LIỆU THAM KHẢO] Cơ sở dữ liệu quan hệ/MySQL.
- [CẦN BỔ SUNG TÀI LIỆU THAM KHẢO] Chart.js nếu mô tả lý thuyết biểu đồ.
- [CẦN BỔ SUNG TÀI LIỆU THAM KHẢO] PHPMailer nếu mô tả phần gửi email.

---

# PHỤ LỤC

## Phụ lục 1. Hướng dẫn cài đặt

Ghi chú cần viết: Trình bày cách cài đặt môi trường, cấu hình `.env`, import database và chạy website ở mức đủ để tái hiện.

## Phụ lục 2. Cấu trúc database

Ghi chú cần viết: Có thể đưa schema rút gọn hoặc các bảng chính.

## Phụ lục 3. Đoạn mã nguồn tiêu biểu

Ghi chú cần viết: Chọn một số đoạn mã có ý nghĩa như router, crawler Tiki, xử lý trạng thái link, cảnh báo giá.

## Phụ lục 4. Biến cấu hình môi trường

Ghi chú cần viết: Mô tả các biến cấu hình quan trọng trong `.env.example`.

## Phụ lục 5. Log crawler mẫu

Ghi chú cần viết: Chèn log mẫu nếu có, chú ý không đưa thông tin nhạy cảm.

---

# GHI CHÚ KIỂM SOÁT NỘI DUNG

## Những ý cần bổ sung nguồn tham khảo

- Khái niệm Web Scraping, Web Crawling.
- Khái niệm API và HTTP request nếu trình bày lý thuyết.
- Selenium/ChromeDriver hoặc trình duyệt tự động.
- Mô hình MVC.
- Cơ sở dữ liệu quan hệ/MySQL.
- Chart.js nếu mô tả cơ sở biểu đồ.
- PHPMailer nếu mô tả thư viện gửi email.
- Số liệu hoặc nhận định về thị trường thương mại điện tử nếu sử dụng.

## Những đoạn có nguy cơ giống lý thuyết chung cần kiểm tra lại

- Định nghĩa Web Scraping/Web Crawling.
- Định nghĩa API.
- Giải thích mô hình MVC.
- Giải thích cơ sở dữ liệu quan hệ.
- Phần tổng quan thương mại điện tử nếu lấy số liệu từ báo cáo bên ngoài.

## Những thông tin cần xác nhận để tránh viết sai thực tế

- Danh sách chức năng cuối cùng của website người dùng.
- Danh sách chức năng cuối cùng của trang quản trị.
- Crawler hiện tại lấy ổn định những trường dữ liệu nào ở từng sàn.
- Shopee/Lazada đang được ghi nhận trạng thái lỗi/xác minh như thế nào trong dữ liệu thật.
- Có sử dụng bảng `product_duplicate_overrides` trong database cuối cùng hay không.
- Danh sách hình ảnh giao diện sẽ chèn vào Chương 5.
- Bảng test case và kết quả kiểm thử thực tế.
- Log crawler mẫu có thể đưa vào phụ lục.
