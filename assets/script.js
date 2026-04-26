document.addEventListener('DOMContentLoaded', () => {
    // Initialize Lucide icons
    lucide.createIcons();

    // Fade out error/success messages after 3 seconds
    const messages = document.querySelectorAll('.message-alert');
    messages.forEach(msg => {
        setTimeout(() => {
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }, 3000);
    });
});