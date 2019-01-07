<?php

namespace Test\Hellpers;

use PHPUnit\Framework\TestCase;
use Hellpers\Logger;
use Exception;

class LoggerTest extends TestCase
{
    public function testCore()
    {
        $logger = new Logger(__DIR__);

        $this->assertInstanceOf(Logger::class, $logger->core(__DIR__));
    }

    public function testConsole()
    {
        $logger = new Logger(__DIR__);

        $this->assertInstanceOf(Logger::class, $logger->console());
        $this->assertInstanceOf(Logger::class, $logger->console(true));
        $this->assertInstanceOf(Logger::class, $logger->console(false));
    }

    public function testPath()
    {
        $logger = new Logger(__DIR__);

        $this->assertInstanceOf(Logger::class, $logger->path());
        $this->assertInstanceOf(Logger::class, $logger->path(''));
        $this->assertInstanceOf(Logger::class, $logger->path('/'));
        $this->assertInstanceOf(Logger::class, $logger->path('/path/to'));
        $this->assertInstanceOf(Logger::class, $logger->path('/path/to/'));
        $this->assertInstanceOf(Logger::class, $logger->path(
            '/path/to/' . Logger::d('H.i.s')
        ));
    }

    public function testFile()
    {
        $logger = new Logger(__DIR__);

        $this->assertInstanceOf(Logger::class, $logger->file('file.txt'));
        $this->assertInstanceOf(Logger::class, $logger->file('test/file.txt'));
        $this->assertInstanceOf(Logger::class, $logger->file(
            Logger::d('H.i.s')
        ));
    }

    public function testBefore()
    {
        $logger = new Logger(__DIR__);

        $this->assertInstanceOf(Logger::class, $logger->before());
        $this->assertInstanceOf(Logger::class, $logger->before(''));
        $this->assertInstanceOf(Logger::class, $logger->before('qwerty\n\r'));
        $this->assertInstanceOf(Logger::class, $logger->before(PHP_EOL));
    }

    public function testAfter()
    {
        $logger = new Logger(__DIR__);

        $this->assertInstanceOf(Logger::class, $logger->after());
        $this->assertInstanceOf(Logger::class, $logger->after(''));
        $this->assertInstanceOf(Logger::class, $logger->after('qwerty\n\r'));
        $this->assertInstanceOf(Logger::class, $logger->after(PHP_EOL));
    }

    public function testMail()
    {
        $logger = new Logger(__DIR__);

        $this->assertInstanceOf(Logger::class, $logger->mail());
        $this->assertInstanceOf(Logger::class, $logger->date(''));
        $this->assertInstanceOf(Logger::class, $logger->mail(false));
        $this->assertInstanceOf(Logger::class, $logger->mail('test@mail.ru'));
    }

    public function testFrom()
    {
        $logger = new Logger(__DIR__);

        $this->assertInstanceOf(Logger::class, $logger->from());
        $this->assertInstanceOf(Logger::class, $logger->from(''));
        $this->assertInstanceOf(Logger::class, $logger->from(
            'Имя сайта <127.0.0.1>'
        ));
    }

    public function testSubject()
    {
        $logger = new Logger(__DIR__);

        $this->assertInstanceOf(Logger::class, $logger->subject());
        $this->assertInstanceOf(Logger::class, $logger->subject(''));
        $this->assertInstanceOf(Logger::class, $logger->subject('Ошибки'));
    }

    public function testDate()
    {
        $logger = new Logger(__DIR__);

        $this->assertInstanceOf(Logger::class, $logger->date());
        $this->assertInstanceOf(Logger::class, $logger->date(''));
        $this->assertInstanceOf(Logger::class, $logger->date(false));
        $this->assertInstanceOf(Logger::class, $logger->date('H:i:s'));
    }

    public function testTimezone()
    {
        $logger = new Logger(__DIR__);

        $this->assertInstanceOf(Logger::class, $logger->timezone());
        $this->assertInstanceOf(Logger::class, $logger->timezone(0));
        $this->assertInstanceOf(Logger::class, $logger->timezone(1));
        $this->assertInstanceOf(Logger::class, $logger->timezone(-1));
    }

    public function testSend()
    {
        $logger = new Logger(__DIR__);

        $logger->before('')->after('')->date('');

        $logger->file(Logger::d('Y') . '.txt');
        $logger->path('folder');
        $path = __DIR__ . '/folder';
        $file = $path . '/' . date('Y') . '.txt';

        ob_start();
        $logger->send('TEST');
        $logger->send('TEST');
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($content, 'TESTTEST');
        $this->assertFileExists($file);

        $content = file_get_contents($file);

        $this->assertEquals($content, 'TESTTEST');

        unlink($file);

        $this->assertFileNotExists($file);

        rmdir($path);

        $this->assertFileNotExists($path);
    }

    public function testD()
    {
        $string = (string)time();

        $result = Logger::d($string);

        $this->assertIsString($result);
        $this->assertContains($string, $result);
    }
}
