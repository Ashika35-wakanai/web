<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
$qtys = $_POST['qty'] ?? [];
$cart = $_SESSION['cart'] ?? [];
foreach($qtys as $id => $q) {
    $id = intval($id); $q = intval($q);
    if ($q <= 0) unset($cart[$id]); else $cart[$id] = $q;
}
$_SESSION['cart'] = $cart;
header('Location: index.php');
exit;
