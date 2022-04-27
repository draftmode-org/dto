<?php
namespace Terrazza\Component\Dto\Tests\_Mocks;
use DateTime;
use Psr\Log\LoggerInterface;
use Throwable;

class LoggerMock implements LoggerInterface {
    private ?string $stream;
    private bool $firstLine=true;

    public static function get($stream=null) : LoggerInterface {
        if (is_string($stream)) {
            return new self($stream);
        } elseif (is_bool($stream)) {
            return new self($stream ? "php://stdout" : null);
        } else {
            return new self();
        }
    }

    public function __construct(?string $stream=null) {
        $this->stream = $stream;
    }

    private function addMessage($message, array $context=[]) : void {
        if ($this->stream !== null) {
            $messages                               = [];
            $text                                   = [];
            $text[]                                 = (new DateTime())->format("Y-m-d H:i:s.u")." ".$message;
            $cKey                                   = "line";
            if (is_array($context) && array_key_exists($cKey, $context)) {
                $text[]                             = "[#".$context[$cKey]."]";
                unset($context[$cKey]);
            }
            $cKey                                   = "method";
            if (is_array($context) && array_key_exists($cKey, $context)) {
                $text[]                             = $context[$cKey];
                unset($context[$cKey]);
            }
            $exception                              = null;
            $cKey                                   = "exception";
            if (is_array($context) && array_key_exists($cKey, $context)) {
                /** @var Throwable $exception */
                $exception                          = $context[$cKey];
                unset($context[$cKey]);
            }
            $messages[]                             = join(" ", $text);
            if ($context && count($context)) {
                $messages[]                         = json_encode($context);
            }
            if ($this->firstLine) {
                file_put_contents($this->stream, PHP_EOL, FILE_APPEND);
                $this->firstLine                    = false;
            }
            if ($exception) {
                $messages[]                         = $exception->getTraceAsString();
            }
            file_put_contents($this->stream, join(PHP_EOL, $messages).PHP_EOL, FILE_APPEND);
        }
    }

    public function emergency($message, array $context = array()) { $this->addMessage($message, $context);}
    public function alert($message, array $context = array()) { $this->addMessage($message, $context);}
    public function critical($message, array $context = array()) { $this->addMessage($message, $context);}
    public function error($message, array $context = array()) { $this->addMessage($message, $context);}
    public function warning($message, array $context = array()) { $this->addMessage($message, $context);}
    public function notice($message, array $context = array()) { $this->addMessage($message, $context);}
    public function info($message, array $context = array()) { $this->addMessage($message, $context);}
    public function debug($message, array $context = array()) { $this->addMessage($message, $context);}
    public function log($level, $message, array $context = array()) { $this->addMessage($message, $context);}
}