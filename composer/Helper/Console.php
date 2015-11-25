<?php

namespace Helper;

use Symfony\Component\Console as SymfontConsole;

/**
 *
 */
class Console
{

    public static function table($headers, $rows, $ifEmptyNotRender=true )
    {
        if ($ifEmptyNotRender && count($rows)<1) {
            return '';
        }

        $consoleOutput  = new SymfontConsole\Output\ConsoleOutput();
        $table          = new SymfontConsole\Helper\Table($consoleOutput);
        $table
            ->setHeaders($headers)
            ->setRows($rows)
        ;
        return $table->render();
    }

}
