</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('<?= BASE_URL ?>/service-worker.js').catch(function () {});
    });
}
</script>
<?php include __DIR__ . '/../layouts/alertas.php'; ?>
</body>
</html>