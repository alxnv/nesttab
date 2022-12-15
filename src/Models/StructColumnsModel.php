<?php

namespace Alxnv\Nesttab\Models;

class StructColumnsModel {
    public static function getTableColumns($table_id) {
        global $db, $yy;
        $flds = $db->qlist_arr("select a.*, b.descr as descr_fld from yy_columns a "
                . "left join yy_col_types_lang b on a.field_type = b.id where a.table_id = $1"
                . " and b.language=$2 order by a.ordr",
                [$table_id, config('nesttab.language')]);

        return $flds;
    }
    
    public static function delete($id) {
        global $db, $yy;
        $err = '';
        if (!$db->qdirect_no_error_message("delete from yy_columns where id=$1", [$id])) {
            $err .= sprintf ("Error %s\n", mysqli_error($db->handle));
        }
        return $err;
        
    }
}