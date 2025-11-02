<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Factuur - Prindustry</title>
    <style>
        html {
            font-size: {{ $font_size }}px;
            font-family: "{{ $font_family }}";
        }

        @page {
            size: {{ $letterhead_size }} portrait;
            margin: 0;
            /**
             * A4: 210mm x 297mm
             * A4 useable space: 184.6mm x 271.6mm
             * Letter: 215.9mm x 279.4mm
             * Letter useable space: 190.5mm x 266.7mm
             */
        }

        .page-number {
            position: absolute;
            bottom: 8mm;
            right: 12.7mm;
            text-align: right;
            z-index: 10;
        }

        .page-number:after {
            content: counter(page);
        }

        .page_break {
            page-break-before: always;
        }

        body {
            background-image: url("{{ $background }}");
            background-size: cover;
            background-position: top left;
            background-repeat: no-repeat;
            color: #333;
            padding: 12.7mm;
        }

        .logo-container {
            height: 70px;
            text-align: {{ $logo_position }};
            margin-bottom: 32px;
        }

        .logo {
            width: {{ $logo_width }}px;
        }

        .header {
            padding-top: {{ $offset }}px;
            margin-bottom: 2.5rem;
            text-align: {{ $direction === 'rtl' ? 'right' : 'left' }};
        }

        .invoice-details {
            margin-bottom: 1rem;
        }

        .invoice-details td {
            padding: 0.25rem 0;
        }

        .invoice-title {
            text-transform: uppercase;
            font-size: 1.75rem;
            font-weight: bold;
            text-align: left;
        }

        table {
            table-layout: fixed;
            width: 100%;
            border-collapse: collapse;
        }

        .items-container {
            min-height: {{ $first_table_height }}px;
            margin-bottom: 1rem;
        }

        .items-container.extra {
            min-height: {{ $extra_table_height }}px;
            margin-bottom: 1rem;
        }

        .items th {
            border-bottom: 1px solid #000;
            text-align: left;
            padding: 0.5rem 1rem;
        }

        .items th:first-child {
            padding-left: 0;
        }

        .items th:last-child {
            padding-right: 0;
        }

        .items td {
            vertical-align: top;
            padding: {{ max(0.25, $font_size * 0.025) }}rem {{ max(0.3, $font_size * 0.03) }}rem;
            min-height: {{ max(3.2, $font_size * 0.22) }}rem;
            height: {{ $row_height }}px;
            overflow: visible;
            word-wrap: break-word;
            line-height: {{ max(1.2, $font_size >= 18 ? 1.35 : ($font_size >= 16 ? 1.25 : 1.2)) }};
            box-sizing: border-box;
        }

        .items td:first-child {
            padding-left: 0;
        }

        .items td:last-child {
            padding-right: 0;
        }

        .items tr {
            border-bottom: 1px solid #aaa;
        }

        .items tr:last-child {
            border-bottom: none;
        }

        .currency-cell {
            position: relative;
            text-align: right;
            padding-left: {{ max(1.2, $font_size * 0.08) }}rem !important;
            white-space: nowrap;
        }

        .currency-symbol {
            position: absolute;
            left: {{ max(0.2, $font_size * 0.015) }}rem;
            font-family: 'DejaVu Sans';
            font-size: {{ max(0.8, min(1.0, $font_size * 0.08)) }}rem;
        }

        .totals {
            width: 100%;
            margin-left: auto;
            margin-top: 1.5rem;
            clear: both;
        }

        .totals tr:first-child {
            border-top: 1px solid #000;
        }

        .totals tr:first-child td {
            padding-top: 0.5rem;
        }

        .totals td {
            padding: 0.25rem 0;
        }

    </style>
</head>
<body>

@yield('content')

</body>
</html>
