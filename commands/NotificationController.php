<?php

declare(strict_types=1);

namespace app\commands;

use app\services\DailySummaryNotificationService;
use yii\console\Controller;
use yii\console\ExitCode;

class NotificationController extends Controller
{
    public function actionDailySummary(?string $date = null): int
    {
        $dayStart = null;
        if ($date !== null) {
            $ts = strtotime($date);
            if ($ts === false) {
                $this->stderr("Invalid date: {$date}\n");
                return ExitCode::USAGE;
            }
            $dayStart = $ts;
        }

        $created = DailySummaryNotificationService::createIfMissing($dayStart);
        $this->stdout($created > 0 ? "Daily summary notification created.\n" : "Daily summary already exists.\n");

        return ExitCode::OK;
    }
}
