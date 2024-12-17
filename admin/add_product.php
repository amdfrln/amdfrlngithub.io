<?php
require_once '../config/init.php';
include '../includes/admin_header.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
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
    
    $errors = [];
    $images = [];

    // Handle image uploads
    if (!empty($_FILES['images']['name'][0])) {
        // Buat direktori jika belum ada
        $upload_dir = "../assets/images/products/$category";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['images']['name'][$key];
            $file_size = $_FILES['images']['size'][$key];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Validasi file
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($file_ext, $allowed)) {
                $errors[] = "File $file_name harus berupa gambar (JPG, PNG, WEBP)";
                continue;
            }
            
            if ($file_size > 5000000) { // 5MB limit
                $errors[] = "File $file_name terlalu besar (max 5MB)";
                continue;
            }
            
            // Generate nama file unik
            $new_name = uniqid() . '.' . $file_ext;
            $upload_path = $upload_dir . '/' . $new_name;
            
            if (move_uploaded_file($tmp_name, $upload_path)) {
                $images[] = "assets/images/products/$category/$new_name";
            } else {
                $errors[] = "Gagal mengupload file $file_name";
            }
        }
    }

    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        $result = $db->products->insertOne([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'category' => $category,
            'stock' => $stock,
            'featured' => $featured,
            'images' => $images,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);

        if ($result->getInsertedCount() > 0) {
            $_SESSION['success'] = 'Produk berhasil ditambahkan';
            header('Location: products.php');
            exit;
        } else {
            $errors[] = 'Gagal menambahkan produk';
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1>Tambah Produk</h1>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Nama Produk</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="description" class="form-control" rows="4" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Harga</label>
                                    <input type="number" name="price" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kategori</label>
                                    <select name="category" class="form-control" required>
                                        <option value="sofa">Sofa</option>
                                        <option value="meja">Meja</option>
                                        <option value="kursi">Kursi</option>
                                        <option value="lemari">Lemari</option>
                                        <option value="tempat-tidur">Tempat Tidur</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Stok</label>
                                    <input type="number" name="stock" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="featured" class="form-check-input">
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
                                    <input type="file" name="images[]" class="form-control" multiple accept="image/*" required>
                                    <small class="text-muted">
                                        Format: JPG, PNG, WEBP (Max 5MB per file)<br>
                                        Bisa upload multiple gambar
                                    </small>
                                </div>
                                <div id="image-preview" class="mt-3"></div>
                                <button type="submit" class="btn btn-primary w-100 mt-3">
                                    <i class="fas fa-save"></i> Simpan Produk
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
// Preview gambar sebelum upload
document.querySelector('input[type="file"]').addEventListener('change', function() {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    for (const file of this.files) {
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-thumbnail mb-2 me-2';
                img.style.width = '100px';
                img.style.height = '100px';
                img.style.objectFit = 'cover';
                preview.appendChild(img);
            }
            reader.readAsDataURL(file);
        }
    }
});
</script>

<?php include '../includes/admin_footer.php'; ?> 