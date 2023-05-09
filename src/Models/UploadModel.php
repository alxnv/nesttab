<?php

/**
 * Модель для загрузки файлов на сервер в каталог upload
 */

/**
 * Папка upload расположена в корневой папке проекта
 * 
 * Структура папки upload:
 * 
 * n подпапок с номерами 1..n, где n>=0
 * файл counter.txt - содержит чилсло n в текстовом виде
 * папка temp - в нее загружаются временные файлы
 * !!! папка upload и все ее подпапки должно стоять разрешение на запись
 * 
 * Первичная структура папки upload:
 *  сама папка и в ней пустой каталог temp
 * 
 */

namespace Alxnv\Nesttab\Models;


class UploadModel {
    
    /**
     * Копирует файл в подпапку папки upload, если нужно 
     *  создает подпапки с новыми номерами
     * @param string $filename - исходное имя загруженного временного файла
     * @param string $file - содержимое загруженного файла
     * @return string - подадрес в папке upload, в который был скопирован файл
     */
    public function copyFileToUpload(string $filename, string $file) {
        $n = intval(self::getCounterFile());
        if ($n == 0) {
            $this->uploadToNewDir($filename, $file, $dst_name);
        } else {
            $ids = [];
            $b = false;
            $tries = 0; // максимум 3 попытки для различных директорий
            do {
                $k = rand(1, $n);
                if (isset($ids[$k])) continue; // в эту директорию уже пытались записать
                $ids[$k] = 1; // попробовали записать в эту директорию
                $s = public_path() . '/upload/' .
                    $k . '/' . $filename;
                if (!\Alxnv\Nesttab\core\FileHelper::numberOfFilesInDirLessThen(public_path() .
                        '/upload/' . $k, $n + 2)) {
                    // если в директории больше файлов чем число в upload/counter.txt,
                    //  то новая попытка, туда не пишем
                    $tries++;
                    continue;
                }
                $b = \Alxnv\Nesttab\core\FileHelper::writeToFile($s, $file);
                $dst_name = $k . '/' . $filename;
                $tries++;
            } while (!$b && ($tries < 3) && (count($ids) < $n));
            if (!$b) $this->uploadToNewDir($filename, $file, $dst_name); // если так и не удалось
              //  записать
              // в существующие директории за 3 попытки, либо пока не просмотрели все
              //  существующие директории (если их меньше 4-х), 
              //  пытаемся записать в новую директорию

        }
        return $dst_name;
    }       

    /**
     * Увеличивать счетчик директорий на единицу и пытаться записать файл,
     * создавая новую директорию,
     *  пока не запишем,
     *  либо пока не увеличим 10 раз, тогдк выдается ошибка
     * @param string $filename - исходное имя загруженного временного файла
     * @param string $file - содержимое файла, который будем копировать
     * @param mixed $dst_name - сюда записывается подадрес загруженного файла
     *   в папке upload (например, "1/file.ext")
     */
    public function uploadToNewDir(string $filename, string $file, &$dst_name) {
        $i=0;
        do {
            $n = $this->increaseCounter();
            $s = public_path() . '/upload/' .
                    $n . '/' . $filename;
            $b = file_exists($s);
            @mkdir(public_path() . '/upload/' . $n);
            if (!$b) {
                $b = !(\Alxnv\Nesttab\core\FileHelper::writeToFile($s, $file));
            }
            $i++;
        } while ($b && ($i < 10));
        if ($i >= 10) {
            \yy::gotoErrorPage('Cannot upload file to new dir');
        }
        $dst_name = '' . $n . '/'.  $filename;
    }


    /**
     * Увеличить счетчик цифровых директорий в папке upload на 1
     * @return int - новое количество цифровых директорий в папке upload
     */
    public function increaseCounter() {
        $s = public_path() . '/upload/counter.txt';
        $ms = 300;
        $t = microtime(true);
        do {
            try {
                $fp = fopen($s, 'r+b'); // открываем для чтения и записи, указатель
                  // помещается на начало файла
            } catch (\Exception $ex) {
                $fp = false;
            }
            if ($fp === false) usleep(10000); // сон 0.01 секунды
        } while (microtime(true) - $t < ($ms/1000));
        
        if ($fp === false) \yy::gotoErrorPage ('Error opening upload/counter.txt');
        if (flock($fp, LOCK_EX)) { // exclusive lock
            if (($data = fread($fp, 100)) !== false) {
                if (!ftruncate($fp, 0)) {
                    \yy::gotoErrorPage ('Error truncating upload/counter.txt');
                }
                fseek($fp, 0);
                $n = intval($data) + 1;
                if (fwrite($fp, $n) !== false) {
                    flock($fp, LOCK_UN);    // release the lock
                } else {
                    flock($fp, LOCK_UN);    // release the lock
                    $data = false;
                }
            }
        } else {
            $data = false;
        }
        fclose($fp);
        if ($data === false) {
            \yy::gotoErrorPage ('Error writing to upload/counter.txt');
        }
        return $n;
    }
    
    /**
     * Получить содержимое файла upload/counter.txt
     * @return string - содержимое файла
     */
    public static function getCounterFile() {
        $s = public_path() . '/upload/counter.txt';
        $s2 = \Alxnv\Nesttab\core\FileHelper::readLocked($s);
        if ($s2 === false) {
            \Alxnv\Nesttab\core\FileHelper::createNewFileAndWriteString($s, '0');
            $s2 = \Alxnv\Nesttab\core\FileHelper::readLocked($s);
        }
        if ($s2 === false) {
            \yy::gotoErrorPage('Error writing file upload/counter.txt');
        }
        return $s2;
        
    }
}  
