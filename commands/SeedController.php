<?php

declare(strict_types=1);

namespace app\commands;

use app\commands\seeders\CategorySeeder;
use app\commands\seeders\InventorySeeder;
use app\commands\seeders\ProductSeeder;
use app\commands\seeders\UserSeeder;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class SeedController extends Controller
{
    public function actionProducts(): int
    {
        $products = (new ProductSeeder())->run();

        $this->stdout("Product catalog seeded. Products added: {$products}.\n");

        return ExitCode::OK;
    }

    public function actionTestData(): int
    {
        $users = (new UserSeeder())->run();
        $categories = (new CategorySeeder())->run();
        $products = (new ProductSeeder())->run();
        ['items' => $items, 'recipes' => $recipes] = (new InventorySeeder())->run();

        $this->stdout(
            "Seed complete. Users: {$users}; categories: {$categories}; products: {$products}; "
            . "inventory items: {$items}; recipes: {$recipes}.\n"
        );

        return ExitCode::OK;
    }

    /**
     * Wipes application data (keeps settings, RBAC definitions and migrations) and re-seeds it.
     */
    public function actionFresh(): int
    {
        if (!$this->confirm('Delete all orders, users, products, inventory and activity logs, then re-seed?')) {
            return ExitCode::OK;
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            // RESTRICT FKs force children before parents.
            foreach ([
                'notifications',
                'activity_log',
                'product_review',
                'favorite',
                'cart_items',
                'order_status_history',
                'order_items',
                'inventory_transactions',
                'orders',
                'product_ingredient',
                'products',
                'inventory',
                'category',
                'auth_assignment',
                'users',
            ] as $table) {
                $db->createCommand()->delete('{{%' . $table . '}}')->execute();
            }
            $transaction->commit();
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            throw $exception;
        }

        $this->stdout("Application data cleared.\n");

        return $this->actionTestData();
    }
}
