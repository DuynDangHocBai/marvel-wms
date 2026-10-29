<?php
use App\Core\Flash;

$messages = Flash::all();
?>
<?php if (!empty($messages)): ?>
    <div class="flash-messages-container" style="margin-bottom: 20px;">
        <?php foreach ($messages as $type => $list): ?>
            <?php
                $alertClass = match ($type) {
                    'success' => 'alert-success',
                    'error'   => 'alert-danger',
                    'warning' => 'alert-warning',
                    default   => 'alert-info',
                };
                $icon = match ($type) {
                    'success' => 'bi-check-circle-fill',
                    'error'   => 'bi-x-circle-fill',
                    'warning' => 'bi-exclamation-triangle-fill',
                    default   => 'bi-info-circle-fill',
                };
            ?>
            <?php foreach ($list as $msg): ?>
                <div class="alert <?= $alertClass ?> alert-dismissible" style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi <?= $icon ?>"></i>
                        <span><?= htmlspecialchars($msg) ?></span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; color: inherit; font-size: 16px;">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

