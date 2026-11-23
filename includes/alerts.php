<?php
/**
 * CHÉRIE — Flash Messages Display Component
 */
$flashMessages = getFlashMessages();
if (!empty($flashMessages)): ?>
    <div class="container" style="margin-top: 20px;">
        <?php foreach ($flashMessages as $flash): 
            $alertClass = match($flash['type']) {
                'success' => 'alert-success',
                'danger', 'error' => 'alert-danger',
                'warning' => 'alert-warning',
                default => 'alert-info'
            };
        ?>
            <div class="alert <?= $alertClass ?>">
                <span><?= htmlspecialchars($flash['message']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
