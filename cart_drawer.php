<!-- Cart Drawer Markup -->
<div class="cart-drawer-overlay" id="cart-overlay" onclick="closeCart()"></div>
<div class="cart-drawer" id="cart-drawer">
    <div class="cart-drawer-header">
        <h3>ตะกร้าสินค้า 🛒</h3>
        <button class="cart-close-btn" onclick="closeCart()">&times;</button>
    </div>
    
    <div class="cart-drawer-body" id="cart-items-container">
        <!-- Rendered dynamically by JS -->
    </div>
    
    <div class="cart-drawer-footer">
        <div class="cart-total-row">
            <span>ราคารวมทั้งหมด:</span>
            <span class="cart-total-price" id="cart-total-price">฿0.00</span>
        </div>
        <a href="checkout.php" class="btn btn-block" style="margin-top: 1.5rem; text-decoration: none;">ดำเนินการชำระเงิน</a>
    </div>
</div>

<style>
/* Cart Drawer Styles */
.cart-drawer-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.4);
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
}
.cart-drawer-overlay.open {
    opacity: 1;
    visibility: visible;
}

.cart-drawer {
    position: fixed;
    top: 0;
    right: -400px;
    width: 400px;
    max-width: 100%;
    height: 100vh;
    background-color: #fff;
    z-index: 1000;
    box-shadow: -5px 0 25px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    transition: var(--transition);
}
.cart-drawer.open {
    right: 0;
}

.cart-drawer-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.cart-drawer-header h3 {
    font-weight: 600;
    font-size: 1.25rem;
}
.cart-close-btn {
    background: transparent;
    border: none;
    font-size: 1.8rem;
    cursor: pointer;
    color: var(--text-muted);
}

.cart-drawer-body {
    flex-grow: 1;
    overflow-y: auto;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.cart-item {
    display: flex;
    gap: 1rem;
    align-items: center;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 1.5rem;
}
.cart-item-img {
    width: 70px;
    height: 70px;
    object-fit: cover;
    border-radius: var(--border-radius-sm);
    border: 1px solid var(--border-color);
}
.cart-item-info {
    flex-grow: 1;
}
.cart-item-name {
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.2rem;
}
.cart-item-price {
    font-family: var(--font-serif);
    font-weight: 700;
    color: var(--text-muted);
    font-size: 0.9rem;
}
.cart-item-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 0.5rem;
}
.cart-item-qty {
    display: inline-flex;
    align-items: center;
    border: 1px solid var(--border-color);
    border-radius: 50px;
    padding: 0.1rem;
    background-color: var(--secondary-color);
}
.cart-item-qty-btn {
    border: none;
    background: transparent;
    width: 25px;
    height: 25px;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
.cart-item-qty-val {
    width: 25px;
    text-align: center;
    font-size: 0.85rem;
    font-weight: 600;
}
.cart-item-remove {
    color: #dc3545;
    font-size: 0.8rem;
    cursor: pointer;
    background: transparent;
    border: none;
}

.cart-drawer-footer {
    padding: 1.5rem;
    border-top: 1px solid var(--border-color);
    background-color: #fafafa;
}
.cart-total-row {
    display: flex;
    justify-content: space-between;
    font-weight: 600;
    font-size: 1.1rem;;
}
.cart-total-price {
    font-family: var(--font-serif);
    font-size: 1.3rem;
    color: var(--primary-color);
}
.cart-empty-text {
    text-align: center;
    color: var(--text-light);
    margin-top: 3rem;
    font-size: 0.95rem;
}
</style>
