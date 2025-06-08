<?php
// Footer with Error Messages
?>
<div class="container fixed-bottom mb-4">
    <?php if (isset($_SESSION['error']) && $_SESSION['error']): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo htmlspecialchars($_SESSION['error']); 
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'config/version.php'; ?>
<footer class="floating-footer">
    <div class="container text-center">
        <span>© 2025 - Timetable Generator <?php echo $version; ?> by Christian Gentilini - All rights reserved</span>
        <span class="mx-2">|</span>
        <a href="privacy-policy.php" class="text-decoration-none">Privacy Policy</a>
        <span class="mx-2">|</span>
        <a href="cookie-policy.php" class="text-decoration-none">Cookie Policy</a>
        <span class="mx-2">|</span>
        <a href="terms.php" class="text-decoration-none">Termini e Condizioni</a>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
