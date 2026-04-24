export function initPostShowInteractions(root = document) {
    const menuWrappers = Array.from(root.querySelectorAll('[data-comment-menu-wrapper]'));
    const reactionMenuWrappers = Array.from(root.querySelectorAll('[data-reaction-menu-wrapper]'));
    const toggleReplyButtons = Array.from(root.querySelectorAll('[data-toggle-replies]'));
    const allRepliesContainers = Array.from(root.querySelectorAll('[data-replies-container]'));
    const allReplyFormContainers = Array.from(root.querySelectorAll('[data-reply-form-container]'));

    if (
        menuWrappers.length === 0 &&
        reactionMenuWrappers.length === 0 &&
        toggleReplyButtons.length === 0
    ) {
        return;
    }

    const closeAllMenus = () => {
        menuWrappers.forEach((wrapper) => {
            const button = wrapper.querySelector('[data-comment-menu-button]');
            const menu = wrapper.querySelector('[data-comment-menu]');

            if (menu) {
                menu.classList.add('hidden');
            }

            if (button) {
                button.setAttribute('aria-expanded', 'false');
            }
        });

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

    toggleReplyButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();

            const commentId = button.dataset.commentId;
            const repliesContainer = root.querySelector(`[data-replies-container="${commentId}"]`);
            const replyFormContainer = root.querySelector(`[data-reply-form-container="${commentId}"]`);
            const replyTextarea = root.querySelector(`[data-reply-textarea="${commentId}"]`);

            const isOpen =
                (repliesContainer && !repliesContainer.classList.contains('hidden')) ||
                (replyFormContainer && !replyFormContainer.classList.contains('hidden'));

            const shouldOpen = !isOpen;

            allRepliesContainers.forEach((container) => {
                container.classList.add('hidden');
            });

            allReplyFormContainers.forEach((container) => {
                container.classList.add('hidden');
            });

            if (repliesContainer) {
                repliesContainer.classList.toggle('hidden', !shouldOpen);
            }

            if (replyFormContainer) {
                replyFormContainer.classList.toggle('hidden', !shouldOpen);
            }

            if (shouldOpen && replyTextarea) {
                replyTextarea.focus();
            }
        });
    });

    menuWrappers.forEach((wrapper) => {
        const button = wrapper.querySelector('[data-comment-menu-button]');
        const menu = wrapper.querySelector('[data-comment-menu]');

        if (!button || !menu) {
            return;
        }

        button.addEventListener('click', (event) => {
            event.stopPropagation();

            const isOpen = !menu.classList.contains('hidden');

            closeAllMenus();

            if (!isOpen) {
                menu.classList.remove('hidden');
                button.setAttribute('aria-expanded', 'true');
            }
        });

        menu.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    });

    reactionMenuWrappers.forEach((wrapper) => {
        const button = wrapper.querySelector('[data-reaction-menu-button]');
        const menu = wrapper.querySelector('[data-reaction-menu]');

        if (!button || !menu) {
            return;
        }

        button.addEventListener('click', (event) => {
            event.stopPropagation();

            const isOpen = !menu.classList.contains('hidden');

            closeAllMenus();

            if (!isOpen) {
                menu.classList.remove('hidden');
                button.setAttribute('aria-expanded', 'true');
            }
        });

        menu.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    });

    document.addEventListener('click', () => {
        closeAllMenus();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAllMenus();
        }
    });
}
