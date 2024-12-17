<?php
require_once '../config/init.php';
include '../includes/admin_header.php';

// Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit;
}

// Gunakan aggregation pipeline dengan $lookup
$pipeline = [
    [
        '$lookup' => [
            'from' => 'categories',
            'localField' => 'category',
            'foreignField' => 'name',
            'as' => 'category_info'
        ],
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
    ],
    [
        '$addFields' => [
            'stock' => ['$ifNull' => ['$stock', 0]]
        ]
    ],
    [
        '$sort' => ['created_at' => -1]
    ]
];

$products = $db->products->aggregate($pipeline);

// Ambil semua kategori untuk filter
$categories = $db->categories->find([], ['sort' => ['display_name' => 1]]);
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1>Kelola Produk</h1>
                <div>
                    <a href="add_category.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-folder-plus"></i> Tambah Kategori
                    </a>
                    <a href="add_product.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </a>
                </div>
            </div>

            <!-- Filter Kategori -->
            <div class="mb-4">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active" onclick="filterProducts('all')">
                        Semua
                    </button>
                    <?php foreach ($categories as $category): ?>
                    <button type="button" class="btn btn-outline-primary" 
                            onclick="filterProducts('<?php echo htmlspecialchars($category->name); ?>')">
                            <i class="<?php echo isset($category->icon) ? htmlspecialchars($category->icon) : 'fas fa-folder'; ?>"></i>
                            <?php echo htmlspecialchars($category->display_name); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Spesifikasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <?php
                            // Pastikan stock selalu numerik
                            $stock = isset($product->stock) && is_numeric($product->stock) ? (int)$product->stock : 0;
                        ?>
                        <tr data-category="<?php echo htmlspecialchars($product->category); ?>">
                            <td>
                                <img src="../<?php echo htmlspecialchars(getImagePath($product->images[0] ?? 'default.png')); ?>" 
                                     alt="<?php echo htmlspecialchars($product->name); ?>" 
                                     style="width: 50px; height: 50px; object-fit: cover;"
                                     class="rounded">
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($product->name); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars(substr($product->description ?? '', 0, 50)); ?>...</small>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <i class="<?php echo htmlspecialchars($product->category_info->icon ?? 'fas fa-tag'); ?>"></i>
                                    <?php echo htmlspecialchars($product->category_info->display_name ?? ucfirst($product->category)); ?>
                                </span>
                            </td>
                            <td><?php echo formatRupiah($product->price ?? 0); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $stock > 0 ? 'success' : 'danger'; ?>">
                                    <?php echo $stock; ?> unit
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#specModal<?php echo $product->_id; ?>">
                                    <i class="fas fa-list"></i> Detail
                                </button>
                            </td>
                            <td>
                                <?php if (!empty($product->featured)): ?>
                                <span class="badge bg-warning">
                                    <i class="fas fa-star"></i> Unggulan
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="edit_product.php?id=<?php echo htmlspecialchars($product->_id); ?>" 
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="deleteProduct('<?php echo htmlspecialchars($product->_id); ?>')" 
                                            class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Spesifikasi -->
                        <div class="modal fade" id="specModal<?php echo htmlspecialchars($product->_id); ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Spesifikasi <?php echo htmlspecialchars($product->name); ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <ul class="list-group">
                                            <?php if (!empty($product->specs_template->attributes)): ?>
                                                <?php foreach ($product->specs_template->attributes as $attr): ?>
                                                    <?php if (!empty($product->specifications->{$attr->name})): ?>
                                                    <li class="list-group-item">
                                                        <strong><?php echo htmlspecialchars($attr->display_name); ?>:</strong>
                                                        <?php 
                                                        $value = $product->specifications->{$attr->name};
                                                        echo is_array($value) ? htmlspecialchars(implode(', ', $value)) : htmlspecialchars($value);
                                                        ?>
                                                    </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <li class="list-group-item">Tidak ada spesifikasi</li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<script>
function filterProducts(category) {
    const rows = document.querySelectorAll('tbody tr');
    const buttons = document.querySelectorAll('.btn-group .btn');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    rows.forEach(row => {
        if (category === 'all' || row.dataset.category === category) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function deleteProduct(productId) {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        fetch('delete_product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Produk berhasil dihapus');
                location.reload();
            } else {
                alert('Gagal menghapus produk: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus produk');
        });
    }
}
</script>

<?php include '../includes/admin_footer.php'; ?>
