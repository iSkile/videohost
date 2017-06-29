<?php

use yii\db\Migration;

class m170526_112249_subscription extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%subscription}}', [
            'user_id' => $this->integer(),
            'section_id' => $this->integer(),
            'PRIMARY KEY (`user_id`, `section_id`)',
        ], $tableOptions);

    }

    public function down()
    {
        $this->dropTable('{{%subscription}}');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
