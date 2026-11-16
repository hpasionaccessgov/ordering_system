<?php

declare(strict_types=1);

namespace tests\unit\widgets;

use app\widgets\ConfirmModal;
use Codeception\Test\Unit;

final class ConfirmModalTest extends Unit
{
    public function testRendersAccessibleConfirmationModalControls(): void
    {
        $renderingResult = ConfirmModal::widget();

        self::assertStringContainsString('data-confirm-modal-root', $renderingResult);
        self::assertStringContainsString('data-confirm-modal-message', $renderingResult);
        self::assertStringContainsString('data-confirm-modal-accept', $renderingResult);
        self::assertStringContainsString('aria-labelledby=', $renderingResult);
    }
}
