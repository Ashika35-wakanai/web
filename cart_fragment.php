<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'database.php';
$cart = $_SESSION['cart'] ?? [];
$ids = array_keys($cart);
$products = [];
if ($ids) {
  $in = implode(',', array_map('intval',$ids));
  $res = $conn->query("SELECT id,name,price FROM products WHERE id IN ($in)");
  while($r = $res->fetch_assoc()) $products[$r['id']] = $r;
}
$total = 0;
foreach($cart as $pid=>$qty) if(isset($products[$pid])) $total += $products[$pid]['price']*$qty;
?>
<?php if(empty($cart)): ?>
<p>Your cart is empty.</p>
<?php else: ?>
<form action="update_cart.php" method="post">
  <div class="cart-list-wrap">
    <table class="cart">
      <tr>
        <th style="width:56%">Product</th>
        <th style="width:14%">Qty</th>
        <th style="width:12%">Price</th>
        <th style="width:8%"></th>
        <th style="width:10%">Subtotal</th>
      </tr>
      <?php foreach($cart as $pid=>$qty): if(!isset($products[$pid])) continue; $p = $products[$pid]; $sub = $p['price']*$qty; ?>
      <tr>
        <td class="cart-prod-name"><?=htmlspecialchars($p['name'])?></td>
        <td>
          <input class="ajax-qty" type="number" data-id="<?= $pid ?>" name="qty[<?= $pid ?>]" value="<?= $qty ?>" min="0" style="width:70px">
        </td>
        <td class="cart-price">₱<?= number_format($p['price'],2) ?></td>
        <td class="cart-del">
          <button type="button" class="del-btn ajax-del" data-id="<?= $pid ?>" title="Remove" aria-label="Remove">
            <span class="del-x">✕</span>
          </button>
        </td>
        <td class="cart-sub">₱<?= number_format($sub,2) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <p class="total">Total: ₱<?= number_format($total,2) ?></p>
  <button type="submit">Update Cart</button>
</form>

<!-- Transaction Details Section -->
<div class="transaction-details calculations">
  <div class="detail-row">
    <span>Subtotal:</span>
    <span id="subtotal">₱<?= number_format($total,2) ?></span>
  </div>
  <div class="detail-row">
    <span>Discount:</span>
    <span id="discount-amount">₱0.00</span>
  </div>
  <div class="detail-row total-row">
    <span>Total:</span>
    <span id="final-total">₱<?= number_format($total,2) ?></span>
  </div>
  <div class="detail-row">
    <span>Cash:</span>
    <input type="number" id="cash-input" placeholder="₱0.00" min="0" step="0.01">
  </div>
  <div class="detail-row">
    <span>Change:</span>
    <span id="change-amount">₱0.00</span>
  </div>
</div>

<!-- Payment Method Buttons -->
<div class="payment-methods">
  <button type="button" class="payment-btn cash-btn" onclick="selectPaymentMethod('CASH')">CASH</button>
  <button type="button" class="payment-btn gcash-btn" onclick="selectPaymentMethod('GCASH')">GCASH</button>
  <button type="button" class="payment-btn grabf-btn" onclick="selectPaymentMethod('RFID')">RFID</button>
</div>

<!-- Number Pad -->
<form id="void-cart-form" method="post" style="display:none;">
  <input type="hidden" name="void_cart" value="1">
</form>
<div class="number-pad">
  <div class="numpad-row">
    <button type="button" onclick="appendToInput('1')">1</button>
    <button type="button" onclick="appendToInput('2')">2</button>
    <button type="button" onclick="appendToInput('3')">3</button>
    <button type="button" class="func-btn" onclick="holdTransaction()">HOLD</button>
  </div>
  <div class="numpad-row">
    <button type="button" onclick="appendToInput('4')">4</button>
    <button type="button" onclick="appendToInput('5')">5</button>
    <button type="button" onclick="appendToInput('6')">6</button>
    <button type="button" class="func-btn clr-btn" onclick="clearInput()">CLR</button>
  </div>
  <div class="numpad-row">
    <button type="button" onclick="appendToInput('7')">7</button>
    <button type="button" onclick="appendToInput('8')">8</button>
    <button type="button" onclick="appendToInput('9')">9</button>
    <button type="button" class="func-btn disc-btn" onclick="applyDiscount()">DISC</button>
  </div>
  <div class="numpad-row">
    <button type="button" onclick="appendToInput('00')">00</button>
    <button type="button" onclick="appendToInput('0')">0</button>
    <button type="button" onclick="appendToInput('.')">.</button>
    <button type="button" class="func-btn void-btn" onclick="voidTransaction()">VOID</button>
  </div>
  <div class="numpad-row"></div>
</div>

<!-- Checkout Button -->
<div class="checkout-area">
  <a class="btn checkout-btn" href="checkout.php">CHECKOUT</a>
</div>

<div id="cart-meta" data-total="<?= $total ?>" style="display:none;"></div>

<?php endif; ?>
