<?php
session_start();
if (empty($_SESSION['is_admin'])) { header('Location: login.php'); exit; }
include '../database.php';

$ingredients = $conn->query("SELECT id, ingredient_name FROM ingredients ORDER BY ingredient_name");

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name = $_POST['name']; 
    $price = floatval($_POST['price']); 
    $desc = $_POST['description'];
    $recipe = $_POST['recipe_description'] ?? '';
    
    $img = '';
    if (!empty($_FILES['image']['tmp_name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $img = 'uploads/' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../'.$img);
    }
    
    $stmt = $conn->prepare("INSERT INTO products (name,price,description,recipe_description,image_path) VALUES (?, ?, ?, ?, ?)"); 
    $stmt->bind_param('sdsss', $name, $price, $desc, $recipe, $img);
    $stmt->execute();
    $product_id = $stmt->insert_id;
    
    // Add product ingredients
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
<title>Add Product</title>
<link rel="stylesheet" href="../css/style.css">
<style>
  .form-group { margin: 15px 0; }
  .ingredient-row { display: flex; gap: 10px; margin: 10px 0; align-items: center; }
  .ingredient-row input, .ingredient-row select { padding: 5px; }
  .ingredient-row button { padding: 5px 10px; }
  #ingredients-container { border: 1px solid #ddd; padding: 10px; border-radius: 5px; }
</style>
</head>
<body>
<div class="wrap">
  <h1>Add Product</h1>
  <p><a href="products.php">← Back to Products</a></p>
  
  <form method="post" enctype="multipart/form-data">
    <div class="form-group">
      <label>Product Name<br><input type="text" name="name" required></label>
    </div>
    
    <div class="form-group">
      <label>Price<br><input type="number" name="price" step="0.01" required></label>
    </div>
    
    <div class="form-group">
      <label>Description<br><textarea name="description" rows="3"></textarea></label>
    </div>
    
    <div class="form-group">
      <label>Recipe Description<br><textarea name="recipe_description" rows="3"></textarea></label>
    </div>
    
    <div class="form-group">
      <label>Image<br><input type="file" name="image" accept="image/*"></label>
    </div>
    
    <div class="form-group">
      <h3>Product Ingredients</h3>
      <div id="ingredients-container">
        <div class="ingredient-row">
          <select name="ingredient_id[]" required>
            <option value="">-- Select Ingredient --</option>
            <?php $ingredients = $conn->query("SELECT id, ingredient_name, unit FROM ingredients ORDER BY ingredient_name"); ?>
            <?php while ($ing = $ingredients->fetch_assoc()): ?>
              <option value="<?= $ing['id'] ?>" data-unit="<?= htmlspecialchars($ing['unit']) ?>"><?= htmlspecialchars($ing['ingredient_name']) ?></option>
            <?php endwhile; ?>
          </select>
          <input type="number" name="quantity_required[]" placeholder="Qty" step="0.01" required>
          <span class="unit-display">unit</span>
          <button type="button" onclick="removeIngredient(this)">Remove</button>
        </div>
      </div>
      <button type="button" onclick="addIngredientRow()">+ Add Ingredient</button>
    </div>
    
    <button type="submit">Save Product</button>
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

// Initialize unit display for first row
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('select[name="ingredient_id[]"]').forEach(select => {
    select.addEventListener('change', function() {
      updateUnitDisplay(this);
    });
  });
});
</script>
</body>
</html>
