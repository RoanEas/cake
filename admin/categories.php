<?php
include_once 'header.php';

// Handle Add Category Form Submission
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    
    // Create slug automatically if empty
    if (empty($slug)) {
        // Simple slugify for English or basic text
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
    }

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        if ($stmt->execute([$name, $slug])) {
            $message = "เพิ่มหมวดหมู่สำเร็จ!";
        } else {
            $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
        }
    } else {
        $error = "กรุณากรอกชื่อหมวดหมู่";
    }
}

// Fetch all categories
$stmt_categories = $pdo->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.id ASC");
$categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if($message): ?>
    <div class="admin-badge admin-badge-success" style="display: block; padding: 1rem; margin-bottom: 1.5rem; border-radius: var(--admin-radius);"><?= $message ?></div>
<?php endif; ?>
<?php if($error): ?>
    <div class="admin-badge admin-badge-danger" style="display: block; padding: 1rem; margin-bottom: 1.5rem; border-radius: var(--admin-radius);"><?= $error ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 2rem; align-items: start;">
    <!-- List Categories -->
    <div class="admin-card">
        <div class="admin-card-title">รายการหมวดหมู่ทั้งหมด</div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อหมวดหมู่</th>
                        <th>Slug</th>
                        <th>จำนวนสินค้า</th>
                        <th style="text-align: right;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($categories) > 0): ?>
                        <?php foreach($categories as $category): ?>
                        <tr>
                            <td>#<?= $category['id'] ?></td>
                            <td><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                            <td><code><?= htmlspecialchars($category['slug']) ?></code></td>
                            <td><span class="admin-badge" style="background-color: #f1f5f9; color: #475569; font-weight: 500;"><?= $category['product_count'] ?> รายการ</span></td>
                            <td style="text-align: right;">
                                <a href="delete_category.php?id=<?= $category['id'] ?>" class="admin-btn admin-btn-danger admin-btn-sm" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบหมวดหมู่นี้? (สินค้าในหมวดหมู่นี้จะยังอยู่แต่จะไม่ถูกผูกกับหมวดหมู่ใดๆ)')">ลบ</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; color: var(--admin-text-muted);">ไม่มีข้อมูลหมวดหมู่ในระบบ</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Category Form -->
    <div class="admin-card">
        <div class="admin-card-title">เพิ่มหมวดหมู่ใหม่</div>
        <form method="POST">
            <input type="hidden" name="add_category" value="1">
            <div class="admin-form-group">
                <label>ชื่อหมวดหมู่</label>
                <input type="text" name="name" class="admin-form-control" placeholder="เช่น เค้กช็อกโกแลตฟัดจ์" required>
            </div>
            <div class="admin-form-group">
                <label>Slug (ภาษาอังกฤษสำหรับ URL เช่น chocolate-fudge)</label>
                <input type="text" name="slug" class="admin-form-control" placeholder="เว้นว่างไว้เพื่อสร้างอัตโนมัติ">
            </div>
            <button type="submit" class="admin-btn admin-btn-primary btn-block" style="justify-content: center; width: 100%;">บันทึกหมวดหมู่</button>
        </form>
    </div>
</div>

<?php
include_once 'footer.php';
?>
