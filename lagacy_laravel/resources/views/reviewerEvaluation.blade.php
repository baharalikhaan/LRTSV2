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
              <div class="tm_logo"><img src="{{ asset('images/research_logo.png') }}" alt="Logo"></div>
            </div>
            <div class="tm_invoice_right tm_text_right tm_mobile_hide">
              <div class="tm_f50 tm_text_uppercase tm_white_color">REPORT CARD </div>
              <div class="tm_f50 tm_text_uppercase tm_white_color" style="font-size:15px; margin-bottom:25; margin-top:5">Reviewer Evaluation </div>
            </div>
            <div class="tm_shape_bg tm_accent_bg tm_mobile_hide"></div>
          </div>



          <div>
            <table class="table table-bordered" style="background:#e3e3e6 ">
              <tbody>
                <tr>
                  <th style="width:150px">Reviewer</th>
                  <td>{{$user->name}}</td>
                </tr>
                <tr>
                  <th>Reviewer Email</th>
                  <td>{{$user->email}}</td>
                </tr>

              </tbody>
            </table>



            <h5 class="tm_gray_bg ">Internal Provider List</h5>

            @foreach($data as $dt)
            <table class="table table-bordered table-sm ">
              <tbody>
                <tr>
                  <th style="width:200px">Scope of Supply/Service</th>
                  <td>{{$dt->scope_of_supply}}</td>
                <tr>
                  <th>Mode of Selection</th>
                  <td>{{$dt->mode_of_selection}}</td>
                </tr>
                </tr>
                <tr>
                  <th>Basis of Approval</th>
                  <td>{{$dt->basis_of_approval}}</td>
                <tr>
                  <th>Type & Entity of Control</th>
                  <td>{{$dt->type_extent_of_control}}</td>
                </tr>
                </tr>
                <tr>
                  <th>Designation of Approver</th>
                  <td>{{$dt->designation_of_approver}}</td>

                </tr>

              </tbody>
            </table>
            @endforeach

            <br>
            <h5 class="tm_gray_bg ">Evaluation</h5>
            <table class="table table-bordered table-sm">
              <tbody>
                <tr>
                  <th style="width:200px">Cycle</th>
                  <th style="width:150px">Mode of Selection</th>
                  <th>Conflict</th>
                  <th>Comprehensiveness</th>
                  <th>Responsiveness</th>
                  <th>No of Reviews</th>
                  <th>Behaviour</th>
                </tr>
                @foreach($data as $dt)
                <tr>
                  <th>{{$dt->cycle_title}}</th>
                  <td>{{$dt->mode_of_selection}}</td>
                  <td>{{$dt->conflict}}</td>
                  <td>{{$dt->comprehensiveness}}</td>
                  <td>{{$dt->responsiveness}}</td>
                  <td>{{$dt->no_reviewers}}</td>
                  <td>{{$dt->behaviour}}</td>
                </tr>
                @endforeach
              </tbody>
            </table>

            <br>
            <h5 class="tm_gray_bg ">Re-Evaluation</h5>
            <table class="table table-bordered table-sm">
              <tbody>
                <tr>
                  <th style="width:350px">Cycle</th>
                  <th>Mode of Selection</th>
                  <th>Score</th>

                </tr>
                @foreach($data as $dt)
                <tr>
                  <th>{{$dt->cycle_title}}</th>
                  <td>{{$dt->mode_of_selection}}</td>
                  <td>{{$dt->conflict+$dt->comprehensiveness+$dt->responsiveness+$dt->no_reviewers+$dt->behaviour}}</td>

                </tr>
                @endforeach
              </tbody>
            </table>

          </div>

          <br>



          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">

                <div id="qrcode"></div>
              </div>

            </div>
          </div>


          <hr>

          <!--
          <div class="tm_invoice_footer tm_type1">
            <div class="tm_left_footer"></div>
            <div class="tm_right_footer">
              <div class="tm_sign tm_text_center">
                <img src="assets/img/sign.svg" alt="Sign">
                <p class="tm_m0 tm_ternary_color">Jhon Donate</p>
                <p class="tm_m0 tm_f16 tm_primary_color">Accounts Manager</p>
              </div>
            </div>
          </div> -->

          <div class="tm_note tm_text_left tm_font_style_normal">

            <p class="tm_mb2"><b class="tm_primary_color">NOTES:</b></p>
            <p class="tm_m0">Please do not share the details contained within this document with unauthorized individuals.</p>
          </div><!-- .tm_note -->
        </div>

      </div>
    </div>


    <div class="tm_invoice_btns tm_hide_print">
      <a href="javascript:window.print()" class="tm_invoice_btn tm_color1">
        <span class="tm_btn_icon">
          <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
            <path d="M384 368h24a40.12 40.12 0 0040-40V168a40.12 40.12 0 00-40-40H104a40.12 40.12 0 00-40 40v160a40.12 40.12 0 0040 40h24" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32"></path>
            <rect x="128" y="240" width="256" height="208" rx="24.32" ry="24.32" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32"></rect>
            <path d="M384 128v-24a40.12 40.12 0 00-40-40H168a40.12 40.12 0 00-40 40v24" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32"></path>
            <circle cx="392" cy="184" r="24" fill="currentColor"></circle>
          </svg>
        </span>
        <span class="tm_btn_text">Print</span>
      </a>

      <button id="tm_download_btn" class="tm_invoice_btn tm_color2">
        <span class="tm_btn_icon">
          <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
            <path d="M320 336h76c55 0 100-21.21 100-75.6s-53-73.47-96-75.6C391.11 99.74 329 48 256 48c-69 0-113.44 45.79-128 91.2-60 5.7-112 35.88-112 98.4S70 336 136 336h56M192 400.1l64 63.9 64-63.9M256 224v224.03" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32">
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
    var hashedText = CryptoJS.SHA256('{{$user->id}}').toString(CryptoJS.enc.Hex);

    var url = "{{ route('reviewerEvaluationPublic', ['u_id' => 5 ]) }}".replace('5', hashedText).substring(0,10);

    $('#qrcode').empty().qrcode({
      text: url
    });

    // Customize QR code size and color using CSS
    $('#qrcode canvas').css({
      width: '80px', // Size in pixels
      height: '80px', // Size in pixels
      'background': '#98f' // Background color
    });
  </script>
</body>

</html>
