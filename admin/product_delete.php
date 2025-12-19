<?php
session_start();
if (empty($_SESSION['is_admin'])) { header('Location: login.php'); exit; }
include '../database.php';

$product_id = intval($_GET['id'] ?? 0);
if (!$product_id) { header('Location: products.php'); exit; }

// Delete associated ingredients first
$conn->query("DELETE FROM product_ingredients WHERE product_id=$product_id");

// Delete associated order items
$conn->query("DELETE FROM order_items WHERE product_id=$product_id");

// Delete associated product order counts
$conn->query("DELETE FROM product_order_count WHERE product_id=$product_id");

// Delete the product
$conn->query("DELETE FROM products WHERE id=$product_id");

header('Location: products.php');
exit;
