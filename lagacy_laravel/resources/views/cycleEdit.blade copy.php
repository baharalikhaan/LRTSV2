<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<style>
    img {
        width: 200px;
        height: 300px;
        object-fit: cover;
    }

    .error {
        color: red;
        font-weight: bold;
    }

    .container {
        margin-left: 5%;
    }

    .heading {
        height: 7%;
        width: 80%;
        padding: 0.5%;
        margin-left: 10%;
        color: teal;
        margin-bottom: 3%;
    }

    td {
        padding: 2%;
        font-size: 80%;
    }

    .message {
        border-radius: 5px;
        border: 2px solid teal;
        background-color: #54BAB9;
        height: 7%;
        width: 80%;
        padding: 0.5%;
        margin-left: 10%;
        color: teal;
        font-weight: bold;
        color: beige;
    }
</style>
@include('components.sidebar',['test'=>'1'])
@include('components.navbar')
<div class="container">

    <fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:15px;border-radius:5px;background:#f2f2f2;border:1px solid teal">
        <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5px;margin-left:20px;box-shadow:0 0 0 2px #ddd">Edit & Update About Us Page</legend>
        <form action="{{route('cycleUpdate',$cycle->id)}}" method="POST">
            @csrf
            <tag class="error">
                <?php if ($errors->any()) : ?>
                    <?php echo "Kindly fill all the fields" ?>
                <?php endif; ?>
            </tag>
            <table>
                <colgroup>
                    <col span="1" style="width: 15%;">
                    <col span="1" style="width: 70%;">
                </colgroup>
                <tr style="display:none">
                    <td></td>
                    <td><input type="text" name="id" value="{{$cycle->id}}" class="form-control" style="width: 200px;"></td>
                </tr>
                <tr>
                    <td><label for="">Progress Report Deadline</label></td>
                    <td><input type="date" id="start" name="prog_rpt_deadline" value="cycle->prog_rpt_deadline" min="2018-01-01" max="2099-12-31"></td>
                </tr>
                <tr>
                    <td><label for="">Extended Deadline for Progress Report</label></td>
                    <td><input type="date" id="start" name="extended_prog_rpt_deadline" value="cycle->extended_prog_rpt_deadline" min="2018-01-01" max="2099-12-31"></td>
                </tr>
                <tr>
                    <td><label for="">Final Report Deadline</label></td>
                    <td><input type="date" id="start" name="final_rpt_deadline" value="cycle->final_rpt_deadline" min="2018-01-01" max="2099-12-31"></td>
                </tr>
                <tr>
                    <td><label for="">Extended Deadline for Final Report</label></td>
                    <td><input type="date" id="start" name="extended_final_rpt_deadline" value="cycle->extended_final_rpt_deadline" min="2018-01-01" max="2099-12-31"></td>
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
                <tr>
                <td><label for="">Status</label></td>
                    <td><input type="radio" name="status" value="active" checked=true>
                        <label for="html">Active</label><br>
                        <input type="radio" name="status" value="finish">
                        <label for="css">Finish</label><br>
                    </td>
                </tr>
            </table>
            <div class="form-group mb-3" style="font-size:80%">
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
</div>