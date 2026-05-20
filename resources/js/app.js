import './bootstrap';
import * as lucideIcons from 'lucide';

// Make lucide globally available
window.lucide = {
    createIcons: (options = {}) => {
        lucideIcons.createIcons({
            icons: lucideIcons,
            ...options
        });
    },
    icons: lucideIcons
};

// Initialize icons when document is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.lucide.createIcons();
    });
} else {
    window.lucide.createIcons();
}
