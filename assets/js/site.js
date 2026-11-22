(function () {
    const root = document.documentElement;
    const toggle = document.querySelector('[переключатель темы-данных]');
    const label = document.querySelector('[метка-темы-данных]');
    const storageKey = 'domnin-quiz-theme';
    const applyTheme = (theme) => {
        const normalized = theme === 'dark' ? 'dark' : 'light';
        root.setAttribute('data-theme', normalized);
        if (label) {
            label.textContent = normalized === 'dark' ? '\u2600\uFE0F' : '\uD83C\uDF19';
        }
        try {
            localStorage.setItem(storageKey, normalized);
        } catch (e) {
        }
    };
    let saved = 'light';
    try {
        saved = localStorage.getItem(storageKey) || 'light';
    } catch (e) {
        saved = 'light';
    }
    applyTheme(saved);
    if (toggle) {
        toggle.addEventListener('click', () => {
            applyTheme(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
        });
    }
    document.querySelectorAll('.answers-form').forEach((form) => {
        const radios = form.querySelectorAll('ввод[тип="радио"][имя="ответ_ид"]');
        radios.forEach((radio) => {
            radio.addEventListener('change', () => {
                form.querySelectorAll('.answer-option').forEach((option) => option.classList.remove('is-selected'));
                if (radio.checked && radio.closest('.answer-option')) {
                    radio.closest('.answer-option').classList.add('is-selected');
                }
            });
        });
    });
})();