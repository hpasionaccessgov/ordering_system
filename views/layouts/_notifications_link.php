<?php

use app\models\OrderNotification;
use app\services\OrderAccessPolicy;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

$canViewAll = Yii::$app->user->can('order.view') && OrderAccessPolicy::canViewAllOrders(
    Yii::$app->user->can('admin.access'),
    Yii::$app->user->can('staff.access'),
);
$unreadCount = OrderNotification::visibleTo(Yii::$app->user->id, $canViewAll)
    ->andWhere(['read_at' => null])
    ->count();
$url = Json::htmlEncode(Url::to(['/notification/unread-count']));
$this->registerJs(<<<JS
const notificationLink = document.querySelector('[data-notification-link]');
const notificationBadge = notificationLink.querySelector('[data-notification-count]');
async function refreshNotificationCount() {
    try {
        const response = await fetch($url, {headers: {'Accept': 'application/json'}, cache: 'no-store'});
        if (!response.ok || !response.headers.get('content-type')?.includes('application/json')) {
            throw new Error('Notification count request failed');
        }
        const data = await response.json();
        notificationBadge.textContent = data.count;
        notificationBadge.hidden = data.count === 0;
        notificationLink.setAttribute('aria-label', data.count ? `Notifications, \${data.count} unread` : 'Notifications');
    } catch (error) {
        console.warn('Could not refresh notifications:', error);
    }
}
setInterval(refreshNotificationCount, 30000);
JS, View::POS_READY);
?>
<?= Html::a(
    Html::tag('i', '', ['class' => 'bi bi-bell'])
        . ' ' . Html::tag('span', Html::encode($unreadCount), [
            'class' => 'badge bg-danger',
            'data-notification-count' => true,
            'aria-live' => 'polite',
            'hidden' => !$unreadCount,
        ]),
    ['/notification/index'],
    [
        'class' => 'btn btn-link text-decoration-none position-relative',
        'title' => 'Notifications',
        'aria-label' => $unreadCount ? "Notifications, $unreadCount unread" : 'Notifications',
        'data-notification-link' => true,
    ]
) ?>
