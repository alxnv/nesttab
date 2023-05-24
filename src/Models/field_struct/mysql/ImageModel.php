<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа image
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

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
        $v2 = $value;
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
        }
        return $value;
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
        if (isset($r[$index . '_srv_2'])) {
            // загружен новый файл
            $file = json_decode($r[$index . '_srv_2']);
            $fname = basename($file->name); // validate input values
            
            $um = new \Alxnv\Nesttab\Models\UploadModel();
            $value = $um->copyFileToUpload($fname, base64_decode($file->data));
            $columns[$i]['value'] = $value;
        };
        
    }
    
    /**
     * Вывод поля таблицы для редактирования
     * @param array $rec - массив с данными поля
     * @param array $errors - массив ошибок
     */
    public function editField(array $rec, array $errors) {
        echo \yy::qs($rec['descr']);
        echo '<br />';
        \yy::imageLoad($rec['name'], basename($rec['value']));
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
            $r['allowed'] = $fh::delimetedByCommaToArray(mb_substr($r['allowed'], 0, 10000));
            $allowed = $r['allowed'];
        } else {
            $allowed = [];
        }
        /*if (count($allowed) == 0) {
            $this->setErr('allowed', __('You must specify files extensions'));
        }*/
        $this->imageArrayTestValid($allowed); // проверить, нет ли в 
         // массиве элементов вида 'php*', 'py'
        $params = ['allowed' => $allowed];
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
