<style>
    #div {
        border: 2px solid teal;
        padding: 5%;
        margin-left: 5%;
        margin-right: 5%;
    }

    #btn {
        display: block;
        width: 8%;
        height: 5%;
        border: 2px solid teal;
        border-radius: 10%;
    }

    .h4 {
        font-weight: bold;
        color: teal;
        padding-left: 5%;
    }

    .error {
        font-weight: bold;
        color: red;
    }
</style>
@include('components.sidebar')
@include('components.navbar')
<h4 class='h4' align="left">Title: {{$project->title}} </h4>
<br>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<?php if ($errors->any()) : ?>
    <div class="error">
        <?php foreach ($errors->all() as $error) : ?>
            <?php echo $error ?>
        <?php endforeach; ?>
    </div><br>
<?php endif; ?>

<body>

    <fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:1%;border-radius:3%;background:#f2f2f2;border:1px solid teal">
        <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5%;margin-left:20px;box-shadow:0 0 0 2px #ddd">Project Files</legend>
        <fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:2%;border-radius:5%;background:#f2f2f2;border:1px solid teal;">
            <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5%;margin-left:20px;box-shadow:0 0 0 2px #ddd">Project Proposal</legend>
            <div>
                @if($progress_report)
                <b style="font-size:10;font-weight:bold;"><u>The Proposal Deadline is @if($cycle->prog_rpt_deadline){{$cycle->prog_rpt_deadline}}@else TBD @endif</u><br></b>
                <b style="font-size:10;font-weight:bold;"><u>The Extended deadline for Proposal is @if($cycle->extended_prog_rpt_deadline){{$cycle->extended_prog_rpt_deadline}} @else TBD @endif</u><br></b>
                <b style="font-size:10;font-weight:bold;color:green"> You have already uploaded proposal.</b><br>
                <b style="font-size:14;font-weight:bold;">Do you want to upload updated Proposal?
                </b>
                <form action="{{ route('reportUpload',['p_id'=>$p_id])}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="text" name='report_type' value="proposal" hidden>
                    <input type="text" name='title' value="{{$project->title}}" hidden><br>
                    <label>Update Report <input type="file" name="file" style="font-size:11"></label><br><br>
                    <button type="submit" class="btn btn-primary" id="btn" style="width:15%">
                        {{ __('Submit') }}
                    </button>
                </form>
                @else
                <b style="font-size:14;font-weight:bold;">Kindly upload the Proposal of your Project here:
                </b>
                <form action="{{ route('reportUpload',['p_id'=>$p_id])}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="text" name='report_type' value="proposal" hidden>
                    <input type="text" name='title' value="{{$project->title}}" hidden><br>
                    <label>Attach <input type="file" name="file" style="font-size:11"></label><br><br>
                    <button type="submit" class="btn btn-primary" id="btn" style="width:15%">
                        {{ __('Submit') }}
                    </button>
                </form>
                @endif

                @if(session('successproposal'))
                {!! session('successproposal') !!}
                @php
                session()->forget('successproposal');
                @endphp
                @endif
            </div>
        </fieldset>
        <br>



        <fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:2%;border-radius:5%;background:#f2f2f2;border:1px solid teal;">
                <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5%;margin-left:20px;box-shadow:0 0 0 2px #ddd">Project Progress Report</legend>
                <div>
                    @if($progress_report)
                    <b style="font-size:10;font-weight:bold;"><u>The Progress Report Deadline is @if($cycle->prog_rpt_deadline){{$cycle->prog_rpt_deadline}}@else TBD @endif</u><br></b>
                    <b style="font-size:10;font-weight:bold;"><u>The Extended deadline for Progress Report is @if($cycle->extended_prog_rpt_deadline){{$cycle->extended_prog_rpt_deadline}} @else TBD @endif</u><br></b>
                    <b style="font-size:10;font-weight:bold;color:green"> You have already uploaded this report.</b><br>
                    <b style="font-size:14;font-weight:bold;">Do you want to upload updated Progress Report?
                    </b>
                    <form action="{{ route('reportUpload',['p_id'=>$p_id])}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="text" name='report_type' value="progress" hidden>
                        <input type="text" name='title' value="{{$project->title}}" hidden><br>
                        <label>Update Report <input type="file" name="file" style="font-size:11"></label><br><br>
                        <button type="submit" class="btn btn-primary" id="btn" style="width:15%">
                            {{ __('Submit') }}
                        </button>
                    </form>
                    @else
                    <b style="font-size:14;font-weight:bold;">Kindly upload the Progress Report of your Project here:
                    </b>
                    <form action="{{ route('reportUpload',['p_id'=>$p_id])}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="text" name='report_type' value="progress" hidden>
                        <input type="text" name='title' value="{{$project->title}}" hidden><br>
                        <label>Attach <input type="file" name="file" style="font-size:11"></label><br><br>
                        <button type="submit" class="btn btn-primary" id="btn" style="width:15%">
                            {{ __('Submit') }}
                        </button>
                    </form>
                    @endif

                    @if(session('successreport'))
                    {!! session('successreport') !!}
                    @php
                    session()->forget('successreport');
                    @endphp
                    @endif
                </div>
            </fieldset>
            <br>
            <fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:2%;border-radius:5%;background:#f2f2f2;border:1px solid teal;">
                <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5%;margin-left:20px;box-shadow:0 0 0 2px #ddd">Final Report</legend>
                <div>
                    <b style="font-size:10;font-weight:bold;"><u>The Final Report Deadline is @if($cycle->final_rpt_deadline){{$cycle->final_rpt_deadline}}@else TBD @endif</u><br></b>
                    <b style="font-size:10;font-weight:bold;"><u>The Extended deadline for Final Report is @if($cycle->extended_final_rpt_deadline){{$cycle->extended_final_rpt_deadline}} @else TBD @endif</u><br></b>
                    @if($final_report)
                    <b style="font-size:10;font-weight:bold;color:green"> You have already uploaded final report.</b><br>
                    <b style="font-size:14;font-weight:bold;">Do you want to upload an updated version of Final Report:</b><br>
                    <form action="{{ route('reportUpload',['p_id'=>$p_id])}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="text" name='report_type' value="final" hidden>
                        <input type="text" name='title' value="{{$project->title}}" hidden><br>
                        <label>Update File <input type="file" name="file" style="font-size:11"></label><br><br>
                        <button type="submit" id="btn" class="btn btn-primary" style="width:15%">
                            {{ __('Submit') }}
                        </button>
                    </form>
                    @else
                    <b style="font-size:14;font-weight:bold;">Kindly upload the Final Report of your Project here:</b><br>
                    <form action="{{ route('reportUpload',['p_id'=>$p_id])}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="text" name='report_type' value="final" hidden>
                        <input type="text" name='title' value="{{$project->title}}" hidden><br>
                        <label>Attach <input type="file" name="file" style="font-size:11"></label><br><br>
                        <button type="submit" id="btn" class="btn btn-primary" style="width:15%">
                            {{ __('Submit') }}
                        </button>
                    </form>
                    @endif

                    @if(session('successreportfinal'))
                    {!! session('successreportfinal') !!}
                    @php
                    session()->forget('successreportfinal');
                    @endphp
                    @endif


                </div>
            </fieldset>


            <br>
            <fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:2%;border-radius:5%;background:#f2f2f2;border:1px solid teal;">
                <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5%;margin-left:20px;box-shadow:0 0 0 2px #ddd">Qu readiness mapping</legend>
                <div>
                    <b style="font-size:10;font-weight:bold;"><u>The Qu readiness mapping Deadline is @if($cycle->final_rpt_deadline){{$cycle->final_rpt_deadline}}@else TBD @endif</u><br></b>
                    <b style="font-size:10;font-weight:bold;"><u>The Extended deadline for Final Report is @if($cycle->extended_final_rpt_deadline){{$cycle->extended_final_rpt_deadline}} @else TBD @endif</u><br></b>
                    @if($final_report)
                    <b style="font-size:10;font-weight:bold;color:green"> You have already uploaded final report.</b><br>
                    <b style="font-size:14;font-weight:bold;">Do you want to upload an updated version of Final Report:</b><br>
                    <form action="{{ route('reportUpload',['p_id'=>$p_id])}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="text" name='report_type' value="readiness" hidden>
                        <input type="text" name='title' value="{{$project->title}}" hidden><br>
                        <label>Update File <input type="file" name="file" style="font-size:11"></label><br><br>
                        <button type="submit" id="btn" class="btn btn-primary" style="width:15%">
                            {{ __('Submit') }}
                        </button>
                    </form>
                    @else
                    <b style="font-size:14;font-weight:bold;">Kindly upload the Qu readiness mapping of your Project here:</b><br>
                    <form action="{{ route('reportUpload',['p_id'=>$p_id])}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="text" name='report_type' value="readiness" hidden>
                        <input type="text" name='title' value="{{$project->title}}" hidden><br>
                        <label>Attach <input type="file" name="file" style="font-size:11"></label><br><br>
                        <button type="submit" id="btn" class="btn btn-primary" style="width:15%">
                            {{ __('Submit') }}
                        </button>
                    </form>
                    @endif

                    @if(session('successreadiness'))
                    {!! session('successreadiness') !!}
                    @php
                    session()->forget('successreadiness');
                    @endphp
                    @endif


                </div>
            </fieldset>




            <br>
            <fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:2%;border-radius:5%;background:#f2f2f2;border:1px solid teal;">
                <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5%;margin-left:20px;box-shadow:0 0 0 2px #ddd">Outcomes of Project</legend>
                <div>
                    @if($project->upload_outcomes=='active')
                    <b style="font-size:14;font-weight:bold;">Kindly fill out the detailed "Outcome-Form" of your project:</b>
                    <input type="text" name='report_type' value="final" hidden><br>
                    <form action="{{ route('projectOutcomes') }}">
                        <input type="text" name='p_id' value="{{$p_id}}" hidden><br>
                        <button id="btn" class="btn btn-primary" style="width:15%">
                            {{ __('Open Form') }}
                        </button>
                    </form>

                    @if(session('successoutcome'))
                    {!! session('successoutcome') !!}
                    @php
                    session()->forget('successoutcome');
                    @endphp
                    @endif

                    <br>
                    <b style="font-size:14;font-weight:bold;">For Social Sciences and Humanities, fill this form:</b>
                    <input type="text" name='report_type' value="final" hidden><br>
                    <form action="{{ route('uHass') }}">
                        <input type="text" name='p_id' value="{{$p_id}}" hidden><br>
                        <button id="btn" class="btn btn-primary" style="width:15%">
                            {{ __('Open Form') }}
                        </button>
                    </form>
                    @if(session('successoutcomehumanities'))
                    {!! session('successoutcomehumanities') !!}
                    @php
                    session()->forget('successoutcomehumanities');
                    @endphp
                    @endif
                    <br>
                    <form action="{{ route('uploadedOutcomes') }}">
                        <input type="text" name='p_id' value="{{$p_id}}" hidden><br>
                        @if($outcomes)
                        <button id="btn" class="btn btn-primary" style="width:25%">
                            List of already filed outcomes
                        </button>
                        @endif
                    </form>
                    @else
                    <b style="font-size:10;font-weight:bold;color:green"> This form is closed for time being.</b>
                    @endif
                </div>
            </fieldset>
</body>

</html>