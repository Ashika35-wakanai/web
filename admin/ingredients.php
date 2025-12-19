<?php
session_start();
if (empty($_SESSION['is_admin'])) { header('Location: login.php'); exit; }
include '../database.php';

// Sorting logic
$allowedCols = ['id' => 'id', 'ingredient_name' => 'ingredient_name', 'stock_qty' => 'stock_qty', 'low_stock_limit' => 'low_stock_limit'];
$sort = $_GET['sort'] ?? 'ingredient_name';
$dir = strtolower($_GET['dir'] ?? 'asc');
$sortCol = $allowedCols[$sort] ?? 'ingredient_name';
$dir = ($dir === 'desc') ? 'desc' : 'asc';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM ingredients WHERE id=$id");
    header('Location: ingredients.php'); exit;
}

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['ingredient_name'];
    $qty = floatval($_POST['stock_qty']);
    $unit = $_POST['unit'];
    $limit = floatval($_POST['low_stock_limit']);
    
    $stmt = $conn->prepare("INSERT INTO ingredients (ingredient_name, stock_qty, unit, low_stock_limit) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('sdsd', $name, $qty, $unit, $limit);
    $stmt->execute();
    header('Location: ingredients.php'); exit;
}

$orderSql = "$sortCol " . strtoupper($dir);
$ingredients = $conn->query("SELECT * FROM ingredients ORDER BY $orderSql");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Ingredients - Admin</title>
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

.ingredients-wrap {
  position: relative;
  margin-right: 0;
  min-height: 70vh;
}

.ingredients-title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  gap: 20px;
}

.ingredients-title-row h1 {
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

.ingredients-search {
  width: 300px;
  padding: 10px 14px;
  border: 2px solid rgba(161,120,81,0.25);
  border-radius: 10px;
  font-family: 'Viga', sans-serif;
  font-size: 14px;
  background: #fffaf1;
  color: #4a3626;
}

.ingredients-search:focus {
  outline: none;
  border-color: rgba(161,120,81,0.5);
}

.ingredients-grid {
  flex: 1;
  overflow-x: auto;
}

.ingredient-list-shell {
  border: 2px solid #A17851;
  border-radius: 14px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 8px 20px rgba(0,0,0,0.18);
}

.ingredient-list-table {
  width: 100%;
  border-collapse: collapse;
  background: transparent;
}

.ingredient-list-table thead {
  position: sticky;
  top: 0;
  z-index: 30;
  background: linear-gradient(135deg, rgba(161,120,81,0.22), rgba(111,90,71,0.12));
}

.ingredient-list-table th {
  padding: 14px 12px;
  text-align: left;
  font-weight: 700;
  color: var(--accent-dark);
  border-bottom: 2px solid rgba(161,120,81,0.15);
  position: sticky;
  top: 0;
  z-index: 31;
  background: linear-gradient(135deg, rgba(161,120,81,0.22), rgba(111,90,71,0.12));
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.ingredient-list-table th a {
  color: var(--accent-dark);
  text-decoration: none;
  font-weight: 700;
}

.ingredient-list-table th a:hover {
  color: var(--accent);
}

.ingredient-list-table tbody tr {
  border-bottom: 1px solid rgba(161,120,81,0.1);
  transition: background 0.15s ease;
}

.ingredient-list-table tbody tr:hover {
  background: #fffaf1;
}

.ingredient-list-table tbody tr.low-stock {
  background: linear-gradient(90deg, rgba(230,180,140,0.2), rgba(255,250,241,0.6));
}

.ingredient-list-table td {
  padding: 12px;
  color: #4a3626;
}

.ingredient-status {
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 6px;
  display: inline-block;
}

.status-ok {
  background: rgba(139,195,74,0.15);
  color: #558b2f;
}

.status-low {
  background: rgba(255,152,0,0.15);
  color: #e65100;
}

.edit-btn, .delete-btn {
  padding: 6px 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 700;
  font-size: 12px;
  margin-right: 6px;
  transition: transform 0.1s ease;
  color: #fff;
}

.edit-btn {
  background: linear-gradient(135deg, #c9a77c, #a17851);
}

.delete-btn {
  background: linear-gradient(135deg, #8a6847, #5c432f);
}

.edit-btn:hover, .delete-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.18);
}

.ingredient-add-panel {
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

.ingredient-add-panel h3 {
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
  max-width: 600px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.modal,
.modal.show {
  z-index: 9999 !important;
}
.modal-content {
  z-index: 10000 !important;
}



@media (max-width: 1200px) {
  .ingredient-add-panel { width: 360px; flex: 0 0 360px; }
}
/* --- Tablet view: make Add Ingredient panel smaller --- */
@media (max-width: 1024px) {

  .ingredient-add-panel {
    width: 260px !important;
    flex: 0 0 260px !important;
    padding: 14px !important;
    border-width: 3px !important;
    transform: scale(0.95);
    transform-origin: top right;
  }
  /* FIX SCALE BUG — REMOVE TRANSFORM */
  @media (max-width: 1024px) {
  .ingredient-add-panel {
    transform: none !important;
  }
  }


  .ingredient-add-panel h3 {
    font-size: 14px !important;
    margin-bottom: 10px !important;
  }

  .form-group-compact label {
    font-size: 12px !important;
    margin-bottom: 3px !important;
  }

  .form-group-compact input,
  .form-group-compact select {
    padding: 6px !important;
    font-size: 11px !important;
    height: 30px !important;
  }

  .form-submit {
    font-size: 13px !important;
    padding: 8px !important;
    height: 34px !important;
  }
  .modal,
  .modal.show {
  z-index: 9999 !important;
  }

  .modal-content {
  z-index: 10000 !important;
  }
  html, body {
  scroll-behavior: auto !important;
  }
  
  

}


</style>

</head>

<body 
  
  class="ingredients-page no-scroll ">


<main class="container">
  <div class="content-wrapper ingredients-wrap">
    <div class="ingredients-title-row">
      <a href="dashboard.php" class="back-to-dashboard">← Dashboard</a>
      <h1>Ingredients</h1>
        <input type="search"
       class="ingredients-search"
       placeholder="Search ingredients..."
       id="ingredientSearch">
       
      </div>
    
    <div class="admin-main-container">
      <div class="ingredients-grid">
        <div class="ingredient-list-shell">
        <table class="ingredient-list-table">
          <thead>
            <tr>
              <?php
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
                <a href="?sort=ingredient_name&dir=<?= nextDir('ingredient_name', $sort, $dir) ?>">Name<?= dirArrow('ingredient_name', $sort, $dir) ?></a>
              </th>
              <th>
                <a href="?sort=stock_qty&dir=<?= nextDir('stock_qty', $sort, $dir) ?>">Stock<?= dirArrow('stock_qty', $sort, $dir) ?></a>
              </th>
              <th>Unit</th>
              <th>
                <a href="?sort=low_stock_limit&dir=<?= nextDir('low_stock_limit', $sort, $dir) ?>">Low Limit<?= dirArrow('low_stock_limit', $sort, $dir) ?></a>
              </th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($ing = $ingredients->fetch_assoc()): 
              $isLow = $ing['stock_qty'] <= $ing['low_stock_limit'];
              $status = $isLow ? 'Low Stock' : 'OK';
              $statusClass = $isLow ? 'status-low' : 'status-ok';
              $rowClass = $isLow ? 'low-stock' : '';
            ?>
            <tr class="<?= $rowClass ?>">
              <td><?= $ing['id'] ?></td>
              <td><?= htmlspecialchars($ing['ingredient_name']) ?></td>
              <td><?= number_format($ing['stock_qty'], 2) ?></td>
              <td><?= htmlspecialchars($ing['unit']) ?></td>
              <td><?= number_format($ing['low_stock_limit'], 2) ?></td>
              <td><span class="ingredient-status <?= $statusClass ?>"><?= $status ?></span></td>
              <td>
                <button class="edit-btn" onclick="openEditModal(<?= $ing['id'] ?>)">Edit</button>
               
                <button class="delete-btn" onclick="if(confirm('Delete this ingredient?')) location.href='?delete=<?= $ing['id'] ?>'">Delete</button>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        </div>
      </div>

      <aside class="ingredient-add-panel">
        <h3>Add New Ingredient</h3>
        <form method="post">
          <div class="form-group-compact">
            <label>Ingredient Name</label>
            <input type="text" name="ingredient_name" required>
          </div>
          <div class="form-group-compact">
            <label>Stock Quantity</label>
            <input type="number" name="stock_qty" step="0.01" required>
          </div>
          <div class="form-group-compact">
            <label>Unit</label>
            <input type="text" name="unit" placeholder="e.g., kg, liter, pcs" value="pcs" required>
          </div>
          <div class="form-group-compact">
            <label>Low Stock Limit</label>
            <input type="number" name="low_stock_limit" step="0.01" required>
          </div>
          <button type="submit" class="form-submit">Add Ingredient</button>
        </form>
      </aside>
    </div>
  </div>
</main>

  <!-- Edit Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
    <iframe id="editFrame" style="width:100%; border:none; height:450px; display:block;"></iframe>
    </div>
  </div>
  <script>
function openEditModal(id) {
    const frame = document.getElementById('editFrame');
    frame.onload = autoResizeIframe;
    frame.src = 'ingredient_edit.php?id=' + id;
    document.getElementById('editModal').classList.add('show');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
    document.getElementById('editFrame').src = '';
}

window.closeEditModal = closeEditModal;

function autoResizeIframe() {
    const frame = document.getElementById('editFrame');
    if (frame && frame.contentWindow && frame.contentWindow.document.body) {
        frame.style.height =
          frame.contentWindow.document.body.scrollHeight + "px";
    }
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('editModal');
    if (event.target === modal) {
        closeEditModal();
    }
});

</script>
<script>
document.querySelectorAll('input, textarea').forEach(el => {
  el.addEventListener('focus', () => {
    setTimeout(() => {
      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 300);
  });
});
</script>
<script>
function filterIngredients() {
  const input = document.getElementById('ingredientSearch');
  const filter = input.value.toLowerCase();
  const rows = document.querySelectorAll('.ingredient-list-table tbody tr');

  rows.forEach(row => {
    const nameCell = row.querySelector('td:nth-child(2)');
    if (!nameCell) return;

    const text = nameCell.textContent.toLowerCase();
    row.style.display = text.includes(filter) ? '' : 'none';
  });
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

  const searchInput = document.getElementById('ingredientSearch');
  const tableRows = document.querySelectorAll(
    '.ingredient-list-table tbody tr'
  );

  if (!searchInput) {
    console.error('Search input NOT found');
    return;
  }

  searchInput.addEventListener('input', function () {
    const filter = this.value.toLowerCase();

    tableRows.forEach(row => {
      const nameCell = row.children[1]; // Ingredient Name column
      if (!nameCell) return;

      const text = nameCell.textContent.toLowerCase();
      row.style.display = text.includes(filter) ? '' : 'none';
    });
  });

});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('ingredientSearch');
  if (!input) return;

  input.addEventListener('input', filterIngredients);
});
</script>






</body>
</html>
