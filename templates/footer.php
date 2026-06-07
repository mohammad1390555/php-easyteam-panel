<?php if (Auth::isLoggedIn()): ?>
            </div> <!-- .content-area -->
            
            <footer class="footer">
                <span><?= __('footer_text') ?></span>
                <span><?= __('footer_version') ?> <?= PANEL_VERSION ?></span>
            </footer>
        </main>
    </div> <!-- .app-container -->
<?php else: ?>
    </div> <!-- .auth-container -->
<?php endif; ?>

    <script src="assets/js/app.js?v=<?= PANEL_VERSION ?>"></script>
</body>
</html>
