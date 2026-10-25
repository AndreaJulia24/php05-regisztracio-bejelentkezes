//Light /dark mód váltó
(function () {
    const root = document.documentElement;

    const saved = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    let theme = saved || (prefersDark ? 'dark' : 'light');

    root.setAttribute('data-theme', theme);

    document.addEventListener('DOMContentLoaded', function () {
        const button = document.querySelector('.theme-toggle');
        if (!button) {
            return;
        }
       
        function updateButton() {
            const icon = theme === 'dark' ? 'assets/icons/sun.png' : 'assets/icons/moon.png';
            const text = theme === 'dark' ? 'Light' : 'Dark';
            button.innerHTML = '<img src="' + icon + '" alt="">' + '<span>' + text + '</span>';
            button.setAttribute('aria-label', theme === 'dark' ? 'Világos mód' : 'Sötét mód');
        }

        updateButton();

        button.addEventListener('click', function () {
            theme = theme === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            updateButton();
        });
    });
})();
