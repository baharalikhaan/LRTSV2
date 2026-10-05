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
@include('components.sidebar')
@include('components.navbar')
<div class="container">

   <fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:15px;border-radius:5px;background:#f2f2f2;border:1px solid teal">
    <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5px;margin-left:20px;box-shadow:0 0 0 2px #ddd">Edit & Update Emails</legend>
                    <form action="{{route('emailUpdate',$email->id)}}" method="POST">
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
                                <td><input type="text" name="id" value="{{$email->id}}" class="form-control" style="width: 200px;"></td>
                            </tr>
                            <tr>
                                <td><label>Subject</label></td>
                                <td><input type="text" name="subject" value="{{$email->subject}}" class="form-control"></td>
                            </tr>
                            <tr>
                                <td><label for="">Title</label></td>
                                <td><input type="text" name="title" value="{{$email->title}}" class="form-control"></td>
                            </tr>
                            <tr>
                                <td><label for="">Content A</label></td>
                                <td><textarea name="contenta" class="form-control" rows="4" cols="50">{{$email->contenta}}</textarea></td>
                            </tr>
                            <tr>
                                <td><label for="">Content B</label></td>
                                <td><textarea name="contentb" class="form-control" rows="4" cols="50">{{$email->contentb}}</textarea></td>
                            </tr>
                            <tr>
                                <td><label for="">Farewell</label></td>
                                <td><textarea name="farewell" class="form-control" rows="4" cols="50">{{$email->farewell}}</textarea></td>
                            </tr>
                            <tr>
                                <td><label for="">Regards</label></td>
                                <td><textarea name="regards" class="form-control" rows="4" cols="50">{{$email->regards}}</textarea></td>
                            </tr>
                        </table>
                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">Update Email</button>
                        </div>
                    </form>
</div>
