<?php

declare(strict_types=1);

namespace app\commands;

use app\commands\seeders\ProductSeeder;
use app\commands\seeders\UserSeeder;
use yii\console\Controller;
use yii\console\ExitCode;

class SeedController extends Controller
{
    public function actionTestData(): int
    {
        $users = (new UserSeeder())->run();
        $products = (new ProductSeeder())->run();

        $this->stdout("Seed complete. Users added: {$users}; products added: {$products}.\n");

        return ExitCode::OK;
    }
}
