<?php declare(strict_types=1);

require_once __DIR__ . '/service.php';

$inputCep = trim($_GET['cep'] ?? '');

if ($inputCep === '') {
    header('Location: index.php');
    exit;
}

$result = getDeliveryOptions(
    $inputCep,
    new DateTime(),
    $cepRanges,
    $blockedLocations,
    $regionRules,
    $slots,
    $leadTimeHours
);

$showJson = true;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado da Validação Logística</title>
    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --bg: #050816;
            --panel: rgba(11, 19, 38, 0.86);
            --line: rgba(255,255,255,0.08);
            --text: #eef4ff;
            --muted: #9fb0cc;
            --muted-strong: #c7d4ec;
            --primary: #4f8cff;
            --primary-2: #7c5cff;
            --success-bg: rgba(34, 197, 94, 0.14);
            --success-text: #93f5b2;
            --error-bg: rgba(239, 68, 68, 0.14);
            --error-text: #ffb0b0;
            --warning-bg: rgba(245, 158, 11, 0.14);
            --warning-text: #ffd590;
            --shadow-lg: 0 28px 80px rgba(0, 0, 0, 0.42);
            --shadow-md: 0 18px 40px rgba(0, 0, 0, 0.24);
            --radius-xl: 26px;
            --radius-lg: 20px;
            --radius-md: 16px;
            --max-width: 1360px;
        }

        body {
            margin: 0;
            font-family: Inter, Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 0% 0%, rgba(79,140,255,0.18), transparent 26%),
                radial-gradient(circle at 100% 0%, rgba(124,92,255,0.16), transparent 24%),
                radial-gradient(circle at 80% 80%, rgba(32,201,151,0.10), transparent 18%),
                linear-gradient(180deg, #050816 0%, #071024 45%, #050816 100%);
            min-height: 100vh;
        }

        .page-shell {
            position: fixed;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .orb {
            position: absolute;
            border-radius: 999px;
            filter: blur(70px);
            opacity: 0.35;
        }

        .orb-a {
            width: 280px;
            height: 280px;
            top: 30px;
            left: -80px;
            background: #2f6fed;
        }

        .orb-b {
            width: 320px;
            height: 320px;
            top: 20px;
            right: -100px;
            background: #7c5cff;
        }

        .orb-c {
            width: 260px;
            height: 260px;
            bottom: 60px;
            left: 50%;
            transform: translateX(-50%);
            background: #20c997;
            opacity: 0.16;
        }

        .container {
            width: 100%;
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 34px 22px 60px;
            position: relative;
            z-index: 2;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            margin-bottom: 22px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(79,140,255,0.95), rgba(124,92,255,0.95));
            box-shadow: 0 16px 30px rgba(79,140,255,0.28);
            position: relative;
        }

        .brand-mark::before,
        .brand-mark::after {
            content: "";
            position: absolute;
            background: rgba(255,255,255,0.95);
            border-radius: 999px;
        }

        .brand-mark::before {
            width: 24px;
            height: 6px;
            left: 14px;
            top: 16px;
        }

        .brand-mark::after {
            width: 18px;
            height: 6px;
            left: 14px;
            top: 28px;
        }

        .brand-copy h1 {
            margin: 0;
            font-size: 18px;
            letter-spacing: 0.2px;
        }

        .brand-copy p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 14px;
            border-radius: 999px;
            text-decoration: none;
            color: #d9e4f7;
            font-size: 13px;
            font-weight: 700;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--line);
        }

        .action-link.primary {
            background: linear-gradient(135deg, var(--primary), #3b82f6 55%, var(--primary-2));
            color: white;
            border: none;
        }

        .result-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 22px;
            border-radius: 22px;
            margin-bottom: 22px;
            border: 1px solid var(--line);
            box-shadow: var(--shadow-md);
            backdrop-filter: blur(14px);
        }

        .result-banner.success {
            background: linear-gradient(90deg, rgba(34, 197, 94, 0.16), rgba(11, 19, 38, 0.92));
        }

        .result-banner.error {
            background: linear-gradient(90deg, rgba(239, 68, 68, 0.16), rgba(11, 19, 38, 0.92));
        }

        .result-banner .left h3 {
            margin: 0 0 6px;
            font-size: 22px;
        }

        .result-banner .left p {
            margin: 0;
            color: var(--muted-strong);
            line-height: 1.6;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 14px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 13px;
            white-space: nowrap;
        }

        .status-pill.success {
            background: var(--success-bg);
            color: var(--success-text);
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .status-pill.error {
            background: var(--error-bg);
            color: var(--error-text);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(14px);
            padding: 24px;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .panel-title {
            margin: 0;
            font-size: 22px;
            letter-spacing: -0.2px;
        }

        .panel-subtitle {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        .section-pill {
            padding: 9px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--line);
            color: var(--muted-strong);
            font-size: 12px;
            font-weight: 700;
        }

        .summary-banner {
            margin-bottom: 20px;
            padding: 18px 20px;
            border-radius: 20px;
            background: linear-gradient(90deg, rgba(255,255,255,0.04), rgba(79,140,255,0.05));
            border: 1px solid rgba(255,255,255,0.08);
            color: var(--muted-strong);
            line-height: 1.7;
            font-size: 14px;
        }

        .metrics-layout {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 18px;
            align-items: start;
        }

        .metrics-main {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .metrics-side {
            display: grid;
            gap: 14px;
        }

        .metric-inline-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .metric-stack {
            display: grid;
            gap: 14px;
        }

        .metric-card {
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, rgba(255,255,255,0.05), rgba(255,255,255,0.025));
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 18px 18px 16px;
            min-height: 118px;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            border-color: rgba(79,140,255,0.24);
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.18);
        }

        .metric-card::after {
            content: "";
            position: absolute;
            top: -30px;
            right: -30px;
            width: 110px;
            height: 110px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(79,140,255,0.14), transparent 70%);
        }

        .metric-card.wide {
            min-height: 132px;
        }

        .metric-card.highlight {
            background: linear-gradient(180deg, rgba(79,140,255,0.10), rgba(255,255,255,0.03));
            border-color: rgba(79,140,255,0.18);
        }

        .metric-card.success {
            background: linear-gradient(180deg, rgba(34,197,94,0.09), rgba(255,255,255,0.03));
            border-color: rgba(34,197,94,0.16);
        }

        .metric-card.warning {
            background: linear-gradient(180deg, rgba(245,158,11,0.09), rgba(255,255,255,0.03));
            border-color: rgba(245,158,11,0.16);
        }

        .metric-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .metric-label {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.4;
        }

        .metric-icon {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.06);
            color: #dbe8ff;
            font-size: 13px;
            font-weight: 800;
            flex-shrink: 0;
        }

        .metric-value {
            font-size: 22px;
            font-weight: 800;
            line-height: 1.15;
            color: var(--text);
            word-break: break-word;
            margin-bottom: 6px;
        }

        .metric-sub {
            font-size: 12px;
            color: #8da1c1;
            line-height: 1.55;
        }

        .reason-box {
            margin-top: 16px;
            padding: 14px;
            border-radius: 16px;
            background: rgba(245, 158, 11, 0.08);
            border: 1px solid rgba(245, 158, 11, 0.16);
            color: #ffe0ac;
            line-height: 1.55;
            font-size: 14px;
        }

        .grid-main {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 22px;
            margin: 22px 0;
        }

        .timeline {
            position: relative;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .timeline::before {
            content: "";
            position: absolute;
            left: 8px;
            top: 4px;
            bottom: 4px;
            width: 2px;
            background: linear-gradient(180deg, rgba(79,140,255,0.45), rgba(124,92,255,0.15));
        }

        .timeline li {
            position: relative;
            margin-bottom: 16px;
            padding-left: 34px;
            color: #d7e4fb;
            line-height: 1.6;
            font-size: 15px;
        }

        .timeline li::before {
            content: "";
            position: absolute;
            left: 2px;
            top: 6px;
            width: 14px;
            height: 14px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 0 0 6px rgba(79,140,255,0.12);
        }

        .meta-stack {
            display: grid;
            gap: 12px;
        }

        .meta-box {
            border-radius: 16px;
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.03);
            padding: 16px;
        }

        .meta-box strong {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            color: var(--muted);
            font-weight: 600;
        }

        .meta-box span {
            display: block;
            color: var(--text);
            font-weight: 700;
            line-height: 1.5;
        }

        .slots-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 22px;
            margin-bottom: 22px;
        }

        .available-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(330px, 1fr));
            gap: 16px;
        }

        .available-card {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            padding: 22px;
            border: 1px solid rgba(34, 197, 94, 0.18);
            background: linear-gradient(180deg, rgba(34,197,94,0.08), rgba(255,255,255,0.02));
            box-shadow: var(--shadow-md);
        }

        .available-card::after {
            content: "";
            position: absolute;
            top: -34px;
            right: -24px;
            width: 120px;
            height: 120px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(255,255,255,0.07), transparent 72%);
        }

        .slot-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
        }

        .slot-title {
            margin: 0;
            font-size: 28px;
            line-height: 1;
        }

        .slot-day {
            margin-top: 6px;
            color: var(--muted);
            font-size: 13px;
        }

        .chip {
            padding: 9px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .chip.success {
            background: var(--success-bg);
            color: var(--success-text);
            border: 1px solid rgba(34, 197, 94, 0.18);
        }

        .chip.warning {
            background: var(--warning-bg);
            color: var(--warning-text);
            border: 1px solid rgba(245, 158, 11, 0.18);
        }

        .slot-time {
            display: inline-flex;
            align-items: center;
            padding: 10px 12px;
            border-radius: 14px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .slot-metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .slot-metric {
            padding: 14px;
            border-radius: 16px;
            background: rgba(255,255,255,0.035);
            border: 1px solid rgba(255,255,255,0.06);
        }

        .slot-metric strong {
            display: block;
            font-size: 20px;
            margin-bottom: 4px;
        }

        .slot-metric span {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .rejected-list {
            display: grid;
            gap: 12px;
        }

        .rejected-row {
            display: grid;
            grid-template-columns: 180px 190px 1fr 180px;
            gap: 14px;
            align-items: center;
            padding: 16px 18px;
            border-radius: 18px;
            border: 1px solid rgba(245, 158, 11, 0.14);
            background: linear-gradient(180deg, rgba(245,158,11,0.05), rgba(255,255,255,0.02));
        }

        .rejected-slot strong {
            display: block;
            font-size: 18px;
            margin-bottom: 4px;
        }

        .rejected-slot span,
        .rejected-time,
        .rejected-capacity,
        .rejected-reason {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .rejected-time b,
        .rejected-capacity b,
        .rejected-reason b {
            color: var(--text);
            font-weight: 700;
        }

        .reason-pill {
            display: inline-flex;
            align-items: center;
            padding: 9px 12px;
            border-radius: 999px;
            background: rgba(245, 158, 11, 0.10);
            border: 1px solid rgba(245, 158, 11, 0.18);
            color: #ffd590;
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .empty-card {
            border-radius: 22px;
            border: 1px dashed rgba(255,255,255,0.14);
            background: rgba(255,255,255,0.025);
            padding: 34px 22px;
            text-align: center;
            color: var(--muted);
            line-height: 1.8;
        }

        .json-panel {
            background: linear-gradient(180deg, rgba(7, 13, 28, 0.98), rgba(5, 10, 22, 0.98));
        }

        .json-block {
            margin: 0;
            padding: 22px;
            border-radius: 18px;
            background: #060c1a;
            border: 1px solid rgba(255,255,255,0.08);
            color: #dbe8ff;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.7;
        }

        .footer-note {
            margin-top: 12px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        @media (max-width: 1180px) {
            .metrics-layout {
                grid-template-columns: 1fr;
            }

            .metrics-main,
            .metric-inline-grid {
                grid-template-columns: 1fr 1fr;
            }

            .grid-main {
                grid-template-columns: 1fr;
            }

            .rejected-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 760px) {
            .container {
                padding: 22px 14px 42px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .result-banner {
                flex-direction: column;
                align-items: flex-start;
            }

            .metrics-main,
            .metric-inline-grid,
            .slot-metrics {
                grid-template-columns: 1fr;
            }

            .available-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <div class="orb orb-a"></div>
        <div class="orb orb-b"></div>
        <div class="orb orb-c"></div>
    </div>

    <div class="container">
        <div class="topbar">
            <div class="brand">
                <div class="brand-mark"></div>
                <div class="brand-copy">
                    <h1>Logistics Validation Engine</h1>
                    <p>Resultado da simulação logística por CEP</p>
                </div>
            </div>

            <div class="actions">
                <a href="index.php" class="action-link">← Nova consulta</a>
                <a href="index.php" class="action-link primary">Alterar CEP</a>
            </div>
        </div>

        <div class="result-banner <?= $result['success'] ? 'success' : 'error' ?>">
            <div class="left">
                <h3><?= htmlspecialchars($result['message']) ?></h3>
                <p><?= htmlspecialchars($result['summary_text'] ?? '') ?></p>
            </div>
            <div class="status-pill <?= $result['success'] ? 'success' : 'error' ?>">
                <?= $result['success'] ? 'Entrega disponível' : 'Entrega indisponível' ?>
            </div>
        </div>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Resumo executivo</h3>
                    <p class="panel-subtitle">Visão consolidada do resultado processado pelo service</p>
                </div>
                <div class="section-pill">Overview</div>
            </div>

            <div class="summary-banner">
                <?= htmlspecialchars($result['summary_text'] ?? '') ?>
            </div>

            <div class="metrics-layout">
                <div class="metrics-main">
                    <div class="metric-card highlight wide">
                        <div class="metric-top">
                            <div class="metric-label">CEP consultado</div>
                            <div class="metric-icon">CEP</div>
                        </div>
                        <div class="metric-value"><?= htmlspecialchars($inputCep) ?></div>
                        <div class="metric-sub">Entrada enviada pelo usuário</div>
                    </div>

                    <div class="metric-card wide">
                        <div class="metric-top">
                            <div class="metric-label">CEP normalizado</div>
                            <div class="metric-icon">FMT</div>
                        </div>
                        <div class="metric-value"><?= htmlspecialchars($result['meta']['normalized_cep'] ?? '-') ?></div>
                        <div class="metric-sub">Formato interno de processamento</div>
                    </div>

                    <div class="metric-card highlight">
                        <div class="metric-top">
                            <div class="metric-label">Região encontrada</div>
                            <div class="metric-icon">REG</div>
                        </div>
                        <div class="metric-value"><?= htmlspecialchars($result['data']['region_name'] ?? '-') ?></div>
                        <div class="metric-sub">Faixa operacional correspondente</div>
                    </div>

                    <div class="metric-card highlight">
                        <div class="metric-top">
                            <div class="metric-label">Frete</div>
                            <div class="metric-icon">R$</div>
                        </div>
                        <div class="metric-value"><?= htmlspecialchars($result['data']['freight_label'] ?? '-') ?></div>
                        <div class="metric-sub">Valor definido pelas regras regionais</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-top">
                            <div class="metric-label">Status da região</div>
                            <div class="metric-icon">OPS</div>
                        </div>
                        <div class="metric-value"><?= htmlspecialchars($result['data']['region_status'] ?? '-') ?></div>
                        <div class="metric-sub">Situação operacional da malha</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-top">
                            <div class="metric-label">Perfil logístico</div>
                            <div class="metric-icon">LOG</div>
                        </div>
                        <div class="metric-value"><?= htmlspecialchars($result['data']['delivery_profile'] ?? '-') ?></div>
                        <div class="metric-sub">Tipo de atendimento configurado</div>
                    </div>
                </div>

                <div class="metrics-side">
                    <div class="metric-inline-grid">
                        <div class="metric-card success">
                            <div class="metric-top">
                                <div class="metric-label">Slots válidos</div>
                                <div class="metric-icon">OK</div>
                            </div>
                            <div class="metric-value"><?= count($result['data']['available_slots'] ?? []) ?></div>
                            <div class="metric-sub">Janelas elegíveis para exibição</div>
                        </div>

                        <div class="metric-card warning">
                            <div class="metric-top">
                                <div class="metric-label">Slots rejeitados</div>
                                <div class="metric-icon">NO</div>
                            </div>
                            <div class="metric-value"><?= count($result['data']['unavailable_slots'] ?? []) ?></div>
                            <div class="metric-sub">Slots descartados pelas regras</div>
                        </div>
                    </div>

                    <div class="metric-stack">
                        <div class="metric-card">
                            <div class="metric-top">
                                <div class="metric-label">Lead time</div>
                                <div class="metric-icon">LT</div>
                            </div>
                            <div class="metric-value"><?= htmlspecialchars((string) ($result['meta']['lead_time_hours'] ?? $leadTimeHours)) ?>h</div>
                            <div class="metric-sub">Antecedência mínima exigida</div>
                        </div>

                        <div class="metric-card">
                            <div class="metric-top">
                                <div class="metric-label">Última validação</div>
                                <div class="metric-icon">TS</div>
                            </div>
                            <div class="metric-value" style="font-size: 20px;">
                                <?= htmlspecialchars(isset($result['meta']['processed_at']) ? formatDisplayDate($result['meta']['processed_at']) : '-') ?>
                            </div>
                            <div class="metric-sub">Data e hora da execução do service</div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($result['data']['blocked_reason'])): ?>
                <div class="reason-box">
                    <strong>Motivo do bloqueio:</strong>
                    <?= htmlspecialchars($result['data']['blocked_reason']) ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="grid-main">
            <div class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Fluxo executado</h3>
                        <p class="panel-subtitle">Trilha lógica percorrida até a resposta final</p>
                    </div>
                    <div class="section-pill">Flow</div>
                </div>

                <ul class="timeline">
                    <?php foreach (($result['flow'] ?? []) as $step): ?>
                        <li><?= htmlspecialchars($step) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Metadados técnicos</h3>
                        <p class="panel-subtitle">Informações auxiliares para análise e debug</p>
                    </div>
                    <div class="section-pill">Meta</div>
                </div>

                <div class="meta-stack">
                    <div class="meta-box">
                        <strong>Processado em</strong>
                        <span><?= htmlspecialchars(isset($result['meta']['processed_at']) ? formatDisplayDate($result['meta']['processed_at']) : '-') ?></span>
                    </div>

                    <div class="meta-box">
                        <strong>Slots verificados</strong>
                        <span><?= htmlspecialchars((string) ($result['meta']['checked_slots'] ?? 0)) ?></span>
                    </div>

                    <div class="meta-box">
                        <strong>Válidos encontrados</strong>
                        <span><?= htmlspecialchars((string) ($result['meta']['valid_slots_found'] ?? count($result['data']['available_slots'] ?? []))) ?></span>
                    </div>

                    <div class="meta-box">
                        <strong>Inválidos encontrados</strong>
                        <span><?= htmlspecialchars((string) ($result['meta']['invalid_slots_found'] ?? count($result['data']['unavailable_slots'] ?? []))) ?></span>
                    </div>
                </div>
            </div>
        </section>

        <section class="slots-layout">
            <div class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Slots disponíveis</h3>
                        <p class="panel-subtitle">Janelas aprovadas pelo motor de validação logística</p>
                    </div>
                    <div class="section-pill">Available</div>
                </div>

                <?php if (!empty($result['data']['available_slots'])): ?>
                    <div class="available-grid">
                        <?php foreach ($result['data']['available_slots'] as $slot): ?>
                            <article class="available-card">
                                <div class="slot-head">
                                    <div>
                                        <h4 class="slot-title"><?= htmlspecialchars($slot['id']) ?></h4>
                                        <div class="slot-day">
                                            <?= htmlspecialchars($slot['weekday_label']) ?> • <?= htmlspecialchars($slot['date_label']) ?>
                                        </div>
                                    </div>
                                    <div class="chip success">Disponível</div>
                                </div>

                                <div class="slot-time">
                                    <?= htmlspecialchars($slot['start_time']) ?> às <?= htmlspecialchars($slot['end_time']) ?>
                                </div>

                                <div class="slot-metrics">
                                    <div class="slot-metric">
                                        <strong><?= htmlspecialchars((string) $slot['capacity_max']) ?></strong>
                                        <span>Capacidade máxima</span>
                                    </div>
                                    <div class="slot-metric">
                                        <strong><?= htmlspecialchars((string) $slot['current_bookings']) ?></strong>
                                        <span>Reservas atuais</span>
                                    </div>
                                    <div class="slot-metric">
                                        <strong><?= htmlspecialchars((string) $slot['available_capacity']) ?></strong>
                                        <span>Capacidade restante</span>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-card">
                        Nenhum slot elegível foi encontrado para este CEP no cenário atual do mock.
                    </div>
                <?php endif; ?>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Slots rejeitados</h3>
                        <p class="panel-subtitle">Ordenados por dia da semana para facilitar leitura operacional</p>
                    </div>
                    <div class="section-pill">Rejected</div>
                </div>

                <?php if (!empty($result['data']['unavailable_slots'])): ?>
                    <div class="rejected-list">
                        <?php foreach ($result['data']['unavailable_slots'] as $slot): ?>
                            <article class="rejected-row">
                                <div class="rejected-slot">
                                    <strong><?= htmlspecialchars($slot['id']) ?></strong>
                                    <span><?= htmlspecialchars($slot['weekday_label']) ?> • <?= htmlspecialchars($slot['date_label']) ?></span>
                                </div>

                                <div class="rejected-time">
                                    <div class="reason-pill">Rejeitado</div><br>
                                    <b><?= htmlspecialchars($slot['start_time']) ?> às <?= htmlspecialchars($slot['end_time']) ?></b>
                                </div>

                                <div class="rejected-reason">
                                    <b>Motivo:</b> <?= htmlspecialchars($slot['reason'] ?? 'Não informado') ?>
                                </div>

                                <div class="rejected-capacity">
                                    <b>Capacidade:</b> <?= htmlspecialchars((string) $slot['capacity_max']) ?><br>
                                    <b>Reservas:</b> <?= htmlspecialchars((string) $slot['current_bookings']) ?><br>
                                    <b>Restante:</b> <?= htmlspecialchars((string) $slot['available_capacity']) ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-card">
                        Nenhum slot foi rejeitado para este cenário.
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($showJson): ?>
            <section class="panel json-panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Payload técnico em JSON</h3>
                        <p class="panel-subtitle">Útil para debug, documentação do retorno e futura integração de front-end</p>
                    </div>
                    <div class="section-pill">JSON</div>
                </div>

                <pre class="json-block"><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                <div class="footer-note">
                    Esse bloco é útil num projeto de portfólio porque mostra exatamente o payload retornado pelo motor de validação. Em produção, isso normalmente ficaria restrito à API ou ao ambiente de inspeção.
                </div>
            </section>
        <?php endif; ?>
    </div>
</body>
</html>