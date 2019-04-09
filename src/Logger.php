<?php

namespace Hellpers;

use Exception;
use DateTime;
use DirectoryIterator;

class Logger
{
    /**
     * @var string Корень приложения
     */
    private $core;

    /**
     * @var string Путь к папке хранения логов
     */
    private $path;

    /**
     * @var string Имя файла, в который будет писаться лог
     */
    private $file;

    /**
     * @var bool Включить/отключить вывод логов в консоль
     */
    private $console;

    /**
     * @var string Строка перед сообщением лога
     */
    private $before;

    /**
     * @var string Строка после сообщения лога
     */
    private $after;

    /**
     * @var string Адрес электронной почты, для отправки лога на почту
     */
    private $mail;

    /**
     * @var string Адрес отправителя для mail()
     */
    private $from;

    /**
     * @var string Тема письма для mail()
     */
    private $subject;

    /**
     * @var string Шаблон для преобразования методом DateTime::format()
     */
    private $date;

    /**
     * @var int Псевдо временная зона
     */
    private $timezone;

    /**
     * @var int|null Секунды для определения устаревших файлов
     */
    private $seconds;

    /**
     * @var string|null Расширение для удаления устаревших файлов
     */
    private $extension;

    /**
     * Инициализация
     * 
     * @param string $core Корень приложения (абсолютный путь)
     */
    public function __construct(string $core)
    {
        $this->core($core);
        $this->console(true);
        $this->path('');
        $this->file('');
        $this->before(str_repeat('=', 80) . PHP_EOL);
        $this->after(PHP_EOL . PHP_EOL);
        $this->mail('');
        $this->from('');
        $this->subject('');
        $this->date('[H:i:s.u - d.m.Y]' . PHP_EOL);
        $this->timezone(0);
        $this->delete(null, null);

        unset($core);
    }

    /**
     * Позволяет изменить корень приложения
     * 
     * @param string $path Корень приложения (абсолютный путь)
     * @return self Модифицированный текущий объект
     * @throws Exception
     */
    public function core(string $path): self
    {
        if (!$path or !$this->core = realpath($path)) {
            throw new Exception('Некорректный корень приложения');
        }

        $this->core = Pather::rstrim($this->core);

        unset($path);

        return $this;
    }

    /**
     * Устанавливает путь к папке хранения логов
     * 
     * @param string $path (optional) Путь относительно корня приложения
     * @return self Модифицированный текущий объект
     */
    public function path(string $path = ''): self
    {
        $this->path = Pather::rstrim($path);

        unset($path);

        return $this;
    }

    /**
     * Позволяет задать имя файла, в который будет писаться лог
     * 
     * @param string $name (optional) Имя файла
     * @return self Модифицированный текущий объект
     */
    public function file(string $name = ''): self
    {
        $this->file = Pather::name($name);

        unset($name);

        return $this;
    }

    /**
     * Включить/отключить вывод логов в консоль
     * 
     * @param bool $flag (optional) true/false - включить/отключить
     * @return self Модифицированный текущий объект
     */
    public function console(bool $flag = true): self
    {
        $this->console = $flag;

        unset($flag);

        return $this;
    }

    /**
     * Установить обромляющую строку перед сообщением лога
     * 
     * @param string $string (optional) Строка перед сообщением лога
     * @return self Модифицированный текущий объект
     */
    public function before(string $string = ''): self
    {
        $this->before = $string;

        unset($string);

        return $this;
    }

    /**
     * Установить обромляющую строку после сообщением лога
     * 
     * @param string $string (optional) Строка после сообщением лога
     * @return self Модифицированный текущий объект
     */
    public function after(string $string = ''): self
    {
        $this->after = $string;

        unset($string);

        return $this;
    }

    /**
     * Позволяет задать адрес электронной почты, если необходимо отправлять лог
     * на почту
     * 
     * @param string $mail (optional) Электронный адрес
     * @return self Модифицированный текущий объект
     */
    public function mail(string $mail = ''): self
    {
        $this->mail = $mail;

        unset($mail);

        return $this;
    }

    /**
     * Позволяют установить адрес отправителя для метода self::mail()
     * 
     * @param string $value (optional) Адрес отправителя
     * @return self Модифицированный текущий объект
     */
    public function from(string $value = ''): self
    {
        $this->from = $value;

        unset($value);

        return $this;
    }

    /**
     * Позволяют установить тему письма для метода self::mail()
     * 
     * @param string $value (optional) Тема письма
     * @return self Модифицированный текущий объект
     */
    public function subject(string $value = ''): self
    {
        $this->subject = $value;

        unset($value);

        return $this;
    }

    /**
     * Позволяет установить необходимый формат отображения времени, когда был
     * сделан лог
     * 
     * @param string $template (optional) Строка, для преобразования стандартным
     * PHP методом DateTime::format()
     * @return self Модифицированный текущий объект
     */
    public function date(string $template = ''): self
    {
        $this->date = $template;

        unset($template);

        return $this;
    }

    /**
     * Позволяет установить псевдо временную зону. Прибавляет указанное число к
     * часам метода self::date()
     * 
     * @param int $hours (optional) Часы - целое число (как отрицательное, так и
     * положительное)
     * @return self Модифицированный текущий объект
     */
    public function timezone(int $hours = 0): self
    {
        $this->timezone = $hours;

        unset($hours);

        return $this;
    }

    /**
     * Удалять устаревшие файлы логов
     * 
     * @param int|null $seconds Количество секунд прошедших с момента последнего
     * изменения файла
     * @param string|null $extension Файлы с каким расширением следует удалять
     * @return self Модифицированный текущий объект
     */
    public function delete(?int $seconds, ?string $extension = null): self
    {
        $this->seconds   = $seconds;
        $this->extension = $extension;

        unset($seconds, $extension);

        return $this;
    }

    /**
     * Создать шаблон для преобразования методом DateTime::format()
     * 
     * @param string $string Строка содержащая спецсиволы
     * @return string Строка обернутая шаблоном для декодирования
     */
    public static function d(string $string): string
    {
        return Structurer::d($string);
    }

    /**
     * Отправка лога
     * 
     * @param string $message (optional) Текст сообщения
     * @return self Модифицированный текущий объект
     */
    public function send(string $message = ''): self
    {
        $date       = null;
        $path       = '';
        $structurer = null;
        $directory  = null;
        $file       = null;

        $date = new DateTime();
        $date->modify("{$this->timezone} hours");
        $date = $date->format($this->date);

        $message = "{$this->before}$date$message{$this->after}";

        if ($this->console) {
            echo $message;
        }

        if ($this->file) {
            $path = Structurer::make("{$this->core}/{$this->path}");

            if (is_numeric($this->seconds)) {
                $directory = new DirectoryIterator($path);

                foreach ($directory as $file) {
                    if (
                        $file->isFile()
                        and (time() - $file->getCTime()) > $this->seconds
                    ) {
                        if (
                            !is_string($this->extension)
                            or $file->getExtension() === $this->extension
                        ) {
                            if (!unlink($file->getPathname())) {
                                throw new Exception(
                                    "Не удалось удалить файл: $file"
                                );
                            }
                        }
                    }
                }
            }

            $structurer = new Structurer($path);
            $structurer->file($this->file)->content($message, true);

            unset($path, $structurer);
        }

        if ($this->mail) {
            mail(
                $this->mail,
                "=?utf-8?B?" . base64_encode($this->subject) . "?=",
                $message,
                "Content-type: text/plain; charset=utf-8\r\n"
                    . "MIME-Version: 1.0\r\n"
                    . "From: {$this->from}\r\n"
            );
        }

        unset($message, $date, $directory, $file);

        return $this;
    }
}
