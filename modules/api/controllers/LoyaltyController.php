<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\ApplicationSettings;
use app\models\LoyaltyPointTransaction;
use app\models\Product;
use app\models\Reward;
use app\services\LoyaltyService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

final class LoyaltyController extends ApiController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                ['actions' => ['index', 'transactions', 'rewards'], 'allow' => true, 'roles' => ['loyalty.view']],
                ['actions' => ['redeem'], 'allow' => true, 'roles' => ['loyalty.redeem']],
            ],
        ];

        return $behaviors;
    }

    protected function verbs(): array
    {
        return [
            'index' => ['GET'],
            'transactions' => ['GET'],
            'rewards' => ['GET'],
            'redeem' => ['POST'],
        ];
    }

    public function actionIndex(): array
    {
        $summary = LoyaltyService::summary((int) Yii::$app->user->id);
        $summary['tier_label'] = LoyaltyService::tierLabel((string) $summary['tier']);

        return $this->ok(['summary' => $summary]);
    }

    public function actionTransactions(): array
    {
        $provider = new ActiveDataProvider([
            'query' => LoyaltyPointTransaction::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => ApplicationSettings::pagination(),
        ]);

        $transactions = array_map(
            fn(LoyaltyPointTransaction $transaction): array => $this->serializeTransaction($transaction),
            $provider->getModels(),
        );

        return $this->ok($transactions, ['pagination' => $this->paginationMeta($provider->getPagination())]);
    }

    public function actionRewards(): array
    {
        $provider = new ActiveDataProvider([
            'query' => $this->rewardQuery(),
            'pagination' => ApplicationSettings::pagination(),
        ]);

        $rewards = array_map(
            static fn(Reward $reward): array => $reward->toArray(['id', 'name', 'description', 'point_cost', 'product_id'])
                + ['product_name' => $reward->product?->name],
            $provider->getModels(),
        );

        return $this->ok($rewards, ['pagination' => $this->paginationMeta($provider->getPagination())]);
    }

    public function actionRedeem(int $id): array
    {
        $reward = Reward::findOne($id);
        if ($reward === null) {
            $this->fail(404, 'not_found', 'Reward not found.');
        }

        $transaction = LoyaltyService::redeem($reward, (int) Yii::$app->user->id);
        $data = $this->serializeTransaction($transaction);
        $data['order_number'] = $transaction->order?->order_number;

        return $this->ok($data, [], 201);
    }

    private function rewardQuery(): \yii\db\ActiveQuery
    {
        return Reward::find()
            ->innerJoin(['p' => '{{%products}}'], 'p.id = reward.product_id')
            ->where(['reward.status' => Reward::STATUS_ACTIVE])
            ->andWhere(['p.status' => Product::STATUS_ACTIVE])
            ->orderBy(['reward.point_cost' => SORT_ASC, 'reward.name' => SORT_ASC]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeTransaction(LoyaltyPointTransaction $transaction): array
    {
        return $transaction->toArray([
            'id',
            'order_id',
            'points',
            'transaction_type',
            'remarks',
            'created_at',
            'typeLabel',
        ]);
    }
}
