<?php

declare(strict_types=1);

namespace app\commands;

use app\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\console\ExitCode;

class RbacController extends Controller
{
    public function actionMakeAdmin(string $email): int
    {
        $user = User::find()->where(['email' => mb_strtolower(trim($email))])->one();
        if ($user === null) {
            throw new \yii\console\Exception("No user found with email {$email}.");
        }

        $auth = Yii::$app->authManager;
        $adminRole = $auth->getRole('admin');
        if ($adminRole === null) {
            throw new InvalidConfigException('The admin RBAC role is missing. Run the RBAC migrations first.');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $auth->revokeAll($user->id);
            $auth->assign($adminRole, $user->id);
            $transaction->commit();
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            throw $exception;
        }

        $this->stdout("Admin role assigned to {$user->email}.\n");

        return ExitCode::OK;
    }
}
