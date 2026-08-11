<body class="body">
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Project Details</title>
    </head>

    @extends('layouts.app')
    @section('title', 'Home Page')
    @section('content')



        <style>
            .heading {
                position: absolute;
                top: -15;
                left: 35;
                background-color: teal;
                color: white;
                padding: 6px;
                border-radius: 15px 15px 15px 15px;
            }

            .heading2 {
                position: absolute;
                top: -15;
                left: 35;
                background-color: #623C21;
                color: white;
                padding: 6px;
                border-radius: 15px 15px 15px 15px;
            }

            .footer {
                position: absolute;
                bottom: 1;
                right: 55;
                font-size: 11px;
                font-style: italic;
                color: #623C21;

            }


            .btn-teal {
                color: #fff;
                background-color: #008080;
                float: right;
                border-color: #008080;

            }

            .btn-teal:hover {
                color: #fff;
                background-color: #005959;
                border-color: #005959;
            }

            .container {
                display: flex;

            }

            .section {
                flex: 1;
                text-align: center;
                border-right: 1px solid #ccc;
                padding: 20px;
            }

            .section:last-child {
                border-right: none;
            }

            .rating .star {
                color: teal;
            }
        </style>


        <div class="row">



            <div class="col-md-12">

                <div class="row" >
                    <div class="col-md-12" style="margin-top: 10;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                            <div style=" margin: 5px;">

                                <div class="container">
                                    <div class="section">
                                        <b> {{ $user->name }} </b><br>
                                        {{ $user->email }}
                                    </div>
                                    <div class="section">
                                        <b> Total Cycles Entertained </b><br>
                                        {{ sizeof($cycles) }}
                                    </div>
                                    <div class="section">
                                        <b> Total Projects Reviewed </b><br>
                                        {{ $projects->total }}
                                    </div>

                                    <div class="section">
                                        <b> Average Ratings </b> <br>
                                        {{ $avgrating }}
                                    </div>

                                    <div class="section">
                                        <a href="{{ route('reviewerDetail', ['u_id' => $user->id]) }}"
                                            style="background-color:  teal; margin:5;  border-color: teal; float:right; color: white;"
                                            class="btn btn-sm">View Details</a>

                                    </div>
                                </div>
                                <div class="heading">
                                    Reviewers' Information
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

<br>
                <div class="row" >
                    <div class="col-md-12" style="margin-top: 10;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; ">
                            <div style=" margin: 5px;">

                                <div class="container">
                                    <div class="section">
                                        <b> Select Cycle </b><br>
                                        <select class="form-select" id="dropdown">
                                            <option value="0">Select a cycle</option>
                                            @foreach ($cycles as $cycle)
                                                <option value="{{ $cycle->id }}">{{ $cycle->cycle_title }}</option>
                                            @endforeach
                                        </select>
                                    </div>


                                    <div class="section">
                                        <b> Cycle Title </b> <br>
                                        <span id='cycletitle'></span>
                                    </div>

                                    <div class="section">
                                        <b> Total Projects </b> <br>
                                        <span id='cycleprojects'></span>
                                    </div>


                                </div>




                                <div class="row">

                                    <div class="col-md-10">
                                        <div class="row" style="margin-top: 5; margin-left:5">
                                            <div class="col-md-12" style="margin-top: 10;">
                                                <div
                                                    style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px;  ">
                                                    <div style=" margin: 20px; margin-top: 30px;">

                                                        <div id='divprojects'>

                                                        </div>

                                                        <div class="heading">
                                                            Projects' Details
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2">

                                        <div class="row" style="margin-top: 5; margin-right:0">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-12" style="margin-top: 10;">
                                                        <div
                                                            style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                                                            <div style=" margin: 13px; margin-top:25">



                                                                <!-- Your Blade template file -->

                                                                <form method="POST" action="{{ route('saveratings') }}">
                                                                    @csrf
                                                                    <!-- Hidden fields for reviewer and user_id -->
                                                                    <input type="hidden" name="reviewer"
                                                                        value="{{ $user->id }}">
                                                                    <input type="hidden" name="user_id"
                                                                        value="{{ auth()->user()->id }}">
                                                                    <input type="hidden" id="cycle_id" name="cycle_id"
                                                                        value="">

                                                                    <!-- Star rating for 'conflict' -->
                                                                    <label for="conflict">Conflict:</label>
                                                                    <input id="conflict" name="conflict" type="number"
                                                                        class="rating" data-min="0" data-max="10"
                                                                        data-step="2" data-size="sm">
                                                                    <br> <br>

                                                                    <!-- Star rating for 'responsiveness' -->
                                                                    <label for="responsiveness">Responsiveness:</label>
                                                                    <input id="responsiveness" name="responsiveness"
                                                                        type="number" class="rating" data-min="0"
                                                                        data-max="10" data-step="2" data-size="sm">
                                                                    <br> <br>
                                                                    <!-- Star rating for 'comprehensiveness' -->
                                                                    <label
                                                                        for="comprehensiveness">Comprehensiveness:</label>
                                                                    <input id="comprehensiveness" name="comprehensiveness"
                                                                        type="number" class="rating" data-min="0"
                                                                        data-max="10" data-step="2" data-size="sm">
                                                                    <br> <br>
                                                                    <!-- Star rating for 'no_reviewes' -->
                                                                    <label for="no_reviewes">No. of Reviews:</label>
                                                                    <input id="no_reviewers" name="no_reviewers"
                                                                        type="text" class="rating" data-min="0"
                                                                        data-max="10" data-step="2" data-size="sm">
                                                                    <br> <br>
                                                                    <!-- Star rating for 'behaviour' -->
                                                                    <label for="behaviour">Behaviour:</label>
                                                                    <input id="behaviour" name="behaviour" type="number"
                                                                        class="rating" data-min="0" data-max="10"
                                                                        data-step="2" data-size="sm">
                                                                    <br>
                                                                    <br>
                                                                    <span id="ratingmsg"></span><br>
                                                                    <span id="ratingmsg2"></span>
                                                                    <span id="ratingmsg3">
                                                                        @if (session('successrating'))
                                                                            {!! session('successrating') !!}
                                                                            @php
                                                                                session()->forget('successrating');
                                                                            @endphp
                                                                        @endif
                                                                    </span>
                                                                    <button id="submit"
                                                                        style="background-color:  teal; margin:10;  border-color: teal; float:right; color: rgb(228, 124, 98);"
                                                                        class="btn">Save</button>
                                                                </form>
                                                                <br>

                                                                <div class="heading">
                                                                    Rate Cycle
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>



                                    </div>


                                </div>



                                <div class="heading">
                                    Cycles' Ratings Information
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>



        <script>
            $(document).ready(function() {
                $('.rating').rating();

                $('#dropdown').change(function() {

                    var selectedCycleId = $(this).val();
                    $('#cycle_id').val($(this).val());

                    var userId = '{{ $user->id }}';
                    $.ajax({
                        url: '{{ route('ajaxListreviewerGrading') }}',
                        type: 'GET',
                        data: {
                            cycle_id: selectedCycleId,
                            user_id: userId
                        },
                        success: function(response) {
                            var aa = '';
                            var count = 0;
                            $.each(response['projects'], function(key, value) {


                                    console.log(value);

                                    count++;
                                    if (value.gradeA == null) {

                                        var date = new Date(value.deadline);
                                        date.setTime(date.getTime() + (2 * 7 * 24 * 60 * 60 *
                                            1000));
                                        var newDatetimeString = date.toISOString().slice(0, 19)
                                            .replace('T', ' ');

                                        aa += `
                              <table class="table table-bordered table-sm" style="font-size:13;background-color: #facfd6; border-color: black" >
                              <tr>
                                <th style="width:250px; text-align:center" rowspan="4">` + value.old_project_id + `</th>
                                <th style="width:250px;">Review Invitation Status</th>
                                <td>` + value.proposalstatus + `</td>
                                </tr>

                                <tr>
                                <th style="width:100px;">Feedback Deadline on Invitation</th>
                                <td>` + newDatetimeString + `</td>
                                </tr>

                                <tr>
                                <th style="width:100px;">Review Status</th>
                                <td>Not yet reviewed</td>
                                </tr>

                                      `;
                                    } else {


                                        aa += ` <table class="table table-bordered table-sm" style="font-size:13; border-color: black">
                              <tr>
                                <th style="width:100px; text-align:center" rowspan="4">` + value.old_project_id + `</th>
                                <th style="width:100px;" rowspan="2">Progress Report Grading</th>

                                <th colspan="2" style="background:#E9F6F6; ">Achievements</th>


                                 <th colspan="2" style="background:#E9F6F6; ">Publications</th>


                                  <th  colspan="2" style="background:#E9F6F6; ">Students Involment</th>


                                  <th  colspan="2" style="background:#E9F6F6; ">Budget Utilization</th>


                              </tr>

                              <tr>
                                <td  >` + value.achievementsRating + `</td>
                                <td  >` + value.achievementsComments + `</td>
                                <td >` + value.publicationsRating + `</td>
                                <td >` + value.publicationsComments + `</td>
                                <td >` + value.studentsRating + `</td>
                                <td >` + value.studentsComments + `</td>
                                <td >` + value.budgetRating + `</td>
                                <td >` + value.budgetComments + `</td>

                              </tr>

                              <tr>
                                <th rowspan="2">Final Report Grading</th>
                                <th colspan="2" style="background:#E9F6F6; width:65">Achievement</th>

                                <th colspan="2" style="background:#E9F6F6; width:65">Publications</th>

                                <th colspan="2" style="background:#E9F6F6; width:65">Student Involvement</th>

                                <th colspan="2" style="background:#E9F6F6; width:65">Project Impact</th>


                              </tr>

                              <tr>
                                <td>` + value.gradeA + `</td>
                                <td>
                                  ` + value.commentA + `
                                </td>
                                <td>   ` + value.gradeB + `</td>
                                <td>
                             ` + value.commentB + `
                                </td>
                                <td>` + value.gradeD + `</td>
                                <td>
                                  ` + value.commentD + `
                                </td>
                                <td>` + value.gradeC + `</td>
                                <td>
                             ` + value.commentC + `
                                </td>
                              </tr>

                              </table>

                              `;
                                    }


                                }

                            );

                            $('#divprojects').html(aa);
                            $('#cycleprojects').html(count);
                            $('#cycletitle').html($('#dropdown').find('option:selected').text());



                            if (response['ratings']) {
                                $('#conflict').rating('update', response['ratings'].conflict);
                                $('#responsiveness').rating('update', response['ratings']
                                    .responsiveness);
                                $('#comprehensiveness').rating('update', response['ratings']
                                    .comprehensiveness);
                                $('#no_reviewers').rating('update', response['ratings']
                                    .no_reviewers);
                                $('#behaviour').rating('update', response['ratings'].behaviour);
                                $('#ratingmsg').html('Rating has already been set');
                                var avg = (response['ratings'].conflict + response['ratings']
                                    .responsiveness + response['ratings'].comprehensiveness +
                                    response['ratings'].no_reviewers + response['ratings']
                                    .behaviour) / 5;
                                $('#ratingmsg2').html('Average Rating: <b>' + avg + '</b>');
                                $('#ratingmsg3').html('');

                                $('#cyclerating').html(avg);

                            } else {
                                $('#conflict').rating('update', 0);
                                $('#responsiveness').rating('update', 0);
                                $('#comprehensiveness').rating('update', 0);
                                $('#no_reviewers').rating('update', 0);
                                $('#behaviour').rating('update', 0);
                                $('#ratingmsg').html('');
                                $('#ratingmsg2').html('');
                                $('#ratingmsg3').html('');
                            }






                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                });
            });
        </script>
    @endsection
