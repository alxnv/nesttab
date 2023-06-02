<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа image
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

use Illuminate\Support\Facades\Session;

class ImageModel extends \Alxnv\Nesttab\Models\field_struct\mysql\BasicModel {

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
        /*$v2 = $value;
        if (isset($r[$index])) {
            // загружен новый файл
            $file = json_decode($r[$index]);
            $ext = pathinfo($file->name, PATHINFO_EXTENSION);
            if (!\Alxnv\Nesttab\core\FormatHelper::validExt($ext))
                $table_recs->setErr($index, __('Wrong file type'));
            $allowed = $columns[$i]['parameters']['allowed'];
            if (count($allowed) > 0) {
                if (!\Alxnv\Nesttab\core\FormatHelper::inListCaseInsensitive($ext,
                    $allowed)) {
                    $arr = \Alxnv\Nesttab\core\ArrayHelper::forArray($allowed,
                            function($value) {
                                return '"' . $value . '"';
                            });
                    $table_recs->setErr($index, __('Allowed extensions') . ': '
                            . join(', ', $arr));
                }
            }
            $r[$index . '_srv_2'] = $r[$index];
            $v2 = '1';
        };
        $value = '';
        $r[$index] = '';
        if (isset($columns[$i]['parameters']['req']) && (trim($v2) == '')) {
            $table_recs->setErr($index, __('The file must exist'));
        }*/
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
        if (isset($r[$index])) {
            // загружен новый файл
            $token = $r[$index];
            if (!preg_match('/^[0-9a-f]{8}$/', $token)) {
                return false;
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
        return true;
    }
    
    
    /**
     * Вывод поля таблицы для редактирования
     * @param array $rec - массив с данными поля
     * @param array $errors - массив ошибок
     */
    public function editField(array $rec, array $errors) {
        echo \yy::qs($rec['descr']);
        echo '<br />';
        $fieldName = $rec['name'];
        $value = basename($rec['value']);
        echo '<input type="file" id = "' . $fieldName . '"  name = "' . $fieldName . '" />';
        echo "<script>
    let inputElement_" . $fieldName . " = document.querySelector('#" . $fieldName . "');
    const pond_" . $fieldName . " = FilePond.create(inputElement_" . $fieldName . ", {
    allowImageTransform: false,
    server: {
        process: '" . asset('/nesttab/upload_image') . "?file=" . $fieldName . "',
        revert: '" . asset('/nesttab/upload_image/revert') . "?file=" . $fieldName . "',
        headers: {
            'X-CSRF-TOKEN': '" . Session::token() . "',
        }}
    });
    </script>";
        if (isset($value) && $value <> '') {
            echo __('File') . ': ' . \yy::qs($value) . '<br />';
            echo '<input type="checkbox" '
            . 'name="' . $fieldName .'_srv_" id="' . $fieldName .'_srv_" /> ';
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
