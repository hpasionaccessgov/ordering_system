<?php

declare(strict_types=1);

namespace app\widgets;

use yii\bootstrap5\LinkPager;
use yii\grid\GridView;

class AdminGridView extends GridView
{
    public $options = [
        'class' => 'grid-view cafebara-admin-grid table-responsive',
    ];

    public $tableOptions = [
        'class' => 'table table-hover align-middle mb-0',
    ];

    public $layout = "{items}\n<div class=\"d-flex justify-content-end p-3\">{pager}</div>";

    public $pager = [
        'class' => LinkPager::class,
    ];
}