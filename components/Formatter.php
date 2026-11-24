<?php
namespace app\components;

use yii\helpers\Html;

/**
 * Application formatter, available as `Yii::$app->formatter`.
 */
class Formatter extends \yii\i18n\Formatter
{
    /**
     * Displays a timestamp relative to now ("15 perce", "12 órája", "3 napja", "2 hete", "5 hónapja", "3 éve"),
     * with the actual timestamp shown on hover or tap. Timestamps in the future are displayed as they are.
     *
     * In a view: `Yii::$app->formatter->asTimeAgo($model->created_at)`
     * In a GridView / DetailView: `'created_at:timeAgo'`
     *
     * Returns a `<time>` tag, so the result should not be encoded or placed inside a link
     * (the tooltip needs it to be focusable). Use `strip_tags()` to get the text only.
     * Values without a time part (date columns) are displayed as "ma" on the first day.
     * Stored values are expected to be in [[defaultTimeZone]] (UTC), same as the database.
     *
     * @param string|null $value timestamp, as it comes from the database
     * @param string|null $actualText text to display instead of $value as the actual timestamp
     * @return string
     */
    public function asTimeAgo($value, $actualText = null)
    {
        if ($value === null || $value === '') {
            return $this->nullDisplay;
        }

        $actualText = $actualText ?? (string)$value;
        $relativeText = $this->getRelativeText($value);

        if ($relativeText === null) {
            return Html::tag('time', Html::encode($actualText));
        }

        return Html::tag('time', $relativeText, [
            'title' => $actualText,
            'tabindex' => 0,
            'data-toggle' => 'tooltip',
        ]);
    }

    /**
     * @param string $value
     * @return string|null null if the value should be displayed as it is
     */
    private function getRelativeText($value)
    {
        list($date, $hasTime) = $this->normalizeDatetimeValue($value, true);
        $now = time();
        $seconds = $now - $date->getTimestamp();
        // a minute of tolerance, the clock of the database server may be slightly ahead
        if ($seconds < -60) {
            return null;
        }

        if (!$hasTime && $seconds < 86400) {
            return 'ma';
        }

        if ($seconds < 60) {
            return 'most';
        }

        if ($seconds < 3600) {
            return intdiv($seconds, 60) . ' perce';
        }

        if ($seconds < 86400) {
            return intdiv($seconds, 3600) . ' órája';
        }

        // both are whole seconds in UTC, so the age is always at least a day here
        $age = (new \DateTime('@' . $date->getTimestamp()))->diff(new \DateTime('@' . $now));

        if ($age->y > 0) {
            return $age->y . ' éve';
        }

        if ($age->m > 0) {
            return $age->m . ' hónapja';
        }

        if ($age->days >= 7) {
            return intdiv($age->days, 7) . ' hete';
        }

        return $age->days . ' napja';
    }
}
