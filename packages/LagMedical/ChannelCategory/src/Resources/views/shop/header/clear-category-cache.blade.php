<script>
    (function () {
        Object.keys(localStorage)
            .filter((key) => key === 'categories' || key.startsWith('categories:'))
            .forEach((key) => localStorage.removeItem(key));
    })();
</script>
