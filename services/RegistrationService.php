<?php

declare(strict_types=1);

namespace app\services;

use app\models\RegisterForm;
use app\models\User;
use Yii;
use yii\base\InvalidConfigException;

final class RegistrationService
{
    public function register(RegisterForm $form): User
    {
        $user = new User();
        $user->username = $form->username;
        $user->email = mb_strtolower($form->email);
        $user->full_name = $form->fullName;
        $user->setPassword($form->password);
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;

        $customerRole = Yii::$app->authManager->getRole('customer');
        if ($customerRole === null) {
            throw new InvalidConfigException(
                'The customer RBAC role is missing. Run the RBAC migrations.'
            );
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$user->save()) {
                $transaction->rollBack();

                return $user;
            }

            // Guest registration: no logged-in user for BlameableBehavior,
            // so credit the account to itself.
            $user->updateAttributes([
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
            Yii::$app->authManager->assign($customerRole, $user->getId());
            $transaction->commit();
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            throw $exception;
        }

        return $user;
    }
}
