$(document).ready(function(){
    $('#resend-otp-btn').on('click' , function(event){
        const data = {
            hospital_code :  $('#sdn-hospital-code').val(),
            email : $('#sdn-email-address').val()
        }


        $.ajax({
            url: './SDN/resend_otp.php',
            method: "POST",
            data:data,
            success: function(response){
                // sdn_loading_modal_div.classList.remove('z-10')
                // sdn_loading_modal_div.classList.add('hidden')
                // const otp_modal_div = document.querySelector('.otp-modal-div');
                // otp_modal_div.className = "otp-modal-div z-10 absolute flex flex-col justify-start items-center gap-3 w-11/12 sm:w-2/6 h-80 translate-y-[200px] sm:translate-y-[350px] translate-x-50px border bg-white rounded-lg"
                // Set the countdown duration in seconds (5 minutes)

                // $('#resend-otp-btn').addClass('opacity-50 pointer-events-none')
                $('#new-otp-sent-txt').css('opacity' , '1')
                $('#resend-otp-btn').css('opacity', '0.5')
                $('#resend-otp-btn').css('pointer-events', 'none')
                const countdownDuration = 300;
                    
                // Get the timer element
                const timerElement = document.getElementById('resend-otp-timer');

                // Initialize the countdown value
                let countdown = countdownDuration;
                let opacity_new_otp_txt = 5;
                // Update the timer display function
                function updateTimer() {
                    const minutes = Math.floor(countdown / 60);
                    const seconds = countdown % 60;

                    // Display minutes and seconds
                    timerElement.textContent = `Resend OTP after: ${minutes}m ${seconds}s`;

                    // #new-otp-sent-txt
                    if(opacity_new_otp_txt >= 0){
                        $('#new-otp-sent-txt').css('opacity' , '0.' + opacity_new_otp_txt)
                        opacity_new_otp_txt -= 1
                    }

                    // Check if the countdown has reached zero
                    if (countdown === 0) {
                        clearInterval(timerInterval); // Stop the timer
                        timerElement.textContent = '00:00';
                        $('#resend-otp-btn').css('opacity', '1')
                        $('#resend-otp-btn').css('pointer-events', 'auto')
                    } else {
                        countdown--; // Decrement the countdown
                    }
                }

                // Set up the timer to update every second
                timerInterval = setInterval(updateTimer, 1000);
            }
        })

    })
})