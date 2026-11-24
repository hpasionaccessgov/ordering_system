<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Inventory;
use app\models\InventorySearch;
use app\services\InventoryService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * InventoryController implements the CRUD actions for Inventory model.
 */
class InventoryController extends Controller
{
    public $layout = 'admin';

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['inventory.index'],
                    ],
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['inventory.view'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['inventory.create'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['inventory.update'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['inventory.delete'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex(): string
    {
        $searchModel = new InventorySearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new Inventory();
        $model->loadDefaultValues();

        if ($this->request->isPost && $model->load($this->request->post()) && InventoryService::create($model)) {
            Yii::$app->session->setFlash('success', 'Inventory item created.');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate(int $id)
    {
        if (!Yii::$app->user->can('inventory.view')) {
            throw new ForbiddenHttpException('You are not allowed to view inventory details.');
        }

        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && InventoryService::update($model)) {
            Yii::$app->session->setFlash('success', 'Inventory item updated.');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete(int $id)
    {
        $model = $this->findModel($id);

        if ((int) $model->getTransactions()->count() > 0) {
            Yii::$app->session->setFlash('error', 'This item has stock history and cannot be deleted. Set it to Inactive instead.');

            return $this->redirect(['view', 'id' => $model->id]);
        }
        if ((int) $model->getProductIngredients()->count() > 0) {
            Yii::$app->session->setFlash('error', 'This item is used in product recipes and cannot be deleted. Set it to Inactive instead.');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Inventory item deleted.');

        return $this->redirect(['index']);
    }

    protected function findModel(int $id): Inventory
    {
        $model = Inventory::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $model;
    }
}
