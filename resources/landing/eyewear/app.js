(() => {
  const root = document.querySelector('.lf-page');
  const brands = root.querySelector('.lf-all-brands');
  const desktop = window.matchMedia('(min-width: 901px)');
  const syncBrands = () => { brands.open = desktop.matches; };
  syncBrands();
  desktop.addEventListener('change', syncBrands);
  const signup = document.getElementById('lf-signup');
  const privacy = document.getElementById('lf-privacy');
  const open = dialog => { dialog.showModal(); document.body.style.overflow = 'hidden'; };
  const scheduleSignup = () => {
    window.setTimeout(() => { if (!signup.open) open(signup); }, 25000);
  };
  if (document.readyState === 'complete') scheduleSignup();
  else window.addEventListener('load', scheduleSignup, { once: true });
  root.querySelectorAll('[data-signup]').forEach(button => button.addEventListener('click', () => open(signup)));
  root.querySelectorAll('.lf-privacy-open').forEach(button => button.addEventListener('click', () => open(privacy)));
  [signup, privacy].forEach(dialog => {
    dialog.querySelector('.lf-close').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => { if (event.target === dialog) { const r = dialog.getBoundingClientRect(); if (event.clientX < r.left || event.clientX > r.right || event.clientY < r.top || event.clientY > r.bottom) dialog.close(); }});
    dialog.addEventListener('close', () => { if (!signup.open && !privacy.open) document.body.style.overflow = ''; });
  });
  const form = document.getElementById('lf-form');
  const message = form.querySelector('.lf-form-message');
  const submit = form.querySelector('[type="submit"]');
  form.addEventListener('submit', async event => {
    event.preventDefault();
    if (form.elements.email_address_check.value) return;
    message.textContent = 'Enviando…';
    message.dataset.state = 'loading';
    submit.disabled = true;
    form.setAttribute('aria-busy', 'true');
    try {
      const response = await fetch('/api/subscribe', {
        method: 'POST',
        headers: { 'content-type': 'application/json' },
        body: JSON.stringify({ email: form.elements.EMAIL.value, locale: form.elements.locale.value })
      });
      const result = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(result.message || 'No pudimos completar la suscripción.');
      message.textContent = result.message || '¡Listo! Revisa tu correo para confirmar la suscripción.';
      message.dataset.state = 'success';
      form.reset();
    } catch (error) {
      message.textContent = error.message || 'Ocurrió un error. Inténtalo de nuevo.';
      message.dataset.state = 'error';
    } finally {
      submit.disabled = false;
      form.removeAttribute('aria-busy');
    }
  });
})();
