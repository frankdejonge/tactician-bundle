<?php

namespace League\Tactician\Bundle\Tests\Security\Voter;

use League\Tactician\Bundle\Security\Voter\HandleCommandVoter;
use League\Tactician\Bundle\Tests\Fake\FakeCommand;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

/**
 * Unit test for the handle command voter
 *
 * @author Ron Rademaker
 */
class HandleCommandVoterTest extends TestCase
{
    /**
     * Tests the vote method.
     *
     * @param string $attribute
     * @param mixed $subject
     * @param array $roles
     * @param array $mapping
     * @param int $expected
     *
     * @dataProvider provideTestVoteData
     */
    public function testVote(string $attribute, $subject, array $roles, array $mapping, int $expected)
    {
        $voter = new HandleCommandVoter(new RoleHierarchy(['ROLE_ROOT' => ['ROLE_USER']]), $mapping);
        $tokenMock = Mockery::mock(TokenInterface::class);
        $tokenMock->shouldReceive('getRoles')->andReturn($roles);

        $this->assertEquals($expected, $voter->vote($tokenMock, $subject, [$attribute]));
    }

    /**
     * Gets the testdata for the vote test.
     *
     * @return array
     */
    public function provideTestVoteData()
    {
        return [
            'default access is false' => ['handle', new FakeCommand, [new Role('ROLE_ADMIN')], [], VoterInterface::ACCESS_DENIED],
            'abstain when not handling a command, but using the handle attribute' => ['handle', null, [new Role('ROLE_ADMIN')], [], VoterInterface::ACCESS_ABSTAIN],
            'abstain when not handling a command and not using the handle attribute' => ['create', null, [new Role('ROLE_ADMIN')], [], VoterInterface::ACCESS_ABSTAIN],
            'abstain when not handling a command' => ['create', new FakeCommand, [new Role('ROLE_ADMIN')], [FakeCommand::class => ['ROLE_ADMIN']], VoterInterface::ACCESS_ABSTAIN],
            'default is unrelated to roles' => ['handle', new FakeCommand, [new Role('ROLE_ADMIN')], [], VoterInterface::ACCESS_DENIED],
            'deny access if incorrect role' => ['handle', new FakeCommand, [new Role('ROLE_ADMIN')], [FakeCommand::class => ['ROLE_USER']], VoterInterface::ACCESS_DENIED],
            'grant access if the user has the configure role' => ['handle', new FakeCommand, [new Role('ROLE_USER')], [FakeCommand::class => ['ROLE_USER']], VoterInterface::ACCESS_GRANTED],
            'grant access if the user has an inherited role' => ['handle', new FakeCommand, [new Role('ROLE_ROOT')], [FakeCommand::class => ['ROLE_USER']], VoterInterface::ACCESS_GRANTED],
            'grant access if the user has one of the configure roles' => ['handle', new FakeCommand, [new Role('ROLE_USER')], [FakeCommand::class => ['ROLE_USER', 'ROLE_TWO']], VoterInterface::ACCESS_GRANTED],
            'grant access if the user has one of the configure roles, but also another role' => ['handle', new FakeCommand, [new Role('ROLE_USER', new Role('ROLE_THREE'))], [FakeCommand::class => ['ROLE_USER', 'ROLE_TWO']], VoterInterface::ACCESS_GRANTED],
            'deny access if the command is not in the mapping (i.e. a default deny access case)' => ['handle', new FakeCommand, [new Role('ROLE_USER')], ['someOtherCommand' => ['ROLE_USER']], VoterInterface::ACCESS_DENIED],
        ];
    }
}
