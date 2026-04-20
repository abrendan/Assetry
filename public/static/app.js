// Toggle key visibility
function toggleKey(id) {
    const el = document.getElementById('key-' + id);
    if (!el) return;
    const hidden = el.textContent.includes('•');
    el.textContent = hidden ? el.dataset.key : '••••••••••••••••••••••••••••••';
}

// Copy key to clipboard
function copyKey(id) {
    const el = document.getElementById('key-' + id);
    if (!el) return;
    navigator.clipboard.writeText(el.dataset.key).then(() => {
        // Brief feedback
        const original = el.textContent;
        el.textContent = 'Copied!';
        el.style.color = 'var(--success)';
        setTimeout(() => {
            el.textContent = original;
            el.style.color = '';
        }, 1500);
    }).catch(() => {
        // Fallback: reveal the key
        el.textContent = el.dataset.key;
    });
}

// Auto-dismiss flash messages
document.addEventListener('DOMContentLoaded', function () {
    const flashes = document.querySelectorAll('.flash');
    flashes.forEach(function (flash) {
        setTimeout(function () {
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-4px)';
            flash.style.transition = 'opacity 0.3s, transform 0.3s';
            setTimeout(() => flash.remove(), 300);
        }, 3000);
    });

    // Color picker selection highlight
    document.querySelectorAll('.color-swatch input').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('color-selected'));
            this.closest('.color-swatch').classList.add('color-selected');
        });
    });
});

// Confirm before delete with custom message
document.querySelectorAll('[data-confirm]') && document.addEventListener('submit', function (e) {
    const form = e.target;
    const msg = form.dataset.confirm;
    if (msg && !confirm(msg)) e.preventDefault();
});
