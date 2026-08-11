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
</style>

<body class="body">



    <br>
    <br>

    <div class="container" id="box-form"
        style="border: 2px solid teal; border-radius: 18px; width:100% ; padding:0px;  padding-bottom: 50px;">

        <h5
            style="background-color: teal; color: white; padding: 10px; border-top-left-radius: 15px; border-top-right-radius: 15px; text-align:center; margin: 0;">
            Update Email Template</h5>

        <div class="container" id="box-form" style="  margin: 0; ">





            @if (session('emailtemplatesuccess'))
                <div class="alert alert-success" role="alert">
                    {{ session('emailtemplatesuccess') }}
                </div>
            @endif
            @if (isset($message))
                <div class="message" align="center">
                    {{ $message }}
                </div>
            @endif



            <br>
            <br>


            <form action="{{ route('emailUpdate', $email->id) }}" method="POST">
                @csrf
                <tag class="error">
                    <?php if ($errors->any()) : ?>
                    <?php echo 'Kindly fill all the fields'; ?>
                    <?php endif; ?>
                </tag>
                <table>
                    <colgroup>
                        <col span="1" style="width: 15%;">
                        <col span="1" style="width: 70%;">
                    </colgroup>
                    <tr style="display:none">
                        <td></td>
                        <td><input type="text" name="id" value="{{ $email->id }}" class="form-control"
                                style="width: 500px;"></td>
                    </tr>
                    <tr>
                        <td><label>Subject</label></td>
                        <td><input type="text" name="subject" value="{{ $email->subject }}" class="form-control">
                        </td>
                    </tr>
                    <tr>
                        <td><label for="">Contents</label></td>
                        <td>
                            <textarea name="contents" class="form-control" rows="20" cols="100">{{ $email->contents }}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="">Signature</label></td>
                        <td>
                            <textarea name="signature" class="form-control" rows="20" cols="50">{{ $email->signature }}</textarea>
                        </td>
                    </tr>

                </table>
                <br>
                <div class="container">
                    <button type="submit"
                        style="float:right; background-color: teal; border-color: teal; color: white;"
                        class="btn btn-primary">
                        {{ __('submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
