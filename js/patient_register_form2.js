
$(document).ready(function(){
    startIdleTimer()
    const loadContent = (url) => {
        $.ajax({
            url:url,
            success: function(response){
                $('#container').html(response);
            }
        })
    }

    for(let i = 0; i < $('.side-bar-navs-class').length; i++){
        $('.side-bar-navs-class').css('opacity' , '0.3')
        $('.side-bar-navs-class').css('border-top' , 'none')
        $('.side-bar-navs-class').css('border-bottom' , 'none')
    }

    $('#patient-reg-form-sub-side-bar').css('opacity' , '1')
    $('#patient-reg-form-sub-side-bar').css('border-top' , '2px solid #3e515b')
    $('#patient-reg-form-sub-side-bar').css('border-bottom' , '2px solid #3e515b')

    // patHistoryModal
    const patHistoryModal = new bootstrap.Modal(document.getElementById('patHistoryModal'));
    let patRegModal = new bootstrap.Modal(document.getElementById('myModal_pat_reg'));
    let prompt_modal = new bootstrap.Modal(document.getElementById('myModal_prompt'));
    
    // loadContent('../../SDN/opd_referral_form.php?type=' + $('#tertiary-case').val() + "&code=" + $('#hpercode-input').val())
    // loadContent('../../SDN/referral_form.php?type="OR"&code="PAT000040"')

    $('#check-if-registered-btn').on('click', function(event) {
        event.stopPropagation();  // Prevent the click event from propagating to the document
        $('#check-if-registered-btn').css('display', 'none');
        $('#check-if-registered-div')[0].style.setProperty('right', '8px', 'important');
        
    
        // Enable click outside to close the div
        $(document).on('click', function(e) {
            // Check if the clicked element is not #check-if-registered-div or its children
            if (!$(e.target).closest('#check-if-registered-div').length) {
                $('#check-if-registered-btn').css('display', 'flex');
                $("#check-if-registered-div").css("right", "-550px");
    
                // Unbind the click event to avoid repeated binding
                $(document).off('click');
            } 
        });
    });
    
    // Prevent the click event from closing the div when clicking inside the div
    $('#check-if-registered-div').on('click', function(event) {
        event.stopPropagation();
    });

    $('#close-search-pat-btn').on('click', function(){
        $('#check-if-registered-btn').css('display', 'flex')
        $("#check-if-registered-div").css("right" , "-550px")
    }) 

    //***********************************************************************************************************************/

    let input_arr = document.querySelectorAll('.input-txt-classes')
    let all_non_req_input_arr = document.querySelectorAll('.input-txt-classes-non')
    let all_input_arr = document.querySelectorAll('.input-txt-classes-non, .input-txt-classes')
    let zero_inputs = 0;
    let data;

    $('#same-as-perma-btn').off('click', '#same-as-perma-btn').on('click' , function(event){
        document.querySelector('#hperson-house-no-ca').value = document.querySelector('#hperson-house-no-pa').value
        document.querySelector('#hperson-street-block-ca').value = document.querySelector('#hperson-street-block-pa').value
        document.querySelector('#hperson-region-select-ca').value = document.querySelector('#hperson-region-select-pa').value

        let province_element_ca = document.createElement('option')
        province_element_ca.value = $('#hperson-province-select-pa').val()
        province_element_ca.text =  $('#hperson-province-select-pa').find(':selected').text()
        document.querySelector('#hperson-province-select-ca').appendChild(province_element_ca);
        document.querySelector('#hperson-province-select-ca').value = province_element_ca.value

        let city_element_ca = document.createElement('option')
        city_element_ca.value = $('#hperson-city-select-pa').val()
        city_element_ca.text =  $('#hperson-city-select-pa').find(':selected').text()
        document.querySelector('#hperson-city-select-ca').appendChild(city_element_ca);
        document.querySelector('#hperson-city-select-ca').value = city_element_ca.value

        let brgy_element_ca = document.createElement('option')
        brgy_element_ca.value = $('#hperson-brgy-select-pa').val()
        brgy_element_ca.text =  $('#hperson-brgy-select-pa').find(':selected').text()
        document.querySelector('#hperson-brgy-select-ca').appendChild(brgy_element_ca);
        document.querySelector('#hperson-brgy-select-ca').value = brgy_element_ca.value
        
        document.querySelector('#hperson-home-phone-no-ca').value = document.querySelector('#hperson-home-phone-no-pa').value
        document.querySelector('#hperson-mobile-no-ca').value = document.querySelector('#hperson-mobile-no-pa').value
        document.querySelector('#hperson-email-ca').value = document.querySelector('#hperson-email-pa').value
    })
    

    let age_value = 0
    $('#hperson-birthday').on('input' , function(event){
        //converting of birthdate
        const timestamp = Date.parse( $('#hperson-birthday').val());
        const date = new Date(timestamp)
        let year = date.getFullYear()
        let month = date.getMonth() + 1
        month = month <= 9 ? "0" + month.toString() : month
        let day = (date.getDate() < 10) ? "0" + date.getDate().toString() : date.getDate().toString()

        //calculating the age based on day of birth
        const dateOfBirth = year.toString() + "-" + month.toString() + "-" + day.toString()
        const age = calculateAge(dateOfBirth);
        age_value = age
        document.querySelector('#hperson-age').value = age_value

        // document.querySelector('#hperson-gender').value = response[i].patsex
    })


    $('#add-patform-btn-id').off('click', '#add-patform-btn-id').on('click' , function(event){
        event.preventDefault();

        if($('#add-patform-btn-id').text() == 'Add'){
            zero_inputs = 0;

            // check if the required inputs have values , if no, border color = red.
            for(let i = 0; i < input_arr.length; i++){
                if($(input_arr[i]).val() === ""){
                    $('.input-txt-classes').eq(i).css('border' , '2px solid red')
                    zero_inputs += 1
                }
            }

            // zero_inputs = 0;
            if(zero_inputs >= 1){
                $('#modal-body').text('Please fill out the required fields.')
                $('#ok-modal-btn').text('OK')
                $('#modal-title').text('Warning')
                $('#yes-modal-btn').css('display' , 'none')
                $("#ok-modal-btn").css("margin-right", "0")
                const myModal = new bootstrap.Modal(document.getElementById('myModal_pat_reg'));
                myModal.show();
            }else{
                $('#modal-title').text('Warning')
                // $('#modal-icon').attr('class', 'fa-triangle-exclamation');
                $('#modal-body').text('Are you sure with the information?')
                $('#ok-modal-btn').text('No')

                $('#yes-modal-btn').text('Register');
                $('#yes-modal-btn').css('display' , 'block')

                const myModal = new bootstrap.Modal(document.getElementById('myModal_pat_reg'));
                myModal.show();
            }
        }else if($('#add-patform-btn-id').text() == 'Refer'){
            $('#modal-title').text('Warning')
            // $('#modal-icon').addClass('fa-triangle-exclamation')
            // $('#modal-icon').removeClass('fa-circle-check')
            $('#modal-body').text('Confirmation?')
            $('#ok-modal-btn').text('No')

            $('#yes-modal-btn').text('Confirm');
            $('#yes-modal-btn').css('display' , 'flex')

            const myModal = new bootstrap.Modal(document.getElementById('myModal_pat_reg'));
            myModal.show();
        }
    })

    $('#clear-patform-btn-id').off('click', '#clear-patform-btn-id').on('click' , function(event){
        if($('#clear-patform-btn-id').text() == "Cancel"){
            $('#modal-body').text('Are you sure you want to cancel the Referral?')
            $('#ok-modal-btn').text('No')

            $('#yes-modal-btn').text('Yes');

            $('#modal-title').text('Warning')

            $('#yes-modal-btn').css('display' , 'block')

            const myModal = new bootstrap.Modal(document.getElementById('myModal_pat_reg'));
            myModal.show();
            // $('#myModal').modal('show');

            for(let i = 0; i < all_input_arr.length; i++){
                $(all_input_arr[i]).css('pointer-events' , 'none')
                $(all_input_arr[i]).css('background' , '#cccccc')
            }
        }
        else{
            for(let i = 0; i < all_input_arr.length; i++){
                $(all_input_arr[i]).val('')
            }
        }
        
    })

    $('#yes-modal-btn').off('click', '#yes-modal-btn').on('click' , function(event){
        if($('#yes-modal-btn').text() == 'Yes'){

            $('#ok-modal-btn').text('Ok')
            $('#clear-patform-btn-id').text('Clear')

            $('#add-patform-btn-id').text('Add')
            $('#add-patform-btn-id').css('pointer-events', 'auto');
            $('#add-patform-btn-id').css('opacity', '1');
            $('#add-patform-btn-id').css('margin-left', '0');

            $('#classification-dropdown').css('display', 'none');

            $("#add-patform-btn-id").css('background-color' , '#0991b3')
            $("#add-patform-btn-id").hover(
                function() {
                  $(this).css('background-color', '#0e7590');
                },
                function() {
                  // Mouse leaves the element
                  $(this).css('background-color', '#0991b3'); // Reset to original color or specify a color
                }
            );
        
            for (var i = 0; i < all_input_arr.length; i++) {
                // Check if the current element's ID is not equal to 'hperson-hospital-no' and is in the targetIDs array
                if (all_input_arr[i].id !== 'hperson-hospital-no') {
                    $(all_input_arr[i]).val('');
                    $(all_input_arr[i]).css('pointer-events', 'auto');
                    $(all_input_arr[i]).css('background', 'white');
                }
            }
        }
        else if($('#yes-modal-btn').text() == 'Register'){
            const currentDateTime = new Date();
            const year = currentDateTime.getFullYear();
            const month = String(currentDateTime.getMonth() + 1).padStart(2, '0'); // Add leading zero
            const day = String(currentDateTime.getDate()).padStart(2, '0'); // Add leading zero
            const hours = String(currentDateTime.getHours()).padStart(2, '0'); // Add leading zero
            const minutes = String(currentDateTime.getMinutes()).padStart(2, '0'); // Add leading zero
            const seconds = String(currentDateTime.getSeconds()).padStart(2, '0'); // Add leading zero

            let created_at = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

            data = {
                //PERSONAL INFORMATIONS
                //initial idea is to fetch the last patient hpatcode from the database whenever the patient registration form clicked
                //16
                // hpercode : (Math.floor(Math.random() * 1000) + 1).toString(),
                hpatcode : $('#hpatcode-input').val(),
                patlast : $('#hperson-last-name').val(),
                patfirst : $('#hperson-first-name').val(),
                patmiddle : $('#hperson-middle-name').val(),
                patsuffix : ($('#hperson-ext-name').val()) ? $('#hperson-ext-name').val() : "N/A",
                pat_bdate : $('#hperson-birthday').val(),
                pat_age : $('#hperson-age').val(),
                patsex : $('#hperson-gender').val(),
                patcstat : $('#hperson-civil-status').val(), //accepts null = yes
                relcode : $('#hperson-religion').val(),
                
                pat_occupation :($('#hperson-occupation').val()) ? $('#hperson-occupation').val() : "N/A",
                natcode : $('#hperson-nationality').val(),
                pat_passport_no : ($('#hperson-passport-no').val()) ? $('#hperson-passport-no').val() : "N/A",
                hospital_code : $('#hperson-hospital-no').text(),
                phicnum : $('#hperson-phic').val(),
    
                //PERMANENT ADDRESS
                pat_bldg_pa : $('#hperson-house-no-pa').val(),
                hperson_street_block_pa: $('#hperson-street-block-pa').val(),
                pat_region_pa : $('#hperson-region-select-pa').val(),
                pat_province_pa : $('#hperson-province-select-pa').val(),
                pat_municipality_pa : $('#hperson-city-select-pa').val(),
                pat_barangay_pa : $('#hperson-brgy-select-pa').val(),
                pat_email_pa :($('#hperson-email-pa').val()) ? $('#hperson-email-pa').val() : "N/A",
                pat_homephone_no_pa : ($('#hperson-home-phone-no-pa').val()) ? $('#hperson-home-phone-no-pa').val() : 0,
                pat_mobile_no_pa : $('#hperson-mobile-no-pa').val(),
    
                //CURRENT ADDRESS
                pat_bldg_ca : $('#hperson-house-no-ca').val(),
                hperson_street_block_ca: $('#hperson-street-block-ca').val(),
                pat_region_ca : $('#hperson-region-select-ca').val(),
                pat_province_ca : $('#hperson-province-select-ca').val(),
                pat_municipality_ca : $('#hperson-city-select-ca').val(),
                pat_barangay_ca : $('#hperson-brgy-select-ca').val(),
                pat_email_ca :($('#hperson-email-ca').val()) ? $('#hperson-email-ca').val() : "N/A",
                pat_homephone_no_ca :($('#hperson-home-phone-no-ca').val()) ? $('#hperson-home-phone-no-ca').val() : 0,
                pat_mobile_no_ca : $('#hperson-mobile-no-ca').val(),
    
                // CURRENT WORKPLACE ADDRESS
                pat_bldg_cwa : $('#hperson-house-no-cwa').val() ? $('#hperson-house-no-cwa').val() : "N/A",
                hperson_street_block_pa_cwa: $('#hperson-street-block-cwa').val() ? $('#hperson-street-block-cwa').val() : "N/A",
                pat_region_cwa : $('#hperson-region-select-cwa').val() ? $('#hperson-region-select-cwa').val() : "N/A",
                pat_province_cwa : $('#hperson-province-select-cwa').val() ? $('#hperson-province-select-cwa').val() : "N/A",
                pat_municipality_cwa : $('#hperson-city-select-cwa').val() ? $('#hperson-city-select-cwa').val() : "N/A",
                pat_barangay_cwa : $('#hperson-brgy-select-cwa').val() ? $('#hperson-brgy-select-cwa').val() : "N/A",
                pat_namework_place : $('#hperson-workplace-cwa').val() ? $('#hperson-workplace-cwa').val() : "N/A",
                pat_landline_no : $('#hperson-ll-mb-no-cwa').val() ? $('#hperson-ll-mb-no-cwa').val() : "N/A",
                pat_email_cwa : $('#hperson-email-cwa').val() ? $('#hperson-email-cwa').val() : "N/A",
    
    
                // FOR OFW ONLY
                pat_emp_name : $('#hperson-emp-name-ofw').val() ? $('#hperson-emp-name-ofw').val() : "N/A",
                pat_occupation_ofw: $('#hperson-occupation-ofw').val() ? $('#hperson-occupation-ofw').val() : "N/A",
                pat_place_work : $('#hperson-place-work-ofw').val()? $('#hperson-place-work-ofw').val() : "N/A",
                pat_bldg_ofw : $('#hperson-house-no-ofw').val() ? $('#hperson-house-no-ofw').val() : "N/A",
                hperson_street_block_ofw : $('#hperson-street-ofw').val() ? $('#hperson-street-ofw').val() : "N/A",
                pat_region_ofw : $('#hperson-region-select-ofw').val() ? $('#hperson-region-select-ofw').val() : "N/A",
                pat_province_ofw : $('#hperson-province-select-ofw').val() ? $('#hperson-province-select-ofw').val() : "N/A",
                pat_city_ofw : $('#hperson-city-select-ofw').val() ? $('#hperson-city-select-ofw').val() : "N/A",
                pat_country_ofw : $('#hperson-country-select-ofw').val() ? $('#hperson-country-select-ofw').val() : "N/A",
                pat_office_mobile_no_ofw : $('#hperson-office-phone-no-ofw').val() ? $('#hperson-office-phone-no-ofw').val() : 0,
                pat_mobile_no_ofw : $('#hperson-mobile-no-ofw').val() ? $('#hperson-mobile-no-ofw').val() : 0,

                created_at : created_at,
            }   


            // data = {
            //     //PERSONAL INFORMATIONS
            //     //initial idea is to fetch the last patient hpatcode from the database whenever the patient registration form clicked
            //     //16
            //     // hpercode : (Math.floor(Math.random() * 1000) + 1).toString(),
            //     hpatcode : $('#hpatcode-input').val(),w
            //     patlast : "Test 0528C",
            //     patfirst : "Test 0528C",
            //     patmiddle : "Test 0528C",
            //     patsuffix : "N/A",
            //     pat_bdate : '2000-05-16',
            //     pat_age : 23,
            //     patsex : 'Male',
            //     patcstat :"Test 0528C", //accepts null = yes
            //     relcode : "Test 0528C",
                
            //     pat_occupation: "Test 0528C",
            //     natcode : "Test 0528C",
            //     pat_passport_no : "N/A",
            //     hospital_code : $('#hpatcode-input').val(),
            //     phicnum : 34252522535,
    
            //     //PERMANENT ADDRESS
            //     pat_bldg_pa : "Test 0528C",
            //     hperson_street_block_pa: "Test 0528C",
            //     pat_region_pa : '3',
            //     pat_province_pa : "308",
            //     pat_municipality_pa : '30804',
            //     pat_barangay_pa : '30804015',
            //     pat_email_pa :"N/A",
            //     pat_homephone_no_pa : 0,
            //     pat_mobile_no_pa : '09823425253',
    
            //     //CURRENT ADDRESS
            //     pat_bldg_ca : "Test 0528C",
            //     hperson_street_block_ca: "Test 0528C",
            //     pat_region_ca : '3',
            //     pat_province_ca : "308",
            //     pat_municipality_ca : '30804',
            //     pat_barangay_ca : '30804015',
            //     pat_email_ca :"N/A",
            //     pat_homephone_no_ca : 0,
            //     pat_mobile_no_ca : '09823425253',
    
            //     // CURRENT WORKPLACE ADDRESS
            //     pat_bldg_cwa : $('#hperson-house-no-cwa').val() ? $('#hperson-house-no-cwa').val() : "N/A",
            //     hperson_street_block_pa_cwa: $('#hperson-street-block-cwa').val() ? $('#hperson-street-block-cwa').val() : "N/A",
            //     pat_region_cwa : $('#hperson-region-select-cwa').val() ? $('#hperson-region-select-cwa').val() : "N/A",
            //     pat_province_cwa : $('#hperson-province-select-cwa').val() ? $('#hperson-province-select-cwa').val() : "N/A",
            //     pat_municipality_cwa : $('#hperson-city-select-cwa').val() ? $('#hperson-city-select-cwa').val() : "N/A",
            //     pat_barangay_cwa : $('#hperson-brgy-select-cwa').val() ? $('#hperson-brgy-select-cwa').val() : "N/A",
            //     pat_namework_place : $('#hperson-workplace-cwa').val() ? $('#hperson-workplace-cwa').val() : "N/A",
            //     pat_landline_no : parseInt($('#hperson-ll-mb-no-cwa').val()) ? $('#hperson-ll-mb-no-cwa').val() : "N/A",
            //     pat_email_cwa : $('#hperson-email-cwa').val() ? $('#hperson-email-cwa').val() : "N/A",
    

            //     // FOR OFW ONLY
            //     pat_emp_name : $('#hperson-emp-name-ofw').val() ? $('#hperson-emp-name-ofw').val() : "N/A",
            //     pat_occupation_ofw: $('#hperson-occupation-ofw').val() ? $('#hperson-occupation-ofw').val() : "N/A",
            //     pat_place_work : $('#hperson-place-work-ofw').val()? $('#hperson-place-work-ofw').val() : "N/A",
            //     pat_bldg_ofw : $('#hperson-house-no-ofw').val() ? $('#hperson-house-no-ofw').val() : "N/A",
            //     hperson_street_block_ofw : $('#hperson-street-ofw').val() ? $('#hperson-street-ofw').val() : "N/A",
            //     pat_region_ofw : $('#hperson-region-select-ofw').val() ? $('#hperson-region-select-ofw').val() : "N/A",
            //     pat_province_ofw : $('#hperson-province-select-ofw').val() ? $('#hperson-province-select-ofw').val() : "N/A",
            //     pat_city_ofw : $('#hperson-city-select-ofw').val() ? $('#hperson-city-select-ofw').val() : "N/A",
            //     pat_country_ofw : $('#hperson-country-select-ofw').val() ? $('#hperson-country-select-ofw').val() : "N/A",
            //     pat_office_mobile_no_ofw : parseInt($('#hperson-office-phone-no-ofw').val()) ? $('#hperson-office-phone-no-ofw').val() : 0,
            //     pat_mobile_no_ofw : parseInt($('#hperson-mobile-no-ofw').val()) ? $('#hperson-mobile-no-ofw').val() : 0,

            //     created_at : created_at,
            // }


            for(let i = 0; i < all_non_req_input_arr.length; i++){

                if($(all_non_req_input_arr[i]).val() === "" && $(all_non_req_input_arr[i]).prop('tagName').toLowerCase() !== 'span'){
                    $(all_non_req_input_arr[i]).val("N/A")
                }
                
            }


            $.ajax({
                url: '../SDN/add_patient_form.php',
                method: "POST",
                data:data,
                success: function(response){
                    if(response != "duplicate"){
                        $('#modal-title').text('Notification')
                        $('#modal-icon').attr('class', 'fa-solid fa-circle-check');
                        $('#modal-body').text('Successfully registered')
                        $('#ok-modal-btn').text('OK')
                        $("#ok-modal-btn").css("margin-right", "0")


                        $('#yes-modal-btn').text('Yes');
                        $('#yes-modal-btn').css('display' , 'none')

                        const myModal = new bootstrap.Modal(document.getElementById('myModal_pat_reg'));
                        myModal.show();

                        let targetSelectIDs = [
                            'hperson-region-select-pa',
                            'hperson-province-select-pa',
                            'hperson-city-select-pa',
                            'hperson-brgy-select-pa',
                            'hperson-region-select-ca',
                            'hperson-province-select-ca',
                            'hperson-city-select-ca',
                            'hperson-brgy-select-ca',
                            'hperson-region-select-cwa',
                            'hperson-province-select-cwa',
                            'hperson-city-select-cwa',
                            'hperson-brgy-select-cwa',
                            'hperson-region-select-ofw',
                            'hperson-province-select-ofw',
                            'hperson-city-select-ofw',
                            'hperson-brgy-select-ofw'
                        ];

                        for(let i = 0; i < all_input_arr.length; i++){
                            if(all_input_arr[i].id === "hperson-gender" || all_input_arr[i].id === "hperson-civil-status" || targetSelectIDs.includes(all_input_arr[i].id)){
                                all_input_arr[i].selectedIndex = 0;
                            }else if(all_input_arr[i].id !== 'hperson-hospital-no'){
                                all_input_arr[i].value = ""
                                all_input_arr[i].textContent = ""
                            }
                        }
                    }else{
                        $('#modal-title').text('Notification')
                        $('#modal-icon').attr('class', 'fa-solid fa-triangle-exclamation');
                        $('#modal-body').text('Duplicate Entry, Please check the Patient Registration')
                        $('#ok-modal-btn').text('OK')
                        $("#ok-modal-btn").css("margin-right", "0")


                        $('#yes-modal-btn').text('Yes');
                        $('#yes-modal-btn').css('display' , 'none')

                        const myModal = new bootstrap.Modal(document.getElementById('myModal_pat_reg'));
                        myModal.show();
                    }

                }
            })


            // setTimeout(function() {
            //     $('#modal-title').text('Successed')
            //     $('#modal-icon').removeClass('fa-triangle-exclamation')
            //     $('#modal-icon').addClass('fa-circle-check')
            //     $('#modal-body').text('Registered Successfully')

            //     $('#yes-modal-btn').addClass('hidden')
            //     $('#ok-modal-btn').text('Ok')
            //     const myModal = new bootstrap.Modal(document.getElementById('myModal_pat_reg'));
            //     myModal.show();
            //     // $('#myModal').modal('show');
            // }, 500);
            
        }
        else if($('#yes-modal-btn').text() == 'Confirm'){
            // check if may laman yung mga referring doctor
            if(doctor_list > 0){
                loadContent('../SDN/referral_form.php?type=' + $('#tertiary-case').val() + "&code=" + $('#hpercode-input').val())
            }else{
                // modal
                prompt_modal.show()
            }
        }
    })

    $('#ok-modal-btn').off('click', '#ok-modal-btn').on('click' , function(event){
        // if($('#ok-modal-btn').text() == 'No' && $('#clear-patform-btn-id').text() == "Cancel"){
        //     $('#add-patform-btn-id').removeClass('hidden')
        //     $('#clear-patform-btn-id').removeClass('hidden')
        // }
        // else if($('#ok-modal-btn').text() == 'OK' && $('#clear-patform-btn-id').text() == "Cancel"){
        //     $('#add-patform-btn-id').text('Refer')
        //     for(let i = 0; i < input_arr.length; i++){
        //         $(input_arr[i]).removeClass('border-2 border-red-600')
        //         $(input_arr[i]).addClass('border-2 border-[#bfbfbf]')
        //     }
        // }
    })
    
    $('#redirect-modal-btn').off('click', '#redirect-modal-btn').on('click' , () =>{
        window.location.href = "../SDN/setting.php";
    })

    // let classification_dd_counter = true
    // $('#classification-dropdown').on('click' , function(event){
    //     if(classification_dd_counter){
    //         $('#add-clear-btn-div').removeClass('mt-10')
    //         $('#add-clear-btn-div').addClass('mt-[70%]')

    //         classification_dd_counter = false
    //     }else{
    //         $('#add-clear-btn-div').addClass('mt-10')
    //         $('#add-clear-btn-div').removeClass('mt-[70%]')

    //         classification_dd_counter = true
    //     }
    // })

    

    // Use jQuery to handle the change event
    $("#classification-dropdown").change(function() {
        // Get the selected value using val()
        var selectedValue = $(this).val();
  
        // Display the selected value

        // let chosen_case = ""
        // switch(selectedValue){
        //     case 'er' : chosen_case = "ER"; break;
        //     case 'ob' : chosen_case = "OB"; break;
        //     case 'opd' : chosen_case = "OPD"; break;
        //     case 'pcr' : chosen_case = "PCR"; break;
        //     case 'toxicology' : chosen_case = "Toxicology"; break;
        //     // case 'er' : chosen_case = "ER";
        // }

        $("#add-patform-btn-id").css("pointer-events" , "auto")
        $("#add-patform-btn-id").css("opacity" , "1")
        $('#add-patform-btn-id').text('Refer')
        $('#tertiary-case').val(selectedValue)
      });

      $('#update-stat-select').change(function() {
        var selectedOption = $(this).val();
      });

      var checkPatientRegUniq_var;
      $('#check-pat-registration-btn').off('click', '#check-pat-registration-btn').on('click' , function() {
        $('#searching-btn').css('display','block')

        setTimeout(() => {
            // send ajax  here to fetch if may existing 
            $.ajax({
                url: '../SDN/checkPatientRegUniq.php',
                method: "POST",
                data:{
                    patlast : 'Test 0527',
                    patfirst : 'Test 0527',
                    patmiddle : 'Test 0527',
                    patsuffix : 'N/A',
                    patbdate : '2000-05-16'
                },
                dataType : 'JSON',
                success: function(response){
                    checkPatientRegUniq_var = response
                    $('#searching-btn').css('display','none')
                    $('#data-found-btn').css('display','block')
                    $('#data-found-i').removeClass('fa-circle-exclamation')
                    $('#data-found-i').addClass('fa-circle-check')
                    $('#data-found-i').css('color','#759577')

                    
                }
            })
            
        }, 2000);
      });
      

    $('#data-found-btn').off('click', '#data-found-btn').on('click' , function() {
        var parentElement = document.querySelector('#hperson-province-select-pa');
    
        while (parentElement.firstChild) {
            parentElement.removeChild(parentElement.firstChild);
        }
        
        //Personal Information
        $('#hpercode-input').val(checkPatientRegUniq_var[0].hpercode)
        document.querySelector('#hperson-last-name').value = checkPatientRegUniq_var[0].patlast    
        document.querySelector('#hperson-first-name').value = checkPatientRegUniq_var[0].patfirst
        document.querySelector('#hperson-middle-name').value = checkPatientRegUniq_var[0].patmiddle
        document.querySelector('#hperson-ext-name').value = checkPatientRegUniq_var[0].patsuffix

        //converting of birthdate
        const timestamp = Date.parse(checkPatientRegUniq_var[0].patbdate);
        const date = new Date(timestamp)
        let year = date.getFullYear()
        let month = date.getMonth() + 1
        month = month <= 9 ? "0" + month.toString() : month
        let day = (date.getDate() < 10) ? "0" + date.getDate().toString() : date.getDate().toString()
        document.querySelector('#hperson-birthday').value = year.toString() + "-" + month.toString() + "-" + day.toString()

        //calculating the age based on day of birth
        const dateOfBirth = year.toString() + "-" + month.toString() + "-" + day.toString()
        const age = calculateAge(dateOfBirth);
        document.querySelector('#hperson-age').value = age

        document.querySelector('#hperson-gender').value = checkPatientRegUniq_var[0].patsex


        let cstat = ""
        switch(checkPatientRegUniq_var[0].patcstat){
            case "1": cstat = "Single";break;
            case "2": cstat = "Married";break;
            case "3": cstat = "Divorced";break;
            case "4": cstat = "Widowed";break;
            default: break;
        }
        document.querySelector('#hperson-civil-status').value = cstat
        
        document.querySelector('#hperson-religion').value = checkPatientRegUniq_var[0].relcode
        
        document.querySelector('#hperson-occupation').value = (checkPatientRegUniq_var[0].pat_occupation) ? checkPatientRegUniq_var[0].pat_occupation : "N/A"
        document.querySelector('#hperson-nationality').value = (checkPatientRegUniq_var[0].natcode) ? checkPatientRegUniq_var[0].natcode : "N/A"
        document.querySelector('#hperson-passport-no').value = (checkPatientRegUniq_var[0].pat_passport_no) ? checkPatientRegUniq_var[0].pat_passport_no : "N/A"


            //Others
        
        document.querySelector('#hperson-hospital-no').value = parseInt((checkPatientRegUniq_var[0].hospital_code)) ? parseInt(checkPatientRegUniq_var[0].hospital_code) : 0
        document.querySelector('#hperson-phic').value = (checkPatientRegUniq_var[0].phicnum) ? checkPatientRegUniq_var[0].phicnum : "N/A"
        // document.querySelector('#hperson-nationality').value = (checkPatientRegUniq_var[0].natcode) ? checkPatientRegUniq_var[0].natcode : "N/A"


        // PERMANENT ADDRESS
        document.querySelector('#hperson-house-no-pa').value = checkPatientRegUniq_var[0].pat_bldg
        document.querySelector('#hperson-street-block-pa').value = checkPatientRegUniq_var[0].pat_street_block


        document.querySelector('#hperson-region-select-pa').value = checkPatientRegUniq_var[0].pat_region

        // create option element for the province select input
        let province_element = document.createElement('option')
        province_element.value = checkPatientRegUniq_var[0].pat_province;
        province_element.text =  checkPatientRegUniq_var[0].pat_province;
        document.querySelector('#hperson-province-select-pa').appendChild(province_element);
        document.querySelector('#hperson-province-select-pa').value = province_element.value
        

        // create option element for the city select input
        let city_element = document.createElement('option')
        city_element.value = checkPatientRegUniq_var[0].pat_municipality;
        city_element.text =  checkPatientRegUniq_var[0].pat_municipality;
        document.querySelector('#hperson-city-select-pa').appendChild(city_element);
        document.querySelector('#hperson-city-select-pa').value = city_element.value

            // create option element for the barangay select input
            let brgy_element = document.createElement('option')
            brgy_element.value = checkPatientRegUniq_var[0].pat_barangay;
            brgy_element.text =  checkPatientRegUniq_var[0].pat_barangay;
            document.querySelector('#hperson-brgy-select-pa').appendChild(brgy_element);
            document.querySelector('#hperson-brgy-select-pa').value = brgy_element.value

        document.querySelector('#hperson-home-phone-no-pa').value = checkPatientRegUniq_var[0].pat_homephone_no
        document.querySelector('#hperson-mobile-no-pa').value = checkPatientRegUniq_var[0].pat_mobile_no
        document.querySelector('#hperson-email-pa').value = checkPatientRegUniq_var[0].pat_email


        // CURRENT ADDRESS
        document.querySelector('#hperson-house-no-ca').value = checkPatientRegUniq_var[0].pat_curr_bldg
        document.querySelector('#hperson-street-block-ca').value = checkPatientRegUniq_var[0].pat_curr_street

        document.querySelector('#hperson-region-select-ca').value = checkPatientRegUniq_var[0].pat_curr_region

        // create option element for the province select input
        let province_element_ca = document.createElement('option')
        province_element_ca.value = checkPatientRegUniq_var[0].pat_curr_province;
        province_element_ca.text =  checkPatientRegUniq_var[0].pat_curr_province;
        document.querySelector('#hperson-province-select-ca').appendChild(province_element_ca);
        document.querySelector('#hperson-province-select-ca').value = province_element_ca.value
        

        // create option element for the city select input
        let city_element_ca = document.createElement('option')
        city_element_ca.value = checkPatientRegUniq_var[0].pat_curr_municipality;
        city_element_ca.text =  checkPatientRegUniq_var[0].pat_curr_municipality;
        document.querySelector('#hperson-city-select-ca').appendChild(city_element_ca);
        document.querySelector('#hperson-city-select-ca').value = city_element_ca.value

        // create option element for the barangay select input
        let brgy_element_ca = document.createElement('option')
        brgy_element_ca.value = checkPatientRegUniq_var[0].pat_curr_barangay;
        brgy_element_ca.text =  checkPatientRegUniq_var[0].pat_curr_barangay;
        document.querySelector('#hperson-brgy-select-ca').appendChild(brgy_element_ca);
        document.querySelector('#hperson-brgy-select-ca').value = brgy_element_ca.value


        document.querySelector('#hperson-home-phone-no-ca').value = checkPatientRegUniq_var[0].pat_curr_homephone_no
        document.querySelector('#hperson-mobile-no-ca').value = checkPatientRegUniq_var[0].pat_curr_mobile_no
        document.querySelector('#hperson-email-ca').value = checkPatientRegUniq_var[0].pat_email_ca

            
        // CURRENT WORKPLACE ADDRESS
        document.querySelector('#hperson-house-no-cwa').value = checkPatientRegUniq_var[0].pat_work_bldg
        document.querySelector('#hperson-street-block-cwa').value = checkPatientRegUniq_var[0].pat_work_street


        document.querySelector('#hperson-region-select-cwa').value = checkPatientRegUniq_var[0].pat_work_region

        let region_element_cwa = document.createElement('option')
        region_element_cwa.value = checkPatientRegUniq_var[0].pat_work_region;
        region_element_cwa.text =  checkPatientRegUniq_var[0].pat_work_region;
        document.querySelector('#hperson-region-select-cwa').appendChild(region_element_cwa);
        document.querySelector('#hperson-region-select-cwa').value = region_element_cwa.value

        // create option element for the province select input
        let province_element_cwa = document.createElement('option')
        province_element_cwa.value = checkPatientRegUniq_var[0].pat_work_province;
        province_element_cwa.text =  checkPatientRegUniq_var[0].pat_work_province;
        document.querySelector('#hperson-province-select-cwa').appendChild(province_element_cwa);
        document.querySelector('#hperson-province-select-cwa').value = province_element_cwa.value

        // create option element for the city select input
        let city_element_cwa = document.createElement('option')
        city_element_cwa.value = checkPatientRegUniq_var[0].pat_work_municipality;
        city_element_cwa.text =  checkPatientRegUniq_var[0].pat_work_municipality;
        document.querySelector('#hperson-city-select-cwa').appendChild(city_element_cwa);
        document.querySelector('#hperson-city-select-cwa').value = city_element_cwa.value

        // create option element for the barangay select input
        let brgy_element_cwa = document.createElement('option')
        brgy_element_cwa.value = checkPatientRegUniq_var[0].pat_work_barangay;
        brgy_element_cwa.text =  checkPatientRegUniq_var[0].pat_work_barangay;
        document.querySelector('#hperson-brgy-select-cwa').appendChild(brgy_element_cwa);
        document.querySelector('#hperson-brgy-select-cwa').value = brgy_element_cwa.value


        document.querySelector('#hperson-workplace-cwa').value = checkPatientRegUniq_var[0].pat_namework_place
        document.querySelector('#hperson-ll-mb-no-cwa').value = checkPatientRegUniq_var[0].pat_work_landline_no
        document.querySelector('#hperson-email-cwa').value = checkPatientRegUniq_var[0].pat_work_email_add

        // OFW
        document.querySelector('#hperson-emp-name-ofw').value = checkPatientRegUniq_var[0].ofw_employers_name
        document.querySelector('#hperson-occupation-ofw').value = checkPatientRegUniq_var[0].ofw_occupation
        document.querySelector('#hperson-place-work-ofw').value = checkPatientRegUniq_var[0].ofw_place_of_work
        document.querySelector('#hperson-house-no-ofw').value = checkPatientRegUniq_var[0].ofw_bldg
        document.querySelector('#hperson-street-ofw').value = checkPatientRegUniq_var[0].ofw_street

        document.querySelector('#hperson-region-select-ofw').value = checkPatientRegUniq_var[0].ofw_region
        document.querySelector('#hperson-province-select-ofw').value = checkPatientRegUniq_var[0].ofw_province
        document.querySelector('#hperson-city-select-ofw').value = checkPatientRegUniq_var[0].ofw_municipality
        document.querySelector('#hperson-country-select-ofw').value = checkPatientRegUniq_var[0].ofw_country

        document.querySelector('#hperson-office-phone-no-ofw').value = checkPatientRegUniq_var[0].ofw_office_phone_no
        document.querySelector('#hperson-mobile-no-ofw').value = checkPatientRegUniq_var[0].ofw_mobile_phone_no

         

        for(let j = 0; j < all_input_arr.length; j++){
            $(all_input_arr[j]).css('border' , '2px solid #bfbfbf')

            $(all_input_arr[j]).css('pointer-events' , 'none')
            $(all_input_arr[j]).css('background' , '#cccccc')
            // $(all_input_arr[j]).css('border' , '2px solid red')
        }
        console.log(checkPatientRegUniq_var[0].status)
        if(checkPatientRegUniq_var[0].status === null || checkPatientRegUniq_var[0].status === "Discharged"){
            $("#classification-dropdown").css('display' , 'block')   
        }else{
            // #0991b3 // #0e7590
            // #17a44f // #178140
            $("#add-patform-btn-id").css('background-color' , '#17a44f')
            $("#add-patform-btn-id").hover(
                function() {
                    $(this).css('background-color', '#178140');
                },
                function() {
                    // Mouse leaves the element
                    $(this).css('background-color', '#17a44f'); // Reset to original color or specify a color
                }
            );

            $("#add-patform-btn-id").css('pointer-events' , 'none')
            $("#classification-dropdown").css('display' , 'none')
        }

        $('#clear-patform-btn-id').text('Cancel')
        $('#clear-patform-btn-id').css('width' , '90px')
        $('#add-patform-btn-id').css('margin-left', '5%')
        $('#add-patform-btn-id').css('pointer-events', 'none')
        $('#add-patform-btn-id').css('opacity', '0.3')
    });

    // const mobileNumValue = (val) => {
    //     // Remove any non-numeric characters
    //     let value = $('.mobile-inputs-class').val().replace(/[^0-9]/g, '');

    //     if(val === 1){
    //         value = $("#sdn-hospital-mobile-no").val().replace(/[^0-9]/g, '');
    //     }else if(val === 2){
    //         value = $("#sdn-hospital-director-mobile-no").val().replace(/[^0-9]/g, '');
    //     }else if(val === 3){
    //         value = $("#sdn-point-person-mobile-no").val().replace(/[^0-9]/g, '');
    //     }


    //     // Add dashes at specific positions
    //     if (value.length >= 4) {
    //         value = value.slice(0, 4) + '-' + value.slice(4);
    //       }
    //     if (value.length >= 9) {
    //         value = value.slice(0, 9) + '-' + value.slice(9);
    //     }
    //     if (value.length > 13) {
    //         value = value.slice(0, 13);
    //     }
    //     if(val === 1){
    //         $("#sdn-hospital-mobile-no").val(value);
    //     }else if(val === 2){
    //         $("#sdn-hospital-director-mobile-no").val(value);
    //     }else if(val === 3){
    //         $("#sdn-point-person-mobile-no").val(value);
    //     }
    // }

    // $("#sdn-hospital-mobile-no").on("input", () => mobileNumValue(1))
    // $("#sdn-hospital-director-mobile-no").on("input", () => mobileNumValue(2))
    // $("#sdn-point-person-mobile-no").on("input", () => mobileNumValue(3))

    $('.mobile-inputs-class').on('input', function () {
        let value = $(this).val().replace(/[^0-9]/g, '');
        if (value.length >= 4) {
            value = value.slice(0, 4) + '-' + value.slice(4);
        }
        if (value.length >= 9) {
            value = value.slice(0, 9) + '-' + value.slice(9);
        }
        if (value.length > 13) {
            value = value.slice(0, 13);
        }
    
        $(this).val(value);  // Update the specific input field
    });

    $(".home-phone-inputs-class").on("input", function(){
        let value = $(this).val().replace(/[^0-9]/g, '');
        if (value.length >= 3) {
            value = value.slice(0, 3) + '-' + value.slice(3);
        }
        if (value.length > 8) {
            value = value.slice(0, 8);
        }
        $(this).val(value);
    })
})