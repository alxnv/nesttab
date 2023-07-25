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

/**
 * Порядок редактирования полей типа file и image:
 * 
 * передающиеся данные в session в случае, если во временном файле был сохранен
 *    загруженный файл:
 *  $name . '_srv_name' - имя файла без пути (для отображения около чекбокса "Удалить"
 *       при редактировании файла)
 *  $name . '_srv_file' - токен временного загруженного файла
 *  $name . '_srv_del' - (del) - 1/0 - чекбокс, означающий что нужно удалит текущий файл
 *  $name = '' (обнуляем, чтобы не портил сессию)
 *
 * 
 * 0. если был del, то в дальшейшем не считается что был заружен файл
 * 1 в случае, если при валидации данных были ошибки, то
 * 1.1 для тех полей fi (file, image) для которых не было ошибок, и был загружен файл:
 *    если была предыдущая временная загрузка:
 *      ее token удаляется  
 *    присваевается _srv_name новому имени файла
 *    файл загружается в tokens
 * 1.2 для тех полей fi (file, image) для которых были ошибоки, и был загружен файл:
 *    _srv_name и token остаются прежними
 *     временный загруженный файл удаляется
 * 1.3 если не был заружен файл, _srv_name и token остаются прежними
 * 2 если не было ошибок (тогда сохраняем данные в БД)
 * 2.1 если установлен _srv_name
 *     2.1.1 если было del:
 *       файл из upload удаляется (если он там был)
 *       файл из tokens удаляется
 *       $columns[$i]['value] = ''
 *       
 *     2.1.2
 *     {если был загружен файл:
 *       файл из upload удаляется (если он там был)
 *       файл из tokens удаляется
 *       файл записывается из base64 в upload } else {
 *     если не был загружен файл:
 *       если файл был в записи бд и upload каталоге:
 *         файл из upload удаляется (если он там был)
 *       файл из tokens перемещается в upload
 *       записи присваевается адрес файла в upload ($columns[$i]['value] = <значение>)
 *     }  
 *     происходит постобработка файла
 *       (в частности, для image могут добавляться thumbnails)
 * 2.2 если не установлен _srv_name
 *     2.2.1 если было del:
 *       файл из upload удаляется (если он там был)
 *       $columns[$i]['value] = ''
 *     2.2.2
 *     {если был загружен файл:
 *       предыдущий загруженый файл удаляется из upload, если он был
 *       новый загруженный файл записывается в upload
 *       записи присваевается адрес файла в upload ($columns[$i]['value] = <значение>)
 *       происходит постобработка файла
 *          (в частности, для image могут добавляться thumbnails)
 *     } else {если не был загружен файл:
 *       все данные остаются прежними, в бд перезаписывается старое значение
 *     }
 */

namespace Alxnv\Nesttab\Models;


class UploadModel {
    
    /**
     * Копирует файл в подпапку папки upload, если нужно 
     *  создает подпапки с новыми номерами
     *   копирует также thumbnail если это изображение
     * @param string $token - токен загруженного файла
     * @return string | boolean - подадрес в папке upload, в который был скопирован файл
     *   либо false, если произошла ошибка
     */
    public function moveFilesToUpload(string $token) {
        // в каталоге могут создаваться директории 1-4, так что убедимся, что не будет
        //  файлов с такими именами
        
        $tm = new \Alxnv\Nesttab\Models\TokenUploadModel();
        $filename = $tm->getFileName($token); // получить имя загруженного в токен файла

        if ($filename === false) return false;
        
        if (in_array($filename[0], ['1', '2', '3', '4'])) $filename[0] .= '_';
        $n = intval(static::getCounterFile());
        if ($n == 0) {
            $this->uploadToNewDir($filename, $token, $dst_name, $tm);
        } else {
            $ids = [];
            $b = false;
            $tries = 0; // максимум 3 попытки для различных директорий
            do {
                $k = rand(1, $n);
                if (isset($ids[$k])) continue; // в эту директорию уже пытались записать
                $ids[$k] = 1; // попробовали записать в эту директорию
                $s = public_path() . '/upload/' .
                    $k . '/' . $filename[0];
                if (!\Alxnv\Nesttab\core\FileHelper::numberOfFilesInDirLessThen(public_path() .
                        '/upload/' . $k, $n + 2)) {
                    // если в директории больше файлов чем число в upload/counter.txt,
                    //  то новая попытка, туда не пишем
                    $tries++;
                    continue;
                }
                $b = $this->moveTokenToFile(public_path() . '/upload/' .
                    $k, $filename, $token, $tm);
                $dst_name = $k . '/' . $filename[0];
                $tries++;
            } while (!$b && ($tries < 3) && (count($ids) < $n));
            if (!$b) $this->uploadToNewDir($filename, $token, $dst_name, $tm); // если так и не удалось
              //  записать
              // в существующие директории за 3 попытки, либо пока не просмотрели все
              //  существующие директории (если их меньше 4-х), 
              //  пытаемся записать в новую директорию

        }
        return $dst_name;
    }       

    /**
     * Переместить файл (и thumbnal, если он есть) в директорию $upload_dir
     *   из директории, на которую указывает $token
     * @param string $upload_dir - директория вида public_path() . '/upload/<N>',
     *  в которую будем перемещать файл
     * @param array $fn - [<имя файла в который пытаемся переместить файл из токена>|
     *   <имя thumbnail(если есть)>]
     * @param string $token - токен (TokenUploadModel)
     * @param object $tm - TokenUploadModel
     * @return type
     */
    public function moveTokenToFile(string $upload_dir, array $fn, string $token, object $tm) {
        
        $fn2 = public_path() . '/upload/temp/' . $token . '/' . $fn[0];
        $th2 = public_path() . '/upload/temp/' . $token . '/' . $fn[1];
        $file = $upload_dir . '/' . $fn[0];
        $b = \Alxnv\Nesttab\core\FileHelper::copyFile($file, $fn2);
        if ($fn[1] <> '') {
            // if thumbnail exists
            @mkdir($upload_dir . '/1');
            \Alxnv\Nesttab\core\FileHelper::copyFile(
                    $upload_dir . '/1/' . $fn[0], $th2);
        }
        if ($b) $tm->deleteTokenDir($token);
        return $b;
    }
    /**
     * Увеличивать счетчик директорий на единицу и пытаться записать файл,
     * создавая новую директорию,
     *  пока не запишем,
     *  либо пока не увеличим 10 раз, тогдк выдается ошибка
     * @param array $filename - [<исходное имя загруженного временного файла
     *  в директории $token>, <имя thumbnail в директории $token(если есть)>]
     * @param string $file - содержимое файла, который будем копировать
     * @param mixed $dst_name - сюда записывается подадрес загруженного файла
     *   в папке upload (например, "1/file.ext")
     */
    public function uploadToNewDir(array $filename, string $token, &$dst_name, object $tm) {
        $i=0;
        $fn2 = public_path() . '/upload/temp/' . $token . '/' . $filename[0];
        do {
            $n = $this->increaseCounter();
            $s = public_path() . '/upload/' .
                    $n . '/' . $filename[0];
            $b = file_exists($s);
            try {
                @mkdir(public_path() . '/upload/' . $n);
            } catch (\Exception $ex) {
                $b = true;
            }
            if (!$b) {
                $b = !(\Alxnv\Nesttab\core\FileHelper::copyFile($s, $fn2));
                if ($filename[1] <> '') {
                    @mkdir(public_path() . '/upload/' . $n . '/1'); // make dir for thumbnail
                    \Alxnv\Nesttab\core\FileHelper::copyFile(public_path() . '/upload/' .
                        $n . '/1/' . $filename[0],
                        public_path() . '/upload/temp/' . $token . '/' . $filename[1]);
                }

                if (!$b) $tm->deleteTokenDir($token);
            }
            $i++;
        } while ($b && ($i < 10));
        if ($i >= 10) {
            \yy::gotoErrorPage('Cannot upload file to new dir');
        }
        $dst_name = '' . $n . '/'.  $filename[0];
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
