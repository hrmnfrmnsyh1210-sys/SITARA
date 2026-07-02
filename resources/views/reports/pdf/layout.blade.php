<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Laporan' }}</title>
    <style>
        @page { margin: 90px 40px 70px 40px; }
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11px; color: #1f2937; margin: 0; }

        /* Kop surat */
        .kop { position: fixed; top: -70px; left: 0; right: 0; height: 78px; border-bottom: 2px solid #111827; padding-bottom: 6px; }
        .kop table { width: 100%; border-collapse: collapse; }
        .kop .logo { width: 64px; vertical-align: middle; text-align: center; }
        .kop .logo img { max-height: 58px; max-width: 58px; }
        .kop .txt { vertical-align: middle; text-align: center; }
        .kop .school { font-size: 17px; font-weight: bold; letter-spacing: .3px; text-transform: uppercase; }
        .kop .school .lvl { display: block; font-size: 12px; letter-spacing: 2px; font-weight: bold; }
        .kop .addr { font-size: 9px; color: #4b5563; margin-top: 2px; }

        .foot { position: fixed; bottom: -50px; left: 0; right: 0; height: 40px; font-size: 8px; color: #6b7280; border-top: 1px solid #d1d5db; padding-top: 4px; }
        .foot .pn:after { content: counter(page); }

        h1.report-title { text-align: center; font-size: 14px; margin: 4px 0 2px; text-transform: uppercase; }
        .report-sub { text-align: center; font-size: 10px; color: #6b7280; margin: 0 0 12px; }

        table.meta { width: 100%; font-size: 10px; margin-bottom: 10px; }
        table.meta td { padding: 1px 4px; vertical-align: top; }
        table.meta td.k { width: 130px; color: #6b7280; }
        table.meta td.s { width: 12px; }

        table.data { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.data th, table.data td { border: 1px solid #cbd5e1; padding: 5px 6px; }
        table.data thead th { background: #2563eb; color: #fff; font-size: 10px; text-align: center; }
        table.data tbody td { font-size: 10px; }
        table.data tbody tr:nth-child(even) td { background: #f8fafc; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .muted { color: #6b7280; }
        .pass { color: #16a34a; font-weight: bold; }
        .fail { color: #dc2626; font-weight: bold; }

        .summary { width: 100%; border-collapse: collapse; margin: 6px 0 14px; }
        .summary td { border: 1px solid #e5e7eb; padding: 8px; text-align: center; width: 20%; }
        .summary .val { font-size: 16px; font-weight: bold; color: #111827; }
        .summary .lbl { font-size: 9px; color: #6b7280; text-transform: uppercase; }

        .sign { width: 100%; margin-top: 28px; font-size: 10px; page-break-inside: avoid; }
        .sign td { vertical-align: top; width: 50%; }
        .sign .space { height: 58px; }
        .sign .name { font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="kop">
        <table>
            <tr>
                @if(!empty($logoPath))
                    <td class="logo"><img src="{{ $logoPath }}" alt="logo"></td>
                @endif
                <td class="txt">
                    <div class="school">
                        {{ $school?->name ?? 'SEKOLAH' }}
                        @if($school?->level)<span class="lvl">{{ $school->level }}</span>@endif
                    </div>
                    <div class="addr">
                        {{ $school?->address }}@if($school?->phone) · Telp: {{ $school->phone }}@endif
                        @if($school?->email) · {{ $school->email }}@endif
                        @if($school?->npsn) · NPSN: {{ $school->npsn }}@endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="foot">
        <table style="width:100%"><tr>
            <td>Dicetak melalui SITARA — {{ now()->translatedFormat('d F Y H:i') }}</td>
            <td style="text-align:right">Halaman <span class="pn"></span></td>
        </tr></table>
    </div>

    <main>
        <h1 class="report-title">{{ $title ?? 'Laporan' }}</h1>
        @isset($subtitle)<p class="report-sub">{{ $subtitle }}</p>@endisset

        @if(!empty($meta))
            <table class="meta">
                @foreach($meta as $k => $v)
                    <tr><td class="k">{{ $k }}</td><td class="s">:</td><td>{{ $v }}</td></tr>
                @endforeach
            </table>
        @endif

        @yield('report')

        @hasSection('signature')
            <table class="sign">
                <tr>
                    <td></td>
                    <td class="text-center">
                        @yield('signature')
                    </td>
                </tr>
            </table>
        @endif
    </main>
</body>
</html>
