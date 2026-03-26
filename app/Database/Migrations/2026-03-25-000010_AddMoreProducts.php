<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMoreProducts extends Migration
{
    public function up()
    {
        $productTable = $this->db->table('products');
        $variantTable = $this->db->table('product_variants');
        $attrTable = $this->db->table('attributes');
        $valueTable = $this->db->table('attribute_values');
        $pvaTable = $this->db->table('product_variant_attribute_values');

        $products = [
            ['slug' => 'organic-hoodie', 'name' => 'Organic Hoodie', 'description' => 'Brushed fleece hoodie', 'base_price' => 45.00],
            ['slug' => 'canvas-backpack', 'name' => 'Canvas Backpack', 'description' => 'Everyday carry backpack', 'base_price' => 39.00],
            ['slug' => 'travel-joggers', 'name' => 'Travel Joggers', 'description' => 'Stretch travel pants', 'base_price' => 48.00],
        ];

        $productIds = [];
        foreach ($products as $product) {
            $existing = $productTable->where('slug', $product['slug'])->get()->getRowArray();
            if ($existing) {
                $productIds[$product['slug']] = (int) $existing['id'];
                continue;
            }
            $productTable->insert($product);
            $productIds[$product['slug']] = (int) $this->db->insertID();
        }

        $variants = [
            ['sku' => 'HOODIE-S-NAVY', 'product_slug' => 'organic-hoodie', 'variant_name' => 'S / Navy', 'size' => 'S', 'color' => 'Navy', 'price' => 45.00, 'stock' => 12],
            ['sku' => 'HOODIE-M-BEIGE', 'product_slug' => 'organic-hoodie', 'variant_name' => 'M / Beige', 'size' => 'M', 'color' => 'Beige', 'price' => 46.00, 'stock' => 10],
            ['sku' => 'HOODIE-L-GREEN', 'product_slug' => 'organic-hoodie', 'variant_name' => 'L / Green', 'size' => 'L', 'color' => 'Green', 'price' => 46.00, 'stock' => 8],
            ['sku' => 'BACKPACK-M-BLACK', 'product_slug' => 'canvas-backpack', 'variant_name' => 'M / Black', 'size' => 'M', 'color' => 'Black', 'price' => 39.00, 'stock' => 14],
            ['sku' => 'BACKPACK-L-NAVY', 'product_slug' => 'canvas-backpack', 'variant_name' => 'L / Navy', 'size' => 'L', 'color' => 'Navy', 'price' => 41.00, 'stock' => 9],
            ['sku' => 'JOGGER-S-BLACK', 'product_slug' => 'travel-joggers', 'variant_name' => 'S / Black', 'size' => 'S', 'color' => 'Black', 'price' => 48.00, 'stock' => 16],
            ['sku' => 'JOGGER-M-NAVY', 'product_slug' => 'travel-joggers', 'variant_name' => 'M / Navy', 'size' => 'M', 'color' => 'Navy', 'price' => 49.00, 'stock' => 12],
            ['sku' => 'JOGGER-L-GREEN', 'product_slug' => 'travel-joggers', 'variant_name' => 'L / Green', 'size' => 'L', 'color' => 'Green', 'price' => 49.00, 'stock' => 10],
        ];

        $variantIds = [];
        foreach ($variants as $variant) {
            $existing = $variantTable->where('sku', $variant['sku'])->get()->getRowArray();
            if ($existing) {
                $variantIds[$variant['sku']] = (int) $existing['id'];
                continue;
            }

            $productId = $productIds[$variant['product_slug']] ?? null;
            if (!$productId) {
                continue;
            }

            $variantTable->insert([
                'product_id' => $productId,
                'sku' => $variant['sku'],
                'variant_name' => $variant['variant_name'],
                'size' => $variant['size'],
                'color' => $variant['color'],
                'price' => $variant['price'],
                'stock' => $variant['stock'],
                'is_active' => 1,
            ]);
            $variantIds[$variant['sku']] = (int) $this->db->insertID();
        }

        $sizeAttr = $attrTable->where('slug', 'size')->get()->getRowArray();
        $colorAttr = $attrTable->where('slug', 'color')->get()->getRowArray();
        if (!$sizeAttr || !$colorAttr) {
            return;
        }

        $sizeId = (int) $sizeAttr['id'];
        $colorId = (int) $colorAttr['id'];

        $sizeValues = $valueTable->where('attribute_id', $sizeId)->get()->getResultArray();
        $colorValues = $valueTable->where('attribute_id', $colorId)->get()->getResultArray();

        $sizeMap = array_column($sizeValues, 'id', 'value');
        $colorMap = array_column($colorValues, 'id', 'value');

        foreach ($variants as $variant) {
            $variantId = $variantIds[$variant['sku']] ?? null;
            if (!$variantId) {
                continue;
            }

            $sizeValueId = $sizeMap[$variant['size']] ?? null;
            $colorValueId = $colorMap[$variant['color']] ?? null;

            if ($sizeValueId) {
                $exists = $pvaTable
                    ->where('variant_id', $variantId)
                    ->where('attribute_value_id', $sizeValueId)
                    ->get()
                    ->getRowArray();
                if (!$exists) {
                    $pvaTable->insert([
                        'variant_id' => $variantId,
                        'attribute_value_id' => $sizeValueId,
                    ]);
                }
            }

            if ($colorValueId) {
                $exists = $pvaTable
                    ->where('variant_id', $variantId)
                    ->where('attribute_value_id', $colorValueId)
                    ->get()
                    ->getRowArray();
                if (!$exists) {
                    $pvaTable->insert([
                        'variant_id' => $variantId,
                        'attribute_value_id' => $colorValueId,
                    ]);
                }
            }
        }
    }

    public function down()
    {
        $productTable = $this->db->table('products');
        $variantTable = $this->db->table('product_variants');
        $pvaTable = $this->db->table('product_variant_attribute_values');

        $productSlugs = ['organic-hoodie', 'canvas-backpack', 'travel-joggers'];
        $variantSkus = [
            'HOODIE-S-NAVY',
            'HOODIE-M-BEIGE',
            'HOODIE-L-GREEN',
            'BACKPACK-M-BLACK',
            'BACKPACK-L-NAVY',
            'JOGGER-S-BLACK',
            'JOGGER-M-NAVY',
            'JOGGER-L-GREEN',
        ];

        $variantRows = $variantTable->whereIn('sku', $variantSkus)->get()->getResultArray();
        $variantIds = array_column($variantRows, 'id');

        if (!empty($variantIds)) {
            $pvaTable->whereIn('variant_id', $variantIds)->delete();
        }

        $variantTable->whereIn('sku', $variantSkus)->delete();
        $productTable->whereIn('slug', $productSlugs)->delete();
    }
}
