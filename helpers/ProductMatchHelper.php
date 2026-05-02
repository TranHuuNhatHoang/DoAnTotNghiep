<?php
class ProductMatchHelper {
    public static function normalizeProductName($name) {
        $text = self::removeVietnameseAccents((string) $name);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', trim($text));

        return $text;
    }

    public static function normalizePlatformUrl($platform, $url) {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        $parts = parse_url($url);
        if (!$parts || empty($parts['host'])) {
            return strtolower(rtrim($url, '/'));
        }

        $scheme = 'https';
        $host = strtolower($parts['host']);
        $host = preg_replace('/^www\./', '', $host);
        $path = isset($parts['path']) ? preg_replace('#/+#', '/', $parts['path']) : '';
        $path = rtrim($path, '/');

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $keptQuery = [];
        $platform = self::normalizePlatformName($platform);
        if ($platform === 'Tiki' && !empty($query['spid'])) {
            $keptQuery['spid'] = preg_replace('/\D+/', '', (string) $query['spid']);
        }
        if ($platform === 'Shopee') {
            foreach (['shopid', 'itemid'] as $key) {
                if (!empty($query[$key])) {
                    $keptQuery[$key] = preg_replace('/\D+/', '', (string) $query[$key]);
                }
            }
        }
        if ($platform === 'Lazada') {
            foreach (['itemId', 'itemid', 'spm'] as $key) {
                if (!empty($query[$key]) && $key !== 'spm') {
                    $keptQuery[$key] = preg_replace('/\D+/', '', (string) $query[$key]);
                }
            }
        }

        ksort($keptQuery);
        $normalized = $scheme . '://' . $host . $path;
        if (!empty($keptQuery)) {
            $normalized .= '?' . http_build_query($keptQuery);
        }

        return strtolower($normalized);
    }

    public static function extractPlatformProductId($platform, $url) {
        $platform = self::normalizePlatformName($platform);
        $url = trim((string) $url);
        $parts = parse_url(preg_match('/^https?:\/\//i', $url) ? $url : 'https://' . ltrim($url, '/'));
        $path = $parts['path'] ?? '';
        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        if ($platform === 'Tiki') {
            if (preg_match('/-p(\d+)\.html/i', $path, $matches)) {
                return $matches[1];
            }
            return null;
        }

        if ($platform === 'Shopee') {
            $shopId = null;
            $itemId = null;
            if (preg_match('/(?:^|[.\-\/])i\.(\d+)\.(\d+)(?:$|[?\/\-])?/i', $url, $matches)) {
                $shopId = $matches[1];
                $itemId = $matches[2];
            } elseif (preg_match('/\/product\/(\d+)\/(\d+)/i', $path, $matches)) {
                $shopId = $matches[1];
                $itemId = $matches[2];
            } else {
                $shopId = isset($query['shopid']) ? preg_replace('/\D+/', '', (string) $query['shopid']) : null;
                $itemId = isset($query['itemid']) ? preg_replace('/\D+/', '', (string) $query['itemid']) : null;
            }
            return ($shopId && $itemId) ? $shopId . '_' . $itemId : null;
        }

        if ($platform === 'Lazada') {
            if (!empty($query['itemId'])) {
                return preg_replace('/\D+/', '', (string) $query['itemId']);
            }
            if (!empty($query['itemid'])) {
                return preg_replace('/\D+/', '', (string) $query['itemid']);
            }
            if (preg_match('/-i(\d+)(?:-s\d+)?\.html/i', $path, $matches)) {
                return $matches[1];
            }
            return null;
        }

        return null;
    }

    public static function buildUrlHash($platform, $normalizedUrl) {
        $normalizedUrl = trim((string) $normalizedUrl);
        if ($normalizedUrl === '') {
            return null;
        }
        return sha1(self::normalizePlatformName($platform) . '|' . $normalizedUrl);
    }

    public static function calculateNameSimilarity($left, $right) {
        $leftTokens = self::tokenSet(self::normalizeProductName($left));
        $rightTokens = self::tokenSet(self::normalizeProductName($right));
        if (!$leftTokens || !$rightTokens) {
            return 0;
        }

        $intersection = array_intersect($leftTokens, $rightTokens);
        $union = array_unique(array_merge($leftTokens, $rightTokens));
        $tokenScore = count($union) > 0 ? (count($intersection) / count($union)) * 100 : 0;

        similar_text(implode(' ', $leftTokens), implode(' ', $rightTokens), $textScore);
        return (int) round(($tokenScore * 0.7) + ($textScore * 0.3));
    }

    public static function normalizePlatformName($platform) {
        $platform = strtolower(trim((string) $platform));
        if ($platform === 'tiki') return 'Tiki';
        if ($platform === 'shopee') return 'Shopee';
        if ($platform === 'lazada') return 'Lazada';
        return '';
    }

    private static function tokenSet($text) {
        $tokens = preg_split('/\s+/', trim((string) $text));
        $tokens = array_values(array_unique(array_filter($tokens, static function ($token) {
            return $token !== '';
        })));
        sort($tokens);
        return $tokens;
    }

    private static function removeVietnameseAccents($text) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string) $text);
        if ($converted !== false && $converted !== '') {
            return $converted;
        }

        $map = [
            'đ' => 'd', 'Đ' => 'D',
            'á' => 'a', 'à' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
            'ă' => 'a', 'ắ' => 'a', 'ằ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
            'â' => 'a', 'ấ' => 'a', 'ầ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
            'é' => 'e', 'è' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
            'ê' => 'e', 'ế' => 'e', 'ề' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
            'í' => 'i', 'ì' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
            'ô' => 'o', 'ố' => 'o', 'ồ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
            'ơ' => 'o', 'ớ' => 'o', 'ờ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
            'ư' => 'u', 'ứ' => 'u', 'ừ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
            'ý' => 'y', 'ỳ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
        ];
        return strtr($text, $map);
    }
}
?>
