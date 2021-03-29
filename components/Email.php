<?php
namespace app\components;

class Email
{
    /**
     * @param string|array $to
     * @param $subject
     * @param $message
     * @param array $params
     */
    public static function send($to, $subject, $message, $params = [])
    {
        if (YII_ENV === 'dev') {
            $to = 'tamaskelemen.kt@gmail.com';
        }

        $result = \Yii::$app->mailer->compose('password-reset', $params)
            ->setFrom('no-reply@eszleles.hu')
            ->setTo($to)
            ->setSubject($subject)
            ->send();

        return $result;
    }
}