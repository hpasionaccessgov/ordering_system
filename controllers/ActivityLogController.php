<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\ActivityLog;
use app\models\ActivityLogSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class ActivityLogController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index'], 'roles' => ['activityLog.index']],
                    ['allow' => true, 'actions' => ['view'], 'roles' => ['activityLog.view']],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->layout = 'admin';
        $searchModel = new ActivityLogSearch();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->request->queryParams),
        ]);
    }

    public function actionView(int $id): string
    {
        $model = ActivityLog::find()->with('user')->where(['id' => $id])->one();
        if ($model === null) {
            throw new NotFoundHttpException('Activity log not found.');
        }
        $this->layout = 'admin';

        return $this->render('view', ['model' => $model]);
    }
}
