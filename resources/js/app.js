const copyText = async (text) => {
    if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);

        return;
    }

    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'fixed';
    textarea.style.top = '-1000px';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    textarea.remove();
};

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-copy-all]');

    if (! button) {
        return;
    }

    const originalText = button.textContent;
    const encodedText = button.dataset.copyText || '';
    const text = decodeURIComponent(encodedText);

    try {
        await copyText(text);
        button.textContent = 'Copied';
    } catch {
        button.textContent = 'Copy failed';
    }

    window.setTimeout(() => {
        button.textContent = originalText;
    }, 1800);
});
