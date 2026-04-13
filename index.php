<?php declare(strict_types=1);
require_once __DIR__ . '/service.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service de Validação Logística</title>
    <style>
        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        :root {
            --bg: #050816;
            --bg-2: #071024;
            --panel: rgba(11, 19, 38, 0.86);
            --panel-strong: rgba(10, 18, 36, 0.96);
            --panel-soft: rgba(255,255,255,0.03);
            --line: rgba(255,255,255,0.08);
            --line-strong: rgba(255,255,255,0.14);
            --text: #eef4ff;
            --muted: #9fb0cc;
            --muted-2: #c7d4ec;
            --primary: #4f8cff;
            --primary-2: #7c5cff;
            --accent: #20c997;
            --accent-2: #16c2ff;
            --success: #22c55e;
            --warning: #ffb84d;
            --danger: #ef4444;
            --shadow-lg: 0 28px 80px rgba(0, 0, 0, 0.42);
            --shadow-md: 0 18px 40px rgba(0, 0, 0, 0.24);
            --radius-xl: 28px;
            --radius-lg: 22px;
            --radius-md: 18px;
            --radius-sm: 14px;
            --max-width: 1380px;
        }

        body {
            margin: 0;
            font-family: Inter, Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 0% 0%, rgba(79,140,255,0.18), transparent 24%),
                radial-gradient(circle at 100% 0%, rgba(124,92,255,0.18), transparent 24%),
                radial-gradient(circle at 50% 72%, rgba(32,201,151,0.10), transparent 18%),
                linear-gradient(180deg, var(--bg) 0%, var(--bg-2) 48%, var(--bg) 100%);
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
            filter: blur(80px);
            opacity: 0.35;
        }

        .orb-a {
            width: 280px;
            height: 280px;
            top: 20px;
            left: -80px;
            background: #2f6fed;
        }

        .orb-b {
            width: 340px;
            height: 340px;
            top: 10px;
            right: -110px;
            background: #7c5cff;
        }

        .orb-c {
            width: 300px;
            height: 300px;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #20c997;
            opacity: 0.14;
        }

        .container {
            width: 100%;
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 30px 22px 60px;
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
            width: 54px;
            height: 54px;
            border-radius: 17px;
            background: linear-gradient(135deg, rgba(79,140,255,0.96), rgba(124,92,255,0.96));
            box-shadow: 0 16px 30px rgba(79,140,255,0.28);
            position: relative;
        }

        .brand-mark::before,
        .brand-mark::after {
            content: "";
            position: absolute;
            background: rgba(255,255,255,0.96);
            border-radius: 999px;
        }

        .brand-mark::before {
            width: 24px;
            height: 6px;
            left: 15px;
            top: 16px;
        }

        .brand-mark::after {
            width: 18px;
            height: 6px;
            left: 15px;
            top: 29px;
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

        .top-pill {
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--line);
            color: var(--muted-2);
            font-size: 13px;
            white-space: nowrap;
        }

        .hero {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 22px;
            align-items: stretch;
            margin-bottom: 22px;
        }

        .hero-panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(14px);
            position: relative;
            overflow: hidden;
            transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease;
        }

        .hero-panel::after {
            content: "";
            position: absolute;
            right: -120px;
            bottom: -120px;
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, rgba(124,92,255,0.16) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-left {
            padding: 30px;
        }

        .hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #cfd9ec;
            padding: 9px 12px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: rgba(255,255,255,0.03);
            margin-bottom: 18px;
        }

        .hero-title {
            margin: 0 0 14px;
            font-size: 58px;
            line-height: 0.98;
            letter-spacing: -1.5px;
            max-width: 760px;
        }

        .hero-subtitle {
            margin: 0;
            max-width: 820px;
            font-size: 19px;
            line-height: 1.8;
            color: var(--muted);
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .hero-btn,
        .hero-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 50px;
            padding: 0 18px;
            border-radius: 16px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 800;
            transition: transform 0.2s ease, filter 0.2s ease, border-color 0.2s ease;
            cursor: pointer;
        }

        .hero-btn {
            background: linear-gradient(135deg, var(--primary), #3b82f6 55%, var(--primary-2));
            color: white;
            box-shadow: 0 18px 34px rgba(59, 130, 246, 0.28);
        }

        .hero-btn:hover,
        .hero-link:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
        }

        .hero-link {
            background: rgba(255,255,255,0.04);
            color: #dbe8ff;
            border: 1px solid var(--line);
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 26px;
        }

        .hero-stat {
            padding: 18px;
            border-radius: var(--radius-md);
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--line);
            min-height: 120px;
        }

        .hero-stat strong {
            display: block;
            font-size: 28px;
            margin-bottom: 6px;
            line-height: 1.1;
        }

        .hero-stat span {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .hero-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
        }

        .badge {
            padding: 11px 15px;
            border-radius: 999px;
            background: rgba(255,255,255,0.035);
            border: 1px solid var(--line);
            color: #d9e4f7;
            font-size: 13px;
            font-weight: 600;
        }

        .hero-right {
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .mini-card {
            border: 1px solid var(--line);
            background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.025));
            border-radius: 20px;
            padding: 18px;
        }

        .mini-card h3 {
            margin: 0 0 10px;
            font-size: 18px;
        }

        .mini-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.7;
            font-size: 14px;
        }

        .engine-box {
            position: relative;
            overflow: hidden;
            padding: 20px;
            border-radius: 24px;
            border: 1px solid var(--line-strong);
            background:
                linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02)),
                radial-gradient(circle at top right, rgba(79,140,255,0.15), transparent 30%);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
            transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease;
        }

        .engine-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .engine-top strong {
            font-size: 15px;
        }

        .engine-pill {
            padding: 8px 11px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            color: #dbe8ff;
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.04);
        }

        .engine-flow {
            display: grid;
            gap: 10px;
            margin-bottom: 16px;
        }

        .engine-step {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.05);
        }

        .engine-icon {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 13px;
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            flex-shrink: 0;
        }

        .engine-copy strong {
            display: block;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .engine-copy span {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.5;
        }

        .search-box {
            margin-top: auto;
            padding: 18px;
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.02));
            border: 1px solid var(--line);
            transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease;
        }

        .search-box-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 16px;
        }

        .search-box-title h3 {
            margin: 0;
            font-size: 19px;
        }

        .search-box-title span {
            color: var(--muted);
            font-size: 13px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: end;
        }

        .field label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            color: #dce8ff;
            font-weight: 700;
        }

        .field input {
            width: 100%;
            height: 58px;
            border-radius: 16px;
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.06);
            color: var(--text);
            padding: 0 16px;
            font-size: 15px;
            outline: none;
            transition: 0.2s ease;
        }

        .field input::placeholder {
            color: #7f92b3;
        }

        .field input:focus {
            border-color: rgba(79,140,255,0.8);
            box-shadow: 0 0 0 5px rgba(79,140,255,0.12);
            background: rgba(255,255,255,0.08);
        }

        .btn {
            height: 58px;
            padding: 0 24px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), #3b82f6 55%, var(--primary-2));
            color: white;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            min-width: 180px;
            box-shadow: 0 18px 34px rgba(59, 130, 246, 0.28);
            transition: transform 0.2s ease, filter 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
        }

        .test-list {
            margin-top: 16px;
            color: var(--muted);
            line-height: 1.8;
            font-size: 14px;
        }

        .test-list strong {
            color: #dbe8ff;
        }

        .legend-strip {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 22px;
        }

        .legend-item {
            padding: 16px;
            border-radius: 18px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--line);
        }

        .legend-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            flex-shrink: 0;
        }

        .legend-dot.green { background: var(--success); }
        .legend-dot.yellow { background: var(--warning); }
        .legend-dot.red { background: var(--danger); }

        .legend-item strong {
            font-size: 15px;
        }

        .legend-item span {
            display: block;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.65;
        }

        .section {
            margin-top: 22px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .feature-card,
        .table-card,
        .flow-preview {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 22px;
            box-shadow: var(--shadow-md);
        }

        .feature-top {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .feature-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 12px 22px rgba(79,140,255,0.22);
        }

        .feature-card h3 {
            margin: 0;
            font-size: 19px;
        }

        .feature-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.75;
            font-size: 14px;
        }

        .section-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }

        .section-head h2 {
            margin: 0;
            font-size: 28px;
            letter-spacing: -0.4px;
        }

        .section-head p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.7;
            max-width: 700px;
        }

        .section-tag {
            padding: 10px 13px;
            border-radius: 999px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--line);
            color: var(--muted-2);
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .regions-table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
        }

        .regions-table th,
        .regions-table td {
            text-align: left;
            padding: 14px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            font-size: 14px;
        }

        .regions-table th {
            color: var(--muted-2);
            font-weight: 700;
            font-size: 13px;
        }

        .regions-table td {
            color: var(--text);
        }

        .table-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.04);
        }

        .table-badge.active {
            color: #95f5b6;
            background: rgba(34, 197, 94, 0.12);
            border-color: rgba(34, 197, 94, 0.16);
        }

        .table-badge.paused {
            color: #ffd590;
            background: rgba(245, 158, 11, 0.12);
            border-color: rgba(245, 158, 11, 0.16);
        }

        .flow-line {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .flow-node {
            position: relative;
            padding: 18px;
            border-radius: 18px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--line);
        }

        .flow-node strong {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .flow-node span {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.65;
        }

        .bottom-note {
            margin-top: 22px;
            padding: 26px 24px;
            border-radius: 24px;
            border: 1px dashed rgba(255,255,255,0.14);
            background:
                linear-gradient(90deg, rgba(79,140,255,0.06), rgba(124,92,255,0.04), rgba(32,201,151,0.04));
            color: var(--muted-2);
            line-height: 1.9;
            text-align: center;
            font-size: 15px;
        }

        .highlight-focus {
            animation: pulseHighlight 1.2s ease;
        }

        @keyframes pulseHighlight {
            0% {
                box-shadow: 0 0 0 0 rgba(79, 140, 255, 0.00);
                border-color: rgba(255,255,255,0.08);
                transform: translateY(0);
            }
            30% {
                box-shadow: 0 0 0 8px rgba(79, 140, 255, 0.10);
                border-color: rgba(79,140,255,0.75);
                transform: translateY(-2px);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(79, 140, 255, 0.00);
                border-color: rgba(255,255,255,0.08);
                transform: translateY(0);
            }
        }

        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(5, 8, 22, 0.82);
            backdrop-filter: blur(10px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-card {
            width: 100%;
            max-width: 520px;
            background: rgba(11, 19, 38, 0.96);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            padding: 28px;
        }

        .loading-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 22px;
        }

        .loading-spinner {
            width: 48px;
            height: 48px;
            border-radius: 999px;
            border: 4px solid rgba(255,255,255,0.10);
            border-top-color: var(--primary);
            animation: spin 0.9s linear infinite;
            flex-shrink: 0;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-header h3 {
            margin: 0 0 4px;
            font-size: 22px;
        }

        .loading-header p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
            font-size: 14px;
        }

        .loading-steps {
            display: grid;
            gap: 10px;
        }

        .loading-step {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 14px;
            border-radius: 14px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            color: var(--muted);
            transition: all 0.2s ease;
        }

        .loading-step.active {
            border-color: rgba(79,140,255,0.28);
            background: rgba(79,140,255,0.08);
            color: var(--text);
        }

        .loading-index {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.06);
            font-size: 12px;
            font-weight: 800;
            color: white;
            flex-shrink: 0;
        }

        @media (max-width: 1180px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .hero-title {
                font-size: 46px;
            }

            .grid-3,
            .flow-line,
            .legend-strip {
                grid-template-columns: 1fr 1fr;
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

            .hero-title {
                font-size: 36px;
            }

            .hero-subtitle {
                font-size: 16px;
            }

            .hero-stats,
            .grid-3,
            .flow-line,
            .legend-strip {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .btn {
                width: 100%;
            }

            .section-head {
                flex-direction: column;
                align-items: flex-start;
            }

            .regions-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
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

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-card">
            <div class="loading-header">
                <div class="loading-spinner"></div>
                <div>
                    <h3>Processando consulta logística</h3>
                    <p>Simulando o fluxo do motor de validação antes de abrir o resultado.</p>
                </div>
            </div>

            <div class="loading-steps" id="loadingSteps">
                <div class="loading-step">
                    <div class="loading-index">01</div>
                    <span>Normalizando CEP informado</span>
                </div>
                <div class="loading-step">
                    <div class="loading-index">02</div>
                    <span>Identificando região de atendimento</span>
                </div>
                <div class="loading-step">
                    <div class="loading-index">03</div>
                    <span>Aplicando regras operacionais</span>
                </div>
                <div class="loading-step">
                    <div class="loading-index">04</div>
                    <span>Montando resposta final</span>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="topbar">
            <div class="brand">
                <div class="brand-mark"></div>
                <div class="brand-copy">
                    <h1>Logistics Validation Engine</h1>
                    <p>Simulador visual de elegibilidade logística e janelas de entrega</p>
                </div>
            </div>
            <div class="top-pill">Mock operacional • PHP puro • Portfólio demo</div>
        </div>

        <section class="hero">
            <div class="hero-panel hero-left">
                <div class="hero-kicker">Service design • Delivery intelligence</div>
                <h2 class="hero-title">Regra logística com cara de produto premium</h2>
                <p class="hero-subtitle">
                    Uma interface pensada para apresentar a lógica de validação de entrega de forma profissional:
                    CEP, região, bloqueios, lead time e capacidade operacional convertidos em uma experiência
                    visual, explicável e pronta para evoluir para service real.
                </p>

                <div class="hero-actions">
                    <a href="#consultar" class="hero-btn" id="goToSearch">Simular entrega agora</a>
                    <a href="#visao-geral" class="hero-link" id="goToArchitecture">Ver arquitetura visual</a>
                </div>

                <div class="hero-stats">
                    <div class="hero-stat">
                        <strong>CEP → Região</strong>
                        <span>Identificação imediata da faixa operacional e do valor de frete aplicável.</span>
                    </div>
                    <div class="hero-stat">
                        <strong>Validação real</strong>
                        <span>Lead time, capacidade e disponibilidade avaliados com base em regras do mock.</span>
                    </div>
                    <div class="hero-stat">
                        <strong>Pronto pra API</strong>
                        <span>Estrutura fácil de evoluir para integração, endpoint ou camada de front real.</span>
                    </div>
                </div>

                <div class="hero-badges">
                    <span class="badge">Validação por CEP</span>
                    <span class="badge">Bloqueios regionais</span>
                    <span class="badge">Lead time</span>
                    <span class="badge">Capacidade de slots</span>
                    <span class="badge">Fluxo auditável</span>
                    <span class="badge">Payload técnico</span>
                </div>

                <div class="legend-strip">
                    <div class="legend-item">
                        <div class="legend-head">
                            <div class="legend-dot green"></div>
                            <strong>Disponível</strong>
                        </div>
                        <span>CEP atendido com região liberada e janelas elegíveis para entrega.</span>
                    </div>

                    <div class="legend-item">
                        <div class="legend-head">
                            <div class="legend-dot yellow"></div>
                            <strong>Sem janela elegível</strong>
                        </div>
                        <span>CEP atendido, porém as regras operacionais descartaram os slots.</span>
                    </div>

                    <div class="legend-item">
                        <div class="legend-head">
                            <div class="legend-dot red"></div>
                            <strong>Bloqueado ou fora da malha</strong>
                        </div>
                        <span>Região indisponível ou CEP fora da cobertura configurada no mock.</span>
                    </div>
                </div>
            </div>

            <div class="hero-panel hero-right">
                <div class="mini-card">
                    <h3>Visão do motor de decisão</h3>
                    <p>
                        O sistema recebe o CEP, identifica a região, valida bloqueios, aplica regras de agenda
                        e retorna apenas as janelas realmente elegíveis para entrega.
                    </p>
                </div>

                <div class="engine-box" id="visao-geral">
                    <div class="engine-top">
                        <strong>Motor de validação</strong>
                        <span class="engine-pill">Pipeline lógico</span>
                    </div>

                    <div class="engine-flow">
                        <div class="engine-step">
                            <div class="engine-icon">01</div>
                            <div class="engine-copy">
                                <strong>Entrada do CEP</strong>
                                <span>Normalização e verificação do formato antes do processamento.</span>
                            </div>
                        </div>

                        <div class="engine-step">
                            <div class="engine-icon">02</div>
                            <div class="engine-copy">
                                <strong>Mapa regional</strong>
                                <span>Associação da faixa de CEP à região e ao frete correspondente.</span>
                            </div>
                        </div>

                        <div class="engine-step">
                            <div class="engine-icon">03</div>
                            <div class="engine-copy">
                                <strong>Regras operacionais</strong>
                                <span>Validação de bloqueios, lead time, dia permitido e capacidade.</span>
                            </div>
                        </div>

                        <div class="engine-step">
                            <div class="engine-icon">04</div>
                            <div class="engine-copy">
                                <strong>Resposta final</strong>
                                <span>Entrega disponível ou indisponível com slots auditáveis.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="search-box" id="consultar">
                    <div class="search-box-title">
                        <h3>Consultar entrega</h3>
                        <span>Simulação operacional</span>
                    </div>

                    <form method="get" action="resultado.php" id="deliveryForm">
                        <div class="form-grid">
                            <div class="field">
                                <label for="cep">CEP</label>
                                <input
                                    type="text"
                                    id="cep"
                                    name="cep"
                                    placeholder="00000-000"
                                    maxlength="9"
                                    inputmode="numeric"
                                    autocomplete="postal-code"
                                    required
                                >
                            </div>
                            <button type="submit" class="btn">Validar entrega</button>
                        </div>
                    </form>

                    <div class="test-list">
                        Testes rápidos:
                        <strong>01001-000</strong>,
                        <strong>01005-000</strong>,
                        <strong>02001-000</strong>,
                        <strong>03001-000</strong>,
                        <strong>99999-999</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-head">
                <div>
                    <h2>Malha simulada de atendimento</h2>
                    <p>
                        Uma visão rápida das regiões cadastradas no mock, incluindo faixa de CEP, perfil operacional
                        e status atual de atendimento.
                    </p>
                </div>
                <div class="section-tag">Coverage</div>
            </div>

            <div class="table-card">
                <table class="regions-table">
                    <thead>
                        <tr>
                            <th>Região</th>
                            <th>Faixa de CEP</th>
                            <th>Frete</th>
                            <th>Perfil</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cepRanges as $range): ?>
                            <tr>
                                <td><?= htmlspecialchars($range['region_name']) ?></td>
                                <td><?= htmlspecialchars(substr($range['cep_start'], 0, 5) . '-' . substr($range['cep_start'], 5)) ?> até <?= htmlspecialchars(substr($range['cep_end'], 0, 5) . '-' . substr($range['cep_end'], 5)) ?></td>
                                <td><?= htmlspecialchars(formatCurrency((float) $range['freight_value'])) ?></td>
                                <td><?= htmlspecialchars($range['delivery_profile'] ?? '-') ?></td>
                                <td>
                                    <span class="table-badge <?= ($range['status'] ?? 'active') === 'active' ? 'active' : 'paused' ?>">
                                        <?= htmlspecialchars(getRegionStatusLabel((string) ($range['status'] ?? 'active'))) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="section">
            <div class="section-head">
                <div>
                    <h2>O que este projeto demonstra</h2>
                    <p>
                        Mais do que uma tela bonita, essa demo mostra como transformar regra de negócio logística
                        em uma estrutura visual clara, elegante e apresentável para portfólio técnico.
                    </p>
                </div>
                <div class="section-tag">Highlights</div>
            </div>

            <div class="grid-3">
                <div class="feature-card">
                    <div class="feature-top">
                        <div class="feature-icon">A</div>
                        <h3>Camada de decisão</h3>
                    </div>
                    <p>
                        Separa regiões por faixa de CEP, identifica frete, trata bloqueios operacionais
                        e organiza o fluxo principal do serviço.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-top">
                        <div class="feature-icon">B</div>
                        <h3>Janelas elegíveis</h3>
                    </div>
                    <p>
                        Filtra slots com base em dias permitidos, antecedência mínima, capacidade máxima
                        e status ativo da janela.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-top">
                        <div class="feature-icon">C</div>
                        <h3>Saída auditável</h3>
                    </div>
                    <p>
                        A tela de resultado expõe indicadores, trilha lógica, slots aprovados, slots rejeitados
                        e payload técnico em JSON.
                    </p>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-head">
                <div>
                    <h2>Fluxo resumido da solução</h2>
                    <p>
                        Um preview visual do pipeline que a segunda tela detalha por completo após a consulta.
                    </p>
                </div>
                <div class="section-tag">Preview</div>
            </div>

            <div class="flow-preview">
                <div class="flow-line">
                    <div class="flow-node">
                        <strong>1. Receber CEP</strong>
                        <span>Entrada do usuário com máscara, normalização e validação do formato.</span>
                    </div>

                    <div class="flow-node">
                        <strong>2. Identificar região</strong>
                        <span>Busca da faixa correspondente e leitura das regras logísticas aplicáveis.</span>
                    </div>

                    <div class="flow-node">
                        <strong>3. Filtrar slots</strong>
                        <span>Aplicação de bloqueios, lead time e capacidade operacional.</span>
                    </div>

                    <div class="flow-node">
                        <strong>4. Retornar resultado</strong>
                        <span>Exibição da resposta final com visão executiva e técnica.</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="bottom-note">
            Faça uma consulta para abrir a segunda etapa da experiência e visualizar o resultado completo do service,
            com resumo executivo, fluxo executado, slots disponíveis, slots rejeitados e retorno técnico estruturado.
        </div>
    </div>

    <script>
        const cepInput = document.getElementById('cep');
        const goToSearch = document.getElementById('goToSearch');
        const goToArchitecture = document.getElementById('goToArchitecture');
        const searchBox = document.getElementById('consultar');
        const architectureBox = document.getElementById('visao-geral');
        const deliveryForm = document.getElementById('deliveryForm');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const loadingSteps = document.querySelectorAll('.loading-step');

        if (cepInput) {
            cepInput.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                value = value.slice(0, 8);

                if (value.length > 5) {
                    value = value.slice(0, 5) + '-' + value.slice(5);
                }

                e.target.value = value;
            });
        }

        function highlightElement(element) {
            if (!element) return;
            element.classList.remove('highlight-focus');
            void element.offsetWidth;
            element.classList.add('highlight-focus');
        }

        if (goToSearch && searchBox && cepInput) {
            goToSearch.addEventListener('click', function (e) {
                e.preventDefault();
                searchBox.scrollIntoView({ behavior: 'smooth', block: 'center' });

                setTimeout(() => {
                    highlightElement(searchBox);
                    cepInput.focus();
                }, 350);
            });
        }

        if (goToArchitecture && architectureBox) {
            goToArchitecture.addEventListener('click', function (e) {
                e.preventDefault();
                architectureBox.scrollIntoView({ behavior: 'smooth', block: 'center' });

                setTimeout(() => {
                    highlightElement(architectureBox);
                }, 350);
            });
        }

        if (deliveryForm && loadingOverlay) {
            deliveryForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const rawCep = cepInput.value.replace(/\D/g, '');
                if (rawCep.length !== 8) {
                    cepInput.focus();
                    highlightElement(searchBox);
                    return;
                }

                loadingOverlay.classList.add('active');

                loadingSteps.forEach(step => step.classList.remove('active'));

                const timings = [150, 650, 1150, 1650];
                loadingSteps.forEach((step, index) => {
                    setTimeout(() => {
                        step.classList.add('active');
                    }, timings[index]);
                });

                setTimeout(() => {
                    deliveryForm.submit();
                }, 2200);
            });
        }
    </script>
</body>
</html>