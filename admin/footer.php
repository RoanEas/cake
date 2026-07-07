        </div> <!-- End of admin-body -->
    </div> <!-- End of admin-main -->
</div> <!-- End of admin-wrapper -->

<script>
    // General JavaScript helper for visual image upload preview
    function previewImage(input, previewId, cardId) {
        const file = input.files[0];
        const previewContainer = document.getElementById(cardId);
        const previewImg = document.getElementById(previewId);
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewContainer.classList.add('upload-preview-active');
            }
            reader.readAsDataURL(file);
        } else {
            previewContainer.classList.remove('upload-preview-active');
        }
    }
</script>
</body>
</html>
