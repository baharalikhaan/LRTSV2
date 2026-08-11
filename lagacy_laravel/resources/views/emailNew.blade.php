  <head>

      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>New Email Template</title>


  </head>

  @extends('layouts.app')
  @section('title', 'Home Page')
  @section('content')


      <body class="body">


          <br>
          <br>

          <div class="container" id="box-form"
              style="border: 2px solid teal; border-radius: 18px; width:500px ; padding:0px;  padding-bottom: 50px;">

              <h5
                  style="background-color: teal; color: white; padding: 10px; border-top-left-radius: 15px; border-top-right-radius: 15px; text-align:center; margin: 0;">
                  New Email Template</h5>

              <div class="container" id="box-form" style="  margin: 10; ">


                  <div align="center">
                      @if (session('emailtemplatesuccess'))
                          <b> {!! session('emailtemplatesuccess') !!} </b>
                          @php
                              session()->forget('emailtemplatesuccess');
                          @endphp
                      @endif
                  </div>

                  <br>
                  <br>


                  <form action="{{ route('emailNew') }}" method="POST">
                      @csrf
                      <tag class="error">
                          <?php if ($errors->any()) : ?>
                          <?php echo 'Kindly fill all the fields'; ?>
                          <?php endif; ?>
                      </tag>
                      <table>
                          <colgroup>
                              <col span="1" style="width: 15%;">
                              <col span="1" style="width: 70%;">
                          </colgroup>

                          <tr>
                              <td><label>Subject</label></td>
                              <td><input type="text" name="subject" class="form-control">
                              </td>
                          </tr>
                          <tr>
                              <td><label for="">Contents</label></td>
                              <td>
                                  <textarea name="contents" class="form-control" rows="4" cols="50"> </textarea>
                              </td>
                          </tr>
                          <tr>
                              <td><label for="">Signature</label></td>
                              <td>
                                  <textarea name="signature" class="form-control" rows="4" cols="50"> </textarea>
                              </td>
                          </tr>

                      </table>
                      <br>
                      <div class="container">
                          <button type="submit"
                              style="float:right; background-color: teal; border-color: teal; color: white;"
                              class="btn btn-primary">
                              {{ __('Save') }}
                          </button>
                      </div>
                  </form>
              </div>
          </div>
      </body>
  @endsection
