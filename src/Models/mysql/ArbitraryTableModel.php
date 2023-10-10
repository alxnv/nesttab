<?php

namespace Alxnv\Nesttab\Models\mysql;

/**
 * Description of ArbitraryTableModel
 *
 * @author Alexandr
 */
class ArbitraryTableModel {
    public $fs;
    
    public function init($fs) {
        $this->fs = $fs;
    }
    /*
     * Try to move to a new positiona a field in a table with ordr field (by default,'L')
     * @param string $tableName - table data
     * @param array $rec - current record
     * @param int $pos2 - new value of 'ordr' field
     * @param int $parentId - if it is 0, its a top level, otherwise its parent_id value
     *   if $parentId <> 0, there must be a field 'parent_id' in the table
     */
    
    public function move(string $tableName, array $rec, int $pos2, int $parentId) {
        // перемещает запись внутри в таблице, изменяя ordr
        global $db, $yy;
        $tbl = $tableName;
        $db->qdirect("lock tables $tbl write");
        $id = $rec['id'];
        $row = (object)$rec;
        $where = ($parentId == 0 ? '1' : 'parent_id = ' . $parentId);
        //$row=$db->qobj("select table_id, ordr from $tbl where id=$id");
        if (!is_null($row)) {
            if ($pos2 < 1) $pos2 = 1;
            $id1 = $row->ordr;
            $row2 = $db->qobj("select max(ordr) as mo from $tbl where
                    $where");
            $mxx1 = $row2->mo;

            if ($mxx1 === false) {
                $mxx1 = 0;
            };
            if ($pos2 > $mxx1)
                $pos2 = $mxx1;

            $mini = min($id1, $pos2);
            $maxi = max($id1, $pos2);
            if ($id1 > $pos2) {
                $mn1 = $pos2;
                $mx1 = $id1 - 1;
                $dir1 = ' desc';
                $sg1 = '+1';
            }
            else {
                $mn1 = $id1 + 1;
                $mx1 = $pos2;
                $dir1 = '';
                $sg1 = '-1';
            };

            $arr = $db->qlist("select id from $tbl
                    where $where
                    and ordr>=$mn1 and ordr<=$mx1 order by ordr".$dir1);

            $b = 0;
            $s = '';
            for ($i = 0;$i < count($arr); $i++) {
                if ($b) $s .= ',';
                $s .= $arr[$i]->id;
                $b = 1;
            }
            if ($s<>'') $db->qdirect("update $tbl set ordr=ordr $sg1 where id in ($s)");

            $db->qdirect("update $tbl set ordr=$1 where id=$2", [$pos2, $id]);
        }
        $db->qdirect('unlock tables');
    }
}
