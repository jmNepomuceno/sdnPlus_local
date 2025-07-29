$(document).ready(function(){
    let guide_modal = new bootstrap.Modal(document.getElementById('guide-referral'));

    if(case_type === "OB"){
        $('.container .referral-body .third-part .left-side .left-sub-div-2').css('height' , '60%')
        $('.container .referral-body .third-part .left-side .left-sub-div-2').css('margin-top' , '10px')
    }else{
        $('.container .referral-body .third-part .left-side .left-sub-div-2').css('height' , '80%')
        $('.container .referral-body .third-part .left-side .left-sub-div-2').css('margin-top' , '30px')
    }

    const myModal = new bootstrap.Modal(document.getElementById('myModal-referral'));
    // myModal.show()
    var selectedValue_doctor = ""
    var selectedValue_doctor_mobile = ""
    const loadContent = (url) => {
        $.ajax({
            url:url,
            success: function(response){
                $('#container').html(response);
            }
        })
    }

    $("#referring-doctor-select").change(function() {
        // Get the selected value using val()
        selectedValue_doctor = $(this).val();
        selectedValue_doctor_mobile = $("#referring-doctor-select option:selected").attr('id');
        // Display the selected value
    });


    $('#submit-referral-btn-id').on('click' , function(event){
        var selectedValue = $('input[name="sensitive"]:checked').val();

        var sensitive_radios = document.querySelectorAll('input[name="sensitive"]');
        var sensitive_selected = true;

        for (var i = 0; i < sensitive_radios.length; i++) {
            if (sensitive_radios[i].checked) {
                sensitive_selected = false;
                break;
            }
        }

        // if(parseInt(pat_age_data) >= 18){
            
        // }

        
        if(sensitive_selected) {
            let data; 
            if(parseInt(pat_age_data) >= 18){
                data = {
                    type : $('#type-input').val(),
                    code : $('#code-input').val(),
    
                    refer_to : $('#refer-to-select').val(),
                    icd_diagnosis : $('#icd-select').val() == null ? "" : $('#icd-select').val(),
                    sensitive_case : $('input[name="sensitive_case"]:checked').val(),
                    phic_member : $('#phic-member-select').val(),
                    transport : $('#transport-select').val(),
                    // referring_doc : $('#referring-doc-input').val(),
                    referring_doc : selectedValue_doctor,
                    referring_doc_mobile : selectedValue_doctor_mobile,
    
                    complaint_history_input : $('#complaint-history-input').val(),
                    reason_referral_input : $('#reason-referral-input').val(),
                    diagnosis : $('#diagnosis').val(),
                    remarks : $('#remarks').val(),
    
                    bp_input : $('#bp-input').val(),
                    hr_input : $('#hr-input').val(),
                    rr_input : $('#rr-input').val(),
                    temp_input : $('#temp-input').val(),
                    weight_input : $('#weight-input').val(),
                    pe_findings_input : $('#pe-findings-input').val(),
                }
            }else{
                data = {
                    type : $('#type-input').val(),
                    code : $('#code-input').val(),
    
                    refer_to : $('#refer-to-select').val(),
                    sensitive_case : $('input[name="sensitive_case"]:checked').val(),
                    parent_guardian : $('#parent-guard-input').val(),
                    phic_member : $('#phic-member-select').val(),
                    transport : $('#transport-select').val(),
                    // referring_doc : $('#referring-doc-input').val(),
                    referring_doc : selectedValue_doctor,
                    referring_doc_mobile : selectedValue_doctor_mobile,
                    icd_diagnosis : $('#icd-select').val() == null ? "" : $('#icd-select').val(),

                    complaint_history_input : $('#complaint-history-input').val(),
                    reason_referral_input : $('#reason-referral-input').val(),
                    diagnosis : $('#diagnosis').val(),
                    remarks : $('#remarks').val(),
    
                    bp_input : $('#bp-input').val(),
                    hr_input : $('#hr-input').val(),
                    rr_input : $('#rr-input').val(),
                    temp_input : $('#temp-input').val(),
                    weight_input : $('#weight-input').val(),
                    pe_findings_input : $('#pe-findings-input').val(),
                }
            }
            

            // if(parseInt(pat_age_data) >= 18){
            //     delete data.parent_guardian
            // }

            if($('#type-input').val() === "OB"){
                data['fetal_heart_inp'] = $('#fetal-heart-inp').val()
                data['fundal_height_inp'] = $('#fundal-height-inp').val()
                data['cervical_dilation_inp'] = $('#cervical-dilation-inp').val()
                data['bag_water_inp'] = $('#bag-water-inp').val()
                data['presentation_ob_inp'] = $('#presentation-ob-inp').val()
                data['others_ob_inp'] = $('#others-ob-inp').val()
            }

            function areAllValuesFilled(obj) {
                for (const key in obj) {
                    if (obj.hasOwnProperty(key)) {
                        const value = obj[key];
            
                        // Covers: undefined, null, empty string, or string with only spaces
                        if (value === undefined || value === null || 
                            (typeof value === 'string' && !value.trim())) {
                            return false;
                        }
                    }
                }
                return true;
            }


            if (areAllValuesFilled(data)) {
            // if (true) {
                data['parent_guardian'] = $('#parent-guard-input').val() ? $('#parent-guard-input').val() : "N/A"
                $.ajax({
                    url: '../SDN/add_referral_form.php',
                    method: "POST",
                    data:data,
                    success: function(response){
                        console.log(response)
                        if(response === 'valid'){
                            $('#modal-title').text('Successed')
                            $('#modal-icon').removeClass('fa-triangle-exclamation')
                            $('#modal-icon').addClass('fa-circle-check')
                            $('#modal-body').text('Successfully Referred')
        
                            $('#yes-modal-btn').css('display' , 'none')
                            $('#ok-modal-btn').text('OK')
                            // $('#myModal').modal('show');
                            
                            
                            $('#ok-modal-btn').on('click' , function(event){
                                if($('#ok-modal-btn').text() == 'OK'){
                                    loadContent('../SDN/default_view2.php')
                                }
                            })
                        }else{
                            $('#modal-title').text('Error')
                            $('#modal-icon').removeClass('fa-triangle-exclamation')
                            $('#modal-icon').addClass('fa-circle-warning')
                            $('#modal-body').text('Patient is already referred.')
        
                            $('#yes-modal-btn').css('display' , 'none')
                            $('#ok-modal-btn').text('OK')
                            // $('#myModal').modal('show');
                            
                            
                            $('#ok-modal-btn').on('click' , function(event){
                                if($('#ok-modal-btn').text() == 'OK'){
                                    loadContent('../SDN/default_view2.php')
                                }
                            })
                        }
                        
                    }
                })
            } else {
                myModal.show();
            }

            
        }
        
    })

    $('#cancel-referral-btn-id').on('click' , function(){
        $('#modal-title').text('Warning')
        $('#modal-icon').attr('class', 'fa-solid fa-triangle-exclamation');
        $('#modal-body').text('Are you sure you want to cancel the referral?')
        $('#ok-modal-btn').text('No')

        $('#yes-modal-btn').css('display' , "block")
    })

    $('#yes-modal-btn').on('click' , () =>{
        if($('#yes-modal-btn').text() === 'Yes'){
            // window.location.href = "http://10.10.90.14:8079/index.php" 
            loadContent('../SDN/patient_register_form2.php')
        }
    })

    $(document).off('input', '.icd-inputs').on('input', '.icd-inputs', function(event) {
        const input_name = $(this).attr('name')
        const index = $('.icd-inputs').index(this);

        for(let i = 0; i < $('.icd-inputs').length; i++){
            if(i != index){
                $('.icd-inputs').eq(i).val("")
            }
        }

        $.ajax({
            url: '../SDN/icd_query.php',
            method: "POST", 
            data:{
                column : input_name,
                search_keyword : $(this).val()
            },
            dataType : 'json',
            success: function(response){
                // Reference to the <select> element you want to update
                let selectElement = $('#icd-select'); // Replace with the actual <select> element ID

                // Clear the existing options (except for a placeholder, if any)
                selectElement.empty();
                // selectElement.append('<option value="">Select an option</option>'); // Optional placeholder

                // Populate the select element with the new data
                response.forEach(item => {
                    selectElement.append('<option value="' + item.icd10_code + '">' + item.icd10_code + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + item.icd10_title + '</option>');
                });+

                // Optionally trigger the dropdown to be visible if needed (some frameworks need this)
                selectElement.show();
            }
        })
    })

    $('#guide-btn').on('click' , function(){
        $('#ok-modal-guide-btn').css('margin-right' , "-50px")

        guide_modal.show()
    })
})