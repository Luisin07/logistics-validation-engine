<?php declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('America/Sao_Paulo');

/*
|--------------------------------------------------------------------------
| DADOS MOCK
|--------------------------------------------------------------------------
*/

$cepRanges = [
    [
        'region_id' => 'bairro_a',
        'region_name' => 'Bairro A',
        'cep_start' => '01000000',
        'cep_end' => '01009999',
        'freight_value' => 12.50,
        'status' => 'active',
        'delivery_profile' => 'Entrega agendada',
    ],
    [
        'region_id' => 'bairro_b',
        'region_name' => 'Bairro B',
        'cep_start' => '02000000',
        'cep_end' => '02009999',
        'freight_value' => 8.00,
        'status' => 'active',
        'delivery_profile' => 'Entrega padrão',
    ],
    [
        'region_id' => 'bairro_c',
        'region_name' => 'Bairro C',
        'cep_start' => '03000000',
        'cep_end' => '03009999',
        'freight_value' => 15.00,
        'status' => 'paused',
        'delivery_profile' => 'Região restrita',
    ],
];

$blockedLocations = [
    [
        'type' => 'CEP',
        'value' => '01005000',
        'reason' => 'CEP temporariamente indisponível para entrega.',
    ],
    [
        'type' => 'REGION',
        'value' => 'bairro_c',
        'reason' => 'Região temporariamente suspensa por restrição operacional.',
    ],
];

$regionRules = [
    [
        'region_id' => 'bairro_a',
        'allowed_weekdays' => [0, 2],
    ],
    [
        'region_id' => 'bairro_b',
        'allowed_weekdays' => [0, 1, 2, 3, 4],
    ],
    [
        'region_id' => 'bairro_c',
        'allowed_weekdays' => [1, 3, 5],
    ],
];

$slots = [
    [
        'id' => 'slot_1',
        'weekday' => 0,
        'start_time' => '08:00',
        'end_time' => '12:00',
        'capacity_max' => 10,
        'current_bookings' => 4,
        'active' => true,
    ],
    [
        'id' => 'slot_2',
        'weekday' => 2,
        'start_time' => '14:00',
        'end_time' => '18:00',
        'capacity_max' => 5,
        'current_bookings' => 5,
        'active' => true,
    ],
    [
        'id' => 'slot_3',
        'weekday' => 4,
        'start_time' => '09:00',
        'end_time' => '13:00',
        'capacity_max' => 8,
        'current_bookings' => 2,
        'active' => true,
    ],
    [
        'id' => 'slot_4',
        'weekday' => 1,
        'start_time' => '10:00',
        'end_time' => '14:00',
        'capacity_max' => 6,
        'current_bookings' => 1,
        'active' => true,
    ],
    [
        'id' => 'slot_5',
        'weekday' => 5,
        'start_time' => '08:00',
        'end_time' => '11:00',
        'capacity_max' => 4,
        'current_bookings' => 0,
        'active' => true,
    ],
    [
        'id' => 'slot_6',
        'weekday' => 3,
        'start_time' => '13:00',
        'end_time' => '18:00',
        'capacity_max' => 7,
        'current_bookings' => 7,
        'active' => true,
    ],
    [
        'id' => 'slot_7',
        'weekday' => 1,
        'start_time' => '07:00',
        'end_time' => '09:00',
        'capacity_max' => 5,
        'current_bookings' => 2,
        'active' => false,
    ],
];

$leadTimeHours = 18;

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

function normalizeCep(string $cep): string
{
    return str_pad(preg_replace('/\D/', '', $cep), 8, '0', STR_PAD_LEFT);
}

function isValidCep(string $cep): bool
{
    $normalized = normalizeCep($cep);
    return strlen($normalized) === 8 && ctype_digit($normalized);
}

function formatCurrency(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function formatDisplayDate(string $date): string
{
    try {
        $dateTime = new DateTime($date);
        return $dateTime->format('d/m/Y \à\s H:i');
    } catch (Exception $e) {
        return $date;
    }
}

function getWeekdayLabel(int $weekday): string
{
    $days = [
        0 => 'Segunda-feira',
        1 => 'Terça-feira',
        2 => 'Quarta-feira',
        3 => 'Quinta-feira',
        4 => 'Sexta-feira',
        5 => 'Sábado',
        6 => 'Domingo',
    ];

    return $days[$weekday] ?? 'Dia inválido';
}

function getWeekdaySortOrder(int $weekday): int
{
    $order = [
        6 => 0, // domingo
        0 => 1, // segunda
        1 => 2, // terça
        2 => 3, // quarta
        3 => 4, // quinta
        4 => 5, // sexta
        5 => 6, // sábado
    ];

    return $order[$weekday] ?? 99;
}

function sortSlotsByWeekdayAndTime(array &$slots): void
{
    usort($slots, function (array $a, array $b): int {
        $weekdayCompare = getWeekdaySortOrder((int) $a['weekday']) <=> getWeekdaySortOrder((int) $b['weekday']);
        if ($weekdayCompare !== 0) {
            return $weekdayCompare;
        }

        $dateCompare = strcmp((string) ($a['date'] ?? ''), (string) ($b['date'] ?? ''));
        if ($dateCompare !== 0) {
            return $dateCompare;
        }

        return strcmp((string) $a['start_time'], (string) $b['start_time']);
    });
}

function getRegionStatusLabel(string $status): string
{
    return match ($status) {
        'active' => 'Ativa',
        'paused' => 'Pausada',
        'blocked' => 'Bloqueada',
        default => 'Indefinida',
    };
}

function findRegionByCep(string $cep, array $cepRanges): ?array
{
    $cepNumber = (int) normalizeCep($cep);

    foreach ($cepRanges as $range) {
        $start = (int) $range['cep_start'];
        $end = (int) $range['cep_end'];

        if ($cepNumber >= $start && $cepNumber <= $end) {
            return $range;
        }
    }

    return null;
}

function getBlockedReason(string $cep, string $regionId, array $blockedLocations): ?string
{
    $normalizedCep = normalizeCep($cep);

    foreach ($blockedLocations as $item) {
        if ($item['type'] === 'CEP' && $item['value'] === $normalizedCep) {
            return $item['reason'] ?? 'CEP bloqueado';
        }

        if ($item['type'] === 'REGION' && $item['value'] === $regionId) {
            return $item['reason'] ?? 'Região bloqueada';
        }
    }

    return null;
}

function getRegionRule(string $regionId, array $regionRules): ?array
{
    foreach ($regionRules as $rule) {
        if ($rule['region_id'] === $regionId) {
            return $rule;
        }
    }

    return null;
}

function getNextDateForWeekday(int $targetWeekday, DateTime $now): DateTime
{
    $date = clone $now;
    $currentWeekday = (int) $date->format('N') - 1;
    $diff = $targetWeekday - $currentWeekday;

    if ($diff < 0) {
        $diff += 7;
    }

    $date->modify("+{$diff} days");
    return $date;
}

function buildSlotStartDateTime(array $slot, DateTime $now): DateTime
{
    $date = getNextDateForWeekday((int) $slot['weekday'], $now);
    [$hour, $minute] = array_map('intval', explode(':', $slot['start_time']));
    $date->setTime($hour, $minute, 0);
    return $date;
}

function buildSlotEndDateTime(array $slot, DateTime $now): DateTime
{
    $date = getNextDateForWeekday((int) $slot['weekday'], $now);
    [$hour, $minute] = array_map('intval', explode(':', $slot['end_time']));
    $date->setTime($hour, $minute, 0);
    return $date;
}

function getSlotAvailableCapacity(array $slot): int
{
    return max(0, (int) $slot['capacity_max'] - (int) $slot['current_bookings']);
}

function evaluateSlot(array $slot, array $regionRule, DateTime $now, int $leadTimeHours): array
{
    $slotDate = getNextDateForWeekday((int) $slot['weekday'], $now);
    $slotStart = buildSlotStartDateTime($slot, $now);
    $slotEnd = buildSlotEndDateTime($slot, $now);

    $minimumAllowed = clone $now;
    $minimumAllowed->modify("+{$leadTimeHours} hours");

    $base = [
        'id' => $slot['id'],
        'weekday' => $slot['weekday'],
        'weekday_label' => getWeekdayLabel((int) $slot['weekday']),
        'date' => $slotDate->format('Y-m-d'),
        'date_label' => $slotDate->format('d/m/Y'),
        'start_time' => $slot['start_time'],
        'end_time' => $slot['end_time'],
        'capacity_max' => $slot['capacity_max'],
        'current_bookings' => $slot['current_bookings'],
        'available_capacity' => getSlotAvailableCapacity($slot),
        'active' => $slot['active'],
        'slot_start_iso' => $slotStart->format(DateTime::ATOM),
        'slot_end_iso' => $slotEnd->format(DateTime::ATOM),
    ];

    if ($slot['active'] !== true) {
        return $base + [
            'valid' => false,
            'reason' => 'Slot inativo.',
        ];
    }

    if (!in_array($slot['weekday'], $regionRule['allowed_weekdays'], true)) {
        return $base + [
            'valid' => false,
            'reason' => 'Dia do slot não permitido para a região.',
        ];
    }

    if ($slotStart < $minimumAllowed) {
        return $base + [
            'valid' => false,
            'reason' => 'Slot não respeita a antecedência mínima.',
        ];
    }

    if ((int) $slot['current_bookings'] >= (int) $slot['capacity_max']) {
        return $base + [
            'valid' => false,
            'reason' => 'Slot sem capacidade disponível.',
        ];
    }

    return $base + [
        'valid' => true,
        'reason' => null,
    ];
}

function processDeliverySlots(array $slots, array $regionRule, DateTime $now, int $leadTimeHours): array
{
    $validSlots = [];
    $invalidSlots = [];

    foreach ($slots as $slot) {
        $evaluation = evaluateSlot($slot, $regionRule, $now, $leadTimeHours);

        if ($evaluation['valid']) {
            $validSlots[] = $evaluation;
        } else {
            $invalidSlots[] = $evaluation;
        }
    }

    sortSlotsByWeekdayAndTime($validSlots);
    sortSlotsByWeekdayAndTime($invalidSlots);

    return [
        'valid_slots' => $validSlots,
        'invalid_slots' => $invalidSlots,
        'checked_slots' => count($slots),
    ];
}

function calculateFreight(array $region): float
{
    return (float) $region['freight_value'];
}

function buildUserSummary(array $result): string
{
    if (($result['success'] ?? false) === true) {
        $region = $result['data']['region_name'] ?? 'região identificada';
        $slots = count($result['data']['available_slots'] ?? []);
        return "CEP atendido com sucesso. A {$region} possui {$slots} janela(s) elegível(is) para entrega no cenário atual.";
    }

    $message = $result['message'] ?? '';

    return match ($message) {
        'CEP inválido.' => 'O CEP informado não passou na validação básica de formato. Ajuste a entrada e tente novamente.',
        'CEP não atendido.' => 'O CEP informado está fora da malha de atendimento configurada neste mock logístico.',
        'Região bloqueada.' => 'A região foi localizada, porém está temporariamente indisponível para operação.',
        'Sem janelas elegíveis.' => 'O CEP é atendido, mas nenhuma janela de entrega ficou elegível após aplicar as regras operacionais.',
        default => 'A operação não encontrou elegibilidade de entrega para o cenário atual.',
    };
}

function getDeliveryOptions(
    string $cep,
    DateTime $now,
    array $cepRanges,
    array $blockedLocations,
    array $regionRules,
    array $slots,
    int $leadTimeHours
): array {
    $normalizedCep = normalizeCep($cep);
    $flow = [];

    $flow[] = 'CEP recebido na entrada do service';
    $flow[] = 'CEP normalizado para o formato interno';

    if (!isValidCep($cep)) {
        $flow[] = 'Falha na validação do CEP';

        $result = [
            'success' => false,
            'message' => 'CEP inválido.',
            'data' => null,
            'flow' => $flow,
            'meta' => [
                'input_cep' => $cep,
                'normalized_cep' => $normalizedCep,
                'processed_at' => $now->format(DateTime::ATOM),
            ],
        ];

        $result['summary_text'] = buildUserSummary($result);
        return $result;
    }

    $flow[] = 'CEP validado com sucesso';

    $region = findRegionByCep($cep, $cepRanges);

    if (!$region) {
        $flow[] = 'Busca por faixa de CEP executada';
        $flow[] = 'Nenhuma região encontrada';

        $result = [
            'success' => false,
            'message' => 'CEP não atendido.',
            'data' => null,
            'flow' => $flow,
            'meta' => [
                'input_cep' => $cep,
                'normalized_cep' => $normalizedCep,
                'processed_at' => $now->format(DateTime::ATOM),
            ],
        ];

        $result['summary_text'] = buildUserSummary($result);
        return $result;
    }

    $flow[] = 'Região encontrada: ' . $region['region_name'];

    $blockedReason = getBlockedReason($cep, $region['region_id'], $blockedLocations);

    if ($blockedReason !== null) {
        $flow[] = 'Verificação de bloqueio executada';
        $flow[] = 'Local bloqueado para entrega';

        $result = [
            'success' => false,
            'message' => 'Região bloqueada.',
            'data' => [
                'region_id' => $region['region_id'],
                'region_name' => $region['region_name'],
                'region_status' => getRegionStatusLabel((string) ($region['status'] ?? 'paused')),
                'delivery_profile' => $region['delivery_profile'] ?? '-',
                'freight_value' => $region['freight_value'],
                'freight_label' => formatCurrency((float) $region['freight_value']),
                'blocked' => true,
                'blocked_reason' => $blockedReason,
                'available_slots' => [],
                'unavailable_slots' => [],
            ],
            'flow' => $flow,
            'meta' => [
                'input_cep' => $cep,
                'normalized_cep' => $normalizedCep,
                'processed_at' => $now->format(DateTime::ATOM),
            ],
        ];

        $result['summary_text'] = buildUserSummary($result);
        return $result;
    }

    $flow[] = 'Região liberada para entrega';

    $regionRule = getRegionRule($region['region_id'], $regionRules);

    if (!$regionRule) {
        $flow[] = 'Falha ao carregar regras da região';

        $result = [
            'success' => false,
            'message' => 'Região sem regras configuradas.',
            'data' => null,
            'flow' => $flow,
            'meta' => [
                'input_cep' => $cep,
                'normalized_cep' => $normalizedCep,
                'processed_at' => $now->format(DateTime::ATOM),
            ],
        ];

        $result['summary_text'] = 'A região foi encontrada, mas não há regras operacionais cadastradas para processar a entrega.';
        return $result;
    }

    $flow[] = 'Regras da região carregadas';
    $flow[] = 'Início do processamento dos slots';

    $slotProcessing = processDeliverySlots($slots, $regionRule, $now, $leadTimeHours);
    $validSlots = $slotProcessing['valid_slots'];
    $invalidSlots = $slotProcessing['invalid_slots'];

    if (count($validSlots) === 0) {
        $flow[] = 'Processamento concluído sem slots válidos';

        $result = [
            'success' => false,
            'message' => 'Sem janelas elegíveis.',
            'data' => [
                'region_id' => $region['region_id'],
                'region_name' => $region['region_name'],
                'region_status' => getRegionStatusLabel((string) ($region['status'] ?? 'active')),
                'delivery_profile' => $region['delivery_profile'] ?? '-',
                'freight_value' => $region['freight_value'],
                'freight_label' => formatCurrency((float) $region['freight_value']),
                'blocked' => false,
                'available_slots' => [],
                'unavailable_slots' => $invalidSlots,
            ],
            'flow' => $flow,
            'meta' => [
                'input_cep' => $cep,
                'normalized_cep' => $normalizedCep,
                'processed_at' => $now->format(DateTime::ATOM),
                'lead_time_hours' => $leadTimeHours,
                'checked_slots' => $slotProcessing['checked_slots'],
                'valid_slots_found' => 0,
                'invalid_slots_found' => count($invalidSlots),
            ],
        ];

        $result['summary_text'] = buildUserSummary($result);
        return $result;
    }

    $flow[] = 'Processamento concluído com slots elegíveis';
    $flow[] = 'Frete calculado';
    $flow[] = 'Resposta final montada';

    $result = [
        'success' => true,
        'message' => 'Entrega disponível.',
        'data' => [
            'region_id' => $region['region_id'],
            'region_name' => $region['region_name'],
            'region_status' => getRegionStatusLabel((string) ($region['status'] ?? 'active')),
            'delivery_profile' => $region['delivery_profile'] ?? '-',
            'freight_value' => calculateFreight($region),
            'freight_label' => formatCurrency((float) $region['freight_value']),
            'blocked' => false,
            'available_slots' => $validSlots,
            'unavailable_slots' => $invalidSlots,
        ],
        'flow' => $flow,
        'meta' => [
            'input_cep' => $cep,
            'normalized_cep' => $normalizedCep,
            'processed_at' => $now->format(DateTime::ATOM),
            'lead_time_hours' => $leadTimeHours,
            'checked_slots' => $slotProcessing['checked_slots'],
            'valid_slots_found' => count($validSlots),
            'invalid_slots_found' => count($invalidSlots),
        ],
    ];

    $result['summary_text'] = buildUserSummary($result);
    return $result;
}