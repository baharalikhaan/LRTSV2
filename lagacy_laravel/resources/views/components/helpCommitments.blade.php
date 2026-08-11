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
        <i>(Help regarding commitments)</i>
    </a>
    <!-- Bootstrap Modal -->
    <div class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Help regarding commitments</h5>
                    <button type="button" class="close btn-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">


                    <b>A. What are commitments</b>
                    <ul>
                        <li>answer</li>

                    </ul>
                    <b>B. What is score</b>
                    <ul>
                        <li>score formula</li>

                    </ul>

                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Modal Overlay -->
    <!-- <div class="modal-backdrop overlay hidden"></div> -->

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