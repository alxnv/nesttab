<?php
namespace app\controllers;
//use \app\yy\Controller;

function logged_and_renew() {
				/*
        if (!isset($_SESSION['logged_user7237'])) {
                header("Location: ./login.php");
                exit;
        } else {
                # обновляем данные о текущем пользователе по его логину
            
                global $yy;
                global $db; 
                
                if ($yy->user_data->use_custom_user_mangement_system) {
                        $arr = $yy->user_data->refresh_user_credentials($_SESSION['logged_user7237']['name']);
                } else {
                        $login = $db->escape($_SESSION['logged_user7237']['name']);
                        $arr = $yy->q("select * from yy_custom_users where name='$login'");
                        if (is_null($arr)) {
                                // не найден логин
                                $_SESSION['err4'] = 1;
                                unset($_SESSION['logged_user7237']);
                                header("Location: .");
                                exit;
                        }
                }

                $_SESSION['logged_user7237'] = $arr;
                return $arr;

        }*/
        

}


class SiteController extends \app\backend\controllers\ControllerIndex {
    public function IndexAction() {
        //global $yy;


        $user = logged_and_renew();
        //var_dump($user);
        //exit;
        $this->render([]);
    }
}

/*


include "prolog-before.php";

include "epilog-after.php";
*/