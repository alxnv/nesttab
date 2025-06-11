<?php
namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;

class StructAddTableController extends BasicController {
    /**
     * Создать подтаблицу для таблицы
     *  форма ввода
     * @global type $db
     * @param int $id - айди таблицы для которой создается подтаблица
     *   (если $id==0, то создается таблица верхнего уровня)
     * @return type
     */
    public function index(int $id) {
        global $db;
        $db->loadAllTablesData();
        // получить структуру таблицы с заданным $id
        if ($id == 0) {
            $tbl = ['id' => 0];
        } else {
            $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($id); 
        }
        return view('nesttab::struct_add_table', ['tbl' => $tbl]);
    }
    
    /**
     * Создать подтаблицу для таблицы
     *  непосредственно само создание
     * @global type $db
     * @param int $id - айди таблицы для которой создается подтаблица
     *   (если $id==0, то создается таблица верхнего уровня)
     * @return type
     */
    public function step22(int $id, Request $request) {
        // create table structure, step 2, write to the tables
	// пытаемся создать таблицу указанного типа и с указанным именем
        global $yy;
        $r = $request->all();
        // получить структуру таблицы с заданным $id
        if ($id == 0) {
            $tbl = ['id' => 0];
        } else {
            $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($id); 
        }
        $arr2 = $yy->settings2['table_types'];
	if (!isset($r['tbl_type']) || !isset($r['int_bytes']))  \yy::gotoErrorPage(__('Required parameter is not passed'));
	$tbl_idx = intval($r['tbl_type']);
        $intBytes = intval($r['int_bytes']);
	if ($tbl_idx < 0 || $tbl_idx >= count($arr2)) \yy::gotoErrorPage('Wrong index of table');
        $model = \Alxnv\Nesttab\Models\Factory::createTableModel($yy->settings2['table_names'][$tbl_idx]);
        
        $tableId = 0;
        $topTable = 0; // parent table id for top level table equal to 0
        if ($model->createTable($r, $message, $tableId, $tbl['id'], $intBytes, $tbl)) {
            if ($id == 0) {
                \yy::gotoMessagePage($message);
            } else {
                \yy::redirectNow($yy->nurl . 'struct-change-table/edit/' . $id);
            }
        } else \yy::gotoErrorPage($message);
        
    }
    
}

