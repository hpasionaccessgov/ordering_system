<?php

declare(strict_types=1);

namespace app\controllers;

use app\services\OrderService;
use app\services\ShoppingCart;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

/**
 * CartController handles actions related to the shopping cart, including adding, updating,
 * removing products, and checking out.
 */
final class CartController extends Controller
{
    public $layout = 'admin';

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['actions' => ['index', 'summary'], 'allow' => true, 'roles' => ['cart.index']],
                    ['actions' => ['add', 'add-batch'], 'allow' => true, 'roles' => ['cart.add']],
                    ['actions' => ['update'], 'allow' => true, 'roles' => ['cart.update']],
                    ['actions' => ['remove'], 'allow' => true, 'roles' => ['cart.remove']],
                    ['actions' => ['checkout'], 'allow' => true, 'roles' => ['cart.checkout']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add' => ['POST'],
                    'add-batch' => ['POST'],
                    'update' => ['POST'],
                    'remove' => ['POST'],
                    'checkout' => ['GET', 'POST'],
                    'summary' => ['GET'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $cart = new ShoppingCart();
        $this->notifyRemovedUnavailable($cart);

        return $this->render('index', [
            'summary' => $cart->summary(),
        ]);
    }

    public function actionSummary(): Response|array
    {
        if (!$this->request->isAjax) {
            return $this->redirect(['index']);
        }

        return $this->cartResponse(new ShoppingCart(), '');
    }

    public function actionAdd(int $id): Response|array
    {
        $cart = new ShoppingCart();
        $cart->add($id, $this->postedQuantity());

        return $this->cartResponse($cart, 'Product added to your cart.');
    }

    /**
     * Adds every posted product quantity (quantities[<id>]) to the cart in one request.
     */
    public function actionAddBatch(): Response
    {
        $quantities = $this->request->post('quantities', []);
        if (!is_array($quantities)) {
            throw new BadRequestHttpException('Quantities are invalid.');
        }

        $cart = new ShoppingCart();
        $added = 0;
        $skipped = 0;
        foreach ($quantities as $productId => $quantity) {
            $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
            if ($quantity === false || $quantity < 1) {
                continue;
            }

            try {
                $cart->add((int) $productId, $quantity);
                $added++;
            } catch (BadRequestHttpException) {
                $skipped++;
            }
        }

        if ($added === 0) {
            Yii::$app->session->setFlash('warning', 'Choose at least one product to add.');
        } elseif ($skipped > 0) {
            Yii::$app->session->setFlash(
                'success',
                "{$added} product(s) added to your cart; {$skipped} unavailable product(s) were skipped.",
            );
        } else {
            Yii::$app->session->setFlash('success', 'Products added to your cart.');
        }

        return $this->redirect(['index']);
    }

    public function actionUpdate(int $id): Response|array
    {
        (new ShoppingCart())->update($id, $this->postedQuantity(true));

        return $this->cartResponse(new ShoppingCart(), '');
    }

    public function actionRemove(int $id): Response|array
    {
        (new ShoppingCart())->remove($id);

        return $this->cartResponse(new ShoppingCart(), 'Product removed from your cart.');
    }

    public function actionCheckout(): Response|string
    {
        $cart = new ShoppingCart();
        $this->notifyRemovedUnavailable($cart);
        $summary = $cart->summary();

        if ($this->request->isPost) {
            $token = $this->request->post('checkout_token');
            if (!is_string($token)) {
                throw new BadRequestHttpException('Invalid checkout token.');
            }
            $cart->assertCheckoutToken($token);

            $items = [];
            foreach ($cart->items() as $productId => $quantity) {
                $items[] = ['product_id' => $productId, 'quantity' => $quantity];
            }
            $order = (new OrderService())->create(
                (int) Yii::$app->user->id,
                $items,
                $token,
            );
            $cart->clear();

            return $this->redirect(['/order/confirmation', 'id' => $order->id]);
        }

        if ($summary['lines'] === []) {
            Yii::$app->session->setFlash('warning', 'Add at least one active product before checkout.');

            return $this->redirect(['index']);
        }

        return $this->render('checkout', [
            'summary' => $summary,
            'checkoutToken' => $cart->checkoutToken(),
        ]);
    }

    /**
     * @return Response|array<string, mixed>
     */
    private function cartResponse(ShoppingCart $cart, string $message): Response|array
    {
        $summary = $cart->summary();
        if (!$this->request->isAjax) {
            if ($message !== '') {
                Yii::$app->session->setFlash('success', $message);
            }

            return $this->redirect(['index']);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'success' => true,
            'message' => $message,
            'count' => $summary['count'],
            'total' => Yii::$app->formatter->asCurrency($summary['total']),
            'html' => $this->renderPartial('@app/views/layouts/_mini_cart_body', [
                'summary' => $summary,
            ]),
        ];
    }

    private function postedQuantity(bool $allowZero = false): int
    {
        $quantity = filter_var($this->request->post('quantity'), FILTER_VALIDATE_INT);
        if ($quantity === false || (!$allowZero && $quantity < 1)) {
            throw new BadRequestHttpException('Enter a valid quantity.');
        }

        return $quantity;
    }

    private function notifyRemovedUnavailable(ShoppingCart $cart): void
    {
        $removed = $cart->removeUnavailable();
        if ($removed > 0) {
            Yii::$app->session->setFlash(
                'warning',
                'Unavailable products were removed from your cart.',
            );
        }
    }
}
