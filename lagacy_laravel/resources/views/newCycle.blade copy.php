<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
    #box-form {
        font-family: "Times New Roman", Times, serif;
        border-radius: 15px;
        height: 70%;
        margin: 0 auto;
        position: relative;
    }

    #input {
        margin: 0 auto;
        position: relative;
        width: 100%;
        font-size: 120%;
        color: teal;
        padding: 10%
    }

    #submit {
        margin: auto;
        display: block;
        width: 40%;
        height: 6%;
        border: 3px solid teal;
        border-radius: 5px;
    }

    #header {
        background-color: teal;
        font-weight: bold;
        border-radius: 2px;
        width: 100%;
        height: 8%;
    }

    .h5 {
        color: beige;
        padding-top: 8px;
    }

    #icons {
        font-size: 130%;
    }

    #non {
        color: grey;
    }

    #act {
        color: teal;
    }

    .invalid-feedback {
        color: red;
        font-size: 70%;
    }
    label{
        font-size: 12;
        font-weight: bold;
    }
</style>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
</head>

<body>


    <fieldset style="margin-left:2%;margin-right:2%;font-family:sans-serif;padding:2%;border-radius:5%;background:#f2f2f2;border:1px solid teal;width:30%">
        <legend style="background:teal;color:beige;font-size:12px;border-radius:5%;padding:1%;margin-left:20px;box-shadow:0 0 0 2px #ddd">Creating New Cycle in RTS</legend>
        <form method="POST" action="{{route('createCycle')}}" id="box-form">
            @csrf
            <div>
                <div id="header" class="w3-bar-block w3-large">
                    <h5 class="h5" align="center">Cycle Information</h5>
                </div>
                <div id="input">
                    <label>Cycle:</label>
                    <input type="text" value={{$cycle}} disabled>
                    <input type="text" name="cycle" value={{$cycle}} hidden>
                    <br>

                    @error('cycle_title')
                    <span class="invalid-feedback" role="alert">
                        <strong>this field is required</strong></span>
                    @enderror<br>
                    <label>Cycle Title:</label>
                    <input type="text" name="cycle_title">
                    <br>

                    @error('prg_rpt_deadline')
                    <span class="invalid-feedback" role="alert">
                        <strong>this field is required</strong></span>
                    @enderror<br>
                    <label>Progress Report Deadline:</label>
                    <input type="date" name="prg_rpt_deadline">
                    <br>

                    @error('extended_prg_rpt_deadline')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong></span>
                    @enderror<br>
                    <label>Extended deadline for progress Report:</label>
                    <input type="date" name="extended_prg_rpt_deadline">
                    <br>

                    @error('final_rpt_deadline')
                    <span class="invalid-feedback" role="alert">
                        <strong>this field is required</strong></span>
                    @enderror<br>
                    <label>Final Report Deadline:</label>
                    <input type="date" name="final_rpt_deadline">
                    <br>

                    @error('extended_final_rpt_dealine')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong></span>
                    @enderror<br>
                    <label>Extended final report deadline:</label>
                    <input type="date" name="extended_final_rpt_deadline">
                    <br>

                    @error('upload_outcomes')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong></span>
                    @enderror<br>
                    <label>Can LPI upload outcomes:</label><br>
                    <i style="font-size: 12;">
                    <input type="radio" name="upload_outcomes" value='active' checked=true>Active<br>
                    <input type="radio" name="upload_outcomes" value='inactive'>Inactive<br>
                    <input type="radio" name="upload_outcomes" value='finish'>Finish</i>
                <button id="submit" class="btn btn-primary" type="submit" style="margin-left:20%">Create</button>
            </div>
            </div>
            <br>
            <br>
            </div>
        </form>
        <div><br></div>
</body>