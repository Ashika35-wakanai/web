<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include 'database.php';
$action = $_POST['action'] ?? ($_GET['action'] ?? '');
$id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
$qty = isset($_POST['qty']) ? intval($_POST['qty']) : null;
$cart = &$_SESSION['cart'];
if (!is_array($cart)) $cart = [];

if ($action === 'add' && $id>0) {
  $cart[$id] = ($cart[$id] ?? 0) + 1;
  $_SESSION['cart'] = $cart;
} elseif ($action === 'remove' && $id>0) {
  unset($cart[$id]);
  $_SESSION['cart'] = $cart;
} elseif ($action === 'update' && $id>0 && $qty !== null) {
  if ($qty <= 0) unset($cart[$id]); else $cart[$id] = $qty;
  $_SESSION['cart'] = $cart;
}

// Capture the cart.php output (it's the inner HTML used inside .cart-summary)
ob_start();
include 'cart_fragment.php';
$html = ob_get_clean();

// Return HTML so client can replace .cart-summary contents
echo json_encode(['html' => $html]);
