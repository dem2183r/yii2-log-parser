<?php

namespace app\models;

use yii\db\ActiveRecord;

class LogEntry extends ActiveRecord
{
    public static function tableName()
    {
        return 'log_entry';
    }

    /**
     * @return array 
     */
    public function rules()
    {
        return [
            [['ip', 'timestamp', 'url', 'user_agent'], 'required'],
            [['timestamp'], 'safe'],
            [['url', 'user_agent'], 'string'],
            [['ip', 'os', 'architecture', 'browser'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return array 
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'IP адрес',
            'timestamp' => 'Дата и время',
            'url' => 'URL',
            'user_agent' => 'User Agent',
            'os' => 'Операционная система',
            'architecture' => 'Архитектура',
            'browser' => 'Браузер',
        ];
    }

    
    public static function getUniqueOS()
    {
        return self::find()->select('os')->distinct()->column();
    }

    
    public static function getUniqueArchitectures()
    {
        return self::find()->select('architecture')->distinct()->column();
    }
}