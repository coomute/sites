<?php

class LoginController extends Controller
{
	public $defaultAction = 'login';

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
    //redirect to the login page if he's guest or if there's no cookie set
		if (Yii::app()->user->isGuest || !array_key_exists(  'sso_authent_coomute', $_COOKIE)) {
			$model=new UserLogin;
			// collect user input data
			if(isset($_POST['UserLogin'])){
				$model->attributes=$_POST['UserLogin'];
				// validate user input and redirect to previous page if valid
				if($model->validate()){
          //check if the action contains a URL, meaning it's a remote login
          if(isset($_GET['url'])){
            $this->createSession();
            //This is an ugly hack needed to generate the mg cookie so the user sees he is connected
            //First it goes to dashboard without the mg cookie then it's redirected to the index with the cookie
            //ugly
            $this->redirect('http://coomute.net/dogma/dashboard/');
            
          }
          elseif (strpos(Yii::app()->user->returnUrl,'/index.php')!==false)
						$this->redirect(Yii::app()->controller->module->returnUrl);
					else
						$this->redirect(Yii::app()->user->returnUrl);
				}
			}
      $this->render('/user/login',array('model'=>$model));
			// display the login form
		}else{
        //Heads toward the profile page if connected
        $this->redirect(Yii::app()->controller->module->returnUrl);
    }
	}
	
	private function createSession() {
		$user = User::model()->notsafe()->findByPk(Yii::app()->user->id);
    //create the session token
    $user->session = uniqid(crypt(rand()).'_', 'true');

    //create the cookie with the user ID and the token
    setcookie("sso_authent_coomute[id]", $user->id, 0,"/", 'coomute.net');
    setcookie("sso_authent_coomute[token]", $user->session, 0,"/", 'coomute.net');
    //strore the time of last visit
    $user->lastvisit = time();
    $user->save();
    header( 'Location:'.$_GET['url']);
    die;
	}

  public function actionRemoteLogin(){
		$model=new UserLogin;
    //don't redirect to activation, else you'd get errors
    if(strstr($_GET['url'], 'user/activation/activation')){
      $url = '/';
    }else{ 
      $url = $_GET['url'];
    }
    $this->renderPartial('/user/loginminimal',array('model'=>$model, 'url'=>$url));
  }

}
