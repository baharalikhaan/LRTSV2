<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .btn-circle {
            border-radius: 50%;
            width: 30px;
            height: 30px;
            line-height: 40px;
            text-align: center;
            padding: 0;
        }
    </style>
</head>

<body>

    <!-- Button to trigger the modal -->
    <!-- <button class="btn btn-primary btn-open">Help</button> -->
    <!-- <button class="btn btn-primary btn-circle btn-open">
        <i class="fa fa-question"></i>
    </button> -->

    <a href="#" class=" btn-open">
        <i>(Instructions regarding filling the form)</i>
    </a>
    <!-- Bootstrap Modal -->
    <div class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">FINAL REPORT form help</h5>
                    <button type="button" class="close btn-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">


                    <b>A. Achievements</b>
                    <ul>
                        <li>Degree of realization of the proposed outcomes in the project</li>
                        <li>Does the project produced a Prototype, Patent, Open Source Software, etc.?</li>
                        <li>If a Prototype is achieved, state its TRL level (or SRL for society readiness)</li>
                    </ul>
                    <b>B. Publication</b>
                    <ul>
                        <li>Number of Q1/Q2 publications in ranked journals</li>
                        <li>Number of Q1 publications in highly ranked journals</li>
                        <li>Number and quality of Books, Chapters, etc</li>
                    </ul>


                    <b>C. Did the project commited in Students and Young Researchers Supervision?</b>
                    <ul>
                        <li>Level of engagement of graduate students in the activities of the proejct</li>
                        <li>Training of undergraduate students</li>
                        <li>Involvement of RAs and GAs in the project</li>
                    </ul>

                    <b>D. Project Impact</b>
                    <ul>
                        <li>Has the project provided concise KPIs for the proposed outcomes?</li>
                        <li>The value of the reported outcomes (e.g., KPIs) in comparison to what was suggested in the proposal on industry/society/government, etc.</li>
                        <li>The potential to benefit society or advance desired economical (e.g., Patents, technology transfer) and societal outcomes (e.g. capacity building of students and researchers, change in policy)</li>
                        <li>The level of engagement with end-users. Extent to which end-users locally and internationally may realistically benefit from the outcomes..</li>
                        <li>The relevance of the project to partners’ development with respect to industrial development, socio-economic, health and environmental aspects and the ability to address end-user needs, as well as the potential to create positive international scientific visibility for the partners (if any).</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Modal Overlay -->
    <div class="modal-backdrop overlay hidden"></div>

    <!-- Bootstrap JavaScript and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Your custom script -->
    <script>
        $(document).ready(function() {
            const modal = $(".modal");
            const overlay = $(".overlay");
            const openModalBtn = $(".btn-open");
            const closeModalBtn = $(".btn-close");

            const closeModal = function() {
                modal.modal("hide");
                overlay.addClass("hidden");
            };

            closeModalBtn.on("click", closeModal);
            overlay.on("click", closeModal);

            $(document).on("keydown", function(e) {
                if (e.key === "Escape" && !modal.hasClass("hidden")) {
                    closeModal();
                }
            });

            const openModal = function() {
                modal.modal("show");
                overlay.removeClass("hidden");
            };

            openModalBtn.on("click", openModal);
        });
    </script>

</body>

</html>