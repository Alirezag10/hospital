document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-debug-toolbar-toggle]');
    if (!toggle) {
        return;
    }

    event.preventDefault();
    const nextState = toggle.dataset.debugToolbarEnabled === '1' ? '0' : '1';
    document.cookie = `yii_debug_toolbar=${nextState}; Path=/; Max-Age=31536000; SameSite=Lax`;
    window.location.reload();
});
