<?php

declare(strict_types=1);

namespace app\services;

use app\models\LoyaltyPointTransaction;
use app\models\ReportFilter;
use Yii;

final class LoyaltyReportService
{
    /**
     * @return array{issued: int, redeemed: int, activeMembers: int}
     */
    public function summary(ReportFilter $filter): array
    {
        [$from, $to] = $filter->range();
        $row = LoyaltyPointTransaction::find()
            ->select([
                'issued' => 'COALESCE(SUM(CASE WHEN [[transaction_type]] = :earn THEN [[points]] ELSE 0 END), 0)',
                'redeemed' => 'COALESCE(ABS(SUM(CASE WHEN [[transaction_type]] = :redeem THEN [[points]] ELSE 0 END)), 0)',
                'activeMembers' => 'COUNT(DISTINCT [[user_id]])',
            ])
            ->addParams([
                ':earn' => LoyaltyPointTransaction::TYPE_EARN,
                ':redeem' => LoyaltyPointTransaction::TYPE_REDEEM,
            ])
            ->where(['between', 'created_at', $from, $to])
            ->asArray()
            ->one();

        return [
            'issued' => (int) ($row['issued'] ?? 0),
            'redeemed' => (int) ($row['redeemed'] ?? 0),
            'activeMembers' => (int) ($row['activeMembers'] ?? 0),
        ];
    }

    /**
     * Tier of every member active in the selected range, using lifetime earned points up to the range end.
     *
     * @return list<array{tier: string, label: string, members: int}>
     */
    public function tiers(ReportFilter $filter): array
    {
        [$from, $to] = $filter->range();
        $rows = Yii::$app->db->createCommand(
            'SELECT [[user_id]], SUM(CASE WHEN [[transaction_type]] = :earn THEN [[points]] ELSE 0 END) AS [[earned]]
            FROM {{%loyalty_point_transaction}}
            WHERE [[created_at]] <= :to
              AND [[user_id]] IN (
                  SELECT DISTINCT [[user_id]] FROM {{%loyalty_point_transaction}}
                  WHERE [[created_at]] BETWEEN :from AND :to
              )
            GROUP BY [[user_id]]',
            [
                ':earn' => LoyaltyPointTransaction::TYPE_EARN,
                ':from' => $from,
                ':to' => $to,
            ],
        )->queryAll();

        $counts = [
            LoyaltyService::TIER_BRONZE => 0,
            LoyaltyService::TIER_SILVER => 0,
            LoyaltyService::TIER_GOLD => 0,
        ];
        foreach ($rows as $row) {
            $tier = LoyaltyService::tierFor((int) $row['earned']);
            $counts[$tier]++;
        }

        $result = [];
        foreach (LoyaltyService::tierLabels() as $tier => $label) {
            $result[] = [
                'tier' => $tier,
                'label' => $label,
                'members' => $counts[$tier],
            ];
        }

        return $result;
    }

    /**
     * @return list<array{date: string, earned: int, redeemed: int, members: int}>
     */
    public function rows(ReportFilter $filter): array
    {
        [$from, $to] = $filter->range();
        $rows = LoyaltyPointTransaction::find()
            ->select([
                'date' => 'DATE(FROM_UNIXTIME([[created_at]]))',
                'earned' => 'COALESCE(SUM(CASE WHEN [[transaction_type]] = :earn THEN [[points]] ELSE 0 END), 0)',
                'redeemed' => 'COALESCE(ABS(SUM(CASE WHEN [[transaction_type]] = :redeem THEN [[points]] ELSE 0 END)), 0)',
                'members' => 'COUNT(DISTINCT [[user_id]])',
            ])
            ->addParams([
                ':earn' => LoyaltyPointTransaction::TYPE_EARN,
                ':redeem' => LoyaltyPointTransaction::TYPE_REDEEM,
            ])
            ->where(['between', 'created_at', $from, $to])
            ->groupBy(['date'])
            ->orderBy(['date' => SORT_ASC])
            ->asArray()
            ->all();

        return array_map(
            static fn(array $row): array => [
                'date' => (string) $row['date'],
                'earned' => (int) $row['earned'],
                'redeemed' => (int) $row['redeemed'],
                'members' => (int) $row['members'],
            ],
            $rows,
        );
    }
}
