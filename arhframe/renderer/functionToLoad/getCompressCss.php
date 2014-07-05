<?php
function getCompressCss($html=null)
{
    import('arhframe.compressor.Compressor');
    $compressor = new Compressor();
    if (empty($html)) {
        return $compressor->getCssCompressFile();
    }

    return $compressor->getCssCompressHtml();
}
