export function initPostFeedInteractions(root = document) {
    const reactionMenuWrappers = Array.from(root.querySelectorAll('[data-reaction-menu-wrapper]'));
    const postCards = Array.from(root.querySelectorAll('[data-post-card]'));

    if (reactionMenuWrappers.length === 0 && postCards.length === 0) {
        return;
    }

    const closeAllReactionMenus = () => {
        reactionMenuWrappers.forEach((wrapper) => {
            const button = wrapper.querySelector('[data-reaction-menu-button]');
            const menu = wrapper.querySelector('[data-reaction-menu]');

            if (menu) {
                menu.classList.add('hidden');
            }

            if (button) {
                button.setAttribute('aria-expanded', 'false');
            }
        });
    };

    reactionMenuWrappers.forEach((wrapper) => {
        const button = wrapper.querySelector('[data-reaction-menu-button]');
        const menu = wrapper.querySelector('[data-reaction-menu]');

        if (!button || !menu) {
            return;
        }

        button.addEventListener('click', (event) => {
            event.stopPropagation();

            const isOpen = !menu.classList.contains('hidden');

            closeAllReactionMenus();

            if (!isOpen) {
                menu.classList.remove('hidden');
                button.setAttribute('aria-expanded', 'true');
            }
        });

        menu.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    });

    postCards.forEach((card) => {
        const openPost = () => {
            const postUrl = card.dataset.postUrl;

            if (postUrl) {
                window.location.href = postUrl;
            }
        };

        card.setAttribute('role', 'link');
        card.setAttribute('tabindex', '0');

        card.addEventListener('click', (event) => {
            if (event.target.closest('a, button, form, input, textarea, select, label')) {
                return;
            }

            openPost();
        });

        card.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openPost();
            }
        });
    });

    document.addEventListener('click', () => {
        closeAllReactionMenus();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAllReactionMenus();
        }
    });
}
