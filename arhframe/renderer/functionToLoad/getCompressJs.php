<?php
function getCompressJs($html=null)
{
    import('arhframe.compressor.Compressor');
    $compressor = new Compressor();
    if (empty($html)) {
        return $compressor->getJsCompressFile();
    }

    return $compressor->getJsCompressHtml();
}
