<?php

declare(strict_types=1);

namespace app\widgets;

use yii\bootstrap5\LinkPager;
use yii\grid\GridView;

class AdminGridView extends GridView
{
    public $options = [
        'class' => 'grid-view cafebara-admin-grid table-responsive cafebara-table-scroll',
    ];

    public $tableOptions = [
        'class' => 'table table-striped table-hover table-bordered align-middle mb-0 cafebara-data-table',
    ];

    public $headerRowOptions = [
        'class' => 'cafebara-table-header',
    ];

    public $layout = "{items}\n<div class=\"d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-3\">{summary}{pager}</div>";

    public $summaryOptions = [
        'class' => 'mb-0 text-body-secondary small',
    ];

    public $pager = [
        'class' => LinkPager::class,
        'prevPageLabel' => 'Previous',
        'nextPageLabel' => 'Next',
        'options' => ['class' => 'pagination mb-0'],
    ];
}
