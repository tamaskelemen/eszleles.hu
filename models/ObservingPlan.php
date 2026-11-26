<?php

namespace app\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "observing_plans".
 *
 * An observing plan: a list of sky objects made by a user. A public one can be seen by everybody.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property bool $is_public
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 * @property ObservingPlanItem[] $items
 */
class ObservingPlan extends \yii\db\ActiveRecord
{
    /** Maximal number of objects in a plan */
    const ITEM_LIMIT = 500;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'observing_plans';
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (!$insert) {
            $this->updated_at = new Expression('now()');
        }

        return true;
    }

    /**
     * @return \yii\db\ActiveQuery|UsersQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery|ObservingPlanItemsQuery
     */
    public function getItems()
    {
        return $this->hasMany(ObservingPlanItem::class, ['plan_id' => 'id'])->orderBy(['position' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /**
     * @return bool whether the visitor made the plan
     */
    public function isOwn()
    {
        return !Yii::$app->user->isGuest && (int)$this->user_id === (int)Yii::$app->user->id;
    }

    /**
     * @return bool whether the visitor may see the plan
     */
    public function isVisible()
    {
        return $this->is_public || $this->isOwn();
    }

    /**
     * {@inheritdoc}
     * @return ObservingPlansQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ObservingPlansQuery(get_called_class());
    }
}
