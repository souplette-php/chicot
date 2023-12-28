<?php declare(strict_types=1);

namespace Souplette\Chicot\Command;

use ReflectionExtension;
use Souplette\Chicot\StubsGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateStubsCommand extends Command
{
    #[\Override]
    protected function configure(): void
    {
        $this
            ->setDescription('Generates IDE stubs for an extension module.')
            ->addArgument('module', InputArgument::REQUIRED, 'The name of the module')
            ->addArgument('output-file', InputArgument::OPTIONAL, 'The output file path. If absent, stubs are written to stdout.')
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stderr = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $logger = new ConsoleLogger($stderr);
        $ext = new ReflectionExtension($input->getArgument('module'));
        $code = StubsGenerator::generate($ext, $logger);
        if ($outputPath = $input->getArgument('output-file')) {
            $file = new \SplFileObject($outputPath, 'w');
            $file->fwrite($code);
        } else {
            $output->write($code);
        }
        return Command::SUCCESS;
    }
}
