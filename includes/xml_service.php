<?php
/**
 * CHÉRIE — XML Service Layer
 * Meaningful XML Integration using PHP SimpleXML
 */

if (!defined('CHERIE_APP')) {
    define('CHERIE_APP', true);
}

class XmlService {

    /**
     * Load promotional campaigns from XML using SimpleXML
     */
    public static function getActivePromotions(): array {
        $filePath = XML_PATH . DIRECTORY_SEPARATOR . 'promotions.xml';
        if (!file_exists($filePath)) {
            return [];
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($filePath);
        if ($xml === false) {
            libxml_clear_errors();
            return [];
        }

        $promotions = [];
        $currentDate = date('Y-m-d');

        foreach ($xml->campaign as $campaign) {
            $startDate = (string)$campaign->start_date;
            $endDate = (string)$campaign->end_date;

            if ($currentDate >= $startDate && $currentDate <= $endDate) {
                $promotions[] = [
                    'id' => (string)$campaign->id,
                    'code' => strtoupper((string)$campaign->code),
                    'title' => (string)$campaign->title,
                    'description' => (string)$campaign->description,
                    'discount_type' => (string)$campaign->discount_type,
                    'discount_value' => (float)$campaign->discount_value,
                    'min_order_amount' => (float)$campaign->min_order_amount,
                    'max_discount_amount' => (float)$campaign->max_discount_amount,
                    'badge_text' => (string)$campaign->badge_text,
                    'banner_active' => ((string)$campaign->banner_active === 'true')
                ];
            }
        }
        return $promotions;
    }

    /**
     * Validate coupon code from XML against cart subtotal
     */
    public static function validateCoupon(string $couponCode, float $subtotal): array {
        $code = strtoupper(trim($couponCode));
        $promos = self::getActivePromotions();

        foreach ($promos as $promo) {
            if ($promo['code'] === $code) {
                if ($subtotal < $promo['min_order_amount']) {
                    return [
                        'valid' => false,
                        'message' => 'This coupon requires a minimum cart value of ' . formatPrice($promo['min_order_amount'])
                    ];
                }

                $discount = 0.0;
                if ($promo['discount_type'] === 'percentage') {
                    $discount = ($subtotal * $promo['discount_value']) / 100;
                    if ($discount > $promo['max_discount_amount']) {
                        $discount = $promo['max_discount_amount'];
                    }
                } else {
                    $discount = min($promo['discount_value'], $subtotal);
                }

                return [
                    'valid' => true,
                    'code' => $code,
                    'title' => $promo['title'],
                    'discount_amount' => round($discount, 2),
                    'message' => 'Coupon ' . $code . ' applied! You saved ' . formatPrice($discount)
                ];
            }
        }

        return [
            'valid' => false,
            'message' => 'Invalid or expired coupon code.'
        ];
    }

    /**
     * Load shipping methods and calculate cost based on subtotal
     */
    public static function getShippingMethods(float $subtotal): array {
        $filePath = XML_PATH . DIRECTORY_SEPARATOR . 'shipping_rates.xml';
        if (!file_exists($filePath)) {
            return [
                [
                    'code' => 'STANDARD',
                    'name' => 'Standard Boutique Delivery',
                    'estimated_days' => '3-5 business days',
                    'cost' => ($subtotal >= 1499.00) ? 0.00 : 99.00,
                    'is_free' => ($subtotal >= 1499.00),
                    'description' => 'Delivered with luxury rose ribbon wrap.'
                ]
            ];
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($filePath);
        if ($xml === false) {
            libxml_clear_errors();
            return [];
        }

        $methods = [];
        foreach ($xml->method as $m) {
            $baseCost = (float)$m->base_cost;
            $freeThreshold = (float)$m->free_shipping_threshold;
            $isFree = ($subtotal >= $freeThreshold);
            $cost = $isFree ? 0.00 : $baseCost;

            $methods[] = [
                'code' => (string)$m->code,
                'name' => (string)$m->name,
                'estimated_days' => (string)$m->estimated_days,
                'cost' => $cost,
                'is_free' => $isFree,
                'free_threshold' => $freeThreshold,
                'description' => (string)$m->description
            ];
        }
        return $methods;
    }

    /**
     * Export complete product catalog to an XML format using SimpleXML
     */
    public static function exportCatalogToXml(): string {
        if (!Database::isConnected()) {
            return '';
        }

        $products = Database::fetchAll("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 ORDER BY p.id ASC");

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><cherie_catalog></cherie_catalog>');
        $xml->addAttribute('generated_at', date('c'));
        $xml->addAttribute('total_products', (string)count($products));
        $xml->addChild('store_name', APP_NAME);
        $xml->addChild('currency', APP_CURRENCY);

        $itemsNode = $xml->addChild('products');

        foreach ($products as $p) {
            $itemNode = $itemsNode->addChild('product');
            $itemNode->addChild('id', (string)$p['id']);
            $itemNode->addChild('sku', htmlspecialchars($p['sku']));
            $itemNode->addChild('name', htmlspecialchars($p['name']));
            $itemNode->addChild('category', htmlspecialchars($p['category_name']));
            $itemNode->addChild('price', (string)$p['price']);
            $itemNode->addChild('compare_at_price', (string)($p['compare_at_price'] ?? '0.00'));
            $itemNode->addChild('stock_quantity', (string)$p['stock_quantity']);
            $itemNode->addChild('in_stock', ($p['stock_quantity'] > 0) ? 'true' : 'false');
            $itemNode->addChild('url', BASE_URL . '/product.php?slug=' . urlencode($p['slug']));
            $itemNode->addChild('short_description', htmlspecialchars($p['short_description'] ?? ''));
        }

        return $xml->asXML();
    }
}
