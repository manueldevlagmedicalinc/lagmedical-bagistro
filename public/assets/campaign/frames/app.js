(() => {
  const root = document.querySelector('.lf-page');
  if (!root) return;
  const signup = document.getElementById('lf-signup');
  const privacy = document.getElementById('lf-privacy');
  const open = dialog => {
    dialog.showModal();
    requestAnimationFrame(() => dialog.classList.add('lf-dialog-visible'));
    document.body.style.overflow = 'hidden';
  };
  const close = dialog => {
    dialog.classList.remove('lf-dialog-visible');
    window.setTimeout(() => {
      if (dialog.open) dialog.close();
      document.body.style.overflow = '';
    }, 220);
  };
  window.setTimeout(() => { if (!signup.open) open(signup); }, 25000);
  root.querySelectorAll('[data-signup]').forEach(button => button.addEventListener('click', () => open(signup)));
  root.querySelectorAll('.lf-privacy-open').forEach(button => button.addEventListener('click', () => open(privacy)));
  const form = document.getElementById('lf-form');
  const submitButton = form?.querySelector('[data-submit-button]');
  const submitText = submitButton?.querySelector('[data-submit-text]');

  form?.addEventListener('submit', () => {
    if (!submitButton || !submitText) return;

    submitButton.disabled = true;
    submitButton.setAttribute('aria-busy', 'true');
    submitText.textContent = 'Enviando...';
    submitButton.insertAdjacentHTML('afterbegin', '<span class="lf-spinner" aria-hidden="true"></span>');
  });
  [signup, privacy].forEach(dialog => {
    dialog.querySelector('.lf-close').addEventListener('click', () => close(dialog));
    dialog.addEventListener('click', event => {
      if (event.target === dialog) close(dialog);
    });
  });
  if (signup.querySelector('.lf-form-message')) open(signup);
})();
