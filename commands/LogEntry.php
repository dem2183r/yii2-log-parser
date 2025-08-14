namespace app\models;

use yii\db\ActiveRecord;

class LogEntry extends ActiveRecord
{
    public static function tableName()
    {
        return 'log_entry';
    }
}