<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Laporan' }}</title>
    <style>
        @page { margin: 112px 36px 64px 36px; }
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11px; color: #1f2937; margin: 0; }

        /* ===== Kop surat / letterhead ===== */
        .kop { position: fixed; top: -92px; left: 0; right: 0; height: 96px; }
        .kop table.head { width: 100%; border-collapse: collapse; }
        .kop .logo { width: 62px; vertical-align: middle; text-align: center; }
        .kop .logo img { max-height: 56px; max-width: 56px; }
        .kop .txt { vertical-align: middle; text-align: center; }
        .kop .school { font-size: 18px; font-weight: bold; letter-spacing: .4px; text-transform: uppercase; color: #0f172a; }
        .kop .school .lvl { display: block; font-size: 11px; letter-spacing: 3px; font-weight: bold; color: #2563EB; margin-top: 2px; }
        .kop .addr { font-size: 8.5px; color: #64748b; margin-top: 4px; }
        .kop .rule { height: 3px; margin-top: 8px; background-image: linear-gradient(90deg, #2563EB 0%, #14b8a6 100%); border-radius: 2px; }

        /* ===== Footer ===== */
        .foot { position: fixed; bottom: -44px; left: 0; right: 0; height: 34px; font-size: 8px; color: #94a3b8; border-top: 1px solid #eef2f7; padding-top: 5px; }
        .foot .brand { color: #2563EB; font-weight: bold; }
        .foot .pn:after { content: counter(page); }

        /* ===== Judul laporan ===== */
        h1.report-title { text-align: center; font-size: 15px; font-weight: bold; letter-spacing: .5px; margin: 2px 0 0; text-transform: uppercase; color: #0f172a; }
        .title-rule { width: 68px; height: 3px; margin: 7px auto 5px; background-image: linear-gradient(90deg, #2563EB 0%, #14b8a6 100%); border-radius: 2px; }
        .report-sub { text-align: center; font-size: 10px; color: #64748b; margin: 0 0 14px; }

        /* ===== Blok meta (panel lembut) ===== */
        table.meta { width: 100%; font-size: 10px; margin: 0 0 12px; background: #f8fafc; border: 1px solid #eef2f7; border-left: 3px solid #2563EB; border-radius: 8px; }
        table.meta td { padding: 4px 9px; vertical-align: top; }
        table.meta td.k { width: 150px; color: #64748b; }
        table.meta td.s { width: 10px; color: #94a3b8; }

        /* ===== Tabel data ===== */
        table.data { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.data th, table.data td { border: 1px solid #e8edf3; padding: 5px 7px; font-size: 10px; }
        table.data thead th { background: #2563EB; color: #fff; font-size: 9px; text-transform: uppercase; letter-spacing: .4px; text-align: center; padding: 7px 6px; border-color: #2f6bef; border-bottom: 2px solid #14b8a6; }
        table.data tbody td { font-size: 10px; }
        table.data tbody tr:nth-child(even) td { background: #f6f9fd; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .muted { color: #94a3b8; }
        .pass { color: #059669; font-weight: bold; }
        .fail { color: #dc2626; font-weight: bold; }

        /* ===== Kartu ringkasan (stat) ===== */
        .summary { width: 100%; border-collapse: separate; border-spacing: 5px 0; table-layout: fixed; margin: 4px 0 16px; }
        .summary td { background: #fff; border: 1px solid #e9eef5; border-top: 3px solid #2563EB; border-radius: 9px; padding: 10px 6px 11px; text-align: center; }
        .summary .val { font-size: 19px; font-weight: bold; color: #0f172a; line-height: 1.1; }
        .summary .lbl { font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: .6px; margin-top: 3px; }
        .summary td:nth-child(1) { border-top-color: #2563EB; }
        .summary td:nth-child(1) .val { color: #2563EB; }
        .summary td:nth-child(2) { border-top-color: #14b8a6; }
        .summary td:nth-child(2) .val { color: #0d9488; }
        .summary td:nth-child(3) { border-top-color: #f59e0b; }
        .summary td:nth-child(3) .val { color: #d97706; }
        .summary td:nth-child(4) { border-top-color: #0ea5e9; }
        .summary td:nth-child(4) .val { color: #0284c7; }
        .summary td:nth-child(5) { border-top-color: #7c3aed; }
        .summary td:nth-child(5) .val { color: #7c3aed; }

        /* ===== Sub-judul seksi ===== */
        h3.section-title { font-size: 11px; color: #0f172a; margin: 16px 0 5px; padding-left: 8px; border-left: 3px solid #14b8a6; }

        /* ===== Tanda tangan ===== */
        .sign { width: 100%; margin-top: 26px; font-size: 10px; page-break-inside: avoid; }
        .sign td { vertical-align: top; width: 50%; }
        .sign .space { height: 56px; }
        .sign .name { font-weight: bold; text-decoration: underline; color: #0f172a; }
    </style>
</head>
<body>
    <div class="kop">
        <table class="head">
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
        <div class="rule"></div>
    </div>

    <div class="foot">
        <table style="width:100%"><tr>
            <td>Dicetak melalui <span class="brand">SITARA</span> — {{ now()->translatedFormat('d F Y H:i') }}</td>
            <td style="text-align:right">Halaman <span class="pn"></span></td>
        </tr></table>
    </div>

    <main>
        <h1 class="report-title">{{ $title ?? 'Laporan' }}</h1>
        <div class="title-rule"></div>
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
