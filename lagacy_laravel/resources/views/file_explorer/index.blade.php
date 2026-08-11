<body class="body">

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>RTS Downloads</title>
    </head>


    @extends('layouts.app')
    @section('title', 'Home Page')
    @section('content')


        <div class="row" style="  padding-right:20px">
            <div class="col-md-6">
                <div class="bg3" style="border: 2px solid teal; border-radius: 30px; background-color: #E9F6F6;">
                    <div style="margin: 10px; margin-bottom: 30px;">
                        <br>
                        <div class="container py-4">

                            <ul class="list-group">
                                @foreach ($structure as $item)
                                    @if (isset($folderLabels[$item['name']]))
                                        @include('file_explorer.item', [
                                            'item' => $item,
                                            'folderLabels' => $folderLabels,
                                        ])
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-md-6">

                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                    class="bg3">
                    <div style=" margin: 40px;">


                        @if (session('error'))
                            <div class="alert alert-danger">
                                <i class="fa fa-exclamation-circle me-1"></i> {{ session('error') }}
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success">
                                <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
                            </div>
                        @endif


                        <table id="conftooltable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Project ID</th>
                                    <th>Title</th>

                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>

                    </div>
                </div>
            </div>
        </div>

        </div>
        </div>


    </body>

    </html>


    <script>
        // document.addEventListener('DOMContentLoaded', function() {
        //     const toggles = document.querySelectorAll('.folder-toggle');
        //     console.log('Found folder toggles:', toggles.length); // ← Debug line

        //     toggles.forEach(toggle => {
        //         toggle.addEventListener('click', function() {
        //             const parent = this.closest('li');
        //             console.log('Toggling folder:', parent); // ← Debug line
        //             parent.classList.toggle('active-folder');
        //         });
        //     });
        // });



            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.folder-toggle').forEach(toggle => {
                    const collapseTarget = document.querySelector(toggle.getAttribute('data-bs-target'));
                    const icon = toggle.querySelector('.toggle-icon');

                    collapseTarget.addEventListener('show.bs.collapse', () => {
                        icon.classList.remove('fa-plus-square');
                        icon.classList.add('fa-minus-square');
                    });

                    collapseTarget.addEventListener('hide.bs.collapse', () => {
                        icon.classList.remove('fa-minus-square');
                        icon.classList.add('fa-plus-square');
                    });
                });
            });



    $(document).ready(function() {
    table = $('#conftooltable').DataTable({

    "processing": true,
    "serverSide": true,
    columns: [{
    data: 'old_project_id',
    name: 'old_project_id'
    },
    {
    data: 'title',
    name: 'title',
    orderable: true,
    searchable: true
    },


    {
    data: 'action',
    name: 'action',
    orderable: false,
    searchable: false
    },

    ],

    ajax: "{{ route('home.ajaxList3') }}",

    });

    });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('a[data-bs-toggle="collapse"]').forEach(link => {
                const icon = link.querySelector(".folder-icon");
                link.addEventListener("click", () => {
                    const target = document.querySelector(link.getAttribute("href"));
                    if (target.classList.contains("show")) {
                        target.classList.remove("show");
                    } else {
                        target.classList.add("show");
                    }
                });
            });
        });
    </script>


@endsection
