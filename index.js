let mobile_responsive = false; 
if (window.matchMedia("(max-width: 480px)").matches) {
    mobile_responsive = true; 
} else {
    mobile_responsive = false
}

$(document).ready(function(){
    const myModal = new bootstrap.Modal(document.getElementById('myModal'));
    const tutorialModal = new bootstrap.Modal(document.getElementById('tutorialModal'));
    // tutorialModal.show()
   

    function validateElement(element) { 
        var isValid = true;

        if (!element.val()) {
            isValid = false;
            element.addClass('is-invalid').removeClass('is-valid');
        } else {
            element.removeClass('is-invalid').addClass('is-valid');
        }
    }

    $("#myModal").on('shown.bs.modal', function () {
        $(".modal-dialog").draggable({
          handle: ".modal-header"
        });
    });


    $('#query-signin-txt').on('click' , function(event){
        event.preventDefault();
        $('.main-content').css('display', 'none');
        $('.sub-content').css('display', 'flex');

        // if(tutorialMode_on){
        //     tutorial_modal.show()
        //     $('#tutorial_title').text("Registration of your RHUs")
        //     $('#tutorial_body').text("All field must be filled")
        //     $('#tutorial_dialog').removeClass('modal-lg')
        //     $('#tutorial_dialog').addClass('modal-md')
        // }
    })  

    $('.return').on('click' , function(event){
        event.preventDefault();
        $('.main-content').css('display', 'flex');
        $('.sub-content').css('display', 'none');
    })

    // registration-btn
    $('#registration-btn').on('click' , function(event){
        event.preventDefault();

        $('.sub-content-registration-form').css('display', 'block');
        $('.sub-content-authorization-form').css('display', 'none');

        $('#registration-btn').attr('class', 'btn btn-primary');
        $('#authorization-btn').attr('class', 'btn btn-dark');

    })

    $('#authorization-btn').on('click' , function(event){
        event.preventDefault();

        $('.sub-content-registration-form').css('display', 'none');
        $('.sub-content-authorization-form').css('display', 'block');

        $('#registration-btn').attr('class', 'btn btn-dark');
        $('#authorization-btn').attr('class', 'btn btn-primary');
    })

    $("#sdn-landline-no").on("input", function(){
        let value = $("#sdn-landline-no").val().replace(/[^0-9]/g, '');
        // Add dashes at specific positions
        if (value.length >= 3) {
            value = value.slice(0, 3) + '-' + value.slice(3);
        }
        if (value.length > 8) {
            value = value.slice(0, 8);
        }
        $("#sdn-landline-no").val(value);
    })

    const mobileNumValue = (val) => {
        // Remove any non-numeric characters
        let value;
        if(val === 1){
            value = $("#sdn-hospital-mobile-no").val().replace(/[^0-9]/g, '');
        }else if(val === 2){
            value = $("#sdn-hospital-director-mobile-no").val().replace(/[^0-9]/g, '');
        }else if(val === 3){
            value = $("#sdn-point-person-mobile-no").val().replace(/[^0-9]/g, '');
        }
        // Add dashes at specific positions
        if (value.length >= 4) {
            value = value.slice(0, 4) + '-' + value.slice(4);
          }
        if (value.length >= 9) {
            value = value.slice(0, 9) + '-' + value.slice(9);
        }
        if (value.length > 13) {
            value = value.slice(0, 13);
        }
        if(val === 1){
            $("#sdn-hospital-mobile-no").val(value);
        }else if(val === 2){
            $("#sdn-hospital-director-mobile-no").val(value);
        }else if(val === 3){
            $("#sdn-point-person-mobile-no").val(value);
        }
    }

    $("#sdn-hospital-mobile-no").on("input", () => mobileNumValue(1))
    $("#sdn-hospital-director-mobile-no").on("input", () => mobileNumValue(2))
    $("#sdn-point-person-mobile-no").on("input", () => mobileNumValue(3))

    // Common function to move focus to the next or previous input
    function moveToNextInput(currentInput, nextInputId) {
        if (currentInput.value.length === 1) {
            document.querySelector(nextInputId).focus();
        }
    }

    function moveToPreviousInput(currentInput, prevInputId) {
        if (currentInput.value.length === 0) {
            document.querySelector(prevInputId).focus();
        }
    }

    // OTP Input 1
    $("#otp-input-1").on("input", function (elem) {
        moveToNextInput(this, '#otp-input-2');
    });

    // OTP Input 2
    $("#otp-input-2").on("input", function () {
        moveToNextInput(this, '#otp-input-3');
    }).on("keydown", function (event) {
        if (event.keyCode === 8 && this.value === "") {
            event.preventDefault();
            document.querySelector('#otp-input-1').focus();
        }
    });

    // OTP Input 3
    $("#otp-input-3").on("input", function () {
        moveToNextInput(this, '#otp-input-4');
    }).on("keydown", function (event) {
        if (event.keyCode === 8 && this.value === "") {
            event.preventDefault();
            document.querySelector('#otp-input-2').focus();
        }
    });

    // OTP Input 4
    $("#otp-input-4").on("input", function () {
        moveToNextInput(this, '#otp-input-5');
    }).on("keydown", function (event) {
        if (event.keyCode === 8 && this.value === "") {
            event.preventDefault();
            document.querySelector('#otp-input-3').focus();
        }
    });

    // OTP Input 5
    $("#otp-input-5").on("input", function () {
        moveToNextInput(this, '#otp-input-6');
    }).on("keydown", function (event) {
        if (event.keyCode === 8 && this.value === "") {
            event.preventDefault();
            document.querySelector('#otp-input-4').focus();
        }
    });

    // OTP Input 6
    $("#otp-input-6").on("input", function () {
        if (this.value.length > 1) {
            this.value = this.value.slice(0, 1); // Limit to one character
        }
    }).on("keydown", function (event) {
        if (event.keyCode === 8 && this.value === "") {
            event.preventDefault();
            document.querySelector('#otp-input-5').focus();
        }
    });

    function validateEmail(email) {
        // Regular expression for validating an email address
        var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return emailPattern.test(email);
    }
    
    // Get the email input element
    var emailInput = document.getElementById('sdn-email-address');
    
    // Add event listener for input or change event
    emailInput.addEventListener('input', function() {
        if (validateEmail(emailInput.value)) {
            emailInput.classList.remove('is-invalid');  // Remove invalid styling
        } else {
            emailInput.classList.add('is-invalid');     // Add invalid styling
        }
    });
    
    // tutorial_modal.show()
    
    $('#tutorial-btn').mouseenter(function(){
        $('#tutorial-btn').removeClass('fa-regular fa-circle-question');
        $('#tutorial-btn').addClass('fa-solid fa-circle-question');
    }).mouseout(function(){
        $('#tutorial-btn').removeClass('fa-solid fa-circle-question');
        $('#tutorial-btn').addClass('fa-regular fa-circle-question');
    })

    $('#tutorial-btn').on('click' , function(){
        if(mobile_responsive === false){
            window.open("../assets/user_guide/hcpn_user_guide.pdf", "_blank");
        }
    })

    $('#license-div').css('width', '100%')
    $('#license-div').css('left', '0')

    $('#myModal').off('click', '#ok-modal-btn').on('click', '#ok-modal-btn', function(event) {

    })

    function showModal(message, type = "warning") {
        $("#modal-title").text(type === "warning" ? "Warning" : "Info");
        $("#modal-icon").addClass(type === "warning" ? "fa-triangle-exclamation" : "fa-circle-check");
        $("#modal-icon").removeClass(type === "warning" ? "fa-circle-check" : "fa-triangle-exclamation");
        $("#modal-body").text(message);
        $("#ok-modal-btn").text("OK");
        $("#yes-modal-btn").hide();
        $("#ok-modal-btn").css("margin-right", "0");
        $("#myModal").modal("show");
    }
})