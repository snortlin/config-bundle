<?php
declare(strict_types=1);

namespace Snortlin\Bundle\ConfigBundle\Command;

use Psr\Cache\InvalidArgumentException;
use Snortlin\Bundle\ConfigBundle\Config\ConfigDefaultsInterface;
use Snortlin\Bundle\ConfigBundle\Manager\Manager;
use Snortlin\Bundle\ConfigBundle\Persister\Persister;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'system:config:set:defaults', description: 'Set the default configuration values')]
final class ConfigSetDefaultsCommand extends Command
{
    public function __construct(private readonly Manager   $manager,
                                private readonly Persister $persister)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('key', InputArgument::REQUIRED, 'Configuration key')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command set the default configuration values

  <info>php %command.full_name% <key></info>
EOF
            );
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $key = $input->getArgument('key');

        try {
            $configClass = $this->manager->getConfigClass($key);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::INVALID;
        }

        if (!is_subclass_of($configClass, ConfigDefaultsInterface::class)) {
            $io->error(sprintf('Unable to create default configuration. Config class "%s" does not implement "%s" interface.', $configClass, ConfigDefaultsInterface::class));

            return Command::INVALID;
        }

        $this->persister->set($configClass::getConfigDefaults());

        $io->success('Default configuration has been created successfully.');

        return Command::SUCCESS;
    }
}
