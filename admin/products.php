<?php
session_start();
if (empty($_SESSION['is_admin'])) { header('Location: login.php'); exit; }
include '../database.php';
// Sorting logic
$allowedCols = ['id' => 'id', 'name' => 'name', 'price' => 'price'];
$sort = $_GET['sort'] ?? 'id';
$dir = strtolower($_GET['dir'] ?? 'asc');
$sortCol = $allowedCols[$sort] ?? 'id';
$dir = ($dir === 'desc') ? 'desc' : 'asc';

$orderSql = "$sortCol " . strtoupper($dir);
$res = $conn->query("SELECT * FROM products ORDER BY $orderSql");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Products - Admin</title>
<link rel="stylesheet" href="../css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Viga&family=Seaweed+Script&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/design.css">
<link rel="stylesheet" href="../css/admin.css">
<style>
.admin-main-container {
  display: flex;
  justify-content: flex-start;
  gap: 20px;
  align-items: flex-start;
  position: relative;
}

.products-wrap {
  position: relative;
  margin-right: 0;
  min-height: 70vh;
}

.products-title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  gap: 20px;
}

.products-title-row h1 {
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

.products-search {
  width: 300px;
  padding: 10px 14px;
  border: 2px solid rgba(161,120,81,0.25);
  border-radius: 10px;
  font-family: 'Viga', sans-serif;
  font-size: 14px;
  background: #fffaf1;
  color: #4a3626;
}

.products-search:focus {
  outline: none;
  border-color: rgba(161,120,81,0.5);
  box-shadow: 0 2px 8px rgba(161,120,81,0.15);
}

.products-grid {
  flex: 1;
  max-height: calc(100vh - 200px);
  overflow-y: auto;
  padding-right: 5px;
  position: relative;
}

.product-list-shell {
  width: 100%;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(0,0,0,0.12);
  border: 2px solid rgba(161,120,81,0.25);
  background: #fffaf1;
}

.product-list-table {
  width: 100%;
  border-collapse: collapse;
  background: transparent;
}

.product-list-table thead {
  position: sticky;
  top: 0;
  background: linear-gradient(135deg, rgba(161,120,81,0.22), rgba(111,90,71,0.12));
  border-bottom: 2px solid rgba(161,120,81,0.4);
  z-index: 30;
  box-shadow: 0 4px 8px rgba(0,0,0,0.08);
}

.product-list-table thead th {
  position: sticky;
  top: 0;
  z-index: 31;
}

.product-list-table tbody tr:last-child td {
  border-bottom: none;
}

.product-list-table th {
  padding: 12px 14px;
  text-align: left;
  font-weight: 700;
  color: #4a3626;
  letter-spacing: 0.3px;
  font-size: 14px;
  cursor: pointer;
  background: linear-gradient(135deg, rgba(161,120,81,0.22), rgba(111,90,71,0.12));
  box-shadow: 0 4px 8px rgba(0,0,0,0.08);
}

.product-list-table th a {
  color: #4a3626;
  text-decoration: none;
}

.product-list-table th a:hover {
  text-decoration: underline;
}

.product-list-table td {
  padding: 10px 14px;
  border-bottom: 1px solid rgba(0,0,0,0.05);
  color: #4c3a2b;
  font-size: 14px;
  background: #fffaf1;
}

.product-list-table tbody tr:last-child td {
  border-bottom: none;
}

.product-list-table tbody tr:hover {
  background: linear-gradient(90deg, rgba(161,120,81,0.12), rgba(255,250,241,0.6));
}

.product-list-table .product-id {
  font-weight: 700;
  color: #4a3626;
}

.product-list-table .product-name {
  font-weight: 600;
  color: #3d2d1f;
}

.product-list-table .product-price {
  font-weight: 700;
  color: #8a6847;
}

.product-list-table .product-actions {
  display: flex;
  gap: 6px;
  align-items: center;
  white-space: nowrap;
}

.product-list-table a,
.product-list-table button {
  color: #fff;
  text-decoration: none;
  font-weight: 700;
  padding: 5px 10px;
  border-radius: 5px;
  font-size: 12px;
  cursor: pointer;
  border: none;
  transition: transform 0.1s ease, box-shadow 0.1s ease;
  display: inline-flex;
  align-items: center;
  background: linear-gradient(135deg, #c9a77c, #a17851);
}

.product-list-table .edit-btn {
  background: linear-gradient(135deg, #c9a77c, #a17851);
}

.product-list-table .edit-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.18);
}

.product-list-table .delete-btn {
  background: linear-gradient(135deg, #8a6847, #5c432f);
}

.product-list-table .delete-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.18);
}

.product-add-panel {
  position: sticky;
  top: 40px;
  width: 420px;
  max-height: calc(100vh - 140px);
  background: #C7AD8A;
  border: 4px solid #A17851;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 12px 28px rgba(0,0,0,0.2);
  overflow-y: auto;
  flex: 0 0 420px;
}

.product-add-panel h3 {
  margin-top: 0;
  color: #3d291a;
  font-size: 16px;
  font-weight: 700;
}

.form-group-compact {
  margin-bottom: 12px;
}

.form-group-compact label {
  display: block;
  color: #3d291a;
  font-weight: 700;
  margin-bottom: 4px;
  font-size: 13px;
}

.form-group-compact input,
.form-group-compact textarea,
.form-group-compact select {
  width: 100%;
  padding: 8px;
  border: 2px solid rgba(161,120,81,0.25);
  border-radius: 8px;
  font-family: 'Viga', sans-serif;
  font-size: 12px;
  box-sizing: border-box;
  background: #fffaf1;
  color: #4a3626;
}

.form-group-compact input:focus,
.form-group-compact textarea:focus,
.form-group-compact select:focus {
  outline: none;
  border-color: rgba(161,120,81,0.5);
}

.form-group-compact textarea {
  resize: vertical;
  min-height: 60px;
}

.form-submit {
  width: 100%;
  padding: 10px;
  background: linear-gradient(135deg, #A17851, #6F5A47);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  font-size: 14px;
  margin-top: 8px;
  transition: transform 0.1s ease;
}

.form-submit:hover {
  transform: translateY(-2px);
}

/* Modal for edit */
.modal {
  display: none;
  position: fixed;
  z-index: 200;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.4);
}

.modal.show {
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-content {
  background: #FDF7D7;
  padding: 0;
  border-radius: 16px;
  width: 90%;
  max-width: 1000px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

@media (max-width: 1200px) {
  .product-add-panel { width: 360px; flex: 0 0 360px; }
}
</style>
</head>
<body>
<main class="container">
  <div class="content-wrapper products-wrap">
    <div class="products-title-row">
      <a href="dashboard.php" class="back-to-dashboard">← Dashboard</a>
      <h1>Products</h1>
      <input type="search" class="products-search" placeholder="Search products..." id="productSearch" onkeyup="filterProducts()">
    </div>
    
    <div class="admin-main-container">
      <div class="products-grid">
        <div class="product-list-shell">
        <table class="product-list-table">
          <thead>
            <tr>
              <?php
                // compute next dir per column
                function nextDir($col, $sort, $dir) { return ($sort === $col && $dir === 'asc') ? 'desc' : 'asc'; }
                function dirArrow($col, $sort, $dir) {
                  if ($sort !== $col) return '';
                  return $dir === 'asc' ? ' ▲' : ' ▼';
                }
              ?>
              <th>
                <a href="?sort=id&dir=<?= nextDir('id', $sort, $dir) ?>">ID<?= dirArrow('id', $sort, $dir) ?></a>
              </th>
              <th>
                <a href="?sort=name&dir=<?= nextDir('name', $sort, $dir) ?>">Name<?= dirArrow('name', $sort, $dir) ?></a>
              </th>
              <th>
                <a href="?sort=price&dir=<?= nextDir('price', $sort, $dir) ?>">Price<?= dirArrow('price', $sort, $dir) ?></a>
              </th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($r=$res->fetch_assoc()): ?>
            <tr>
              <td class="product-id"><?= $r['id'] ?></td>
              <td class="product-name"><?= htmlspecialchars($r['name']) ?></td>
              <td class="product-price">₱<?= number_format($r['price'],2) ?></td>
              <td class="product-actions">
                <button class="edit-btn" onclick="openEditModal(<?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['name'])) ?>')">Edit</button>
                <a href="product_delete.php?id=<?= $r['id'] ?>" class="delete-btn" onclick="return confirm('Delete this product?')">Delete</a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        </div>
      </div>

      <!-- Add Product Panel (right) -->
      <aside class="product-add-panel">
        <h3>Add New Product</h3>
        <form method="post" action="product_add.php" enctype="multipart/form-data">
          <div class="form-group-compact">
            <label>Name</label>
            <input type="text" name="name" required>
          </div>
          <div class="form-group-compact">
            <label>Price</label>
            <input type="number" name="price" step="0.01" required>
          </div>
          <div class="form-group-compact">
            <label>Description</label>
            <textarea name="description"></textarea>
          </div>
          <div class="form-group-compact">
            <label>Image</label>
            <input type="file" name="image" accept="image/*">
          </div>
          <button type="submit" class="form-submit">Add Product</button>
        </form>
      </aside>
    </div>
  </div>
</main>

<!-- Edit Modal -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <iframe id="editFrame" style="width:100%; border:none; height:750px; display:block;"></iframe>
  </div>
</div>

<script>
function openEditModal(productId, productName) {
  document.getElementById('editFrame').src = 'product_edit.php?id=' + productId + '&modal=1';
  document.getElementById('editModal').classList.add('show');
}

function closeEditModal() {
  document.getElementById('editModal').classList.remove('show');
  document.getElementById('editFrame').src = '';
}

window.addEventListener('click', function(event) {
  const modal = document.getElementById('editModal');
  if (event.target === modal) {
    closeEditModal();
  }
});

function filterProducts() {
  const input = document.getElementById('productSearch');
  const filter = input.value.toLowerCase();
  const table = document.querySelector('.product-list-table tbody');
  const rows = table.getElementsByTagName('tr');
  
  for (let i = 0; i < rows.length; i++) {
    const nameCell = rows[i].getElementsByClassName('product-name')[0];
    const priceCell = rows[i].getElementsByClassName('product-price')[0];
    if (nameCell || priceCell) {
      const nameText = nameCell ? nameCell.textContent || nameCell.innerText : '';
      const priceText = priceCell ? priceCell.textContent || priceCell.innerText : '';
      if (nameText.toLowerCase().indexOf(filter) > -1 || priceText.toLowerCase().indexOf(filter) > -1) {
        rows[i].style.display = '';
      } else {
        rows[i].style.display = 'none';
      }
    }
  }
}
</script>
</body>
</html>
