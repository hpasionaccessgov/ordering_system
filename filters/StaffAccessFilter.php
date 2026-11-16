<?php

declare(strict_types=1);

namespace app\filters;

use app\services\RbacPermissionPolicy;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;

final class StaffAccessFilter extends ActionFilter
{
    public function beforeAction($action): bool
    {
        if (RbacPermissionPolicy::isStaffAccessDisabledForCurrentUser()) {
            throw new ForbiddenHttpException('Staff access has been disabled for this account.');
        }

        return parent::beforeAction($action);
    }
}
