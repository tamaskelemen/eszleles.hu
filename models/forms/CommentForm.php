<?php
namespace app\models\forms;

use app\components\Flash;
use app\models\Comment;
use Yii;
use yii\base\Model;
use yii\db\Exception;

class CommentForm extends Model
{
    public $comment;
    public $observation_id;

    public function rules()
    {
        return [
            [['comment', 'observation_id'], 'required', 'message' => 'A mező kitöltése kötelező!'],
            ['comment', 'string'],
            ['observation_id', 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'comment' => 'Új hozzászólás',
        ];
    }

    public function add()
    {
        if (!$this->validate()) {
            return false;
        }

        $comment = new Comment();
        $comment->comment = $this->comment;
        $comment->observation_id = $this->observation_id;
        $comment->user_id = Yii::$app->user->getIdentity()->id;

        try {
            if (!$comment->save()) {
                throw new Exception("A hozzászólás mentése nem sikerült.");
            }

            return true;
        } catch (\Exception $exception) {
            Flash::addWarning($exception->getMessage());
            return false;
        }
    }


}