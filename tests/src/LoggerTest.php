<?php

namespace Test\Hellpers;

use PHPUnit\Framework\TestCase;
use Hellpers\Logger;
use Exception;
use DateTime;

class LoggerTest extends TestCase
{
    private $root   = '';
    private $logger = null;

    protected function setUp()
    {
        $this->root = __DIR__ . '/test';

        if (file_exists($this->root)) {
            $this->removeRoot($this->root);
        }

        mkdir($this->root);

        $this->logger = new Logger($this->root);
    }

    protected function tearDown()
    {
        $this->removeRoot($this->root);
    }

    private function removeRoot(string $path): bool
    {
        if (is_file($path)) {
            return unlink($path);
        }

        if (is_dir($path)) {
            foreach (scandir($path) as $p) {
                if ($p === '.' or $p === '..') {
                    continue;
                }

                $this->removeRoot($path . DIRECTORY_SEPARATOR . $p);
            }

            return rmdir($path); 
        }

        return false;
    }

    public function testSend()
    {
        $file  = 'test.txt';
        $text1 = 'Hello, world!';
        $text2 = 'Test string';

        $this->assertFileNotExists("{$this->root}/$file");

        $this->logger->file($file);

        // $text1 из вывода
        ob_start();
        $this->assertInstanceOf(Logger::class, $this->logger->send($text1));
        $content = ob_get_clean();
        $this->assertContains($text1, $content);

        // Наличие файла
        $this->assertFileExists("{$this->root}/$file");

        // $text1 из файла
        $content = file_get_contents("{$this->root}/$file");
        $this->assertContains($text1, $content);
        $this->assertNotContains($text2, $content);

        // $text2 из вывода
        ob_start();
        $this->assertInstanceOf(Logger::class, $this->logger->send($text2));
        $content = ob_get_clean();

        // $text1 и $text2 из файла
        $this->assertContains($text2, $content);
        $content = file_get_contents("{$this->root}/$file");
        $this->assertContains($text1, $content);
        $this->assertContains($text2, $content);
    }

    public function testCore()
    {
        $file = 'test.txt';
        $core = $this->root . DIRECTORY_SEPARATOR . 'root';

        mkdir($core);

        $this->assertInstanceOf(Logger::class, $this->logger->core($core));

        $this->logger->file($file);

        $this->assertFileNotExists("$core/$file");

        ob_start();
        $this->logger->send();
        ob_end_clean();

        $this->assertFileExists("$core/$file");
    }

    public function testPath()
    {
        $path = 'my/logs';

        $this->assertInstanceOf(Logger::class, $this->logger->path($path));

        $this->logger->file('test.txt');

        $this->assertFileNotExists("{$this->root}/$path");

        ob_start();
        $this->logger->send();
        ob_end_clean();

        $this->assertFileExists("{$this->root}/$path");
    }

    public function testFile()
    {
        $file = 'test.txt';

        $this->assertInstanceOf(Logger::class, $this->logger->file($file));

        $this->assertFileNotExists("{$this->root}/$file");

        ob_start();
        $this->logger->send();
        ob_end_clean();

        $this->assertFileExists("{$this->root}/$file");
    }

    public function testConsole()
    {
        $text = 'Hello, world!';

        $this->logger->file('test.txt');

        // Без вывода
        $this->assertInstanceOf(Logger::class, $this->logger->console(false));
        ob_start();
        $this->logger->send($text);
        $content = ob_get_clean();
        $this->assertNotContains($text, $content);

        // С выводом
        $this->assertInstanceOf(Logger::class, $this->logger->console(true));
        ob_start();
        $this->logger->send($text);
        $content = ob_get_clean();
        $this->assertContains($text, $content);
    }

    public function testBefore()
    {
        $file    = 'test.txt';
        $before  = '-----' . PHP_EOL;
        $pattern = preg_quote($before);

        $this->assertInstanceOf(Logger::class, $this->logger->before($before));

        $this->logger->file($file);

        ob_start();
        $this->logger->send();
        $content = ob_get_clean();

        $this->assertRegExp("/^$pattern.*/ui", $content);

        $content = file_get_contents("{$this->root}/$file");

        $this->assertRegExp("/^$pattern.*/ui", $content);
    }

    public function testAfter()
    {
        $file    = 'test.txt';
        $after   = PHP_EOL . '-----' . PHP_EOL;
        $pattern = preg_quote($after);

        $this->assertInstanceOf(Logger::class, $this->logger->after($after));

        $this->logger->file($file);

        ob_start();
        $this->logger->send();
        $content = ob_get_clean();

        $this->assertRegExp("/.*$pattern$/ui", $content);

        $content = file_get_contents("{$this->root}/$file");

        $this->assertRegExp("/.*$pattern$/ui", $content);
    }

    public function testMail()
    {
        $logger = new Logger(__DIR__);

        $this->assertInstanceOf(Logger::class, $this->logger->mail());
        $this->assertInstanceOf(Logger::class, $this->logger->mail(false));

        $this->assertInstanceOf(
            Logger::class, $this->logger->mail('test@mail.ru')
        );
    }

    public function testFrom()
    {
        $this->assertInstanceOf(Logger::class, $this->logger->from());
        $this->assertInstanceOf(Logger::class, $this->logger->from(''));

        $this->assertInstanceOf(Logger::class, $this->logger->from(
            'Имя сайта <127.0.0.1>'
        ));
    }

    public function testSubject()
    {
        $this->assertInstanceOf(Logger::class, $this->logger->subject());
        $this->assertInstanceOf(Logger::class, $this->logger->subject(''));

        $this->assertInstanceOf(
            Logger::class, $this->logger->subject('Ошибки')
        );
    }

    public function testDate()
    {
        $file = 'test.txt';
        $date = (new DateTime())->format(PHP_EOL . 'm.Y' . PHP_EOL);

        $this->assertInstanceOf(Logger::class, $this->logger->date('m.Y'));

        $this->logger->file($file);

        ob_start();
        $this->logger->send();
        $content = ob_get_clean();

        $this->assertContains($date, $content);

        $content = file_get_contents("{$this->root}/$file");

        $this->assertContains($date, $content);
    }

    public function testTimezone()
    {
        $file  = 'test.txt';

        $date = (new DateTime())->modify('-3 hours')
            ->format(PHP_EOL . 'H - m.Y' . PHP_EOL);

        $this->assertInstanceOf(Logger::class, $this->logger->timezone(-3));

        $this->logger->date('H - m.Y')->file($file);

        ob_start();
        $this->logger->send();
        $content = ob_get_clean();

        $this->assertContains($date, $content);

        $content = file_get_contents("{$this->root}/$file");

        $this->assertContains($date, $content);
    }

    public function testDelete()
    {
        $file1 = 'test1.txt';
        $file2 = 'test2.log';
        $file3 = 'test3.txt';

        // Все файлы
        $this->assertInstanceOf(Logger::class, $this->logger->delete(2));

        $this->assertFileNotExists("{$this->root}/$file1");
        $this->assertFileNotExists("{$this->root}/$file2");
        $this->assertFileNotExists("{$this->root}/$file3");

        $this->logger->file($file1);
        ob_start();
        $this->logger->send();
        ob_end_clean();
        $this->assertFileExists("{$this->root}/$file1");

        $this->logger->file($file2);
        ob_start();
        $this->logger->send();
        ob_end_clean();
        $this->assertFileExists("{$this->root}/$file2");

        sleep(3);

        $this->logger->file($file3);
        ob_start();
        $this->logger->send();
        ob_end_clean();
        $this->assertFileExists("{$this->root}/$file3");

        $this->assertFileNotExists("{$this->root}/$file1");
        $this->assertFileNotExists("{$this->root}/$file2");

        // Только .log
        $this->assertInstanceOf(Logger::class, $this->logger->delete(2, 'log'));

        $this->assertFileNotExists("{$this->root}/$file1");
        $this->assertFileNotExists("{$this->root}/$file2");
        $this->assertFileExists("{$this->root}/$file3");

        $this->logger->file($file1);
        ob_start();
        $this->logger->send();
        ob_end_clean();
        $this->assertFileExists("{$this->root}/$file1");

        $this->logger->file($file2);
        ob_start();
        $this->logger->send();
        ob_end_clean();
        $this->assertFileExists("{$this->root}/$file2");

        sleep(3);

        $this->logger->file($file3);
        ob_start();
        $this->logger->send();
        ob_end_clean();
        $this->assertFileExists("{$this->root}/$file3");

        $this->assertFileExists("{$this->root}/$file1");
        $this->assertFileNotExists("{$this->root}/$file2");
    }

    public function testD()
    {
        $name    = (new DateTime)->format('d-m-Y');
        $pattern = Logger::d('d-m-Y');

        $this->assertIsString($pattern);
        $this->assertContains('d-m-Y', $pattern);

        $this->assertFileNotExists("{$this->root}/path/$name/to");
        $this->assertFileNotExists("{$this->root}/path/$name/to/$name.txt");

        ob_start();
        $this->logger->path("path/$pattern/to")->file("$pattern.txt")->send();
        ob_end_clean();

        $this->assertFileExists("{$this->root}/path/$name/to/$name.txt");
    }
}
