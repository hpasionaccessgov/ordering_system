<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\ApplicationSettings;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class SettingsController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index'], 'roles' => ['settings.index']],
                    ['allow' => true, 'actions' => ['update'], 'roles' => ['settings.update']],
                ],
            ],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['update' => ['post']]],
        ];
    }

    public function actionIndex(): string
    {
        $this->layout = 'admin';
        return $this->render('index', ['model' => ApplicationSettings::current()]);
    }

    public function actionUpdate(): Response|string
    {
        $model = ApplicationSettings::current();
        if (!$model->load(Yii::$app->request->post())) {
            throw new BadRequestHttpException('Settings data is required.');
        }
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Settings saved.');
            return $this->redirect(['index']);
        }
        $this->layout = 'admin';
        return $this->render('index', ['model' => $model]);
    }
}
