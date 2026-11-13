<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\ContactForm;
use app\models\LoginForm;
use app\models\RegisterForm;
use app\models\User;
use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\base\Security;
use yii\mail\MailerInterface;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Response;
use app\models\ChangePasswordForm;
use app\models\ProfileForm;
use yii\web\ForbiddenHttpException;
use app\models\DeactivateForm;

class SiteController extends Controller
{

    private function getCurrentUser(): User
    {
        $user = Yii::$app->user->identity;

        if (!$user instanceof User) {
            throw new ForbiddenHttpException();
        }

        return $user;
    }

    public function __construct(
        mixed $id,
        mixed $module,
        private readonly MailerInterface $mailer,
        private readonly Security $security,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'profile', 'change-password', 'deactivate', 'dashboard'],
                'rules' => [
                    [
                        'actions' => ['logout', 'profile', 'change-password', 'deactivate', 'dashboard'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'profile' => ['get', 'post'],
                    'change-password' => ['get', 'post'],
                    'deactivate' => ['get', 'post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
            'captcha' => [
                'class' => CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'transparent' => true,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    /**
     * Displays registration page.
     *
     * @return Response|string
     */
    public function actionRegister(): Response|string
    {
        $this->layout = 'auth';

        $model = new RegisterForm();

        if ($model->load($this->request->post()) && $model->validate()) {
            $user = new User();
            $user->username = $model->username;
            $user->email = mb_strtolower($model->email);
            $user->full_name = $model->fullName;
            $user->setPassword($model->password);
            $user->generateAuthKey();
            $user->status = User::STATUS_ACTIVE;

            $customerRole = Yii::$app->authManager->getRole('customer');
            if ($customerRole === null) {
                throw new \yii\base\InvalidConfigException(
                    'The customer RBAC role is missing. Run the RBAC migrations.'
                );
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $registrationSaved = $user->save();

                if ($registrationSaved) {
                    Yii::$app->authManager->assign($customerRole, $user->getId());
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                }
            } catch (\Throwable $exception) {
                if ($transaction->isActive) {
                    $transaction->rollBack();
                }

                throw $exception;
            }

            if ($registrationSaved) {
                if (Yii::$app->user->login($user)) {
                    Yii::$app->session->setFlash(
                        'success',
                        'Your account was created and you are now logged in.',
                    );

                    return $this->redirect(['site/dashboard']);
                }

                Yii::error(
                    "Registration succeeded but login failed for user ID {$user->id}.",
                    __METHOD__,
                );
                Yii::$app->session->setFlash(
                    'error',
                    'Your account was created, but automatic login failed. Please log in.',
                );

                return $this->redirect(['site/login']);
            }

            Yii::error([
                'message' => 'Registration user save failed.',
                'errors' => $user->getErrors(),
            ], __METHOD__);

            $model->addError(
                'username',
                'We could not create your account. Check the submitted details and try again.',
            );
        }

        return $this->render('registration', ['model' => $model]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin(): Response|string
    {
        $this->layout = 'auth';

        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/dashboard']);
        }

        $model = new LoginForm($this->security);

        if ($model->load($this->request->post()) && $model->login()) {
            return $this->redirect(['site/dashboard']);
        }

        $model->password = '';

        return $this->render('login', ['model' => $model]);
    }

    /** 
     * Dashboard action
     * @return Response|string
     */
    public function actionDashboard(): Response|string
    {
        $this->layout = 'admin';

        if (Yii::$app->user->can('admin.access')) {
            return $this->redirect(['/admin/dashboard']);
        }

        if (Yii::$app->user->can('staff.access')) {
            return $this->redirect(['/staff/dashboard']);
        }

        if (Yii::$app->user->can('customer.access')) {
            return $this->redirect(['/customer/dashboard']);
        }

        throw new ForbiddenHttpException('No application role is assigned to this account.');
    }

    /** 
     * Profile action
     * 
     * @return Response|string
     */
    public function actionProfile(): Response|string
    {
        $this->layout = 'admin';
        $user = $this->getCurrentUser();
        $model = new ProfileForm(['fullName' => $user->full_name]);

        if ($model->load($this->request->post()) && $model->validate()) {
            $user->full_name = $model->fullName;

            if ($user->save(false, ['full_name', 'updated_at'])) {
                Yii::$app->session->setFlash('success', 'Your profile was update.');
                return $this->refresh();
            }

            Yii::error("Could not update profile for user ID {$user->id}.", __METHOD__);
            Yii::$app->session->setFlash('error', 'Your profile could not be updated.');
        }

        return $this->render('user_profile', [
            'user' => $user,
            'model' => $model,
        ]);
    }

    /**
     * Change password action
     * 
     * @return Response|string
     */
    public function actionChangePassword(): Response|string
    {
        $this->layout = 'admin';
        $user = $this->getCurrentUser();
        $model = new ChangePasswordForm($user);

        if ($model->load($this->request->post()) && $model->validate()) {
            $user->setPassword($model->newPassword);
            $user->generateAuthKey();

            if ($user->save(false, ['password_hash', 'auth_key', 'updated_at'])) {
                Yii::$app->user->logout();
                Yii::$app->session->setFlash(
                    'success',
                    'Your password was changed. Please login again.'
                );
                return $this->redirect(['site/login']);
            }

            Yii::error("Could not change password for user ID {$user->id}.", __METHOD__);
            Yii::$app->session->setFlash('error', 'Your password could not be changed.');
        }

        return $this->render('change_password', ['model' => $model]);
    }

    /** 
     * Deaction aciton
     * 
     * @return Response|string
     */
    public function actionDeactivate(): Response|string
    {
        $this->layout = 'admin';
        $user = $this->getCurrentUser();
        $model = new DeactivateForm($user);

        if ($model->load($this->request->post()) && $model->validate()) {
            $user->status = User::STATUS_DISABLED;

            if ($user->save(false, ['status', 'updated_at'])) {
                Yii::$app->user->logout();
                Yii::$app->session->setFlash(
                    'success',
                    'Your account has been deactivated.',
                );

                return $this->redirect(['site/login']);
            }

            Yii::error("Could not deactivate account for user ID {$user->id}.", __METHOD__);
            Yii::$app->session->setFlash('error', 'Your account could not be deactivated.');
        }

        return $this->render('deactivate', ['model' => $model]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact(): Response|string
    {
        $model = new ContactForm();

        $contact = $model->load($this->request->post()) && $model->contact(
            $this->mailer,
            Yii::$app->params['adminEmail'],
            Yii::$app->params['senderEmail'],
            Yii::$app->params['senderName'],
        );

        if ($contact) {
            Yii::$app->session->setFlash(
                'success',
                'Thank you for contacting us. We will respond to you as soon as possible.',
            );

            return $this->refresh();
        }

        return $this->render('contact', ['model' => $model]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout(): string
    {
        return $this->render('about');
    }
}
