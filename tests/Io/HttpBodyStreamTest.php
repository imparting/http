<?php

namespace React\Tests\Http\Io;

use React\Http\Io\HttpBodyStream;
use React\Stream\ThroughStream;
use React\Tests\Http\TestCase;

class HttpBodyStreamTest extends TestCase
{
    private $input;
    private $bodyStream;

    /**
     * @before
     */
    public function setUpBodyStream()
    {
        $this->input = new ThroughStream();
        $this->bodyStream = new HttpBodyStream($this->input, null);
    }

    public function testDataEmit()
    {
        $this->bodyStream->on('data', $this->expectCallableOnce(array("hello")));
        $this->input->emit('data', array("hello"));
    }

    public function testPauseStream()
    {
        $input = $this->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();
        $input->expects($this->once())->method('pause');

        $bodyStream = new HttpBodyStream($input, null);
        $bodyStream->pause();
    }

    public function testResumeStream()
    {
        $input = $this->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();
        $input->expects($this->once())->method('resume');

        $bodyStream = new HttpBodyStream($input, null);
        $bodyStream->resume();
    }

    public function testPipeStream()
    {
        $dest = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();

        $ret = $this->bodyStream->pipe($dest);

        $this->assertSame($dest, $ret);
    }

    public function testHandleClose()
    {
        $this->bodyStream->on('close', $this->expectCallableOnce());

        $this->input->close();
        $this->input->emit('end', array());

        $this->assertFalse($this->bodyStream->isReadable());
    }

    public function testStopDataEmittingAfterClose()
    {
        $bodyStream = new HttpBodyStream($this->input, null);
        $bodyStream->on('close', $this->expectCallableOnce());
        $this->bodyStream->on('data', $this->expectCallableOnce(array("hello")));

        $this->input->emit('data', array("hello"));
        $bodyStream->close();
        $this->input->emit('data', array("world"));
    }

    public function testHandleError()
    {
        $this->bodyStream->on('error', $this->expectCallableOnce());
        $this->bodyStream->on('close', $this->expectCallableOnce());

        $this->input->emit('error', array(new \RuntimeException()));

        $this->assertFalse($this->bodyStream->isReadable());
    }

    public function testToString()
    {
        $this->assertEquals('', $this->bodyStream->__toString());
    }

    public function testDetach()
    {
        $this->assertNull($this->bodyStream->detach());
    }

    public function testGetSizeDefault()
    {
        $this->assertNull($this->bodyStream->getSize());
    }

    public function testGetSizeCustom()
    {
        $stream = new HttpBodyStream($this->input, 5);
        $this->assertEquals(5, $stream->getSize());
    }

    public function testTell()
    {
        $this->setExpectedException('BadMethodCallException');
        $this->bodyStream->tell();
    }

    public function testEof()
    {
        $this->setExpectedException('BadMethodCallException');
        $this->bodyStream->eof();
    }

    public function testIsSeekable()
    {
        $this->assertFalse($this->bodyStream->isSeekable());
    }

    public function testWrite()
    {
        $this->setExpectedException('BadMethodCallException');
        $this->bodyStream->write('');
    }

    public function testRead()
    {
        $this->setExpectedException('BadMethodCallException');
        $this->bodyStream->read('');
    }

    public function testGetContents()
    {
        $this->assertEquals('', $this->bodyStream->getContents());
    }

    public function testGetMetaData()
    {
        $this->assertNull($this->bodyStream->getMetadata());
    }

    public function testIsReadable()
    {
        $this->assertTrue($this->bodyStream->isReadable());
    }

    public function testSeek()
    {
        $this->setExpectedException('BadMethodCallException');
        $this->bodyStream->seek('');
    }

    public function testRewind()
    {
        $this->setExpectedException('BadMethodCallException');
        $this->bodyStream->rewind();
    }

    public function testIsWriteable()
    {
        $this->assertFalse($this->bodyStream->isWritable());
    }
}
