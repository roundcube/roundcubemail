try {
    if (document.cookie.indexOf('colorMode=dark') > -1
        || (document.cookie.indexOf('colorMode=light') === -1 && window.matchMedia('(prefers-color-scheme: dark)').matches)
    ) {
        document.documentElement.className += ' dark-mode';
    }
} catch (e) {}

