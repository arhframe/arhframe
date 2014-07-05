<?php
import('arhframe.frameworkCss.LessManager');
import('arhframe.compressor.Compressor');
import('arhframe.frameworkCss.compiler.CompilerLess');

LessManager::compileAll(new CompilerLess());
Compressor::compress();
