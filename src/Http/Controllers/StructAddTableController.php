<?php
namespace app\controllers;

class StructAddTableController extends \app\backend\controllers\Controller {
    public function IndexAction() {
        $this->render([]);
    }
    
    public function step22Action($r) {
        // create table structure, step 2, write to the tables
	// пытаемся создать таблицу указанного типа и с указанным именем
        $sa = new \app\models\StructAddTableModel();
        if ($sa->Execute($r, $message)) \yy::gotoMessagePage($message);
           else \yy::gotoErrorPage($message);
        
    }
    
}

