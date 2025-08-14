<?php

use yii\db\Migration;


class m250814_123056_create_log_entry_table extends Migration
{
    
    public function safeUp()
    {
        $this->createTable('{{%log_entry}}', [
            'id' => $this->primaryKey(),
            'ip' => $this->string()->notNull(),
            'timestamp' => $this->dateTime()->notNull(),
            'url' => $this->text()->notNull(),
            'user_agent' => $this->text()->notNull(),
            'os' => $this->string(),
            'architecture' => $this->string(),
            'browser' => $this->string(),
        ]);
    }

    
    public function safeDown()
    {
        $this->dropTable('{{%log_entry}}');
    }
}