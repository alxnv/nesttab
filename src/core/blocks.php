<?php
class blocks { 
/**
	суперглобальный класс для работы с блоками текста страницы
 *        подключается в BasicController
*/
    
    public $blocks = [];
    public $tags = [];
    
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
     * Add text to block once for every tag
     * @param string $blockName
     * @param strign $tag
     * @param string $text
     */
    public static function addBlockOnce(string $blockName, string $tag, string $text) {
        global $blocks;
        if (!isset($blocks->tags[$blockName])) {
            $blocks->tags[$blockName] = [];
        }
        if (isset($blocks->tags[$blockName][$tag])) return; // text for tag already
          // exists. do nothing

        // set text for tag
        $blocks->tags[$blockName][$tag] = $text . chr(13) . chr(10);
    }
    
    /**
     * Show block
     *  first show tags in alphabetical order, then main block
     * @global blocks $blocks
     * @param string $blockName -  block name
     */
    public static function show(string $blockName) { 
        global $blocks;
        if (isset($blocks->tags[$blockName])) {
            ksort($blocks->tags[$blockName]); // sort tag by array keys
            foreach ($blocks->tags[$blockName] as $txt) {
                echo $txt;
            }
        }
        if (isset($blocks->blocks[$blockName])) echo $blocks->blocks[$blockName];
    }
}

global $blocks;
$blocks = new blocks();
//$blocks->init();
?>