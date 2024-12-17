<?php
include 'includes/header.php';

// Ambil produk unggulan
$featured_products = $db->products->find(['featured' => true]);
?>

<div class="hero-section animate__animated animate__fadeIn">
    <div class="container text-center">
        <h1 class="display-4">Selamat Datang di FurnitureTREND</h1>
        <p class="lead">Temukan furniture berkualitas untuk rumah impian Anda</p>
        <a href="products.php" class="btn btn-primary btn-lg">Lihat Koleksi</a>
    </div>
</div>

<div class="container my-5">
    <h2 class="text-center mb-4 animate-on-scroll">Produk Unggulan</h2>
    
    <div class="row">
        <?php foreach ($featured_products as $product): ?>
        <div class="col-md-4 mb-4 animate-on-scroll">
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
                        <button onclick="addToCart('<?php echo $product->_id; ?>')" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 