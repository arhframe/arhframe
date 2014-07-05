<?php
function getImage($image=null)
{
    if (empty($resource)) {
        throw new ArhframeException("Image name can't be empty");
    }
    import('arhframe.ImageManager');
    $img = new ImageManager($image);

    return $img;
}
