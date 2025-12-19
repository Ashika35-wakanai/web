<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['is_admin'])) {
  header('Location: welcome.php');
  exit;
}
// index.php - POS interface
include 'database.php';

// --- Product Filter Logic ---
$filter = isset($_GET['filter']) ? $_GET['filter'] : "";
if ($filter != "") {
  $stmt = $conn->prepare("SELECT id, name, price, description, image_path FROM products WHERE description LIKE ? ORDER BY id DESC");
  $search = "%$filter%";
  $stmt->bind_param("s", $search);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $result = $conn->query("SELECT id, name, price, description, image_path FROM products ORDER BY id DESC");
}

// Function to check product availability and get ingredients
function getProductAvailability($product_id, $conn) {
  $ings = $conn->query("
    SELECT pi.quantity_required, i.ingredient_name, i.stock_qty, i.unit 
    FROM product_ingredients pi 
    JOIN ingredients i ON pi.ingredient_id = i.id 
    WHERE pi.product_id=$product_id
  ");
  $ingredients = [];
  $available = true;
  while ($ing = $ings->fetch_assoc()) {
    $ingredients[] = $ing;
    if ($ing['stock_qty'] < $ing['quantity_required']) {
      $available = false;
    }
  }
  return ['available' => $available, 'ingredients' => $ingredients];
}
?>
<!doctype html>
<html class="no-scroll">
<head>
<meta charset="utf-8">
<title>POS - Products</title>
<!-- Core styles -->
<link rel="stylesheet" href="css/style.css">
<!-- Design theme (Viga + Seaweed Script used by design) -->
<link href="https://fonts.googleapis.com/css2?family=Viga&family=Seaweed+Script&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/design.css">
</head>
<body class="menu-page no-scroll">
<?php include 'includes/header.php'; ?>

<?php if (isset($_GET['order']) && $_GET['order'] === 'placed'): ?>
<div style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; background: linear-gradient(135deg, #4CAF50, #45a049); color: white; padding: 16px 32px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.3); font-family: 'Viga', sans-serif; font-size: 16px; animation: slideDown 0.4s ease;">
  ✓ Order placed successfully! Check <a href="my_orders.php" style="color: #FFF; text-decoration: underline; font-weight: 700;">My Orders</a> to track it.
</div>
<style>
@keyframes slideDown {
  from { top: -100px; opacity: 0; }
  to { top: 20px; opacity: 1; }
}
</style>
<script>
setTimeout(() => {
  const msg = document.querySelector('[style*="slideDown"]');
  if (msg) {
    msg.style.transition = 'opacity 0.5s, top 0.5s';
    msg.style.opacity = '0';
    msg.style.top = '-100px';
    setTimeout(() => msg.remove(), 500);
  }
}, 5000);
</script>
<?php endif; ?>

<main class="container">
  <div class="content-wrapper">
    <h1>Menu Category</h1>
    <div class="product-filters">
      <a href="?filter=Milk Series" class="btn" style="margin-right:5px;">Milk Series</a>
      <a href="?filter=Fruit Soda" class="btn" style="margin-right:5px;">Fruit Soda</a>
      <a href="?filter=Iced Coffee" class="btn" style="margin-right:5px;">Iced Coffee</a>
      <a href="?filter=Hot Coffee" class="btn" style="margin-right:5px;">Hot Coffee</a>
      <a href="index.php" class="btn" style="background:#6c757d;">Show All</a>
    </div>
    <div class="main-container">
    <div class="product-grid">
      <?php
        // Load all products into an array so we can group size variants
        $products = [];
        while($r = $result->fetch_assoc()) { $products[] = $r; }

        // Group by base name (strip size after last " - ")
        $groups = [];
        foreach ($products as $p) {
          $parts = explode(' - ', $p['name']);
          $base = trim($parts[0]);
          if (!isset($groups[$base])) $groups[$base] = [];
          $groups[$base][] = $p;
        }

        foreach ($groups as $baseName => $variants):
          // choose a representative variant for image/description
          $rep = $variants[0];
      ?>
        <?php $isSoda = (stripos($baseName,'soda') !== false) || (isset($rep['description']) && stripos($rep['description'],'soda') !== false); ?>
        <div class="product-card group-card <?= $isSoda ? 'soda-series' : '' ?>" id="product-group-<?= md5($baseName) ?>">
          <div class="product-img-wrap">
          <?php
            // Build image path from product name
            $imageFile = $baseName . '.png';
            $imagePath = 'image_path/' . $imageFile;

            // Fallback if image does not exist
            if (!file_exists($imagePath)) {
            $imagePath = 'image_path/placeholder.png';
            }
         ?>
          <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($rep['name']) ?>">
          </div>
          <div class="product-info">
            <?php if (!$isSoda): ?>
              <h3><?= htmlspecialchars($baseName) ?></h3>
            <?php else: ?>
              <div class="soda-title"><?= htmlspecialchars($baseName) ?></div>
            <?php endif; ?>
            <?php
              // show price range or single price
              $prices = array_map(function($v){ return $v['price']; }, $variants);
              $min = min($prices); $max = max($prices);
            ?>
            <?php if (!$isSoda): ?>
              <p class="price"><?= ($min==$max) ? '₱'.number_format($min,2) : 'From ₱'.number_format($min,2) ?></p>
            <?php endif; ?>

            <div class="product-sizes">
              <?php foreach ($variants as $v):
                $avail = getProductAvailability($v['id'], $conn);
                $available = $avail['available'];
                // determine size label if present
                $label = (strpos($v['name'], ' - ') !== false) ? trim(explode(' - ', $v['name'])[1]) : 'Price';
                $ret = urlencode(($_SERVER['REQUEST_URI'] ?? 'index.php') . '#product-' . $v['id']);
              ?>
                <div class="size-option <?= $available ? '' : 'unavailable' ?>">
                  <div class="size-label"><?= htmlspecialchars($label) ?></div>
                  <div class="size-price">₱<?= number_format($v['price'],2) ?></div>
                  <div class="size-action">
                    <?php if ($available): ?>
                      <a class="btn add-cart-btn" href="add_to_cart.php?id=<?= $v['id'] ?>&return=<?= $ret ?>">Add</a>
                    <?php else: ?>
                      <button class="btn" disabled>Out</button>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="cart-summary">
      <h2>Order Summary</h2>
      <?php include 'cart_fragment.php'; ?>
    </div>
  </div>
  </div><!-- end content-wrapper -->
</main>
</main>
<?php include 'includes/footer.php'; ?>
</body>
<script src="js/cart.js"></script>
</html>
