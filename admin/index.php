<?php
require_once '../config/init.php';
include '../includes/admin_header.php';

// Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Ambil statistik dasar
$total_products = $db->products->countDocuments();
$total_categories = $db->categories->countDocuments();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1>Dashboard Admin</h1>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Produk</h5>
                            <p class="card-text display-6"><?php echo $total_products; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Kategori</h5>
                            <p class="card-text display-6"><?php echo $total_categories; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
