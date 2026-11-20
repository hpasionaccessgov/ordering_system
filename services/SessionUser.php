<?php

declare(strict_types=1);

namespace app\services;

use app\models\ApplicationSettings;
use Yii;
use yii\web\Cookie;
use yii\web\User;

class SessionUser extends User
{
    private const LAST_ACTIVITY = '__last_user_activity';
    private bool $expiredOnRenew = false;

    public function init()
    {
        $this->authTimeout = (int) ApplicationSettings::current()->idle_minutes * 60;
        parent::init();
    }

    public function switchIdentity($identity, $duration = 0)
    {
        parent::switchIdentity($identity, $duration);
        if ($this->enableSession) {
            if ($identity === null) {
                Yii::$app->session->remove(self::LAST_ACTIVITY);
            } else {
                Yii::$app->session->set(self::LAST_ACTIVITY, time());
            }
        }
    }

    protected function renewAuthStatus()
    {
        $session = Yii::$app->session;
        $hasSession = $session->getHasSessionId() || $session->getIsActive();
        $lastActivity = $hasSession ? $session->get(self::LAST_ACTIVITY) : null;
        if ($lastActivity !== null) {
            $session->set($this->authTimeoutParam, (int) $lastActivity + $this->authTimeout);
        }
        $expiry = $hasSession ? $session->get($this->authTimeoutParam) : null;
        $this->expiredOnRenew = $expiry !== null && $expiry < time();
        [$route] = Yii::$app->request->resolve();
        $background = in_array($route, ['notification/unread-count', 'session/status'], true);
        $autoRenew = $this->autoRenewCookie;
        if ($background) {
            $this->autoRenewCookie = false;
        }
        try {
            parent::renewAuthStatus();
        } finally {
            $this->autoRenewCookie = $autoRenew;
        }
        if (!$this->getIsGuest()) {
            if ($background && $expiry !== null) {
                $session->set($this->authTimeoutParam, $expiry);
            } elseif (!$background || $lastActivity === null) {
                $session->set(self::LAST_ACTIVITY, time());
            }
        }
    }

    protected function loginByCookie()
    {
        if ($this->expiredOnRenew) {
            return;
        }
        $cookie = $this->getIdentityAndDurationFromCookie();
        if (isset($cookie['duration']) && $cookie['duration'] > $this->authTimeout) {
            $this->removeIdentityCookie();
            return;
        }
        parent::loginByCookie();
    }

    protected function getIdentityAndDurationFromCookie()
    {
        $data = parent::getIdentityAndDurationFromCookie();
        if ($data === null) {
            return null;
        }
        // Browser cookie expiry alone does not prevent replay of an expired cookie.
        if (
            !is_string($data['duration'])
            || !preg_match('/^([1-9][0-9]*):([1-9][0-9]*)$/D', $data['duration'], $matches)
            || (int) $matches[2] <= time()
        ) {
            $this->removeIdentityCookie();
            return null;
        }
        $data['duration'] = (int) $matches[1];
        return $data;
    }

    protected function sendIdentityCookie($identity, $duration)
    {
        $expires = time() + $duration;
        $cookie = Yii::createObject(array_merge($this->identityCookie, [
            'class' => Cookie::class,
            'value' => json_encode([
                $identity->getId(),
                $identity->getAuthKey(),
                $duration . ':' . $expires,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'expire' => $expires,
        ]));
        Yii::$app->response->cookies->add($cookie);
    }

    protected function renewIdentityCookie()
    {
        $data = $this->getIdentityAndDurationFromCookie();
        if ($data !== null) {
            $this->sendIdentityCookie($data['identity'], min($data['duration'], $this->authTimeout));
        }
    }
}
