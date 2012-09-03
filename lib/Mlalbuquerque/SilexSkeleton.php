<?php

namespace Mlalbuquerque;

use Composer\Script\Event;

class SilexSkeleton
{

    public static function moveFiles(Event $event)
    {
        $basepath = __DIR__ . '/../../../silex-skeleton';
        $targetpath = __DIR__ . '/../../../../..';
        
        if (!rename($basepath . '/config', $targetpath . '/config')      ||
            !rename($basepath . '/config', $targetpath . '/controllers') ||
            !rename($basepath . '/config', $targetpath . '/views')       ||
            !rename($basepath . '/config', $targetpath . '/web')         ||
            !rename($basepath . '/config', $targetpath . '/README.md')   ||
            !rename($basepath . '/config', $targetpath . '/vendor')      ||
            !rename($basepath . '/config', $targetpath . '/lib'))
        {
            throw new Exception('Problema na movimentação dos arquivos');
        }
    }

}
