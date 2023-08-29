<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;

use app\models\LoginAttempt;

/**
* This is a example only.
*/
class SiteController extends Controller
{
	/**
	* Login Attempt Protection
	* @see models/LoginAttempt.php
	*/
	private $_ip;
	
	/**
	* Initializes the object.
	* @url https://www.yiiframework.com/doc/guide/2.0/en/concept-components
	* @url https://www.yiiframework.com/doc/api/2.0/yii-base-model#init()-detail
	*
	* This method is invoked at the end of the constructor after the object is initialized with the
	* given configuration.
	*/
	public function init()
	{
		parent::init();// __construct()
		
		 // ... initialization after configuration is applied...
		 
		/**
		* User IP
		* @url https://www.yiiframework.com/doc/api/2.0/yii-web-request#$referrer-detail
		* @url https://www.yiiframework.com/doc/guide/2.0/en/runtime-requests#client-information
		* @url https://www.yiiframework.com/doc/guide/2.0/en/runtime-requests#http-headers
		*/
		$userIP = $userIP = \Yii::$app->getRequest()->getUserIP();
		if (!empty($userIP)) {

			$this->_ip = $userIP;
		}
	}
	
	/**
	* Login action.
	* @url https://stackoverflow.com/questions/33608821/nullable-return-types-in-php7
	* @return yii\web\Response|string
	*/
	public function actionLogin()//: yii\web\Response|string
	{
		// if logged in... go home... else...
		$isGuest = \Yii::$app->user->isGuest;
		if (!$isGuest) {
		
			return $this->goHome();
		}
		
		// Security - @app\models\LoginAttempt.php
		$loginAttempt = new LoginAttempt();
		$loginAttempt->purgeBannedIp($this->_ip);
		$isBanned = $loginAttempt->isBanned($this->_ip);
		if ($isBanned) {

			$msg = \Yii::t('app', 'Too many login attempts. Please try again later.');
			throw new \yii\web\NotFoundHttpException($msg);
		}
		else {
			// ...do something...
			
			$model = new LoginForm();
			$request = \Yii::$app->request;
			if ($model->load($request->post())) {
				if ($model->login()) {
				
					return $this->goBack();
				}
				
				// Security - @app\models\LoginAttempt.php - log IP
				$loginAttempt->ip = $this->_ip;
				$loginAttempt->save();
			}
			
			$model->password = '';
				
			return $this->render('login', [
				'model' => $model,
			]);
		}
	}
  
}
