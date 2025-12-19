<?php
require_once __DIR__ . '/../vendor/autoload.php';
  session_start();
  if (empty($_SESSION['is_admin'])) { header('Location: login.php'); exit; }
  include '../database.php';

// Check if DomPDF is installed
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    die('<p style="color:red;"><strong>Error:</strong> DomPDF not installed. Please run: <code>composer install</code> in the project root.</p>');
}


use Dompdf\Dompdf;

// Get filter parameters
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? null;
$report_type = $_GET['type'] ?? 'sales'; // sales or ingredients
$generate_pdf = $_GET['generate_pdf'] ?? false;

// Sorting for sales report
$allowedSalesCols = ['id' => 'o.id', 'created_at' => 'o.created_at', 'customer_name' => 'o.customer_name', 'total_price' => 'o.total_price'];
$sort = $_GET['sort'] ?? 'created_at';
$dir = strtolower($_GET['dir'] ?? 'desc');
$sortCol = $allowedSalesCols[$sort] ?? 'o.created_at';
$dir = ($dir === 'desc') ? 'desc' : 'asc';

// Sorting for ingredients report
$allowedIngCols = ['ingredient_name' => 'i.ingredient_name', 'total_used' => 'total_used'];
$ing_sort = $_GET['ing_sort'] ?? 'ingredient_name';
$ing_dir = strtolower($_GET['ing_dir'] ?? 'asc');
$ingSortCol = $allowedIngCols[$ing_sort] ?? 'i.ingredient_name';
$ing_dir = ($ing_dir === 'desc') ? 'desc' : 'asc';

// Build date filter
$date_filter = "YEAR(o.created_at) = $year";
if ($month) {
    $date_filter .= " AND MONTH(o.created_at) = " . intval($month);
}

// ===== SALES REPORT (Orders bundled by Order ID) =====
if ($report_type === 'sales') {
    $orderSql = "$sortCol " . strtoupper($dir);
    // Get bundled orders with date/time
    $orders_res = $conn->query("
      SELECT o.id, o.customer_name, o.total_price, o.created_at,
             GROUP_CONCAT(CONCAT(p.name, ' x', oi.quantity) SEPARATOR ', ') as items,
             COUNT(oi.id) as item_count
      FROM orders o
      LEFT JOIN order_items oi ON o.id = oi.order_id
      LEFT JOIN products p ON oi.product_id = p.id
      WHERE o.status = 'accepted' AND $date_filter
      GROUP BY o.id
      ORDER BY $orderSql
    ");
    
    $total_revenue = $conn->query("
      SELECT SUM(o.total_price) as revenue
      FROM orders o
      WHERE o.status = 'accepted' AND $date_filter
    ")->fetch_assoc()['revenue'] ?? 0;
    
    $total_orders_count = $conn->query("
      SELECT COUNT(o.id) as total
      FROM orders o
      WHERE o.status = 'accepted' AND $date_filter
    ")->fetch_assoc()['total'] ?? 0;
    
    if ($generate_pdf) {
        $html = '<html><head><meta charset="utf-8"><link href="https://fonts.googleapis.com/css2?family=Viga&display=swap" rel="stylesheet"><style>body { font-family: "Viga", sans-serif; }</style></head><body>';
        $html .= '<h1>Sales Report - Orders by Order ID</h1>';
        $html .= '<p><strong>Period:</strong> ' . ($month ? date('F Y', mktime(0, 0, 0, $month, 1, $year)) : $year) . '</p>';
        $html .= '<table style="width:100%; border-collapse:collapse; margin:20px 0; font-family: Viga, sans-serif;">';
        $html .= '<tr style="background:#f0f0f0;"><th style="border:1px solid #ddd; padding:10px;">Order ID</th><th style="border:1px solid #ddd; padding:10px;">Date & Time</th><th style="border:1px solid #ddd; padding:10px;">Customer</th><th style="border:1px solid #ddd; padding:10px;">Items</th><th style="border:1px solid #ddd; padding:10px;">Total</th></tr>';
        
        $orders_res = $conn->query("
          SELECT o.id, o.customer_name, o.total_price, o.created_at,
                 GROUP_CONCAT(CONCAT(p.name, ' x', oi.quantity) SEPARATOR ', ') as items
          FROM orders o
          LEFT JOIN order_items oi ON o.id = oi.order_id
          LEFT JOIN products p ON oi.product_id = p.id
          WHERE o.status = 'accepted' AND $date_filter
          GROUP BY o.id
          ORDER BY o.created_at DESC
        ");
        
        while ($row = $orders_res->fetch_assoc()) {
            $html .= '<tr>';
            $html .= '<td style="border:1px solid #ddd; padding:10px;">#' . $row['id'] . '</td>';
            $html .= '<td style="border:1px solid #ddd; padding:10px;">' . date('M d, Y H:i', strtotime($row['created_at'])) . '</td>';
            $html .= '<td style="border:1px solid #ddd; padding:10px;">' . htmlspecialchars($row['customer_name'] ?: 'Guest') . '</td>';
            $html .= '<td style="border:1px solid #ddd; padding:10px;">' . htmlspecialchars($row['items']) . '</td>';
            $html .= '<td style="border:1px solid #ddd; padding:10px; text-align:right;">₱' . number_format($row['total_price'], 2) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '<tr style="background:#f0f0f0; font-weight:bold;">';
        $html .= '<td colspan="4" style="border:1px solid #ddd; padding:10px;">TOTAL</td>';
        $html .= '<td style="border:1px solid #ddd; padding:10px; text-align:right;">₱' . number_format($total_revenue, 2) . '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '<p style="margin-top:20px;"><strong>Total Orders:</strong> ' . $total_orders_count . '</p>';
        $html .= '</body></html>';
        
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $filename = 'sales_report_' . ($month ? date('Y-m', mktime(0, 0, 0, $month, 1, $year)) : $year) . '.pdf';
        $dompdf->stream($filename);
        exit;
    }
}

// ===== INGREDIENTS REPORT (Usage per product per month) =====
else if ($report_type === 'ingredients') {
    // Get ingredient usage data
    $ingredients_res = $conn->query("
      SELECT i.ingredient_name, p.name as product_name, i.unit,
             SUM(pi.quantity_required * oi.quantity) as total_used
      FROM order_items oi
      JOIN product_ingredients pi ON oi.product_id = pi.product_id
      JOIN ingredients i ON pi.ingredient_id = i.id
      JOIN products p ON oi.product_id = p.id
      JOIN orders o ON oi.order_id = o.id
      WHERE o.status = 'accepted' AND $date_filter
      GROUP BY i.id, p.id
      ORDER BY i.ingredient_name, p.name
    ");
    
    // Summary by ingredient
    $ingredient_summary = $conn->query("
      SELECT i.ingredient_name, i.unit,
             SUM(pi.quantity_required * oi.quantity) as total_used
      FROM order_items oi
      JOIN product_ingredients pi ON oi.product_id = pi.product_id
      JOIN ingredients i ON pi.ingredient_id = i.id
      JOIN orders o ON oi.order_id = o.id
      WHERE o.status = 'accepted' AND $date_filter
      GROUP BY i.id
      ORDER BY i.ingredient_name
    ");
    
    if ($generate_pdf) {
        $html = '<html><head><meta charset="utf-8"><link href="https://fonts.googleapis.com/css2?family=Viga&display=swap" rel="stylesheet"><style>body { font-family: "Viga", sans-serif; }</style></head><body>';
        $html .= '<h1>Ingredient Usage Report</h1>';
        $html .= '<p><strong>Period:</strong> ' . ($month ? date('F Y', mktime(0, 0, 0, $month, 1, $year)) : $year) . '</p>';
        
        $html .= '<h2>Summary by Ingredient</h2>';
        $html .= '<table style="width:100%; border-collapse:collapse; margin:20px 0; font-family: Viga, sans-serif;">';
        $html .= '<tr style="background:#f0f0f0;"><th style="border:1px solid #ddd; padding:10px;">Ingredient</th><th style="border:1px solid #ddd; padding:10px;">Total Used</th><th style="border:1px solid #ddd; padding:10px;">Unit</th></tr>';
        
        $ingredient_summary = $conn->query("
          SELECT i.ingredient_name, i.unit,
                 SUM(pi.quantity_required * oi.quantity) as total_used
          FROM order_items oi
          JOIN product_ingredients pi ON oi.product_id = pi.product_id
          JOIN ingredients i ON pi.ingredient_id = i.id
          JOIN orders o ON oi.order_id = o.id
          WHERE o.status = 'accepted' AND $date_filter
          GROUP BY i.id
          ORDER BY i.ingredient_name
        ");
        
        while ($row = $ingredient_summary->fetch_assoc()) {
            $html .= '<tr>';
            $html .= '<td style="border:1px solid #ddd; padding:10px;">' . htmlspecialchars($row['ingredient_name']) . '</td>';
            $html .= '<td style="border:1px solid #ddd; padding:10px; text-align:right;">' . number_format($row['total_used'], 2) . '</td>';
            $html .= '<td style="border:1px solid #ddd; padding:10px;">' . htmlspecialchars($row['unit']) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        
        $html .= '<h2 style="margin-top:30px;">Detail by Product</h2>';
        $html .= '<table style="width:100%; border-collapse:collapse; margin:20px 0; font-family: Viga, sans-serif;">';
        $html .= '<tr style="background:#f0f0f0;"><th style="border:1px solid #ddd; padding:10px;">Ingredient</th><th style="border:1px solid #ddd; padding:10px;">Product</th><th style="border:1px solid #ddd; padding:10px;">Amount Used</th><th style="border:1px solid #ddd; padding:10px;">Unit</th></tr>';
        
        $ingredients_res = $conn->query("
          SELECT i.ingredient_name, p.name as product_name, i.unit,
                 SUM(pi.quantity_required * oi.quantity) as total_used
          FROM order_items oi
          JOIN product_ingredients pi ON oi.product_id = pi.product_id
          JOIN ingredients i ON pi.ingredient_id = i.id
          JOIN products p ON oi.product_id = p.id
          JOIN orders o ON oi.order_id = o.id
          WHERE o.status = 'accepted' AND $date_filter
          GROUP BY i.id, p.id
          ORDER BY i.ingredient_name, p.name
        ");
        
        while ($row = $ingredients_res->fetch_assoc()) {
            $html .= '<tr>';
            $html .= '<td style="border:1px solid #ddd; padding:10px;">' . htmlspecialchars($row['ingredient_name']) . '</td>';
            $html .= '<td style="border:1px solid #ddd; padding:10px;">' . htmlspecialchars($row['product_name']) . '</td>';
            $html .= '<td style="border:1px solid #ddd; padding:10px; text-align:right;">' . number_format($row['total_used'], 2) . '</td>';
            $html .= '<td style="border:1px solid #ddd; padding:10px;">' . htmlspecialchars($row['unit']) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        $html .= '</body></html>';
        
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $filename = 'ingredient_report_' . ($month ? date('Y-m', mktime(0, 0, 0, $month, 1, $year)) : $year) . '.pdf';
        $dompdf->stream($filename);
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Reports - Admin</title>
<link rel="stylesheet" href="../css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Viga&family=Seaweed+Script&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/design.css">
<link rel="stylesheet" href="../css/admin.css">
<style>
body {
  font-family: 'Viga', sans-serif;
}

.reports-wrap {
  position: relative;
  margin-right: 0;
  min-height: 70vh;
}

.reports-title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  gap: 20px;
}

.reports-title-row h1 {
  margin: 0;
  flex: 1;
  text-align: center;
}

.back-to-dashboard {
  padding: 8px 14px;
  background: linear-gradient(135deg, #8a6847, #5c432f);
  color: #fff;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 700;
  font-size: 13px;
  transition: transform 0.1s ease;
}

.back-to-dashboard:hover {
  transform: translateY(-1px);
}

.report-filter {
  background: linear-gradient(135deg, #D4BFA0, #C7AD8A);
  border: 3px solid rgba(161,120,81,0.5);
  border-radius: 14px;
  padding: 18px 20px;
  margin-bottom: 24px;
  box-shadow: 0 6px 14px rgba(0,0,0,0.15);
}

.report-filter form {
  display: flex;
  gap: 14px;
  align-items: center;
  flex-wrap: wrap;
}

.report-filter label {
  color: #3d291a;
  font-weight: 700;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.report-filter input,
.report-filter select {
  padding: 8px 12px;
  border: 2px solid rgba(161,120,81,0.3);
  border-radius: 7px;
  font-family: 'Viga', sans-serif;
  font-size: 13px;
  background: #fffaf1;
  color: #4a3626;
}

.report-filter input:focus,
.report-filter select:focus {
  outline: none;
  border-color: rgba(161,120,81,0.6);
}

.report-filter button,
.report-filter .btn {
  padding: 9px 16px;
  background: linear-gradient(135deg, #A17851, #6F5A47);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  font-size: 13px;
  transition: transform 0.1s ease;
  text-decoration: none;
  display: inline-block;
}

.report-filter button:hover,
.report-filter .btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.tabs {
  display: flex;
  gap: 12px;
  margin: 24px 0;
  border-bottom: 3px solid rgba(161,120,81,0.3);
}

.tab-btn {
  padding: 12px 20px;
  border: none;
  background: linear-gradient(135deg, #E8D4B8, #D9C3A5);
  cursor: pointer;
  font-size: 15px;
  font-weight: 700;
  border-bottom: 4px solid transparent;
  transition: all 0.2s;
  border-radius: 8px 8px 0 0;
  color: #5c432f;
}

.tab-btn.active {
  background: linear-gradient(135deg, #C7AD8A, #B39A7A);
  border-bottom-color: #A17851;
  color: #3d291a;
}

.tab-btn:hover {
  transform: translateY(-2px);
}

.tab-content {
  display: none;
}

.tab-content.active {
  display: block;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.tab-content h2 {
  font-family: 'Seaweed Script', cursive;
  color: var(--accent-dark);
  margin: 0 0 20px 0;
  font-size: 28px;
}

.tab-content h3 {
  color: var(--accent-dark);
  margin: 24px 0 14px 0;
  font-size: 20px;
  font-weight: 700;
  border-bottom: 2px solid rgba(161,120,81,0.3);
  padding-bottom: 6px;
}

.report-table-shell {
  border: 2px solid #A17851;
  border-radius: 14px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
  margin-bottom: 24px;
}

.report-table {
  width: 100%;
  border-collapse: collapse;
  background: transparent;
}

.report-table thead {
  background: linear-gradient(135deg, rgba(161,120,81,0.22), rgba(111,90,71,0.12));
}

.report-table th {
  padding: 14px 12px;
  text-align: left;
  font-weight: 700;
  color: var(--accent-dark);
  border-bottom: 2px solid rgba(161,120,81,0.15);
}

.report-table th a {
  color: var(--accent-dark);
  text-decoration: none;
  font-weight: 700;
  display: block;
}

.report-table th a:hover {
  color: var(--accent);
}

.report-table tbody tr {
  border-bottom: 1px solid rgba(161,120,81,0.1);
  transition: background 0.15s ease;
}

.report-table tbody tr:hover {
  background: #fffaf1;
}

.report-table td {
  padding: 12px;
  color: #4a3626;
}

.report-summary {
  background: linear-gradient(135deg, #F5EBD8, #E8DACC);
  border: 3px solid rgba(161,120,81,0.4);
  padding: 18px 20px;
  border-radius: 14px;
  margin-top: 24px;
  box-shadow: 0 6px 14px rgba(0,0,0,0.12);
}

.report-summary p {
  margin: 8px 0;
  color: #5c432f;
  font-size: 16px;
  font-weight: 700;
}

.empty-state {
  background: linear-gradient(135deg, #E8D4B8, #D9C3A5);
  border: 2px dashed rgba(161,120,81,0.5);
  color: var(--accent-dark);
  padding: 24px;
  border-radius: 14px;
  text-align: center;
  font-weight: 600;
  font-size: 15px;
}
</style>
</head>
<body>
<main class="container">
  <div class="content-wrapper reports-wrap">
    <div class="reports-title-row">
      <a href="dashboard.php" class="back-to-dashboard">← Dashboard</a>
      <h1>Reports</h1>
      <div style="width: 140px;"></div>
    </div>

  <div class="report-filter">
    <form method="get" id="filterForm">
      <input type="hidden" name="type" id="typeInput" value="<?= htmlspecialchars($report_type) ?>">
      <label>Year: <input type="number" name="year" value="<?= $year ?>" min="2020" onchange="this.form.submit()"></label>
      <label>Month: 
        <select name="month" onchange="this.form.submit()">
          <option value="">All Months</option>
          <?php for($m=1; $m<=12; $m++): ?>
            <option value="<?= $m ?>" <?= ($month == $m) ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
          <?php endfor; ?>
        </select>
      </label>
      <a class="btn" href="?type=<?= htmlspecialchars($report_type) ?>&year=<?= $year ?>&month=<?= $month ?>&generate_pdf=1" style="text-decoration:none;">Download PDF</a>
    </form>
  </div>

  <div class="tabs">
    <button class="tab-btn <?= ($report_type === 'sales') ? 'active' : '' ?>" onclick="switchTab('sales')">Sales Report (By Order)</button>
    <button class="tab-btn <?= ($report_type === 'ingredients') ? 'active' : '' ?>" onclick="switchTab('ingredients')">Ingredient Usage Report</button>
  </div>

  <!-- SALES REPORT TAB -->
  <div id="sales" class="tab-content <?= ($report_type === 'sales') ? 'active' : '' ?>">
    <h2>Sales Report: <?= $month ? date('F Y', mktime(0, 0, 0, $month, 1, $year)) : $year ?></h2>
    
    <?php if ($orders_res && $orders_res->num_rows > 0): ?>
      <div class="report-table-shell">
      <table class="report-table">
        <thead>
        <tr>
          <?php
            function nextDirSales($col, $sort, $dir) { return ($sort === $col && $dir === 'asc') ? 'desc' : 'asc'; }
            function dirArrowSales($col, $sort, $dir) {
              if ($sort !== $col) return '';
              return $dir === 'asc' ? ' ▲' : ' ▼';
            }
            $params = "type=sales&year=$year" . ($month ? "&month=$month" : "");
          ?>
          <th>
            <a href="?<?= $params ?>&sort=id&dir=<?= nextDirSales('id', $sort, $dir) ?>">Order ID<?= dirArrowSales('id', $sort, $dir) ?></a>
          </th>
          <th>
            <a href="?<?= $params ?>&sort=created_at&dir=<?= nextDirSales('created_at', $sort, $dir) ?>">Date & Time<?= dirArrowSales('created_at', $sort, $dir) ?></a>
          </th>
          <th>
            <a href="?<?= $params ?>&sort=customer_name&dir=<?= nextDirSales('customer_name', $sort, $dir) ?>">Customer<?= dirArrowSales('customer_name', $sort, $dir) ?></a>
          </th>
          <th>Items</th>
          <th style="text-align:right;">
            <a href="?<?= $params ?>&sort=total_price&dir=<?= nextDirSales('total_price', $sort, $dir) ?>" style="text-align:right; display:block;">Total<?= dirArrowSales('total_price', $sort, $dir) ?></a>
          </th>
        </tr>
        </thead>
        <tbody>
        <?php 
        $orders_res = $conn->query("
          SELECT o.id, o.customer_name, o.total_price, o.created_at,
                 GROUP_CONCAT(CONCAT(p.name, ' x', oi.quantity) SEPARATOR ', ') as items
          FROM orders o
          LEFT JOIN order_items oi ON o.id = oi.order_id
          LEFT JOIN products p ON oi.product_id = p.id
          WHERE o.status = 'accepted' AND $date_filter
          GROUP BY o.id
          ORDER BY o.created_at DESC
        ");
        while ($row = $orders_res->fetch_assoc()): 
        ?>
        <tr>
          <td style="text-align:center;"><strong>#<?= $row['id'] ?></strong></td>
          <td><?= date('M d, Y H:i', strtotime($row['created_at'])) ?></td>
          <td><?= htmlspecialchars($row['customer_name'] ?: 'Guest') ?></td>
          <td><?= htmlspecialchars($row['items']) ?></td>
          <td style="text-align:right;"><strong>₱<?= number_format($row['total_price'], 2) ?></strong></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      </div>

      <div class="report-summary">
        <p><strong>Total Orders:</strong> <?= $total_orders_count ?></p>
        <p><strong>Total Revenue:</strong> ₱<?= number_format($total_revenue, 2) ?></p>
      </div>
    <?php else: ?>
      <p class="empty-state">No sales data available for the selected period.</p>
    <?php endif; ?>
  </div>

  <!-- INGREDIENTS REPORT TAB -->
  <div id="ingredients" class="tab-content <?= ($report_type === 'ingredients') ? 'active' : '' ?>">
    <h2>Ingredient Usage Report: <?= $month ? date('F Y', mktime(0, 0, 0, $month, 1, $year)) : $year ?></h2>
    
    <?php 
    $ingredient_summary = $conn->query("
      SELECT i.ingredient_name, i.unit,
             SUM(pi.quantity_required * oi.quantity) as total_used
      FROM order_items oi
      JOIN product_ingredients pi ON oi.product_id = pi.product_id
      JOIN ingredients i ON pi.ingredient_id = i.id
      JOIN orders o ON oi.order_id = o.id
      WHERE o.status = 'accepted' AND $date_filter
      GROUP BY i.id
      ORDER BY i.ingredient_name
    ");
    
    if ($ingredient_summary && $ingredient_summary->num_rows > 0): 
    ?>
      <h3>Summary by Ingredient</h3>
      <div class="report-table-shell">
      <table class="report-table">
        <thead>
        <tr>
          <th>Ingredient</th>
          <th style="text-align:right;">Total Used</th>
          <th style="text-align:center;">Unit</th>
        </tr>
        </thead>
        <tbody>
        <?php 
        $ingredient_summary = $conn->query("
          SELECT i.ingredient_name, i.unit,
                 SUM(pi.quantity_required * oi.quantity) as total_used
          FROM order_items oi
          JOIN product_ingredients pi ON oi.product_id = pi.product_id
          JOIN ingredients i ON pi.ingredient_id = i.id
          JOIN orders o ON oi.order_id = o.id
          WHERE o.status = 'accepted' AND $date_filter
          GROUP BY i.id
          ORDER BY i.ingredient_name
        ");
        while ($row = $ingredient_summary->fetch_assoc()): 
        ?>
        <tr>
          <td><?= htmlspecialchars($row['ingredient_name']) ?></td>
          <td style="text-align:right;"><strong><?= number_format($row['total_used'], 2) ?></strong></td>
          <td style="text-align:center;"><?= htmlspecialchars($row['unit']) ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      </div>

      <h3>Detail by Product</h3>
      <div class="report-table-shell">
      <table class="report-table">
        <thead>
        <tr>
          <th>Ingredient</th>
          <th>Product</th>
          <th style="text-align:right;">Amount Used</th>
          <th style="text-align:center;">Unit</th>
        </tr>
        </thead>
        <tbody>
        <?php 
        $ingredients_res = $conn->query("
          SELECT i.ingredient_name, p.name as product_name, i.unit,
                 SUM(pi.quantity_required * oi.quantity) as total_used
          FROM order_items oi
          JOIN product_ingredients pi ON oi.product_id = pi.product_id
          JOIN ingredients i ON pi.ingredient_id = i.id
          JOIN products p ON oi.product_id = p.id
          JOIN orders o ON oi.order_id = o.id
          WHERE o.status = 'accepted' AND $date_filter
          GROUP BY i.id, p.id
          ORDER BY i.ingredient_name, p.name
        ");
        while ($row = $ingredients_res->fetch_assoc()): 
        ?>
        <tr>
          <td><?= htmlspecialchars($row['ingredient_name']) ?></td>
          <td><?= htmlspecialchars($row['product_name']) ?></td>
          <td style="text-align:right;"><strong><?= number_format($row['total_used'], 2) ?></strong></td>
          <td style="text-align:center;"><?= htmlspecialchars($row['unit']) ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      </div>
    <?php else: ?>
      <p class="empty-state">No ingredient data available for the selected period.</p>
    <?php endif; ?>
  </div>
  </div>
</main>

<script>
function switchTab(tabName) {
  // Hide all tabs
  document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
  
  // Show selected tab
  document.getElementById(tabName).classList.add('active');
  event.target.classList.add('active');
  
  // Update form and reload
  document.getElementById('typeInput').value = tabName;
  document.getElementById('filterForm').submit();
}
</script>
</body>
</html>
