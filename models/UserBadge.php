<?php

namespace app\models;

use app\components\Badges;

/**
 * This is the model class for table "user_badges".
 *
 * A badge the user has earned. It is written by BadgeSync only.
 *
 * @property int $id
 * @property int $user_id
 * @property string $category
 * @property int $threshold
 * @property string $title
 * @property int $tier color of the badge
 * @property string|null $earned_at null if it is not known
 *
 * @property User $user
 */
class UserBadge extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_badges';
    }

    /**
     * @return \yii\db\ActiveQuery|UsersQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return array see Badges::getCategories(), a removed category has a general name
     */
    public function getCategoryData()
    {
        return Badges::getCategories()[$this->category]
            ?? ['key' => $this->category, 'group' => Badges::GROUP_ACTIVITY, 'name' => 'Jelvény', 'glyph' => null];
    }

    /**
     * @return array level of a badge category, see Badges::build()
     */
    public function getLevel()
    {
        return [
            'threshold' => $this->threshold,
            'title' => $this->title,
            'tier' => $this->tier,
            'isEarned' => true,
            'date' => $this->earned_at,
        ];
    }

    /**
     * @param UserBadge[] $badges of one user
     * @return UserBadge[] the highest one of every category, in the order of the categories
     */
    public static function filterHighest(array $badges)
    {
        $highest = [];

        foreach ($badges as $badge) {
            $other = $highest[$badge->category] ?? null;

            if ($other === null || $other->threshold < $badge->threshold) {
                $highest[$badge->category] = $badge;
            }
        }

        // the ones of a removed category are left out
        return array_values(array_replace(
            array_intersect_key(Badges::getCategories(), $highest),
            array_intersect_key($highest, Badges::getCategories())
        ));
    }

    /**
     * {@inheritdoc}
     * @return UserBadgesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserBadgesQuery(get_called_class());
    }
}
