/**
 * CHÉRIE — Admin Panel Interactive Scripts
 */

document.addEventListener('DOMContentLoaded', function () {
    // Multi-Image Upload File Previewer
    const imageInput = document.getElementById('productImageInput');
    const previewContainer = document.getElementById('imagePreviewContainer');

    if (imageInput && previewContainer) {
        imageInput.addEventListener('change', function () {
            previewContainer.innerHTML = '';
            const files = Array.from(this.files);

            files.forEach((file, index) => {
                if (!file.type.match('image.*')) return;

                const reader = new FileReader();
                reader.onload = function (e) {
                    const wrap = document.createElement('div');
                    wrap.style.cssText = 'position:relative; display:inline-block; margin-right:10px; margin-bottom:10px;';

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'preview-thumb';

                    const badge = document.createElement('span');
                    badge.style.cssText = 'position:absolute; bottom:5px; left:5px; background:rgba(0,0,0,0.6); color:#fff; font-size:10px; padding:2px 6px; border-radius:4px;';
                    badge.textContent = index === 0 ? 'Primary' : `Image ${index + 1}`;

                    wrap.appendChild(img);
                    wrap.appendChild(badge);
                    previewContainer.appendChild(wrap);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    // Confirmation Prompts for Delete Actions
    const deleteButtons = document.querySelectorAll('.confirm-delete');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            const itemName = this.getAttribute('data-item-name') || 'this item';
            if (!confirm(`Are you certain you wish to delete ${itemName}? This action cannot be undone.`)) {
                e.preventDefault();
            }
        });
    });
});
