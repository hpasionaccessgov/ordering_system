<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\Security;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class LoginForm extends Model
{
    private const MAX_ATTEMPTS = 5;
    private const THROTTLE_SECONDS = 60;

    public string $username = '';
    public string $password = '';
    public bool $rememberMe = true;
    private User|null $_user = null;
    private bool $_userLoaded = false;
    public function __construct(private readonly Security $security, $config = [])
    {
        parent::__construct($config);
    }

    /**
     * @return array the validation rules.
     */
    public function rules(): array
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword(string $attribute, array|null $params): void
    {
        if (!$this->hasErrors()) {
            $attempt = $this->attemptState();
            $remaining = $attempt['blocked_until'] - time();
            if ($remaining > 0) {
                $this->addError(
                    $attribute,
                    "Too many login attempts. Try again in {$remaining} seconds.",
                );

                return;
            }

            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->recordFailedAttempt($attempt);
                $this->addError($attribute, 'Incorrect username or password.');

                return;
            }

            if ((int) $user->status !== User::STATUS_ACTIVE) {
                $this->recordFailedAttempt($attempt);
                $this->addError(
                    $attribute,
                    (int) $user->status === User::STATUS_DISABLED
                        ? 'This account has been disabled. Contact an administrator.'
                        : 'This account is inactive. Contact an administrator.',
                );
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login(): bool
    {
        if ($this->validate()) {
            Yii::$app->cache->delete($this->attemptKey());
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }

        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser(): User|null
    {
        if (!$this->_userLoaded) {
            $this->_user = User::findByLoginIdentifier($this->username);
            $this->_userLoaded = true;
        }

        return $this->_user;
    }

    /**
     * @return array{failures: int, blocked_until: int}
     */
    private function attemptState(): array
    {
        $state = Yii::$app->cache->get($this->attemptKey());
        if (
            !is_array($state)
            || !isset($state['failures'], $state['blocked_until'])
        ) {
            return ['failures' => 0, 'blocked_until' => 0];
        }

        return [
            'failures' => (int) $state['failures'],
            'blocked_until' => (int) $state['blocked_until'],
        ];
    }

    /**
     * @param array{failures: int, blocked_until: int} $attempt
     */
    private function recordFailedAttempt(array $attempt): void
    {
        $attempt['failures']++;
        if ($attempt['failures'] >= self::MAX_ATTEMPTS) {
            $attempt['blocked_until'] = time() + self::THROTTLE_SECONDS;
            $attempt['failures'] = 0;
        }
        Yii::$app->cache->set(
            $this->attemptKey(),
            $attempt,
            self::THROTTLE_SECONDS,
        );
    }

    private function attemptKey(): string
    {
        $request = Yii::$app->request;
        $ip = method_exists($request, 'getUserIP')
            ? (string) $request->getUserIP()
            : '';

        return 'login-attempt:' . hash(
            'sha256',
            mb_strtolower(trim($this->username)) . '|' . $ip,
        );
    }
}
