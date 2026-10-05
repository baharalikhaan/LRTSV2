 <head>

     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

     <style>
         .navbar-logo {

             width: 80px;
             font-size: 34px;
             color: white;
             padding-left: 40;
             cursor: pointer;
         }

         .navbar-brand {

             width: 335px;
             font-size: 34px;
             color: white;
             padding-left: 30;
             cursor: pointer;
         }

         .navbar .navbar-nav .nav-link {
             color: white;
         }

         .navbar-icon-top .navbar-nav .nav-link>.fa {
             position: relative;
             width: 36px;
             font-size: 24px;
             color: white;
         }

         .navbar-icon-top .navbar-nav .nav-link>.fa>.badge {
             font-size: 0.75rem;
             position: absolute;
             right: 0;
             font-family: sans-serif;
             color: white;
         }

         .navbar-icon-top .navbar-nav .nav-link>.fa {
             top: 3px;
             line-height: 12px;
         }

         .navbar-icon-top .navbar-nav .nav-link>.fa>.badge {
             top: -10px;
         }

         @media (min-width: 576px) {
             .navbar-icon-top.navbar-expand-sm .navbar-nav .nav-link {
                 text-align: center;
                 display: table-cell;
                 height: 70px;
                 vertical-align: middle;
                 padding-top: 0;
                 padding-bottom: 0;
             }

             .navbar-icon-top.navbar-expand-sm .navbar-nav .nav-link>.fa {
                 display: block;
                 width: 48px;
                 margin: 2px auto 4px auto;
                 top: 0;
                 line-height: 24px;
             }

             .navbar-icon-top.navbar-expand-sm .navbar-nav .nav-link>.fa>.badge {
                 top: -7px;
             }
         }

         @media (min-width: 768px) {
             .navbar-icon-top.navbar-expand-md .navbar-nav .nav-link {
                 text-align: center;
                 display: table-cell;
                 height: 70px;
                 vertical-align: middle;
                 padding-top: 0;
                 padding-bottom: 0;
             }

             .navbar-icon-top.navbar-expand-md .navbar-nav .nav-link>.fa {
                 display: block;
                 width: 48px;
                 margin: 2px auto 4px auto;
                 top: 0;
                 line-height: 24px;
             }

             .navbar-icon-top.navbar-expand-md .navbar-nav .nav-link>.fa>.badge {
                 top: -7px;
             }
         }

         @media (min-width: 992px) {
             .navbar-icon-top.navbar-expand-lg .navbar-nav .nav-link {
                 text-align: center;
                 display: table-cell;
                 height: 70px;
                 vertical-align: middle;
                 padding-top: 0;
                 padding-bottom: 0;
             }

             .navbar-icon-top.navbar-expand-lg .navbar-nav .nav-link>.fa {
                 display: block;
                 width: 48px;
                 margin: 2px auto 4px auto;
                 top: 0;
                 line-height: 24px;
             }

             .navbar-icon-top.navbar-expand-lg .navbar-nav .nav-link>.fa>.badge {
                 top: -7px;
             }
         }

         @media (min-width: 1200px) {
             .navbar-icon-top.navbar-expand-xl .navbar-nav .nav-link {
                 text-align: center;
                 display: table-cell;
                 height: 70px;
                 vertical-align: middle;
                 padding-top: 0;
                 padding-bottom: 0;
             }

             .navbar-icon-top.navbar-expand-xl .navbar-nav .nav-link>.fa {
                 display: block;
                 width: 48px;
                 margin: 2px auto 4px auto;
                 top: 0;
                 line-height: 24px;
             }

             .navbar-icon-top.navbar-expand-xl .navbar-nav .nav-link>.fa>.badge {
                 top: -7px;
             }
         }

         /* Teal Button */
         .btn-teal {
             background-color: teal;
             color: white;
         }



         .bg3 {
             background-color: #f0f0f0;
             background-image: url("{{ asset('images/infographs-pattern.png') }}");

         }

         .dropdown-menu2 {
             padding: 0;
             list-style: none;
             margin: 0;
         }

         .dropdown-item2 {
             display: block;
             padding: 8px 16px;
             text-decoration: none;
             color: rgb(245, 242, 242);
             /* Default text color */
             background-color: white;
             /* Default background color */
         }

         .dropdown-item2:hover {
             background-color: teal;
             color: white cursor: pointer;
             /* Text color when hovered */
         }

         /* Styling for selected state using the :target pseudo-class */
         .dropdown-menu2 a:target {
             background-color: teal;
             color: white;
             /* Text color for the selected link */
         }
     </style>
     <title>Research Tracking System</title>
     <link rel="icon" type="image/png" href="{{ asset('images/research_logo.png') }}">
 </head>

 <body>

     <div class="container-fluid" style="padding-left:50">
         <nav class="navbar navbar-icon-top navbar-expand-lg " style="background-color: teal; height: 120px;">
             <p class="navbar-brand">
                 <a href="{{ route('home') }}" style="color: inherit;  text-decoration: none;">
                     <span style="display: block; font-size: 30; background-color: teal; margin-top:8; "> <img
                             src="https://mybanner.qu.edu.qa/css/images/sgu-logo-02.jpg" alt="Logo"
                             style="margin-left:-70;height:80;width:450;"></span>
                     <span
                         style="display: block;  font-size:17px; border-top: 1px solid white;border-bottom: 1px solid white; color: white; background-color: teal; text-align:center;">
                         RTS: Research Tracking System </span>
                 </a>
             </p>

             <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                 aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                 <span class="navbar-toggler-icon"></span>
             </button>

             <div class="collapse navbar-collapse" id="navbarSupportedContent">
                 <ul class="navbar-nav mr-auto">

                 </ul>
                 <ul class="navbar-nav ">

                     @if (auth()->check())
                         <li class="nav-item">
                             <a class="nav-link" href="#">
                                 <span style="display: block; font-size: larger;">{{ auth()->user()->name }}</span>
                             </a>
                         </li>

                         <li class="nav-item" style='padding-left:4;margin-top:18'>
                             <div class="btn-group" role="group" aria-label="Button group">
                                 @php
                                     $roles = explode('+', auth()->user()->roles);
                                 @endphp
                                 @foreach ($roles as $role)
                                     @php
                                         $role = trim($role);
                                     @endphp
                                     <a href="{{ route('switchRole', ['role' => $role]) }}" type="button"
                                         class="btn  btn-outline-light {{ auth()->user()->type == $role ? 'Active' : '' }}">
                                         {{ $role }}</a>
                                 @endforeach
                             </div>
                         </li>

                         <li class="nav-item" style='padding-left:4'>
                             <form action="{{ route('logout') }}" method="post">
                                 @csrf
                                 <div class="btn-group" role="group" aria-label="Button group"
                                     style="margin-top:18;margin-left:18">
                                     <button class="btn   btn-outline-light" type="submit">Logout</button>
                                     <button type="button" class="btn  btn-teal-inverse" href="#"
                                         id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown"
                                         aria-expanded="false">
                                         Help
                                     </button>
                                     <ul class="dropdown-menu dropdown-menu-start"
                                         aria-labelledby="navbarDropdownMenuLink">
                                         <li><a onClick="showModal('add_project_tutorial')" class="dropdown-item2">Add
                                                 Agreement</a></li>
                                         <li><a onClick="showModal('add_project_tutorial')" class="dropdown-item2">Add
                                                 Project</a></li>
                                     </ul>
                                 </div>
                             </form>
                         </li>

                         <li class="nav-item" style='padding-left:0'>
                            <div style="margin-top:22" class="btn-group" role="group" aria-label="Button group">
                                <a class="nav-link" href="#" id="navbarDropdown" role="button"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-bell">
                                        <span class="badge badge-danger">{{ 1 /*$alerts ? count($alerts) : 0 */}}</span>
                                    </i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    @if (auth()->user()->type == 'LPI')
                                        <li><a href="#" class="dropdown-item">Last Date to submit the Progress
                                                Report for Cycle 7 is 31st October 2024</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>


                     @else
                         <li class="nav-item">
                             <!-- <a href="{{ route('auth/login') }}" style="margin-top:18" type="button" class="btn btn-outline-light">Login</a> -->
                             <div class="btn-group" role="group" aria-label="Button group" style="margin:18">
                                 <!-- <button type="button" class="btn btn-sm  btn-outline-light">Login</button> -->
                                 <a href="{{ route('auth/login') }}" type="button"
                                     class="btn   btn-outline-light">Login</a>
                                 {{-- <button type="button" class="btn  btn-teal-inverse">Help</button> --}}

                                 <button type="button" class="btn  btn-teal-inverse" href="#"
                                     id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown"
                                     aria-expanded="false">
                                     Help
                                 </button>

                                 <ul class="dropdown-menu dropdown-menu-start"
                                     aria-labelledby="navbarDropdownMenuLink">
                                     <li><a onClick="showModal('add_project_tutorial')" class="dropdown-item2">Add
                                             Agreement</a></li>
                                     <li><a onClick="showModal('add_project_tutorial')" class="dropdown-item2">Add
                                             Project</a></li>
                                 </ul>
                             </div>

                         </li>
                     @endif

                 </ul>

             </div>
         </nav>

     </div>
     <!--container end.//-->

 </body>

 </html>

 <head>

     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">

     <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js"></script>
     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
     <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

     <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
     <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
     <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

     <link rel="stylesheet" type="text/css"
         href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
     <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
     <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>

     {{-- button strip for datatables --}}
     <script src='https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js'></script>
     <script src='https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js'></script>
     <script src='https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js'></script>
     <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>



     <script>
         function handleOptionClick(option) {
             // Perform the action based on the selected option
             const routeUrl = `{{ route('switchRole', ['role' => '__option__']) }}`;
             const url = routeUrl.replace('__option__', option);

             fetch(url, {
                     method: 'GET',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-Requested-With': 'XMLHttpRequest',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                     },
                 })
                 .then(response => response.json())
                 .then(data => {
                     // Handle the response from the server if needed
                     console.log(data);
                 })
                 .catch(error => {
                     console.error('Error:', error);
                 });
         }
     </script>

     <style>
         .heading {
             position: absolute;
             top: -15;
             left: 35;
             background-color: teal;
             color: white;
             padding: 6px;
             border-radius: 15px 15px 15px 15px;
         }

         .heading2 {
             position: absolute;
             top: -15;
             left: 35;
             background-color: #623C21;
             color: white;
             padding: 6px;
             border-radius: 15px 15px 15px 15px;
         }

         .footer {
             position: absolute;
             bottom: 1;
             right: 55;
             font-size: 11px;
             font-style: italic;
             color: #623C21;
         }

         .btn-teal {
             color: #fff;
             background-color: #008080;
             border-color: #008080;
         }

         .btn-teal:hover {
             color: #fff;
             background-color: #005959;
             border-color: #005959;
         }
     </style>
 </head>


 <!-- Borderless Modal -->
 <div class="modal fade borderless-modal" id="modalagreement" tabindex="-1" role="dialog"
     aria-labelledby="borderlessModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-lg" role="document">
         <div class="modal-content">
             <!-- Modal Header -->
             <div class="modal-header" style="background-color: teal; text-align: center;">
                 <h5 class="modal-title text-white" id="modalTitle"></h5>
             </div>
             <!-- Modal Body -->
             <div class="modal-body" id="body">
                 <!-- Your modal body content goes here -->
             </div>
         </div>
     </div>
 </div>

 <script>
     // storage_path('app\downloads\add_project_tutorial.pdf')


     function showModal(id) {

         var url = '{{ url('/') }}/serveFile3?file=' + id + '.pdf';
         window.open(url, '_blank');

         //  id = 'add_agreement_tutorial';
         //   $('#modalTitle').text('Help');
         //   $('#body').html('<iframe src="{{ url('/') }}/serveFile3?file=' + id +
         //       '.pdf" id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe>');
         //   $('#modalagreement').modal('show');
     }
 </script>
