<?php

namespace Codeception\Module\Drupal\UserRegistry\Command;

use Codeception\Exception\Module as ModuleException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateTestUsersCommand.
 *
 * @package Codeception\Module\DrupalUserRegistry\Command\TestUsersCommand
 */
class CreateTestUsersCommand extends Command
{
    use TestUsersCommandTrait;

    /**
     * Configuration for this command.
     */
    protected function configure()
    {
        $this
            ->setName('users:create')
            ->setDescription('Create test users.')
            ->addArgument(
                'suite',
                InputArgument::OPTIONAL,
                "Which suite configuration to use. Defaults to 'acceptance'."
            );
    }

    /**
     * Execute command: create any defined test users on the configured Drush alias.
     *
     * @todo Codeception debug calls won't work here.
     *
     * @param InputInterface $input
     *   The command input.
     *
     * @param OutputInterface $output
     *   The command output.
     *
     * @return int|null|void
     *
     * @throws ModuleException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // The suite name can be passed as an argument. Without it, the command defaults to 'acceptance'.
        $suiteName = $input->getArgument('suite');
        if (!$suiteName) {
            $suiteName = 'acceptance';
        }

        $this->getTestUsers($suiteName);
        $this->testUserManager->createUsers($this->users);
    }
}
