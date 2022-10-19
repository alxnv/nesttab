<?php

namespace Alxnv\Nesttab\Models;

use Illuminate\Support\Facades\Lang;

class StructTableFieldsModel {
    
    /**
     * Получить следующее незанятое имя данного типа для данной таблицы
     */
    public static function getNextNameOfType($table_id, $field_type) {
        global $db, $yy;
        $arr = $db->qlist_arr("select name from yy_columns where table_id = $1 and name like $2",
                [$table_id, $field_type . '%']);
        
        $arr3 = [];
        foreach ($arr as $arr2) {
            $nm = $arr2['name'];
           
            $arr3[$nm] = 1;
        }
        
        $i=1;
        while (true) {
            $s = ($i == 1 ? '' : $i);
            if (!isset($arr3[$field_type . $s])) return $field_type . $s;
            $i++;
        }
    }
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
        $arr = $db->qlist_arr("select * from yy_col_types where language='$lang' "
                . "order by id_category, descr");
        
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
        $arr = $db->q("select * from yy_col_types where language=$1 "
                . " and id=$2", [$lang, $field_id]);
        return $arr;
    }

    public static function move($id, $pos2) {
        // перемещает запись внутри в таблице, изменяя ordr
        global $db, $yy;
        $tbl = 'yy_columns';
        $db->qdirect("lock tables $tbl write");
        $row=$db->qobj("select table_id, ordr from $tbl where id=$id");
        if ($row!==false) {
            if ($pos2<1) $pos2=1;
            //$db=my7::db();
            $id1=$row->ordr;
            $row2=$db->qobj("select max(ordr) as mo from $tbl where
                    table_id=$row->table_id");
            $mxx1=$row2->mo;

            if ($mxx1===false) {
                $mxx1=0;
            };
            if ($pos2>$mxx1)
                $pos2=$mxx1;

            $mini=min($id1,$pos2);
            $maxi=max($id1,$pos2);
            if ($id1>$pos2) {
                $mn1=$pos2;
                $mx1=$id1-1;
                $dir1=' desc';
                $sg1='+1';
            }
            else {
                $mn1=$id1+1;
                $mx1=$pos2;
                $dir1='';
                $sg1='-1';
            };

            $arr = $db->qlist("select id from $tbl
                    where table_id=$row->table_id
                    and ordr>=$mn1 and ordr<=$mx1 order by ordr".$dir1);

            $b=0;
            $s='';
            for ($i=0;$i<count($arr);$i++) {
                if ($b) $s.=',';
                $s.=$arr[$i]->id;
                $b=1;
            }
            if ($s<>'') $db->qdirect("update $tbl set ordr=ordr $sg1 where id in ($s)");

            //$db->update($tbl,array('ordr'=>$pos2),"uid=$id");
            $db->qdirect("update $tbl set ordr=$1 where id=$2", [$pos2, $id]);
        }
        $db->qdirect('unlock tables');
    }
    
}

