document.addEventListener('DOMContentLoaded', function () {
    const themeSelector = document.getElementById('theme');
    const body = document.body;

    // Load saved theme from localStorage
    const savedTheme = localStorage.getItem('theme') || 'light';
    body.setAttribute('data-theme', savedTheme);
    themeSelector.value = savedTheme;

    // Change theme when the user selects a new option
    themeSelector.addEventListener('change', function () {
        const selectedTheme = themeSelector.value;
        body.setAttribute('data-theme', selectedTheme);
        localStorage.setItem('theme', selectedTheme); // Save theme preference
    });
});