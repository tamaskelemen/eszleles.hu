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
    public static function send($to, $subject, $message, array $params = [])
    {
        if (YII_ENV === 'dev') {
            $to = 'tamaskelemen.kt@gmail.com';
        }

        $result = \Yii::$app->mailer->compose($message, ['params' => $params])
            ->setFrom('info@eszleles.hu')
            ->setTo($to)
            ->setSubject($subject .  " - Észlelés.hu")
            ->send();

        return $result;
    }
}