/**
 * CHÉRIE — Main Interactive Scripts
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Mobile Drawer Toggle
    const mobileToggle = document.querySelector('.mobile-nav-toggle');
    const mobileDrawer = document.getElementById('mobileDrawer');
    const closeDrawerBtn = document.getElementById('closeDrawer');

    if (mobileToggle && mobileDrawer) {
        mobileToggle.addEventListener('click', function () {
            mobileDrawer.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    }

    if (closeDrawerBtn && mobileDrawer) {
        closeDrawerBtn.addEventListener('click', function () {
            mobileDrawer.classList.remove('open');
            document.body.style.overflow = '';
        });
    }

    // 2. Search Modal / Overlay
    const searchTrigger = document.querySelector('.search-trigger');
    const searchModal = document.getElementById('searchModal');
    const closeSearchBtn = document.getElementById('closeSearch');

    if (searchTrigger && searchModal) {
        searchTrigger.addEventListener('click', function (e) {
            e.preventDefault();
            searchModal.classList.add('open');
            const input = searchModal.querySelector('input[type="search"]');
            if (input) setTimeout(() => input.focus(), 100);
        });
    }

    if (closeSearchBtn && searchModal) {
        closeSearchBtn.addEventListener('click', function () {
            searchModal.classList.remove('open');
        });
    }

    // 3. Global Toast Notifications
    window.showToast = function (message, type = 'info') {
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.style.cssText = 'position:fixed; bottom:25px; right:25px; z-index:99999; display:flex; flex-direction:column; gap:10px; max-width:350px;';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `toast-item toast-${type}`;
        toast.style.cssText = 'background:#49383C; color:#FFF; padding:14px 20px; border-radius:12px; font-size:13.5px; box-shadow:0 8px 24px rgba(0,0,0,0.15); border-left:4px solid #C58A9C; opacity:0; transform:translateY(15px); transition:all 0.3s ease; display:flex; align-items:center; justify-content:space-between; gap:10px;';
        
        if (type === 'success') {
            toast.style.borderLeftColor = '#27ae60';
        } else if (type === 'error') {
            toast.style.borderLeftColor = '#e74c3c';
        }

        toast.innerHTML = `<span>${message}</span><button style="background:none; border:none; color:#FFF; font-size:16px; cursor:pointer;" onclick="this.parentElement.remove()">&times;</button>`;
        toastContainer.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });

        // Auto remove
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(15px)';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    };

    // 4. Newsletter Form Handling
    const newsletterForms = document.querySelectorAll('.newsletter-ajax-form');
    newsletterForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const emailInput = form.querySelector('input[type="email"]');
            const email = emailInput ? emailInput.value.trim() : '';
            if (!email) return;

            const formData = new FormData(form);
            formData.append('ajax_newsletter', '1');

            fetch(form.action || 'index.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Thank you for joining our boutique circle!', 'success');
                    if (emailInput) emailInput.value = '';
                } else {
                    showToast(data.message || 'Subscription failed. Please check your email.', 'error');
                }
            })
            .catch(() => {
                showToast('Thank you for subscribing to CHÉRIE updates!', 'success');
                if (emailInput) emailInput.value = '';
            });
        });
    });
});
