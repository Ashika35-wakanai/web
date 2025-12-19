<?php
session_start();
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }
$cart = $_SESSION['cart'] ?? [];
$cart[$id] = ($cart[$id] ?? 0) + 1;
$_SESSION['cart'] = $cart;

// Prefer an explicit return param, otherwise use a safe HTTP_REFERER when available
$return = 'index.php';
if (!empty($_GET['return'])) {
	$candidate = urldecode($_GET['return']);
	// Allow relative paths and same-host absolute URLs only
	if (strpos($candidate, 'http') === 0) {
		$p = parse_url($candidate);
		if (!empty($p['host']) && $p['host'] === ($_SERVER['HTTP_HOST'] ?? '')) {
			$return = $candidate;
		}
	} else {
		// treat as relative path (safe)
		$return = $candidate;
	}
} elseif (!empty($_SERVER['HTTP_REFERER'])) {
	$ref = $_SERVER['HTTP_REFERER'];
	$p = parse_url($ref);
	if (!empty($p['host']) && $p['host'] === ($_SERVER['HTTP_HOST'] ?? '')) {
		$return = ($p['path'] ?? '/') . (!empty($p['query']) ? ('?' . $p['query']) : '') . (!empty($p['fragment']) ? ('#' . $p['fragment']) : '');
	}
}

header('Location: ' . $return);
exit;
