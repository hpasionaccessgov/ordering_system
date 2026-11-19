<?php

declare(strict_types=1);

namespace app\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Alert widget renders a message from session flash. All flash messages are displayed
 * in the sequence they were assigned using setFlash. You can set message as following:
 *
 * ```php
 * Yii::$app->session->setFlash('error', 'This is the message');
 * Yii::$app->session->setFlash('success', 'This is the message');
 * Yii::$app->session->setFlash('info', 'This is the message');
 * ```
 *
 * Multiple messages could be set as follows:
 *
 * ```php
 * Yii::$app->session->setFlash('error', ['Error 1', 'Error 2']);
 * ```
 *
 * Unlike {@see \yii\bootstrap5\Alert}, this widget renders the alert markup itself
 * instead of delegating to the Bootstrap widget. The Bootstrap widget would register
 * `yii\bootstrap5\BootstrapPluginAsset` (the vendor `twbs/bootstrap` bundle) every time
 * a flash message is present. On AdminLTE pages that vendor bundle is a second copy of
 * Bootstrap on top of the node_modules bundle loaded by `AdminLteAsset`, which breaks
 * the admin layout: the late `bootstrap.css` overrides the theme and the duplicated
 * `bootstrap.bundle.js` double-binds the `data-bs-toggle` handlers, so the header
 * dropdown opens and immediately closes. Bootstrap is already provided by the layout,
 * so the plugin registration is unnecessary; the close button keeps working through
 * Bootstrap's data API.
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @author Alexander Makarov <sam@rmcreative.ru>
 */
class Alert extends \yii\bootstrap5\Widget
{
    /**
     * @var array the alert types configuration for the flash messages.
     * This array is setup as $key => $value, where:
     * - key: the name of the session flash variable
     * - value: the bootstrap alert type (i.e. danger, success, info, warning)
     */
    public array $alertTypes = [
        'error'   => 'alert-danger',
        'danger'  => 'alert-danger',
        'success' => 'alert-success',
        'info'    => 'alert-info',
        'warning' => 'alert-warning'
    ];

    /**
     * @var array the options for rendering the close button tag.
     * Set to `false` to disable the close button.
     */
    public array $closeButton = [];

    /**
     * @var int number of seconds before each alert fades out and removes itself.
     * Set to 0 to keep alerts open until the user dismisses them.
     */
    public int $autoDismiss = 5;

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $session = Yii::$app->session;

        if (!$session->getIsActive() && !$session->getHasSessionId()) {
            return;
        }

        $rendered = false;
        foreach (array_keys($this->alertTypes) as $type) {
            $flash = $session->getFlash($type);

            foreach ((array) $flash as $i => $message) {
                echo $this->renderAlert($type, (string) $i, (string) $message);
                $rendered = true;
            }

            $session->removeFlash($type);
        }

        if ($rendered && $this->autoDismiss > 0) {
            $this->registerAutoDismissJs();
        }
    }

    /**
     * Renders a single alert.
     *
     * @param string $type the flash type (a key of [[alertTypes]]).
     * @param string $index the position of the message within its flash type.
     * @param string $message the alert message.
     */
    private function renderAlert(string $type, string $index, string $message): string
    {
        $options = $this->options;
        $options['id'] = $this->getId() . '-' . $type . '-' . $index;
        $options['role'] = $options['role'] ?? 'alert';

        if ($this->autoDismiss > 0) {
            $options['data']['auto-dismiss'] = $this->autoDismiss;
        }

        Html::addCssClass($options, 'alert');
        Html::addCssClass($options, $this->alertTypes[$type]);

        $closeButton = '';
        if ($this->closeButton !== false) {
            Html::addCssClass($options, 'alert-dismissible');
            $closeButton = "\n" . $this->renderCloseButton();
        }

        return Html::tag('div', $message . $closeButton, $options) . "\n";
    }

    /**
     * Renders the alert close button.
     */
    private function renderCloseButton(): string
    {
        $closeButton = array_merge([
            'class' => 'btn-close',
            'data' => ['bs-dismiss' => 'alert'],
            'aria' => ['label' => Yii::t('yii/bootstrap5', 'Close')],
        ], $this->closeButton);

        $tag = ArrayHelper::remove($closeButton, 'tag', 'button');
        $label = ArrayHelper::remove($closeButton, 'label', '');

        if ($tag === 'button' && !isset($closeButton['type'])) {
            $closeButton['type'] = 'button';
        }

        return Html::tag($tag, $label, $closeButton);
    }

    /**
     * Registers the one-time script that fades out and removes auto-dismiss alerts.
     */
    private function registerAutoDismissJs(): void
    {
        $this->getView()->registerJs(<<<JS
document.querySelectorAll('[data-auto-dismiss]').forEach(alert => {
    setTimeout(() => {
        alert.style.transition = 'opacity .4s ease';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 400);
    }, Number(alert.dataset.autoDismiss) * 1000);
});
JS, \yii\web\View::POS_END);
    }
}
