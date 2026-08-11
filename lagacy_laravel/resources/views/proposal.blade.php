<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<style>
    body {
        font-family: "Lato", sans-serif;
    }

    .error {
        font-weight: bold;
        color: red;
    }

    #inner {
        border: 2px solid teal;
        padding: 2%;
    }

    .sidebar {
        height: 100%;
        width: 0;
        position: fixed;
        z-index: 1;
        top: 0;
        right: 0;
        background-color: whitesmoke;
        overflow-x: hidden;
        transition: 0.5s;
        padding-top: 60px;
        border: 2px solid teal;
        font-size: 8;
        padding-right: 1%;
        padding-left: 1%;
        font-weight: bold;
    }

    .sidebar a {
        padding: 8px 8px 8px 32px;
        text-decoration: none;
        font-size: 25px;
        color: #818181;
        display: block;
        transition: 0.3s;
    }

    .sidebar a:hover {
        color: #f1f1f1;
    }

    .sidebar .closebtn {
        position: absolute;
        top: 0;
        left: 0px;
        font-size: 16px;
        margin-right: 5px;
        font-weight: bold;
    }

    .openbtn {
        font-size: 20px;
        cursor: pointer;
        background-color: teal;
        color: beige;
        padding: 10px 15px;
        border: none;
    }

    .openbtn:hover {
        background-color: #444;
    }

    #main {
        transition: margin-right .5s;
        padding: 16px;
    }

    #header .title {
        font-weight: bold;
        color: teal;
        font-size: 25;
    }

    .radio {
        margin-left: 2%;
        font-size: 10px;
        font-weight: bold;
        color: teal;
    }


    /* On smaller screens, where height is less than 450px, change the style of the sidenav (less padding and a smaller font size) */
    @media screen and (max-height: 450px) {
        .sidebar {
            padding-top: 15px;
        }

        .sidebar a {
            font-size: 18px;
        }
    }
</style>
</head>

<body>
    <?php if (isset($message))
        echo $message; ?>

    <div id="main">
        <div id='header'>
            <h6 class='title' align="center">{{$project->title}} </h6>
            
            <h6 class='title'>Project Proposal </h6>
            <button class="openbtn w3-button w3-right" onclick="openNav()"><i class="fa-regular fa-left"></i>
            </button>
        </div>
    </div>
    <iframe src="http://localhost:8000/serveFile?file={{$report->path}}" height="90%" width="95%" align="center"></iframe>
    <div id="Sidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
        <?php if ($errors->any()) : ?>
            <div class="error">
                <?php foreach ($errors->all() as $error) : ?>
                    <?php echo $error ?><br>
                <?php endforeach; ?>
            </div><br>
        <?php endif; ?>

        <div id="proposal_form">
            <form method="POST" action="{{route('statusUpdate')}}">
             
                @csrf
                <input type="text" name="p_id" value={{$p_id}} hidden>
                <h6><b>Proposal Acceptance Form</b></h6><br>
                <br>
                <b>Q. Are you satisfied with the proposal?</b><br><br>
                <input type="radio" id="status" value="Accepted" name="status">
                <label for="Yes">Proposal Accepted</label><br>
                <input type="radio" id="status" value="Rejected" name="status">
                <label for="No">Proposal Rejected</label><br><br>
                <button type="submit" class="btn btn-primary">
                    {{ __('Confirm') }}
                </button>

            </form>
        </div>
    </div>

    <div class="w3-container">
                        <button onclick="document.getElementById('id01').style.display='block'" class="w3-button w3-display-bottomcenter">Reviewer</button>
                        <div id="id01" class="w3-modal">
                            <div class="w3-modal-content">
                                <div class="w3-container">
                                    <span onclick="document.getElementById('id01').style.display='none'" class="w3-button w3-display-topright">&times;</span>
                                    @include('components.helpFinal')
                                </div>
                            </div>
                        </div>
                    </div>
    <script>


        function openNav() {
            document.getElementById("Sidebar").style.width = "250px";
            document.getElementById("main").style.marginRight = "250px";
        }

        function closeNav() {
            document.getElementById("Sidebar").style.width = "0";
            document.getElementById("main").style.marginRight = "0";
        }
    </script>