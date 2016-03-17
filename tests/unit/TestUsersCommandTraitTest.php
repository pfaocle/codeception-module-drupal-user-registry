<?php

/**
 * Test the methods in TestUsersCommandTrait
 *
 * @group cli
 */
class TestUsersCommandTraitTest extends \Codeception\TestCase\Test
{
    use \Codeception\Module\Drupal\UserRegistry\Command\TestUsersCommandTrait;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Test getTestUsers() throws the expected exception when given an invalid configuration.
     *
     * @throws \Codeception\Exception\Module
     */
    public function testGetTestUsersThrowsModuleExceptionWithInvalidConfig()
    {
        $this->setExpectedException('\Codeception\Exception\Module');
        $this->getTestUsers("unit", array("invalid" => "config"));
    }
}
