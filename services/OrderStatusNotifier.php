<?php

declare(strict_types=1);

namespace app\services;

use app\models\Order;
use yii\mail\MailerInterface;
use Yii;

final class OrderStatusNotifier
{
    public function __construct(private readonly MailerInterface $mailer)
    {
    }

    public function send(Order $order): bool
    {
        $customer = $order->user;
        if ($customer === null || trim($customer->email) === '') {
            Yii::warning(
                "Order {$order->id} status changed, but no customer email is available.",
                __METHOD__,
            );

            return false;
        }

        $params = Yii::$app->params;

        return $this->mailer->compose('order-status-changed', ['order' => $order])
            ->setFrom([$params['senderEmail'] => $params['senderName']])
            ->setTo($customer->email)
            ->setSubject("Cafébara order {$order->order_number} is {$order->status}")
            ->send();
    }
}
