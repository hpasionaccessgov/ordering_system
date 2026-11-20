<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\AdminUserCreateForm;
use app\models\Order;
use app\models\User;
use app\models\UserSearch;
use app\services\AdminAccessRecovery;
use Yii;
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
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['user.create'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['user.update'],
                    ],
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['user.view'],
                    ],
                    [
                        'actions' => ['index', 'list'],
                        'allow' => true,
                        'roles' => ['user.index'],
                    ],
                    [
                        'actions' => ['assign-role'],
                        'allow' => true,
                        'roles' => ['user.assignRole'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['user.delete'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['GET', 'POST'],
                    'assign-role' => ['post'],
                    'view' => ['get'],
                    'update' => ['GET', 'POST'],
                    'delete' => ['post'],
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
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'roles' => Yii::$app->authManager->getRoles(),
            'search' => $searchModel->q,
        ]);
    }

    public function actionView(int $id): string
    {
        $user = User::findOne($id);
        if ($user === null) {
            throw new NotFoundHttpException('The requested user was not found.');
        }

        $this->layout = 'admin';

        return $this->render('view', [
            'model' => $user,
            'roles' => array_keys(Yii::$app->authManager->getRolesByUser($user->id)),
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

                    if ($model->roleName === 'admin') {
                        AdminAccessRecovery::restoreAdminRoleAccess();
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

                return $this->redirect($this->getUserReturnRoute());
            }
        }

        return $this->render('create', [
            'model' => $model,
            'roles' => $roles,
        ]);
    }

    public function actionUpdate(int $id): Response|string
    {
        if (!Yii::$app->user->can('user.view')) {
            throw new ForbiddenHttpException('You are not allowed to view this account.');
        }

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

                return $this->redirect($this->getUserReturnRoute());
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
        $roleChanged = count($currentRoles) !== 1 || !isset($currentRoles[$roleName]);
        if (!$roleChanged) {
            Yii::$app->session->setFlash('info', "{$user->username} already has this role.");

            return $this->redirect($this->getUserReturnRoute());
        }
        if (isset($currentRoles['admin']) && $roleName !== 'admin') {
            $adminIds = $auth->getUserIdsByRole('admin');
            if (count($adminIds) <= 1) {
                throw new ForbiddenHttpException('The last administrator cannot be demoted.');
            }
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($roleName === 'admin') {
                AdminAccessRecovery::restoreAdminRoleAccess();
            }
            $auth->revokeAll($user->id);
            $auth->assign($role, $user->id);
            if ($roleChanged) {
                $user->updated_by = (int) Yii::$app->user->id;
                if (!$user->save(false, ['updated_at', 'updated_by'])) {
                    throw new \RuntimeException('The role audit information could not be saved.');
                }
            }
            $transaction->commit();
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            throw $exception;
        }

        Yii::$app->session->setFlash('success', "The role for {$user->username} was updated.");

        return $this->redirect($this->getUserReturnRoute());
    }

    public function actionDelete(int $id): Response
    {
        if ((string) $id === (string) Yii::$app->user->id) {
            throw new ForbiddenHttpException('You cannot delete your own account.');
        }

        $auth = Yii::$app->authManager;
        $user = User::findOne($id);
        if ($user === null) {
            throw new NotFoundHttpException('The requested user was not found.');
        }

        $currentRoles = $auth->getRolesByUser($user->id);
        if (isset($currentRoles['admin']) && count($auth->getUserIdsByRole('admin')) <= 1) {
            Yii::$app->session->setFlash('error', 'The last administrator cannot be deleted.');

            return $this->redirect($this->getUserReturnRoute());
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $lockedId = Yii::$app->db->createCommand(
                'SELECT id FROM {{%users}} WHERE id = :id FOR UPDATE',
                [':id' => $id],
            )->queryScalar();

            if ($lockedId === false) {
                throw new NotFoundHttpException('The requested user was not found.');
            }

            $user = User::findOne($lockedId);
            if ($user === null) {
                throw new NotFoundHttpException('The requested user was not found.');
            }

            if (Order::find()->where(['user_id' => $user->id])->exists()) {
                $transaction->rollBack();
                Yii::$app->session->setFlash(
                    'error',
                    'This account has order history and cannot be permanently deleted.',
                );

                return $this->redirect($this->getUserReturnRoute());
            }

            $auth->revokeAll($user->id);
            if ($user->delete() === false) {
                throw new BadRequestHttpException('The user account could not be deleted.');
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            throw $exception;
        }

        Yii::$app->session->setFlash('success', "Account '{$user->username}' was deleted.");

        return $this->redirect($this->getUserReturnRoute());
    }

    /**
     * @return array|string
     */
    private function getUserReturnRoute(): array|string
    {
        return Yii::$app->user->can('user.index')
            ? ['list']
            : ['/site/dashboard'];
    }
}
