<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\AdjustPointsForm;
use app\models\LoyaltyPointTransaction;
use app\models\LoyaltyTransactionSearch;
use app\models\User;
use app\services\ActivityLogger;
use app\services\LoyaltyReportService;
use app\services\LoyaltyService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

class AdminLoyaltyController extends Controller
{
    public $layout = 'admin';

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'transactions', 'redemptions'],
                        'roles' => ['loyalty.transaction.index'],
                    ],
                    ['allow' => true, 'actions' => ['adjust'], 'roles' => ['loyalty.adjust']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['adjust' => ['GET', 'POST']],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $filter = new \app\models\ReportFilter();
        $filter->preset = 'year';
        $filter->validate();

        return $this->render('index', [
            'summary' => (new LoyaltyReportService())->summary($filter),
            'tiers' => (new LoyaltyReportService())->tiers($filter),
            'recentTransactions' => LoyaltyPointTransaction::find()
                ->with(['user', 'order'])
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
                ->limit(8)
                ->all(),
        ]);
    }

    public function actionTransactions(): string
    {
        $searchModel = new LoyaltyTransactionSearch();

        return $this->render('transactions', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->request->queryParams),
            'title' => 'Loyalty transactions',
        ]);
    }

    public function actionRedemptions(): string
    {
        $searchModel = new LoyaltyTransactionSearch();

        return $this->render('transactions', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->request->queryParams, true),
            'title' => 'Reward redemption history',
        ]);
    }

    public function actionAdjust(): Response|string
    {
        $model = new AdjustPointsForm();

        if ($this->request->isPost) {
            try {
                if ($model->load($this->request->post()) && $model->validate()) {
                    LoyaltyService::adjust($model, (int) Yii::$app->user->id);
                    Yii::$app->session->setFlash('success', 'Loyalty points adjusted and logged.');

                    return $this->redirect(['/admin-loyalty/transactions']);
                }
            } catch (BadRequestHttpException $exception) {
                Yii::$app->session->setFlash('error', $exception->getMessage());
            }
        }

        return $this->render('adjust', [
            'model' => $model,
            'customerOptions' => $this->customerOptions(),
        ]);
    }

    /**
     * @return array<int|string, string>
     */
    private function customerOptions(): array
    {
        $rows = User::find()
            ->select(['id', 'label' => new \yii\db\Expression("COALESCE(NULLIF([[full_name]], ''), [[username]])")])
            ->orderBy(['username' => SORT_ASC])
            ->asArray()
            ->all();

        return ArrayHelper::map($rows, 'id', 'label');
    }
}
