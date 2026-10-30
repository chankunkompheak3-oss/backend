    <footer class="text-center text-secondary py-3">
        <small>&copy; <?= date('Y') ?> Car Rental Management System. All rights reserved.</small>
    </footer>
</main>
<div id="toastBox" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($needCharts)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<?php endif; ?>
<script src="<?= BASE_URL ?>/js/api.js"></script>
<?php if (!empty($needCrud)): ?><script src="<?= BASE_URL ?>/js/crud.js"></script><?php endif; ?>
<?php foreach (($scripts ?? []) as $s): ?>
<script src="<?= BASE_URL ?>/js/<?= $e($s) ?>"></script>
<?php endforeach; ?>
</body>
</html>