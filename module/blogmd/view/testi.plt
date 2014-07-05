<?php $this->layout('testdep::test.plt') ?>


<?php $this->start('content') ?>
    <h1>Welcome!</h1>
    <p>Hello <?=$this->e($this->name)?></p>
<?php $this->end() ?>

<?php $this->start('sidebar') ?>
    <ul>
        <li><a href="/link">Example Link</a></li>
        <li><a href="/link">Example Link</a></li>
        <li><a href="/link">Example Link</a></li>
        <li><a href="/link">Example Link</a></li>
        <li><a href="/link">Example Link</a></li>
    </ul>
<?php $this->end() ?>