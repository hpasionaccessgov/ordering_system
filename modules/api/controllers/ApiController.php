<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\modules\api\exceptions\ApiException;
use Yii;
use yii\data\Pagination;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\User as WebUser;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;

abstract class ApiController extends Controller
{
    public bool $authRequired = true;

    private ?WebUser $previousUser = null;

    public function init(): void
    {
        parent::init();
        // Bearer tokens are stateless: use an isolated user component and restore the browser one after the request.
        $previous = Yii::$app->getUser();
        $this->previousUser = $previous;
        Yii::$app->set('user', new WebUser([
            'identityClass' => $previous->identityClass,
            'enableSession' => false,
            'enableAutoLogin' => false,
            'loginUrl' => null,
        ]));
    }

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        unset($behaviors['rateLimiter']);
        if ($this->authRequired) {
            $behaviors['authenticator'] = ['class' => HttpBearerAuth::class];
        } else {
            unset($behaviors['authenticator']);
        }

        return $behaviors;
    }

    public function runAction($id, $params = [])
    {
        try {
            return parent::runAction($id, $params);
        } catch (\Throwable $exception) {
            return $this->errorResponse($exception);
        } finally {
            if ($this->previousUser !== null) {
                Yii::$app->set('user', $this->previousUser);
                $this->previousUser = null;
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $meta
     * @return array<string, mixed>
     */
    protected function ok(array $data, array $meta = [], int $status = 200): array
    {
        Yii::$app->response->setStatusCode($status);

        $result = ['success' => true, 'data' => $data];
        if ($meta !== []) {
            $result['meta'] = $meta;
        }

        return $result;
    }

    /**
     * @return array{total: int, page_count: int, current_page: int, per_page: int}
     */
    protected function paginationMeta(Pagination $pagination): array
    {
        return [
            'total' => (int) $pagination->totalCount,
            'page_count' => $pagination->getPageCount(),
            'current_page' => $pagination->getPage() + 1,
            'per_page' => $pagination->getPageSize(),
        ];
    }

    protected function bodyInt(string $key, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
    {
        $value = filter_var(Yii::$app->request->getBodyParams()[$key] ?? null, FILTER_VALIDATE_INT);
        if ($value === false || $value < $min || $value > $max) {
            $this->fail(422, 'validation_failed', 'Please correct the errors below.', [
                $key => ["{$key} must be an integer between {$min} and {$max}."],
            ]);
        }

        return (int) $value;
    }

    /**
     * @param array<string, list<string>> $errors
     */
    protected function fail(int $status, string $code, string $message, array $errors = []): never
    {
        throw new ApiException($status, $code, $message, $errors);
    }

    private function errorResponse(\Throwable $exception): Response
    {
        if ($exception instanceof ApiException) {
            $status = $exception->statusCode;
            $code = $exception->errorCode;
            $message = $exception->getMessage();
            $errors = $exception->errors;
        } else {
            [$status, $code, $message] = $this->mapException($exception);
            $errors = [];
            if ($status >= 500) {
                Yii::error($exception, 'api');
            }
        }

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->setStatusCode($status);
        $response->data = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'errors' => $errors,
            ],
        ];

        return $response;
    }

    /**
     * @return array{int, string, string}
     */
    private function mapException(\Throwable $exception): array
    {
        if ($exception instanceof UnauthorizedHttpException) {
            return [401, 'unauthorized', 'Authentication required.'];
        }
        if ($exception instanceof ForbiddenHttpException) {
            return [403, 'forbidden', 'You are not allowed to perform this action.'];
        }
        if ($exception instanceof NotFoundHttpException) {
            return [404, 'not_found', 'The requested resource was not found.'];
        }
        if ($exception instanceof MethodNotAllowedHttpException) {
            return [405, 'method_not_allowed', 'This method is not allowed for this endpoint.'];
        }
        if ($exception instanceof UnprocessableEntityHttpException) {
            return [422, 'validation_failed', $exception->getMessage()];
        }
        if ($exception instanceof BadRequestHttpException || $exception instanceof \InvalidArgumentException) {
            return [400, 'bad_request', $exception->getMessage()];
        }
        if ($exception instanceof HttpException) {
            return [$exception->statusCode, 'http_error', $exception->getMessage()];
        }

        return [500, 'internal_error', 'An internal error occurred.'];
    }
}
