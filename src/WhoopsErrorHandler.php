<?php

declare(strict_types=1);

namespace Chiron\Debug;

use Chiron\Console\Console;
use Chiron\Debug\Exception\FatalErrorException;
use ErrorException;
use Exception;
use Symfony\Component\Console\Output\StreamOutput;
use Throwable;
use Whoops\Run as Whoops;
use Whoops\Handler\HandlerInterface;
use Whoops\Handler\PrettyPageHandler as WhoopsPageHandler;

use Whoops\Handler\PlainTextHandler as WhoopsConsoleHandler;
//use NunoMaduro\Collision\Handler as WhoopsConsoleHandler;

final class WhoopsErrorHandler
{

    /**
     * Register this error handler.
     */
    // TODO : améliorer le disable errors :   https://github.com/nette/tracy/blob/5e900c8c9aee84b3dbe6b5f2650ade578cc2dcfa/src/Tracy/Debugger/Debugger.php#L181
    // https://github.com/nette/tester/blob/bb813b55a9c358ead2897e37d90e29da1644ce41/src/Framework/Environment.php#L100
    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Unregisters this error handler by restoring the PHP error and exception handlers.
     *
     * @since 2.0.32 this will not do anything if the error handler was not registered
     */
    //https://github.com/yiisoft/yii2-framework/blob/master/base/ErrorHandler.php#L85
    /*
    public function unregister(): void
    {
        if ($this->_registered) {
            restore_error_handler();
            restore_exception_handler();
            $this->_registered = false;
        }
    }*/

    /**
     * Unregisters this error handler by restoring the PHP error and exception handlers.
     */
    //https://github.com/yiisoft/yii-web/blob/master/src/ErrorHandler/ErrorHandler.php#L114
    /*
    public function unregister(): void
    {
        restore_error_handler();
        restore_exception_handler();
    }*/

    /**
     * Convert PHP errors to ErrorException instances.
     *
     * @param int    $level
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @throws \ErrorException
     */
    public static function handleError(int $level, string $message, string $file = '', int $line = 0): void
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }


    /**
     * Renders the given exception.
     *
     * As this method is mainly called during boot where nothing is yet available,
     * the output is always either CLI or HTML depending where PHP runs.
     *
     * @param Throwable $exception
     */
    //https://github.com/slashtrace/slashtrace/blob/6509c3b9e67606dc25510d3f28de431f9cdadc97/src/EventHandler/DebugHandler.php#L71
    public static function handleException(Throwable $exception): void
    {
        // TODO : tester avec roaddunner voir ce que ca donne, car cela simule une console.
        // TODO : il faudrait pas aussi tester :    in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)     https://github.com/symfony/error-handler/blob/master/ErrorHandler.php#L703
        // TODO : utiliser isset($_SERVER["REQUEST_URI"]) pour détecter si on est sur un mode "http" ????   https://github.com/filp/whoops/blob/master/src/Whoops/Util/Misc.php#L23


        $whoops = new Whoops();
        // Select the Console or Web error handler.
        $whoops->pushHandler(self::getHandler());
        // There is an 'exit(1)' done in the 'handleException' function.
        $whoops->handleException($exception);
    }

    private static function getHandler(): HandlerInterface
    {
        if (PHP_SAPI === 'cli') {
            return new WhoopsConsoleHandler();
        } else {
            return new WhoopsPageHandler();
        }
    }

    /**
     * Handle php shutdown and search for fatal errors.
     *
     *
     * @throws FatalErrorException
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error && self::isLevelFatal($error['type'])) {
            $exception = new FatalErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );

            static::handleException($exception);
        }
    }

    /**
     * Determine if the error type is fatal (halts execution).
     *
     * @see https://www.php.net/manual/en/function.set-error-handler.php
     *
     * @param int $level
     *
     * @return bool
     */
    //https://github.com/slashtrace/slashtrace/blob/master/src/Level.php#L16
    private static function isLevelFatal(int $level): bool
    {
        $errors = E_ERROR;
        $errors |= E_PARSE;
        $errors |= E_CORE_ERROR;
        $errors |= E_CORE_WARNING;
        $errors |= E_COMPILE_ERROR;
        $errors |= E_COMPILE_WARNING;

        return ($level & $errors) > 0;
    }
}
