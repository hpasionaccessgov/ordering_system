<?php

declare(strict_types=1);

namespace app\controllers;

use app\services\Navigation;


use app\models\LoyaltyPointTransaction;
use app\models\Product;
use app\models\Reward;
use app\services\LoyaltyService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class LoyaltyController extends Controller
{
    public $layout = 'admin';

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index'], 'roles' => ['loyalty.view']],
                    ['allow' => true, 'actions' => ['redeem'], 'roles' => ['loyalty.redeem']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['redeem' => ['POST']],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $userId = (int) Yii::$app->user->id;

        return $this->render('index', [
            'summary' => LoyaltyService::summary($userId),
            'rewards' => Reward::find()
                ->innerJoin(['p' => '{{%products}}'], 'p.id = reward.product_id')
                ->where(['reward.status' => Reward::STATUS_ACTIVE])
                ->andWhere(['p.status' => Product::STATUS_ACTIVE])
                ->orderBy(['reward.point_cost' => SORT_ASC, 'reward.name' => SORT_ASC])
                ->all(),
            'recentTransactions' => LoyaltyService::recentTransactions($userId, 8),
            'redemptionHistory' => LoyaltyPointTransaction::find()
                ->where([
                    'user_id' => $userId,
                    'transaction_type' => LoyaltyPointTransaction::TYPE_REDEEM,
                ])
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
                ->limit(8)
                ->all(),
        ]);
    }

    public function actionRedeem(int $id): Response
    {
        $reward = Reward::findOne($id);
        if ($reward === null) {
            throw new NotFoundHttpException('The requested reward does not exist.');
        }

        try {
            $transaction = LoyaltyService::redeem($reward, (int) Yii::$app->user->id);
            $order = $transaction->order;
            $queueMessage = $order === null ? '' : ' Order #' . $order->order_number . ' is now in the queue.';
            Yii::$app->session->setFlash(
                'success',
                'You redeemed ' . $reward->name . '. ' . abs($transaction->points)
                    . ' points were deducted. Remaining balance: '
                    . Yii::$app->formatter->asInteger(LoyaltyService::balance((int) Yii::$app->user->id))
                    . ' points.' . $queueMessage,
            );
        } catch (BadRequestHttpException $exception) {
            Yii::$app->session->setFlash('error', $exception->getMessage());
        }

        return $this->redirect(Navigation::url(Navigation::REWARDS));
    }
}
