<?php if ($errors):?>
    <?php foreach ($errors as $error): ?>
        <?php echo $error;?> <br />
    <?php endforeach; ?>
<?php endif; ?>