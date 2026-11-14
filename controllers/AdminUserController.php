<?php

declare(strict_types=1);

namespace app\controllers;

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
    private const ASSIGNABLE_ROLES = ['admin', 'staff', 'customer'];

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['user.assignRole'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'assign-role' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->layout = 'admin';

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => User::find()->orderBy(['id' => SORT_ASC]),
                'pagination' => ['pageSize' => 20],
            ]),
        ]);
    }

    public function actionAssignRole(int $id): Response
    {
        $roleName = $this->request->post('role');
        if (!is_string($roleName) || !in_array($roleName, self::ASSIGNABLE_ROLES, true)) {
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

        return $this->redirect(['index']);
    }
}
