<?php

class LogoutController extends Controller
{
	public $defaultAction = 'logout';
	
	/**
	 * Logout the current user and redirect to returnLogoutUrl.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
    setcookie("sso_authent_coomute[id]",'', time()-3600,"/", 'coomute.net');
    setcookie("sso_authent_coomute[token]",'', time()-3600,"/", 'coomute.net');
    //don't redirect if it's a remote log out
    if(isset($_GET['url'])){
      return;
    }else{
      $this->redirect(Yii::app()->controller->module->returnLogoutUrl);
    }
	}

}
