<?php

declare(strict_types=1);

use app\services\LoyaltyService;
use yii\helpers\Html;

/** @var string $tier */

$tier = $tier ?? LoyaltyService::TIER_BRONZE;
?>
<span class="badge <?= Html::encode(LoyaltyService::tierBadgeClass($tier)) ?>">
    [<?= Html::encode(LoyaltyService::tierLabel($tier)) ?>]
</span>
