<?php

declare(strict_types=1);

namespace app\exceptions;

/**
 * Raised when an order cannot be completed because stock is short.
 */
final class InsufficientInventoryException extends \RuntimeException
{
    /**
     * @param list<array{name: string, needed: int, available: int}> $shortages
     */
    public function __construct(array $shortages)
    {
        $lines = ['Cannot complete order.', '', 'Insufficient inventory:'];
        foreach ($shortages as $shortage) {
            $lines[] = sprintf(
                '- %s needs %d, available %d',
                $shortage['name'],
                $shortage['needed'],
                $shortage['available'],
            );
        }

        parent::__construct(implode("\n", $lines));
    }
}
