<?php
include_once 'header.php';

// 1. ดึงข้อมูลหมวดหมู่ทั้งหมดจากฐานข้อมูลมาเตรียมไว้สำหรับ Dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $category_id = $_POST['category_id'] ?? null;
    
    // Fetch Category Slug for backwards compatibility in storefront
    $category_slug = 'minimalist';
    if ($category_id) {
        $stmt_cat = $pdo->prepare("SELECT slug FROM categories WHERE id = ?");
        $stmt_cat->execute([$category_id]);
        $category_slug = $stmt_cat->fetchColumn() ?: 'minimalist';
    }

    // Handle file upload (รูปภาพหลัก)
    $image_path = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/" . $new_filename;
        }
    }

    // Handle additional images (รูปภาพเพิ่มเติม 1-3)
    $image_paths = [null, null, null];
    for ($i = 1; $i <= 3; $i++) {
        $key = 'image_' . $i;
        if(isset($_FILES[$key]) && $_FILES[$key]['error'] == 0) {
            $target_dir = "../uploads/";
            if(!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_extension = pathinfo($_FILES[$key]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '_add_' . $i . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if(move_uploaded_file($_FILES[$key]["tmp_name"], $target_file)) {
                $image_paths[$i-1] = "uploads/" . $new_filename;
            }
        }
    }

    // บันทึกข้อมูลลงฐานข้อมูลตาราง products
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, image_1, image_2, image_3, category, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if($stmt->execute([$name, $description, $price, $image_path, $image_paths[0], $image_paths[1], $image_paths[2], $category_slug, $category_id])) {
        echo "<script>window.location.href='products.php';</script>";
        exit;
    }
}
?>

<div class="admin-card" style="max-width: 800px; margin: 0 auto;">
    <div class="admin-card-title">เพิ่มรายละเอียดเค้กชิ้นใหม่</div>
    <form method="POST" enctype="multipart/form-data">
        <div class="admin-form-group">
            <label>ชื่อเค้ก</label>
            <input type="text" name="name" class="admin-form-control" placeholder="เช่น เค้กส้มมินิมอลครีมสด" required>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="admin-form-group">
                <label>ราคา (บาท)</label>
                <input type="number" step="0.01" name="price" class="admin-form-control" placeholder="เช่น 250" required>
            </div>
            
            <div class="admin-form-group">
                <label>หมวดหมู่เค้ก</label>
                <select name="category_id" class="admin-form-control" required>
                    <option value="">-- เลือกหมวดหมู่ --</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="admin-form-group">
            <label>รายละเอียด / ส่วนผสม / ขนาดเค้ก</label>
            <textarea name="description" class="admin-form-control" rows="4" placeholder="ระบุรายละเอียด เช่น ขนาด 1 ปอนด์, ครีมส้มนุ่มนวลอมเปรี้ยวหวานลงตัว..." required></textarea>
        </div>

        <div class="admin-form-group">
            <label style="margin-bottom: 1rem;">อัพโหลดรูปภาพประกอบเค้ก (UI อัพโหลดแบบใหม่)</label>
            <div class="upload-grid">
                <div>
                    <span class="upload-label-badge">รูปภาพหลัก (จำเป็น)</span>
                    <div class="image-upload-card" id="card-main">
                        <input type="file" name="image" accept="image/*" onchange="previewImage(this, 'preview-main', 'card-main')" required>
                        <div class="upload-placeholder">
                            <span class="upload-icon">📸</span>
                            <span style="font-size: 0.8rem; font-weight: 500;">เลือกรูปภาพหลัก</span>
                        </div>
                        <div class="upload-preview" id="preview-wrap-main">
                            <img id="preview-main" src="" alt="Main Preview">
                        </div>
                    </div>
                </div>

                <div>
                    <span class="upload-label-badge">รูปเพิ่มเติม 1</span>
                    <div class="image-upload-card" id="card-1">
                        <input type="file" name="image_1" accept="image/*" onchange="previewImage(this, 'preview-1', 'card-1')">
                        <div class="upload-placeholder">
                            <span class="upload-icon">➕</span>
                            <span style="font-size: 0.8rem; font-weight: 500;">รูปประกอบ 1</span>
                        </div>
                        <div class="upload-preview" id="preview-wrap-1">
                            <img id="preview-1" src="" alt="Preview 1">
                        </div>
                    </div>
                </div>

                <div>
                    <span class="upload-label-badge">รูปเพิ่มเติม 2</span>
                    <div class="image-upload-card" id="card-2">
                        <input type="file" name="image_2" accept="image/*" onchange="previewImage(this, 'preview-2', 'card-2')">
                        <div class="upload-placeholder">
                            <span class="upload-icon">➕</span>
                            <span style="font-size: 0.8rem; font-weight: 500;">รูปประกอบ 2</span>
                        </div>
                        <div class="upload-preview" id="preview-wrap-2">
                            <img id="preview-2" src="" alt="Preview 2">
                        </div>
                    </div>
                </div>

                <div>
                    <span class="upload-label-badge">รูปเพิ่มเติม 3</span>
                    <div class="image-upload-card" id="card-3">
                        <input type="file" name="image_3" accept="image/*" onchange="previewImage(this, 'preview-3', 'card-3')">
                        <div class="upload-placeholder">
                            <span class="upload-icon">➕</span>
                            <span style="font-size: 0.8rem; font-weight: 500;">รูปประกอบ 3</span>
                        </div>
                        <div class="upload-preview" id="preview-wrap-3">
                            <img id="preview-3" src="" alt="Preview 3">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="admin-btn admin-btn-primary" style="flex-grow: 1; justify-content: center; padding: 0.8rem;">บันทึกรายการสินค้า</button>
            <a href="products.php" class="admin-btn admin-btn-secondary" style="padding: 0.8rem 1.5rem;">ยกเลิก</a>
        </div>
    </form>
</div>

<?php
include_once 'footer.php';
?>