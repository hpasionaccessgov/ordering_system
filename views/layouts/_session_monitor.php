<?php

use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

if (Yii::$app->user->isGuest) {
    return;
}

$statusUrl = Json::htmlEncode(Url::to(['/session/status']));
$activityUrl = Json::htmlEncode(Url::to(['/session/activity']));
$loginUrl = Json::htmlEncode(Url::to(['/site/login']));
$this->registerJs(<<<JS
let userActivity = false;
['keydown', 'pointerdown', 'pointermove', 'scroll', 'touchstart'].forEach(event =>
    document.addEventListener(event, () => { userActivity = true; }, {passive: true})
);
setInterval(async () => {
    const active = userActivity;
    userActivity = false;
    try {
        const headers = {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'};
        if (active) {
            headers['X-CSRF-Token'] = document.querySelector('meta[name="csrf-token"]').content;
        }
        const response = await fetch(active ? $activityUrl : $statusUrl, {
            method: active ? 'POST' : 'GET', headers, cache: 'no-store'
        });
        if (!response.ok) {
            throw new Error('Session check failed');
        }
        const result = await response.json();
        if (!result.authenticated) {
            window.location.assign($loginUrl);
        }
    } catch (error) {
        console.warn('Could not check session:', error);
    }
}, 15000);
JS, View::POS_READY);
