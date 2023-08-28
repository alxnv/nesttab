<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа image
 */

namespace Alxnv\Nesttab\Models\field_struct;

use Illuminate\Support\Facades\Session;

class ImageModel extends \Alxnv\Nesttab\Models\field_struct\BasicModel {

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
    public function validate($value, object $table_recs, string $index, array &$columns, int $i, array &$r) {
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
     * Удаляем файлы в upload соответствующие $fn (нужно еще удалить файлы в подкаталогах
     *   1. 2. 3. 4)
     * @param type $fn - имя файла для удаления
     */
    public function deleteFiles($fn) {
        @unlink(public_path() . '/upload/' . $fn);
        $pn = pathinfo($fn);
        for ($i = 1; $i < 5; $i++) {
            $s = public_path() . '/upload/' . $pn['dirname'] . '/' . $i . '/' . $pn['basename'];
            @unlink($s);
        }
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
     * get array of accepted file types in mime form
     * @param array $allowed - array of types
     * @return array - array of mime type strings 
     */
    public function getAcceptedFileTypes(array $allowed) {
        if (count($allowed) == 0) {
            return ['image/*'];
        };
        $arr = [];
        $arr2 = [];
        foreach ($allowed as $a) {
            if ($a == 'gif') $arr['gif'] = 1;
            if ($a == 'jpg') $arr['jpg'] = 1;
            if ($a == 'jpeg') $arr['jpg'] = 1;
            if ($a == 'png') $arr['png'] = 1;
        }
        if (isset($arr['jpg'])) $arr2[] = 'image/jpeg';
        if (isset($arr['gif'])) $arr2[] = 'image/gif';
        if (isset($arr['png'])) $arr2[] = 'image/png';
        return $arr2;
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
        $accepted = $this->getAcceptedFileTypes($params->allowed);
        $arr = \Alxnv\Nesttab\core\ArrayHelper::forArray($accepted, 
                function($s) {
                    return "'" . $s . "'";
                });
        $s = join(', ', $arr);
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
    allowImageTransform: false,
    acceptedFileTypes: [" . $s . "],";
    if (!$isReq) {
        echo "      onprocessfilestart: (file) => {  
        document.getElementById(" . '"' .  $fieldName . '_srv_"' . ").checked = false; },";
    };
    if ($isUploaded) {
        if ($rec['value'] <> '') {
            echo "files: [{"
            . 'source: "' . \yy::jsmstr($rec['value']) . '", 
                options: { type: "limbo" }
                }],';
        }
    } else {
        if ($rec['value_old'] <> '') {
            // if there was a picture in db
            echo "files: [{"
            . 'source: "' . \yy::jsmstr($rec['value_old']) . '", 
                options: { type: "local" }
                }],';
        };
    }
    echo "server: {";
    if (!$isReq) {
        echo "      remove: (source, load, error) => { 
        document.getElementById(" . '"' .  $fieldName . '_srv_"' . ").checked = true; load(); },";
    };
    echo "    process: '" . asset('/' . config('nesttab.nurl') .'/upload_image') . "?file928357=" . $fieldName . "&tbl=" . $table_id . "&rec=" . $rec_id . "',
        revert: '" . asset('/' . config('nesttab.nurl') . '/upload_image/revert') . "?file928357=" . $fieldName . "',
        restore: '" . asset('/' . config('nesttab.nurl') . '/upload_image/restore') . "?token=',
        load: '" . asset('/' . config('nesttab.nurl') . '/upload_image/load') . "?tbl=" . $table_id . "&rec=" . $rec_id . "&file928357=" . $fieldName . "|',
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
        //dd($r);
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
        $this->imageArrayTestValid($allowed); // проверить, нет ли в 
         // массиве элементов вида 'php*', 'py'
        $arr = [];
        for ($i = 0; $i < count($r['i_type']); $i++) {
            $w = intval($r['i_width'][$i]);
            $h = intval($r['i_height'][$i]);
            $arr[] = (object)['w' => $w,
                'h' => $h,
                't' => $r['i_type'][$i]];
            if ($i == 0) {
                if ((($w == 0) && ($h <> 0)) || (($w <> 0) && ($h == 0))) {
                    $this->setErr('iprm' . $i, __('There are zero values'));
                }
            } else {
                if (($w == 0) || ($h == 0)) {
                    $this->setErr('iprm' . $i, __('There are zero values'));
                }
            }
            if (($w < 0) || ($h < 0)) {
                $this->setErr('iprm' . $i, __('Negative number'));
            }
            if (($w > 1000000) || ($h > 1000000)) {
                $this->setErr('iprm' . $i, __('Too big number'));
            }
        }
        $r['iprm'] = $arr;
        
        $params = ['allowed' => $allowed, 'iprm' => $arr];
        return $this->saveStep2($tbl, $fld, $r, $old_values, $default, $params);

    }
    
    /**
     * Проверяет, что значения из массива разрешены (gif, jpeg, jpg или png)
     * @param array $allowed
     */
    public function imageArrayTestValid(array $allowed) {
        $arr = [];
        foreach ($allowed as $a) {
            if (!in_array($a, ['gif', 'jpeg', 'jpg', 'png']))
                $arr[] = ('"' . $a . '"');
        }
        if (count($arr) > 0) {
            $this->setErr('allowed', __('Forbidden extensions') 
                    . ': ' . join(', ', $arr));
        }
    }

    
}
