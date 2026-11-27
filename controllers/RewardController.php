<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\ActivityLog;
use app\models\Product;
use app\models\Reward;
use app\models\RewardSearch;
use app\services\ActivityLogger;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class RewardController extends Controller
{
    public $layout = 'admin';

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index'], 'roles' => ['reward.index']],
                    ['allow' => true, 'actions' => ['view'], 'roles' => ['reward.view']],
                    ['allow' => true, 'actions' => ['create'], 'roles' => ['reward.create']],
                    ['allow' => true, 'actions' => ['update'], 'roles' => ['reward.update']],
                    ['allow' => true, 'actions' => ['delete'], 'roles' => ['reward.delete']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $searchModel = new RewardSearch();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->request->queryParams),
        ]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    public function actionCreate(): Response|string
    {
        $model = new Reward();
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            ActivityLogger::record(ActivityLog::REWARD_CREATED, Yii::$app->user->id, 'reward/create');
            Yii::$app->session->setFlash('success', 'Reward created.');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', ['model' => $model, 'products' => $this->activeProducts()]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            $event = $model->isActive() ? ActivityLog::REWARD_UPDATED : ActivityLog::REWARD_DISABLED;
            ActivityLogger::record($event, Yii::$app->user->id, 'reward/update');
            Yii::$app->session->setFlash('success', 'Reward updated.');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', ['model' => $model, 'products' => $this->activeProducts()]);
    }

    public function actionDelete(int $id): Response
    {
        $model = $this->findModel($id);
        if ($model->isActive()) {
            $model->status = Reward::STATUS_INACTIVE;
            $model->save(false, ['status', 'updated_at']);
            ActivityLogger::record(ActivityLog::REWARD_DISABLED, Yii::$app->user->id, 'reward/delete');
            Yii::$app->session->setFlash('success', 'Reward disabled. Customers can no longer redeem it.');
        }

        return $this->redirect(['index']);
    }

    /**
     * @return array<int, string>
     */
    private function activeProducts(): array
    {
        return Product::find()
            ->where(['status' => Product::STATUS_ACTIVE])
            ->orderBy(['name' => SORT_ASC])
            ->select('name')
            ->indexBy('id')
            ->column();
    }

    protected function findModel(int $id): Reward
    {
        $model = Reward::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('The requested reward does not exist.');
        }

        return $model;
    }
}
