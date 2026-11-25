<?php

namespace app\controllers;

use app\models\Category;
use app\models\CategorySearch;
use app\models\Product;
use app\services\NotificationService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends Controller
{
    public $layout = 'admin';

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        ['actions' => ['index'], 'allow' => true, 'roles' => ['category.index']],
                        ['actions' => ['view'], 'allow' => true, 'roles' => ['category.view']],
                        ['actions' => ['create'], 'allow' => true, 'roles' => ['category.create']],
                        ['actions' => ['update'], 'allow' => true, 'roles' => ['category.update']],
                        ['actions' => ['delete'], 'allow' => true, 'roles' => ['category.delete']],
                    ]
                ],

                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Category models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CategorySearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $canViewCategories = Yii::$app->user->can('category.view');
        if (!$canViewCategories) {
            $dataProvider->query->andWhere(['id' => null]);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'canViewCategories' => $canViewCategories,
        ]);
    }

    /**
     * Displays a single Category model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Category model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Category();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                NotificationService::notifyManagers(
                    NotificationService::TYPE_CATEGORY_CREATED,
                    'New category ' . $model->name . ' added.',
                    '/category/view',
                    (int) $model->id,
                );

                return $this->redirect($this->getCategoryReturnRoute($model->id));
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Category model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        if (!Yii::$app->user->can('category.view')) {
            throw new ForbiddenHttpException('You are not allowed to view this category.');
        }

        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            NotificationService::notifyManagers(
                NotificationService::TYPE_CATEGORY_UPDATED,
                'Category ' . $model->name . ' updated.',
                '/category/view',
                (int) $model->id,
            );

            return $this->redirect($this->getCategoryReturnRoute($model->id));
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Category model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (Product::find()->where(['category_id' => $model->id])->exists()) {
            Yii::$app->session->setFlash(
                'error',
                'This category is assigned to products. Reassign them before deleting it.',
            );

            return $this->redirect($this->getCategoryListReturnRoute());
        }

        $categoryName = $model->name;
        $model->delete();
        NotificationService::notifyManagers(
            NotificationService::TYPE_CATEGORY_DELETED,
            'Category ' . $categoryName . ' deleted.',
            '/catalog/categories',
        );

        return $this->redirect($this->getCategoryListReturnRoute());
    }

    private function getCategoryReturnRoute(int $id): array
    {
        if (Yii::$app->user->can('category.view')) {
            return ['view', 'id' => $id];
        }

        return $this->getCategoryListReturnRoute();
    }

    private function getCategoryListReturnRoute(): array
    {
        return Yii::$app->user->can('category.index') ? ['index'] : ['/dashboard'];
    }

    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Category the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Category::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
