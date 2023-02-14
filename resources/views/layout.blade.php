<?php
// main layout
?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=__("Administrator's module")?></title>
    <link rel="stylesheet" href="<?=asset('/nsttab/css/styles.css')?>">
    <link rel="stylesheet" href="<?=asset('/nsttab/css/jquery-confirm.min.css')?>">
    <?php 
    if (isset($with_tinymce)) {
        echo '<script type="text/javascript" src="' . asset('/nsttab/js/tinymce/tinymce.min.js') . '"></script>';
    ?>
    
    <script type="text/javascript">
    tinymce.init({
      selector: '.myeditablediv',
      plugins: 'link, image, code, table, textcolor, lists',
    });
    </script>
    <?php
    }
    ?>
    <script type="text/javascript" src="<?=asset('/nsttab/js/jquery-3.6.0.min.js')?>"></script>
    <script type="text/javascript" src="<?=asset('/nsttab/js/jquery-confirm.min.js')?>"></script>
    <script type="text/javascript" src="<?=asset('/nsttab/' . \yy::getJsLangFile())?>"></script>
    <script type="text/javascript" src="<?=asset('/nsttab/js/vue.global.js')?>"></script>
    <script type="text/javascript" src="<?=asset('/nsttab/js/functions.js')?>"></script>
    
  </head>
  <body>
      <div id="body-wrapper">
          <div id="header"><a href="<?=asset('/nesttab')?>">Nesttab</a></div>
        <div id="page_content">
	<div id="admin_left">
	@include('nesttab::menu_left')
<?php
/*
if ($user['can_modify_structure']) {
	echo '<h2>' . \yy::t('Structure') . '</h2>';
	if ($user['all_tables']) {
		echo '<p><a href="' . $yy->baseurl . 'struct-add-table">' . \yy::t('Add table') . '</a><br />'
                        . '<a href="' .$yy->baseurl. 'change-struct-list">' . \yy::t('All upper level tables list') . '</a></p>';
	}
}*/
?>	
	</div>
	<div id="admin_right">
 <?php //\app\core\Helper::show_prev_link($controller); // отображаем ссылку "уровень вверх",
    // если $controller->prev_link<>'',
    // ссылки "Назад" и "Выйти"
 ?>
		

	@yield('content')
	</div>
        </div>    
        <div id="footer">&copy; 2022-2023 by Alexander Vorobyov</div>
      </div>
    <script>
    @stack('js')
    </script>
  </body>
</html>
                