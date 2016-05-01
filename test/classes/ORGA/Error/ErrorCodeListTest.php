<?php
/**
 * ORGA Server
 * @link https://github.com/marcowuelser/orga_server
 * @copyright (c) 2016 @author Marco Wuelser @license MIT (see /LICENSE)
 *
 * Unit test for the ErrorCodeList class.
 */

use \ORGA\Error\ErrorCodeList as ErrorCodeList;
use \ORGA\Error\ErrorCode as ErrorCode;
use \ORGA\Error\HTTPStatusCode as HTTPStatusCode;

/**
 * Test case.
 */
class ErrorCodeListTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        // Arrange

        // Act
        $list = new ErrorCodeList();

        // Assert
        $data = $list->getData(23); // not in list
        $this->assertEquals(ErrorCode::UNEXPECTED, $data->code);
        $this->assertEquals("Unexpected", $data->text);
        $this->assertEquals(HTTPStatusCode::INTERNAL_SERVER_ERROR, $data->httpStatusCode);
    }

    public function testGetData()
    {
        // Arrange
        $list = new ErrorCodeList();

        // Act
        $data = $list->getData(ErrorCode::INVALID_REQUEST);

        // Assert
        $this->assertEquals(ErrorCode::INVALID_REQUEST, $data->code);
        $this->assertEquals("Invalid request", $data->text);
        $this->assertEquals(HTTPStatusCode::BAD_REQUEST, $data->httpStatusCode);
    }

    public function testAdd()
    {
        // Arrange
        $list = new ErrorCodeList();

        // Act
        $list->add(42, "test message", 403);

        // Assert
        $data = $list->getData(42);
        $this->assertEquals(42, $data->code);
        $this->assertEquals("test message", $data->text);
        $this->assertEquals(403, $data->httpStatusCode);
    }
}
