document.querySelectorAll('[data-copy]').forEach((button) => {
  button.addEventListener('click', async () => {
    await navigator.clipboard.writeText(button.dataset.copy || '');
    const old = button.textContent;
    button.textContent = '✓ Copied';
    setTimeout(() => (button.textContent = old), 1400);
  });
});
