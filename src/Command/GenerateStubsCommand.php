<?php declare(strict_types=1);

namespace Souplette\Chicot\Command;

use ReflectionExtension;
use Souplette\Chicot\StubsGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateStubsCommand extends Command
{
    #[\Override]
    protected function configure(): void
    {
        $this
            ->setDescription('Generates IDE stubs for an extension.')
            ->addArgument('extension', InputArgument::REQUIRED, 'The name of the extension')
            ->addArgument('output-file', InputArgument::OPTIONAL, 'The output file path')
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ext = new ReflectionExtension($input->getArgument('extension'));
        $code = StubsGenerator::generate($ext);
        if ($outputPath = $input->getArgument('output-file')) {
            $file = new \SplFileObject($outputPath, 'w');
            $file->fwrite($code);
        } else {
            $output->writeln($code);
        }
        return Command::SUCCESS;
    }
}
