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
        <a href="https://www.iubenda.com/privacy-policy/61515820" class="iubenda-white iubenda-noiframe iubenda-embed iubenda-noiframe " title="Privacy Policy ">Privacy Policy</a><script type="text/javascript">(function (w,d) {var loader = function () {var s = d.createElement("script"), tag = d.getElementsByTagName("script")[0]; s.src="https://cdn.iubenda.com/iubenda.js"; tag.parentNode.insertBefore(s,tag);}; if(w.addEventListener){w.addEventListener("load", loader, false);}else if(w.attachEvent){w.attachEvent("onload", loader);}else{w.onload = loader;}})(window, document);</script>
        <span class="mx-2">|</span>
        <a href="https://www.iubenda.com/privacy-policy/61515820/cookie-policy" class="iubenda-white iubenda-noiframe iubenda-embed iubenda-noiframe " title="Cookie Policy ">Cookie Policy</a><script type="text/javascript">(function (w,d) {var loader = function () {var s = d.createElement("script"), tag = d.getElementsByTagName("script")[0]; s.src="https://cdn.iubenda.com/iubenda.js"; tag.parentNode.insertBefore(s,tag);}; if(w.addEventListener){w.addEventListener("load", loader, false);}else if(w.attachEvent){w.attachEvent("onload", loader);}else{w.onload = loader;}})(window, document);</script>
        <span class="mx-2">|</span>
        <a href="terms.php" class="text-decoration-none">Termini e Condizioni</a>
    </div>
</footer>

<script type="text/javascript" src="//embeds.iubenda.com/widgets/632acb25-21ea-44dc-bf98-d3f4106f0873.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
