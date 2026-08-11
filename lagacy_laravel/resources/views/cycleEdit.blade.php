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

    <div class="container" id="box-form" style="border: 2px solid teal; border-radius: 18px; width:500px ;  padding: 0px;">
        <h5 style="background-color: teal; color: white; padding: 10px; border-top-left-radius: 15px; border-top-right-radius: 15px; text-align:center; margin: 0;">Update Cycle</h5>
        <div class="container" id="box-form" style="  margin: 10; ">

            <form action="{{route('cycleUpdate',$cycle->id)}}" method="POST">
                @csrf
                <tag class="error">
                    <?php if ($errors->any()) : ?>
                        <?php echo "Kindly fill all the fields" ?>
                    <?php endif; ?>
                </tag>
                <table>

                    <tr style="display:none">
                        <td></td>
                        <td><input class="form-control" type="text" name="id" value="{{$cycle->id}}" class="form-control" style="width: 200px;"></td>
                    </tr>
                    <tr>
                        <td><label for="">Progress Report Deadline</label></td>
                        <td><input class="form-control" type="date" id="start" name="prog_rpt_deadline" value="{{$cycle->prog_rpt_deadline}}" min="2018-01-01" max="2099-12-31"></td>
                    </tr>
                    <tr>
                        <td><label for="">Extended Deadline for Progress Report</label></td>
                        <td><input class="form-control" type="date" id="start" name="extended_prog_rpt_deadline" value="{{$cycle->extended_prog_rpt_deadline}}" min="2018-01-01" max="2099-12-31"></td>
                    </tr>
                    <tr>
                        <td><label for="">Progress Report 2 Deadline</label></td>
                        <td><input class="form-control" type="date" id="start" name="prog2_rpt_deadline" value="{{$cycle->prog2_rpt_deadline}}" min="2018-01-01" max="2099-12-31"></td>
                    </tr>
                    <tr>
                        <td><label for="">Extended Deadline for Progress Report 2</label></td>
                        <td><input class="form-control" type="date" id="start" name="extended_prog2_rpt_deadline" value="{{$cycle->extended_prog2_rpt_deadline}}" min="2018-01-01" max="2099-12-31"></td>
                    </tr>
                    <tr>
                        <td><label for="">Final Report Deadline</label></td>
                        <td><input class="form-control" type="date" id="start" name="final_rpt_deadline" value="{{$cycle->final_rpt_deadline}}" min="2018-01-01" max="2099-12-31"></td>
                    </tr>
                    <tr>
                        <td><label for="">Extended Deadline for Final Report</label></td>
                        <td><input class="form-control" class="form-control" type="date" id="start" name="extended_final_rpt_deadline" value="{{$cycle->extended_final_rpt_deadline}}" min="2018-01-01" max="2099-12-31"></td>
                    </tr>
                    <tr>
                        <td><label for="">Upload Outcomes</label></td>
                        <td><input type="radio" name="upload_outcomes" value="inactive">
                            <label for="inactive">Inactive</label><br>
                            <input type="radio" name="upload_outcomes" value="active" checked=true>
                            <label for="active">Active</label><br>
                            <input type="radio" name="upload_outcomes" value="finish">
                            <label for="finish">Finish</label><br>
                        </td>
                    </tr>
                    <br>
                    <tr>
                        <td><label for="">Status</label></td>
                        <td><input type="radio" name="status" value="active" checked=true>
                            <label for="html">Active</label><br>
                            <input type="radio" name="status" value="finish">
                            <label for="css">Finish</label><br>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>

                    </tr>
                    <tr>
                        <td></td>
                        <td></td>

                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <button type="submit" style="background-color:  teal;float:right; border-color: teal; color: white;" class="btn btn-sm">
                                {{ __('Update') }}
                            </button>
                        </td>

                    </tr>


                </table>




            </form>

        </div>
    </div>
</body>
