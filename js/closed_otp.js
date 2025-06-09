$(document).ready(function(){
    $('#sdn-otp-modal-btn-close').on('click' , function(event){
        // main OTP modal div
        const otp_modal_div = document.querySelector('.otp-modal-div')

        // handle close button for otp modal
        let response= confirm("Do you want to cancel the registration?");
        if (response == true) {
            otp_modal_div.className = "otp-modal-div hidden absolute flex flex-col justify-start items-center gap-3 w-11/12 sm:w-2/6 h-80 translate-y-[200px] sm:translate-y-[350px] translate-x-50px border bg-teleCreateAccColor rounded-lg"
            const data = {
                hospital_code : $('#sdn-hospital-code').val(),
                // hospital_code : 2007,
            }
            $.ajax({
                url: './SDN/closed_otp.php',
                method: "POST",
                data:data,
                success: function(response){
                    otp_modal_div.style.display = "none"
                    clearInterval(timerInterval)
                    timerElement.textContent = "Resend OTP after: 00:00"
                    let inputValues = $('.sub-content-registration-form input').map(function() {
                        return $(this).val();
                    }).get();
                    // window.location.href = "http://10.10.90.14:8079/index.php" 
                    // window.location.href = '../index.php'
                }
            })
        }else {
            // Select all input elements inside the form with class "sub-content-registration-form"
            
        }
    })
})