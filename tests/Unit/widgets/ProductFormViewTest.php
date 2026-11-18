<?php

declare(strict_types=1);

namespace app\tests\Unit\widgets;

use app\models\Product;
use Codeception\Test\Unit;
use Yii;
use yii\web\View;

final class ProductFormViewTest extends Unit
{
    public function testCreateAndUpdatePassCategoriesToForm(): void
    {
        Yii::$app->request->setScriptUrl('/index.php');
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(['__get'])
            ->getMock();
        $product->method('__get')->willReturn('Test product');
        $categories = [7 => 'Coffee'];

        foreach (['create', 'update'] as $page) {
            $view = $this->getMockBuilder(View::class)
                ->onlyMethods(['render'])
                ->getMock();
            $view->expects(self::once())
                ->method('render')
                ->with('_form', ['model' => $product, 'categories' => $categories])
                ->willReturn('form rendered');

            $html = $view->renderFile(
                Yii::getAlias("@app/views/product/{$page}.php"),
                ['model' => $product, 'categories' => $categories],
            );
            self::assertStringContainsString('form rendered', $html);
        }
    }
}
