<?php
include_once 'header.php';

// Handle Add or Edit Category Form Submission
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_category'])) {
    $id = trim($_POST['category_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    
    // Create slug automatically if empty
    if (empty($slug)) {
        // Simple slugify for English or basic text
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
    }

    if (!empty($name)) {
        if (!empty($id)) {
            // Edit Category
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
            if ($stmt->execute([$name, $slug, $id])) {
                $message = "แก้ไขหมวดหมู่สำเร็จ!";
            } else {
                $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
            }
        } else {
            // Add Category
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
            if ($stmt->execute([$name, $slug])) {
                $message = "เพิ่มหมวดหมู่สำเร็จ!";
            } else {
                $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
            }
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

<div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 2rem; align-items: start;" class="admin-grid-layout">
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
                            <td data-label="ID">#<?= $category['id'] ?></td>
                            <td data-label="ชื่อหมวดหมู่"><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                            <td data-label="Slug"><code><?= htmlspecialchars($category['slug']) ?></code></td>
                            <td data-label="จำนวนสินค้า"><span class="admin-badge" style="background-color: #f1f5f9; color: #475569; font-weight: 500;"><?= $category['product_count'] ?> รายการ</span></td>
                            <td style="text-align: right;" data-label="จัดการ">
                                <div style="display: inline-flex; gap: 0.35rem; justify-content: flex-end;">
                                    <button class="admin-btn admin-btn-secondary admin-btn-sm" onclick="editCategory(<?= $category['id'] ?>, <?= htmlspecialchars(json_encode($category['name'])) ?>, <?= htmlspecialchars(json_encode($category['slug'])) ?>)">แก้ไข</button>
                                    <a href="delete_category.php?id=<?= $category['id'] ?>" class="admin-btn admin-btn-danger admin-btn-sm" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบหมวดหมู่นี้? (สินค้าในหมวดหมู่นี้จะยังอยู่แต่จะไม่ถูกผูกกับหมวดหมู่ใดๆ)')">ลบ</a>
                                </div>
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

    <!-- Add/Edit Category Form -->
    <div class="admin-card">
        <div class="admin-card-title" id="form-title">เพิ่มหมวดหมู่ใหม่</div>
        <form method="POST" id="category-form">
            <input type="hidden" name="save_category" value="1">
            <input type="hidden" name="category_id" id="category_id" value="">
            
            <div class="admin-form-group">
                <label>ชื่อหมวดหมู่</label>
                <input type="text" name="name" id="category_name" class="admin-form-control" placeholder="เช่น เค้กช็อกโกแลตฟัดจ์" required>
            </div>
            <div class="admin-form-group">
                <label>Slug (ภาษาอังกฤษสำหรับ URL เช่น chocolate-fudge)</label>
                <input type="text" name="slug" id="category_slug" class="admin-form-control" placeholder="เว้นว่างไว้เพื่อสร้างอัตโนมัติ">
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" id="submit-btn" class="admin-btn admin-btn-primary" style="justify-content: center; flex: 1;">บันทึกหมวดหมู่</button>
                <button type="button" id="cancel-btn" class="admin-btn admin-btn-secondary" style="display: none; justify-content: center;" onclick="resetForm()">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCategory(id, name, slug) {
    document.getElementById('category_id').value = id;
    document.getElementById('category_name').value = name;
    document.getElementById('category_slug').value = slug;
    
    document.getElementById('form-title').innerText = 'แก้ไขหมวดหมู่ #' + id;
    document.getElementById('submit-btn').innerText = 'บันทึกการแก้ไข';
    document.getElementById('cancel-btn').style.display = 'inline-flex';
    
    // Scroll form into view on mobile devices
    document.getElementById('category-form').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('category_id').value = '';
    document.getElementById('category_name').value = '';
    document.getElementById('category_slug').value = '';
    
    document.getElementById('form-title').innerText = 'เพิ่มหมวดหมู่ใหม่';
    document.getElementById('submit-btn').innerText = 'บันทึกหมวดหมู่';
    document.getElementById('cancel-btn').style.display = 'none';
}
</script>

<?php
include_once 'footer.php';
?>
