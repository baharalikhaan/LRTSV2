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
        width: 10%;
        height: 6%;
        border: 3px solid teal;
        border-radius: 5px;
        padding:0;
    }

    #header {
        background-color: teal;
        font-weight: bold;
        border-radius: 2px;
        width: 100%;
        height: 7%;
    }

    /* Create three equal columns that floats next to each other */
    .column {
        float: left;
        width: 31%;
        padding: 10px;
    }

    /* Clear floats after the columns */
    .row:after {
        content: "";
        display: table;
        clear: both;
    }

    .h5 {
        color: beige;
        padding-top: 6px;
    }

    #icons {
        font-size: 130%;
    }

    #act {
        color: teal;
    }

    #non {
        color: #aaa;
    }

    table {
        font-size: 80%;
        font-weight: bold;
    }

    #bottom {

        position: absolute;
        bottom: 1%;
        font-size: 80%;
        color: #CC7722;
    }
    .error{
        color:red;
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


    <fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:1%;border-radius:3%;background:#f2f2f2;border:1px solid teal;width:90%;height:100%">
        <legend style="background:teal;color:beige;padding:1% 1%;font-size:12px;border-radius:10%;margin-left:20px;box-shadow:0 0 0 2px #ddd">Registering New Project in RTS</legend>
        <div align="center" id="icons">
            <i class="fa fa-circle-info" id="non"> </i> &emsp;&emsp;&emsp;
            <i class="fa fa-fw fa-link" id="non"></i>&emsp;&emsp;&emsp;
            <i class="fa fa-fw fa-list-check" id="act"></i>
        </div>
        <form method="POST" action="{{route('createProjectStep3')}}" id="box-form">
            @csrf
            <div>
                <div id="header" class="w3-padding w3-bar-block w3-large">
                    <h5 class="h5" align="center">Project Commitments</h5>
                </div>
                <div class="row" style="margin-left:2%;">
                    <div class="column">
                        <div id="header">
                            <h5 class="h5" align="center">Scholarly Commitments</h5>
                        </div>
                        <table>
                            <tr>
                                <td>1. No. of Articles to be published in journals listed in Thomson Reuters Web of Science-SCI</td>
                            </tr>
                            <tr>
                                <td>&emsp;&emsp;a. Quartile-1 journal</td>
                                <td><input type=“text” name='Q1' size='2'></td>
                            </tr>
                            <tr>
                                <td>&emsp;&emsp;b. Quartile-2 journal </td>
                                <td><input type=“text” name='Q2' size='2'></td>
                            </tr>
                            <tr>
                                <td>&emsp;&emsp;c. Quartile-3 journal </td>
                                <td><input type=“text” name='Q3' size='2'></td>
                            </tr>
                            <tr>
                                <td>&emsp;&emsp;d. Quartile-4 journal</td>
                                <td><input type=“text” name='Q4' size='2'></td>
                            </tr>
                            <tr>
                                <td>2. No. of articles to be published in indexed international conferences</td>
                                <td><input type=“text” name='conf' size='2'></td>
                            </tr>
                            <tr>
                                <td>3. No. of books to be publish</td>
                                <td><input type=“text” name='book_publish' size='2'></td>
                            </tr>
                            <tr>
                                <td>4. No. of books to be edited</td>
                                <td><input type=“text” name='book_edit' size='2'></td>
                            </tr>
                            <tr>
                                <td>5. No. of book chapters to be publish</td>
                                <td><input type=“text” name='chap' size='2'></td>
                            </tr>
                        </table>

                    </div>
                    <div class="column">
                        <div id="header">
                        <h5 class="h5" align="center">Intellectual Property</h5>
                        </div>
                        <table>
                            <br>
                            
                            <tr>
                                <td>1. No. of Intellectual Property Disclosure form to be submitted</td>
                                <td><input type=“text” name='form' size='2'></td>
                            </tr>
                            <tr>
                                <td>2. No. of Provisional Patent to be filed.</td>
                                <td><input type=“text” name='patent' size='2'></td>
                            </tr>
                            <tr>
                                <td>3. Open source software been develop</td>
                                <td><select name="sw">
                                        <option value='' disabled selected>--</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select></td>
                            </tr>
                            <tr>
                                <td>4. Creation of start up</td>
                                <td><select name="start_up">
                                        <option value='' disabled selected>--</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select></td>
                            </tr>
                            <br>
                            <tr>
                                <tag class="error">
                                <?php if ($errors->any()) : ?>
                                        <?php echo "Kindly fill all the fields" ?>
                                    <?php endif; ?>
                                </tag>
                            </tr>
                            <br>
                        </table>

                    </div>
                    <div class="column">
                        <div id="header">
                            <h5 class="h5" align="center">Student Involvement</h5>
                        </div>
                        <table>
                            <tr>
                                <td>1. Inclusion of Student</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>&emsp;a. No. of Under Graduate Student</td>
                                <td><input type=“text” name='UG' size='2'></td>
                            </tr>
                            <tr>
                                <td>&emsp;b. No. of Masters Students</td>
                                <td><input type=“text” name='masters' size='2'></td>
                            </tr>
                            <tr>
                                <td>&emsp;c. No. of Ph.D Students</td>
                                <td><input type=“text” name='phd' size='2'></td>
                            </tr>
                            <tr>
                            <td><br></td>
                            </tr>
                            <td>2. Includes Cross College Participation? &emsp;</td>
                            <td><select name="crossClg" id="crossClg">
                                    <option value='' disabled selected>--</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select></td>
                            </tr>
                        </table>
                        <div id="bottom" align="left">
                           
                            <b style="color:red">Note</b>
                            <br>
                            <tag style="color:red">
                                - Kindly fill all the fields<br>
                                - All textboxes need numeric values. From 0-99
                            </tag>
                        </div>
                    </div>
                </div>
                <button id="submit" type="submit">Submit</button>
            </div>
            <br>
            </div>
        </form>
    </fieldset>
</body>