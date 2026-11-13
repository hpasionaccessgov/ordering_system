<?php

declare(strict_types=1);

namespace tests\unit\widgets;

use app\widgets\AdminButton;
use yii\base\InvalidArgumentException;

class AdminButtonTest extends \Codeception\Test\Unit
{
    public function testSubmitButtonUsesThemeClassesAndKeepsOptions(): void
    {
        $button = AdminButton::submit('Save profile', 'outline-primary', [
            'class' => 'w-100',
            'data-test' => 'save-profile',
        ]);

        verify($button)->stringContainsString('btn-outline-primary');
        verify($button)->stringContainsString('w-100');
        verify($button)->stringContainsString('data-test="save-profile"');
        verify($button)->stringContainsString('Save profile');
    }

    public function testButtonLabelIsEncoded(): void
    {
        $button = AdminButton::button('<script>alert(1)</script>');

        verify($button)->stringNotContainsString('<script>');
        verify($button)->stringContainsString('&lt;script&gt;');
    }

    public function testVariantsUsedByAdminViewsRender(): void
    {
        foreach (['outline-primary', 'outline-danger', 'outline-warning', 'cafebara-light', 'link'] as $variant) {
            $button = AdminButton::submit('Action', $variant);

            verify($button)->stringContainsString('btn-' . $variant);
        }
    }

    public function testUnsupportedVariantIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AdminButton::submit('Save', 'not-a-variant');
    }
}
