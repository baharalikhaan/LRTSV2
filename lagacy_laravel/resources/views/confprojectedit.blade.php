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

    input[type="radio"] {
        background-color: white;
        border-color: teal;
    }

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
        style="border: 2px solid teal; border-radius: 18px; width:800px ;  padding: 0px;">
        <h5
            style="background-color: teal; color: white; padding: 10px; border-top-left-radius: 15px; border-top-right-radius: 15px; text-align:center; margin: 0;">
            Update Conf-Tool Project</h5>
        <div class="container" id="box-form" style="  margin-top: 20; ">

            <form action="{{ route('confprojectupdate', $project->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <tag class="error">
                    <?php if ($errors->any()) : ?>
                    <?php echo 'Kindly fill all the fields'; ?>
                    <?php endif; ?>
                </tag>
                <table class="table-display" style="width:100%">

                    <input type="hidden" name="id" value="{{ $project->id }}">
                    <input type="hidden" name="cycle" value="{{ $project->cycle }}">

                    <tr style="display:none">
                        <td>Project Id</td>
                        <td><input class="form-control" type="text" name="old_project_id"
                                value="{{ $project->old_project_id }}" class="form-control"></td>
                    </tr>
                    <tr>
                        <td><label for="">Project Title</label></td>
                        <td><input class="form-control" type="text" id="title" name="title"
                                value="{{ $project->title }}"></td>
                    </tr>
                    <tr>
                        <td><br></td>
                        <td></td>
                    </tr>

                    <tr>
                        <td><label for="">Author Email</label></td>
                        <td>
                            <textarea class="form-control" type="text" id="author" name="author">{{ $project->author }}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td><br></td>
                        <td></td>
                    </tr>

                    <tr>
                        <td>project Proposal</td>
                        <td> <input type="file" name="proposal"></td>
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
