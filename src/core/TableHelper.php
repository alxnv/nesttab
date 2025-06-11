<?php

/**
 * Description of TableHelper
 *
 * Helper class for tables
 * 
 * @author Alexandr
 */

namespace Alxnv\Nesttab\core;

class TableHelper {
    /**
     * returns long table type name by short table type name
     * @global type $yy
     * @param string $char ('O','L','D','T')
     * @return string ('one', 'list', 'ord', 'tree')
     * @throws \Exception
     */
    public static function getTableTypeByOneChar(string $char):string {
        global $yy;
        if (($key = array_search($char, $yy->settings2['table_names_short'])) === false) {
                throw new \Exception('Bad table type');
        }
        return $yy->settings2['table_names'][$key];
    }
    
}
