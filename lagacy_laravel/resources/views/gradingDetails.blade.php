<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body data-new-gr-c-s-check-loaded="14.1155.0" data-gr-ext-installed="">
    <div class="tm_container  ">
        <div class="tm_invoice_wrap" style="border: 1px solid teal; margin-right: 5px; margin-left:5px;">
            <div class="tm_invoice tm_style1 tm_type1" id="tm_download_section">
                <div class="tm_invoice_in">
                    <div class="tm_invoice_head tm_top_head tm_mb15 tm_align_center">
                        <div class="tm_invoice_left">
                            <div class="tm_logo"><img src="{{ asset('images/research_logo.png') }}" alt="Logo">
                            </div>
                        </div>
                        <div class="tm_invoice_right tm_text_right tm_mobile_hide">
                            <div class="tm_f50 tm_text_uppercase tm_white_color">REPORT CARD </div>
                            <div class="tm_f50 tm_text_uppercase tm_white_color"
                                style="font-size:15px; margin-bottom:25; margin-top:5">Detailed Project Evaluation
                            </div>
                        </div>
                        <div class="tm_shape_bg tm_accent_bg tm_mobile_hide"></div>
                    </div>
                    <div>
                        <table class="table table-bordered" style="background:#e3e3e6 ">
                            <tbody>
                                <tr>
                                    <th style="width:150px">Project ID</th>
                                    <td>{{ $project->old_project_id }}</td>
                                </tr>
                                <tr>
                                    <th>Project Title</th>
                                    <td>{{ $project->title }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <h5 style="color:teal;">Progress Report 1 Remarks</h5>
                        @foreach ($progressGrades as $key => $pg)
                            <table class="table table-sm ">
                                <tbody>
                                    <tr class="tm_gray_bg ">
                                        <th class="tm_width_1" rowspan="6"
                                        style="width:50px;
                                               transform: rotate(-90deg);
                                               transform-origin: center;
                                               white-space: nowrap;
                                               min-height: 100px; /* Set a minimum height for the cell */
                                               padding-top: 10px; /* Add some padding as needed */
                                               vertical-align: middle;">
                                            Reviewer-{{ $key + 1 }}</th>
                                        <th style="width:50px">#</th>
                                        <th style="width:250px">Criteria</th>
                                        <th style="width:100px">Rating</th>
                                        <th>Comment</th>
                                    </tr>
                                    <tr>
                                        <td>1</td>
                                        <td>Progress Toward Achieving Outcomes</td>
                                        <td>{{ $pg['rt1'] }}</td>
                                        <td>{{ $pg['achievementsComments'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Progress in Publications</td>
                                        <td>{{ $pg['rt2'] }}</td>
                                        <td>{{ $pg['publicationsComments'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Engagement in Student Involvement and Capacity Building</td>
                                        <td>{{ $pg['rt3'] }}</td>
                                        <td>{{ $pg['studentsComments'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td>Budget Utilization</td>
                                        <td>{{ $pg['rt4'] }}</td>
                                        <td>{{ $pg['budgetComments'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>5</td>
                                        <td>Recommendation for Continuation</td>
                                        <td colspan="2" style="color: {{ $pg['isAccepted'] == '0' ? 'red' : 'green' }};">
                                            {{ $pg['isAccepted'] == '0' ? 'Rejected' : 'Accepted' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        @endforeach

                        @if ($project->has_progress_report2)
                        <br>
                        <h5 style="color:teal;">Progress Report 2 Remarks</h5>
                        @foreach ($progressGrades2 as $key => $pg2)
                            <table class="table table-sm ">
                                <tbody>
                                    <tr class="tm_gray_bg ">
                                        <th class="tm_width_1" rowspan="6"
                                        style="width:50px;
                                               transform: rotate(-90deg);
                                               transform-origin: center;
                                               white-space: nowrap;
                                               min-height: 100px; /* Set a minimum height for the cell */
                                               padding-top: 10px; /* Add some padding as needed */
                                               vertical-align: middle;">
                                            Reviewer-{{ $key + 1 }}</th>
                                        <th style="width:50px">#</th>
                                        <th style="width:250px">Criteria</th>
                                        <th style="width:100px">Rating</th>
                                        <th>Comment</th>
                                    </tr>
                                    <tr>
                                        <td>1</td>
                                        <td>Progress Toward Achieving Outcomes</td>
                                        <td>{{ $pg2['rt1'] }}</td>
                                        <td>{{ $pg2['achievementsComments'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Progress in Publications</td>
                                        <td>{{ $pg2['rt2'] }}</td>
                                        <td>{{ $pg2['publicationsComments'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Engagement in Student Involvement and Capacity Building</td>
                                        <td>{{ $pg2['rt3'] }}</td>
                                        <td>{{ $pg2['studentsComments'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td>Budget Utilization</td>
                                        <td>{{ $pg2['rt4'] }}</td>
                                        <td>{{ $pg2['budgetComments'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>5</td>
                                        <td>Recommendation for Continuation</td>
                                        <td colspan="2" style="color: {{ $pg2['isAccepted'] == '0' ? 'red' : 'green' }};">
                                            {{ $pg2['isAccepted'] == '0' ? 'Rejected' : 'Accepted' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                         @endforeach
                         @endif

                        <br>
                        <h5 style="color:teal;">Final Report Evaluation</h5>

                        @foreach ($finalGrades as $key => $fg)
                            <table class="table table-sm">
                                <tbody>
                                    <tr class="tm_gray_bg ">
                                        <th class="tm_width_1" rowspan="6"
                                        style="width:50px;
                                               transform: rotate(-90deg);
                                               transform-origin: center;
                                               white-space: nowrap;
                                               min-height: 100px; /* Set a minimum height for the cell */
                                               padding-top: 10px; /* Add some padding as needed */
                                               vertical-align: middle;">
                                            Reviewer-{{ $key + 1 }}</th>
                                        <th style="width:50px">#</th>
                                        <th style="width:200px">Criteria</th>
                                        <th style="width:50px">Score</th>
                                        <th>Comment</th>
                                    </tr>
                                    <tr>
                                        <td>1</td>
                                        <td>Results and Outcomes</td>
                                        <td>{{ $fg['gradeA'] }}</td>
                                        <td>{{ $fg['commentA'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Publications</td>
                                        <td>{{ $fg['gradeB'] }}</td>
                                        <td>{{ $fg['commentB'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Young Researcher Supervision</td>
                                        <td>{{ $fg['gradeD'] }}</td>
                                        <td>{{ $fg['commentD'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td>Project:</td>
                                        <td>{{ $fg['gradeC'] }}</td>
                                        <td>{{ $fg['commentC'] }}</td>
                                    </tr>



                                </tbody>
                            </table>
                        @endforeach
                    </div>
                    <br>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-4">
                                <div id="qrcode"></div>
                            </div>

                            <div class="col-sm-4">
                                @if (!empty($pg2))
                                    @if ($pg2['isAccepted'] == '0')
                                        <img src="{{ asset('images/rejected.png') }}" style="width:100;height:100">
                                    @endif
                                @elseif (!empty($pg))
                                    @if ($pg['isAccepted'] == '0')
                                        <img src="{{ asset('images/rejected.png') }}" style="width:100;height:100">
                                    @endif
                                @endif
                            </div>

                            <div class="col-sm-4">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th style="width:150px; background:#a7d2dd">Sum of Grades</th>
                                            <td>{{ $sum->sum }}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:150px; background:#a7d2dd">Average Grades</th>
                                            <td> {{ $avg->avg }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <hr>

                    <div class="tm_note tm_text_left tm_font_style_normal">
                        <p class="tm_mb2"><b class="tm_primary_color">NOTES:</b></p>
                        <p class="tm_m0">Please do not share the details contained within this document with
                            unauthorized individuals</p>
                    </div>
                </div>

            </div>
        </div>


        <div class="tm_invoice_btns tm_hide_print">
            <a href="javascript:window.print()" class="tm_invoice_btn tm_color1">
                <span class="tm_btn_icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                        <path
                            d="M384 368h24a40.12 40.12 0 0040-40V168a40.12 40.12 0 00-40-40H104a40.12 40.12 0 00-40 40v160a40.12 40.12 0 0040 40h24"
                            fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32"></path>
                        <rect x="128" y="240" width="256" height="208" rx="24.32" ry="24.32" fill="none"
                            stroke="currentColor" stroke-linejoin="round" stroke-width="32"></rect>
                        <path d="M384 128v-24a40.12 40.12 0 00-40-40H168a40.12 40.12 0 00-40 40v24" fill="none"
                            stroke="currentColor" stroke-linejoin="round" stroke-width="32"></path>
                        <circle cx="392" cy="184" r="24" fill="currentColor"></circle>
                    </svg>
                </span>
                <span class="tm_btn_text">Print</span>
            </a>

            <button id="tm_download_btn" class="tm_invoice_btn tm_color2">
                <span class="tm_btn_icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                        <path
                            d="M320 336h76c55 0 100-21.21 100-75.6s-53-73.47-96-75.6C391.11 99.74 329 48 256 48c-69 0-113.44 45.79-128 91.2-60 5.7-112 35.88-112 98.4S70 336 136 336h56M192 400.1l64 63.9 64-63.9M256 224v224.03"
                            fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="32">
                        </path>
                    </svg>
                </span>
                <span class="tm_btn_text">Download</span>
            </button>


        </div>
    </div>
    </div>
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jspdf.min.js') }}"></script>
    <script src="{{ asset('js/html2canvas.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>

    <script>
        var hashedText = CryptoJS.SHA256('{{ $project->old_project_id }}').toString(CryptoJS.enc.Hex).substring(0, 10);

        var url = "{{ route('gradingDetailsPublic', ['p_id' => 5]) }}".replace('5', hashedText);

        $('#qrcode').empty().qrcode({
            text: url
        });

        // Customize QR code
        $('#qrcode canvas').css({
            width: '80px',
            height: '80px',
            'background': '#98f'
        });
    </script>
</body>

</html>
