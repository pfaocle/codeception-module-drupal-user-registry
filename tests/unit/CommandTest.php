<?php

use Symfony\Component\Console\Application;
use Codeception\Module\Drupal\UserRegistry\Command\CreateTestUsersCommand;
use Codeception\Module\Drupal\UserRegistry\Command\DeleteTestUsersCommand;

/**
 * Class CommandTest
 *
 * @group cli
 */
class CommandTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    /**
     *
     */
    public function testCreateUsersCommand()
    {
        $app = new Application();
        $app->add(new CreateTestUsersCommand());
        $command = $app->find("users:create");
        $tester = new \Symfony\Component\Console\Tester\CommandTester($command);

        $tester->execute(
            array(
                "command" => $command->getName(),
                "suite" => "unit",
            )
        );

        // @todo Verify...?
    }
}
