<?php declare(strict_types=1);

namespace Souplette\Chicot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ListModulesCommand extends Command
{
    #[\Override]
    protected function configure(): void
    {
        $this
            ->setDescription('Lists installed extension modules.')
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach (get_loaded_extensions() as $name) {
            $output->writeln($name);
        }
        return Command::SUCCESS;
    }
}
