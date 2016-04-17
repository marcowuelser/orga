<?php
require_once("src/classes/ScopeEnum.php");
/**
 * Unit test for the ScopeEnum class.
 */

/**
 * Test case.
 */
class ScopeEnumTest extends PHPUnit_Framework_TestCase
{
    public function testValues()
    {
        // Arrange

        // Act

        // Assert
        $this->assertEquals(1, ScopeEnum::ScopeUser);
        $this->assertEquals(2, ScopeEnum::ScopeGame);
        $this->assertEquals(4, ScopeEnum::ScopeCharacter);
    }

    public function testToString()
    {
        // Arrange
        $sut = new ScopeEnum();

        // Act

        // Assert
        $this->assertEquals("User", $sut::toString(ScopeEnum::ScopeUser));
        $this->assertEquals("User", $sut::toString(1));

        $this->assertEquals("Game", $sut::toString(ScopeEnum::ScopeGame));
        $this->assertEquals("Game", $sut::toString(2));

        $this->assertEquals("Character", $sut::toString(ScopeEnum::ScopeCharacter));
        $this->assertEquals("Character", $sut::toString(4));

        $this->assertEquals("Unknown", $sut::toString(-1));
        $this->assertEquals("Unknown", $sut::toString(0));
        $this->assertEquals("Unknown", $sut::toString(3));
        $this->assertEquals("Unknown", $sut::toString(5));
        $this->assertEquals("Unknown", $sut::toString(42));
    }

    public function testList()
    {
        // Arrange
        $sut = new ScopeEnum();

        // Act
        $list = $sut::toList();

        // Assert
        $this->assertEquals(3, count($list));
        $this->assertEquals(ScopeEnum::ScopeUser, $list[0]);
        $this->assertEquals(ScopeEnum::ScopeGame, $list[1]);
        $this->assertEquals(ScopeEnum::ScopeCharacter, $list[2]);
    }

    public function testArray()
    {
        // Arrange
        $sut = new ScopeEnum();
        $expected = array(
            ScopeEnum::ScopeUser => "User",
            ScopeEnum::ScopeGame => "Game",
            ScopeEnum::ScopeCharacter => "Character",
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
        $sut = new ScopeEnum();
        $expectedUser = array(
            "id" => ScopeEnum::ScopeUser,
            "name" => "User");
        $expectedGame = array(
            "id" => ScopeEnum::ScopeGame,
            "name" => "Game");
        $expectedCharacter = array(
            "id" => ScopeEnum::ScopeCharacter,
            "name" => "Character");

        // Act
        $array = $sut::toAssocArray();

        // Assert
        $this->assertEquals(3, count($array));
        $this->assertEquals($array[0], $expectedUser);
        $this->assertEquals($array[1], $expectedGame);
        $this->assertEquals($array[2], $expectedCharacter);
    }
}
