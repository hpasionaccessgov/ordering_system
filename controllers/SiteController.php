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

class SiteController extends Controller
{
    public function __construct(
        $id,
        $module,
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
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'activate' => ['get', 'post']
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
        $model = new RegisterForm();

        if ($model->load($this->request->post()) && $model->validate()) {
            $user = new User();
            $user->username = $model->username;
            $user->email = mb_strtolower($model->email);
            $user->full_name = $model->fullName;
            $user->setPassword($model->password);
            $user->generateAuthKey();

            $token = Yii::$app->security->generateRandomString(64);
            $user->activation_token_hash = hash('sha256', $token);
            $user->activation_token_expires_at = time() + 86400;
            $user->status = User::STATUS_PENDING;

            if ($user->save()) {
                $activationUrl = Yii::$app->urlManager->createAbsoluteUrl([
                    'site/activate',
                    'id' => $user->id,
                    'token' => $token,
                ]);

                $sent = $this->mailer->compose()
                    ->setTo($user->email)
                    ->setFrom([
                        Yii::$app->params['senderEmail'] => Yii::$app->params['senderName'],
                    ])
                    ->setSubject('Activate your Ordering System account')
                    ->setTextBody(
                        "Activate your account using this link:\n\n{$activationUrl}\n\n"
                            . "This link will expire in 24 hours.",
                    )
                    ->send();

                if (!$sent) {
                    Yii::error("Activation email could not be sent for user ID {$user->id}.");
                    Yii::$app->session->setFlash('error', 'Your account was created, but the activation email could not be sent. Please contact support.');
                } else {
                    Yii::$app->session->setFlash(
                        'success',
                        'Registration received. Check your email for an activation link.',
                    );
                }

                return $this->redirect(['site/login']);
            }

            $model->addError('email', 'We could not create youyr account. Check your details and try again.');
        }

        return $this->render('registration', ['model' => $model]);
    }

    public function actionActivate(int $id, string $token): Response|string
    {
        $user = User::findOne([
            'id' => $id,
            'status' => User::STATUS_PENDING,
        ]);

        $validToken = $user !== null && $user->activation_token_hash !== null && $user->activation_token_expires_at !== null && $user->activation_token_expires_at >= time() && hash_equals(
            $user->activation_token_hash,
            hash('sha256', $token),
        );

        if (!$validToken) {
            Yii::$app->session->setFlash(
                'error',
                'This activation link is invalid, expired, or already used.',
            );
            return $this->redirect(['site/login']);
        }

        if ($this->request->isPost) {
            $user->status = User::STATUS_ACTIVE;
            $user->activation_token_hash = null;
            $user->activation_token_expires_at = null;

            if ($user->save(false, [
                'status',
                'activation_token_hash',
                'activation_token_expires_at',
            ])) {
                Yii::$app->session->setFlash(
                    'success',
                    'Your account is activated. You can now login.',
                );
                return $this->redirect(['site/login']);
            }

            $errors = $user->getFirstErrors();

            Yii::$app->session->setFlash(
                'error',
                $errors
                    ? implode(' ', array_values($errors))
                    : 'The account could not be saved. Check the application log.',
            );

            Yii::error([
                'message' => 'Registration user save failed.',
                'errors' => $user->getErrors(),
            ], __METHOD__);

            return $this->redirect(['site/login']);
        }

        return $this->render('activate', [
            'id' => $id,
            'token' => $token
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm($this->security);

        if ($model->load($this->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', ['model' => $model]);
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
