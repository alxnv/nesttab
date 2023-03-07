<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа float
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

class FloatModel extends \Alxnv\Nesttab\Models\field_struct\mysql\BasicModel {

    
    //public function data_type() {
    //    return 'tinyint(4)';
    //}

    /**
     * пытается сохранить(изменить)  в таблице поле
     * @param array $tbl
     * @param array $fld
     * @param array $r
     */
    public function save(array $tbl, array $fld, array &$r, array $old_values) {
        global $yy, $db;
        if (isset($r['default'])) {
            $r['default'] = mb_substr(trim($r['default']), 0, 255);
            $default = $r['default'];
            $fh = new \Alxnv\Nesttab\core\FormatHelper();
            if (false === $fh::floatConv($default)) {
                $this->setErr('default', '"' . $default . '" ' . __('is not valid') . ' ' . __('float value'));
            }
            $default = intval($default);
        } else {
            $default = '';
            $this->setErr('default', '"" ' . __('is not valid') . ' ' . __('float value'));
        }
        if (!isset($r['m'])) {
            $r['m'] = '0';
        }
        if (!isset($r['d'])) {
            $r['d'] = '0';
        }
        $m = intval($r['m']);
        $d = intval($r['d']);
        if ($m < 0) {
            $this->setErr('m', __("This number must be positive"));
        }
        if ($d < 0) {
            $this->setErr('d', __("This number must be positive"));
        }
        if ($m > 255) {
            $this->setErr('m', __('This number must be less than') . ' 256');
        }
        if ($d >= $m && !($d == 0 && $m == 0)) {
            $this->setErr('d', __('This number must be less than') . ' ' . __('previous number'));
        }
        $params = ['m' => $m, 'd' => $d];
        return $this->saveStep2($tbl, $fld, $r, $old_values, $default, $params);

    }
    
    /**
     * function that determines if col definition is changed
     * @param array $params
     * @param array $old_params
     * @return boolean
     */
    protected function colDefChanged($params, $old_params) {
        return (($params['m'] <> $old_params['m']) || ($params['d'] <> $old_params['d']));
    }
    
}
