<div class="modal fade" id="mss-setting-modal" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
     
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 id="modal-title" class="modal-title" id="exampleModalLabel">Medical Social Service | Women and Children Protection Unit</h5>
                <button type="button" data-bs-dismiss="modal" aria-label="Close" style="color:white">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="mss-password-form">
                    <div class="mb-3 position-relative">
                        <label for="mss-password-old" class="form-label">Enter Current Password</label>
                        <input type="password" class="form-control" id="mss-password-old" placeholder="Enter Current Password" required>
                        <i class="fa-solid fa-eye toggle-password" data-target="#mss-password-old"></i>
                    </div>

                    <div class="mb-3 position-relative">
                        <label for="mss-password" class="form-label">Enter Password</label>
                        <input type="password" class="form-control" id="mss-password" placeholder="Enter password" required>
                        <i class="fa-solid fa-eye toggle-password" data-target="#mss-password"></i>
                    </div>

                    <div class="mb-3 position-relative">
                        <label for="mss-password-confirm" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="mss-password-confirm" placeholder="Re-enter password" required>
                        <i class="fa-solid fa-eye toggle-password" data-target="#mss-password-confirm"></i>
                    </div>

                    <div id="password-feedback" class="text-danger mb-3" style="display: none;">
                        Passwords do not match.
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">Set Password</button>
                    </div>
                </form>

            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('.toggle-password').on('click', function () {
        const input = $($(this).data('target'));
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        $(this).toggleClass('fa-eye fa-eye-slash');
    });

    // On any input, hide feedback and optionally validate
    $('#mss-password, #mss-password-confirm').on('input', function () {
        $('#password-feedback').hide();
    });

    $('#mss-password-form').on('submit', function (e) {
        e.preventDefault(); // Prevent form from submitting right away

        const oldInput = $('#mss-password-old').val().trim();
        const newPassword = $('#mss-password').val().trim();
        const confirmPassword = $('#mss-password-confirm').val().trim();

        // First, fetch current password from server
        // $.ajax({
        //     url: '../SDN/fetch_mssPassword.php',
        //     method: "GET",
        //     dataType: "json",
        //     success: function(response) {
        //         const currentPasswordDB = response.mss_password; // The password stored in DB

        //         if (oldInput !== currentPasswordDB) {
        //             $('#password-feedback').text("Current password is incorrect.").show();
        //             return;
        //         }

        //         if (newPassword !== confirmPassword) {
        //             $('#password-feedback').text("Passwords do not match.").show();
        //             return;
        //         }

        //         if (newPassword.length < 3) {
        //             $('#password-feedback').text("Password should be at least 6 characters long.").show();
        //             return;
        //         }

        //         // Proceed with saving new password (e.g., via AJAX)
        //         console.log(newPassword); // For debugging
        //         $.ajax({
        //             url: '../SDN/save_mssPassword.php',
        //             method: 'POST',
        //             data: {
        //                 new_password: newPassword.toString()
        //             },
        //             success: function(saveResponse) {
        //                 if(saveResponse === "success") {
        //                     $('#password-feedback').text("Successfully Updated.").show();
        //                     return;
        //                 }

        //             },
        //             error: function() {
        //                 alert("An error occurred while saving the password.");
        //             }
        //         });
        //     },
        //     error: function() {
        //         $('#password-feedback').text("Failed to verify current password. Try again later.").show();
        //     }
        // });
    });
});
</script>

