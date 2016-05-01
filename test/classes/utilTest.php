<?php
/**
 * ORGA Server
 * @link https://github.com/marcowuelser/orga_server
 * @copyright (c) 2016 @author Marco Wuelser @license MIT (see /LICENSE)
 *
 * Unit test for the util methods.
 */

/**
 * Test case.
 */
class UtilTest extends PHPUnit_Framework_TestCase
{
    public function testConcatenate()
    {
        // Arrange
        $str1 = "First String";
        $str2 = "2nd String";

        // Act

        // Assert
        $this->assertEquals("", concatenate("", ""));
        $this->assertEquals("First String", concatenate("", $str1));
        $this->assertEquals("First String", concatenate($str1, ""));
        $this->assertEquals("First String, 2nd String", concatenate($str1, $str2));
    }
}
