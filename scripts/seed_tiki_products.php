<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ProductModel.php';

$dryRun = in_array('--dry-run', $argv ?? [], true);
$description = '';

$seedProducts = [
    [
        'category' => 'Phụ kiện công nghệ',
        'name' => 'Chuột Wireless Bluetooth Logitech MX Master 4 - Hàng Chính Hãng',
        'tiki_url' => 'https://tiki.vn/chuot-wireless-bluetooth-logitech-mx-master-4-hang-chinh-hang-p279121452.html',
    ],
    [
        'category' => 'Phụ kiện công nghệ',
        'name' => 'Chuột Bluetooth Silent Logitech M240 - Hàng chính hãng',
        'tiki_url' => 'https://tiki.vn/chuot-bluetooth-silent-logitech-m240-hang-chinh-hang-p278949196.html',
    ],
    [
        'category' => 'Phụ kiện công nghệ',
        'name' => 'Cáp sạc nhanh Type C to Type C Anker Zolo 240W - A8060 - Hàng Chính Hãng',
        'tiki_url' => 'https://tiki.vn/cap-sac-nhanh-type-c-to-type-c-anker-zolo-dai-1m-240w-a8060-hang-chinh-hang-p278516492.html',
    ],
    [
        'category' => 'Laptop - Máy tính',
        'name' => 'Apple Macbook Air 13 Inch M5 10CPU 8GPU 16GB 512GB - Hàng Chính Hãng',
        'tiki_url' => 'https://tiki.vn/apple-macbook-air-13-inch-m5-10cpu-8gpu-16gb-512gb-hang-chinh-hang-p279225805.html',
    ],
    [
        'category' => 'Laptop - Máy tính',
        'name' => 'MacBook Neo A18 Pro - Hàng Chính Hãng',
        'tiki_url' => 'https://tiki.vn/macbook-neo-a18-pro-hang-chinh-hang-p279212151.html',
    ],
    [
        'category' => 'Laptop - Máy tính',
        'name' => 'Màn hình Gaming Samsung Odyssey G5 G55C LS32CG552EEXXV 32 inch QHD 165Hz - Hàng chính hãng',
        'tiki_url' => 'https://tiki.vn/man-hinh-gaming-odyssey-g5-g55c-ls32cg552eexxv-32-inch-qhd-165hz-p279113664.html',
    ],
    [
        'category' => 'Thời trang',
        'name' => 'Áo thun nam cổ tròn NOVELTY vải Cotton co giãn dày dặn 210147N',
        'tiki_url' => 'https://tiki.vn/ao-thun-nam-co-tron-novelty-vai-cotton-co-gian-day-dan-210147n-p84285383.html?spid=84285385',
    ],
    [
        'category' => 'Thời trang',
        'name' => 'Quần Jean Nữ Ống Rộng Lưng Cao Aaa Jeans',
        'tiki_url' => 'https://tiki.vn/quan-jeans-ong-rong-lung-cao-aaa-jeans-p146957711.html?spid=252994176',
    ],
    [
        'category' => 'Thời trang',
        'name' => "Giày Thể Thao Nam Biti's",
        'tiki_url' => 'https://tiki.vn/giay-the-thao-nam-biti-s-p193043008.html?spid=193043034',
    ],
    [
        'category' => 'Nhà cửa - Đời sống',
        'name' => 'Bộ nồi Inox dập nguyên khối Elmich Trimax Classic EL-2110OL',
        'tiki_url' => 'https://tiki.vn/bo-noi-inox-dap-nguyen-khoi-elmich-trimax-classic-el-2110ol-size-18-20-24-chao-26cm-p195109412.html?spid=195109415',
    ],
    [
        'category' => 'Nhà cửa - Đời sống',
        'name' => 'Cốc giữ nhiệt Elmich inox 304 580ml EL3666',
        'tiki_url' => 'https://tiki.vn/coc-giu-nhiet-elmich-inox-304-580ml-el3666-p49865603.html?spid=49865607',
    ],
    [
        'category' => 'Điện lạnh',
        'name' => 'Máy lạnh Sharp Inverter 1.5 HP AH-X13CEWC - Hàng Chính Hãng',
        'tiki_url' => 'https://tiki.vn/may-lanh-sharp-1-5-hp-ah-x12new-hang-chinh-hang-p21780621.html?spid=21780667',
    ],
    [
        'category' => 'Điện lạnh',
        'name' => 'Tủ Lạnh Aqua 130 lít AQR-T150FA-BS',
        'tiki_url' => 'https://tiki.vn/tu-lanh-aqua-130-lit-aqr-t150fa-bs-p50560441.html?spid=148952824',
    ],
    [
        'category' => 'Điện lạnh',
        'name' => 'Máy giặt Aqua Inverter 8.5 kg AQD-A852J(BK) - Hàng chính hãng',
        'tiki_url' => 'https://tiki.vn/may-giat-aqua-inverter-8-5-kg-aqd-a852j-bk-hang-chinh-hang-chi-giao-hcm-p260220457.html?spid=260222121',
    ],
    [
        'category' => 'Tivi - Âm thanh',
        'name' => 'Smart Tivi LG QNED AI 4K 50 Inch 50QNED80ASA - Hàng Chính Hãng',
        'tiki_url' => 'https://tiki.vn/smart-tivi-lg-qned-ai-4k-50-inch-50qned80asa-hang-chinh-hang-p278531000.html?spid=278531001',
    ],
    [
        'category' => 'Tivi - Âm thanh',
        'name' => 'Smart Tivi LG AI 4K 65 Inch 65UA8450PSA - Hàng Chính Hãng',
        'tiki_url' => 'https://tiki.vn/smart-tivi-lg-ai-4k-65-inch-65ua8450psa-hang-chinh-hang-p278138206.html',
    ],
    [
        'category' => 'Tivi - Âm thanh',
        'name' => 'Google Tivi TCL QLED Full HD 32 Inch 32S5K - Hàng chính hãng',
        'tiki_url' => 'https://tiki.vn/google-tivi-tcl-qled-full-hd-32-inch-32s5k-hang-chinh-hang-p277989480.html',
    ],
    [
        'category' => 'Sức khỏe - Y tế',
        'name' => 'Nhiệt Kế Điện Tử Omron TH839S Đo Tai',
        'tiki_url' => 'https://tiki.vn/nhiet-ke-dien-tu-omron-th839s-do-tai-100544824-p435390.html?spid=2413175',
    ],
    [
        'category' => 'Sức khỏe - Y tế',
        'name' => 'Máy Đo Huyết Áp OMRON HEM-8712 - Công Nghệ IntelliSense',
        'tiki_url' => 'https://tiki.vn/may-do-huyet-ap-omron-hem-8712-cong-nghe-intellisense-p97002952.html?spid=161268694',
    ],
    [
        'category' => 'Sức khỏe - Y tế',
        'name' => 'Máy đo nồng độ oxy trong máu và nhịp tim SpO2 Beurer PO30',
        'tiki_url' => 'https://tiki.vn/may-do-nong-do-oxy-trong-mau-va-nhip-tim-spo2-beurer-po30-p156726707.html?spid=190700262',
    ],
    [
        'category' => 'Mẹ và bé',
        'name' => 'Thùng SBPS Nutifood GrowPLUS+ ít đường Suy Dinh Dưỡng 48 Hộp x 110ml',
        'tiki_url' => 'https://tiki.vn/thung-sbps-nutifood-growplus-it-duong-suy-dinh-duong-tang-can-tang-chieu-cao-48-hop-x-110ml-p278695700.html',
    ],
    [
        'category' => 'Mẹ và bé',
        'name' => 'Hộp Sữa bột Vinamilk Dielac Alpha 1 hộp thiếc 900g',
        'tiki_url' => 'https://tiki.vn/sua-bot-dielac-alpha-1-ht-900g-p278275289.html',
    ],
    [
        'category' => 'Mẹ và bé',
        'name' => 'Sữa bột Nutifood GrowPLUS+ Cao Lớn Vượt Trội trên 1 tuổi Lon 800g',
        'tiki_url' => 'https://tiki.vn/sua-bot-nutifood-growplus-cao-lon-vuot-troi-tren-1-tuoi-lon-800g-p277596987.html',
    ],
    [
        'category' => 'Sách - Văn phòng phẩm',
        'name' => 'Sách Đắc Nhân Tâm',
        'tiki_url' => 'https://tiki.vn/sach-dac-nhan-tam-p276388836.html?spid=276388840',
    ],
    [
        'category' => 'Sách - Văn phòng phẩm',
        'name' => 'Sách Nhà Giả Kim Tái Bản 2020',
        'tiki_url' => 'https://tiki.vn/sach-nha-gia-kim-tai-ban-2020-p52789367.html?spid=52789368',
    ],
    [
        'category' => 'Sách - Văn phòng phẩm',
        'name' => 'Combo 10 cây Bút Bi Thiên Long TL-047',
        'tiki_url' => 'https://tiki.vn/combo-10-cay-but-bi-thien-long-tl-047-p243118179.html?spid=243118182',
    ],
    [
        'category' => 'Công nghiệp - Khoa học',
        'name' => "Máy khoan vặn vít dùng pin 18V 1/4'' Bosch GDR 180-LI - Hàng Chính Hãng",
        'tiki_url' => 'https://tiki.vn/may-khoan-van-vit-dung-pin-18v-1-4-6-35mm-bosch-gdr-180-li-hang-chinh-hang-p217263607.html?spid=225920688',
    ],
    [
        'category' => 'Công nghiệp - Khoa học',
        'name' => 'Kính hiển vi bỏ túi đa năng MICROBRITE PLUS MM-300',
        'tiki_url' => 'https://tiki.vn/kinh-hien-vi-bo-tui-da-nang-microbrite-plus-mm-300-phong-dai-60-120x-hang-chinh-hang-p23839834.html?spid=23839835',
    ],
    [
        'category' => 'Công nghiệp - Khoa học',
        'name' => 'Cân điện tử NiNDA A6',
        'tiki_url' => 'https://tiki.vn/can-dien-tu-ninda-a6-p59409423.html?spid=274608451',
    ],
    [
        'category' => 'Thể thao - Du lịch',
        'name' => 'Thảm Yoga TPE Procare',
        'tiki_url' => 'https://tiki.vn/tham-yoga-tpe-procare-p45814297.html?spid=64350082',
    ],
    [
        'category' => 'Thể thao - Du lịch',
        'name' => 'Vali kéo Kami 360 KAMILIANT',
        'tiki_url' => 'https://tiki.vn/vali-keo-kami-360-kamiliant-my-he-thong-4-banh-xe-doi-xoay-360-voi-nut-chan-khoa-keo-chong-trom-p79729017.html?spid=216129398',
    ],
    [
        'category' => 'Thể thao - Du lịch',
        'name' => 'Bình Giữ Nhiệt Locknlock Bucket Tumbler 540ml LHC4269NVY',
        'tiki_url' => 'https://tiki.vn/binh-giu-nhiet-locknlock-bucket-tumbler-lhc4269-540ml-p99868980.html?spid=99868986',
    ],
    [
        'category' => 'Máy ảnh - Máy quay phim',
        'name' => 'Camera Wifi Ngoài trời TP-Link Tapo C520WS 2K 4MP - Hàng chính hãng',
        'tiki_url' => 'https://tiki.vn/camera-wifi-ngoai-troi-tp-link-tapo-c520ws-do-phan-giai-2k-4mp-chong-chiu-thoi-tiet-ip66-co-mau-ban-dem-hang-chinh-hang-p275257961.html?spid=276436652',
    ],
    [
        'category' => 'Máy ảnh - Máy quay phim',
        'name' => 'Camera hành trình Vietmap TS-5K - Hàng chính hãng',
        'tiki_url' => 'https://tiki.vn/camera-hanh-trinh-vietmap-ts-5k-hang-chinh-hang-p277382299.html?spid=277382315',
    ],
    [
        'category' => 'Máy ảnh - Máy quay phim',
        'name' => 'Camera Wifi Ezviz CS-H1C 2.0MP - Hàng chính hãng',
        'tiki_url' => 'https://tiki.vn/camera-wifi-ezviz-cs-h1c-2-0mp-p270964237.html?spid=274422176',
    ],
    [
        'category' => 'Ô tô - Xe máy - Xe đạp',
        'name' => 'Dầu Nhớt Xe Máy Tay Ga Liqui Moly MolyGen Scooter 1L - Hàng Chính Hãng',
        'tiki_url' => 'https://tiki.vn/dau-nhot-xe-may-tay-ga-liqui-moly-molygen-scooter-5w-30-10w-40-the-he-moi-1l-hang-chinh-hang-p276087395.html?spid=276088527',
    ],
    [
        'category' => 'Ô tô - Xe máy - Xe đạp',
        'name' => 'Bơm lốp ô tô Xiaomi 70mai Midrive TP03',
        'tiki_url' => 'https://tiki.vn/bom-lop-o-to-xiaomi-70mai-midrive-tp03-bom-lop-da-nang-bom-lop-mini-hang-nhap-khau-p58244258.html?spid=63136558',
    ],
    [
        'category' => 'Ô tô - Xe máy - Xe đạp',
        'name' => 'Mũ bảo hiểm xe máy nửa đầu Rona Haly viền bạc',
        'tiki_url' => 'https://tiki.vn/mu-bao-hiem-nua-dau-rona-haly-vien-bac-duoc-phan-phoi-tai-he-thong-non-trum-p184907371.html?spid=184907379',
    ],
    [
        'category' => 'Thực phẩm - Đồ uống',
        'name' => 'Trung Nguyên Legend Cà phê rang xay Sáng tạo 1 Bịch 340g',
        'tiki_url' => 'https://tiki.vn/trung-nguyen-legend-ca-phe-rang-xay-sang-tao-1-bich-340gr-p58407832.html?spid=58407833',
    ],
    [
        'category' => 'Thực phẩm - Đồ uống',
        'name' => 'Thùng 30 gói mì Hảo Hảo Tôm Chua Cay 75g',
        'tiki_url' => 'https://tiki.vn/thung-30-goi-mi-hao-hao-tom-chua-cay-75g-p192886296.html?spid=192886298',
    ],
    [
        'category' => 'Thực phẩm - Đồ uống',
        'name' => 'Thùng 48 Hộp Sữa Nestlé MILO A2 Mới 110ml',
        'tiki_url' => 'https://tiki.vn/thung-sua-lua-mach-nestle-milo-a2-moi-12-4x110ml-p277570683.html?spid=277570684',
    ],
    [
        'category' => 'Chăm sóc thú cưng',
        'name' => 'Thức ăn cho mèo mọi lứa tuổi Catsrang',
        'tiki_url' => 'https://tiki.vn/thuc-an-cho-meo-moi-lua-tuoi-catsrang-p197645733.html?spid=197645735',
    ],
    [
        'category' => 'Chăm sóc thú cưng',
        'name' => 'Cát vệ sinh cho mèo đậu nành Acropet trà xanh',
        'tiki_url' => 'https://tiki.vn/cat-ve-sinh-cho-meo-dau-nanh-acropet-tra-xanh-p171942920.html?spid=171942922',
    ],
    [
        'category' => 'Chăm sóc thú cưng',
        'name' => 'Sữa Tắm SOS 530ml Cho Chó Mèo',
        'tiki_url' => 'https://tiki.vn/sua-tam-sos-530ml-chinh-hang-cho-cho-meo-p273878618.html?spid=273878675',
    ],
    [
        'category' => 'Dịch vụ - Quà tặng',
        'name' => 'Ly nến thơm Bispol BIS0402 Flower Mail 100g',
        'tiki_url' => 'https://tiki.vn/ly-nen-thom-bispol-bis0402-flower-mail-100g-dien-hoa-tuoi-p12366982.html?spid=12366983',
    ],
    [
        'category' => 'Dịch vụ - Quà tặng',
        'name' => 'Thiệp chúc mừng sinh nhật SDstationery BOUQUET 12x12',
        'tiki_url' => 'https://tiki.vn/thiep-chuc-mung-sinh-nhat-12x12-sdstationery-bouquet-hoa-tiet-hien-dai-mau-sac-ruc-ro-p157021037.html?spid=157021039',
    ],
    [
        'category' => 'Dịch vụ - Quà tặng',
        'name' => 'Combo hộp đựng quà kèm túi vải cao cấp 22x16x6cm',
        'tiki_url' => 'https://tiki.vn/combo-hop-dung-qua-kem-tui-vai-cao-cap-hop-dung-qua-sinh-nhat-dung-qua-tang-cac-dip-le-mau-den-p202818141.html?spid=202818142',
    ],
];

$database = new Database();
$conn = $database->getConnection();
$productModel = new ProductModel($conn);

function fetchCategoryIds(mysqli $conn) {
    $result = $conn->query("SELECT id, name FROM categories");
    if (!$result) {
        throw new RuntimeException('Không đọc được danh mục: ' . $conn->error);
    }

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[$row['name']] = (int) $row['id'];
    }
    return $categories;
}

function exactTikiDuplicate(ProductModel $productModel, array $row) {
    $meta = $productModel->buildPlatformLinkMeta('Tiki', $row['tiki_url']);
    if (!$meta['platform_name'] || !$meta['normalized_url']) {
        return ['error' => 'URL Tiki không hợp lệ'];
    }

    $duplicate = $productModel->findExactPlatformDuplicate(
        $meta['platform_name'],
        $meta['platform_product_id'],
        $meta['url_hash'],
        null,
        null,
        $meta['normalized_url'],
        $row['tiki_url']
    );

    return ['duplicate' => $duplicate];
}

$categoryIds = fetchCategoryIds($conn);
$added = 0;
$skipped = 0;
$errors = 0;

echo $dryRun ? "DRY RUN - chưa ghi database\n" : "IMPORT - sẽ ghi database\n";
echo "Tổng sản phẩm seed: " . count($seedProducts) . "\n";

foreach ($seedProducts as $index => $row) {
    $line = $index + 1;
    $categoryName = $row['category'];
    $productName = $row['name'];

    if (!isset($categoryIds[$categoryName])) {
        $errors++;
        echo "[Lỗi] #{$line} Không tìm thấy danh mục: {$categoryName}\n";
        continue;
    }

    $duplicateCheck = exactTikiDuplicate($productModel, $row);
    if (isset($duplicateCheck['error'])) {
        $errors++;
        echo "[Lỗi] #{$line} {$duplicateCheck['error']}: {$productName}\n";
        continue;
    }

    if (!empty($duplicateCheck['duplicate'])) {
        $skipped++;
        $existing = $duplicateCheck['duplicate'];
        echo "[Bỏ qua] #{$line} Link Tiki đã tồn tại ở product_id={$existing['product_id']}: {$productName}\n";
        continue;
    }

    if ($dryRun) {
        echo "[Có thể thêm] #{$line} {$categoryName} | {$productName}\n";
        continue;
    }

    $conn->begin_transaction();
    try {
        $productId = $productModel->createProduct($productName, $description, $categoryIds[$categoryName]);
        if (!$productId) {
            throw new RuntimeException('Không tạo được product');
        }

        if (!$productModel->addPlatformLink((int) $productId, 'Tiki', $row['tiki_url'])) {
            throw new RuntimeException('Không thêm được link Tiki hoặc link đã trùng');
        }

        $conn->commit();
        $added++;
        echo "[Đã thêm] product_id={$productId} | {$categoryName} | {$productName}\n";
    } catch (Throwable $e) {
        $conn->rollback();
        $errors++;
        echo "[Lỗi] #{$line} {$productName}: {$e->getMessage()}\n";
    }
}

echo "Kết quả: thêm={$added}, bỏ_qua={$skipped}, lỗi={$errors}\n";
?>
