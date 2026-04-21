<?php
require_once '../includes/db.php';
requireProducer();

$producer_id = $_SESSION['user_id'];
$edit_id     = (int)($_GET['edit'] ?? 0);
$product     = null;
$error       = '';

// Load product for editing
if ($edit_id) {
    $s = $conn->prepare("SELECT * FROM products WHERE id = ? AND producer_id = ?");
    $s->bind_param('ii', $edit_id, $producer_id);
    $s->execute();
    $product = $s->get_result()->fetch_assoc();
    if (!$product) {
        flash('Product not found.', 'error');
        redirect(BASE_URL . 'producer/products.php');
    }
}

$page_title = $product ? 'Edit Product' : 'Add Product';
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (float)($_POST['price']    ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $stock       = (int)($_POST['stock_quantity'] ?? 0);
    $featured    = isset($_POST['featured']) ? 1 : 0;

    if (!$name || $price <= 0 || !$category_id) {
        $error = 'Please fill in all required fields.';
    } else {
        // Handle image upload
        $image_path = $product['image'] ?? '';

        if (!empty($_FILES['image']['name'])) {
            $ext       = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed   = ['jpg','jpeg','png','gif','webp'];
            if (!in_array($ext, $allowed)) {
                $error = 'Only JPG, PNG, GIF or WebP images are allowed.';
            } else {
                $uploads_dir = SITE_ROOT . '/assets/uploads/';
                if (!is_dir($uploads_dir)) {
                    mkdir($uploads_dir, 0755, true);
                }
                $filename   = uniqid('product_') . '.' . $ext;
                $dest       = $uploads_dir . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $image_path = 'assets/uploads/' . $filename;
                } else {
                    $error = 'Failed to upload image. Check folder permissions.';
                }
            }
        }

        if (!$error) {
            if ($product) {
                // Update existing product
                $upd = $conn->prepare(
                    "UPDATE products SET name=?, description=?, price=?, image=?, category_id=?, stock_quantity=?, featured=? WHERE id=? AND producer_id=?"
                );
                $upd->bind_param('ssdsiiiii', $name, $description, $price, $image_path, $category_id, $stock, $featured, $edit_id, $producer_id);
                $upd->execute();
                flash('Product updated successfully.', 'success');
            } else {
                // Insert
                $ins = $conn->prepare(
                    "INSERT INTO products (name, description, price, image, category_id, producer_id, stock_quantity, featured) VALUES (?,?,?,?,?,?,?,?)"
                );
                $ins->bind_param('ssdsiiii', $name, $description, $price, $image_path, $category_id, $producer_id, $stock, $featured);
                $ins->execute();
                flash('Product added successfully.', 'success');
            }
            redirect(BASE_URL . 'producer/products.php');
        }
    }
}

require_once '../includes/header.php';
?>
<main>
<div class="container">
<div class="producer-layout">

    <?php include 'producer-sidebar.php'; ?>

    <div class="product-form-wrap">
        <h1 class="manage-heading"><?= $product ? 'Edit Product' : 'Add New Product' ?></h1>

        <?php if ($error): ?>
        <div class="alert alert-error"><span class="material-icons" style="font-size:16px;vertical-align:middle;">error</span> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="checkout-card">
                <h2 class="checkout-heading">Product Details</h2>

                <div class="form-group">
                    <label class="form-label">Product Name: <span style="color:var(--red);">*</span></label>
                    <input class="form-input" type="text" name="name" required
                           value="<?= htmlspecialchars($_POST['name'] ?? $product['name'] ?? '') ?>"
                           placeholder="e.g. Free-range Eggs">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Price (£): <span style="color:var(--red);">*</span></label>
                        <input class="form-input" type="number" name="price" min="0.01" step="0.01" required
                               value="<?= htmlspecialchars($_POST['price'] ?? $product['price'] ?? '') ?>"
                               placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Quantity:</label>
                        <input class="form-input" type="number" name="stock_quantity" min="0"
                               value="<?= htmlspecialchars($_POST['stock_quantity'] ?? $product['stock_quantity'] ?? 0) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Category: <span style="color:var(--red);">*</span></label>
                        <select class="form-input" name="category_id" required>
                            <option value="">-- Select --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                    <?= (($_POST['category_id'] ?? $product['category_id'] ?? 0) == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="visibility:hidden;">Featured</label>
                        <label style="display:flex;align-items:center;gap:10px;padding-top:10px;font-weight:700;">
                            <input type="checkbox" name="featured" value="1"
                                   <?= (($_POST['featured'] ?? $product['featured'] ?? 0) == 1) ? 'checked' : '' ?>
                                   style="width:18px;height:18px;accent-color:var(--green-primary);">
                            Feature on homepage
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description:</label>
                    <textarea class="form-input" name="description" rows="6"
                              placeholder="Describe the product — its origin, taste, uses..."><?= htmlspecialchars($_POST['description'] ?? $product['description'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="checkout-card">
                <h2 class="checkout-heading">Product Image</h2>

                <?php if ($product && $product['image']): ?>
                <div class="image-preview-wrap" style="margin-bottom:14px;">
                    <img src="<?= htmlspecialchars('../' . $product['image']) ?>"
                         alt="Current image" style="max-height:120px;border-radius:var(--radius-sm);">
                    <div class="form-hint" style="margin-top:6px;">Current image. Upload a new one to replace it.</div>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Upload Image (JPG, PNG, WebP):</label>
                    <input type="file" name="image" accept="image/*"
                           class="form-input" style="padding:8px;"
                           onchange="previewImg(this)">
                </div>
                <div id="img-preview" class="image-preview-wrap"></div>
            </div>

            <div style="display:flex;gap:12px;margin-top:6px;">
                <button type="submit" class="btn btn-primary btn-lg">
                    <span class="material-icons">save</span> <?= $product ? 'Save Changes' : 'Add Product' ?>
                </button>
                <a href="products.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>

</div>
</div>
</main>

<script>
function previewImg(input) {
    const preview = document.getElementById('img-preview');
    preview.innerHTML = '';
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'max-height:140px;border-radius:7px;border:2px solid var(--gray-mid);margin-top:8px;';
            preview.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
