<?php
namespace app\components;

use Yii;
use yii\authclient\AuthAction;
use yii\web\NotFoundHttpException;

/**
 * Shows the failures on the login page instead of an error page.
 */
class SocialAuthAction extends AuthAction
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        try {
            return parent::run();
        } catch (NotFoundHttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Yii::error($e, __METHOD__);
            Flash::addWarning("A belépés nem sikerült, kérlek próbáld újra.");

            return $this->controller->redirect(['/site/login']);
        }
    }
}
