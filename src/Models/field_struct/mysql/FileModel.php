<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа file
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

use Illuminate\Support\Facades\Session;

class FileModel extends \Alxnv\Nesttab\Models\field_struct\mysql\BasicModel {

    /**
     * Проверяем на валидность значение $value, и в случае ошибки записываем ее в
     *   $table_recs->err
     * @param type $value
     * @param object $table_recs (TableRecsModel)
     * @param string $index - индекс в массиве ошибок для записи сообщения об ошибке
     * @param array $columns - массив всех колонок таблицы
     * @param int $i - индекс текущего элемента в $columns
     * @param array $r - (array)Request
     * @return mixed - возвращает валидированное (и, возможно, обработанное) значение
     *   текущего поля
     */
    public function validate($value, object $table_recs, string $index, array $columns, int $i, array &$r) {
        if (isset($columns[$i]['parameters']['req']) 
                && ((!isset($columns[$i]['value_old']) || 
                        ($columns[$i]['value_old'] == '')) && ($value == ''))) {
            $table_recs->setErr($index, __('The file must be downloaded'));
        }
        if (isset($r[$columns[$i]['name']]) && 
                !\Alxnv\Nesttab\Models\TokenUploadModel::isValidToken($r[$columns[$i]['name']])) {
            // в $r значение вида '3/image.gif'
            unset($r[$columns[$i]['name']]);
        }
        return $value;
    }
    
    /**
     * Удаляем файлы в upload соответствующие $fn
     * @param type $fn - имя файла для удаления
     */
    public function deleteFiles($fn) {
        @unlink(public_path() . '/upload/' . $fn);
    }
    
    /**
     * Постобработка данных в случае если не было ошибок валидации
     *  (в основном для документов и изображений - загрузка их в каталог upload)
     * @param object $table_recs - TableRecsModel
     * @param array $columns - массив столбцов
     * @param int $i - индекс в массиве столбцов
     * @param array $r - (array)Request
     */
    public function postProcess(object $table_recs, array &$columns, int $i, array $r) {
        $index = $columns[$i]['name'];
        if (isset($r[$index . '_srv_'])) {
            // если выбран чекбокс "удалить"
            if (isset($r[$index]) && \Alxnv\Nesttab\Models\TokenUploadModel::isValidToken($r[$index])) {
                // загружен временный файл, удаляем его
                $tum = new \Alxnv\Nesttab\Models\TokenUploadModel();
                $tum->deleteTokenDir($r[$index]);
                unset($r[$index]);
                $columns[$i]['value'] = '';
            }
            if (isset($columns[$i]['value_old']) &&
                    ($columns[$i]['value_old'] <> '')) {
                // if there was a file before in this field
                $this->deleteFiles($columns[$i]['value_old']);
                $columns[$i]['value'] = '$';
            }
            
        } else {
            if (isset($r[$index]) && \Alxnv\Nesttab\Models\TokenUploadModel::isValidToken($r[$index])) {
                // загружен новый файл
                $token = $r[$index];
                if (isset($columns[$i]['value_old']) &&
                        ($columns[$i]['value_old'] <> '')) {
                    // if there was a file before in this field
                    $this->deleteFiles($columns[$i]['value_old']);
                }
                $um = new \Alxnv\Nesttab\Models\UploadModel();
                $value = $um->moveFilesToUpload($token);
                if ($value !== false) {
                    $columns[$i]['value'] = $value; // если не было ошибки
                } else {
                    return false;
                }
            } else {
                // файл остается прежним, ничего не делаем
            };
        }
        return true;
    }
    
    /**
     * Get file size from upload dir
     * @param type $fn - filename
     * @return boolean | int - file size in bytes or false if error has happened
     */
    public static function getFileSize($fn) {
        try {
            $n = filesize(public_path() . '/upload/' . $fn);
        } catch (\Exception $ex) {
            return false;
        }
        if ($n === false) return false;
        return $n;
    }
    
    public static function getNameFromPathName(string $fn) {
        $arr = \Alxnv\Nesttab\core\StringHelper::splitByFirst('/', $fn);
        return $arr[1];
    }
    
    /**
     * Вывод поля таблицы для редактирования
     * @param array $rec - массив с данными поля
     * @param array $errors - массив ошибок
     * @param int $table_id - id of the table
     * @param int $rec_id - 'id' of the record in the table
     * @param array $r - request data of redirected request
     */
    public function editField(array $rec, array $errors, int $table_id, int $rec_id, $r) {
        $params = json_decode($rec['parameters']);
        //var_dump($r);
        echo \yy::qs($rec['descr']);
        echo '<br />';
        $fieldName = $rec['name'];
        $isOld = (isset($rec['value_old']) && ($rec['value_old'] <>''));
        $isUploaded = (isset($rec['value']) && ($rec['value'] <>''));
        $isReq = (isset($params->req) && ($params->req == 1));
        if ($isUploaded) {
            // есть загруженный временный файл
            $value = basename($rec['value']);
        } else {
            $value = basename($rec['value_old']);
        }
        echo '<input type="file" id = "' . $fieldName . '"  name = "' . $fieldName . '" />';
        echo "<script>
    let inputElement_" . $fieldName . " = document.querySelector('#" . $fieldName . "');
    const pond_" . $fieldName . " = FilePond.create(inputElement_" . $fieldName . ", {
    allowImageTransform: false,";
    if (!$isReq) {
        echo "      onprocessfilestart: (file) => {  
        document.getElementById(" . '"' .  $fieldName . '_srv_"' . ").checked = false; },";
    };
    if ($isUploaded) {
        if ($rec['value'] <> '') {
            $f = \Alxnv\Nesttab\Models\TokenUploadModel::getFileNameAndSize($rec['value']);
            if ($f === false) {
                echo "files: [{"
                . 'source: "' . \yy::jsmstr($rec['value']) . '", 
                    options: { type: "limbo" }
                    }],';
            } else {
                echo "files: [{"
                . 'source: "' . \yy::jsmstr($rec['value']) . '", 
                    options: { type: "limbo",
                    file: {
                        name: "' . \yy::jsmstr($f[0]) . '", 
                        size: ' .  $f[1] .     '
                    }}
                    }],';
            }
        }
    } else {
        if ($rec['value_old'] <> '') {
            // if there was a picture in db
            $f = self::getFileSize($rec['value_old']);
            if ($f === false) {
                echo "files: [{"
                . 'source: "' . \yy::jsmstr($rec['value_old']) . '", 
                    options: { type: "local" }
                    }],';
            } else {
                echo "files: [{"
                . 'source: "' . \yy::jsmstr($rec['value_old']) . '", 
                    options: { type: "local",
                    file: {
                        name: "' . self::getNameFromPathName(\yy::jsmstr($rec['value_old'])) . '", 
                        size: ' .  $f .     '
                    }}
                    }],';
            }
        };
    }
    echo "server: {";
    if (!$isReq) {
        echo "      remove: (source, load, error) => { 
        document.getElementById(" . '"' .  $fieldName . '_srv_"' . ").checked = true; load(); },";
    };
    echo "    process: '" . asset('/' . config('nesttab.nurl') .'/upload_file') . "?file928357=" . $fieldName . "&tbl=" . $table_id . "&rec=" . $rec_id . "',
        revert: '" . asset('/' . config('nesttab.nurl') . '/upload_file/revert') . "?file928357=" . $fieldName . "',
        headers: {
            'X-CSRF-TOKEN': '" . Session::token() . "',
        }}
    });
    </script>";
        if (!$isReq && isset($value) && $value <> '') {
            //echo __('File') . ': ' . \yy::qs($value) . '<br />';
            echo '<input type="checkbox" '
            . 'name="' . $fieldName .'_srv_" id="' . $fieldName .'_srv_" '
                    .  (isset($r[$fieldName. '_srv_']) ? 'checked' : '') . '  /> ';
            echo '<label for="' . $fieldName .'_srv_">' . __('Delete') . '</label><br />';
        }
        
        echo '<br />';
        //echo '<br />';
    }

    /**
     * пытается сохранить(изменить)  в таблице поле
     * @param array $tbl
     * @param array $fld
     * @param array $r
     */
    public function save(array $tbl, array $fld, array &$r, array $old_values) {
        global $yy, $db;
        $s = '\\Alxnv\\Nesttab\\core\\db\\' . config('nesttab.db_driver') . '\\FormatHelper';
        $fh = new $s();
        
        $default = '';
        if (isset($r['allowed'])) {
            $r['allowed'] = $fh::delimetedByCommaToArray(mb_strtolower(mb_substr($r['allowed'], 0, 10000)));
            $allowed = $r['allowed'];
        } else {
            $allowed = [];
        }
        /*if (count($allowed) == 0) {
            $this->setErr('allowed', __('You must specify files extensions'));
        }*/
        $this->extensionsArrayTestValid($allowed); // проверить, нет ли в 
         // массиве элементов вида 'php*', 'py'
        $params = ['allowed' => $allowed];
        return $this->saveStep2($tbl, $fld, $r, $old_values, $default, $params);

    }
    
    
}
