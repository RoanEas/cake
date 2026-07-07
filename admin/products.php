<?php
include_once 'header.php';

// Fetch Products and categories mapping
$stmt_products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories for filtering
$stmt_cats = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>จัดการสินค้าทั้งหมด</h2>
    <a href="add_product.php" class="admin-btn admin-btn-primary">+ เพิ่มเค้กใหม่</a>
</div>

<div class="admin-category-filters" style="display: flex; gap: 0.6rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <button class="filter-tab active" data-category-id="all" style="padding: 0.5rem 1.2rem; border-radius: 30px; border: 1.5px solid var(--admin-border, #e2e8f0); background: white; cursor: pointer; font-size: 0.85rem; font-weight: 500; transition: all 0.3s ease; box-shadow: var(--admin-shadow-subtle);">🎂 ทั้งหมด</button>
    <?php foreach($categories as $cat): ?>
        <button class="filter-tab" data-category-id="<?= $cat['id'] ?>" style="padding: 0.5rem 1.2rem; border-radius: 30px; border: 1.5px solid var(--admin-border, #e2e8f0); background: white; cursor: pointer; font-size: 0.85rem; font-weight: 500; transition: all 0.3s ease; box-shadow: var(--admin-shadow-subtle);"><?= htmlspecialchars($cat['name']) ?></button>
    <?php endforeach; ?>
</div>

<div class="admin-card" style="max-width: 100%;">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>รูปภาพ</th>
                    <th>ชื่อเค้ก</th>
                    <th>หมวดหมู่</th>
                    <th>ราคา</th>
                    <th>รูปภาพเพิ่มเติม</th>
                    <th style="text-align: right;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($products) > 0): ?>
                    <?php foreach($products as $product): ?>
                    <tr data-category-id="<?= htmlspecialchars($product['category_id'] ?? '') ?>">
                        <td data-label="รูปภาพหลัก">
                            <img src="../<?= htmlspecialchars($product['image']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--admin-radius); border: 1px solid var(--admin-border);">
                        </td>
                        <td data-label="ชื่อเค้ก">
                            <strong><?= htmlspecialchars($product['name']) ?></strong>
                            <p style="font-size: 0.8rem; color: var(--admin-text-muted); max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($product['description']) ?></p>
                        </td>
                        <td data-label="หมวดหมู่">
                            <span class="admin-badge" style="background-color: #f1f5f9; color: #475569;">
                                <?= htmlspecialchars($product['category_name'] ?? 'ไม่มีหมวดหมู่') ?>
                            </span>
                        </td>
                        <td data-label="ราคา"><strong>฿<?= number_format($product['price'], 2) ?></strong></td>
                        <td data-label="รูปเพิ่มเติม">
                            <!-- Thumbnails of additional images -->
                            <div style="display: flex; gap: 0.3rem;">
                                <?php for ($i = 1; $i <= 3; $i++): ?>
                                    <?php if (!empty($product['image_' . $i])): ?>
                                        <img src="../<?= htmlspecialchars($product['image_' . $i]) ?>" style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px; border: 1px solid var(--admin-border);">
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </td>
                        <td style="text-align: right;" data-label="จัดการ">
                            <a href="edit_product.php?id=<?= $product['id'] ?>" class="admin-btn admin-btn-secondary admin-btn-sm">แก้ไข</a>
                            <a href="delete_product.php?id=<?= $product['id'] ?>" class="admin-btn admin-btn-danger admin-btn-sm" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบสินค้านี้?')">ลบ</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; color: var(--admin-text-muted); padding: 3rem 0;">ยังไม่มีสินค้าในระบบขณะนี้ คุณสามารถกดปุ่ม "เพิ่มเค้กใหม่" ด้านบนเพื่อเริ่มต้นได้ครับ</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const tabs = document.querySelectorAll('.filter-tab');
    const rows = document.querySelectorAll('.admin-table tbody tr');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => {
                t.classList.remove('active');
                t.style.backgroundColor = 'white';
                t.style.borderColor = 'var(--admin-border, #e2e8f0)';
                t.style.color = '#475569';
            });
            
            tab.classList.add('active');
            tab.style.backgroundColor = '#f7a8b8'; // pastel pink theme color
            tab.style.borderColor = '#f7a8b8';
            tab.style.color = 'white';

            const selectedCatId = tab.getAttribute('data-category-id');

            rows.forEach(row => {
                const rowCatId = row.getAttribute('data-category-id');
                if (!rowCatId) return;
                
                if (selectedCatId === 'all' || rowCatId === selectedCatId) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    // Style the initial active tab
    const activeTab = document.querySelector('.filter-tab.active');
    if (activeTab) {
        activeTab.style.backgroundColor = '#f7a8b8';
        activeTab.style.borderColor = '#f7a8b8';
        activeTab.style.color = 'white';
    }
});
</script>

<?php
include_once 'footer.php';
?>
