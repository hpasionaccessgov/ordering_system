<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\AdminUserCreateForm;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AdminUserController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['create', 'update'],
                        'allow' => true,
                        'roles' => ['admin.access'],
                    ],
                    [
                        'actions' => ['index', 'list', 'assign-role'],
                        'allow' => true,
                        'roles' => ['user.assignRole'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['GET', 'POST'],
                    'assign-role' => ['post'],
                    'update' => ['GET', 'POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        return $this->actionList();
    }

    public function actionList(): string
    {
        $this->layout = 'admin';

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => User::find()->orderBy(['id' => SORT_ASC]),
                'pagination' => ['pageSize' => 20],
            ]),
            'roles' => Yii::$app->authManager->getRoles(),
        ]);
    }

    public function actionCreate(): Response|string
    {
        $this->layout = 'admin';
        $model = new AdminUserCreateForm();
        $auth = Yii::$app->authManager;
        $roles = $auth->getRoles();

        if ($model->load($this->request->post()) && $model->validate()) {
            $role = $auth->getRole($model->roleName);
            if ($role === null) {
                $model->addError(
                    'roleName',
                    'The selected role no longer exists. Reload the page and choose another role.',
                );
            } else {
                $user = new User();
                $user->username = $model->username;
                $user->email = mb_strtolower($model->email);
                $user->full_name = $model->fullName;
                $user->setPassword($model->password);
                $user->generateAuthKey();
                $user->status = User::STATUS_ACTIVE;

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if (!$user->save(false)) {
                        throw new \RuntimeException('The user account could not be saved.');
                    }

                    $auth->assign($role, $user->id);
                    $transaction->commit();
                } catch (\Throwable $exception) {
                    if ($transaction->isActive) {
                        $transaction->rollBack();
                    }

                    throw $exception;
                }

                Yii::$app->session->setFlash('success', "Account for {$user->username} was created.");

                return $this->redirect(['list']);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'roles' => $roles,
        ]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $user = User::findOne($id);
        if ($user === null) {
            throw new NotFoundHttpException('The requested user was not found.');
        }

        $this->layout = 'admin';
        $user->scenario = User::SCENARIO_ADMIN_PROFILE_UPDATE;

        if ($user->load($this->request->post())) {
            if (
                (string) $user->id === (string) Yii::$app->user->id
                && (int) $user->status !== User::STATUS_ACTIVE
            ) {
                throw new ForbiddenHttpException('You cannot block your own account.');
            }

            if ($user->save()) {
                Yii::$app->session->setFlash('success', "Account details for {$user->username} were updated.");

                return $this->redirect(['list']);
            }
        }

        return $this->render('update', ['model' => $user]);
    }

    public function actionRoles(): string
    {
        $this->layout = 'admin';

        return $this->render('roles', [
            'users' => User::find()->orderBy(['id' => SORT_ASC])->all(),
        ]);
    }

    public function actionAssignRole(int $id): Response
    {
        $roleName = $this->request->post('role');
        if (!is_string($roleName) || $roleName === '') {
            throw new BadRequestHttpException('Select a valid role.');
        }

        $user = User::findOne($id);
        if ($user === null) {
            throw new NotFoundHttpException('The requested user was not found.');
        }

        if ((string) $user->id === (string) Yii::$app->user->id) {
            throw new ForbiddenHttpException('You cannot change your own role.');
        }

        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);
        if ($role === null) {
            throw new BadRequestHttpException('The selected RBAC role does not exist. Run the RBAC migrations.');
        }

        $currentRoles = $auth->getRolesByUser($user->id);
        if (isset($currentRoles['admin']) && $roleName !== 'admin') {
            $adminIds = $auth->getUserIdsByRole('admin');
            if (count($adminIds) <= 1) {
                throw new ForbiddenHttpException('The last administrator cannot be demoted.');
            }
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $auth->revokeAll($user->id);
            $auth->assign($role, $user->id);
            $transaction->commit();
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            throw $exception;
        }

        Yii::$app->session->setFlash('success', "The role for {$user->username} was updated.");

        return $this->redirect(['list']);
    }
}
