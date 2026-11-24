<?php
namespace app\models\forms;

use app\components\Flash;
use app\models\Comment;
use app\models\Notification;
use Yii;
use yii\base\Model;
use yii\db\Exception;

class CommentForm extends Model
{
    public $id;
    public $comment;
    public $observation_id;
    public $parent_id;

    public function rules()
    {
        return [
            [['comment', 'observation_id'], 'required', 'message' => 'A mező kitöltése kötelező!'],
            ['comment', 'string'],
            [['observation_id', 'parent_id'], 'integer'],
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
        $comment->parent_id = $this->parent_id;
        $comment->user_id = Yii::$app->user->getIdentity()->id;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$comment->save()) {
                throw new Exception("A hozzászólás mentése nem sikerült.");
            }

            Notification::createForComment($comment);
            $transaction->commit();
            $this->id = $comment->id;

            return true;
        } catch (\Exception $exception) {
            $transaction->rollBack();
            Flash::addWarning($exception->getMessage());
            return false;
        }
    }


}