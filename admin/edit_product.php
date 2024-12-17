<?php
require_once '../config/init.php';
include '../includes/admin_header.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$product_id = $_GET['id'] ?? null;
if (!$product_id || !isValidObjectId($product_id)) {
    header('Location: products.php');
    exit;
}

$product = $db->products->findOne(['_id' => new MongoDB\BSON\ObjectId($product_id)]);
if (!$product) {
    header('Location: products.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = (float) $_POST['price'];
    $category = $_POST['category'];
    $stock = (int) $_POST['stock'];
    $featured = isset($_POST['featured']) ? true : false;

    // Handle image upload
    $images = $product->images; // Keep existing images by default
    if (!empty($_FILES['images']['name'][0])) {
        $images = [];
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['images']['name'][$key];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $new_name = uniqid() . '.' . $file_ext;
            $upload_path = "../assets/images/products/$category/$new_name";
            
            if (move_uploaded_file($tmp_name, $upload_path)) {
                $images[] = "assets/images/products/$category/$new_name";
            }
        }
    }

    // Update product in database
    $result = $db->products->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($product_id)],
        ['$set' => [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'category' => $category,
            'stock' => $stock,
            'featured' => $featured,
            'images' => $images
        ]]
    );

    if ($result->getModifiedCount() > 0) {
        $_SESSION['success'] = 'Produk berhasil diperbarui';
        header('Location: products.php');
        exit;
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1>Edit Produk</h1>
            </div>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Nama Produk</label>
                                    <input type="text" name="name" class="form-control" 
                                           value="<?php echo $product->name; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="description" class="form-control" rows="4" 
                                              required><?php echo $product->description; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Harga</label>
                                    <input type="number" name="price" class="form-control" 
                                           value="<?php echo $product->price; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kategori</label>
                                    <select name="category" class="form-control" required>
                                        <option value="sofa" <?php echo $product->category === 'sofa' ? 'selected' : ''; ?>>Sofa</option>
                                        <option value="meja" <?php echo $product->category === 'meja' ? 'selected' : ''; ?>>Meja</option>
                                        <option value="kursi" <?php echo $product->category === 'kursi' ? 'selected' : ''; ?>>Kursi</option>
                                        <option value="lemari" <?php echo $product->category === 'lemari' ? 'selected' : ''; ?>>Lemari</option>
                                        <option value="tempat-tidur" <?php echo $product->category === 'tempat-tidur' ? 'selected' : ''; ?>>Tempat Tidur</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Stok</label>
                                    <input type="number" name="stock" class="form-control" 
                                           value="<?php echo $product->stock; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="featured" class="form-check-input" 
                                               <?php echo $product->featured ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Produk Unggulan</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Gambar Produk</label>
                                    <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                                    <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar</small>
                                </div>
                                <div class="current-images">
                                    <?php foreach ($product->images as $image): ?>
                                    <img src="<?php echo $image; ?>" alt="" class="img-thumbnail mb-2" style="width: 100px;">
                                    <?php endforeach; ?>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?> 