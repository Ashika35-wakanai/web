<?php
session_start();
if (empty($_SESSION['is_admin'])) { header('Location: login.php'); exit; }
include '../database.php';

$product_id = intval($_GET['id'] ?? 0);
if (!$product_id) { header('Location: products.php'); exit; }

$product = $conn->query("SELECT * FROM products WHERE id=$product_id")->fetch_assoc();
if (!$product) { header('Location: products.php'); exit; }

$product_ings = $conn->query("SELECT pi.ingredient_id, pi.quantity_required, i.ingredient_name, i.unit FROM product_ingredients pi JOIN ingredients i ON pi.ingredient_id = i.id WHERE pi.product_id=$product_id");
$all_ingredients = $conn->query("SELECT id, ingredient_name, unit FROM ingredients ORDER BY ingredient_name");

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name = $_POST['name'];
    $price = floatval($_POST['price']);
    $desc = $_POST['description'];
    $recipe = $_POST['recipe_description'] ?? '';
    
    $img = $product['image_path'];
    if (!empty($_FILES['image']['tmp_name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $img = 'uploads/' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../'.$img);
    }
    
    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, description=?, recipe_description=?, image_path=? WHERE id=?");
    $stmt->bind_param('sdsssi', $name, $price, $desc, $recipe, $img, $product_id);
    $stmt->execute();
    
    // Update ingredients
    $conn->query("DELETE FROM product_ingredients WHERE product_id=$product_id");
    if (isset($_POST['ingredient_id'])) {
        $stmt2 = $conn->prepare("INSERT INTO product_ingredients (product_id, ingredient_id, quantity_required) VALUES (?, ?, ?)");
        foreach ($_POST['ingredient_id'] as $idx => $ing_id) {
            $qty = floatval($_POST['quantity_required'][$idx] ?? 0);
            $ing_id = intval($ing_id);
            if ($ing_id > 0 && $qty > 0) {
                $stmt2->bind_param('iid', $product_id, $ing_id, $qty);
                $stmt2->execute();
            }
        }
    }
    
    header('Location: products.php');
    exit;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Edit Product</title>
<link rel="stylesheet" href="../css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Viga&family=Seaweed+Script&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/design.css">
<link rel="stylesheet" href="../css/admin.css">
<style>
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
.form-group input[type="number"],
.form-group textarea,
.form-group select {
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

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: rgba(161,120,81,0.5);
}

.form-group textarea {
  resize: vertical;
  min-height: 65px;
}

.current-image {
  max-width: 100px;
  margin-top: 6px;
  border-radius: 7px;
  border: 2px solid rgba(161,120,81,0.2);
}

.ingredients-section h3 {
  color: var(--accent-dark);
  font-size: 16px;
  margin: 0 0 10px 0;
}

#ingredients-container {
  border: 2px solid rgba(161,120,81,0.2);
  padding: 12px;
  border-radius: 8px;
  background: rgba(255,250,241,0.5);
  margin-bottom: 10px;
  max-height: 200px;
  overflow-y: auto;
}

.ingredient-row {
  display: flex;
  gap: 8px;
  margin: 8px 0;
  align-items: center;
}

.ingredient-row select {
  flex: 2;
  padding: 7px;
  font-size: 12px;
}

.ingredient-row input {
  flex: 1;
  padding: 7px;
  font-size: 12px;
}

.ingredient-row .unit-display {
  color: var(--accent-dark);
  font-weight: 600;
  font-size: 12px;
  min-width: 40px;
}

.ingredient-row button {
  padding: 6px 10px;
  background: linear-gradient(135deg, #8a6847, #5c432f);
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 11px;
  font-weight: 700;
}

.ingredient-row button:hover {
  transform: translateY(-1px);
}

.add-ingredient-btn {
  padding: 8px 14px;
  background: linear-gradient(135deg, #c9a77c, #a17851);
  color: #fff;
  border: none;
  border-radius: 7px;
  cursor: pointer;
  font-weight: 700;
  font-size: 13px;
}

.add-ingredient-btn:hover {
  transform: translateY(-1px);
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
</style>
</head>
<body>
<div class="edit-container">
  <button type="button" onclick="window.parent.closeEditModal()" class="back-btn">← Back to Products</button>
  <form method="post" enctype="multipart/form-data">
    <div class="form-group">
      <label>Product Name</label>
      <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
    </div>
    
    <div class="form-group">
      <label>Price</label>
      <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required>
    </div>
    
    <div class="form-group">
      <label>Description</label>
      <textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea>
    </div>
    
    <div class="form-group">
      <label>Recipe Description</label>
      <textarea name="recipe_description"><?= htmlspecialchars($product['recipe_description']) ?></textarea>
    </div>
    
    <div class="form-group">
      <label>Image</label>
      <input type="file" name="image" accept="image/*">
      <?php if ($product['image_path']): ?>
        <br><img src="../<?= htmlspecialchars($product['image_path']) ?>" class="current-image">
      <?php endif; ?>
    </div>
    
    <div class="form-group ingredients-section">
      <h3>Product Ingredients</h3>
      <div id="ingredients-container">
        <?php while ($pi = $product_ings->fetch_assoc()): ?>
        <div class="ingredient-row">
          <select name="ingredient_id[]" required onchange="updateUnitDisplay(this)">
            <option value="">-- Select Ingredient --</option>
            <?php $all_ingredients = $conn->query("SELECT id, ingredient_name, unit FROM ingredients ORDER BY ingredient_name"); ?>
            <?php while ($ing = $all_ingredients->fetch_assoc()): ?>
              <option value="<?= $ing['id'] ?>" data-unit="<?= htmlspecialchars($ing['unit']) ?>" <?= ($ing['id'] == $pi['ingredient_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($ing['ingredient_name']) ?>
              </option>
            <?php endwhile; ?>
          </select>
          <input type="number" name="quantity_required[]" placeholder="Qty" step="0.01" value="<?= $pi['quantity_required'] ?>" required>
          <span class="unit-display"><?= htmlspecialchars($pi['unit']) ?></span>
          <button type="button" onclick="removeIngredient(this)">Remove</button>
        </div>
        <?php endwhile; ?>
      </div>
      <button type="button" class="add-ingredient-btn" onclick="addIngredientRow()">+ Add Ingredient</button>
    </div>
    
    <button type="submit" class="submit-btn">Update Product</button>
  </form>
</div>

<script>
function addIngredientRow() {
  const container = document.getElementById('ingredients-container');
  const row = document.createElement('div');
  row.className = 'ingredient-row';
  
  const ingredientsOptions = document.querySelector('select[name="ingredient_id[]"]').innerHTML;
  
  row.innerHTML = `
    <select name="ingredient_id[]" required onchange="updateUnitDisplay(this)">
      ${ingredientsOptions}
    </select>
    <input type="number" name="quantity_required[]" placeholder="Qty" step="0.01" required>
    <span class="unit-display">unit</span>
    <button type="button" onclick="removeIngredient(this)">Remove</button>
  `;
  
  container.appendChild(row);
}

function removeIngredient(btn) {
  btn.parentElement.remove();
}

function updateUnitDisplay(select) {
  const unit = select.options[select.selectedIndex].dataset.unit || 'unit';
  select.parentElement.querySelector('.unit-display').textContent = unit;
}
</script>
</body>
</html>
