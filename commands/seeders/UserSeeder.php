<?php

declare(strict_types=1);

namespace app\commands\seeders;

use app\models\User;
use app\services\AdminAccessRecovery;
use Yii;

class UserSeeder
{
    /**
     * Add local test accounts without modifying existing user records.
     *
     * @return int number of users inserted
     */
    public function run(): int
    {
        $records = [
            ['seed_admin', 'seed.admin@cafebara.test', 'Seed Admin', 'admin'],
            ['seed_staff', 'seed.staff@cafebara.test', 'Seed Staff', 'staff'],
            ['seed_customer', 'seed.customer@cafebara.test', 'Seed Customer', 'customer'],
        ];
        $added = 0;
        $auth = Yii::$app->authManager;
        AdminAccessRecovery::restoreAdminRoleAccess();

        foreach ($records as [$username, $email, $fullName, $roleName]) {
            $user = User::find()->where(['or', ['username' => $username], ['email' => $email]])->one();
            if ($user === null) {
                $user = new User([
                    'username' => $username,
                    'email' => $email,
                    'full_name' => $fullName,
                    'status' => User::STATUS_ACTIVE,
                ]);
                $user->setPassword('CafebaraTest123!');
                $user->generateAuthKey();

                if (!$user->save()) {
                    throw new \RuntimeException("Could not seed user {$username}: " . json_encode($user->getErrors()));
                }
                $added++;
            }

            $role = $auth->getRole($roleName);
            if ($role === null) {
                throw new \RuntimeException("RBAC role {$roleName} does not exist. Run migrations first.");
            }

            // Repair accounts left over from an earlier seed run with no RBAC assignment.
            if ($auth->getRolesByUser($user->getId()) === []) {
                $auth->assign($role, $user->getId());
            }
        }

        return $added;
    }
}
