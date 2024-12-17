<?php
require_once 'config/init.php';
include 'includes/header.php';

// Ambil semua kategori produk
$categories = $db->products->distinct('category');

// Ambil semua produk
$products = $db->products->find();
?>

<div class="container my-5">
    <h1 class="text-center mb-5 animate__animated animate__fadeIn">Koleksi Furniture Kami</h1>

    <!-- Search Bar -->
    <div class="search-container animate__animated animate__fadeIn">
        <input type="text" class="search-input" placeholder="Cari furniture...">
    </div>

    <!-- Filter Buttons -->
    <div class="filter-buttons animate__animated animate__fadeIn">
        <button class="filter-button active" onclick="filterProducts('all')">Semua</button>
        <?php foreach ($categories as $category): ?>
        <button class="filter-button" onclick="filterProducts('<?php echo $category; ?>')">
            <?php echo ucfirst($category); ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- Products Grid -->
    <div class="row">
        <?php foreach ($products as $product): ?>
        <div class="col-md-4 mb-4 animate-on-scroll" data-category="<?php echo $product->category; ?>">
            <div class="product-card">
                <div class="product-image-wrapper">
                    <img src="<?php echo getImagePath($product->images[0]); ?>" class="card-img-top" alt="<?php echo $product->name; ?>">
                    <?php if(isset($product->images[1])): ?>
                    <div class="product-image-hover">
                        <img src="<?php echo getImagePath($product->images[1]); ?>" class="card-img-top" alt="<?php echo $product->name; ?>">
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $product->name; ?></h5>
                    <p class="card-text"><?php echo $product->description; ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="price"><?php echo formatRupiah($product->price); ?></span>
                        <div>
                            <a href="product_detail.php?id=<?php echo $product->_id; ?>" 
                               class="btn btn-outline-primary me-2">
                                <i class="fas fa-eye"></i> Lihat Detail
                            </a>
                            <button onclick="addToCart('<?php echo $product->_id; ?>')" 
                                    class="btn btn-primary">
                                <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 