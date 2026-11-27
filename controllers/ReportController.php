<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\ReportFilter;
use app\models\User;
use app\services\InventoryReportService;
use app\services\LoyaltyReportService;
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

    private const REPORTS = ['sales', 'product', 'orders', 'inventory', 'loyalty'];
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

    public function actionLoyalty(): string
    {
        return $this->render('loyalty', ['filter' => $this->filter()] + $this->build('loyalty'));
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
            $data['charts'] ?? [],
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
            'loyalty' => $this->loyaltyData(),
        };
    }

    /**
     * @param list<string|int> $labels
     * @param list<int|float|string> $values
     * @return array{title: string, labels: list<string>, values: list<float>, format: string}
     */
    private function chart(string $title, array $labels, array $values, string $format = 'number'): array
    {
        return [
            'title' => $title,
            'labels' => array_slice(array_map('strval', array_values($labels)), -12),
            'values' => array_slice(array_map('floatval', array_values($values)), -12),
            'format' => $format,
        ];
    }

    private function salesData(): array
    {
        $service = new SalesReportService();
        $summary = $service->summary($this->filter());
        $rows = $service->daily($this->filter());
        $labels = array_column($rows, 'date');
        $revenue = array_column($rows, 'revenue');
        $orders = array_column($rows, 'orders');

        return [
            'title' => 'Sales report',
            'columns' => $this->columns('sales'),
            'rows' => $rows,
            'summary' => [
                ['label' => 'Total revenue', 'value' => Yii::$app->formatter->asCurrency($summary['revenue']), 'caption' => 'Completed orders in range', 'icon' => 'bi-cash-coin'],
                ['label' => 'Total orders', 'value' => Yii::$app->formatter->asInteger($summary['orders']), 'caption' => 'All statuses in range', 'icon' => 'bi-receipt'],
                ['label' => 'Average order value', 'value' => Yii::$app->formatter->asCurrency($summary['aov']), 'caption' => 'Completed orders in range', 'icon' => 'bi-graph-up'],
                ['label' => 'Completed orders', 'value' => Yii::$app->formatter->asInteger($summary['completed']), 'caption' => 'Completed in range', 'icon' => 'bi-check2-circle'],
            ],
            'revenueLabels' => $labels,
            'revenueValues' => $revenue,
            'orderValues' => $orders,
            'charts' => [
                $this->chart('Revenue by day', $labels, $revenue, 'money'),
                $this->chart('Orders by day', $labels, $orders),
            ],
        ];
    }

    private function productData(): array
    {
        $service = new ProductReportService();
        $ranking = $service->ranking($this->filter());
        $top = array_slice($ranking, 0, 10);
        $labels = array_column($top, 'product_name');
        $quantities = array_column($top, 'quantity');
        $revenues = array_column($top, 'revenue');

        return [
            'title' => 'Product performance report',
            'columns' => $this->columns('product'),
            'rows' => $top,
            'summary' => [
                ['label' => 'Top selling product', 'value' => $top !== [] ? $top[0]['product_name'] . ' (' . $top[0]['quantity'] . ')' : 'No sales', 'caption' => 'By units sold in range', 'icon' => 'bi-trophy'],
                ['label' => 'Products with sales', 'value' => Yii::$app->formatter->asInteger(count($ranking)), 'caption' => 'Active products sold in range', 'icon' => 'bi-cup-hot'],
                ['label' => 'Units sold', 'value' => Yii::$app->formatter->asInteger(array_sum(array_column($ranking, 'quantity'))), 'caption' => 'Completed orders in range', 'icon' => 'bi-box-seam'],
                ['label' => 'Revenue generated', 'value' => Yii::$app->formatter->asCurrency(array_sum(array_column($ranking, 'revenue'))), 'caption' => 'Completed orders in range', 'icon' => 'bi-cash-coin'],
            ],
            'topRows' => $top,
            'leastRows' => $service->least($this->filter()),
            'topLabels' => $labels,
            'topValues' => $quantities,
            'revenueValues' => $revenues,
            'charts' => [
                $this->chart('Units sold by product', $labels, $quantities),
                $this->chart('Revenue by product', $labels, $revenues, 'money'),
            ],
        ];
    }

    private function ordersData(): array
    {
        $service = new OrderReportService();
        $summary = $service->summary($this->filter());
        $statusRows = $service->statusBreakdown($this->filter());
        $volumeRows = $service->dailyVolume($this->filter());
        $statusLabels = array_map(
            static fn(array $row): string => \app\models\Order::statusLabel((string) $row['status']),
            $statusRows,
        );
        $statusValues = array_column($statusRows, 'orders');
        $volumeLabels = array_column($volumeRows, 'date');
        $volumeValues = array_column($volumeRows, 'orders');

        return [
            'title' => 'Order report',
            'columns' => $this->columns('orders'),
            'rows' => $service->rows($this->filter()),
            'summary' => [
                ['label' => 'Total orders', 'value' => Yii::$app->formatter->asInteger($summary['total']), 'caption' => 'All statuses in range', 'icon' => 'bi-receipt'],
                ['label' => 'Completed orders', 'value' => Yii::$app->formatter->asInteger($summary['completed']), 'caption' => 'Completed in range', 'icon' => 'bi-check2-circle'],
                ['label' => 'Cancelled orders', 'value' => Yii::$app->formatter->asInteger($summary['cancelled']), 'caption' => 'Cancelled in range', 'icon' => 'bi-x-circle'],
                ['label' => 'In progress', 'value' => Yii::$app->formatter->asInteger($summary['pending'] + $summary['confirmed'] + $summary['processing'] + $summary['ready_for_pickup']), 'caption' => 'Pending to ready for pickup', 'icon' => 'bi-hourglass-split'],
            ],
            'statusRows' => $statusRows,
            'statusLabels' => $statusLabels,
            'statusValues' => $statusValues,
            'volumeRows' => $volumeRows,
            'volumeLabels' => $volumeLabels,
            'volumeValues' => $volumeValues,
            'charts' => [
                $this->chart('Orders by status', $statusLabels, $statusValues),
                $this->chart('Order volume by day', $volumeLabels, $volumeValues),
            ],
        ];
    }

    private function inventoryData(): array
    {
        $service = new InventoryReportService();
        $summary = $service->summary($this->filter());
        $rows = $service->consumption($this->filter());
        $levels = $service->levels();
        $consumptionLabels = array_column($rows, 'item');
        $consumptionValues = array_column($rows, 'consumption');
        $levelLabels = array_column($levels, 'name');
        $levelValues = array_column($levels, 'current_stock');

        return [
            'title' => 'Inventory report',
            'columns' => $this->columns('inventory'),
            'rows' => $rows,
            'summary' => [
                ['label' => 'Current stock', 'value' => Yii::$app->formatter->asInteger($summary['current_stock']), 'caption' => 'Units on hand', 'icon' => 'bi-box-seam'],
                ['label' => 'Low stock items', 'value' => Yii::$app->formatter->asInteger($summary['low_stock']), 'caption' => 'Below minimum', 'icon' => 'bi-exclamation-triangle'],
                ['label' => 'Out of stock items', 'value' => Yii::$app->formatter->asInteger($summary['out_of_stock']), 'caption' => 'Need restocking', 'icon' => 'bi-x-circle'],
                ['label' => 'Fast moving items', 'value' => Yii::$app->formatter->asInteger($summary['fast_moving']), 'caption' => 'Deducted in range', 'icon' => 'bi-lightning-charge'],
            ],
            'consumptionRows' => $rows,
            'consumptionLabels' => $consumptionLabels,
            'consumptionValues' => $consumptionValues,
            'levelLabels' => $levelLabels,
            'levelValues' => $levelValues,
            'minimumValues' => array_column($levels, 'minimum_stock'),
            'charts' => [
                $this->chart('Consumption by item', $consumptionLabels, $consumptionValues, 'decimal'),
                $this->chart('Current stock by item', $levelLabels, $levelValues),
            ],
        ];
    }

    private function loyaltyData(): array
    {
        $service = new LoyaltyReportService();
        $filter = $this->filter();
        $summary = $service->summary($filter);
        $tiers = $service->tiers($filter);
        $rows = $service->rows($filter);
        $tierLabels = array_column($tiers, 'label');
        $tierValues = array_column($tiers, 'members');
        $dates = array_column($rows, 'date');

        return [
            'title' => 'Loyalty report',
            'columns' => $this->columns('loyalty'),
            'rows' => $rows,
            'summary' => [
                ['label' => 'Total points issued', 'value' => Yii::$app->formatter->asInteger($summary['issued']), 'caption' => 'Earned in range', 'icon' => 'bi-coin'],
                ['label' => 'Total points redeemed', 'value' => Yii::$app->formatter->asInteger($summary['redeemed']), 'caption' => 'Used in range', 'icon' => 'bi-gift'],
                ['label' => 'Active loyalty members', 'value' => Yii::$app->formatter->asInteger($summary['activeMembers']), 'caption' => 'With activity in range', 'icon' => 'bi-people'],
                ['label' => 'Tier tracked', 'value' => '3', 'caption' => 'Bronze / Silver / Gold', 'icon' => 'bi-trophy'],
            ],
            'tierLabels' => $tierLabels,
            'tierValues' => $tierValues,
            'volumeLabels' => $dates,
            'earnedValues' => array_column($rows, 'earned'),
            'redeemedValues' => array_column($rows, 'redeemed'),
            'charts' => [
                $this->chart('Members by tier', $tierLabels, $tierValues),
                $this->chart('Points issued by day', $dates, array_column($rows, 'earned')),
                $this->chart('Points redeemed by day', $dates, array_column($rows, 'redeemed')),
            ],
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
            'loyalty' => [
                ['label' => 'Date', 'attribute' => 'date', 'type' => 'date'],
                ['label' => 'Points issued', 'attribute' => 'earned', 'type' => 'number'],
                ['label' => 'Points redeemed', 'attribute' => 'redeemed', 'type' => 'number'],
                ['label' => 'Active members', 'attribute' => 'members', 'type' => 'number'],
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
