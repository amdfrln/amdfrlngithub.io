// Animasi scroll
document.addEventListener("DOMContentLoaded", function () {
  const animateElements = document.querySelectorAll(".animate-on-scroll");

  function checkScroll() {
    animateElements.forEach((element) => {
      const elementTop = element.getBoundingClientRect().top;
      const windowHeight = window.innerHeight;

      if (elementTop < windowHeight - 50) {
        element.classList.add("visible");
      }
    });
  }

  window.addEventListener("scroll", checkScroll);
  checkScroll();
});

// Animasi keranjang
function addToCart(productId) {
  const cart = document.querySelector(".cart-icon");
  cart.classList.add("animate__animated", "animate__rubberBand");

  setTimeout(() => {
    cart.classList.remove("animate__animated", "animate__rubberBand");
  }, 1000);

  // Ajax request untuk menambahkan ke keranjang
  fetch("add-to-cart.php", {
    method: "POST",
    body: JSON.stringify({ productId: productId }),
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Produk berhasil ditambahkan ke keranjang!");
        updateCartCount(data.cartCount);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Terjadi kesalahan saat menambahkan produk");
    });
}

// Notifikasi
function showNotification(message) {
  const notification = document.createElement("div");
  notification.className =
    "notification animate__animated animate__fadeInRight";
  notification.innerHTML = message;
  document.body.appendChild(notification);

  setTimeout(() => {
    notification.classList.remove("animate__fadeInRight");
    notification.classList.add("animate__fadeOutRight");
    setTimeout(() => {
      notification.remove();
    }, 1000);
  }, 3000);
}

// Update jumlah item di keranjang
function updateCartCount(count) {
  const cartCount = document.querySelector(".cart-count");
  if (cartCount) {
    cartCount.textContent = count;
    cartCount.classList.add("animate__animated", "animate__bounce");
    setTimeout(() => {
      cartCount.classList.remove("animate__animated", "animate__bounce");
    }, 1000);
  }
}

// Image slider untuk produk
class ProductSlider {
  constructor(element) {
    this.slider = element;
    this.slides = element.querySelectorAll(".slide");
    this.currentSlide = 0;
    this.init();
  }

  init() {
    this.createNavigation();
    this.showSlide(0);
    this.startAutoSlide();
  }

  createNavigation() {
    const nav = document.createElement("div");
    nav.className = "slider-nav";

    this.slides.forEach((_, index) => {
      const dot = document.createElement("button");
      dot.addEventListener("click", () => this.showSlide(index));
      nav.appendChild(dot);
    });

    this.slider.appendChild(nav);
  }

  showSlide(index) {
    this.slides[this.currentSlide].classList.remove("active");
    this.slides[index].classList.add("active");
    this.currentSlide = index;
  }

  startAutoSlide() {
    setInterval(() => {
      const next = (this.currentSlide + 1) % this.slides.length;
      this.showSlide(next);
    }, 5000);
  }
}

// Inisialisasi slider
document.addEventListener("DOMContentLoaded", function () {
  const sliders = document.querySelectorAll(".product-slider");
  sliders.forEach((slider) => new ProductSlider(slider));
});

// Filter produk
function filterProducts(category) {
  const products = document.querySelectorAll(".product-card");

  products.forEach((product) => {
    const productCategory = product.dataset.category;
    if (category === "all" || productCategory === category) {
      product.style.display = "block";
      setTimeout(() => {
        product.classList.add("visible");
      }, 100);
    } else {
      product.classList.remove("visible");
      setTimeout(() => {
        product.style.display = "none";
      }, 500);
    }
  });
}

// Search produk
const searchInput = document.querySelector(".search-input");
if (searchInput) {
  searchInput.addEventListener("input", function (e) {
    const searchTerm = e.target.value.toLowerCase();
    const products = document.querySelectorAll(".product-card");

    products.forEach((product) => {
      const title = product
        .querySelector(".card-title")
        .textContent.toLowerCase();
      const description = product
        .querySelector(".card-text")
        .textContent.toLowerCase();

      if (title.includes(searchTerm) || description.includes(searchTerm)) {
        product.style.display = "block";
      } else {
        product.style.display = "none";
      }
    });
  });
}

// Tambahkan fungsi updateCart
function updateCart(productId, action, quantity = 1) {
  fetch("update-cart.php", {
    method: "POST",
    body: JSON.stringify({
      productId: productId,
      action: action,
      quantity: quantity,
    }),
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(data.message);
        updateCartCount(data.cartCount);
        if (action === "remove") {
          location.reload(); // Reload halaman setelah menghapus item
        }
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Terjadi kesalahan saat memperbarui keranjang");
    });
}

// Tambahkan fungsi checkout
function checkout() {
  // Untuk sementara hanya menampilkan alert
  alert("Fitur checkout akan segera tersedia!");
  // Nanti bisa diarahkan ke halaman checkout
  // window.location.href = 'checkout.php';
}

// Fungsi untuk menghapus produk
function deleteProduct(productId) {
  if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
    const currentPath = window.location.pathname;
    const isAdmin = currentPath.includes('/admin/');
    const deleteUrl = isAdmin ? 'delete_product.php' : '../admin/delete_product.php';

    fetch(deleteUrl, {
      method: 'POST',
      body: JSON.stringify({ id: productId }),
      headers: {
        'Content-Type': 'application/json'
      }
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
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
