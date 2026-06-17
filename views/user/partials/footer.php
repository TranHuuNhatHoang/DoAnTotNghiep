<style>
    .sp-footer {
        background: #eef1f6;
        color: #111827;
        border-top: 1px solid #d9dee8;
        margin-top: 36px;
    }

    .sp-footer-grid {
        display: grid;
        grid-template-columns: 1.45fr 1fr 1fr 1fr;
        gap: 46px;
        padding: 38px 0 30px;
    }

    .sp-footer-brand {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #111827;
        font-size: 1.6rem;
        font-weight: 950;
        text-decoration: none;
    }

    .sp-footer-brand span:first-child {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        background: #2aa7df;
        color: #fff;
    }

    .sp-footer-company {
        margin-top: 18px;
    }

    .sp-footer-company strong,
    .sp-footer-title {
        display: block;
        margin: 0 0 12px;
        color: #111827;
        font-size: 1.03rem;
        font-weight: 900;
    }

    .sp-footer-desc {
        max-width: 380px;
        margin: 0;
        color: #344054;
        line-height: 1.55;
        font-weight: 600;
    }

    .sp-footer-links {
        display: grid;
        gap: 11px;
    }

    .sp-footer-links a,
    .sp-footer-links span {
        color: #1f2937;
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 650;
    }

    .sp-footer-links a:hover {
        color: #0b5fff;
    }

    .sp-footer-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    .sp-footer-badge {
        height: 34px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 0 10px;
        border-radius: 8px;
        background: #fff;
        border: 1px solid #d0d5dd;
        color: #111827;
        font-size: .82rem;
        font-weight: 900;
    }

    .sp-footer-badge i {
        color: #16a34a;
    }

    .sp-partner-button {
        width: fit-content;
        min-height: 54px;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        border-radius: 8px;
        background: #e92b2b;
        color: #fff !important;
        text-decoration: none;
        font-weight: 900;
        line-height: 1.15;
    }

    .sp-partner-button i {
        font-size: 1.6rem;
    }

    .sp-footer-social {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #315ca8;
        color: #fff !important;
        font-size: 1.4rem;
        text-decoration: none;
    }

    .sp-footer-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 0;
        border-top: 1px solid #d9dee8;
        color: #667085;
        font-size: 0.88rem;
        font-weight: 650;
    }

    @media (max-width: 1100px) {
        .sp-footer-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .sp-footer-grid {
            grid-template-columns: 1fr;
        }

        .sp-footer-bottom {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<footer class="sp-footer">
    <div class="container sp-footer-grid">
        <div>
            <a class="sp-footer-brand" href="index.php" aria-label="SmartPrice">
                <span><i class="fas fa-tags"></i></span>
                <span>SmartPrice</span>
            </a>
            <div class="sp-footer-company">
                <strong>WEBSITE THEO DÕI GIÁ ĐA SÀN</strong>
                <p class="sp-footer-desc">
                    Công cụ so sánh giá online phục vụ đồ án tốt nghiệp, không trực tiếp bán hàng.
                    Dữ liệu được tổng hợp từ Tiki, Shopee và Lazada.
                </p>
            </div>
            <div class="sp-footer-badges">
                <span class="sp-footer-badge"><i class="fas fa-check-circle"></i>Đồ án tốt nghiệp</span>
                <span class="sp-footer-badge"><i class="fas fa-shield-alt"></i>Bảo vệ dữ liệu</span>
            </div>
        </div>

        <div>
            <h3 class="sp-footer-title">Hỗ trợ khách hàng</h3>
            <div class="sp-footer-links">
                <span>Hotline: <strong>Đang cập nhật</strong></span>
                <span>Email: <strong>support@smartprice.local</strong></span>
                <a href="index.php?role=user&controller=product&action=search">Câu hỏi thường gặp</a>
                <a href="index.php?role=user&controller=product&action=myAlerts">Quản lý cảnh báo giá</a>
                <a href="index.php?role=user&controller=auth&action=login">Đăng nhập tài khoản</a>
            </div>
        </div>

        <div>
            <h3 class="sp-footer-title">Hợp tác và liên kết</h3>
            <div class="sp-footer-links">
                <a class="sp-partner-button" href="index.php?role=user&controller=product&action=search">
                    <i class="fas fa-store"></i>
                    <span>Theo dõi giá<br>đa sàn</span>
                </a>
                <a href="index.php?role=user&controller=product&action=search">Tất cả sản phẩm</a>
                <a href="index.php?role=user&controller=product&action=search&platform_filter=Tiki">Tiki</a>
                <a href="index.php?role=user&controller=product&action=search&platform_filter=Shopee">Shopee</a>
                <a href="index.php?role=user&controller=product&action=search&platform_filter=Lazada">Lazada</a>
            </div>
        </div>

        <div>
            <h3 class="sp-footer-title">Kết nối với chúng tôi</h3>
            <div class="sp-footer-links">
                <a class="sp-footer-social" href="index.php" aria-label="SmartPrice"><i class="fab fa-facebook-f"></i></a>
                <a href="index.php?role=user&controller=product&action=search">Chính sách dữ liệu</a>
                <a href="index.php?role=user&controller=product&action=search&sort_by=price_asc">Tin giá tốt</a>
                <span>So sánh giá đa sàn</span>
                <span>Phân tích biến động giá</span>
            </div>
        </div>
    </div>

    <div class="container sp-footer-bottom">
        <span>© <?php echo date('Y'); ?> SmartPrice. Website theo dõi giá sản phẩm thương mại điện tử.</span>
        <span>Đồ án tốt nghiệp PHP/MySQL/Python crawler</span>
    </div>
</footer>
