<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


<style>
    .modal2 {
        display: flex;
        flex-direction: column;
        width: 85%;
        padding: 1%;
        position: absolute;
        z-index: 2;
        top: 0;
        right: 0;
        background-color: #fdfbf6;
        border: 1px solid #ddd;
        border-radius: 3%;
    }
    .modal2 .flex {
        display: flex;
        align-items: center;
    }
    .modal2 input {
        padding: 1%;
        border: 1px solid teal;
        border-radius: 1%;
    }
    .modal2 p {
        font-size: 79%;
    }
    .overlay2 {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(3px);
        z-index: 1;
    }
    .hidden {
        display: none;
    }
</style>
<section class="modal2 hidden">
    <div class="flex">
        <button class="btn-close2">⨉</button>
    </div>
    @if($verify_outcomes)
      @include('components.gradedOutcomes',['verify_outcomes'=>$verify_outcomes])
    @elseif($outcomes)
      @include('components.outcomeGrading',['outcomes'=>$outcomes])
    @endif

    
</section>
<div class="overlay2 hidden"></div>
<button class="btn btn-open2" style="font-size:100%;"><i class="fa fa-fw fa-info" title="info" style="margin-left:5%;margin-top:-3.2%;"></i></button>

<script>
    const modal2 = document.querySelector(".modal2");
    const overlay2 = document.querySelector(".overlay2");
    const openModalBtn2 = document.querySelector(".btn-open2");
    const closeModalBtn2 = document.querySelector(".btn-close2");

    const closeModal2 = function() {
        modal2.classList.add("hidden");
        overlay2.classList.add("hidden");
    };

    closeModalBtn2.addEventListener("click", closeModal2);
    overlay2.addEventListener("click", closeModal2);

    document.addEventListener("keydown", function(e) {
        if (e.key === "Escape" && !modal2.classList.contains("hidden")) {
            closeModal2();
        }
    });

    const openModal2 = function() {
        modal2.classList.remove("hidden");
        overlay2.classList.remove("hidden");
    };
    openModalBtn2.addEventListener("click", openModal2);
</script>