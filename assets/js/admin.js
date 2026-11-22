document.querySelectorAll('форма[onsubmit]').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('кнопка[тип="отправить"]');
        if (button) {
            button.disabled = true;
            setTimeout(() => {
                button.disabled = false;
            }, 2000);
        }
    });
});