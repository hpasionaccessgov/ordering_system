(() => {
    const modalElement = document.querySelector('[data-confirm-modal-root]');
    if (!modalElement) {
        console.error('Confirmation modal markup is missing.');
        return;
    }
    if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
        console.error('Bootstrap Modal is required for confirmation prompts.');
        return;
    }

    const titleElement = modalElement.querySelector('[data-confirm-modal-title]');
    const messageElement = modalElement.querySelector('[data-confirm-modal-message]');
    const acceptButton = modalElement.querySelector('[data-confirm-modal-accept]');
    if (!titleElement || !messageElement || !acceptButton) {
        console.error('Confirmation modal controls are incomplete.');
        return;
    }
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    let pendingTrigger = null;

    modalElement.addEventListener('show.bs.modal', () => {
        document.body.classList.add('confirm-modal-open');
    });

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-confirm-modal]');
        if (!trigger || trigger.disabled) {
            return;
        }

        event.preventDefault();
        pendingTrigger = trigger;
        titleElement.textContent = trigger.dataset.confirmTitle || 'Confirm action';
        messageElement.textContent = trigger.dataset.confirmModal || 'Are you sure?';
        acceptButton.textContent = trigger.dataset.confirmButton || 'Confirm';
        modal.show();
    });

    acceptButton.addEventListener('click', () => {
        const trigger = pendingTrigger;
        pendingTrigger = null;
        if (!trigger) {
            return;
        }

        modal.hide();

        if (trigger instanceof HTMLAnchorElement) {
            if (trigger.target && trigger.target !== '_self') {
                window.open(trigger.href, trigger.target);
            } else {
                window.location.assign(trigger.href);
            }
            return;
        }

        const form = trigger.form || trigger.closest('form');
        if (!form) {
            console.error('A confirmation control must be inside a form or be a link.');
            return;
        }
        if (typeof form.requestSubmit !== 'function') {
            console.error('This browser does not support form.requestSubmit().');
            return;
        }

        form.requestSubmit(trigger);
    });

    modalElement.addEventListener('hidden.bs.modal', () => {
        pendingTrigger = null;
        document.body.classList.remove('confirm-modal-open');
    });
})();
