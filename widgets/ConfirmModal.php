<?php

declare(strict_types=1);

namespace app\widgets;

use app\assets\ConfirmModalAsset;
use yii\base\Widget;
use yii\helpers\Html;

final class ConfirmModal extends Widget
{
    public function run(): string
    {
        ConfirmModalAsset::register($this->view);

        $id = $this->getId();
        $titleId = $id . '-title';
        $modalContent = Html::tag(
            'div',
            Html::tag(
                'div',
                Html::tag('h2', 'Confirm action', [
                    'class' => 'modal-title fs-5',
                    'id' => $titleId,
                    'data-confirm-modal-title' => 'true',
                ]) . Html::button('', [
                    'type' => 'button',
                    'class' => 'btn-close',
                    'data-bs-dismiss' => 'modal',
                    'aria-label' => 'Close',
                ]),
                ['class' => 'modal-header'],
            ) . Html::tag('div', 'Are you sure?', [
                'class' => 'modal-body',
                'data-confirm-modal-message' => 'true',
            ]) . Html::tag(
                'div',
                Html::button('Cancel', [
                    'type' => 'button',
                    'class' => 'btn btn-secondary',
                    'data-bs-dismiss' => 'modal',
                ]) . Html::button('Confirm', [
                    'type' => 'button',
                    'class' => 'btn btn-primary',
                    'data-confirm-modal-accept' => 'true',
                ]),
                ['class' => 'modal-footer'],
            ),
            ['class' => 'modal-content'],
        );

        return Html::tag(
            'div',
            Html::tag('div', $modalContent, [
                'class' => 'modal-dialog modal-dialog-centered',
                'role' => 'document',
            ]),
            [
                'id' => $id,
                'class' => 'modal fade',
                'tabindex' => '-1',
                'aria-labelledby' => $titleId,
                'aria-hidden' => 'true',
                'data-confirm-modal-root' => 'true',
            ],
        );
    }
}
