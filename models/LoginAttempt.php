<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
* @property int $id
* @property string $ip
* @property int $created_at
* @property int $expiration_at
*/
class LoginAttempt extends ActiveRecord
{
	const LOGIN_ATTEMPT_LIMIT = '2';// Starts at '0' zero, '2' is (default 3 chances)
	const BANNED_IP_EXPIRATION_TIME = '300';// Time to block/ban user in seconds '300' is (default 5 min)

	public static function tableName()
	{
		return '{{%login_attempt}}';
	}
	
	public function rules()
	{
		return [
			[['created_at', 'expiration_at'], 'integer'],
			
			// checks if "ip_address" is a valid IPv4 or IPv6 address
			[['ip'], 'string', 'max' => 32],
			// checks if "ip" is a valid email address
			['ip', 'ip'],
		];
	}
	
	public function beforeSave($insert): bool
	{
		if (!parent::beforeSave($insert)) {
			
			return false;
		}
		
		// ...custom code here...
		
		$this->created_at = time();
		$this->expiration_at = self::BANNED_IP_EXPIRATION_TIME + $this->created_at;
		
		// ...custom code here...
		
		return true;
	}
	
	/**
	* @param string $ip ip address
	* @return boolean
	*/
	public function isBanned($ip): bool
	{
		$q = $this->getCriteriaByIp($ip);
		if ($q >= self::LOGIN_ATTEMPT_LIMIT) {
		
			return true;
		}
		
		return false;
	}
	
	/**
	* @param string $ip ip address
	*/
	public function purgeBannedIp(string $ip)
	{
		$q = $this->getCriteriaByIp($ip);
		if ($q >= self::LOGIN_ATTEMPT_LIMIT) {
		
			$model = $this->find()
				->select('expiration_at')
				->orderBy(['created_at' => SORT_DESC])
				->limit('1')
				->one();
				
			if (time() >= $model->expiration_at) {
			
				$this->deleteAll('ip=:ip', [':ip' => $ip]);
			}
		}
	}
	
	/**
	* The Query, return matches.
	* @param string $ip ip address
	* @return object criteria
	*/
	private function getCriteriaByIp(string $ip): string
	{
		$q = LoginAttempt::find()
			->where('ip=:ip', [':ip' => $ip])
			->params([':ip' => $ip])
			->count();
			
		return $q;
	}
}
