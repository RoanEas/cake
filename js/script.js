document.addEventListener("DOMContentLoaded", () => {
    // Clear cart if checkout was successful
    if (window.location.search.includes('order=success')) {
        localStorage.removeItem('cake_cart');
    }

    // Auto-hide alert messages after 3 seconds
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 3000);
    }

    // Dynamic Category Filtering & Pagination Setup
    const tabs = document.querySelectorAll('.category-tab');
    const cards = Array.from(document.querySelectorAll('.product-card'));
    const paginationContainer = document.getElementById('product-pagination-container');
    const PRODUCTS_PER_PAGE = 9;
    let currentPage = 1;
    let currentFilter = 'all';

    function updateProductsDisplay() {
        if (cards.length === 0) return;

        // 1. Filter cards by category
        const filteredCards = cards.filter(card => {
            const cardCategory = card.getAttribute('data-category');
            return currentFilter === 'all' || cardCategory === currentFilter;
        });

        // 2. Hide all cards first
        cards.forEach(card => card.style.display = 'none');

        // 3. Compute pagination limits
        const totalPages = Math.ceil(filteredCards.length / PRODUCTS_PER_PAGE) || 1;
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIdx = (currentPage - 1) * PRODUCTS_PER_PAGE;
        const endIdx = startIdx + PRODUCTS_PER_PAGE;

        // 4. Show current page cards
        const pageCards = filteredCards.slice(startIdx, endIdx);
        pageCards.forEach(card => {
            card.style.display = 'flex';
            card.style.animation = 'none';
            card.offsetHeight; // trigger reflow
            card.style.animation = 'fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards';
        });

        // 5. Render Pagination Controls
        renderPaginationControls(filteredCards.length, totalPages);
    }

    function renderPaginationControls(totalItems, totalPages) {
        if (!paginationContainer) return;

        if (totalPages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = '';
        
        // Prev button
        html += `<button class="pagination-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">ก่อนหน้า</button>`;

        // Page buttons
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="pagination-btn ${currentPage === i ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
        }

        // Next button
        html += `<button class="pagination-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">ถัดไป</button>`;

        paginationContainer.innerHTML = html;
    }

    window.changePage = function(page) {
        currentPage = page;
        updateProductsDisplay();
        
        // Smooth scroll to products section
        const section = document.getElementById('products');
        if (section) {
            section.scrollIntoView({ behavior: 'smooth' });
        }
    };

    if (tabs.length > 0 && cards.length > 0) {
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                currentFilter = tab.getAttribute('data-filter');
                currentPage = 1; // Reset to page 1 on filter switch
                updateProductsDisplay();
            });
        });

        // Initial render
        updateProductsDisplay();
    }

    // Cart Drawer Navigation Toggle
    const cartToggle = document.getElementById('cart-toggle-nav');
    if (cartToggle) {
        cartToggle.addEventListener('click', (e) => {
            e.preventDefault();
            openCart();
        });
    }

    // --- Secret Admin Access: กดค้างโลโก้ 10 วินาทีเพื่อเข้าหน้าแอดมิน ---
    const secretLogo = document.getElementById('logo-nightcake');
    if (secretLogo) {
        let holdTimer = null;

        const startHold = (e) => {
            e.preventDefault();
            holdTimer = setTimeout(() => {
                window.location.href = 'admin/index.php';
            }, 10000); // 10 วินาที
        };

        const cancelHold = () => {
            if (holdTimer) {
                clearTimeout(holdTimer);
                holdTimer = null;
            }
        };

        // Desktop (mouse)
        secretLogo.addEventListener('mousedown', startHold);
        secretLogo.addEventListener('mouseup', cancelHold);
        secretLogo.addEventListener('mouseleave', cancelHold);

        // Mobile / Tablet (touch)
        secretLogo.addEventListener('touchstart', startHold, { passive: false });
        secretLogo.addEventListener('touchend', cancelHold);
        secretLogo.addEventListener('touchcancel', cancelHold);
    }

    // Initialize Cart count on load
    updateCartNavCount();
});

// Cart Helper Functions on Window
window.openCart = function() {
    const drawer = document.getElementById('cart-drawer');
    const overlay = document.getElementById('cart-overlay');
    if (drawer && overlay) {
        drawer.classList.add('open');
        overlay.classList.add('open');
        renderCart();
    }
};

window.closeCart = function() {
    const drawer = document.getElementById('cart-drawer');
    const overlay = document.getElementById('cart-overlay');
    if (drawer && overlay) {
        drawer.classList.remove('open');
        overlay.classList.remove('open');
    }
};

window.renderCart = function() {
    const container = document.getElementById('cart-items-container');
    const totalElem = document.getElementById('cart-total-price');
    if (!container || !totalElem) return;

    let cart = JSON.parse(localStorage.getItem('cake_cart')) || [];
    
    if (cart.length === 0) {
        container.innerHTML = '<p class="cart-empty-text">ไม่มีสินค้าในตะกร้าของคุณ 🍰</p>';
        totalElem.innerText = '฿0.00';
        updateCartNavCount();
        return;
    }

    let html = '';
    let grandTotal = 0;

    cart.forEach(item => {
        const itemTotal = item.price * item.qty;
        grandTotal += itemTotal;

        html += `
            <div class="cart-item">
                <img src="${item.img}" class="cart-item-img" alt="${item.name}">
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">฿${parseFloat(item.price).toLocaleString('th-TH', {minimumFractionDigits: 2})}</div>
                    <div class="cart-item-actions">
                        <div class="cart-item-qty">
                            <button class="cart-item-qty-btn" onclick="updateCartQty(${item.id}, -1)">-</button>
                            <span class="cart-item-qty-val">${item.qty}</span>
                            <button class="cart-item-qty-btn" onclick="updateCartQty(${item.id}, 1)">+</button>
                        </div>
                        <button class="cart-item-remove" onclick="removeCartItem(${item.id})">ลบ</button>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
    totalElem.innerText = '฿' + grandTotal.toLocaleString('th-TH', {minimumFractionDigits: 2});
    updateCartNavCount();
};

window.updateCartQty = function(id, change) {
    let cart = JSON.parse(localStorage.getItem('cake_cart')) || [];
    const index = cart.findIndex(item => item.id === id);
    
    if (index > -1) {
        cart[index].qty += change;
        if (cart[index].qty <= 0) {
            cart.splice(index, 1);
        }
        localStorage.setItem('cake_cart', JSON.stringify(cart));
        renderCart();
    }
};

window.removeCartItem = function(id) {
    let cart = JSON.parse(localStorage.getItem('cake_cart')) || [];
    cart = cart.filter(item => item.id !== id);
    localStorage.setItem('cake_cart', JSON.stringify(cart));
    renderCart();
};

function updateCartNavCount() {
    const navCount = document.getElementById('cart-count-nav');
    if (navCount) {
        let cart = JSON.parse(localStorage.getItem('cake_cart')) || [];
        let totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
        navCount.innerText = totalQty;
    }
}
// --- เพิ่มระบบกดค้างโลโก้ที่ Footer 10 วินาทีเพื่อเข้าหน้าแอดมิน ---
let footerPressTimer;
const footerLogo = document.getElementById('admin-secret-logo-footer');

if (footerLogo) {
    const startFooterPress = (e) => {
        if(e.cancelable) e.preventDefault(); 
        
        footerPressTimer = window.setTimeout(() => {
            window.location.href = 'admin/index.php';
        }, 1000); // 10 วินาที
    };

    const cancelFooterPress = () => {
        clearTimeout(footerPressTimer);
    };

    // รองรับคอมพิวเตอร์
    footerLogo.addEventListener('mousedown', startFooterPress);
    footerLogo.addEventListener('mouseup', cancelFooterPress);
    footerLogo.addEventListener('mouseleave', cancelFooterPress);

    // รองรับมือถือ (กันเมนูระบบเด้งแทรก)
    footerLogo.addEventListener('touchstart', startFooterPress, { passive: false });
    footerLogo.addEventListener('touchend', cancelFooterPress);
    footerLogo.addEventListener('touchcancel', cancelFooterPress);
    
    footerLogo.addEventListener('contextmenu', (e) => {
        e.preventDefault();
    });
}