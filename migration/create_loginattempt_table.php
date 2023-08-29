<?php

use yii\db\Migration;

/**
* Handles the creation of table `{{%login_attempt}}`
* @url https://www.yiiframework.com/doc/api/2.0/yii-db-migration
*/
class create_loginattempt_table extends Migration
{
	public function up()
	{
    		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
		
			// @url http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}
		
		$this->createTable('{{%login_attempt}}', [
			'id' => $this->primaryKey(),
			'ip' => $this->string(32)->notNull(),
			'expiration_at' => $this->integer()->notNull(),
			'created_at' => $this->integer()->notNull(),
		], $tableOptions);
	}
	
	public function down()
	{
		$this->dropTable('{{%login_attempt}}');
	}
}
