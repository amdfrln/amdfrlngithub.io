<?php
require_once 'config/init.php';
include 'includes/header.php';

$product_id = $_GET['id'] ?? null;

if (!$product_id || !isValidObjectId($product_id)) {
    header('Location: products.php');
    exit;
}

// Gunakan aggregation pipeline dengan $lookup
$pipeline = [
    [
        '$match' => [
            '_id' => new MongoDB\BSON\ObjectId($product_id)
        ]
    ],
    [
        '$lookup' => [
            'from' => 'categories',
            'localField' => 'category',
            'foreignField' => 'name',
            'as' => 'category_info'
        ]
    ],
    [
        '$lookup' => [
            'from' => 'specifications',
            'localField' => 'category',
            'foreignField' => 'category',
            'as' => 'specs_template'
        ]
    ],
    [
        '$unwind' => [
            'path' => '$category_info',
            'preserveNullAndEmptyArrays' => true
        ]
    ],
    [
        '$unwind' => [
            'path' => '$specs_template',
            'preserveNullAndEmptyArrays' => true
        ]
    ]
];

$product = $db->products->aggregate($pipeline)->toArray()[0] ?? null;

if (!$product) {
    header('Location: products.php');
    exit;
}

// Ambil produk terkait dengan lookup kategori
$related_pipeline = [
    [
        '$match' => [
            'category' => $product->category,
            '_id' => ['$ne' => new MongoDB\BSON\ObjectId($product_id)]
        ]
    ],
    [
        '$lookup' => [
            'from' => 'categories',
            'localField' => 'category',
            'foreignField' => 'name',
            'as' => 'category_info'
        ]
    ],
    [
        '$limit' => 3
    ]
];

$related_products = $db->products->aggregate($related_pipeline);
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
            <li class="breadcrumb-item"><a href="products.php">Produk</a></li>
            <li class="breadcrumb-item">
                <a href="products.php?category=<?php echo $product->category; ?>">
                    <?php echo $product->category_info->display_name ?? ucfirst($product->category); ?>
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $product->name; ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Gambar Produk -->
        <div class="col-md-6">
            <div class="product-gallery">
                <div class="main-image mb-3">
                    <img src="<?php echo getImagePath($product->images[0]); ?>" 
                         class="img-fluid rounded" 
                         alt="<?php echo $product->name; ?>"
                         id="main-product-image">
                </div>
                <div class="thumbnail-images">
                    <?php foreach ($product->images as $index => $image): ?>
                    <img src="<?php echo getImagePath($image); ?>" 
                         class="img-thumbnail me-2 <?php echo $index === 0 ? 'active' : ''; ?>"
                         onclick="changeMainImage(this.src)"
                         style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Detail Produk -->
        <div class="col-md-6">
            <div class="category-badge mb-2">
                <i class="<?php echo $product->category_info->icon ?? 'fas fa-tag'; ?>"></i>
                <?php echo $product->category_info->display_name ?? ucfirst($product->category); ?>
            </div>
            <h1 class="mb-3"><?php echo $product->name; ?></h1>
            <div class="price mb-3">
                <h2><?php echo formatRupiah($product->price); ?></h2>
            </div>
            <div class="description mb-4">
                <h5>Deskripsi</h5>
                <p><?php echo $product->description; ?></p>
            </div>
            <div class="specifications mb-4">
                <h5>Spesifikasi</h5>
                <ul class="list-unstyled">
                    <li><strong>Stok:</strong> <?php echo $product->stock; ?> unit</li>
                    <?php if (isset($product->specs_template->attributes)): ?>
                        <?php foreach ($product->specs_template->attributes as $attr): ?>
                            <?php if (isset($product->specifications->{$attr->name})): ?>
                            <li>
                                <strong><?php echo $attr->display_name; ?>:</strong>
                                <?php 
                                $value = $product->specifications->{$attr->name};
                                echo is_array($value) ? implode(', ', $value) : $value;
                                ?>
                            </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="actions">
                <button onclick="addToCart('<?php echo $product->_id; ?>')" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                </button>
            </div>
        </div>
    </div>

    <!-- Produk Terkait -->
    <div class="related-products mt-5">
        <h3 class="mb-4">Produk Terkait dalam <?php echo $product->category_info->display_name ?? ucfirst($product->category); ?></h3>
        <div class="row">
            <?php foreach ($related_products as $related): ?>
            <div class="col-md-4">
                <div class="product-card">
                    <div class="product-image-wrapper">
                        <img src="<?php echo getImagePath($related->images[0]); ?>" 
                             class="card-img-top" 
                             alt="<?php echo $related->name; ?>">
                    </div>
                    <div class="card-body">
                        <div class="category-badge mb-2">
                            <i class="<?php echo $related->category_info[0]->icon ?? 'fas fa-tag'; ?>"></i>
                            <?php echo $related->category_info[0]->display_name ?? ucfirst($related->category); ?>
                        </div>
                        <h5 class="card-title"><?php echo $related->name; ?></h5>
                        <p class="price"><?php echo formatRupiah($related->price); ?></p>
                        <a href="product_detail.php?id=<?php echo $related->_id; ?>" 
                           class="btn btn-outline-primary">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function changeMainImage(src) {
    document.getElementById('main-product-image').src = src;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail-images img').forEach(img => {
        img.classList.remove('active');
        if (img.src === src) {
            img.classList.add('active');
        }
    });
}
</script>

<style>
.product-gallery .main-image {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 10px;
}

.product-gallery .main-image img {
    width: 100%;
    height: auto;
    object-fit: cover;
}

.thumbnail-images img.active {
    border: 2px solid #2c3e50;
}

.price h2 {
    color: #2c3e50;
    font-weight: bold;
}

.specifications ul li {
    margin-bottom: 0.5rem;
}

.btn-outline-primary {
    color: #2c3e50;
    border-color: #2c3e50;
}

.btn-outline-primary:hover {
    background-color: #2c3e50;
    color: white;
}

.category-badge {
    display: inline-block;
    padding: 5px 10px;
    background-color: #f8f9fa;
    border-radius: 15px;
    font-size: 0.9rem;
    color: #2c3e50;
}

.category-badge i {
    margin-right: 5px;
}
</style>

<?php include 'includes/footer.php'; ?> 