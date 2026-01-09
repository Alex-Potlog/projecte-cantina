document.addEventListener('DOMContentLoaded', () => {
    const darkLightButton = document.getElementById('darkLight');
    const body = document.body;

    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        body.classList.add('darkmode');
    }

    if (darkLightButton) {
        darkLightButton.addEventListener('click', (e) => {
            e.preventDefault();
            body.classList.toggle('darkmode');
            if (body.classList.contains('darkmode')) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
        });
    }
});