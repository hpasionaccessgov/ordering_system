<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Product;
use app\services\ReviewService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class ProductReviewController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['actions' => ['create'], 'allow' => true, 'roles' => ['review.create']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['create' => ['POST']],
            ],
        ];
    }

    public function actionCreate(int $id): Response
    {
        if (Product::find()->where(['id' => $id, 'status' => Product::STATUS_ACTIVE])->exists() === false) {
            throw new NotFoundHttpException('Product not found.');
        }

        $orderId = filter_var($this->request->post('order_id'), FILTER_VALIDATE_INT);
        $rating = filter_var($this->request->post('rating'), FILTER_VALIDATE_INT);
        if ($orderId === false || $orderId < 1 || $rating === false) {
            throw new BadRequestHttpException('Choose an order and a rating.');
        }

        ReviewService::create(
            (int) Yii::$app->user->id,
            $id,
            $orderId,
            $rating,
            (string) $this->request->post('review', ''),
        );
        Yii::$app->session->setFlash('success', 'Thank you for your review.');

        return $this->redirect(['/product/view', 'id' => $id]);
    }
}
