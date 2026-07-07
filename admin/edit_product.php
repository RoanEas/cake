<?php
include_once 'header.php';

// Fetch Categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

if(!isset($_GET['id'])) {
    echo "<script>window.location.href='products.php';</script>";
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$product) {
    echo "<script>window.location.href='products.php';</script>";
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $category_id = $_POST['category_id'] ?? null;
    
    // Fetch Category Slug for backwards compatibility
    $category_slug = 'minimalist';
    if ($category_id) {
        $stmt_cat = $pdo->prepare("SELECT slug FROM categories WHERE id = ?");
        $stmt_cat->execute([$category_id]);
        $category_slug = $stmt_cat->fetchColumn() ?: 'minimalist';
    }

    // Read the current state from POST fields (which may have been shifted/cleared by JS)
    $image_path = $_POST['current_image'] ?? '';
    $image_paths = [
        $_POST['current_image_1'] ?? null,
        $_POST['current_image_2'] ?? null,
        $_POST['current_image_3'] ?? null
    ];

    // Main Image
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            if(!empty($product['image']) && file_exists("../" . $product['image'])) {
                unlink("../" . $product['image']);
            }
            $image_path = "uploads/" . $new_filename;
        }
    } else {
        if (empty($image_path)) {
            if(!empty($product['image']) && file_exists("../" . $product['image'])) {
                unlink("../" . $product['image']);
            }
        }
    }

    // Additional Images 1, 2, 3
    for ($i = 1; $i <= 3; $i++) {
        $key = 'image_' . $i;
        $orig_key = 'image_' . $i;
        
        if (isset($_FILES[$key]) && $_FILES[$key]['error'] == 0) {
            $target_dir = "../uploads/";
            if(!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_extension = pathinfo($_FILES[$key]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '_edit_' . $i . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if(move_uploaded_file($_FILES[$key]["tmp_name"], $target_file)) {
                if(!empty($product[$orig_key]) && file_exists("../" . $product[$orig_key])) {
                    unlink("../" . $product[$orig_key]);
                }
                $image_paths[$i-1] = "uploads/" . $new_filename;
            }
        } else {
            $current_val = $image_paths[$i-1];
            $orig_file = $product[$orig_key];
            if (!empty($orig_file) && file_exists("../" . $orig_file)) {
                $still_used = ($image_path === $orig_file || in_array($orig_file, $image_paths));
                if (!$still_used) {
                    unlink("../" . $orig_file);
                }
            }
        }
    }

    // Clean up empty paths to NULL for database consistency
    $image_path = !empty($image_path) ? $image_path : null;
    $image_paths[0] = !empty($image_paths[0]) ? $image_paths[0] : null;
    $image_paths[1] = !empty($image_paths[1]) ? $image_paths[1] : null;
    $image_paths[2] = !empty($image_paths[2]) ? $image_paths[2] : null;

    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, image=?, image_1=?, image_2=?, image_3=?, category=?, category_id=? WHERE id=?");
    if($stmt->execute([$name, $description, $price, $image_path, $image_paths[0], $image_paths[1], $image_paths[2], $category_slug, $category_id, $id])) {
        echo "<script>window.location.href='products.php';</script>";
        exit;
    }
}
?>

<div class="admin-card" style="max-width: 800px; margin: 0 auto;">
    <div class="admin-card-title">แก้ไขรายละเอียดสินค้า</div>
    <form method="POST" enctype="multipart/form-data">
        <!-- Hidden inputs to track current image paths on the server -->
        <input type="hidden" id="current_image" name="current_image" value="<?= htmlspecialchars($product['image'] ?? '') ?>">
        <input type="hidden" id="current_image_1" name="current_image_1" value="<?= htmlspecialchars($product['image_1'] ?? '') ?>">
        <input type="hidden" id="current_image_2" name="current_image_2" value="<?= htmlspecialchars($product['image_2'] ?? '') ?>">
        <input type="hidden" id="current_image_3" name="current_image_3" value="<?= htmlspecialchars($product['image_3'] ?? '') ?>">

        <div class="admin-form-group">
            <label>ชื่อเค้ก</label>
            <input type="text" name="name" class="admin-form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="admin-form-group">
                <label>ราคา (บาท)</label>
                <input type="number" step="0.01" name="price" class="admin-form-control" value="<?= $product['price'] ?>" required>
            </div>
            
            <div class="admin-form-group">
                <label>หมวดหมู่เค้ก</label>
                <select name="category_id" class="admin-form-control" required>
                    <option value="">-- เลือกหมวดหมู่ --</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? null) == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="admin-form-group">
            <label>รายละเอียด / ส่วนผสม / ขนาดเค้ก</label>
            <textarea name="description" class="admin-form-control" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <div class="admin-form-group">
            <label style="margin-bottom: 1rem;">อัพโหลดรูปภาพประกอบเค้ก (UI อัพโหลดแบบใหม่)</label>
            <div class="upload-grid">
                <!-- Main Image Upload Card -->
                <div>
                    <span class="upload-label-badge">รูปภาพหลัก</span>
                    <div class="image-upload-card <?= !empty($product['image']) ? 'upload-preview-active' : '' ?>" id="card-main" style="position: relative;">
                        <button type="button" class="delete-img-btn" onclick="deleteAndShift(0)" title="ลบรูปภาพ">×</button>
                        <input type="file" name="image" id="input-main" accept="image/*" onchange="previewImage(this, 'preview-main', 'card-main')">
                        <div class="upload-placeholder">
                            <span class="upload-icon">📸</span>
                            <span style="font-size: 0.8rem; font-weight: 500;">เลือกรูปภาพหลัก</span>
                        </div>
                        <div class="upload-preview" id="preview-wrap-main">
                            <img id="preview-main" src="../<?= htmlspecialchars($product['image'] ?? '') ?>" alt="Main Preview">
                        </div>
                    </div>
                </div>

                <!-- Add Image 1 -->
                <div>
                    <span class="upload-label-badge">รูปเพิ่มเติม 1</span>
                    <div class="image-upload-card <?= !empty($product['image_1']) ? 'upload-preview-active' : '' ?>" id="card-1" style="position: relative;">
                        <button type="button" class="delete-img-btn" onclick="deleteAndShift(1)" title="ลบรูปภาพ">×</button>
                        <input type="file" name="image_1" id="input-1" accept="image/*" onchange="previewImage(this, 'preview-1', 'card-1')">
                        <div class="upload-placeholder">
                            <span class="upload-icon">➕</span>
                            <span style="font-size: 0.8rem; font-weight: 500;">รูปประกอบ 1</span>
                        </div>
                        <div class="upload-preview" id="preview-wrap-1">
                            <img id="preview-1" src="../<?= htmlspecialchars($product['image_1'] ?? '') ?>" alt="Preview 1">
                        </div>
                    </div>
                </div>

                <!-- Add Image 2 -->
                <div>
                    <span class="upload-label-badge">รูปเพิ่มเติม 2</span>
                    <div class="image-upload-card <?= !empty($product['image_2']) ? 'upload-preview-active' : '' ?>" id="card-2" style="position: relative;">
                        <button type="button" class="delete-img-btn" onclick="deleteAndShift(2)" title="ลบรูปภาพ">×</button>
                        <input type="file" name="image_2" id="input-2" accept="image/*" onchange="previewImage(this, 'preview-2', 'card-2')">
                        <div class="upload-placeholder">
                            <span class="upload-icon">➕</span>
                            <span style="font-size: 0.8rem; font-weight: 500;">รูปประกอบ 2</span>
                        </div>
                        <div class="upload-preview" id="preview-wrap-2">
                            <img id="preview-2" src="../<?= htmlspecialchars($product['image_2'] ?? '') ?>" alt="Preview 2">
                        </div>
                    </div>
                </div>

                <!-- Add Image 3 -->
                <div>
                    <span class="upload-label-badge">รูปเพิ่มเติม 3</span>
                    <div class="image-upload-card <?= !empty($product['image_3']) ? 'upload-preview-active' : '' ?>" id="card-3" style="position: relative;">
                        <button type="button" class="delete-img-btn" onclick="deleteAndShift(3)" title="ลบรูปภาพ">×</button>
                        <input type="file" name="image_3" id="input-3" accept="image/*" onchange="previewImage(this, 'preview-3', 'card-3')">
                        <div class="upload-placeholder">
                            <span class="upload-icon">➕</span>
                            <span style="font-size: 0.8rem; font-weight: 500;">รูปประกอบ 3</span>
                        </div>
                        <div class="upload-preview" id="preview-wrap-3">
                            <img id="preview-3" src="../<?= htmlspecialchars($product['image_3'] ?? '') ?>" alt="Preview 3">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="admin-btn admin-btn-primary" style="flex-grow: 1; justify-content: center; padding: 0.8rem;">อัปเดตสินค้า</button>
            <a href="products.php" class="admin-btn admin-btn-secondary" style="padding: 0.8rem 1.5rem;">ยกเลิก</a>
        </div>
    </form>
</div>

<style>
.image-upload-card {
    position: relative;
    border: 2.5px dashed #e2e8f0;
    border-radius: 14px;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    overflow: visible !important;
    background-color: #fafafa;
}
.image-upload-card:hover {
    border-color: #ffb7c5;
    background-color: #fffafb;
    transform: translateY(-3px);
}
.image-upload-card.upload-preview-active {
    border: 2.5px dashed #ff8a9e; /* Cute dashed pink border matching screenshot */
    background-color: #fff5f6;
}
.image-upload-card.upload-preview-active:hover {
    border-color: #ff5e7e;
    box-shadow: 0 8px 20px rgba(255, 94, 126, 0.15);
}
.delete-img-btn {
    display: none;
    position: absolute;
    top: -10px;
    right: -10px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #ff5e7e; /* Cute strawberry red */
    color: white;
    border: 2.5px solid white;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    align-items: center;
    justify-content: center;
    z-index: 100;
    box-shadow: 0 3px 8px rgba(255, 94, 126, 0.4);
    transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    font-family: Arial, sans-serif;
    line-height: 1;
}
.delete-img-btn:hover {
    background: #ff3b60;
    transform: scale(1.2) rotate(90deg); /* Bouncy spin scale effect */
    box-shadow: 0 5px 12px rgba(255, 59, 96, 0.55);
}
.image-upload-card.upload-preview-active .delete-img-btn {
    display: flex;
}
</style>

<script>
function deleteAndShift(index) {
    if (!confirm('คุณต้องการลบรูปภาพนี้ใช่หรือไม่? (รูปภาพในช่องถัดไปจะถูกเลื่อนขึ้นมาแทนที่โดยอัตโนมัติ)')) {
        return;
    }
    
    const cardIds = ['card-main', 'card-1', 'card-2', 'card-3'];
    const inputIds = ['input-main', 'input-1', 'input-2', 'input-3'];
    const hiddenIds = ['current_image', 'current_image_1', 'current_image_2', 'current_image_3'];
    const previewIds = ['preview-main', 'preview-1', 'preview-2', 'preview-3'];
    
    // Shift subsequent values
    for (let i = index; i < 3; i++) {
        const nextIdx = i + 1;
        const currentCard = document.getElementById(cardIds[i]);
        const nextCard = document.getElementById(cardIds[nextIdx]);
        const currentHidden = document.getElementById(hiddenIds[i]);
        const nextHidden = document.getElementById(hiddenIds[nextIdx]);
        const currentInput = document.getElementById(inputIds[i]);
        const nextInput = document.getElementById(inputIds[nextIdx]);
        const currentPreview = document.getElementById(previewIds[i]);
        const nextPreview = document.getElementById(previewIds[nextIdx]);
        
        // Copy hidden path
        currentHidden.value = nextHidden.value;
        
        // Copy file selection using DataTransfer
        const dt = new DataTransfer();
        if (nextInput.files && nextInput.files[0]) {
            dt.items.add(nextInput.files[0]);
        }
        currentInput.files = dt.files;
        
        // Copy preview image src
        currentPreview.src = nextPreview.src;
        
        // Copy active state class
        if (nextCard.classList.contains('upload-preview-active')) {
            currentCard.classList.add('upload-preview-active');
        } else {
            currentCard.classList.remove('upload-preview-active');
        }
    }
    
    // Clear last slot (index 3)
    const lastCard = document.getElementById(cardIds[3]);
    const lastHidden = document.getElementById(hiddenIds[3]);
    const lastInput = document.getElementById(inputIds[3]);
    const lastPreview = document.getElementById(previewIds[3]);
    
    lastHidden.value = '';
    lastInput.value = ''; // clears files list
    lastPreview.src = '';
    lastCard.classList.remove('upload-preview-active');
}
</script>
</div>

<?php
include_once 'footer.php';
?>
