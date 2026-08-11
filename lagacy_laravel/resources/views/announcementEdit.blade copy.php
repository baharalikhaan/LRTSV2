<style>
    .error{
        color:red;
        font-weight: bold;
    }
    .container{
        margin-left: 5%;
    }
    .heading {
     height: 7%;
     width: 80%;
     padding: 0.5%;
     margin-left: 10%;
     color: teal;
     margin-bottom:3%;
   }
   td{
    padding:2%;
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
    <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5px;margin-left:20px;box-shadow:0 0 0 2px #ddd">Edit & Update announcement</legend>
                    <form action="{{route('announcementUpdate',$announcement->id)}}" method="POST">
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
                                <td><input type="text" name="id" value="{{$announcement->id}}" class="form-control" style="width: 200px;"></td>
                            </tr>
                            <tr>
                                <td><label>Subject</label></td>
                                <td><input type="text" name="subject" value="{{$announcement->subject}}" class="form-control"></td>
                            </tr>
                            
                            <tr>
                                <td><label for="">Content</label></td>
                                <td><textarea name="content" class="form-control" rows="4" cols="50">{{$announcement->content}}</textarea></td>
                            </tr>
                            <tr>
                                <td><label>Audience</label></td>
                                <td><input type="radio" name="type" value="Admin">
                                <label>Admin</label><br>
                                <input type="radio" name="type" value="Reviewer">
                                <label>Reviewer</label><br>
                                <input type="radio" name="type" value="LPI">
                                <label>LPI</label><br>
                                <input type="radio" name="type" value="all" checked>
                                <label>all</label>
                                </td>
                            </tr>
                            <tr>
                                <td><label>Visbility</label></td>
                                <td><input type="radio" name="status" value="visible"  checked>
                                <label>Visible</label><br>
                                <input type="radio" name="type" value="Hide">
                                <label>Hide</label><br>
                                </td>
                            </tr>
                        </table>
                        <div class="form-group mb-3" style="font-size:80%">
                            <button type="submit" class="btn btn-primary">Update Announcement</button>
                        </div>
                    </form>
</div>
