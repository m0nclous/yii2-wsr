<?php

use yii\db\Migration;

/**
 * Class m190303_140429_add_user_data
 */
class m190303_140429_add_user_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('user', [
            'login' => 'admin',
            'password' => 'sakhalin2018',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190303_140429_add_user_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190303_140429_add_user_data cannot be reverted.\n";

        return false;
    }
    */
}
