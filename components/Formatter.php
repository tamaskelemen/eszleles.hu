<?php
namespace app\components;

use yii\helpers\Html;

/**
 * Application formatter, available as `Yii::$app->formatter`.
 */
class Formatter extends \yii\i18n\Formatter
{
    /**
     * Displays a timestamp relative to now if it is less than 24 hours old ("15 perce", "12 órája"),
     * with the actual timestamp shown on hover or tap. Otherwise displays it as it is.
     *
     * In a view: `Yii::$app->formatter->asTimeAgo($model->created_at)`
     * In a GridView / DetailView: `'created_at:timeAgo'`
     *
     * Returns a `<time>` tag, so the result should not be encoded or placed inside a link
     * (the tooltip needs it to be focusable). Use `strip_tags()` to get the text only.
     * Values without a time part (date columns) are always displayed as they are.
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
        if (!$hasTime) {
            return null;
        }

        $seconds = time() - $date->getTimestamp();
        // a minute of tolerance, the clock of the database server may be slightly ahead
        if ($seconds < -60 || $seconds >= 86400) {
            return null;
        }

        if ($seconds < 60) {
            return 'most';
        }

        if ($seconds < 3600) {
            return intdiv($seconds, 60) . ' perce';
        }

        return intdiv($seconds, 3600) . ' órája';
    }
}
