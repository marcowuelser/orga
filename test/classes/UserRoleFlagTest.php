<?php
/**
 * ORGA Server
 * @link https://github.com/marcowuelser/orga_server
 * @copyright (c) 2016 @author Marco Wuelser @license MIT (see /LICENSE)
 *
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
        $this->assertEquals(1, UserRoleFlag::RoleUser);
        $this->assertEquals(2, UserRoleFlag::RoleOrganisator);
        $this->assertEquals(4, UserRoleFlag::RoleAuthor);
        $this->assertEquals(8, UserRoleFlag::RoleAdmin);
    }

    public function testToString()
    {
        // Arrange
        $sut = new UserRoleFlag();

        // Act

        // Assert
        $this->assertEquals("Guest", $sut::toString(0));

        $this->assertEquals("User", $sut::toString(UserRoleFlag::RoleUser));
        $this->assertEquals("User", $sut::toString(1));

        $this->assertEquals("Organisator", $sut::toString(UserRoleFlag::RoleOrganisator));
        $this->assertEquals("Organisator", $sut::toString(2));

        $this->assertEquals("Author", $sut::toString(UserRoleFlag::RoleAuthor));
        $this->assertEquals("Author", $sut::toString(4));

        $this->assertEquals("Admin", $sut::toString(UserRoleFlag::RoleAdmin));
        $this->assertEquals("Admin", $sut::toString(8));

        $this->assertEquals("User, Organisator", $sut::toString(3));
        $this->assertEquals("Organisator, Author", $sut::toString(6));
        $this->assertEquals("User, Admin", $sut::toString(9));
        $this->assertEquals("User, Organisator, Author, Admin", $sut::toString(15));

        $this->assertEquals("Unknown", $sut::toString(-1));
    }

    public function testList()
    {
        // Arrange
        $sut = new UserRoleFlag();

        // Act
        $list = $sut::toList();

        // Assert
        $this->assertEquals(4, count($list));
        $this->assertEquals(UserRoleFlag::RoleUser, $list[0]);
        $this->assertEquals(UserRoleFlag::RoleOrganisator, $list[1]);
        $this->assertEquals(UserRoleFlag::RoleAuthor, $list[2]);
        $this->assertEquals(UserRoleFlag::RoleAdmin, $list[3]);
    }

    public function testArray()
    {
        // Arrange
        $sut = new UserRoleFlag();
        $expected = array(
            UserRoleFlag::RoleUser => "User",
            UserRoleFlag::RoleOrganisator => "Organisator",
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
        $expectedUser = array(
            "id" => UserRoleFlag::RoleUser,
            "name" => "User");
        $expectedCreator = array(
            "id" => UserRoleFlag::RoleOrganisator,
            "name" => "Organisator");
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
        $this->assertEquals($array[0], $expectedUser);
        $this->assertEquals($array[1], $expectedCreator);
        $this->assertEquals($array[2], $expectedAuthor);
        $this->assertEquals($array[3], $expectedAdmin);
    }

    public function testCheckFlag()
    {
        // Arrange
        $sut = new UserRoleFlag();
        $all =
            UserRoleFlag::RoleUser |
            UserRoleFlag::RoleOrganisator |
            UserRoleFlag::RoleAuthor |
            UserRoleFlag::RoleAdmin;
        $none = 0;
        $admin = UserRoleFlag::RoleAdmin;

        // Assert
        $this->assertEquals(true, $sut::checkFlag($all, UserRoleFlag::RoleUser));
        $this->assertEquals(true, $sut::checkFlag($all, UserRoleFlag::RoleOrganisator));
        $this->assertEquals(true, $sut::checkFlag($all, UserRoleFlag::RoleAuthor));
        $this->assertEquals(true, $sut::checkFlag($all, UserRoleFlag::RoleAdmin));

        $this->assertEquals(false, $sut::checkFlag($none, UserRoleFlag::RoleUser));
        $this->assertEquals(false, $sut::checkFlag($none, UserRoleFlag::RoleOrganisator));
        $this->assertEquals(false, $sut::checkFlag($none, UserRoleFlag::RoleAuthor));
        $this->assertEquals(false, $sut::checkFlag($none, UserRoleFlag::RoleAdmin));

        $this->assertEquals(false, $sut::checkFlag($admin, UserRoleFlag::RoleUser));
        $this->assertEquals(false, $sut::checkFlag($admin, UserRoleFlag::RoleOrganisator));
        $this->assertEquals(false, $sut::checkFlag($admin, UserRoleFlag::RoleAuthor));
        $this->assertEquals(true, $sut::checkFlag($admin, UserRoleFlag::RoleAdmin));
    }

    public function testSetFlag()
    {
        // Arrange
        $sut = new UserRoleFlag();
        $all =
            UserRoleFlag::RoleUser |
            UserRoleFlag::RoleOrganisator |
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
            UserRoleFlag::RoleUser |
            UserRoleFlag::RoleOrganisator |
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
