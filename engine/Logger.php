<?php

class Logger{

    public static function error($exception){
        $log = '['.date('Y-m-d H:i:s').'] ' . $exception->getMessage() . PHP_EOL;
        $log .= 'Filename: ' . $exception->getFile() . PHP_EOL;
        $log .= 'At line: ' . $exception->getLine() . PHP_EOL;
        file_put_contents('error.log', $log, FILE_APPEND | LOCK_EX);
    }

    public static function warning($exception){
        $log = '['.date('Y-m-d H:i:s').'] ' . $exception->getMessage() . PHP_EOL;
        file_put_contents('warning.log', $log, FILE_APPEND | LOCK_EX);
    }
}