<footer class="site-footer">
  <div class="wrap"></div>
</footer>

<!-- Footer navigation (mobile) -->
<?php
  $current = basename($_SERVER['SCRIPT_NAME']);
  function navActive($target, $current){
    return ($current === basename($target)) ? 'active' : '';
  }
?>
<div class="footer-nav-wrap">
  <nav class="footer-nav" aria-label="Bottom navigation">
    <a href="welcome.php" class="<?= navActive('welcome.php',$current) ?>" title="Home" <?= navActive('welcome.php',$current) ? 'aria-current="page"' : '' ?>>
      <img src="css/icons/home.svg" alt="Home">
      <span>Home</span>
    </a>
    <a href="index.php" class="<?= navActive('index.php',$current) ?>" title="Products" <?= navActive('index.php',$current) ? 'aria-current="page"' : '' ?>>
      <img src="css/icons/products.svg" alt="Products">
      <span>Products</span>
    </a>
    <a href="my_orders.php" class="<?= navActive('my_orders.php',$current) ?>" title="My Orders" <?= navActive('my_orders.php',$current) ? 'aria-current="page"' : '' ?>>
      <img src="css/icons/inventory.svg" alt="My Orders">
      <span>My Orders</span>
    </a>
    <a href="logout.php" title="Logout">
      <span style="font-size: 24px;">🚪</span>
      <span>Logout</span>
    </a>
  </nav>
</div>
