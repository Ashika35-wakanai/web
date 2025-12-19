
<?php
session_start();
if (empty($_SESSION['is_admin'])) { header('Location: login.php'); exit; }
include '../database.php';

$ingredient_id = intval($_GET['id'] ?? 0);
if (!$ingredient_id) { header('Location: ingredients.php'); exit; }

$ingredient = $conn->query("SELECT * FROM ingredients WHERE id=$ingredient_id")->fetch_assoc();
if (!$ingredient) { header('Location: ingredients.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name  = $_POST['ingredient_name'];
    $qty   = floatval($_POST['stock_qty']);
    $unit  = $_POST['unit'];
    $limit = floatval($_POST['low_stock_limit']);

    $stmt = $conn->prepare(
        "UPDATE ingredients 
         SET ingredient_name=?, stock_qty=?, unit=?, low_stock_limit=? 
         WHERE id=?"
    );
    $stmt->bind_param('sdsdi', $name, $qty, $unit, $limit, $ingredient_id);
    $stmt->execute();

    // 🔒 RETURN ONLY JS (NO HEADERS, NO ERRORS)
    echo '<!doctype html>
    <html><body>
    <script>
        window.parent.closeEditModal();
        window.parent.location.reload();
    </script>
    </body></html>';
    exit;
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Edit Ingredient</title>
<link rel="stylesheet" href="../css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Viga&family=Seaweed+Script&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/design.css">
<link rel="stylesheet" href="../css/admin.css">
<style>
  .modal,
  .modal.show {
  z-index: 9999 !important;
  }

  .modal-content {
  z-index: 10000 !important;
  } 
  

body {
  background: var(--panel);
  margin: 0;
  padding: 15px;
}

.edit-container {
  max-width: 100%;
  margin: 0;
  background: var(--panel);
  padding: 15px 30px;
  border-radius: 0;
  box-shadow: none;
  border: none;
}

.back-btn {
  padding: 7px 14px;
  background: linear-gradient(135deg, #8a6847, #5c432f);
  color: #fff;
  border: none;
  border-radius: 7px;
  cursor: pointer;
  font-weight: 700;
  font-size: 13px;
  margin-bottom: 12px;
  transition: transform 0.1s ease;
}

.back-btn:hover {
  transform: translateY(-1px);
}

.form-group {
  margin-bottom: 14px;
}

.form-group label {
  display: block;
  color: var(--accent-dark);
  font-weight: 700;
  margin-bottom: 5px;
  font-size: 14px;
}

.form-group input[type="text"],
.form-group input[type="number"] {
  width: 100%;
  padding: 9px;
  border: 2px solid rgba(161,120,81,0.25);
  border-radius: 7px;
  font-family: 'Viga', sans-serif;
  font-size: 13px;
  box-sizing: border-box;
  background: #fffaf1;
  color: #4a3626;
}

.form-group input:focus {
  outline: none;
  border-color: rgba(161,120,81,0.5);
}

.submit-btn {
  width: 100%;
  padding: 12px;
  background: linear-gradient(135deg, #A17851, #6F5A47);
  color: #fff;
  border: none;
  border-radius: 9px;
  font-weight: 700;
  cursor: pointer;
  font-size: 15px;
  margin-top: 14px;
  transition: transform 0.1s ease;
}

.submit-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}
/* MOBILE / TABLET FIX */
@media (max-width: 1024px) {

  body {
    margin: 0;
    overflow-y: auto;
  }

  .edit-container {
    max-height: 70vh;       /* modal never taller than screen */
    overflow-y: auto;      /* allow scrolling */
    padding-bottom: 140px; /* space for keyboard */
  }

  input, select, button {
    font-size: 16px; /* prevents iOS zoom */
  }
}

</style>
</head>
<script>
document.querySelectorAll('input, select').forEach(el => {
  el.addEventListener('focus', () => {
    setTimeout(() => {
      el.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });
    }, 300);
  });
});
</script>
<body>
<div class="edit-container">
  <button type="button" onclick="window.parent.closeEditModal()" class="back-btn">← Back to Ingredients</button>
  <form method="post">
    <div class="form-group">
      <label>Ingredient Name</label>
      <input type="text" name="ingredient_name" value="<?= htmlspecialchars($ingredient['ingredient_name']) ?>" required>
    </div>
    
    <div class="form-group">
      <label>Stock Quantity</label>
      <input type="number" name="stock_qty" step="0.01" value="<?= $ingredient['stock_qty'] ?>" required>
    </div>
    
    <div class="form-group">
      <label>Unit</label>
      <input type="text" name="unit" value="<?= htmlspecialchars($ingredient['unit']) ?>" required>
    </div>
    
    <div class="form-group">
      <label>Low Stock Limit</label>
      <input type="number" name="low_stock_limit" step="0.01" value="<?= $ingredient['low_stock_limit'] ?>" required>
    </div>
    
    <button type="submit" class="submit-btn">Update Ingredient</button>
  </form>
</div>
</body>
</html>


