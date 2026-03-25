<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple Shop</title>
</head>
<body>
<h1>Simple Shop</h1>
<p><a href="<?= site_url('cart') ?>">View Cart</a></p>

<form method="get" action="<?= site_url('/') ?>">
    <label>Search products:</label>
    <input type="text" name="q" value="<?= esc($search ?? '') ?>">
    <button type="submit">Search</button>
    <?php if (!empty($search)) : ?>
        <a href="<?= site_url('/') ?>">Clear</a>
    <?php endif ?>
</form>

<h2>Attributes</h2>
<ul>
    <?php foreach ($attributes as $attribute) : ?>
        <li><strong><?= esc($attribute['name']) ?></strong> (<?= esc($attribute['slug']) ?>)</li>
        <ul>
            <?php foreach ($attributeValues as $value) : ?>
                <?php if ($value['attribute_id'] == $attribute['id']): ?>
                    <li><?= esc($value['value']) ?></li>
                <?php endif ?>
            <?php endforeach ?>
        </ul>
    <?php endforeach ?>
</ul>

<?php if (session()->getFlashdata('error')) : ?>
    <p style="color:red"><?= session()->getFlashdata('error') ?></p>
<?php endif ?>

<ul>
    <?php foreach ($products as $product) : ?>
        <li>
            <h3><?= esc($product['name']) ?> - $<?= number_format($product['base_price'],2) ?></h3>
            <p><?= esc($product['description']) ?></p>

            <form method="post" action="<?= site_url('add-to-cart') ?>" class="product-form" data-product-id="<?= $product['id'] ?>" data-base-price="<?= number_format($product['base_price'], 2) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="hidden" name="variant_id" class="variant-id" value="">

                <label>Size:</label>
                <select name="size" class="size-select">
                    <option value="">Select size</option>
                    <?php foreach ($sizeValues as $size): ?>
                        <option value="<?= esc($size) ?>"><?= esc($size) ?></option>
                    <?php endforeach ?>
                </select>

                <label>Color:</label>
                <select name="color" class="color-select">
                    <option value="">Select color</option>
                    <?php foreach ($colorValues as $color): ?>
                        <option value="<?= esc($color) ?>"><?= esc($color) ?></option>
                    <?php endforeach ?>
                </select>

                <p>Selected Price: $<span class="product-price"><?= number_format($product['base_price'],2) ?></span></p>

                <label>Quantity:</label>
                <input type="number" name="quantity" value="1" min="1" style="width:60px">
                <button type="submit">Add to cart</button>
            </form>
        </li>
    <?php endforeach ?>
</ul>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var variantsByProduct = <?= json_encode($variantByProduct, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

        document.querySelectorAll('.product-form').forEach(function (form) {
            var productId = form.dataset.productId;
            var basePrice = parseFloat(form.dataset.basePrice || '0');
            var sizeSelect = form.querySelector('.size-select');
            var colorSelect = form.querySelector('.color-select');
            var priceEl = form.querySelector('.product-price');
            var variantInput = form.querySelector('.variant-id');

            function findVariant(size, color) {
                var variants = variantsByProduct[productId] || [];
                for (var i = 0; i < variants.length; i++) {
                    var v = variants[i];
                    if (v.size === size && v.color === color) {
                        return v;
                    }
                }
                return null;
            }

            function updatePrice() {
                var size = sizeSelect.value;
                var color = colorSelect.value;
                var variant = null;

                if (size && color) {
                    variant = findVariant(size, color);
                }

                if (variant) {
                    priceEl.textContent = parseFloat(variant.price).toFixed(2);
                    variantInput.value = variant.id;
                } else {
                    priceEl.textContent = basePrice.toFixed(2);
                    variantInput.value = '';
                }
            }

            sizeSelect.addEventListener('change', updatePrice);
            colorSelect.addEventListener('change', updatePrice);
            updatePrice();
        });
    });
</script>
</body>
</html>
