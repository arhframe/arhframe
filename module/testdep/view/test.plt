<!DOCTYPE html>
<html lang="en">


<body>

<div id="content">
    <?=$this->content?>
</div>

<?php if (isset($this->sidebar)): ?>
    <div id="sidebar">
        <?=$this->sidebar?>
    </div>
<?php endif ?>

</body>
</html>