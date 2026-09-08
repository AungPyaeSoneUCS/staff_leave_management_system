<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', session('locale', app()->getLocale())) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('department_head.leave_report') }} — {{ config('app.name') }}</title>
    <style>
        body {
            font-family: "notosansmyanmar", serif;
            font-size: 10px;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        .page {
            padding: 20mm 15mm;
        }

        .header {
            border-bottom: 3px solid #0f766e;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .institution {
            font-size: 14px;
            font-weight: bold;
            color: #0f766e;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #0f766e;
            margin: 10px 0 8px;
        }

        .report-meta {
            font-size: 9px;
            color: #64748b;
            margin-bottom: 12px;
        }

        .report-meta span {
            display: inline-block;
            margin-right: 12px;
        }

        .filters {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 12px;
            font-size: 9px;
        }

        .filters span {
            display: inline-block;
            margin-right: 10px;
        }

        .filters strong {
            color: #334159;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        th {
            background: #0f766e;
            color: #fff;
            padding: 5px 6px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            word-break: nowrap;
        }

        td {
            padding: 4px 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .status-badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            color: #fff;
        }

        .status-pending { background: #eab308; }
        .status-approved { background: #22c55e; }
        .status-rejected { background: #ef4444; }
        .status-cancelled { background: #94a3b8; }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #64748b;
            font-size: 10px;
        }

        .footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            margin-top: 10px;
            font-size: 7px;
            color: #94a3b8;
            text-align: center;
        }

        .summary {
            margin-top: 8px;
            font-size: 9px;
            color: #475569;
        }

        .chart-section {
            margin-bottom: 18px;
            page-break-inside: avoid;
        }

        .chart-title {
            font-size: 11px;
            font-weight: bold;
            color: #0f766e;
            margin-bottom: 8px;
        }

        .chart-wrap {
            text-align: center;
            margin-bottom: 8px;
        }

        .chart-legend {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .chart-legend td {
            border: none;
            padding: 2px 4px;
            font-size: 8px;
            color: #475569;
            background: transparent;
        }

        .legend-swatch {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 2px;
            margin-right: 4px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="header-top">
                <span class="institution">{{ config('app.name') }}</span>
            </div>
        </div>

        <div class="report-title">{{ __('department_head.leave_report') }}</div>

        <div class="report-meta">
            <span>{{ __('admin.generated') }}: {{\App\Support\MyanmarDateFormatter::format(now(), 'Y-m-d H:i')}}</span>
        </div>

        @if (! empty($filters['start_date']) || ! empty($filters['end_date']))
            <div class="filters">
                @if (! empty($filters['start_date']))
                    <span><strong>{{ __('common.start_date') }}:</strong> {{ $filters['start_date'] }}</span>
                @endif
                @if (! empty($filters['end_date']))
                    <span><strong>{{ __('common.end_date') }}:</strong> {{ $filters['end_date'] }}</span>
                @endif
            </div>
        @endif

        @if (! empty($data))
            @if (! empty($chart) && ! empty($chart['labels']))
                @php
                    $labels = $chart['labels'];
                    $values = array_map('floatval', $chart['values']);
                    $colors = array_fill(0, count($labels), '#3b82f6');
                    $maxVal = max($values) ?: 1;
                    $count = count($labels);

                    $svgW = 540;
                    $svgH = 240;
                    $padL = 42;
                    $padR = 16;
                    $padT = 24;
                    $padB = 58;
                    $plotW = $svgW - $padL - $padR;
                    $plotH = $svgH - $padT - $padB;
                    $slot = $plotW / max($count, 1);
                    $barW = min(42, max(14, $slot * 0.55));
                @endphp
                <div class="chart-section">
                    <div class="chart-title">{{ __('department_head.leave_report') }}</div>
                    <div class="chart-wrap">
                        <svg width="{{ $svgW }}" height="{{ $svgH }}" viewBox="0 0 {{ $svgW }} {{ $svgH }}" xmlns="http://www.w3.org/2000/svg">
                            @for ($i = 0; $i <= 4; $i++)
                                @php
                                    $y = $padT + ($plotH * $i / 4);
                                    $tick = round($maxVal * (1 - $i / 4), 1);
                                @endphp
                                <line x1="{{ $padL }}" y1="{{ $y }}" x2="{{ $svgW - $padR }}" y2="{{ $y }}" stroke="#e2e8f0" stroke-width="1"/>
                                <text x="{{ $padL - 6 }}" y="{{ $y + 3 }}" text-anchor="end" font-size="8" fill="#64748b" font-family="notosansmyanmar">{{ $tick }}</text>
                            @endfor

                            <line x1="{{ $padL }}" y1="{{ $padT }}" x2="{{ $padL }}" y2="{{ $padT + $plotH }}" stroke="#94a3b8" stroke-width="1"/>
                            <line x1="{{ $padL }}" y1="{{ $padT + $plotH }}" x2="{{ $svgW - $padR }}" y2="{{ $padT + $plotH }}" stroke="#94a3b8" stroke-width="1"/>

                            @foreach ($labels as $idx => $label)
                                @php
                                    $value = (float) $values[$idx];
                                    $barH = ($value / $maxVal) * $plotH;
                                    $x = $padL + ($idx * $slot) + (($slot - $barW) / 2);
                                    $y = $padT + $plotH - $barH;
                                    $color = $colors[$idx] ?? '#3b82f6';
                                    $shortLabel = mb_strlen($label) > 12 ? mb_substr($label, 0, 11).'…' : $label;
                                @endphp
                                <rect x="{{ round($x, 2) }}" y="{{ round($y, 2) }}" width="{{ round($barW, 2) }}" height="{{ round(max($barH, 0), 2) }}" fill="{{ $color }}" rx="2"/>
                                <text x="{{ round($x + $barW / 2, 2) }}" y="{{ round($y - 4, 2) }}" text-anchor="middle" font-size="8" fill="#334155" font-weight="bold" font-family="notosansmyanmar">{{ $value }}</text>
                                <text x="{{ round($x + $barW / 2, 2) }}" y="{{ $padT + $plotH + 14 }}" text-anchor="middle" font-size="7" fill="#475569" font-family="notosansmyanmar">{{ $shortLabel }}</text>
                            @endforeach
                        </svg>
                    </div>
                    <table class="chart-legend">
                        <tr>
                            @foreach ($labels as $idx => $label)
                                <td>
                                    <span class="legend-swatch" style="background: {{ $colors[$idx] ?? '#3b82f6' }};"></span>
                                    {{ $label }}: {{ $values[$idx] }} {{ __('common.days') }}
                                </td>
                            @endforeach
                        </tr>
                    </table>
                </div>
            @endif
            <table>
                <thead>
                    <tr>
                        <th>{{ __('common.number') }}</th>
                        <th>{{ __('common.name') }}</th>
                        <th>{{ __('common.staff_id') }}</th>
                        <th>{{ __('common.leave_type') }}</th>
                        <th>{{ __('common.start_date') }}</th>
                        <th>{{ __('common.end_date') }}</th>
                        <th>{{ __('common.days') }}</th>
                        <th>{{ __('common.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $index => $item)
                        <tr>
                            <td>{{ config('app.locale') == 'my' ? my_number($index + 1) : $index + 1 }}</td>
                            <td>{{ $item['staff_name'] }}</td>
                            <td>{{ $item['staff_id'] }}</td>
                            <td>{{ $item['leave_type'] }}</td>
                            <td>{{ $item['start_date'] }}</td>
                            <td>{{ $item['end_date'] }}</td>
                            <td>{{ $item['is_not_limited'] ? '-' : $item['total_days'] }}</td>
                            <td>
                                @php
                                    $badge = match ($item['status']) {
                                        'pending' => 'status-pending',
                                        'approved' => 'status-approved',
                                        'rejected' => 'status-rejected',
                                        'revoked' => 'status-rejected',
                                        'cancelled' => 'status-cancelled',
                                        default => 'status-pending',
                                    };
                                @endphp
                                <span class="status-badge {{ $badge }}">{{ $item['status'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="summary">{{ __('common.total_records') }}: {{ count($data) }}</div>
        @else
            <div class="no-data">{{ __('common.no_data') }}</div>
        @endif

        <div class="footer">
            {{ config('app.name') }}
        </div>
    </div>
</body>
</html>
