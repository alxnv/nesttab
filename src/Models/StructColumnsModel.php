<?php

namespace Alxnv\Nesttab\Models;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;

class StructColumnsModel {
    public static function getTableColumns($table_id) {
        global $db, $yy;
        $flds = $db->qlistArr("select a.*, b.descr as descr_fld from yy_columns a "
                . "left join yy_col_types_lang b on a.field_type = b.id where a.table_id = $1"
                . " and b.language=$2 order by a.ordr",
                [$table_id, Lang::getLocale()]);

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