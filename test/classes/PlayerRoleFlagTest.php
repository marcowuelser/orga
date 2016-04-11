<?php
require_once("src/classes/PlayerRoleFlag.php");
/**
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
        $this->assertEquals(1, PlayerRoleFlag::RoleObserver);
        $this->assertEquals(2, PlayerRoleFlag::RolePlayer);
        $this->assertEquals(4, PlayerRoleFlag::RoleExtra);
        $this->assertEquals(8, PlayerRoleFlag::RoleDM);
    }

    public function testToString()
    {
        // Arrange
        $sut = new PlayerRoleFlag();

        // Act

        // Assert
        $this->assertEquals("Observer", $sut::toString(PlayerRoleFlag::RoleObserver));
        $this->assertEquals("Observer", $sut::toString(1));

        $this->assertEquals("Player", $sut::toString(PlayerRoleFlag::RolePlayer));
        $this->assertEquals("Player", $sut::toString(2));

        $this->assertEquals("Extra", $sut::toString(PlayerRoleFlag::RoleExtra));
        $this->assertEquals("Extra", $sut::toString(4));

        $this->assertEquals("DM", $sut::toString(PlayerRoleFlag::RoleDM));
        $this->assertEquals("DM", $sut::toString(8));

        $this->assertEquals("Unknown", $sut::toString(-1));
        $this->assertEquals("Unknown", $sut::toString(0));
        $this->assertEquals("Unknown", $sut::toString(3));
        $this->assertEquals("Unknown", $sut::toString(42));
    }

    public function testList()
    {
        // Arrange
        $sut = new PlayerRoleFlag();

        // Act
        $list = $sut::toList();

        // Assert
        $this->assertEquals(4, count($list));
        $this->assertEquals(PlayerRoleFlag::RoleObserver, $list[0]);
        $this->assertEquals(PlayerRoleFlag::RolePlayer, $list[1]);
        $this->assertEquals(PlayerRoleFlag::RoleExtra, $list[2]);
        $this->assertEquals(PlayerRoleFlag::RoleDM, $list[3]);
    }

    public function testArray()
    {
        // Arrange
        $sut = new PlayerRoleFlag();
        $expected = array(
            PlayerRoleFlag::RoleObserver => "Observer",
            PlayerRoleFlag::RolePlayer => "Player",
            PlayerRoleFlag::RoleExtra => "Extra",
            PlayerRoleFlag::RoleDM => "DM",
        );

        // Act
        $array = $sut::toArray();

        // Assert
        $this->assertEquals(4, count($array));
        $this->assertEquals($expected, $array);
    }

    public function testAssocArray()
    {
        // Arrange
        $sut = new PlayerRoleFlag();
        $expectedObserver = array(
            "id" => PlayerRoleFlag::RoleObserver,
            "name" => "Observer");
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
        $this->assertEquals(4, count($array));
        $this->assertEquals($array[0], $expectedObserver);
        $this->assertEquals($array[1], $expectedPlayer);
        $this->assertEquals($array[2], $expectedExtra);
        $this->assertEquals($array[3], $expectedDM);
    }

    public function testCheckFlag()
    {
        // Arrange
        $sut = new PlayerRoleFlag();
        $all =
            PlayerRoleFlag::RoleObserver |
            PlayerRoleFlag::RolePlayer |
            PlayerRoleFlag::RoleExtra |
            PlayerRoleFlag::RoleDM;
        $none = 0;
        $dm = PlayerRoleFlag::RoleDM;

        // Assert
        $this->assertEquals(true, $sut::checkFlag($all, PlayerRoleFlag::RoleObserver));
        $this->assertEquals(true, $sut::checkFlag($all, PlayerRoleFlag::RolePlayer));
        $this->assertEquals(true, $sut::checkFlag($all, PlayerRoleFlag::RoleExtra));
        $this->assertEquals(true, $sut::checkFlag($all, PlayerRoleFlag::RoleDM));

        $this->assertEquals(false, $sut::checkFlag($none, PlayerRoleFlag::RoleObserver));
        $this->assertEquals(false, $sut::checkFlag($none, PlayerRoleFlag::RolePlayer));
        $this->assertEquals(false, $sut::checkFlag($none, PlayerRoleFlag::RoleExtra));
        $this->assertEquals(false, $sut::checkFlag($none, PlayerRoleFlag::RoleDM));

        $this->assertEquals(false, $sut::checkFlag($dm, PlayerRoleFlag::RoleObserver));
        $this->assertEquals(false, $sut::checkFlag($dm, PlayerRoleFlag::RolePlayer));
        $this->assertEquals(false, $sut::checkFlag($dm, PlayerRoleFlag::RoleExtra));
        $this->assertEquals(true, $sut::checkFlag($dm, PlayerRoleFlag::RoleDM));
    }

    public function testSetFlag()
    {
        // Arrange
        $sut = new PlayerRoleFlag();
        $all =
            PlayerRoleFlag::RoleObserver |
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
            PlayerRoleFlag::RoleObserver |
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
