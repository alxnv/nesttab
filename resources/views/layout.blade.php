<?php
// main layout
?>
<!DOCTYPE html>
<html lang="<?=config('app.locale')?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=__("Administrator's module")?></title>
    <?php
    if (isset($requires) && isset($requires['need_confirm'])) {
    ?>
    <link rel="stylesheet" href="<?=asset('/nsttab/css/jquery-confirm.min.css')?>">
    <?php
    }
    ?>
    <?php
    if (isset($requires) && isset($requires['need_select2'])) {
    ?>
    <link rel="stylesheet" href="<?=asset('/nsttab/select2/select2.css')?>">
    <?php
    }
    ?>
    <link rel="stylesheet" href="<?=asset('/nsttab/css/styles.css')?>">
    <?php 
    if (isset($requires) && isset($requires['need_filepond'])) {
        echo '<link rel="stylesheet" href="' . asset('/nsttab/filepond/filepond-plugin-image-preview.css') . '">';
        echo '<link rel="stylesheet" href="' . asset('/nsttab/filepond/filepond.min.css') . '">';
    }
    ?>
    <script type="text/javascript" src="<?=asset('/nsttab/js/jquery-3.6.0.min.js')?>"></script>
    <?php
    if (isset($requires) && isset($requires['need_confirm'])) {
    ?>
    <script type="text/javascript" src="<?=asset('/nsttab/js/jquery-confirm.min.js')?>"></script>
    <?php
    }
    ?>
    <script type="text/javascript" src="<?=asset('/nsttab/' . \yy::getJsLangFile())?>"></script>
    <script type="text/javascript" src="<?=asset('/nsttab/js/vue/vue.global.js')?>"></script>
    <script type="text/javascript" src="<?=asset('/nsttab/js/functions.js')?>"></script>
    <?php 
    if (isset($requires) && isset($requires['need_html_editor'])) {
        echo '<script src="' . asset('/nsttab/js/ckeditor4/ckeditor.js') . '"></script>';
    }
    if (isset($requires) && isset($requires['need_select2'])) {
        echo '<script src="' . asset('/nsttab/select2/select2.min.js') . '"></script>';
        if (config('app.locale') <> 'en') {
            echo '<script src="' . asset('/nsttab/select2/i18n/' . config('app.locale')  . '.js') . '"></script>';
        }
    }
    ?>
    <?php 
    if (isset($requires) && isset($requires['need_datetimepicker'])) {
        echo '<script src="' . asset('/nsttab/js/jtsage-datebox/jtsage-datebox.min.js') . '"></script>'
                . "\n";
        if (config('app.locale') <> 'en') {
            echo '<script src="' . asset('/nsttab/js/jtsage-datebox/jtsage-datebox.locale.'
                    . config('app.locale') . '.min.js') . '"></script>' . "\n";
        }
        echo '<link rel="stylesheet" href="' . asset('/nsttab/filepond/filepond.min.css') . '">';
    }
    ?>
  </head>
  <body>

      <?php
      if (isset($requires) && isset($requires['need_filepond'])) {
          echo '<script src="' . asset('/nsttab/filepond/filepond.min.js') . '"></script>';
          echo '<script src="' . asset('/nsttab/filepond/filepond-plugin-image-preview.js') . '"></script>';
          //echo '<script src="' . asset('/nsttab/filepond/filepond-plugin-image-resize.js') . '"></script>';
          //echo '<script src="' . asset('/nsttab/filepond/filepond-plugin-image-transform.js') . '"></script>';
          echo '<script src="' . asset('/nsttab/filepond/filepond-plugin-file-validate-type.js') . '"></script>';
          echo '<script>
            FilePond.registerPlugin(FilePondPluginImagePreview);
            //FilePond.registerPlugin(FilePondPluginImageResize);
            //FilePond.registerPlugin(FilePondPluginImageTransform);
            FilePond.registerPlugin(FilePondPluginFileValidateType);
          </script>';
      }
      ?>

      <div id="body-wrapper">
          <div id="header"><a href="<?=asset('/' . config('nesttab.nurl'))?>">Nesttab</a></div>
        <div id="page_content">
	<div id="admin_left">
           <div id="admin_left1">
                @include('nesttab::menu_left')
           </div>
           <div id="admin_left2">
                @include('nesttab::tables_list_for_layout_left')
           </div>     
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
        <div id="footer">&copy; 2022-2023 by Alexander Vorobyov
		<br />
		Powered by <a href="https://laravel.com">Laravel</a>, <a href="https://vuejs.org">Vue</a>,
		<a href="https://jquery.com">JQuery</a> and <a href="https://pqina.nl">PQINA</a>
		</div>
      </div>
    <script type="text/javascript">
        $(function() {
            <?php
            \blocks::show('jquery_before');
            \blocks::show('jquery');
            ?>
        });
    </script>      
  </body>
</html>
                