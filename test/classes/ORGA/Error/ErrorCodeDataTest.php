<?php
/**
 * ORGA Server
 * @link https://github.com/marcowuelser/orga_server
 * @copyright (c) 2016 @author Marco Wuelser @license MIT (see /LICENSE)
 *
 * Unit test for the ErrorCodeData class.
 */

use \ORGA\Error\ErrorCodeData as ErrorCodeData;

/**
 * Test case.
 */
class ErrorCodeDataTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        // Arrange
        $data = new ErrorCodeData(1, "test", 401);

        // Act

        // Assert
        $this->assertEquals(1, $data->code);
        $this->assertEquals("test", $data->text);
        $this->assertEquals(401, $data->httpStatusCode);
    }

    public function testToData()
    {
        // Arrange
        $expected = array(
            "code" => 2,
            "http_status_code" => 404,
            "error" => "test error",
            "description" => "details"
        );
        $data = new ErrorCodeData(2, "test error", 404);

        // Act
        $responseData = $data->toResponseData("details");

        // Assert
        $this->assertEquals($expected, $responseData);
    }
}
