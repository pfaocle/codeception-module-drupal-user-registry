<?php

use \FunctionalTester;

use Symfony\Component\Console\Application;
use Codeception\Module\Drupal\UserRegistry\Command\CreateTestUsersCommand;
use Codeception\Module\Drupal\UserRegistry\Command\DeleteTestUsersCommand;
use Codeception\Util\Fixtures;

/**
 * Class CommandCest
 *
 * @group cli
 */
class CommandCest
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * Set up the console application.
     *
     * @param FunctionalTester $I
     */
    public function _before(FunctionalTester $I)
    {
        $this->app = new Application();
        $this->app->add(new CreateTestUsersCommand());
        $this->app->add(new DeleteTestUsersCommand());
    }

    /**
     * Tear down any created console application.
     *
     * @param FunctionalTester $I
     */
    public function _after(FunctionalTester $I)
    {
//        unset($this->app);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testCreateUsersCommand(FunctionalTester $I)
    {
        $command = $this->app->find("users:create");
        $tester = new \Symfony\Component\Console\Tester\CommandTester($command);

        $tester->execute(
            array(
                "command" => $command->getName(),
                "suite" => "unit",
            )
        );

        // @todo Configs are a bit muxed ip...
        $config = Fixtures::get("validModuleConfig");
        foreach ($config["users"] as $user => $userDetails) {
            $I->seeInDatabase("users", array("name" => $userDetails["name"]));
        }
    }

    /**
     * @before testCreateUsersCommand
     *
     * @param FunctionalTester $I
     */
    public function testCreateAndDeleteUsersCommand(FunctionalTester $I)
    {
        $command = $this->app->find("users:delete");
        $tester = new \Symfony\Component\Console\Tester\CommandTester($command);

        $tester->execute(
            array(
                "command" => $command->getName(),
                "suite" => "unit",
            )
        );

        // @todo Configs are a bit muxed ip...
        $config = Fixtures::get("validModuleConfig");
        foreach ($config["users"] as $user => $userDetails) {
            $I->dontSeeInDatabase("users", array("name" => $userDetails["name"]));
        }
    }
}
