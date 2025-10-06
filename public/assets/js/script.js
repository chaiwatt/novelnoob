/**
 * Central JavaScript for Novel Noob Project
 * Version: 1.0
 * Description: Contains reusable functions for mobile navigation, password toggling,
 * community post interactions (reactions, comments), and modals.
 */

document.addEventListener('DOMContentLoaded', () => {

    /**
     * Sets up the mobile navigation toggle functionality.
     * @param {string} toggleId - The ID of the hamburger button.
     * @param {string} menuId - The ID of the mobile menu overlay.
     */
    function setupMobileMenu(toggleId, menuId) {
        const mobileNavToggle = document.getElementById(toggleId);
        const mobileMenu = document.getElementById(menuId);
        const body = document.body;

        if (!mobileNavToggle || !mobileMenu) return;

        const toggleMenu = () => {
            const isOpen = mobileNavToggle.classList.toggle('open');
            mobileMenu.classList.toggle('open');
            body.classList.toggle('no-scroll', isOpen);
        };

        const closeMenu = () => {
            mobileNavToggle.classList.remove('open');
            mobileMenu.classList.remove('open');
            body.classList.remove('no-scroll');
        };

        mobileNavToggle.addEventListener('click', toggleMenu);
        mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', closeMenu);
        });
    }

    /**
     * Sets up the password visibility toggle for an input field.
     * @param {string} inputId - The ID of the password input field.
     * @param {string} toggleButtonId - The ID of the button that toggles visibility.
     * @param {string} eyeIconId - The ID of the visible/eye icon SVG.
     * @param {string} eyeSlashIconId - The ID of the hidden/eye-slash icon SVG.
     */
    function setupPasswordToggle(inputId, toggleButtonId, eyeIconId, eyeSlashIconId) {
        const passwordInput = document.getElementById(inputId);
        const toggleButton = document.getElementById(toggleButtonId);
        const eyeIcon = document.getElementById(eyeIconId);
        const eyeSlashIcon = document.getElementById(eyeSlashIconId);

        if (passwordInput && toggleButton && eyeIcon && eyeSlashIcon) {
            toggleButton.addEventListener('click', () => {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                eyeIcon.classList.toggle('hide', isPassword);
                eyeSlashIcon.classList.toggle('hide', !isPassword);
            });
        }
    }

    // --- Community/Post Specific Functions ---
    // These functions can be called from community.html and single-post.html

    /**
     * Returns style information for a given reaction emoji.
     * @param {string} emoji - The reaction emoji (e.g., '👍', '❤️').
     * @returns {{text: string, color: string}}
     */
    window.getReactionInfo = function(emoji) {
        const reactions = {
            '👍': { text: 'ถูกใจ', color: 'var(--primary-accent)' },
            '❤️': { text: 'รักเลย', color: '#ef4444' },
            '😂': { text: 'ฮา', color: '#facc15' },
            '😮': { text: 'ว้าว', color: '#facc15' },
            '😢': { text: 'เศร้า', color: '#3b82f6' },
            '😠': { text: 'โกรธ', color: '#f97316' }
        };
        return reactions[emoji] || { text: 'ถูกใจ', color: 'var(--text-secondary)' };
    }

    /**
     * Updates the displayed stats (reactions, useful counts) for a post card.
     * @param {HTMLElement} postCard - The post card element to update.
     */
    window.updatePostStats = function(postCard) {
        const reactionsData = JSON.parse(postCard.dataset.reactions || '{}');
        const usefulData = JSON.parse(postCard.dataset.usefulUsers || '{}');
        const totalLikes = Object.keys(reactionsData).length;
        const totalUseful = Object.keys(usefulData).length;
        const uniqueReactions = [...new Set(Object.values(reactionsData))];

        const summaryContainer = postCard.querySelector('.reactions-summary');
        const totalLikesSpan = postCard.querySelector('.total-likes');
        const usefulStatsContainer = postCard.querySelector('.useful-stats');
        const usefulCountSpan = postCard.querySelector('.useful-count');

        // Update reactions
        if (summaryContainer && totalLikesSpan) {
            totalLikesSpan.textContent = totalLikes > 0 ? totalLikes : '';
            summaryContainer.querySelectorAll('.reaction-icon').forEach(icon => icon.remove());
            uniqueReactions.forEach(emoji => {
                const iconSpan = document.createElement('span');
                iconSpan.className = 'reaction-icon';
                iconSpan.textContent = emoji;
                summaryContainer.prepend(iconSpan);
            });
        }

        // Update useful count
        if (usefulStatsContainer && usefulCountSpan) {
            if (totalUseful > 0) {
                usefulCountSpan.textContent = totalUseful;
                usefulStatsContainer.style.display = 'flex';
            } else {
                usefulCountSpan.textContent = '';
                usefulStatsContainer.style.display = 'none';
            }
        }
    }


    // --- Expose functions to the global scope to be callable from HTML files ---
    window.setupMobileMenu = setupMobileMenu;
    window.setupPasswordToggle = setupPasswordToggle;

    // --- Auto-initialize components found on the current page ---
    if (document.getElementById('mobile-nav-toggle')) {
        setupMobileMenu('mobile-nav-toggle', 'mobile-menu');
    }
    if (document.getElementById('toggle-password')) {
        setupPasswordToggle('password', 'toggle-password', 'eye-icon', 'eye-slash-icon');
    }
     if (document.getElementById('toggle-confirm-password')) {
        setupPasswordToggle('confirm-password', 'toggle-confirm-password', 'confirm-eye-icon', 'confirm-eye-slash-icon');
    }

});
