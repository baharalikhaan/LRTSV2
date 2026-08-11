<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<style>
    * {
        box-sizing: border-box;
    }

    /* Style the navbar */
    .searchBar {
        overflow: hidden;
        background-color: #54BAB9;
        width: 80%;
        margin: 0 auto;
        border-radius: 5px;
        border: 2px solid teal;
    }

    /* button in search bar */
    .searchBar a {
        float: left;
        margin-top: 2px;
        display: block;
        color: beige;
        padding: 14px 16px;
        font-size: 15px;

    }

    /* Navbar links on mouse-over */
    .searchBar a:hover {
        background-color: #ddd;
        color: black;
    }

    /* Active/current link */
    .searchBar a.active {
        background-color: #2196F3;
        color: white;
    }

    /* Style the input container */
    .searchBar .search-container {
        float: right;
    }

    /* Style the input field inside the navbar */
    .searchBar input[type=text] {
        padding: 6px;
        margin-top: 10px;
        margin-right: 8px;
        font-size: 11px;
        border: none;
    }

    /* Style the button inside the input container */
    .searchBar .search-container button {
        float: right;
        padding: 6px;
        margin-top: 8px;
        margin-right: 16px;
        background: #ddd;
        font-size: 17px;
        border: none;
        cursor: pointer;
    }

    .searchBar .search-container button:hover {
        background: #ccc;
    }
</style>
<div class="searchBar">
    @if($type != 'Reviewer')
    <a  href="{{ route('newProject') }}" class="w3-bar-item w3-button" ><i class="fa fa-plus"></i></a>
  @endif
    <div class="search-container">
        <form action="{{route('search_project')}}">
            <input type="text" placeholder="Search.." name="search">
            <button id="submit" type="submit" class="w3-bar-item w3-button"><i class="fa fa-search"></i></button>
        </form>
    </div>
</div>

   