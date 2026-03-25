<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart</title>
</head>
<body>
<h1>Your Cart</h1>
<p><a href="<?= site_url('/') ?>">Continue Shopping</a></p>

<?php if (empty($items)) : ?>
    <p>Your cart is empty.</p>
<?php else : ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
        <tr>
            <th>Product</th>
            <th>Variant</th>
            <th>Qty</th>
            <th>Price each</th>
            <th>Line total</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item) : ?>
            <tr>
                <td><?= esc($item['product']['name']) ?></td>
                <td><?= esc($item['variant'] ? $item['variant']['variant_name'] : 'N/A') ?></td>
                <td><?= esc($item['quantity']) ?></td>
                <td>$<?= number_format($item['price_each'],2) ?></td>
                <td>$<?= number_format($item['line_total'],2) ?></td>
                <td><a href="<?= site_url('cart/remove/' . $item['cart_id']) ?>">Remove</a></td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
    <p><strong>Total: $<?= number_format($total, 2) ?></strong></p>
<?php endif ?>
</body>
</html>
