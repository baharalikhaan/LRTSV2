<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>


    <meta charset="UTF-8">
</head>
<style>
    input[type="radio"]:checked {
        background-color: teal;
        border-color: teal;
    }

    /* Customize the color for the unchecked radio button */
    input[type="radio"] {
        background-color: white;
        border-color: teal;
    }

    /* Style to hide the default radio button appearance */
    input[type="radio"] {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        border-radius: 50%;
        width: 16px;
        height: 16px;
        border: 2px solid teal;
        outline: none;
        display: inline-block;
        vertical-align: middle;
        cursor: pointer;
    }

    .bg3 {
        background-color: #f0f0f0;
        background-image: url("{{ asset('images/infographs-pattern.png') }}");

    }
</style>

<body class="body">


    @include('components.announcementSideBar', ['case' => '5'])
    @include('components.navbar')

    <br>
    <br>
    <div class="container full-height d-flex align-items-center justify-content-center">

        <div class="bg3 " id="box-form"
            style="border: 2px solid teal; border-radius: 18px; width:500px ;  padding: 0px;">

            <h5
                style="background-color: teal; color: white; padding: 10px; border-top-left-radius: 15px; border-top-right-radius: 15px; text-align:center; margin: 0;">
                Update User [{{ $user->id }}]</h5>

                <div class="container mt-4">
                    <div class="row justify-content-center">

                                    <form method="POST" action="{{ route('update') }}">
                                        @csrf
                                        <input type='hidden' value='{{ $user->id }}' id='userid' name='userid'>

                                        @if (session('successuser'))
                                            <div class="alert alert-success" role="alert">
                                                {!! session('successuser') !!}
                                                @php
                                                    session()->forget('successuser');
                                                @endphp
                                            </div>
                                        @endif

                                        <div class="form-group row">
                                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name:') }}</label>
                                            <div class="col-md-6">
                                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                                    name="name" value="{{ $user->name }}" required autocomplete="name" autofocus>
                                                @error('name')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="email"
                                                class="col-md-4 col-form-label text-md-right">{{ __('QU Email:') }}</label>
                                            <div class="col-md-6">
                                                <input id="email" type="email"
                                                    class="form-control @error('email') is-invalid @enderror" name="email"
                                                    value="{{ $user->email }}" required autocomplete="email">
                                                @error('email')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="username"
                                                class="col-md-4 col-form-label text-md-right">{{ __('QU User Name:') }}</label>
                                            <div class="col-md-6">
                                                <input id="username" type="email"
                                                    class="form-control @error('username') is-invalid @enderror" name="username"
                                                    value="{{ $user->username }}"  required autocomplete="username">
                                                @error('username')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-md-4 col-form-label text-md-right">User Type:</label>
                                            <div class="col-md-6">
                                                <select id="user-type" name="type" class="form-control">
                                                    @foreach (['Admin', 'LPI', 'Reviewer', 'Admin+LPI', 'LPI+Reviewer'] as $role)
                                                        <option value="{{ $role }}" {{ $user->roles == $role ? 'selected' : '' }}>
                                                            {{ $role }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('type')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-md-4 col-form-label text-md-right">Pillar:</label>
                                            <div class="col-md-6">
                                                <select name="pillar" class="form-control">
                                                    @foreach ($pillars as $pillar)
                                                        <option value="{{ $pillar->id }}"
                                                            {{ $user->pillar_id == $pillar->id ? 'selected' : '' }}>
                                                            {{ \Illuminate\Support\Str::limit($pillar->pillar, 50) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('pillar')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-md-4 col-form-label text-md-right">Colleges:</label>
                                            <div class="col-md-6">
                                                <select name="tag" class="form-control">
                                                    @foreach ($tags as $tag)
                                                        <option value="{{ $tag->id }}"
                                                            {{ $user->tag_id == $tag->id ? 'selected' : '' }}>
                                                            {{ \Illuminate\Support\Str::limit($tag->tagtitle, 50) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('tag')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-md-4 col-form-label text-md-right">Is user member of QU faculty:</label>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input type="radio" id="Yes" name="faculty" value="1" checked>
                                                    <label class="form-check-label" for="Yes">Yes</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="radio" id="No" name="faculty" value="0">
                                                    <label class="form-check-label" for="No">No</label>
                                                </div>
                                                @error('faculty')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group row mb-0">
                                            <div class="col-md-6 offset-md-4">
                                                <button type="submit" class="btn btn-primary btn-sm float-right" style="background-color: teal; border-color: teal; color: white;">
                                                    {{ __('Submit') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>


        </div>
    </div>
</body>
