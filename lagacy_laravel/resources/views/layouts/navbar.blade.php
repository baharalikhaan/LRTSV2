<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Research Tracking System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/research_logo.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: system-ui, sans-serif;
            margin: 0;
            padding-top: 70px;

        }

        .bg2 {
            background-color: #f0f0f0;
            background-image: url("{{ asset('images/infographs-pattern.png') }}");

        }

        .navbar-custom {
            background-color: teal;
            height: 70px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
        }

        .navbar-brand img {
            height: 60px;
            width: auto;
        }

        .navbar-brand {
            padding: 0;
            margin-right: 20px;
        }

        .navbar-subtitle {
            font-size: 17px;
            color: white;
            border-top: 1px solid white;
            border-bottom: 1px solid white;
            text-align: center;
            width: 100%;
            margin-top: 4px;
        }

        .navbar .nav-link,
        .btn-outline-light {
            color: white;
        }

        .btn-outline-light:hover,
        .btn-outline-light.active,
        .role-button.Active {
            background-color: white;
            color: teal;
        }

        .role-button {
            border-color: white;
        }

        .dropdown-item-custom {
            padding: 8px 16px;
            color: #333;
            background-color: white;
        }

        .dropdown-item-custom:hover,
        .dropdown-item-custom:target {
            background-color: teal;
            color: white;
        }

        .logo-wrapper .logo-img {
            transition: opacity 0.3s ease-in-out;
        }

        .logo-wrapper:hover .logo-img {
            content: url('/images/sgu-logo-inverted.png');
        }

        .rounded-logo {
            border-radius: 12px;
            /* You can change this value as needed */
        }


    </style>


</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-custom">

        <div class="container-fluid px-4">
            {{-- <a class="navbar-brand d-flex flex-column align-items-start" href="{{ route('home') }}">

                <img style="margin:-20px; width:220px;" src="{{ asset('images/sgu-logo-02.png') }}" alt="Logo">
            </a> --}}

            <a class="navbar-brand d-flex flex-column align-items-start logo-wrapper" href="{{ route('home') }}">
                <img class="logo-img rounded-logo" style="margin:-25px; width:220px;"
                    src="{{ asset('images/sgu-logo-02.png') }}" alt="Logo">
            </a>


            <div class="collapse navbar-collapse" id="navbarContent">


                <ul class="navbar-nav ms-auto align-items-center">

                    @if (auth()->check())
                        <li class="nav-item ms-3">
                            <span class="nav-link">{{ auth()->user()->name }}</span>
                        </li>
                        <li class="nav-item ms-3">
                            <div class="btn-group">
                                @php $roles = explode('+', auth()->user()->roles); @endphp
                                @foreach ($roles as $role)
                                    @php $role = trim($role); @endphp
                                    <a href="{{ route('switchRole', ['role' => $role]) }}"
                                        class="btn btn-outline-light role-button {{ auth()->user()->type == $role ? 'Active' : '' }}">
                                        {{ $role }}
                                    </a>
                                @endforeach
                            </div>
                        </li>


                        <li class="nav-item ms-3">
                            <form action="{{ route('logout') }}" method="post" class="d-flex align-items-center">
                                @csrf
                                <div class="btn-group" style="padding-top:15px;">
                                    <button class="btn btn-outline-light" type="submit">Logout</button>
                                    <button type="button" class="btn btn-outline-light dropdown-toggle"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        Help
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item dropdown-item-custom"
                                                href="{{ url('serveFile3?file=add_agreement_tutorial.pdf') }}"
                                                target="_blank">Add
                                                Agreement</a></li>
                                        <li><a class="dropdown-item dropdown-item-custom"
                                                href="{{ url('serveFile3?file=add_project_tutorial.pdf') }}"
                                                target="_blank">Add
                                                Project</a></li>

                                        {{-- onclick="showModal('add_project_tutorial')" --}}
                                    </ul>
                                </div>
                            </form>
                        </li>
                    @else
                        <li class="nav-item ms-3">
                            <div class="btn-group">
                                <a href="{{ route('auth/login') }}" class="btn btn-outline-light">Login</a>
                                <button type="button" class="btn btn-outline-light dropdown-toggle"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    Help
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item dropdown-item-custom" href="#"
                                            onclick="showModal('add_project_tutorial')">Add Agreement</a></li>
                                    <li><a class="dropdown-item dropdown-item-custom" href="#"
                                            onclick="showModal('add_project_tutorial')">Add Project</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <script>
        function showModal(id) {
            alert("Triggering modal: " + id);
        }
    </script>

</body>

</html>
