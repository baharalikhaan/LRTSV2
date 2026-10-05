<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Report 2 Extension</title>
    <style>
        .filter-row {
            display: flex;
            gap: 20px;
            align-items: flex-end;
            margin-bottom: 20px;
            flex-wrap: wrap;
            margin-left: auto;
            justify-content: flex-end;
        }
        .filter-row .form-group {
            margin-bottom: 0;
            min-width: 200px;
        }
        #select-all {
            cursor: pointer;
        }
        .bulk-actions {
            margin: 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .badge-secondary {
            background-color: #6c757d;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .btn-teal {
            background-color: teal;
            border-color: teal;
            color: white;
        }
        .btn-teal:hover {
            background-color: #006666;
            border-color: #006666;
            color: white;
        }
        .btn-teal:disabled {
            background-color: #80b3b3;
            border-color: #80b3b3;
            cursor: not-allowed;
        }
        .text-muted {
            color: #6c757d;
            font-style: italic;
        }
        .filter-label {
            font-weight: 600;
            margin-bottom: 4px;
            display: block;
            color: #333;
        }
        .selected-count {
            font-size: 14px;
            color: #666;
        }
        .alert {
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
    </style>
</head>

<body class="body">
    @extends('layouts.app')
    @section('title', 'PR2 Extension')
    @section('content')
        <div class="col-md-12" style="margin-top: 10px;">
            <div style="border: 2px solid teal; border-radius: 30px 30px 30px 30px; background-color: #E9F6F6">
                <div style="margin: 40px;">

                    <div class="heading" style="margin-bottom: 20px;">
                        Project Extension
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Filters -->
                    <form id="filter-form" class="filter-row">
                        <div class="form-group">
                            <label class="filter-label">Cycle</label>
                            <select name="cycle" id="cycle-filter" class="form-control">
                                <option value="">All Cycles</option>
                                @foreach ($cycles as $cycle)
                                    <option value="{{ $cycle->id }}">{{ $cycle->cycle_title }} ({{ ucfirst($cycle->grant_type) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="button" id="apply-filter" class="btn btn-teal">
                                <i class="fa fa-search"></i> Search
                            </button>
                        </div>
                    </form>

                    <!-- Bulk Actions -->
                    <form id="bulk-form" method="POST" action="{{ route('pr2.extend.bulk') }}">
                        @csrf
                        <div class="bulk-actions">
                            <button type="submit" id="bulk-extend-btn" class="btn btn-teal" disabled>
                                <i class="fa fa-check-circle"></i> Extend Selected
                            </button>
                            <span id="selected-count" class="selected-count">0 projects selected</span>
                        </div>

                        <!-- Data Table -->
                        <table id="pr2-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all" /></th>
                                    <th>Project ID</th>
                                    <th>Title</th>
                                    <th>LPI</th>
                                    <th>Cycle</th>
                                    <th>Grant Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </form>

                </div>
            </div>
        </div>
    </body>

            <script type="text/javascript">
        $(document).ready(function() {
            var table = $('#pr2-table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 100,
                ajax: {
                    url: "{{ route('ajaxListPr2Extension') }}",
                    data: function(d) {
                        d.cycle = $('#cycle-filter').val();
                        d.grant_type = '';
                    }
                },
                columns: [
                    {
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'old_project_id',
                        name: 'old_project_id'
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'lpi_name',
                        name: 'lpi_name'
                    },
                    {
                        data: 'cycle_title',
                        name: 'cycle_title'
                    },
                    {
                        data: 'grant_type',
                        name: 'grant_type'
                    },
                    {
                        data: 'extended',
                        name: 'extended',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                dom: 'lBfrtip',
                buttons: [
                    'copyHtml5',
                    'excelHtml5',
                    'csvHtml5',
                    'pdfHtml5',
                    'print'
                ],
                drawCallback: function() {
                    // Re-bind select-all after table redraw
                    updateSelectAllState();
                    updateSelectedCount();
                    updateBulkButton();
                }
            });

            // Apply filter button
            $('#apply-filter').click(function() {
                table.ajax.reload();
            });

            // Select all checkbox
            $(document).on('change', '#select-all', function() {
                var isChecked = $(this).prop('checked');
                $('.project-checkbox').prop('checked', isChecked);
                updateSelectedCount();
                updateBulkButton();
            });

            // Individual checkbox change
            $(document).on('change', '.project-checkbox', function() {
                updateSelectedCount();
                updateBulkButton();
                updateSelectAllState();
            });

            // Bulk extend button click
            $('#bulk-extend-btn').click(function(e) {
                e.preventDefault();
                var selected = [];
                $('.project-checkbox:checked').each(function() {
                    selected.push($(this).val());
                });
                if (selected.length === 0) {
                    alert('No projects selected.');
                    return;
                }
                var form = $('#bulk-form');
                // Remove existing hidden inputs
                form.find('input[name="selected_projects[]"]').remove();
                $.each(selected, function(i, val) {
                    form.append('<input type="hidden" name="selected_projects[]" value="' + val + '" />');
                });
                form.submit();
            });

            function updateSelectedCount() {
                var count = $('.project-checkbox:checked').length;
                $('#selected-count').text(count + ' project(s) selected');
            }

            function updateBulkButton() {
                var count = $('.project-checkbox:checked').length;
                $('#bulk-extend-btn').prop('disabled', count === 0);
            }

            function updateSelectAllState() {
                var totalCheckboxes = $('.project-checkbox').length;
                var checkedCheckboxes = $('.project-checkbox:checked').length;

                if (totalCheckboxes === 0) {
                    $('#select-all').prop('checked', false);
                    $('#select-all').prop('indeterminate', false);
                } else if (checkedCheckboxes === totalCheckboxes) {
                    $('#select-all').prop('checked', true);
                    $('#select-all').prop('indeterminate', false);
                } else if (checkedCheckboxes > 0) {
                    $('#select-all').prop('checked', false);
                    $('#select-all').prop('indeterminate', true);
                } else {
                    $('#select-all').prop('checked', false);
                    $('#select-all').prop('indeterminate', false);
                }
            }
        });
    </script>
@endsection
