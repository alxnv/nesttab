<?php
namespace app\controllers;

/**
 * Редактирование структуры таблиц - общий список
 */

class ChangeStructListController extends \app\backend\controllers\StructController {
    public function IndexAction() {
        $list = (new \app\models\StructModel())->getAll();
        $this->render(['list' => $list]);
    }
    
    
}

