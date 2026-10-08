(() => {
    console.log('[Eblast] script cargado');

    const runConnectionTest = async (container, button, result) => {
        console.log('[Eblast] click en probar conexión');

        const apiKey = document.querySelector('input[name*="api_key"]')?.value?.trim();
        const listId = Number(document.querySelector('input[name*="frames_campaign_list_key"]')?.value || 9);
        const csrf = document.querySelector('input[name="_token"]')?.value
            || document.querySelector('meta[name="csrf-token"]')?.content
            || '';

        console.log('[Eblast] API key encontrada:', Boolean(apiKey));
        console.log('[Eblast] ID de lista:', listId);
        console.log('[Eblast] URL de prueba:', container.dataset.testUrl);
        console.log('[Eblast] CSRF encontrado:', Boolean(csrf));

        if (!apiKey || !listId) {
            console.error('[Eblast] faltan API key o ID de lista');
            result.textContent = 'Introduce la API key y el ID de la lista antes de probar la conexión.';
            result.className = 'mt-2 text-sm text-red-600';

            return;
        }

        button.disabled = true;
        result.textContent = 'Probando conexión...';
        result.className = 'mt-2 text-sm text-gray-500';

        try {
            console.log('[Eblast] enviando fetch');

            const response = await fetch(container.dataset.testUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ api_key: apiKey, list_id: listId }),
            });
            const payload = await response.json();

            console.log('[Eblast] respuesta HTTP:', response.status, payload);

            if (!response.ok) {
                throw new Error(payload.message || `Error HTTP ${response.status}.`);
            }

            result.textContent = `${payload.message} Lista: ${payload.list.name || listId}. Contactos: ${payload.list.contacts ?? 'no disponible'}.`;
            result.className = 'mt-2 text-sm text-green-600';
        } catch (error) {
            console.error('[Eblast] error en la prueba:', error);
            result.textContent = error.message || 'No se pudo conectar con Brevo.';
            result.className = 'mt-2 text-sm text-red-600';
        } finally {
            button.disabled = false;
        }
    };

    document.addEventListener('click', event => {
        const button = event.target.closest('[data-eblast-test-connection]');

        if (!button) return;

        console.log('[Eblast] evento click capturado por document');

        const container = button.closest('[data-eblast-connection]');
        const result = container?.querySelector('[data-eblast-connection-result]');

        if (!container || !result) {
            console.error('[Eblast] botón encontrado, pero falta su contenedor o resultado');

            return;
        }

        event.preventDefault();
        runConnectionTest(container, button, result);
    }, true);

    const boot = () => {
        console.log('[Eblast] boot ejecutado');
        console.log('[Eblast] contenedores encontrados:', document.querySelectorAll('[data-eblast-connection]').length);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();
