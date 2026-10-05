 
@include('components.settingBar')
@include('components.navbar')
<br>
<br>
<div id='main' align="center" style="margin: 50;padding-left:50">
 
    <table class="table table-striped" >
        <tr style="color:teal;">
            <th style="display:none;">Id</th>
            <th>Title</th>
            <th>Subject</th>
            <th>Content A</th>
            <th>Content B</th>
            <th>Farewell</th>
            <th>Regards</th>
            <th><i class="fa fa-pencil"></i></th>
        </tr>
        <tbody>
        @foreach($emails as $email)
        <tr>
            <td style="display:none;"> {{$email['id']}} </td>
            <td> {{$email['title']}} </td>
            <td> {{$email['subject']}} </td>
            <td> {{$email['contenta']}} </td>
            <td> {{$email['contentb']}} </td>
            <td> {{$email['farewell']}} </td>
            <td>{{$email['regards']}}</td>
            <form action="{{route('emailEdit',$email['id'])}}">
            <td><button class="button" type="submit">&nbsp;&nbsp;Edit&nbsp;&nbsp;</button></td>
            </form>
            <td></td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

