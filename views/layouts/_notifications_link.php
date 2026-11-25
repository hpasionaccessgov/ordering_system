<?php

use app\models\Notification;
use app\services\OrderAccessPolicy;
use app\widgets\ToolTip;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

/** @var string $class */

$class = $class ?? 'btn btn-primary';
$canViewAll = \Yii::$app->user->can('order.view') && OrderAccessPolicy::canViewAllOrders(
    \Yii::$app->user->can('admin.access'),
    \Yii::$app->user->can('staff.access'),
);
$visibleQuery = Notification::visibleTo(\Yii::$app->user->id, $canViewAll)->with('order');
$unreadCount = (clone $visibleQuery)->andWhere(['read_at' => null])->count();
$notifications = (clone $visibleQuery)
    ->andWhere(['read_at' => null])
    ->orderBy(['id' => SORT_DESC])
    ->limit(5)
    ->all();
$canViewNotification = \Yii::$app->user->can('notification.read');
$url = Json::htmlEncode(Url::to(['/notification/unread-count']));
$inboxUrl = Url::to(['/notification/index']);
$this->registerJs(<<<JS
const notificationLink = document.querySelector('[data-notification-link]');
const notificationBadge = notificationLink.querySelector('[data-notification-count]');
function setNotificationCount(count) {
    notificationBadge.textContent = count;
    notificationBadge.hidden = count === 0;
    notificationLink.setAttribute('aria-label', count
        ? 'Notifications, ' + count + ' unread'
        : 'Notifications');
}
async function refreshNotificationCount() {
    try {
        const response = await fetch($url, {headers: {'Accept': 'application/json'}, cache: 'no-store'});
        if (!response.ok || !response.headers.get('content-type')?.includes('application/json')) {
            throw new Error('Notification count request failed');
        }
        const data = await response.json();
        setNotificationCount(data.count);
    } catch (error) {
        console.warn('Could not refresh notifications:', error);
    }
}
setInterval(refreshNotificationCount, 30000);

const notificationMobile = window.matchMedia('(max-width: 575.98px)');
function syncNotificationMenuMode() {
    const isMobile = notificationMobile.matches;
    if (isMobile) {
        notificationLink.removeAttribute('data-bs-toggle');
        notificationLink.removeAttribute('data-bs-auto-close');
        notificationLink.setAttribute('aria-haspopup', 'false');
        if (window.bootstrap && window.bootstrap.Dropdown) {
            window.bootstrap.Dropdown.getInstance(notificationLink)?.hide();
        }
    } else {
        notificationLink.setAttribute('data-bs-toggle', 'dropdown');
        notificationLink.setAttribute('data-bs-auto-close', 'outside');
        notificationLink.setAttribute('aria-haspopup', 'true');
    }
}
if (notificationMobile.addEventListener) {
    notificationMobile.addEventListener('change', syncNotificationMenuMode);
} else {
    notificationMobile.addListener(syncNotificationMenuMode);
}
syncNotificationMenuMode();
JS, View::POS_READY);
?>
<div class="dropdown">
    <?= Html::a(
        Html::tag('i', '', [
            'class' => 'bi bi-bell',
            'aria-hidden' => 'true',
        ])
            . ' ' . Html::tag('span', Html::encode($unreadCount), [
                'class' => 'badge bg-danger',
                'data-notification-count' => true,
                'aria-live' => 'polite',
                'hidden' => !$unreadCount,
            ]),
        $inboxUrl,
        [
            'class' => $class . ' dropdown-toggle cafebara-notification-link',
            'aria-label' => $unreadCount ? "Notifications, $unreadCount unread" : 'Notifications',
            'aria-haspopup' => 'true',
            'aria-expanded' => 'false',
            'data-bs-toggle' => 'dropdown',
            'data-bs-auto-close' => 'outside',
            'data-notification-link' => true,
        ],
    ) ?>
    <ul class="dropdown-menu dropdown-menu-end shadow cafebara-notification-menu">
        <li><span class="dropdown-header text-body-secondary fw-semibold">Unread notifications</span></li>
        <?php foreach ($notifications as $notification) : ?>
            <?php /** @var Notification $notification */ ?>
            <li class="border-bottom border-secondary-subtle">
                <?= Html::a(
                    Html::tag('span', 'Unread', ['class' => 'badge text-bg-primary me-2'])
                        . Html::tag('span', Html::encode($notification->label), ['class' => 'small'])
                        . Html::tag('span', Html::encode(\Yii::$app->formatter->asDatetime($notification->created_at)), [
                            'class' => 'd-block text-body-secondary small',
                        ]),
                    $canViewNotification
                        ? ['/notification/view', 'id' => $notification->id]
                        : ['/notification/index'],
                    [
                        'class' => 'dropdown-item cafebara-notification-item',
                        'data-notification-item' => true,
                    ],
                ) ?>
            </li>
        <?php endforeach; ?>
        <?php if ($notifications === []) : ?>
            <li><span class="dropdown-item-text text-body-secondary">No unread notifications.</span></li>
        <?php endif; ?>
        <li><?= Html::a('View all notifications', ['/notification/index'], ['class' => 'dropdown-item fw-bold cafebara-notification-item']) ?></li>
    </ul>
</div>