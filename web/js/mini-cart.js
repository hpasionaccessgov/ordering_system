(() => {
    const body = document.getElementById('cafebara-cart-body');
    const drawer = document.getElementById('cafebara-cart-offcanvas');
    if (!body || !drawer || typeof fetch !== 'function') {
        return;
    }

    function showFeedback(message, isError = false) {
        const feedback = body.querySelector('[data-cart-feedback]');
        if (!feedback) {
            return;
        }
        feedback.textContent = message;
        feedback.classList.toggle('text-danger', isError);
        feedback.classList.toggle('text-success', !isError);
    }

    function applyCart(data) {
        body.innerHTML = data.html || '';
        document.querySelectorAll('[data-cart-count]').forEach((badge) => {
            badge.textContent = String(data.count ?? 0);
            badge.classList.toggle('d-none', Number(data.count ?? 0) === 0);
            badge.classList.remove('cafebara-cart-bump');
            void badge.offsetWidth;
            badge.classList.add('cafebara-cart-bump');
        });
        if (data.message) {
            showFeedback(data.message);
        }
    }

    async function requestCart(url, options = {}) {
        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                cache: 'no-store',
                ...options,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.headers || {}),
                },
            });
            if (!response.ok) {
                throw new Error('Cart request failed.');
            }
            applyCart(await response.json());
        } catch (error) {
            showFeedback('Your cart could not be updated. Please try again.', true);
        }
    }

    document.addEventListener('submit', (event) => {
        const form = event.target instanceof HTMLFormElement ? event.target : null;
        if (!form) {
            return;
        }

        if (form.dataset.cartAjax === '1') {
            if (!('submitter' in event)) {
                return;
            }
            event.preventDefault();
            const formData = new FormData(form);
            const submitter = event.submitter;
            if (submitter && submitter.name) {
                formData.set(submitter.name, submitter.value);
            }
            requestCart(form.action, {
                method: 'POST',
                body: formData,
            });
            return;
        }

        const favoriteButton = form.querySelector('[data-favorite-button]');
        if (favoriteButton) {
            favoriteButton.classList.add('cafebara-favorite-pulse');
        }
    });

    drawer.addEventListener('show.bs.offcanvas', () => {
        requestCart(drawer.dataset.cartSummaryUrl);
    });
})();
