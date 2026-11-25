<?php
namespace app\components;

use app\models\Activity;
use app\models\SocialAccount;
use app\models\User;
use Yii;
use yii\authclient\ClientInterface;
use yii\db\Expression;

/**
 * Login and registration with the external accounts configured in config/auth_clients.php.
 *
 * If the external account does not belong to a user yet, its profile is kept in the session (pending profile) until
 * the registration is finished or the user logs in to an existing account. It is never connected to an existing
 * account by the email address alone: the addresses of the users are not verified.
 */
class SocialLogin
{
    const SESSION_KEY = 'pendingSocialProfile';
    const PENDING_EXPIRE = 3600;
    const LOGIN_DURATION = 3600 * 24 * 30;

    /**
     * @return ClientInterface[]
     */
    public static function getClients()
    {
        return Yii::$app->authClientCollection->getClients();
    }

    /**
     * @param string $provider
     * @return string
     */
    public static function getProviderName($provider)
    {
        $clients = self::getClients();

        return isset($clients[$provider]) ? $clients[$provider]->getTitle() : $provider;
    }

    /**
     * @param ClientInterface $client
     * @return array attributes of a social account
     * @throws \Exception
     */
    public static function getProfile(ClientInterface $client)
    {
        $attributes = $client->getUserAttributes();

        if (empty($attributes['id'])) {
            throw new \Exception("A külső fiók azonosítója hiányzik.");
        }

        return [
            'provider' => $client->getId(),
            'provider_user_id' => (string)$attributes['id'],
            'email' => ($attributes['email'] ?? null) ?: null,
            'name' => ($attributes['name'] ?? null) ?: null,
            'picture_url' => ($attributes['picture'] ?? null) ?: null,
        ];
    }

    /**
     * @param array $profile
     * @return SocialAccount|null
     */
    public static function findAccount(array $profile)
    {
        return SocialAccount::find()
            ->ofProvider($profile['provider'])
            ->ofProviderUser($profile['provider_user_id'])
            ->one();
    }

    /**
     * @param User $user
     * @param array $profile
     * @throws \Exception
     */
    public static function connect(User $user, array $profile)
    {
        $account = new SocialAccount($profile);
        $account->user_id = $user->id;

        if (!$account->save()) {
            throw new \Exception("A külső fiókot nem sikerült menteni.");
        }
    }

    /**
     * @param User $user
     * @return bool whether the user is logged in successfully
     */
    public static function login(User $user)
    {
        if (!Yii::$app->user->login($user, self::LOGIN_DURATION)) {
            return false;
        }

        $user->updateAttributes(['last_login' => new Expression("NOW()")]);
        Activity::log($user->id, Activity::TYPE_LOGIN_SOCIAL);

        return true;
    }

    /**
     * @param array $profile
     */
    public static function setPending(array $profile)
    {
        Yii::$app->session->set(self::SESSION_KEY, [
            'profile' => $profile,
            'isConnectRequested' => false,
            'expires' => time() + self::PENDING_EXPIRE,
        ]);
    }

    /**
     * The pending profile will be connected to the account the user logs in to, even if the email addresses differ.
     */
    public static function requestConnect()
    {
        if (self::getPending() !== null) {
            $pending = Yii::$app->session->get(self::SESSION_KEY);
            $pending['isConnectRequested'] = true;
            Yii::$app->session->set(self::SESSION_KEY, $pending);
        }
    }

    /**
     * @return array|null the pending profile
     */
    public static function getPending()
    {
        $pending = Yii::$app->session->get(self::SESSION_KEY);

        return $pending !== null && $pending['expires'] > time() ? $pending['profile'] : null;
    }

    public static function clearPending()
    {
        Yii::$app->session->remove(self::SESSION_KEY);
    }

    /**
     * Call it after the user logged in to an existing account. A failure must not break the login.
     *
     * @param User $user
     */
    public static function connectPending(User $user)
    {
        $profile = self::getPending();
        $isConnectRequested = Yii::$app->session->get(self::SESSION_KEY)['isConnectRequested'] ?? false;
        self::clearPending();

        if ($profile === null) {
            return;
        }

        $isSameEmail = $profile['email'] !== null
            && mb_strtolower($profile['email']) === mb_strtolower((string)$user->email);

        // someone else may have logged in on the same computer
        if (!$isConnectRequested && !$isSameEmail) {
            return;
        }

        $name = self::getProviderName($profile['provider']);

        try {
            if (SocialAccount::find()->ofUser($user->id)->ofProvider($profile['provider'])->exists()) {
                Flash::addWarning("Ehhez a profilhoz már egy másik {$name} fiók tartozik.");
                return;
            }

            self::connect($user, $profile);
            Activity::log($user->id, Activity::TYPE_SOCIAL_CONNECT);
            Flash::addSuccess("Összekapcsoltuk a profilodat a {$name} fiókoddal, ezután azzal is beléphetsz.");
        } catch (\Exception $e) {
            Yii::error($e, __METHOD__);
            Flash::addWarning("A {$name} fiók összekapcsolása nem sikerült.");
        }
    }
}
