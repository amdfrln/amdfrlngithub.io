<?php
require_once 'config/init.php';
include 'includes/header.php';

// Inisialisasi array untuk menyimpan detail produk di keranjang
$cart_items = [];

// Jika ada item di keranjang, ambil detailnya dari database
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = $db->products->findOne(['_id' => new MongoDB\BSON\ObjectId($product_id)]);
        if ($product) {
            $cart_items[] = [
                'id' => $product_id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'image' => $product->images[0] ?? '',
                'subtotal' => $product->price * $quantity
            ];
        }
    }
}

$total = array_sum(array_column($cart_items, 'subtotal'));
?>

<div class="container my-5">
    <h1 class="text-center mb-5 animate__animated animate__fadeIn">Keranjang Belanja</h1>

    <?php if (empty($cart_items)): ?>
    <div class="text-center">
        <i class="fas fa-shopping-cart fa-4x mb-3 text-muted"></i>
        <h3>Keranjang Belanja Kosong</h3>
        <p>Anda belum menambahkan produk ke keranjang.</p>
        <a href="products.php" class="btn btn-primary">Lihat Produk</a>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-md-8">
            <?php foreach ($cart_items as $item): ?>
            <div class="card mb-3 animate-on-scroll">
                <div class="row g-0">
                    <div class="col-md-4">
                        <img src="<?php echo $item['image']; ?>" class="img-fluid rounded-start" alt="<?php echo $item['name']; ?>">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $item['name']; ?></h5>
                            <p class="card-text">
                                Harga: Rp <?php echo number_format($item['price'], 0, ',', '.'); ?><br>
                                Jumlah: <?php echo $item['quantity']; ?><br>
                                Subtotal: Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                            </p>
                            <button onclick="updateCart('<?php echo $item['id']; ?>', 'remove')" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Ringkasan Belanja</h5>
                    <p class="card-text">
                        Total: <strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong>
                    </p>
                    <button onclick="checkout()" class="btn btn-primary w-100">
                        <i class="fas fa-shopping-bag"></i> Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 