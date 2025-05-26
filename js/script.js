document.addEventListener('DOMContentLoaded', () => {
    // Hamburger menu toggle
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    if (hamburger && navMenu) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Collapse menu when a link is clicked
        document.querySelectorAll('.nav-menu a').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
    }

    // Popup close button
    const popupClose = document.querySelector('.popup-close');
    const popup = document.querySelector('.preference-popup');

    if (popupClose && popup) {
        popupClose.addEventListener('click', () => {
            popup.style.display = 'none';
        });
    }
});