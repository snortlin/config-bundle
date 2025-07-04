<?php
declare(strict_types=1);

namespace Snortlin\Bundle\ConfigBundle\Command;

use Snortlin\Bundle\ConfigBundle\Manager\Manager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'system:config:list', description: 'List available system configurations')]
final class ConfigListCommand extends Command
{
    public function __construct(private readonly Manager $manager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('key', 'k', InputOption::VALUE_REQUIRED, 'Configuration key')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command lists all available system configurations:

  <info>php %command.full_name%</info>

Or for a specific configuration:

  <info>php %command.full_name% --key=my_config_key</info>
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);


        if (null !== ($key = $input->getOption('key'))) {
            try {
                $configs = [$key => $this->manager->getConfigClass($key)];
            } catch (\Throwable $e) {
                $io->error($e->getMessage());

                return Command::INVALID;
            }
        } else {
            $configs = $this->manager->getConfigClasses();
            ksort($configs);
        }

        if (!empty($configs)) {
            $rows = [];

            foreach ($configs as $configKey => $configClass) {
                $rows[] = [$configKey, $configClass];
            }

            $io->title('Available registered configurations');
            $io->table(['Key', 'Class'], $rows);
        } else {
            $io->comment('No configurations found');
        }

        return Command::SUCCESS;
    }
}
