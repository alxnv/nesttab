<?php

/**
 * Класс со вспомогательными функциями работы с файлами
 */

namespace Alxnv\Nesttab\core;

class FileHelper {
    /**
     * Попытка записать в заданный файл
     * @param string $s - имя файла, в который пытаемся записать
     * @param string $file - имя файла, который пытаемся записать
     * @return boolean
     */
    public static function writeToFile(string $s, string $file) {
        try {
            $fp = fopen($s, 'xb'); // открываем для чтения и записи, указатель
              // помещается на начало файла
        } catch (\Exception $ex) {
            $fp = false;
        }
        if ($fp === false) return false;
        $b = static::writeFileToHandle($fp, $file, $suggest_delete);
        fclose($fp);
        if ($suggest_delete) {
            @unlink($s);
        }
        return $b;
    }
    
    /**
     * Скопировать данные из файла $file в файл, на который указывает $fp (handle)
     * @param type $fp - хэндлер файла в который записываем
     * @param string $file - имя файла, из которого записываем
     * @param int $suggest_delete - эта переменная устанавливается в 1 если нужно 
     *   будет после вызова этого метода удалить файл $fp
     * @return boolean - удалось ли записать файл
     */
    public static function writeFileToHandle($fp, string $file, &$suggest_delete = 0) {
        try {
            $fps = fopen($file, 'rb'); // открываем для чтения и записи, указатель
              // помещается на начало файла
        } catch (\Exception $ex) {
            $fps = false;
        }
        if ($fps === false) {
            $suggest_delete = 1;
            return false;
        }
        do {
            $s = fread($fps, 65536);
            if ($s <> '') {
                if (!fwrite($fp, $s)) return false;
            }
        } while ($s <> '');
        return true;
    }
    
    
    
    /**
     * Создать новый файл и записать в него строку
     * @param string $file - путь к файлу для создания
     * @param string $contents - строку, которую записываем в файл
     */
    public static function createNewFileAndWriteString(string $file, string $contents) {
        try {
            $fp = fopen($file, 'x');
        } catch (\Exception $ex) {
            $fp = false;
        }
        if (!$fp) return false;
        if (flock($fp, LOCK_EX)) { // exclusive lock
            if (fwrite($fp, $contents) !== false) {
                flock($fp, LOCK_UN);    // release the lock
                fclose($fp);
                return true;
            } else {
                flock($fp, LOCK_UN);    // release the lock
                fclose($fp);
                return false;
            }
        } else {
            fclose($fp);
            return false;
        }
        
    }
    
    /**
     * Проверить, меньше ли файлов в директории чем $n
     * @param string $dir - директория
     * @param int $n
     * @return int - количество файлов
     */
    public static function numberOfFilesInDirLessThen(string $dir, int $n) {
        $k = 0;
        foreach (new \DirectoryIterator($dir) as $fileInfo) {
            $k++;
            if ($k >= $n) return false;
        }        
        return true;
    }
    
    /**
     * !!! не используется, блокировка делается самой файловой системой
     * Открывает файл, блокирует для чтения, и читает
     *  пытается читать файл в течение $ms миллисекунд
     * @param string $file - путь к файлу
     * @return string:bool - false в случае неудачи, или строку - содержимое файла
     */
    public static function readLockedWaiting(string $file, int $ms) {
        $t = microtime(true);
        while (microtime(true) - $t < ($ms/1000)) {
            $contents = static::readLocked($file);
            if ($contents === false) usleep(10000); // сон 0.01 секунды
        }
        return $contents;
    }
    /**
     * Открывает файл, блокирует для чтения, и читает
     * @param string $file - путь к файлу
     * @return string:bool - false в случае неудачи, или строку - содержимое файла
     */
    public static function readLocked(string $file) {
        try {
            $fp = fopen($file, 'r');
        } catch (\Exception $ex) {
            $fp = false;
        }
        if (!$fp) return false;
        if (flock($fp, LOCK_SH)) { // lock for reading
            $contents = fread($fp, filesize($file));
            flock($fp, LOCK_UN);    // release the lock
            fclose($fp);
            return $contents;
        } else {
            fclose($fp);
            return false;
        }
    }
}