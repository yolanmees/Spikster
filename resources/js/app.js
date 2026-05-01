import './bootstrap';
import '../css/app.css';

// Initialize Lucide icons (loaded via CDN in layout)
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) lucide.createIcons();
});
// Re-run after Livewire navigations
document.addEventListener('livewire:navigated', () => {
    if (window.lucide) lucide.createIcons();
});
document.addEventListener('livewire:update', () => {
    if (window.lucide) lucide.createIcons();
});

