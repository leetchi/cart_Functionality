<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart</title>
</head>
<body>
<h1>Your Cart</h1>
<p>
    <a href="<?= site_url('/') ?>">Home Page</a>
    |
    <a href="<?= site_url('/') ?>">Continue Shopping</a>
</p>

<?php if (session()->getFlashdata('error')) : ?>
    <p style="color:red"><?= session()->getFlashdata('error') ?></p>
<?php endif ?>

<?php if (empty($items)) : ?>
    <p>Your cart is empty.</p>
<?php else : ?>
    <form method="post" action="<?= site_url('cart/clear') ?>">
        <?= csrf_field() ?>
        <button type="submit">Clear Cart</button>
    </form>
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
                <td>
                    <form method="post" action="<?= site_url('cart/update/' . $item['cart_id']) ?>">
                        <?= csrf_field() ?>
                        <input type="number" name="quantity" value="<?= esc($item['quantity']) ?>" min="1" style="width:60px">
                        <button type="submit">Update</button>
                    </form>
                </td>
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
