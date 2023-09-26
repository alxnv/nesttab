<?php

namespace Alxnv\Nesttab\Models;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;

class ColumnsModel {
    
    /**
     * Получить следующее незанятое имя данного типа для данной таблицы
     */
    public static function getNextNameOfType($table_id, $field_type) {
        global $db, $yy;
        $arr = $db->qlistArr("select name from yy_columns where table_id = $1 and name like $2",
                [$table_id, $field_type . '%']);
        
        $arr3 = [];
        foreach ($arr as $arr2) {
            $nm = $arr2['name'];
           
            $arr3[$nm] = 1;
        }
        
        $i=1;
        while (true) {
            $s = $i; //($i == 1 ? '' : $i);
            if (!isset($arr3[$field_type . $s])) return $field_type . $s;
            $i++;
        }
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
    
    /**
     * Возвращает в виде готовом для вывода в <select> список полей таблицы,
     *   которые могут быть использованы в поле типа select 
     * @global \Alxnv\Nesttab\Models\type $db
     * @global \Alxnv\Nesttab\Models\type $yy
     * @param int $table_id - id of the table
     * @return array - массив для вывода в select со списком всех полей таблицы
     */
    public static function getSelectColumns(int $table_id) {
        global $db, $yy;
        // получаем все типы полей, которые могут быть использованы в поле типа select
        $ar1 = \Alxnv\Nesttab\core\db\BasicTableHelper::getTypesForSelectFld();
        $allowed_types = join(', ', $ar1);
        $flds = $db->qlistArr("select a.*, b.descr as descr_fld from yy_columns a "
                . "left join yy_col_types_lang b on a.field_type = b.id where a.table_id = $1"
                . " and a.field_type in ($allowed_types) and b.language=$2 order by a.descr, a.id",
                [$table_id, Lang::getLocale()]);

        $arr = [];
        foreach ($flds as $fld) {
            $arr[$fld['id']] = \yy::qs($fld['descr'] . ' (' .
                    $fld['name'] . '), ' . mb_strtolower($fld['descr_fld']));
        }
        return $arr;
    }
    
    /**
     * Получаем данные столбцов таблицы с присоединенными названиями типов полей
     * @global type $db
     * @global type $yy
     * @param int $table_id
     * @return type
     */
    public static function getTableColumns($table_id) {
        global $db, $yy;
        $flds = $db->qlistArr("select a.*, b.descr as descr_fld from yy_columns a "
                . "left join yy_col_types_lang b on a.field_type = b.id where a.table_id = $1"
                . " and b.language=$2 order by a.ordr",
                [$table_id, Lang::getLocale()]);

        return $flds;
    }
    
    /**
     * Получаем данные столбцов таблицы с присоединенными строковыми кодами типов полей
     * @global type $db
     * @global type $yy
     * @param int $table_id
     * @return type
     */
    public static function getTableColumnsWithNames($table_id) {
        global $db, $yy;
        $flds = $db->qlistArr("select a.*, b.name as name_field from yy_columns a "
                . "left join yy_col_types b on a.field_type = b.id where a.table_id = $1"
                . " order by a.ordr",
                [$table_id]);

        return $flds;
    }
    
    public static function delete($id) {
        global $db, $yy;
        $err = '';
        $arr = $db->q("select * from yy_columns where id=$1" , [$id]);
        if (is_null($arr)) return 'Record not found';
        if (!$db->qdirectNoErrorMessage("delete from yy_columns where id=$1", [$id])) {
            $err .= sprintf ("Error %s\n", $db->handle->errorInfo()[2]);
            return $err;
        }
        DB::update("update yy_columns set ordr=ordr-1 where table_id=?"
                . " and ordr>?", [$arr['table_id'], 
                    $arr['ordr']]);
        return $err;
        
    }
}