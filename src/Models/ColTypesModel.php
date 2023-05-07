<?php

namespace Alxnv\Nesttab\Models;

use Illuminate\Support\Facades\Lang;

class ColTypesModel {
    
    /**
     * Выбрать все типы полей кроме типа "вложенная таблица"
     * @global type $db
     * @global type $yy
     * @return type
     */
    public function getFieldsList() {
        global $db, $yy;
        
        $lang = Lang::getLocale(); //$yy->settings['language'];
        //dd($lang);
        $arr = $db->qlistArr("select a.*, b.descr from yy_col_types a, "
                . "yy_col_types_lang b where b.language='$lang' and b.id=a.id "
                . "order by a.id_category, b.descr");
        
        $arr2 = [];
        foreach ($arr as $arr3) {
            if ($arr3['id'] == 6) continue; // пропускаем тип "вложенная таблица" 
            if (!isset($arr2[$arr3['id_category']])) {
                $arr2[$arr3['id_category']] = [];
            }
            $arr2[$arr3['id_category']][] = $arr3;
        }
        return $arr2;
    }

    /**
     * Получить один тип поля
     * @global type $db
     * @global type $yy
     * @return type
     */
    public function getOne($field_id) {
        global $db, $yy;
        
        $lang = Lang::getLocale(); //$yy->settings['language'];
        $arr = $db->q("select a.*,b.descr,b.language from yy_col_types a, yy_col_types_lang b"
                . " where b.language=$1 "
                . " and b.id=$2 and a.id=$2", [$lang, $field_id]);
        return $arr;
    }

    
}

