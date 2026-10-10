(function () {
    'use strict';

    const dialog = document.getElementById('app-confirm-dialog');
    const message = document.getElementById('app-confirm-message');
    const cancelButton = dialog && dialog.querySelector('[data-confirm-cancel]');
    const acceptButton = dialog && dialog.querySelector('[data-confirm-accept]');

    if (!dialog || !message || !cancelButton || !acceptButton || typeof dialog.showModal !== 'function') {
        return;
    }

    let callbacks = null;

    function close(accepted) {
        const current = callbacks;
        callbacks = null;
        dialog.close();

        if (current) {
            const callback = accepted ? current.accept : current.cancel;
            if (typeof callback === 'function') {
                callback();
            }
        }
    }

    cancelButton.addEventListener('click', function () {
        close(false);
    });

    acceptButton.addEventListener('click', function () {
        close(true);
    });

    dialog.addEventListener('cancel', function (event) {
        event.preventDefault();
        close(false);
    });

    window.yii.confirm = function (text, accept, cancel) {
        if (dialog.open) {
            close(false);
        }

        callbacks = {accept: accept, cancel: cancel};
        message.textContent = String(text || 'آیا از انجام این عملیات مطمئن هستید؟');
        dialog.showModal();
        cancelButton.focus();
    };
})();
