<?php
/**
 * PHPIMS
 *
 * Copyright (c) 2011 Christer Edvartsen <cogo@starzinger.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * * The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package PHPIMS
 * @subpackage Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/phpims
 */

namespace PHPIMS\Resource\Plugin;

/**
 * @package PHPIMS
 * @subpackage Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/phpims
 */
class PrepareImageTest extends \PHPUnit_Framework_TestCase {
    private $plugin;
    private $request;
    private $response;
    private $database;
    private $storage;

    public function setUp() {
        $this->plugin   = new PrepareImage();
        $this->request  = $this->getMock('PHPIMS\Request\RequestInterface');
        $this->response = $this->getMock('PHPIMS\Response\ResponseInterface');
        $this->database = $this->getMock('PHPIMS\Database\DatabaseInterface');
        $this->storage  = $this->getMock('PHPIMS\Storage\StorageInterface');
    }

    public function tearDown() {
        $this->plugin = null;
    }

    /**
     * @expectedException PHPIMS\Resource\Plugin\Exception
     * @expectedExceptionMessage No image attached
     * @expectedExceptionCode 400
     */
    public function testExecWithMissingImageData() {
        $this->request->expects($this->once())->method('getRawData')->will($this->returnValue(''));

        $this->plugin->exec($this->request, $this->response, $this->database, $this->storage);
    }

    /**
     * @expectedException PHPIMS\Resource\Plugin\Exception
     * @expectedExceptionMessage Hash mismatch
     * @expectedExceptionCode 400
     */
    public function testExecWithHashMismatch() {
        $this->request->expects($this->once())->method('getRawData')->will($this->returnValue(file_get_contents(__FILE__)));
        $this->request->expects($this->once())->method('getImageIdentifier')->will($this->returnValue(md5(microtime()) . '.png'));

        $this->plugin->exec($this->request, $this->response, $this->database, $this->storage);
    }

    public function testSuccessfulExec() {
        $imagePath = __DIR__ . '/../../_files/image.png';
        $imageBlob = file_get_contents($imagePath);
        $imageIdentifier = md5($imageBlob) . '.png';
        $this->request->expects($this->once())->method('getRawData')->will($this->returnValue($imageBlob));
        $this->request->expects($this->once())->method('getImageIdentifier')->will($this->returnValue($imageIdentifier));

        $image = $this->getMock('PHPIMS\Image\ImageInterface');
        $image->expects($this->once())->method('setBlob')->with($imageBlob)->will($this->returnValue($image));
        $image->expects($this->once())->method('setWidth')->with(665)->will($this->returnValue($image));
        $image->expects($this->once())->method('setHeight')->with(463)->will($this->returnValue($image));

        $this->response->expects($this->once())->method('getImage')->will($this->returnValue($image));

        $this->plugin->exec($this->request, $this->response, $this->database, $this->storage);
    }
}
