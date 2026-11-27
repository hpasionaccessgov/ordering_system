<?php

declare(strict_types=1);

namespace app\tests\Unit\Frontend;

use Codeception\Test\Unit;

final class ProductEditorFlagsTest extends Unit
{
    public function testFeaturedAndSpecialCheckboxesAreMutuallyExclusive(): void
    {
        $form = (string) file_get_contents(dirname(__DIR__, 3) . '/views/product/_form.php');

        self::assertStringContainsString("document.getElementById('product-is_featured')", $form);
        self::assertStringContainsString("document.getElementById('product-is_special')", $form);
        self::assertStringContainsString('other.disabled = active.checked;', $form);
        self::assertStringContainsString("featured.addEventListener('change'", $form);
        self::assertStringContainsString("special.addEventListener('change'", $form);
    }

    public function testValidCheckboxKeepsCafebaraColor(): void
    {
        $css = (string) file_get_contents(dirname(__DIR__, 3) . '/web/css/cafebara-theme.css');

        self::assertStringContainsString(
            ".form-check-input.is-valid,\n.was-validated .form-check-input:valid {\n    border-color: var(--cafebara-brown);",
            $css,
        );
        self::assertStringContainsString(
            ".form-check-input.is-valid:checked,\n.was-validated .form-check-input:valid:checked {\n    background-color: var(--cafebara-brown);",
            $css,
        );
        self::assertStringContainsString(
            ".form-check-input.is-valid:focus,\n.was-validated .form-check-input:valid:focus {\n    box-shadow: 0 0 0 .25rem rgba(var(--cafebara-brown-rgb), .2);",
            $css,
        );
    }
}
