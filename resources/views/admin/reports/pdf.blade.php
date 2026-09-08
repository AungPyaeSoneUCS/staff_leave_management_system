<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', session('locale', app()->getLocale())) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — {{ __('app.name') }}</title>
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
                <span class="institution">{{ __('app.name') }}</span>
                <span>{{ __('app.university') }}</span>
            </div>
        </div>

        <div class="report-title">{{ $title }}</div>

        <div class="report-meta">
            <span>{{ __('admin.generated') }}: {{\App\Support\MyanmarDateFormatter::format(now(), 'Y-m-d H:i')}}</span>
        </div>

        @if (! empty($filterSummary))
            <div class="filters">
                @foreach ($filterSummary as $label => $value)
                    <span><strong>{{ $label }}:</strong> {{ $value }}</span>
                @endforeach
            </div>
        @endif

        @if ($type === 'leave_summary')
            @if (! empty($data))
                @if (! empty($chart) && ! empty($chart['labels']))
                    @php
                        $labels = $chart['labels'];
                        $values = $chart['values'];
                        $colors = $chart['colors'] ?? array_fill(0, count($labels), '#3b82f6');
                        $maxVal = max(array_map('floatval', $values)) ?: 1;
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
                        <div class="chart-title">{{ __('admin.leave_by_type') }}</div>
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
                            <th>{{ __('common.department') }}</th>
                            <th>{{ __('common.leave_type') }}</th>
                            <th>{{ __('common.start_date') }}</th>
                            <th>{{ __('common.end_date') }}</th>
                            <th>{{ __('common.days') }}</th>
                            <th>{{ __('common.status') }}</th>
                            <th>{{ __('common.reviewed_by') }}</th>
                            <th>{{ __('common.reviewed_date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $index => $item)
                            <tr>
                                <td>{{ config('app.locale') == 'my' ? my_number($index + 1) : $index + 1 }}</td>
                                <td>
                                    @if(!empty($item['profile_image']))
                                        <img src="{{ public_path('storage/' . $item['profile_image']) }}" style="width: 18px; height: 18px; border-radius: 50%; object-fit: cover; vertical-align: middle; margin-right: 4px;">
                                    @endif
                                    {{ $item['staff_name'] }}
                                </td>
                                <td>{{ $item['staff_id'] }}</td>
                                <td>{{ $item['department'] }}</td>
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
                                    <span class="status-badge {{ $badge }}">{{ __('common.' . $item['status']) }}</span>
                                </td>
                                <td>{{ $item['reviewer'] }}</td>
                                <td>{{ $item['reviewed_at'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="summary">{{ __('common.total_records') }}: {{ count($data) }}</div>
            @else
                <div class="no-data">{{ __('common.no_data') }}</div>
            @endif

        @elseif ($type === 'balance')
            @if (! empty($data))
                @php
                    if (! empty($chart) && ! empty($chart['labels'])) {
                        $labels = array_values($chart['labels']);
                        $usedRaw = $chart['used'] ?? [];
                        $remainingRaw = $chart['remaining'] ?? [];
                        $used = [];
                        $remaining = [];
                        foreach ($labels as $idx => $label) {
                            $used[] = (float) ($usedRaw[$idx] ?? $usedRaw[$label] ?? 0);
                            $remaining[] = (float) ($remainingRaw[$idx] ?? $remainingRaw[$label] ?? 0);
                        }
                        $colors = $chart['colors'] ?? ['#ef4444', '#22c55e'];
                    } else {
                        $labels = [];
                        $used = [];
                        $remaining = [];
                        $colors = ['#ef4444', '#22c55e'];
                        if (! empty($data)) {
                            $g = [];
                            foreach ($data as $d) {
                                $name = $d['staff_name'] ?? '—';
                                if (! isset($g[$name])) {
                                    $g[$name] = ['used' => 0.0, 'remaining' => 0.0];
                                }
                                if (! ($d['is_not_limited'] ?? false)) {
                                    $g[$name]['used'] += (float) ($d['used_days'] ?? 0);
                                    $g[$name]['remaining'] += (float) ($d['remaining_days'] ?? 0);
                                }
                            }
                            $labels = array_keys($g);
                            $used = array_values(array_map(fn($v) => $v['used'], $g));
                            $remaining = array_values(array_map(fn($v) => $v['remaining'], $g));
                        }
                    }

                    $maxVal = max(array_merge($used, $remaining)) ?: 1;
                    $count = count($labels);

                    $svgW = 560;
                    $svgH = 260;
                    $padL = 42;
                    $padR = 16;
                    $padT = 24;
                    $padB = 64;
                    $plotW = $svgW - $padL - $padR;
                    $plotH = $svgH - $padT - $padB;
                    $slot = $plotW / max($count, 1);
                    $groupW = min(88, max(32, $slot * 0.7));
                    $perBarW = $groupW / 2;
                @endphp
                @if ($count > 0)
                    @php
                        $chartRows = [];
                        foreach ($labels as $idx => $label) {
                            $chartRows[] = [
                                'label' => $label,
                                'used' => (float) ($used[$idx] ?? 0),
                                'remaining' => (float) ($remaining[$idx] ?? 0),
                            ];
                        }
                        $tiles = array_chunk($chartRows, 10);
                    @endphp
                    <div class="chart-section">
                        <div class="chart-title">{{ __('admin.balance_report') }} — {{ __('common.used_days') }} vs {{ __('common.remaining_days') }}</div>
                        @foreach ($tiles as $tileIndex => $tile)
                            @php
                                $tileCount = count($tile);
                                $slot = $plotW / max($tileCount, 1);
                                $groupW = min(88, max(32, $slot * 0.7));
                                $perBarW = $groupW / 2;
                            @endphp
                            <div {{ $tileIndex > 0 ? 'style="page-break-before: auto; margin-top: 14px;"' : '' }}>
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

                                        @foreach ($tile as $tIdx => $row)
                                            @php
                                                $u = $row['used'];
                                                $r = $row['remaining'];
                                                $hU = ($u / $maxVal) * $plotH;
                                                $hR = ($r / $maxVal) * $plotH;
                                                $label = $row['label'];
                                                $x0 = $padL + ($tIdx * $slot) + (($slot - $groupW) / 2);
                                                $xU = $x0;
                                                $xR = $x0 + $perBarW;
                                                $yU = $padT + $plotH - $hU;
                                                $yR = $padT + $plotH - $hR;
                                            @endphp
                                            <rect x="{{ round($xU, 2) }}" y="{{ round($yU, 2) }}" width="{{ round($perBarW, 2) }}" height="{{ round(max($hU, 0), 2) }}" fill="{{ $colors[0] ?? '#ef4444' }}" rx="2"/>
                                            <rect x="{{ round($xR, 2) }}" y="{{ round($yR, 2) }}" width="{{ round($perBarW, 2) }}" height="{{ round(max($hR, 0), 2) }}" fill="{{ $colors[1] ?? '#22c55e' }}" rx="2"/>

                                            <text x="{{ round($xU + $perBarW / 2, 2) }}" y="{{ round($yU - 4, 2) }}" text-anchor="middle" font-size="8" fill="#334155" font-weight="bold" font-family="notosansmyanmar">{{ $u }}</text>
                                            <text x="{{ round($xR + $perBarW / 2, 2) }}" y="{{ round($yR - 4, 2) }}" text-anchor="middle" font-size="8" fill="#334155" font-weight="bold" font-family="notosansmyanmar">{{ $r }}</text>

                                            <text x="{{ round($x0 + $groupW / 2, 2) }}" y="{{ $padT + $plotH + 18 }}" text-anchor="middle" font-size="7" fill="#475569" font-family="notosansmyanmar">{{ mb_strlen($label) > 14 ? mb_substr($label, 0, 13).'…' : $label }}</text>
                                        @endforeach
                                    </svg>
                                </div>
                            </div>
                        @endforeach
                        <table class="chart-legend">
                            <tr>
                                <td>
                                    <span class="legend-swatch" style="background: {{ $colors[0] ?? '#ef4444' }};"></span>
                                    {{ __('common.used_days') }}: {{ array_sum($used) }} {{ __('common.days') }}
                                </td>
                                <td>
                                    <span class="legend-swatch" style="background: {{ $colors[1] ?? '#22c55e' }};"></span>
                                    {{ __('common.remaining_days') }}: {{ array_sum($remaining) }} {{ __('common.days') }}
                                </td>
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
                            <th>{{ __('common.department') }}</th>
                            <th>{{ __('common.leave_type') }}</th>
                            <th>{{ __('common.allocated_days') }}</th>
                            <th>{{ __('common.used_days') }}</th>
                            <th>{{ __('common.remaining_days') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr>
                                <td>{{ config('app.locale') == 'my' ? my_number($loop->iteration) : $loop->iteration }}</td>
                                <td>
                                    @if(!empty($item['profile_image']))
                                        <img src="{{ public_path('storage/' . $item['profile_image']) }}" style="width: 18px; height: 18px; border-radius: 50%; object-fit: cover; vertical-align: middle; margin-right: 4px;">
                                    @endif
                                    {{ $item['staff_name'] }}
                                </td>
                                <td>{{ $item['staff_id'] }}</td>
                                <td>{{ $item['department'] }}</td>
                                <td>{{ $item['leave_type'] }}</td>
                                <td>{{ $item['is_not_limited'] ? '-' : $item['allocated_days'] }}</td>
                                <td>{{ $item['is_not_limited'] ? '-' : $item['used_days'] }}</td>
                                <td>{{ $item['is_not_limited'] ? '-' : $item['remaining_days'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="summary">{{ __('common.total_records') }}: {{ count($data) }}</div>
            @else
                <div class="no-data">{{ __('common.no_data') }}</div>
            @endif

        @elseif ($type === 'leave_type')
            @if (! empty($data))
                @if (! empty($chart) && ! empty($chart['labels']))
                    @php
                        $labels = $chart['labels'];
                        $values = array_map('floatval', $chart['values']);
                        $colors = $chart['colors'] ?? array_fill(0, count($labels), '#3b82f6');
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
                        <div class="chart-title">{{ __('admin.leave_type_report') }}</div>
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
                                        {{ $label }}: {{ $values[$idx] }} {{ __('common.staff_count') }}
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
                            <th>{{ __('common.leave_type') }}</th>
                            <th>{{ __('common.staff_count') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr>
                                <td>{{ config('app.locale') == 'my' ? my_number($loop->iteration) : $loop->iteration }}</td>
                                <td>{{ $item['leave_type'] }}</td>
                                <td>{{ $item['staff_count'] ?? $item['total_days'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="summary">{{ __('common.total_records') }}: {{ count($data) }}</div>
            @else
                <div class="no-data">{{ __('common.no_data') }}</div>
            @endif

        @elseif ($type === 'department')
            @if (! empty($data))
                @if (! empty($chart) && ! empty($chart['labels']))
                    @php
                        $labels = $chart['labels'];
                        $values = array_map('floatval', $chart['values']);
                        $colors = $chart['colors'] ?? array_fill(0, count($labels), '#3b82f6');
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
                        <div class="chart-title">{{ __('admin.department_report') }}</div>
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
                            <th>{{ __('common.department') }}</th>
                            <th>{{ __('common.staff_count') }}</th>
                            <th>{{ __('common.total_days') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr>
                                <td>{{ config('app.locale') == 'my' ? my_number($loop->iteration) : $loop->iteration }}</td>
                                <td>{{ $item['department'] }}</td>
                                <td>{{ $item['staff_count'] ?? '-' }}</td>
                                <td>{{ $item['is_not_limited'] ? '-' : $item['total_days'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="summary">{{ __('common.total_records') }}: {{ count($data) }}</div>
            @else
                <div class="no-data">{{ __('common.no_data') }}</div>
            @endif

        @elseif ($type === 'daily')
            @if (! empty($data))
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('common.number') }}</th>
                            <th>{{ __('common.name') }}</th>
                            <th>{{ __('common.staff_id') }}</th>
                            <th>{{ __('common.department') }}</th>
                            <th>{{ __('common.leave_type') }}</th>
                            <th>{{ __('common.total_days') }}</th>
                            <th>{{ __('common.status') }}</th>
                            <th>{{ __('common.duty_exchange') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr>
                                <td>{{ config('app.locale') == 'my' ? my_number($loop->iteration) : $loop->iteration }}</td>
                                <td>{{ $item['staff_name'] }}</td>
                                <td>{{ $item['staff_id'] }}</td>
                                <td>{{ $item['department'] }}</td>
                                <td>{{ $item['leave_type'] }}</td>
                                <td>{{ $item['is_not_limited'] ? '-' : $item['total_days'] }}</td>
                                <td>{{ __('common.' . $item['status']) }}</td>
                                <td>{{ $item['duty_exchange'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="summary">{{ __('common.total_records') }}: {{ count($data) }}</div>
            @else
                <div class="no-data">{{ __('common.no_data') }}</div>
            @endif
        @endif

        <div class="footer">
            {{ __('app.system_footer') }}
        </div>
    </div>
</body>
</html>
