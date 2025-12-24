        </div> <!-- Close container -->
    </div> <!-- Close main-wrapper -->

    <!-- Additional page-specific scripts -->
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo htmlspecialchars($js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
