<?php

namespace App\Controllers;

use App\Models\AttributeModel;
use App\Models\AttributeValueModel;
use App\Models\CartItemModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;

class Home extends BaseController
{
    public function index()
    {
        $productModel = new ProductModel();
        $variantModel = new ProductVariantModel();
        $attributeModel = new AttributeModel();
        $attributeValueModel = new AttributeValueModel();

        $search = trim((string) $this->request->getGet('q'));
        if ($search !== '') {
            $products = $productModel
                ->groupStart()
                ->like('name', $search)
                ->orLike('description', $search)
                ->groupEnd()
                ->findAll();
        } else {
            $products = $productModel->findAll();
        }

        $productVariants = $variantModel
            ->where('is_active', 1)
            ->findAll();

        $attributes = $attributeModel->findAll();
        $attributeValues = $attributeValueModel->findAll();

        $sizeAttributeId = null;
        $colorAttributeId = null;
        foreach ($attributes as $attribute) {
            if (strtolower($attribute['slug']) === 'size') {
                $sizeAttributeId = $attribute['id'];
            }
            if (strtolower($attribute['slug']) === 'color') {
                $colorAttributeId = $attribute['id'];
            }
        }

        $sizeValues = [];
        $colorValues = [];
        foreach ($attributeValues as $value) {
            if ($sizeAttributeId && $value['attribute_id'] == $sizeAttributeId) {
                $sizeValues[] = $value['value'];
            }
            if ($colorAttributeId && $value['attribute_id'] == $colorAttributeId) {
                $colorValues[] = $value['value'];
            }
        }

        $db = \Config\Database::connect();
        $variantAttributes = [];
        if (!empty($productVariants)) {
            $variantIds = array_column($productVariants, 'id');

            $rows = $db->table('product_variant_attribute_values pva')
                ->select('pva.variant_id, attr.slug as attribute_slug, av.value')
                ->join('attribute_values av', 'pva.attribute_value_id = av.id')
                ->join('attributes attr', 'av.attribute_id = attr.id')
                ->whereIn('pva.variant_id', $variantIds)
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $variantAttributes[$row['variant_id']][$row['attribute_slug']] = $row['value'];
            }
        }

        $variantByProduct = [];
        foreach ($productVariants as $variant) {
            $variant['size'] = $variantAttributes[$variant['id']]['size'] ?? null;
            $variant['color'] = $variantAttributes[$variant['id']]['color'] ?? null;
            $variantByProduct[$variant['product_id']][] = $variant;
        }

        return view('shop/index', [
            'products' => $products,
            'variantByProduct' => $variantByProduct,
            'attributes' => $attributes,
            'attributeValues' => $attributeValues,
            'sizeValues' => $sizeValues,
            'colorValues' => $colorValues,
            'search' => $search,
        ]);
    }

    public function addToCart()
    {
        helper('url');

        $productId = $this->request->getPost('product_id');
        $variantId = $this->request->getPost('variant_id');
        $selectedSize = $this->request->getPost('size');
        $selectedColor = $this->request->getPost('color');
        $quantity = max(1, (int) $this->request->getPost('quantity'));

        $productModel = new ProductModel();
        $variantModel = new ProductVariantModel();
        $attributeValueModel = new AttributeValueModel();
        $cartModel = new CartItemModel();

        $product = $productModel->find($productId);
        if (!$product) {
            return redirect()->back()->with('error', 'Product not found');
        }

        $priceEach = (float) $product['base_price'];

        // If the user provides size/color, resolve the matching variant
        if (!$variantId && $selectedSize && $selectedColor) {
            $db = \Config\Database::connect();
            $sizeRow = $attributeValueModel->where('value', $selectedSize)->first();
            $colorRow = $attributeValueModel->where('value', $selectedColor)->first();

            if ($sizeRow && $colorRow) {
                $sizeVariantIds = $db->table('product_variant_attribute_values')
                    ->select('variant_id')
                    ->where('attribute_value_id', $sizeRow['id'])
                    ->get()
                    ->getResultArray();

                $colorVariantIds = $db->table('product_variant_attribute_values')
                    ->select('variant_id')
                    ->where('attribute_value_id', $colorRow['id'])
                    ->get()
                    ->getResultArray();

                $sizeIds = array_column($sizeVariantIds, 'variant_id');
                $colorIds = array_column($colorVariantIds, 'variant_id');
                $common = array_intersect($sizeIds, $colorIds);

                foreach ($common as $candidate) {
                    $candidateVariant = $variantModel->find($candidate);
                    if ($candidateVariant && $candidateVariant['product_id'] == $productId) {
                        $variantId = $candidate;
                        break;
                    }
                }
            }
        }

        if ($variantId) {
            $variant = $variantModel->find($variantId);
            if ($variant) {
                $priceEach = (float) $variant['price'];
            }
        }

        $sessionId = session()->get('cart_session_id');
        if (!$sessionId) {
            $sessionId = bin2hex(random_bytes(16));
            session()->set('cart_session_id', $sessionId);
        }

        $existing = $cartModel
            ->where('session_id', $sessionId)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId ?: null)
            ->first();

        if ($existing) {
            $cartModel->update($existing['id'], [
                'quantity' => $existing['quantity'] + $quantity,
            ]);
        } else {
            $cartModel->insert([
                'session_id' => $sessionId,
                'product_id' => $productId,
                'variant_id' => $variantId ?: null,
                'quantity' => $quantity,
                'price_each' => $priceEach,
            ]);
        }

        return redirect()->to('/cart');
    }

    public function cart()
    {
        $sessionId = session()->get('cart_session_id');
        if (!$sessionId) {
            return redirect()->to('/')->with('error', 'No cart yet.');
        }

        $cartModel = new CartItemModel();

        $items = $cartModel
            ->where('session_id', $sessionId)
            ->findAll();

        $productModel = new ProductModel();
        $variantModel = new ProductVariantModel();

        $detailItems = [];
        $total = 0;

        foreach ($items as $item) {
            $product = $productModel->find($item['product_id']);
            $variant = $item['variant_id'] ? $variantModel->find($item['variant_id']) : null;
            $line = $item['price_each'] * $item['quantity'];
            $total += $line;

            $detailItems[] = [
                'cart_id' => $item['id'],
                'product' => $product,
                'variant' => $variant,
                'quantity' => $item['quantity'],
                'price_each' => $item['price_each'],
                'line_total' => $line,
            ];
        }

        return view('shop/cart', [
            'items' => $detailItems,
            'total' => $total,
        ]);
    }

    public function removeCartItem($id)
    {
        $cartModel = new CartItemModel();
        $cartModel->delete($id);
        return redirect()->back();
    }

    public function updateCartItem($id)
    {
        $sessionId = session()->get('cart_session_id');
        if (!$sessionId) {
            return redirect()->to('/')->with('error', 'No cart yet.');
        }

        $quantity = max(1, (int) $this->request->getPost('quantity'));
        $cartModel = new CartItemModel();

        $item = $cartModel
            ->where('id', $id)
            ->where('session_id', $sessionId)
            ->first();

        if (!$item) {
            return redirect()->back()->with('error', 'Cart item not found.');
        }

        $cartModel->update($id, [
            'quantity' => $quantity,
        ]);

        return redirect()->back();
    }

    public function clearCart()
    {
        $sessionId = session()->get('cart_session_id');
        if (!$sessionId) {
            return redirect()->to('/')->with('error', 'No cart yet.');
        }

        $cartModel = new CartItemModel();
        $cartModel->where('session_id', $sessionId)->delete();

        return redirect()->back();
    }
}
