<?php
/**
 * ORGA Server
 * @link https://github.com/marcowuelser/orga_server
 * @copyright (c) 2016 @author Marco Wuelser @license MIT (see /LICENSE)
 *
 * Unit test for the PlayerRoleFlag class.
 */

/**
 * Test case.
 */
class PlayerRoleFlagTest extends PHPUnit_Framework_TestCase
{
    public function testValues()
    {
        // Arrange

        // Act

        // Assert
        $this->assertEquals(1, PlayerRoleFlag::RolePlayer);
        $this->assertEquals(2, PlayerRoleFlag::RoleExtra);
        $this->assertEquals(4, PlayerRoleFlag::RoleDM);
    }

    public function testToString()
    {
        // Arrange
        $sut = new PlayerRoleFlag();

        // Act

        // Assert
        $this->assertEquals("Observer", $sut::toString(0));

        $this->assertEquals("Player", $sut::toString(PlayerRoleFlag::RolePlayer));
        $this->assertEquals("Player", $sut::toString(1));

        $this->assertEquals("Extra", $sut::toString(PlayerRoleFlag::RoleExtra));
        $this->assertEquals("Extra", $sut::toString(2));

        $this->assertEquals("DM", $sut::toString(PlayerRoleFlag::RoleDM));
        $this->assertEquals("DM", $sut::toString(4));

        $this->assertEquals("Player, Extra", $sut::toString(3));
        $this->assertEquals("Player, DM", $sut::toString(5));

        $this->assertEquals("Unknown", $sut::toString(-1));
    }

    public function testList()
    {
        // Arrange
        $sut = new PlayerRoleFlag();

        // Act
        $list = $sut::toList();

        // Assert
        $this->assertEquals(3, count($list));
        $this->assertEquals(PlayerRoleFlag::RolePlayer, $list[0]);
        $this->assertEquals(PlayerRoleFlag::RoleExtra, $list[1]);
        $this->assertEquals(PlayerRoleFlag::RoleDM, $list[2]);
    }

    public function testArray()
    {
        // Arrange
        $sut = new PlayerRoleFlag();
        $expected = array(
            PlayerRoleFlag::RolePlayer => "Player",
            PlayerRoleFlag::RoleExtra => "Extra",
            PlayerRoleFlag::RoleDM => "DM",
        );

        // Act
        $array = $sut::toArray();

        // Assert
        $this->assertEquals(3, count($array));
        $this->assertEquals($expected, $array);
    }

    public function testAssocArray()
    {
        // Arrange
        $sut = new PlayerRoleFlag();
        $expectedPlayer = array(
            "id" => PlayerRoleFlag::RolePlayer,
            "name" => "Player");
        $expectedExtra = array(
            "id" => PlayerRoleFlag::RoleExtra,
            "name" => "Extra");
        $expectedDM = array(
            "id" => PlayerRoleFlag::RoleDM,
            "name" => "DM");

        // Act
        $array = $sut::toAssocArray();

        // Assert
        $this->assertEquals(3, count($array));
        $this->assertEquals($array[0], $expectedPlayer);
        $this->assertEquals($array[1], $expectedExtra);
        $this->assertEquals($array[2], $expectedDM);
    }

    public function testCheckFlag()
    {
        // Arrange
        $sut = new PlayerRoleFlag();
        $all =
            PlayerRoleFlag::RolePlayer |
            PlayerRoleFlag::RoleExtra |
            PlayerRoleFlag::RoleDM;
        $none = 0;
        $dm = PlayerRoleFlag::RoleDM;

        // Assert
        $this->assertEquals(true, $sut::checkFlag($all, PlayerRoleFlag::RolePlayer));
        $this->assertEquals(true, $sut::checkFlag($all, PlayerRoleFlag::RoleExtra));
        $this->assertEquals(true, $sut::checkFlag($all, PlayerRoleFlag::RoleDM));

        $this->assertEquals(false, $sut::checkFlag($none, PlayerRoleFlag::RolePlayer));
        $this->assertEquals(false, $sut::checkFlag($none, PlayerRoleFlag::RoleExtra));
        $this->assertEquals(false, $sut::checkFlag($none, PlayerRoleFlag::RoleDM));

        $this->assertEquals(false, $sut::checkFlag($dm, PlayerRoleFlag::RolePlayer));
        $this->assertEquals(false, $sut::checkFlag($dm, PlayerRoleFlag::RoleExtra));
        $this->assertEquals(true, $sut::checkFlag($dm, PlayerRoleFlag::RoleDM));
    }

    public function testSetFlag()
    {
        // Arrange
        $sut = new PlayerRoleFlag();
        $all =
            PlayerRoleFlag::RolePlayer |
            PlayerRoleFlag::RoleExtra |
            PlayerRoleFlag::RoleDM;
        $none = 0;
        $player = PlayerRoleFlag::RolePlayer;
        $playerExtra = PlayerRoleFlag::RoleExtra | PlayerRoleFlag::RolePlayer;

        // Assert
        $this->assertEquals($playerExtra, $sut::setFlag($player, PlayerRoleFlag::RoleExtra));
        $this->assertEquals($player, $sut::setFlag($none, PlayerRoleFlag::RolePlayer));
    }

    public function testClearFlag()
    {
        // Arrange
        $sut = new PlayerRoleFlag();
        $all =
            PlayerRoleFlag::RolePlayer |
            PlayerRoleFlag::RoleExtra |
            PlayerRoleFlag::RoleDM;
        $none = 0;
        $player = PlayerRoleFlag::RolePlayer;
        $playerExtra = PlayerRoleFlag::RoleExtra | PlayerRoleFlag::RolePlayer;

        // Assert
        $this->assertEquals($player, $sut::clearFlag($playerExtra, PlayerRoleFlag::RoleExtra));
        $this->assertEquals($none, $sut::clearFlag($player, PlayerRoleFlag::RolePlayer));
    }
}
