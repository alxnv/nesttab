<?php

class user_data {
	
	public $use_custom_user_mangement_system = 0; # использовать ли определенную пользователем систему
	    # управления пользователями
	
	# все нижеприведенные функции вызываются, если $use_custom_user_mangement_system == true

	function get_user_credentials():array {
		
		return array('can_modify_structure' => true, 'all_tables' => true, 'username' => 'admin',
			'can_modify_users' => true);
			  # сейчас указывает что текущий пользователь - admin
			  
	}

	function get_logout_url():string {
		/**
			url для страницы логаута пользователя (по умолчанию возвращает './logout.php')
		*/
		return './logout.php';
	}

	function get_enter_login_password_url():string {
		/**
			url для страницы ввода логина пароля пользователя (по умолчанию возвращает './')
		*/
		return './';
	}
	
	/**
	* обновляем данные пользователя из базы данных и делаем переход на ввод логина пароля если ошибка
	*  @param $login1 логин
	*  @return array данные пользователя из базы данных
	*/
	function refresh_user_credentials(string $login1):array {
		$login = \db::escape($login1);
		$arr = \yy::q("select * from yy_custom_users where name='$login'");
		if (is_null($arr)) {
			// не найден логин
			$_SESSION['err4'] = 1;
			unset($_SESSION['logged_user7237']);
			header("Location: .");
			exit;
		}
		return $arr;
	}

}
?>