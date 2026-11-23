<?php

declare(strict_types=1);

namespace app\tests\Unit\Controllers;

use app\controllers\AdminUserController;
use app\models\User;
use Codeception\Test\Unit;
use Yii;
use yii\rbac\DbManager;

final class AdminUserRoleAssignmentTest extends Unit
{
    public function testRoleAssignmentUpdatesRbacAndUserAuditFields(): void
    {
        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        Yii::$app->set('authManager', DbManager::class);
        $actor = $this->createUser('audit_actor_' . $suffix);
        $target = $this->createUser('audit_target_' . $suffix);
        $auth = Yii::$app->authManager;
        $auth->assign($auth->getRole('customer'), $target->id);
        User::updateAll(['updated_at' => 1, 'updated_by' => null], ['id' => $target->id]);
        $target->refresh();
        Yii::$app->user->setIdentity($actor);
        Yii::$app->request->setBodyParams(['role' => 'staff']);

        (new AdminUserController('admin-user', Yii::$app))->actionAssignRole((int) $target->id);

        $target->refresh();
        self::assertSame(['staff'], array_keys($auth->getRolesByUser($target->id)));
        self::assertSame((int) $actor->id, (int) $target->updated_by);
        self::assertGreaterThan(1, (int) $target->updated_at);
        $updatedAt = $target->updated_at;

        (new AdminUserController('admin-user', Yii::$app))->actionAssignRole((int) $target->id);

        $target->refresh();
        self::assertSame($updatedAt, $target->updated_at);
    }

    private function createUser(string $username): User
    {
        $user = new User([
            'username' => $username,
            'email' => $username . '@audit.test',
            'full_name' => $username,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('AuditRole123!');
        $user->generateAuthKey();
        self::assertTrue($user->save(), json_encode($user->getErrors()));

        return $user;
    }
}
