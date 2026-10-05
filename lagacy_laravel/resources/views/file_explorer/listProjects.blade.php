 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8">
     <title>RTS Downloads</title>

     <!-- Font Awesome -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

     @include('components.announcementSideBar', ['case' => '4'])
     @include('components.navbar')

     <style>
         .folder-toggle {
             cursor: pointer;
         }

         .nested {
             display: none;
         }

         .active-folder>.nested {
             display: block;
         }

         .heading {
             position: absolute;
             top: -15px;
             left: 35px;
             background-color: teal;
             color: white;
             padding: 6px;
             border-radius: 15px;
         }

         .heading2 {
             position: absolute;
             top: -15px;
             left: 35px;
             background-color: #623C21;
             color: white;
             padding: 6px;
             border-radius: 15px;
         }

         .footer {
             position: absolute;
             bottom: 1px;
             right: 55px;
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

 <body class="body">
     <br><br><br>

     <div class="row">
         <div class="col-md-3"></div>

         <div class="col-md-6">
             <div class="row" style="margin-top: 10px; padding-left: 10px;">



                 <h2>Project List</h2>

                 <form method="GET" action="{{ route('list.projects') }}" class="mb-4">
                     <input type="text" name="search" value="{{ request('search') }}"
                         placeholder="Search projects..." class="form-control">
                 </form>

                 <ul class="list-group">
                     @foreach ($projects as $project)
                         <li class="list-group-item">
                             <strong>{{ $project->old_project_id }}</strong>
                         </li>
                     @endforeach
                 </ul>

                 <div class="mt-3">
                     {{ $projects->appends(['search' => request('search')])->links() }}
                 </div>




             </div>
         </div>

         <div class="col-md-3"></div>
     </div>

     <script>
         document.addEventListener('DOMContentLoaded', function() {
             document.querySelectorAll('.folder-toggle').forEach(toggle => {
                 toggle.addEventListener('click', function() {
                     const parent = this.closest('li');
                     parent.classList.toggle('active-folder');
                 });
             });
         });
     </script>
 </body>

 </html>
