<?php
class AdminProductController {
    private $db;
    private $productModel;
    private $categoryModel;

    public function __construct($db = null) {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header("Location: index.php");
            exit();
        }

        $this->db = $db;
        if (!$this->db) {
            require_once 'config/database.php';
            $database = new Database();
            $this->db = $database->getConnection();
        }

        require_once 'models/ProductModel.php';
        require_once 'models/CategoryModel.php';
        $this->productModel = new ProductModel($this->db);
        $this->categoryModel = new CategoryModel($this->db);
    }

    public function index() {
        $products = $this->productModel->getAllProductsWithStats();
        $categories = $this->categoryModel->getAllCategories();
        require_once 'views/admin/products.php';
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = $this->readProductFormData();
            if ($formData['name'] === '') {
                header("Location: index.php?role=admin&controller=adminProduct&action=index");
                exit();
            }

            $exactDuplicates = $this->findExactDuplicateLinks($formData['links']);
            if (!empty($exactDuplicates)) {
                $duplicateMode = 'exact';
                $duplicates = $exactDuplicates;
                $similarCandidates = [];
                $categories = $this->categoryModel->getAllCategories();
                require_once 'views/admin/product_duplicate_warning.php';
                return;
            }

            $similarCandidates = $this->productModel->findSimilarProductCandidates(
                $formData['name'],
                null,
                8,
                $formData['category_id']
            );
            $forceCreate = isset($_POST['force_create']) && $_POST['force_create'] === '1';
            if (!$forceCreate && !empty($similarCandidates)) {
                $duplicateMode = 'similar';
                $duplicates = [];
                $categories = $this->categoryModel->getAllCategories();
                require_once 'views/admin/product_duplicate_warning.php';
                return;
            }

            $this->db->begin_transaction();
            try {
                $productId = $this->productModel->createProduct($formData['name'], $formData['description'], $formData['category_id']);
                if (!$productId) {
                    throw new Exception('Không tạo được sản phẩm.');
                }

                foreach ($formData['links'] as $link) {
                    if ($link['url'] !== '' && !$this->productModel->addPlatformLink($productId, $link['platform'], $link['url'])) {
                        throw new Exception('Không thể gắn link ' . $link['platform'] . '.');
                    }
                }

                if ($forceCreate && !empty($_POST['candidate_ids'])) {
                    $candidateIds = array_filter(array_map('intval', explode(',', (string) $_POST['candidate_ids'])));
                    $this->productModel->logDuplicateOverride(
                        $_SESSION['user_id'] ?? null,
                        $productId,
                        $formData['name'],
                        $candidateIds
                    );
                }

                $this->db->commit();
            } catch (Exception $exc) {
                $this->db->rollback();
                $_SESSION['admin_error'] = $exc->getMessage();
            }
        }

        header("Location: index.php?role=admin&controller=adminProduct&action=index");
        exit();
    }

    public function attachDuplicateLinks() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?role=admin&controller=adminProduct&action=index");
            exit();
        }

        $targetProductId = intval($_POST['target_product_id'] ?? 0);
        $formData = $this->readProductFormData();
        if ($targetProductId <= 0) {
            header("Location: index.php?role=admin&controller=adminProduct&action=index");
            exit();
        }

        $exactDuplicates = $this->findExactDuplicateLinks($formData['links'], $targetProductId);
        if (!empty($exactDuplicates)) {
            $duplicateMode = 'exact';
            $duplicates = $exactDuplicates;
            $similarCandidates = [];
            $categories = $this->categoryModel->getAllCategories();
            require_once 'views/admin/product_duplicate_warning.php';
            return;
        }

        $this->db->begin_transaction();
        try {
            foreach ($formData['links'] as $link) {
                if ($link['url'] !== '' && !$this->productModel->addPlatformLink($targetProductId, $link['platform'], $link['url'])) {
                    throw new Exception('Không thể gắn link ' . $link['platform'] . '.');
                }
            }
            $this->db->commit();
            header("Location: index.php?role=admin&controller=adminPlatform&action=index&product_id=" . $targetProductId);
            exit();
        } catch (Exception $exc) {
            $this->db->rollback();
            $_SESSION['admin_error'] = $exc->getMessage();
            header("Location: index.php?role=admin&controller=adminProduct&action=index");
            exit();
        }
    }

    public function restoreDuplicateForm() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['old_product_form'] = $this->readProductFormData();
            $_SESSION['open_product_modal'] = 'add';
        }

        header("Location: index.php?role=admin&controller=adminProduct&action=index");
        exit();
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id']);
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $categoryId = max(0, intval($_POST['category_id'] ?? 0));

            $this->productModel->updateProduct($id, $name, $description, $categoryId);
        }
        header("Location: index.php?role=admin&controller=adminProduct&action=index");
        exit();
    }

    public function delete() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id > 0) {
            $this->productModel->deleteProduct($id);
        }
        header("Location: index.php?role=admin&controller=adminProduct&action=index");
        exit();
    }

    private function readProductFormData() {
        return [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category_id' => max(0, intval($_POST['category_id'] ?? 0)),
            'links' => [
                ['platform' => 'Tiki', 'url' => trim($_POST['tiki_url'] ?? '')],
                ['platform' => 'Shopee', 'url' => trim($_POST['shopee_url'] ?? '')],
                ['platform' => 'Lazada', 'url' => trim($_POST['lazada_url'] ?? '')],
            ],
        ];
    }

    private function findExactDuplicateLinks($links, $excludeProductId = null) {
        $duplicates = [];

        foreach ($links as $link) {
            if ($link['url'] === '') {
                continue;
            }

            $meta = $this->productModel->buildPlatformLinkMeta($link['platform'], $link['url']);
            $duplicate = $this->productModel->findExactPlatformDuplicate(
                $meta['platform_name'],
                $meta['platform_product_id'],
                $meta['url_hash'],
                $excludeProductId,
                null,
                $meta['normalized_url'],
                $link['url']
            );

            if ($duplicate) {
                $duplicate['submitted_platform'] = $link['platform'];
                $duplicate['submitted_url'] = $link['url'];
                $duplicate['platform_product_id_checked'] = $meta['platform_product_id'];
                $duplicate['url_hash_checked'] = $meta['url_hash'];
                $duplicate['normalized_url_checked'] = $meta['normalized_url'];
                $duplicates[] = $duplicate;
            }
        }

        return $duplicates;
    }
}
?>
