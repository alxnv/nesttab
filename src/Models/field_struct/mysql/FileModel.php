<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа file
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

class FileModel extends \Alxnv\Nesttab\Models\field_struct\mysql\BasicModel {

    /**
     * Проверяем на валидность значение $value, и в случае ошибки записываем ее в
     *   $table_recs->err
     * @param type $value
     * @param object $table_recs (TableRecsModel)
     * @param string $index - индекс в массиве ошибок для записи сообщения об ошибке
     * @param array $columns - массив всех колонок таблицы
     * @param int $i - индекс текущего элемента в $columns
     * @return mixed - возвращает валидированное (и, возможно, обработанное) значение
     *   текущего поля
     */
    public function validate($value, object $table_recs, string $index, array $columns, int $i) {
        $v2 = $value;
        if (isset($_FILES[$index])) {
            // загружен новый файл
            $v2 = '1';
        };
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
     */
    public function postProcess(object $table_recs, array $columns, int $i) {
        $index = $columns[$i]['name'];
        if (isset($_FILES[$index])) {
            // загружен новый файл
            $um = new \Alxnv\Nesttab\Models\UploadModel();
            $value = $um->copyFileToUpload($_FILES[$index]['name'], $_FILES[$index]['tmp_name']);
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
        \yy::imageLoad($rec['name']);
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
        if (count($allowed) == 0) {
            $this->setErr('allowed', __('You must specify files extensions'));
        }
        $params = ['allowed' => $allowed];
        return $this->saveStep2($tbl, $fld, $r, $old_values, $default, $params);

    }
    
    
}
