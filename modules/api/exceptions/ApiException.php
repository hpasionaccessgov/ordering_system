<?php

declare(strict_types=1);

namespace app\modules\api\exceptions;

use yii\web\HttpException;

final class ApiException extends HttpException
{
    /**
     * @param array<string, list<string>> $errors
     */
    public function __construct(
        int $status,
        public readonly string $errorCode,
        string $message,
        public readonly array $errors = [],
    ) {
        parent::__construct($status, $message);
    }
}
