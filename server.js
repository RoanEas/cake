const express = require('express');
const app = express();
const PORT = 3000;

// สั่งให้ Node.js คอยดึงไฟล์จากโฟลเดอร์ public มาแสดงผลเป็นหน้าเว็บ static
app.use(express.static('public'));

app.listen(PORT, () => {
    console.log(`🚀 เซิร์ฟเวอร์รันแล้วที่ http://localhost:${PORT}`);
});