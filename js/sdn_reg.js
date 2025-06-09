var timerInterval, timerElement, yawa = "asdf";
$(document).ready(function(){ //sdn-register-btn
    $('#register-confirm-btn').on('click' , function(event){
    // $('#register-confirm-btn').on('click' , function(event){
        event.preventDefault();
        const reg_inputs = [$('#sdn-hospital-name') , $('#sdn-hospital-code'), $('#sdn-region-select'), $('#sdn-province-select'), $('#sdn-city-select'), $('#sdn-brgy-select'),  $('#sdn-zip-code'), $('#sdn-email-address'), 
                            $('#sdn-landline-no'), $('#sdn-hospital-mobile-no'), $('#sdn-hospital-director'), $('#sdn-hospital-director-mobile-no'), $('#sdn-point-person'), $('#sdn-point-person-mobile-no')]
        let filled_inputs = false

        reg_inputs.forEach((elem)=>{
            if(elem.val() !== "" ){
                filled_inputs = true
            }
        })

        if(filled_inputs){
            const data = {
                hospital_name : $('#sdn-hospital-name').val(),
                hospital_code : $('#sdn-hospital-code').val(),

                region : $('#sdn-region-select').val(),
                province : $('#sdn-province-select').val(),
                municipality : $('#sdn-city-select').val(),
                barangay : $('#sdn-brgy-select').val(),
                zip_code : $('#sdn-zip-code').text(),
                email : $('#sdn-email-address').val(),
                landline_no : $('#sdn-landline-no').val(),

                hospital_mobile_no : $('#sdn-hospital-mobile-no').val(),

                hospital_director : $('#sdn-hospital-director').val(),
                hospital_director_mobile_no : $('#sdn-hospital-director-mobile-no').val(),

                point_person : $('#sdn-point-person').val(),
                point_person_mobile_no : $('#sdn-point-person-mobile-no').val(),
                validity : "false"
            }

            $.ajax({
                url: './SDN/sdn_reg.php',
                method: "POST",
                data:data,
                success: function(response){
                    if(response === 'invalid'){
                        $('#modal-title').text('Warning')
                        $('#modal-icon').addClass('fa-triangle-exclamation')
                        $('#modal-icon').removeClass('fa-circle-check')
                        $('#modal-body').text('Hospital Code is already registered!')
                        $('#ok-modal-btn').text('OK')
                        $('#yes-modal-btn').css('display' , 'none')

                        $('#myModal').modal('show');
                    }else{
                        const sdn_loading_modal_div = document.querySelector('.sdn-loading-div')
                        sdn_loading_modal_div.style.zIndex = '50'
                        sdn_loading_modal_div.style.display = 'flex'
                        data['validity'] = 'true'
                        $.ajax({
                            url: './SDN/sdn_reg.php',
                            method: "POST",
                            data:data,
                            success: function(response){
                                    console.log(response)
                                    
                                    $('#modal-title').text('Successed')
                                    $('#modal-icon').removeClass('fa-triangle-exclamation')
                                    $('#modal-icon').addClass('fa-circle-check')
                                    $('#modal-body').text('Verified Successfully!')
                                    $('#ok-modal-btn').text('OK')
                                    $('#yes-modal-btn').css('display' , 'flex')
            
                                    for(let i = 0; i < reg_inputs.length; i++){
                                        reg_inputs[i].removeClass('is-valid');
                                        reg_inputs[i].removeClass('is-invalid');
                                    }
            
                                    sdn_loading_modal_div.style.zIndex = '0'
                                    sdn_loading_modal_div.style.display = 'none'
                                    const otp_modal_div = document.querySelector('.otp-modal-div');
                                    // otp_modal_div.className = "otp-modal-div z-10 absolute flex flex-col justify-start items-center gap-3 w-11/12 sm:w-2/6 h-80 translate-y-[200px] sm:translate-y-[350px] translate-x-50px border bg-white rounded-lg"
                                    otp_modal_div.style.display = 'flex'
                                    
                                    $('#otp-input-1').focus()
            
                                    // Set the countdown duration in seconds (5 minutes)
                                    const countdownDuration = 300;
                                    
                                    // Get the timer element
                                    timerElement = document.getElementById('resend-otp-timer');
            
                                    // Initialize the countdown value
                                    let countdown = countdownDuration;
            
                                    // Update the timer display function
                                    function updateTimer() {
                                        const minutes = Math.floor(countdown / 60);
                                        const seconds = countdown % 60;
            
                                        // Display minutes and seconds
                                        timerElement.textContent = `Resend OTP after: ${minutes}m ${seconds}s`;
            
                                        // Check if the countdown has reached zero
                                        if (countdown === 0) {
                                            clearInterval(timerInterval); // Stop the timer
                                            timerElement.textContent = '00:00';
                                            // $('#resend-otp-btn').removeClass('opacity-50 pointer-events-none')
                                            const resend_otp_btn = document.querySelector('#resend-otp-btn')
                                            resend_otp_btn.style.opacity = '1'
                                            resend_otp_btn.style.pointerEvents = 'auto'
                                        } else {
                                            countdown--; // Decrement the countdown
                                        }
                                    }
            
                                    // Set up the timer to update every second
                                    const timerInterval = setInterval(updateTimer, 1000);
                            }
                        })
                    }
                
                }
            })
        }else{
            for(let i = 0; i < reg_inputs.length; i++){
                reg_inputs[i].addClass('is-invalid').removeClass('is-valid');
            }
        }
    })
    //get the user typed OTP
})

