/**
 * CHÉRIE — Wishlist Interactions
 */

document.addEventListener('DOMContentLoaded', function () {
    const wishlistButtons = document.querySelectorAll('.product-wishlist-btn');

    wishlistButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            if (!productId) return;

            const formData = new FormData();
            formData.append('toggle_wishlist', '1');
            formData.append('product_id', productId);

            // Fetch request
            fetch('wishlist.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.need_login) {
                    window.showToast?.('Please sign in to save items to your wishlist.', 'info');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1200);
                    return;
                }

                if (data.success) {
                    if (data.action === 'added') {
                        btn.classList.add('active');
                        btn.style.color = '#FFFFFF';
                        btn.style.backgroundColor = '#C58A9C';
                        window.showToast?.(data.message || 'Saved to your wishlist! 💕', 'success');
                    } else {
                        btn.classList.remove('active');
                        btn.style.color = '#49383C';
                        btn.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
                        window.showToast?.(data.message || 'Removed from wishlist.', 'info');
                        // If we are on wishlist.php itself, remove row
                        const card = btn.closest('.product-card');
                        if (window.location.pathname.includes('wishlist.php') && card) {
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.9)';
                            setTimeout(() => card.remove(), 300);
                        }
                    }

                    // Update header wishlist badge count if returned
                    if (data.wishlist_count !== undefined) {
                        const badge = document.querySelector('.wishlist-badge');
                        if (badge) {
                            badge.textContent = data.wishlist_count;
                            badge.style.display = data.wishlist_count > 0 ? 'flex' : 'none';
                        }
                    }
                }
            })
            .catch(err => {
                console.error(err);
            });
        });
    });
});
