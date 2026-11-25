<?php
namespace app\components;

use app\models\Notification;
use app\models\UserBadge;
use Yii;
use yii\base\Application;

/**
 * Keeps the stored badges up to date. Whatever changes something that counts has to mark its user, the badges of the
 * marked users are counted again once, at the end of the request or the command.
 */
class BadgeSync
{
    /** @var array ids of the users as keys */
    private static $changedIds = [];

    /**
     * @param int|null $userId null if it does not belong to anybody
     */
    public static function markChanged($userId)
    {
        if ($userId === null) {
            return;
        }

        if (empty(self::$changedIds)) {
            Yii::$app->on(Application::EVENT_AFTER_REQUEST, [self::class, 'handleAfterRequest']);
        }

        self::$changedIds[(int)$userId] = true;
    }

    public static function handleAfterRequest()
    {
        Yii::$app->off(Application::EVENT_AFTER_REQUEST, [self::class, 'handleAfterRequest']);

        $userIds = array_keys(self::$changedIds);
        self::$changedIds = [];

        // the commands change a lot at once, nobody is notified about those
        $isWeb = Yii::$app instanceof \yii\web\Application;

        foreach ($userIds as $userId) {
            $titles = self::trySync($userId, null, $isWeb);

            if (!empty($titles) && $isWeb && $userId === (int)Yii::$app->user->id) {
                Flash::addInfo((count($titles) > 1 ? 'Új jelvényeid: ' : 'Új jelvényed: ') . implode(', ', $titles) . '.');
            }
        }
    }

    /**
     * A failure is only written to the log, it must not break what the user is doing.
     *
     * @param int $userId
     * @param array|null $categories see sync()
     * @param bool $notify see sync()
     * @return string[] titles of the new badges
     */
    public static function trySync(int $userId, ?array $categories = null, bool $notify = false)
    {
        try {
            return self::sync($userId, $categories, $notify);
        } catch (\Exception $e) {
            Yii::error($e, __METHOD__);

            return [];
        }
    }

    /**
     * Counts the badges of a user again and stores the differences.
     *
     * @param int $userId
     * @param array|null $categories result of Badges::findAll(), if it is known already
     * @param bool $notify whether the user gets a notification about every new badge
     * @return string[] titles of the new badges
     */
    public static function sync(int $userId, ?array $categories = null, bool $notify = false)
    {
        $stored = [];

        foreach (UserBadge::find()->ofUser($userId)->all() as $badge) {
            $stored["{$badge->category} {$badge->threshold}"] = $badge;
        }

        $titles = [];

        foreach ($categories ?? Badges::findAll($userId) as $category) {
            foreach (Badges::getEarnedLevels($category) as $level) {
                $key = "{$category['key']} {$level['threshold']}";
                $values = ['title' => $level['title'], 'tier' => $level['tier'], 'earned_at' => $level['date'] ?: null];
                $badge = $stored[$key] ?? null;
                unset($stored[$key]);

                if ($badge !== null && $badge->getAttributes(array_keys($values)) === $values) {
                    continue;
                }

                // another request may be storing the same one
                Yii::$app->db->createCommand()->upsert(
                    UserBadge::tableName(),
                    ['user_id' => $userId, 'category' => $category['key'], 'threshold' => $level['threshold']] + $values,
                    $values
                )->execute();

                if ($badge === null) {
                    $titles[] = $level['title'];

                    if ($notify) {
                        self::notify($userId, $category['key'], $level['threshold']);
                    }
                }
            }
        }

        if (!empty($stored)) {
            UserBadge::deleteAll(['id' => array_column($stored, 'id')]);
        }

        return $titles;
    }

    private static function notify($userId, $category, $threshold)
    {
        $badge = UserBadge::find()->ofUser($userId)->andWhere(['category' => $category, 'threshold' => $threshold])->one();

        if ($badge !== null) {
            Notification::createForBadge($badge);
        }
    }
}
