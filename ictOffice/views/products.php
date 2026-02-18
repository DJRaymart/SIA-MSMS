<?php include 'header.php'; ?>

<h2>Inventory</h2>

<table border="1">
<tr>
  <th>Name</th><th>Category</th><th>Qty</th><th>Price</th>
</tr>
<?php foreach ($products as $p): ?>
<tr>
  <td><?= $p['name'] ?></td>
  <td><?= $p['category'] ?></td>
  <td><?= $p['quantity'] ?></td>
  <td><?= $p['price'] ?></td>
</tr>
<?php endforeach; ?>
</table>

<?php include 'footer.php'; ?>
