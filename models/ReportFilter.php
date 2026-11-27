<?php

declare(strict_types=1);

namespace app\models;

use DateTimeImmutable;
use DateTimeZone;
use Yii;
use yii\base\Model;

/**
 * Shared date / status / customer filter for every report page and export.
 */
final class ReportFilter extends Model
{
    public const PRESETS = ['today', 'week', 'month', 'year', 'custom'];

    public string $preset = 'week';
    public string $from = '';
    public string $to = '';
    public string $status = '';
    private ?int $customerId = null;

    public function attributes(): array
    {
        return array_merge(parent::attributes(), ['customerId']);
    }

    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }

    public function setCustomerId(mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->customerId = null;

            return;
        }

        $id = is_int($value) ? $value : (is_string($value) && ctype_digit($value) ? (int) $value : 0);
        $this->customerId = $id > 0 ? $id : null;
    }

    public function rules(): array
    {
        return [
            [['preset'], 'in', 'range' => self::PRESETS],
            [['from', 'to'], 'trim'],
            [['from', 'to'], 'date', 'format' => 'php:Y-m-d'],
            [['from', 'to'], 'default', 'value' => ''],
            [['to'], 'compare', 'compareAttribute' => 'from', 'operator' => '>=', 'skipOnEmpty' => true,
                'when' => fn(self $model): bool => $model->from !== '' && !$model->hasErrors('from')],
            [['status'], 'in', 'range' => Order::statusValues(), 'skipOnEmpty' => true],
            [['status'], 'default', 'value' => ''],
            [['customerId'], 'integer', 'min' => 1],
            [['customerId'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ];
    }

    /**
     * @return array{int, int} inclusive Unix start and end of the selected range
     */
    public function range(): array
    {
        $zone = new DateTimeZone((string) (Yii::$app->formatter->timeZone ?: Yii::$app->timeZone));
        $end = (new DateTimeImmutable('tomorrow', $zone))->setTime(0, 0)->getTimestamp() - 1;

        if ($this->preset !== 'custom' || $this->from === '' || $this->to === '' || $this->hasErrors()) {
            $startExpression = match ($this->preset) {
                'week' => 'monday this week',
                'month' => 'first day of this month',
                'year' => 'first day of january this year',
                default => 'today',
            };

            return [(new DateTimeImmutable($startExpression, $zone))->setTime(0, 0)->getTimestamp(), $end];
        }

        return [
            (new DateTimeImmutable($this->from, $zone))->setTime(0, 0)->getTimestamp(),
            (new DateTimeImmutable($this->to, $zone))->modify('+1 day')->setTime(0, 0)->getTimestamp() - 1,
        ];
    }

    public function days(): int
    {
        [$from, $to] = $this->range();

        return max(1, intdiv($to - $from, 86400) + 1);
    }

    public function label(): string
    {
        $presetLabels = [
            'today' => 'Today',
            'week' => 'This week',
            'month' => 'This month',
            'year' => 'This year',
            'custom' => 'Custom range',
        ];
        [$from, $to] = $this->range();
        $formatter = Yii::$app->formatter;
        $label = ($presetLabels[$this->preset] ?? 'Today')
            . ': ' . $formatter->asDate($from, 'php:Y-m-d')
            . ' to ' . $formatter->asDate($to, 'php:Y-m-d');

        if ($this->status !== '') {
            $label .= '; Status: ' . ucfirst($this->status);
        }
        if ($this->customerId !== null) {
            $customer = User::findOne($this->customerId);
            $label .= '; Customer: ' . ($customer?->full_name ?: $customer?->username ?: '#' . $this->customerId);
        }

        return $label;
    }
}
