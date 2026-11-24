<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\ReportFilter;
use app\models\User;
use app\services\InventoryReportService;
use app\services\OrderReportService;
use app\services\ProductReportService;
use app\services\ReportExportService;
use app\services\SalesReportService;
use Yii;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ReportController extends Controller
{
    public $layout = 'admin';

    private const REPORTS = ['sales', 'product', 'orders', 'inventory'];
    private const FORMATS = ['csv', 'excel', 'pdf'];

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['export'],
                        'allow' => true,
                        'roles' => ['reports.export'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['reports.view'],
                    ],
                ],
            ],
        ];
    }

    public function actionSales(): string
    {
        return $this->render('sales', ['filter' => $this->filter()] + $this->build('sales'));
    }

    public function actionProduct(): string
    {
        return $this->render('product', ['filter' => $this->filter()] + $this->build('product'));
    }

    public function actionOrders(): string
    {
        return $this->render('orders', [
            'filter' => $this->filter(),
            'customerOptions' => $this->customerOptions(),
        ] + $this->build('orders'));
    }

    public function actionInventory(): string
    {
        return $this->render('inventory', ['filter' => $this->filter()] + $this->build('inventory'));
    }

    public function actionExport(string $report, string $format): Response
    {
        if (!in_array($report, self::REPORTS, true) || !in_array($format, self::FORMATS, true)) {
            throw new NotFoundHttpException('Report or export format not found.');
        }

        $data = $this->build($report);

        return (new ReportExportService())->export(
            $format,
            $data['title'],
            $data['columns'],
            $data['rows'],
            $data['summary'],
            $this->filter(),
            $report . '-report-' . Yii::$app->formatter->asDate(time(), 'php:Y-m-d'),
        );
    }

    /**
     * @return array{title: string, columns: array, rows: array, summary: array<string, string>}
     */
    private function build(string $report): array
    {
        return match ($report) {
            'sales' => $this->salesData(),
            'product' => $this->productData(),
            'orders' => $this->ordersData(),
            'inventory' => $this->inventoryData(),
        };
    }

    private function salesData(): array
    {
        $service = new SalesReportService();
        $summary = $service->summary($this->filter());
        $rows = $service->daily($this->filter());

        return [
            'title' => 'Sales report',
            'columns' => $this->columns('sales'),
            'rows' => $rows,
            'summary' => [
                'Total revenue' => Yii::$app->formatter->asCurrency($summary['revenue']),
                'Total orders' => Yii::$app->formatter->asInteger($summary['orders']),
                'Average order value' => Yii::$app->formatter->asCurrency($summary['aov']),
                'Completed orders' => Yii::$app->formatter->asInteger($summary['completed']),
                'Cancelled orders' => Yii::$app->formatter->asInteger($summary['cancelled']),
            ],
            'revenueLabels' => array_column($rows, 'date'),
            'revenueValues' => array_column($rows, 'revenue'),
            'orderValues' => array_column($rows, 'orders'),
        ];
    }

    private function productData(): array
    {
        $service = new ProductReportService();
        $ranking = $service->ranking($this->filter());
        $top = array_slice($ranking, 0, 10);

        return [
            'title' => 'Product performance report',
            'columns' => $this->columns('product'),
            'rows' => $top,
            'summary' => [
                'Top selling product' => $top !== []
                    ? $top[0]['product_name'] . ' (' . $top[0]['quantity'] . ')'
                    : 'No sales',
                'Products with sales' => Yii::$app->formatter->asInteger(count($ranking)),
                'Units sold' => Yii::$app->formatter->asInteger(array_sum(array_column($ranking, 'quantity'))),
                'Revenue generated' => Yii::$app->formatter->asCurrency(array_sum(array_column($ranking, 'revenue'))),
            ],
            'topRows' => $top,
            'leastRows' => $service->least($this->filter()),
            'topLabels' => array_column($top, 'product_name'),
            'topValues' => array_column($top, 'quantity'),
            'revenueValues' => array_column($top, 'revenue'),
        ];
    }

    private function ordersData(): array
    {
        $service = new OrderReportService();
        $summary = $service->summary($this->filter());
        $statusRows = $service->statusBreakdown($this->filter());
        $volumeRows = $service->dailyVolume($this->filter());

        return [
            'title' => 'Order report',
            'columns' => $this->columns('orders'),
            'rows' => $service->rows($this->filter()),
            'summary' => [
                'Total orders' => Yii::$app->formatter->asInteger($summary['total']),
                'Completed' => Yii::$app->formatter->asInteger($summary['completed']),
                'Cancelled' => Yii::$app->formatter->asInteger($summary['cancelled']),
                'In progress' => Yii::$app->formatter->asInteger(
                    $summary['pending'] + $summary['confirmed'] + $summary['processing'],
                ),
            ],
            'statusRows' => $statusRows,
            'statusLabels' => array_map(
                static fn(array $row): string => ucfirst($row['status']),
                $statusRows,
            ),
            'statusValues' => array_column($statusRows, 'orders'),
            'volumeRows' => $volumeRows,
            'volumeLabels' => array_column($volumeRows, 'date'),
            'volumeValues' => array_column($volumeRows, 'orders'),
        ];
    }

    private function inventoryData(): array
    {
        $service = new InventoryReportService();
        $summary = $service->summary($this->filter());
        $rows = $service->consumption($this->filter());
        $levels = $service->levels();

        return [
            'title' => 'Inventory report',
            'columns' => $this->columns('inventory'),
            'rows' => $rows,
            'summary' => [
                'Current stock (units)' => Yii::$app->formatter->asInteger($summary['current_stock']),
                'Low stock items' => Yii::$app->formatter->asInteger($summary['low_stock']),
                'Out of stock items' => Yii::$app->formatter->asInteger($summary['out_of_stock']),
                'Fast moving items' => Yii::$app->formatter->asInteger($summary['fast_moving']),
            ],
            'consumptionRows' => $rows,
            'consumptionLabels' => array_column($rows, 'item'),
            'consumptionValues' => array_column($rows, 'consumption'),
            'levelLabels' => array_column($levels, 'name'),
            'levelValues' => array_column($levels, 'current_stock'),
            'minimumValues' => array_column($levels, 'minimum_stock'),
        ];
    }

    private function filter(): ReportFilter
    {
        $filter = new ReportFilter();
        $filter->load(Yii::$app->request->queryParams);
        $filter->validate();

        return $filter;
    }

    /**
     * @return list<array{label: string, attribute: string, type?: string}>
     */
    private function columns(string $report): array
    {
        return match ($report) {
            'sales' => [
                ['label' => 'Date', 'attribute' => 'date', 'type' => 'date'],
                ['label' => 'Revenue', 'attribute' => 'revenue', 'type' => 'money'],
                ['label' => 'Orders', 'attribute' => 'orders', 'type' => 'number'],
            ],
            'product' => [
                ['label' => 'Product Name', 'attribute' => 'product_name'],
                ['label' => 'Quantity Sold', 'attribute' => 'quantity', 'type' => 'number'],
                ['label' => 'Revenue Generated', 'attribute' => 'revenue', 'type' => 'money'],
            ],
            'orders' => [
                ['label' => 'Order ID', 'attribute' => 'order_number'],
                ['label' => 'Customer', 'attribute' => 'customer'],
                ['label' => 'Status', 'attribute' => 'status', 'type' => 'status'],
                ['label' => 'Total', 'attribute' => 'total_amount', 'type' => 'money'],
                ['label' => 'Created At', 'attribute' => 'created_at', 'type' => 'date'],
            ],
            'inventory' => [
                ['label' => 'Inventory Item', 'attribute' => 'item'],
                ['label' => 'Current Stock', 'attribute' => 'current_stock', 'type' => 'number'],
                ['label' => 'Minimum Stock', 'attribute' => 'minimum_stock', 'type' => 'number'],
                ['label' => 'Consumption Rate / day', 'attribute' => 'rate', 'type' => 'decimal'],
            ],
        };
    }

    /**
     * @return array<int|string, string>
     */
    private function customerOptions(): array
    {
        $rows = User::find()
            ->select(new Expression("[[id]], COALESCE(NULLIF([[full_name]], ''), [[username]]) AS [[label]]"))
            ->orderBy(['username' => SORT_ASC])
            ->asArray()
            ->all();

        return ArrayHelper::map($rows, 'id', 'label');
    }
}
