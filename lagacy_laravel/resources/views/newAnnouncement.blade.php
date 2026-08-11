 <head>

     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>New Announcement</title>


 </head>

 @extends('layouts.app')
 @section('title', 'Home Page')
 @section('content')


     <body class="body">




         <br>

         <div class="container" id="box-form"
             style="border: 2px solid teal; border-radius: 18px; width:600px ;  padding: 0px;">
             <h5
                 style="background-color: teal; color: white; padding: 10px; border-top-left-radius: 15px; border-top-right-radius: 15px; text-align:center; margin: 0;">
                 New Announcement</h5>
             <div class="container" id="box-form" style="  margin: 10; ">
                 <br>
                 <form action="{{ route('newAnnouncement') }}" method="POST" enctype="multipart/form-data">
                     @csrf

                     <table>

                         <tr style="display:none">
                             <td></td>
                             <td><input class="form-control" type="text" name="id" class="form-control"></td>
                         </tr>
                         <tr>
                             <td><label for="">Subject</label></td>
                             <td><input class="form-control" type="text" id="start" name="subject">
                                 @if ($errors->has('subject'))
                                     <span class="text-danger">{{ $errors->first('subject') }}</span>
                                 @endif
                             </td>
                         </tr>

                         <tr>
                             <td><label for="">Content</label></td>
                             <td>
                                 <textarea class="form-control" type="text" id="start" name="content"></textarea>
                                 @if ($errors->has('content'))
                                     <span class="text-danger">{{ $errors->first('content') }}</span>
                                 @endif
                             </td>
                         </tr>

                         <tr>
                             <td><label for="">Due Date</label></td>
                             <td><input class="form-control" type="date" id="duedate" name="duedate">
                                 @if ($errors->has('duedate'))
                                     <span class="text-danger">{{ $errors->first('duedate') }}</span>
                                 @endif
                             </td>
                         </tr>


                         <tr>
                             <td><label>Audience</label></td>
                             <td><input type="radio" name="type" value="Admin">
                                 <label>Admin</label><br>
                                 <input type="radio" name="type" value="Reviewers">
                                 <label>Reviewer</label><br>
                                 <input type="radio" name="type" value="LPI">
                                 <label>LPI</label><br>
                                 <input type="radio" name="type" value="all" checked>
                                 <label>all</label>
                             </td>
                         </tr>

                         <tr>
                             <td>Upload Flyer</td>
                             <td> <input type="file" name="image"><br>
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
