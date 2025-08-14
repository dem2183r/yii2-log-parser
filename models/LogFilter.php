<?php

namespace app\models;

use yii\base\Model;
use app\models\LogEntry;

class LogFilter extends Model
{
    public $date_from;
    public $date_to;
    public $os;
    public $architecture;

    /**
     * @return array правила валидации
     */
    public function rules()
    {
        return [
            [['date_from', 'date_to'], 'date', 'format' => 'php:Y-m-d'],
            [['os', 'architecture'], 'string'],
            [['date_from', 'date_to', 'os', 'architecture'], 'safe'],
        ];
    }

    /**
     * @return array  атрибут
     */
    public function attributeLabels()
    {
        return [
            'date_from' => 'Дата от',
            'date_to' => 'Дата до',
            'os' => 'Операционная система',
            'architecture' => 'Архитектура',
        ];
    }

    
    public static function getOsList()
    {
        $oses = LogEntry::find()->select('os')->distinct()->column();
        $result = [];
        foreach ($oses as $os) {
            if (!empty($os)) {
                $result[$os] = $os;
            }
        }
        return ['' => 'Все'] + $result;
    }

    
    public static function getArchitectureList()
    {
        $archs = LogEntry::find()->select('architecture')->distinct()->column();
        $result = [];
        foreach ($archs as $arch) {
            if (!empty($arch)) {
                $result[$arch] = $arch;
            }
        }
        return ['' => 'Все'] + $result;
    }
}