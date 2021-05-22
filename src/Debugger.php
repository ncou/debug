<?php

declare(strict_types=1);

namespace Chiron\Debug;

use Chiron\Console\Console;
use Chiron\Debug\Exception\FatalErrorException;
use ErrorException;
use Exception;
use Symfony\Component\Console\Output\StreamOutput;
use Throwable;

//https://github.com/symfony/error-handler/blob/5.x/Debug.php#L21

// TODO : renommer la classe en "Debugger::class" !!!!

/**
 * Registers the debug tools.
 */
final class Debugger
{
    /**
     * Set the level to show all errors + disable internal php error display and register the error/exception/shutdown handlers.
     */
    public static function enable(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'Off');
        ini_set('html_errors', 'Off');
        // TODO : voir si on doit aussi utiliser ces 2 setters !!!!
        //ini_set('log_errors', '0');
        //ini_set('zend.exception_ignore_args', '0');

        WhoopsErrorHandler::register();
    }
}
