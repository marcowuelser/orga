<?php
require_once("src/classes/UserRoleFlag.php");
/**
 * Unit test for the UserRoleFlag class.
 */

/**
 * Test case.
 */
class UserRoleFlagTest extends PHPUnit_Framework_TestCase
{
    public function testValues()
    {
        // Arrange

        // Act

        // Assert
        $this->assertEquals(1, UserRoleFlag::RoleGuest);
        $this->assertEquals(2, UserRoleFlag::RoleUser);
        $this->assertEquals(4, UserRoleFlag::RoleAuthor);
        $this->assertEquals(8, UserRoleFlag::RoleAdmin);
    }

    public function testToString()
    {
        // Arrange
        $sut = new UserRoleFlag();

        // Act

        // Assert
        $this->assertEquals("Guest", $sut::toString(UserRoleFlag::RoleGuest));
        $this->assertEquals("Guest", $sut::toString(1));
        $this->assertEquals("Guest", $sut::toString("1"));

        $this->assertEquals("User", $sut::toString(UserRoleFlag::RoleUser));
        $this->assertEquals("User", $sut::toString(2));
        $this->assertEquals("User", $sut::toString("2"));

        $this->assertEquals("Author", $sut::toString(UserRoleFlag::RoleAuthor));
        $this->assertEquals("Author", $sut::toString(4));
        $this->assertEquals("Author", $sut::toString("4"));

        $this->assertEquals("Admin", $sut::toString(UserRoleFlag::RoleAdmin));
        $this->assertEquals("Admin", $sut::toString(8));
        $this->assertEquals("Admin", $sut::toString("8"));

        $this->assertEquals("Unknown", $sut::toString(""));
        $this->assertEquals("Unknown", $sut::toString(null));
        $this->assertEquals("Unknown", $sut::toString(-1));
        $this->assertEquals("Unknown", $sut::toString(0));
        $this->assertEquals("Unknown", $sut::toString(3));
        $this->assertEquals("Unknown", $sut::toString(42));
        $this->assertEquals("Unknown", $sut::toString($sut));
    }

    public function testList()
    {
        // Arrange
        $sut = new UserRoleFlag();

        // Act
        $list = $sut::toList();

        // Assert
        $this->assertEquals(4, count($list));
        $this->assertEquals(UserRoleFlag::RoleGuest, $list[0]);
        $this->assertEquals(UserRoleFlag::RoleUser, $list[1]);
        $this->assertEquals(UserRoleFlag::RoleAuthor, $list[2]);
        $this->assertEquals(UserRoleFlag::RoleAdmin, $list[3]);
    }

    public function testArray()
    {
        // Arrange
        $sut = new UserRoleFlag();
        $expected = array(
            UserRoleFlag::RoleGuest => "Guest",
            UserRoleFlag::RoleUser => "User",
            UserRoleFlag::RoleAuthor => "Author",
            UserRoleFlag::RoleAdmin => "Admin",
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
        $sut = new UserRoleFlag();
        $expectedGuest = array(
            "id" => UserRoleFlag::RoleGuest,
            "name" => "Guest");
        $expectedUser = array(
            "id" => UserRoleFlag::RoleUser,
            "name" => "User");
        $expectedAuthor = array(
            "id" => UserRoleFlag::RoleAuthor,
            "name" => "Author");
        $expectedAdmin = array(
            "id" => UserRoleFlag::RoleAdmin,
            "name" => "Admin");

        // Act
        $array = $sut::toAssocArray();

        // Assert
        $this->assertEquals(4, count($array));
        $this->assertEquals($array[0], $expectedGuest);
        $this->assertEquals($array[1], $expectedUser);
        $this->assertEquals($array[2], $expectedAuthor);
        $this->assertEquals($array[3], $expectedAdmin);
    }

    public function testCheckFlag()
    {
        // Arrange
        $sut = new UserRoleFlag();
        $all =
            UserRoleFlag::RoleGuest |
            UserRoleFlag::RoleUser |
            UserRoleFlag::RoleAuthor |
            UserRoleFlag::RoleAdmin;
        $none = 0;
        $admin = UserRoleFlag::RoleAdmin;

        // Assert
        $this->assertEquals(true, $sut::checkFlag($all, UserRoleFlag::RoleGuest));
        $this->assertEquals(true, $sut::checkFlag($all, UserRoleFlag::RoleUser));
        $this->assertEquals(true, $sut::checkFlag($all, UserRoleFlag::RoleAuthor));
        $this->assertEquals(true, $sut::checkFlag($all, UserRoleFlag::RoleAdmin));

        $this->assertEquals(false, $sut::checkFlag($none, UserRoleFlag::RoleGuest));
        $this->assertEquals(false, $sut::checkFlag($none, UserRoleFlag::RoleUser));
        $this->assertEquals(false, $sut::checkFlag($none, UserRoleFlag::RoleAuthor));
        $this->assertEquals(false, $sut::checkFlag($none, UserRoleFlag::RoleAdmin));

        $this->assertEquals(false, $sut::checkFlag($admin, UserRoleFlag::RoleGuest));
        $this->assertEquals(false, $sut::checkFlag($admin, UserRoleFlag::RoleUser));
        $this->assertEquals(false, $sut::checkFlag($admin, UserRoleFlag::RoleAuthor));
        $this->assertEquals(true, $sut::checkFlag($admin, UserRoleFlag::RoleAdmin));
    }

    public function testSetFlag()
    {
        // Arrange
        $sut = new UserRoleFlag();
        $all =
            UserRoleFlag::RoleGuest |
            UserRoleFlag::RoleUser |
            UserRoleFlag::RoleAuthor |
            UserRoleFlag::RoleAdmin;
        $none = 0;
        $user = UserRoleFlag::RoleUser;
        $userAuthor = UserRoleFlag::RoleAuthor | UserRoleFlag::RoleUser;

        // Assert
        $this->assertEquals($userAuthor, $sut::setFlag($user, UserRoleFlag::RoleAuthor));
        $this->assertEquals($user, $sut::setFlag($none, UserRoleFlag::RoleUser));
    }

    public function testClearFlag()
    {
        // Arrange
        $sut = new UserRoleFlag();
        $all =
            UserRoleFlag::RoleGuest |
            UserRoleFlag::RoleUser |
            UserRoleFlag::RoleAuthor |
            UserRoleFlag::RoleAdmin;
        $none = 0;
        $user = UserRoleFlag::RoleUser;
        $userAuthor = UserRoleFlag::RoleAuthor | UserRoleFlag::RoleUser;

        // Assert
        $this->assertEquals($user, $sut::clearFlag($userAuthor, UserRoleFlag::RoleAuthor));
        $this->assertEquals($none, $sut::clearFlag($user, UserRoleFlag::RoleUser));
    }
}
