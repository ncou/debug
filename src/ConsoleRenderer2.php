<?php

declare(strict_types=1);

namespace Chiron\Debug;

use Chiron\Debug\Formatter\FormatterInterface;
use Chiron\Debug\Formatter\PlainTextFormatter;
use Chiron\Http\Exception\HttpException;
//use Chiron\Http\Psr\Response;
use Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

final class ConsoleRenderer2
{
    public function render(Throwable $exception): void
    {
        $output = new ConsoleOutput(ConsoleOutput::VERBOSITY_VERY_VERBOSE); //new StreamOutput(fopen('php://stderr', 'w'));

        $console = new \Symfony\Component\Console\Application();

        $console->renderThrowable($exception, $output);
    }
}

