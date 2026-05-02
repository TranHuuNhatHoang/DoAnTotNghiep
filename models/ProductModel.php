<?php
require_once __DIR__ . '/../helpers/ProductMatchHelper.php';

class ProductModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function validPriceCondition($alias = '') {
        $prefix = $alias !== '' ? $alias . '.' : '';
        return "{$prefix}is_active = 1 AND {$prefix}status = 1 AND {$prefix}availability_status = 'active' AND {$prefix}current_price > 0";
    }

    private function fetchProductCards($orderBy, $limit = null, $having = '', $includeLastUpdate = false) {
        $validPriceWhere = $this->validPriceCondition();
        $lastUpdateSelect = $includeLastUpdate
            ? ", (SELECT MAX(last_scraped_at) FROM platform_links WHERE product_id = p.id) as last_update"
            : "";
        $havingSql = $having !== '' ? " HAVING " . $having : "";
        $limitSql = $limit !== null ? " LIMIT " . (int) $limit : "";

        $sql = "SELECT p.*,
                c.name as category_name,
                (SELECT COUNT(*) FROM platform_links WHERE product_id = p.id AND {$validPriceWhere}) as total_active_links,
                (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND {$validPriceWhere}) as min_price
                {$lastUpdateSelect}
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                {$havingSql}
                ORDER BY {$orderBy}
                {$limitSql}";

        $result = $this->conn->query($sql);
        return ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAllProductsWithStats() {
        return $this->fetchProductCards('p.id DESC', null, '', true);
    }

    public function getTrendingProducts() {
        return $this->fetchProductCards('p.id DESC', 6, '', true);
    }

    public function getNewProducts() {
        return $this->fetchProductCards('p.id DESC', 8);
    }

    public function getTopDeals() {
        return $this->fetchProductCards('total_active_links DESC', 4, 'total_active_links > 1');
    }

    public function searchProducts($keyword, $categoryId = null) {
        return $this->searchProductsAdvanced($keyword, $categoryId, null, null, null, 'newest');
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ?");
        if (!$stmt) return null;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getPlatformsByProductId($id) {
        $validPriceWhere = $this->validPriceCondition();
        $stmt = $this->conn->prepare(
            "SELECT *,
                    CASE WHEN {$validPriceWhere} THEN 1 ELSE 0 END as has_valid_price
             FROM platform_links
             WHERE product_id = ? AND is_active = 1
             ORDER BY has_valid_price DESC,
                      CASE WHEN {$validPriceWhere} THEN current_price ELSE 2147483647 END ASC,
                      platform_name ASC"
        );
        if (!$stmt) return [];

        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getPriceHistory($productId) {
        $sql = "SELECT ph.price, ph.scraped_at, pl.platform_name
                FROM price_history ph
                JOIN platform_links pl ON ph.link_id = pl.id
                WHERE pl.product_id = ?
                ORDER BY ph.scraped_at ASC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("i", $productId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getProductSpecifications($productId) {
        $sql = "SELECT group_name, spec_name, spec_value, source_platform
                FROM product_specifications
                WHERE product_id = ?
                ORDER BY display_order ASC, id ASC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("i", $productId);
        if (!$stmt->execute()) return [];

        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $groups = [];

        foreach ($rows as $row) {
            $groupName = trim((string) ($row['group_name'] ?? ''));
            if ($groupName === '') {
                $groupName = 'Thông tin sản phẩm';
            }

            if (!isset($groups[$groupName])) {
                $groups[$groupName] = [];
            }

            $groups[$groupName][] = $row;
        }

        return $groups;
    }

    public function createProduct($name, $description, $categoryId) {
        $normalizedName = ProductMatchHelper::normalizeProductName($name);
        $categoryId = (int) $categoryId;
        $stmt = $this->conn->prepare("INSERT INTO products (name, normalized_name, description, category_id) VALUES (?, ?, ?, NULLIF(?, 0))");
        if (!$stmt) return false;

        $stmt->bind_param("sssi", $name, $normalizedName, $description, $categoryId);
        return $stmt->execute() ? $this->conn->insert_id : false;
    }

    public function updateProduct($id, $name, $description, $categoryId) {
        $normalizedName = ProductMatchHelper::normalizeProductName($name);
        $categoryId = (int) $categoryId;
        $stmt = $this->conn->prepare("UPDATE products SET name = ?, normalized_name = ?, description = ?, category_id = NULLIF(?, 0) WHERE id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("sssii", $name, $normalizedName, $description, $categoryId, $id);
        return $stmt->execute();
    }

    public function deleteProduct($id) {
        $stmt = $this->conn->prepare("DELETE FROM products WHERE id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function addPlatformLink($productId, $platformName, $url) {
        $meta = $this->buildPlatformLinkMeta($platformName, $url);
        if (!$meta['platform_name'] || !$meta['normalized_url']) {
            return false;
        }

        $duplicate = $this->findExactPlatformDuplicate(
            $meta['platform_name'],
            $meta['platform_product_id'],
            $meta['url_hash'],
            $productId,
            null,
            $meta['normalized_url'],
            $url
        );
        if ($duplicate) {
            return false;
        }

        $sql = "INSERT INTO platform_links
                    (product_id, platform_name, product_url, platform_product_id, normalized_url, url_hash, current_price, original_price,
                     historical_sold, rating_average, review_count, status, is_active,
                     availability_status, error_message, last_checked_at, next_scrape_at,
                     next_check_at, blocked_until, retry_count, consecutive_failures)
                VALUES (?, ?, ?, ?, ?, ?, 0, NULL, 0, 0, 0, 0, 1, 'unknown', NULL, NULL, NULL, NULL, NULL, 0, 0)
                ON DUPLICATE KEY UPDATE
                    current_price = CASE WHEN url_hash <> VALUES(url_hash) OR url_hash IS NULL THEN 0 ELSE current_price END,
                    original_price = CASE WHEN url_hash <> VALUES(url_hash) OR url_hash IS NULL THEN NULL ELSE original_price END,
                    historical_sold = CASE WHEN url_hash <> VALUES(url_hash) OR url_hash IS NULL THEN 0 ELSE historical_sold END,
                    rating_average = CASE WHEN url_hash <> VALUES(url_hash) OR url_hash IS NULL THEN 0 ELSE rating_average END,
                    review_count = CASE WHEN url_hash <> VALUES(url_hash) OR url_hash IS NULL THEN 0 ELSE review_count END,
                    status = CASE WHEN url_hash <> VALUES(url_hash) OR url_hash IS NULL THEN 0 ELSE status END,
                    availability_status = CASE WHEN url_hash <> VALUES(url_hash) OR url_hash IS NULL THEN 'unknown' ELSE availability_status END,
                    error_message = CASE WHEN url_hash <> VALUES(url_hash) OR url_hash IS NULL THEN NULL ELSE error_message END,
                    last_checked_at = CASE WHEN url_hash <> VALUES(url_hash) OR url_hash IS NULL THEN NULL ELSE last_checked_at END,
                    next_scrape_at = CASE WHEN url_hash <> VALUES(url_hash) OR url_hash IS NULL THEN NULL ELSE next_scrape_at END,
                    next_check_at = CASE WHEN url_hash <> VALUES(url_hash) OR url_hash IS NULL THEN NULL ELSE next_check_at END,
                    blocked_until = CASE WHEN url_hash <> VALUES(url_hash) OR url_hash IS NULL THEN NULL ELSE blocked_until END,
                    retry_count = CASE WHEN url_hash <> VALUES(url_hash) OR url_hash IS NULL THEN 0 ELSE retry_count END,
                    consecutive_failures = CASE WHEN url_hash <> VALUES(url_hash) OR url_hash IS NULL THEN 0 ELSE consecutive_failures END,
                    product_url = VALUES(product_url),
                    platform_product_id = VALUES(platform_product_id),
                    normalized_url = VALUES(normalized_url),
                    url_hash = VALUES(url_hash),
                    is_active = 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param(
            "isssss",
            $productId,
            $meta['platform_name'],
            $url,
            $meta['platform_product_id'],
            $meta['normalized_url'],
            $meta['url_hash']
        );
        return $stmt->execute();
    }

    public function getPriceAlert($userId, $productId) {
        $stmt = $this->conn->prepare("SELECT * FROM price_alerts WHERE user_id = ? AND product_id = ?");
        if (!$stmt) return null;

        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function setPriceAlert($userId, $productId, $targetPrice) {
        $sql = "INSERT INTO price_alerts (user_id, product_id, target_price, is_notified)
                VALUES (?, ?, ?, 0)
                ON DUPLICATE KEY UPDATE
                    target_price = VALUES(target_price),
                    is_notified = 0,
                    created_at = CURRENT_TIMESTAMP";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("iii", $userId, $productId, $targetPrice);
        return $stmt->execute();
    }

    public function getAllAlertsForAdmin() {
        $validPriceWhere = $this->validPriceCondition();
        $sql = "SELECT pa.id, u.email, p.name as product_name, p.id as p_id, pa.target_price, pa.is_notified, pa.created_at,
                       (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND {$validPriceWhere}) as current_min_price
                FROM price_alerts pa
                JOIN users u ON pa.user_id = u.id
                JOIN products p ON pa.product_id = p.id
                ORDER BY pa.created_at DESC";

        $result = $this->conn->query($sql);
        return ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function deletePriceAlert($userId, $productId) {
        $stmt = $this->conn->prepare("DELETE FROM price_alerts WHERE user_id = ? AND product_id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("ii", $userId, $productId);
        return $stmt->execute();
    }

    public function updatePlatformLink($linkId, $url, $isActive) {
        $current = $this->getPlatformLinkById($linkId);
        if (!$current) return false;

        $meta = $this->buildPlatformLinkMeta($current['platform_name'], $url);
        if (!$meta['platform_name'] || !$meta['normalized_url']) {
            return false;
        }

        $duplicate = $this->findExactPlatformDuplicate(
            $meta['platform_name'],
            $meta['platform_product_id'],
            $meta['url_hash'],
            (int) $current['product_id'],
            $linkId,
            $meta['normalized_url'],
            $url
        );
        if ($duplicate) {
            return false;
        }

        $sql = "UPDATE platform_links
                SET product_url = ?,
                    platform_product_id = ?,
                    normalized_url = ?,
                    url_hash = ?,
                    is_active = ?,
                    current_price = CASE WHEN url_hash <> ? OR url_hash IS NULL THEN 0 ELSE current_price END,
                    original_price = CASE WHEN url_hash <> ? OR url_hash IS NULL THEN NULL ELSE original_price END,
                    historical_sold = CASE WHEN url_hash <> ? OR url_hash IS NULL THEN 0 ELSE historical_sold END,
                    rating_average = CASE WHEN url_hash <> ? OR url_hash IS NULL THEN 0 ELSE rating_average END,
                    review_count = CASE WHEN url_hash <> ? OR url_hash IS NULL THEN 0 ELSE review_count END,
                    status = CASE WHEN url_hash <> ? OR url_hash IS NULL THEN 0 ELSE status END,
                    availability_status = CASE WHEN url_hash <> ? OR url_hash IS NULL THEN 'unknown' ELSE availability_status END,
                    error_message = CASE WHEN url_hash <> ? OR url_hash IS NULL THEN NULL ELSE error_message END,
                    last_checked_at = CASE WHEN url_hash <> ? OR url_hash IS NULL THEN NULL ELSE last_checked_at END,
                    next_scrape_at = CASE WHEN url_hash <> ? OR url_hash IS NULL THEN NULL ELSE next_scrape_at END,
                    next_check_at = CASE WHEN url_hash <> ? OR url_hash IS NULL THEN NULL ELSE next_check_at END,
                    blocked_until = CASE WHEN url_hash <> ? OR url_hash IS NULL THEN NULL ELSE blocked_until END,
                    retry_count = CASE WHEN url_hash <> ? OR url_hash IS NULL THEN 0 ELSE retry_count END,
                    consecutive_failures = CASE WHEN url_hash <> ? OR url_hash IS NULL THEN 0 ELSE consecutive_failures END
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param(
            "ssssissssssssssssssi",
            $url,
            $meta['platform_product_id'],
            $meta['normalized_url'],
            $meta['url_hash'],
            $isActive,
            $meta['url_hash'],
            $meta['url_hash'],
            $meta['url_hash'],
            $meta['url_hash'],
            $meta['url_hash'],
            $meta['url_hash'],
            $meta['url_hash'],
            $meta['url_hash'],
            $meta['url_hash'],
            $meta['url_hash'],
            $meta['url_hash'],
            $meta['url_hash'],
            $meta['url_hash'],
            $meta['url_hash'],
            $linkId
        );
        return $stmt->execute();
    }

    public function deletePlatformLink($linkId) {
        $stmt = $this->conn->prepare("DELETE FROM platform_links WHERE id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("i", $linkId);
        return $stmt->execute();
    }

    public function getSuggestions($keyword) {
        $searchTerm = "%" . $keyword . "%";
        $sql = "(SELECT id,
                        CONVERT(name USING utf8mb4) COLLATE utf8mb4_unicode_ci as name,
                        CAST('product' AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as type
                 FROM products WHERE name LIKE ? LIMIT 5)
                UNION ALL
                (SELECT id,
                        CONVERT(name USING utf8mb4) COLLATE utf8mb4_unicode_ci as name,
                        CAST('category' AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as type
                 FROM categories WHERE name LIKE ? LIMIT 3)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function searchProductsAdvanced($keyword, $catId, $platform, $minPrice, $maxPrice, $sort) {
        $searchTerm = "%" . $keyword . "%";
        $validPriceWhere = $this->validPriceCondition();

        $sql = "SELECT p.*, c.name as category_name,
                (SELECT current_price FROM platform_links WHERE product_id = p.id AND platform_name = 'Tiki' AND {$validPriceWhere} LIMIT 1) as tiki_price,
                (SELECT current_price FROM platform_links WHERE product_id = p.id AND platform_name = 'Shopee' AND {$validPriceWhere} LIMIT 1) as shopee_price,
                (SELECT current_price FROM platform_links WHERE product_id = p.id AND platform_name = 'Lazada' AND {$validPriceWhere} LIMIT 1) as lazada_price,
                (SELECT product_url FROM platform_links WHERE product_id = p.id AND platform_name = 'Tiki' AND {$validPriceWhere} LIMIT 1) as tiki_url,
                (SELECT product_url FROM platform_links WHERE product_id = p.id AND platform_name = 'Shopee' AND {$validPriceWhere} LIMIT 1) as shopee_url,
                (SELECT product_url FROM platform_links WHERE product_id = p.id AND platform_name = 'Lazada' AND {$validPriceWhere} LIMIT 1) as lazada_url,
                (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND {$validPriceWhere}) as min_price
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.name LIKE ?";

        $params = [$searchTerm];
        $types = "s";

        if ($catId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $catId;
            $types .= "i";
        }

        if ($platform) {
            $sql .= " AND EXISTS (SELECT 1 FROM platform_links WHERE product_id = p.id AND platform_name = ? AND {$validPriceWhere})";
            $params[] = $platform;
            $types .= "s";
        }

        if ($minPrice) {
            $sql .= " AND (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND {$validPriceWhere}) >= ?";
            $params[] = $minPrice;
            $types .= "i";
        }

        if ($maxPrice) {
            $sql .= " AND (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND {$validPriceWhere}) <= ?";
            $params[] = $maxPrice;
            $types .= "i";
        }

        switch ($sort) {
            case 'price_asc':
                $sql .= " ORDER BY min_price IS NULL ASC, min_price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY min_price DESC";
                break;
            default:
                $sql .= " ORDER BY p.id DESC";
        }

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getRelatedProducts($categoryId, $excludeProductId) {
        if (!$categoryId) return [];

        $validPriceWhere = $this->validPriceCondition();
        $sql = "SELECT p.*,
                (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND {$validPriceWhere}) as min_price
                FROM products p
                WHERE p.category_id = ? AND p.id != ?
                ORDER BY p.id DESC LIMIT 4";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("ii", $categoryId, $excludeProductId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getPriceStats($productId) {
        $sql = "SELECT MAX(price) as max_price, MIN(price) as min_price, AVG(price) as avg_price
                FROM price_history WHERE link_id IN (SELECT id FROM platform_links WHERE product_id = ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return ['max_price' => 0, 'min_price' => 0, 'avg_price' => 0];

        $stmt->bind_param("i", $productId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function buildPlatformLinkMeta($platformName, $url) {
        $platform = ProductMatchHelper::normalizePlatformName($platformName);
        $normalizedUrl = ProductMatchHelper::normalizePlatformUrl($platform, $url);
        return [
            'platform_name' => $platform,
            'platform_product_id' => ProductMatchHelper::extractPlatformProductId($platform, $url),
            'normalized_url' => $normalizedUrl,
            'url_hash' => ProductMatchHelper::buildUrlHash($platform, $normalizedUrl),
        ];
    }

    public function getPlatformLinkById($linkId) {
        $stmt = $this->conn->prepare("SELECT * FROM platform_links WHERE id = ?");
        if (!$stmt) return null;

        $stmt->bind_param("i", $linkId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findExactPlatformDuplicate($platformName, $platformProductId, $urlHash, $excludeProductId = null, $excludeLinkId = null, $normalizedUrl = null, $rawUrl = null) {
        $platform = ProductMatchHelper::normalizePlatformName($platformName);
        if (!$platform) {
            return null;
        }

        $conditions = [];
        $params = [];
        $types = "";
        $normalizedUrl = trim((string)($normalizedUrl ?? ''));
        $rawUrl = trim((string)($rawUrl ?? ''));

        if ($platformProductId) {
            $conditions[] = "(platform_name = ? AND platform_product_id = ?)";
            $params[] = $platform;
            $params[] = $platformProductId;
            $types .= "ss";
        }

        if ($urlHash) {
            $conditions[] = "(platform_name = ? AND url_hash = ?)";
            $params[] = $platform;
            $params[] = $urlHash;
            $types .= "ss";
        }

        if ($normalizedUrl !== '') {
            $conditions[] = "(platform_name = ? AND normalized_url = ?)";
            $params[] = $platform;
            $params[] = $normalizedUrl;
            $types .= "ss";
        }

        if ($rawUrl !== '') {
            $conditions[] = "(platform_name = ? AND product_url = ?)";
            $params[] = $platform;
            $params[] = $rawUrl;
            $types .= "ss";
        }

        if (!$conditions) {
            return null;
        }

        $sql = "SELECT pl.*, p.name as product_name, p.category_id, c.name as category_name
                FROM platform_links pl
                JOIN products p ON p.id = pl.product_id
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE (" . implode(" OR ", $conditions) . ")";

        if ($excludeProductId !== null) {
            $sql .= " AND pl.product_id <> ?";
            $params[] = (int) $excludeProductId;
            $types .= "i";
        }

        if ($excludeLinkId !== null) {
            $sql .= " AND pl.id <> ?";
            $params[] = (int) $excludeLinkId;
            $types .= "i";
        }

        $sql .= " ORDER BY pl.id ASC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findSimilarProductCandidates($name, $excludeId = null, $limit = 8, $categoryId = null) {
        $normalizedName = ProductMatchHelper::normalizeProductName($name);
        if ($normalizedName === '') {
            return [];
        }

        $categoryId = (int) ($categoryId ?? 0);
        $fetchRows = function ($mode) use ($excludeId, $categoryId) {
            $sql = "SELECT p.*, c.name as category_name,
                           (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) as min_price
                    FROM products p
                    LEFT JOIN categories c ON c.id = p.category_id";
            $where = [];
            $params = [];
            $types = "";

            if ($excludeId !== null) {
                $where[] = "p.id <> ?";
                $params[] = (int) $excludeId;
                $types .= "i";
            }

            if ($categoryId > 0 && $mode === 'same') {
                $where[] = "p.category_id = ?";
                $params[] = $categoryId;
                $types .= "i";
            } elseif ($categoryId > 0 && $mode === 'fallback') {
                $where[] = "(p.category_id <> ? OR p.category_id IS NULL)";
                $params[] = $categoryId;
                $types .= "i";
            }

            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }

            $sql .= " ORDER BY p.id DESC LIMIT 300";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return [];

            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        };

        $scoreRows = function ($rows) use ($normalizedName) {
            $candidates = [];
            foreach ($rows as $row) {
                $compareName = ProductMatchHelper::normalizeProductName($row['name'] ?? '');
                $score = ProductMatchHelper::calculateNameSimilarity($normalizedName, $compareName);
                if ($score >= 60) {
                    $row['similarity_score'] = $score;
                    $row['platform_links'] = $this->getProductPlatformSummary((int) $row['id']);
                    $candidates[] = $row;
                }
            }
            return $candidates;
        };

        $sameCategoryCandidates = $categoryId > 0 ? $scoreRows($fetchRows('same')) : [];
        $fallbackCandidates = $scoreRows($fetchRows($categoryId > 0 ? 'fallback' : 'all'));

        $sortCandidates = static function ($a, $b) {
            return ($b['similarity_score'] <=> $a['similarity_score']) ?: ($b['id'] <=> $a['id']);
        };
        usort($sameCategoryCandidates, $sortCandidates);
        usort($fallbackCandidates, $sortCandidates);

        $candidates = array_merge($sameCategoryCandidates, $fallbackCandidates);
        return array_slice($candidates, 0, (int) $limit);
    }

    public function getProductPlatformSummary($productId) {
        $stmt = $this->conn->prepare(
            "SELECT id, platform_name, product_url, current_price, availability_status
             FROM platform_links
             WHERE product_id = ?
             ORDER BY platform_name ASC"
        );
        if (!$stmt) return [];

        $stmt->bind_param("i", $productId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function logDuplicateOverride($adminUserId, $productId, $productName, $candidateIds, $reason = 'force_create_after_name_warning') {
        $normalizedName = ProductMatchHelper::normalizeProductName($productName);
        $candidateText = implode(',', array_map('intval', (array) $candidateIds));
        $stmt = $this->conn->prepare(
            "INSERT INTO product_duplicate_overrides
                (admin_user_id, product_id, product_name, normalized_name, candidate_product_ids, reason)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) return false;

        $adminId = $adminUserId ? (int) $adminUserId : null;
        $stmt->bind_param("iissss", $adminId, $productId, $productName, $normalizedName, $candidateText, $reason);
        return $stmt->execute();
    }
}
?>
