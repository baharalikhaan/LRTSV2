{{--
    Shared DataTable treatment for the wide report grids (Project Details,
    Grading Details): the table scrolls inside its own pane, both header rows
    stay pinned while scrolling vertically, the first column stays pinned while
    scrolling sideways, and the header rules survive printing.

    Parameters:
      $tableId      id of the <table> element
      $nowrapCells  true for single-line cells (Project Details), false when the
                    cells hold wrapped text such as reviewer feedback
--}}
@php
    $tableId = $tableId ?? 'reportTable';
    $nowrapCells = $nowrapCells ?? true;
@endphp
<style>
    /* The table is much wider than the screen: keep it scrolling inside the
       panel instead of stretching the grid column (and the sidebar) with it. */
    .report-table-scroll {
        overflow: auto;
        contain: inline-size;
        max-width: 100%;
        max-height: calc(100vh - 250px);
    }

    /* separate borders are needed for the sticky header rows */
    #{{ $tableId }} {
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12.5px;
    }

    #{{ $tableId }} td,
    #{{ $tableId }} th {
        padding: 8px 10px !important;
        vertical-align: middle;
        @if($nowrapCells)
            white-space: nowrap;
        @endif
    }

    /* grouped header row */
    #{{ $tableId }} thead tr.group-header-row th {
        background-color: var(--sand-50, #faf7f0);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--brand-700, #63102b);
        text-align: center;
        padding: 6px 10px !important;
        white-space: nowrap;
        border-top: 1px solid var(--brand-500, #8d1b3d);
        border-bottom: 1px solid var(--brand-500, #8d1b3d);
        position: sticky;
        top: 0;
        z-index: 3;
    }

    /* hairline between neighbouring groups */
    #{{ $tableId }} thead tr.group-header-row th + th {
        border-left: 1px solid rgba(141, 27, 61, .28);
    }

    /* maroon hairline under the column-name row (the primary header).
       background-color (not the background shorthand) so DataTables' sorting
       arrow background image survives. */
    #{{ $tableId }} thead tr:not(.group-header-row) th {
        background-color: var(--sand-100, #f2ead6);
        border-bottom: 1px solid var(--brand-500, #8d1b3d);
        position: sticky;
        top: var(--group-header-h, 28px);
        z-index: 2;
    }

    /* keep the header rules and their tint when printed */
    #{{ $tableId }} thead th {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* The first column stays pinned on the left while scrolling sideways, so
       every row can still be identified. */
    #{{ $tableId }} thead tr.group-header-row th:first-child {
        position: sticky;
        left: 0;
        z-index: 4;
        background-color: var(--sand-50, #faf7f0);
        border-left: none;
    }

    #{{ $tableId }} thead tr:not(.group-header-row) th:first-child {
        position: sticky;
        left: 0;
        z-index: 3;
        background-color: var(--sand-100, #f2ead6);
    }

    #{{ $tableId }} tbody td:first-child {
        position: sticky;
        left: 0;
        z-index: 1;
        background-color: #fff;
        border-right: 1px solid var(--ink-100, #eee);
    }

    /* The pinned cell must be opaque, so the striped and hover tints are
       applied as solid colours (the app's rgba tints would let the scrolling
       content bleed through). */
    #{{ $tableId }} tbody tr:nth-child(even) td:first-child {
        background-color: #faf7f0;
    }

    #{{ $tableId }} tbody tr:hover td:first-child {
        background-color: #fbeef1;
    }

    @media print {
        /* let the table flow over the printed pages again */
        .report-table-scroll {
            max-height: none !important;
            overflow: visible !important;
        }

        #{{ $tableId }} thead th,
        #{{ $tableId }} tbody td {
            position: static !important;
        }

        #{{ $tableId }} thead {
            display: table-header-group;
        }
    }
</style>
