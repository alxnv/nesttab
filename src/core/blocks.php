<?php
class blocks { 
/**
	суперглобальный класс для работы с блоками текста страницы
 *        подключается в BasicController
*/
    
    public $blocks = [];
    
    /**
     * Add Block
     * @global blocks $blocks
     * @param string $blockName - block name
     * @param string $text - text to add
     */
    public static function add(string $blockName, string $text) {
        global $blocks;
        if (!isset($blocks->blocks[$blockName])) $blocks->blocks[$blockName] = '';
        $blocks->blocks[$blockName] .= $text . chr(13) . chr(10);
    }
    
    /**
     * Show block
     * @global blocks $blocks
     * @param string $blockName -  block name
     */
    public static function show(string $blockName) { 
        global $blocks;
        if (isset($blocks->blocks[$blockName])) echo $blocks->blocks[$blockName];
    }
}

global $blocks;
$blocks = new blocks();
//$blocks->init();
?>