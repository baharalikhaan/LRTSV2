<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Announcement</title>


</head>

@extends('layouts.app')
@section('title', 'Home Page')
@section('content')


    <body class="body">
        <br>
        <br>

        <div class="container" id="box-form"
            style="border: 2px solid teal; border-radius: 18px; width:500px ;  padding: 0px;">
            <h5
                style="background-color: teal; color: white; padding: 10px; border-top-left-radius: 15px; border-top-right-radius: 15px; text-align:center; margin: 0;">
                Update Announcement</h5>
            <div class="container" id="box-form" style="  margin: 10; ">

                <form action="{{ route('announcementUpdate', $announcement->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <table class="table-display">

                        <tr style="display:none">
                            <td></td>
                            <td><input class="form-control" type="text" name="id" value="{{ $announcement->id }}"
                                    class="form-control"></td>
                        </tr>
                        <tr>
                            <td><label for="">Subject</label></td>
                            <td><input class="form-control" type="text" id="subject" name="subject"
                                    value="{{ $announcement->subject }}">
                                @if ($errors->has('subject'))
                                    <span class="text-danger">{{ $errors->first('subject') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><label for="">Content</label></td>
                            <td>
                                <textarea class="form-control" type="text" id="content" name="content">{{ $announcement->content }}</textarea>
                                @if ($errors->has('content'))
                                    <span class="text-danger">{{ $errors->first('content') }}</span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td><label for="">Due Date</label></td>
                            <td><input class="form-control" type="date" id="duedate" name="duedate"
                                    value="{{ $announcement->duedate }}">
                                @if ($errors->has('duedate'))
                                    <span class="text-danger">{{ $errors->first('duedate') }}</span>
                                @endif
                            </td>
                        </tr>


                        <tr>
                            <td><label>Audience</label></td>
                            <td>
                                <input type="radio" name="type" value="Admin"
                                    {{ $announcement->type == 'Admin' ? 'checked' : '' }}>
                                <label>Admin</label><br>
                                <input type="radio" name="type" value="Reviewers"
                                    {{ $announcement->type == 'Reviewers' ? 'checked' : '' }}>
                                <label>Reviewer</label><br>
                                <input type="radio" name="type" value="LPI"
                                    {{ $announcement->type == 'LPI' ? 'checked' : '' }}>
                                <label>LPI</label><br>
                                <input type="radio" name="type" value="all"
                                    {{ $announcement->type == 'all' ? 'checked' : '' }}>
                                <label>all</label>
                            </td>
                        </tr>


                        <tr>
                            <td>Upload Flyer</td>
                            <td> <input type="file" name="image">
                                <br>
                                @if ($errors->has('image'))
                                    <span class="text-danger">{{ $errors->first('image') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>

                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <button type="submit"
                                    style="background-color:  teal;float:right; border-color: teal; color: white;"
                                    class="btn btn-sm">
                                    {{ __('Update') }}
                                </button>
                            </td>
                        </tr>
                    </table>
                </form>

            </div>
        </div>
    </body>
@endsection
