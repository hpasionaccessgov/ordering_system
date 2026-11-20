<?php

use app\models\OrderNotification;
use yii\helpers\Html;

$unreadCount = OrderNotification::find()
    ->where(['user_id' => Yii::$app->user->id, 'read_at' => null])
    ->count();
?>
<?= Html::a(
    Html::tag('i', '', ['class' => 'bi bi-bell'])
        . ($unreadCount ? ' <span class="badge bg-danger">' . Html::encode($unreadCount) . '</span>' : ''),
    ['/notification/index'],
    [
        'class' => 'btn btn-link text-decoration-none position-relative',
        'title' => 'Notifications',
        'aria-label' => 'Notifications',
    ]
) ?>
