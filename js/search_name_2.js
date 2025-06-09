function calculateAge(dateOfBirth) {
    // Create a Date object for the current date
    const currentDate = new Date();

    // Parse the date of birth string into a Date object
    const dob = new Date(dateOfBirth);

    // Calculate the time difference in milliseconds
    const timeDiff = currentDate - dob;

    // Calculate the age in years
    const age = Math.floor(timeDiff / (1000 * 60 * 60 * 24 * 365.25));

    return age;
}

$(document).ready(function(){
    let input_arr = ['#hperson-last-name' , '#hperson-first-name' , '#hperson-middle-name' , ' #hperson-birthday' , '#hperson-gender' , '#hperson-civil-status' , 
                    '#hperson-religion' , '#hperson-nationality' , '#hperson-phic' , '#hperson-hospital-no' , '#hperson-house-no-pa' , '#hperson-street-block-pa' , '#hperson-region-select-pa' ,
                    '#hperson-region-select-pa' , '#hperson-province-select-pa' , '#hperson-city-select-pa' , '#hperson-brgy-select-pa' , '#hperson-mobile-no-pa',
                    '#hperson-house-no-ca' , '#hperson-street-block-ca' , '#hperson-region-select-ca' , '#hperson-province-select-ca' , 
                    '#hperson-city-select-ca' , '#hperson-brgy-select-ca' , '#hperson-mobile-no-ca']

    let all_non_req_input_arr = ['#hperson-ext-name' , '#hperson-age', '#hperson-occupation' , '#hperson-passport-no' , '#hperson-home-phone-no-pa' , '#hperson-email-pa',
                                '#hperson-email-ca' , '#hperson-home-phone-no-ca' , '#hperson-house-no-cwa' , '#hperson-street-block-cwa' , '#hperson-region-select-cwa' , 
                                '#hperson-province-select-cwa','#hperson-city-select-cwa', '#hperson-brgy-select-cwa' , '#hperson-workplace-cwa', '#hperson-ll-mb-no-cwa' , '#hperson-email-cwa',
                                '#hperson-emp-name-ofw','#hperson-occupation-ofw','#hperson-place-work-ofw','#hperson-house-no-ofw','#hperson-street-ofw','#hperson-region-select-ofw',
                                '#hperson-province-select-ofw','#hperson-city-select-ofw', '#hperson-country-select-ofw' , '#hperson-office-phone-no-ofw' , '#hperson-mobile-no-ofw']
    let all_input_arr = input_arr.concat(all_non_req_input_arr); 

    let if_clicked_same_perma = false;
    let global_breakdown_index = 0;
    const patHistoryModal = new bootstrap.Modal(document.getElementById('patHistoryModal'));

    function searchSubDiv() {
        const search_Sub_Div_elements = document.querySelectorAll('.search-sub-div');
          search_Sub_Div_elements.forEach(function(element, index) {
            element.addEventListener('click', function() {
                global_breakdown_index = index;

                const data = {
                    hpercode : $('.search-sub-code').eq(global_breakdown_index).text()
                }
                $.ajax({
                    url: '../SDN/populate_pat_form.php',
                    method: "POST",
                    data:data,
                    success: function(response){
                        response = JSON.parse(response);

                        // document.querySelector('#hperson-province-select-pa').innerHTML
                        var parentElement = document.querySelector('#hperson-province-select-pa');
    
                        while (parentElement.firstChild) {
                            parentElement.removeChild(parentElement.firstChild);
                        }
                        
                        //Personal Information
                        $('#hpercode-input').val(response[0].hpercode)
                        document.querySelector('#hperson-last-name').value = response[0].patlast    
                        document.querySelector('#hperson-first-name').value = response[0].patfirst
                        document.querySelector('#hperson-middle-name').value = response[0].patmiddle
                        document.querySelector('#hperson-ext-name').value = response[0].patsuffix

                        //converting of birthdate
                        const timestamp = Date.parse(response[0].patbdate);
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

                        document.querySelector('#hperson-gender').value = response[0].patsex


                        let cstat = ""
                        switch(response[0].patcstat){
                            case "1": cstat = "Single";break;
                            case "2": cstat = "Married";break;
                            case "3": cstat = "Divorced";break;
                            case "4": cstat = "Widowed";break;
                            default: break;
                        }
                        document.querySelector('#hperson-civil-status').value = response[0].patcstat
                        
                        document.querySelector('#hperson-religion').value = response[0].relcode
                        
                        document.querySelector('#hperson-occupation').value = (response[0].pat_occupation) ? response[0].pat_occupation : "N/A"
                        document.querySelector('#hperson-nationality').value = (response[0].natcode) ? response[0].natcode : "N/A"
                        document.querySelector('#hperson-passport-no').value = (response[0].pat_passport_no) ? response[0].pat_passport_no : "N/A"


                         //Others
                        
                        document.querySelector('#hperson-hospital-no').value = parseInt((response[0].hospital_code)) ? parseInt(response[0].hospital_code) : 0
                        document.querySelector('#hperson-phic').value = (response[0].phicnum) ? response[0].phicnum : "N/A"
                        // document.querySelector('#hperson-nationality').value = (response[0].natcode) ? response[0].natcode : "N/A"


                        // PERMANENT ADDRESS
                        document.querySelector('#hperson-house-no-pa').value = response[0].pat_bldg
                        document.querySelector('#hperson-street-block-pa').value = response[0].pat_street_block


                        document.querySelector('#hperson-region-select-pa').value = response[0].pat_region

                        // create option element for the province select input
                        let province_element = document.createElement('option')
                        province_element.value = response[0].pat_province;
                        province_element.text =  response[0].pat_province;
                        document.querySelector('#hperson-province-select-pa').appendChild(province_element);
                        document.querySelector('#hperson-province-select-pa').value = province_element.value
                        

                        // create option element for the city select input
                        let city_element = document.createElement('option')
                        city_element.value = response[0].pat_municipality;
                        city_element.text =  response[0].pat_municipality;
                        document.querySelector('#hperson-city-select-pa').appendChild(city_element);
                        document.querySelector('#hperson-city-select-pa').value = city_element.value

                         // create option element for the barangay select input
                         let brgy_element = document.createElement('option')
                         brgy_element.value = response[0].pat_barangay;
                         brgy_element.text =  response[0].pat_barangay;
                         document.querySelector('#hperson-brgy-select-pa').appendChild(brgy_element);
                         document.querySelector('#hperson-brgy-select-pa').value = brgy_element.value

                        document.querySelector('#hperson-home-phone-no-pa').value = response[0].pat_homephone_no
                        document.querySelector('#hperson-mobile-no-pa').value = response[0].pat_mobile_no
                        document.querySelector('#hperson-email-pa').value = response[0].pat_email


                        // CURRENT ADDRESS
                        document.querySelector('#hperson-house-no-ca').value = response[0].pat_curr_bldg
                        document.querySelector('#hperson-street-block-ca').value = response[0].pat_curr_street

                        document.querySelector('#hperson-region-select-ca').value = response[0].pat_curr_region

                        // create option element for the province select input
                        let province_element_ca = document.createElement('option')
                        province_element_ca.value = response[0].pat_curr_province;
                        province_element_ca.text =  response[0].pat_curr_province;
                        document.querySelector('#hperson-province-select-ca').appendChild(province_element_ca);
                        document.querySelector('#hperson-province-select-ca').value = province_element_ca.value
                        

                        // create option element for the city select input
                        let city_element_ca = document.createElement('option')
                        city_element_ca.value = response[0].pat_curr_municipality;
                        city_element_ca.text =  response[0].pat_curr_municipality;
                        document.querySelector('#hperson-city-select-ca').appendChild(city_element_ca);
                        document.querySelector('#hperson-city-select-ca').value = city_element_ca.value

                        // create option element for the barangay select input
                        let brgy_element_ca = document.createElement('option')
                        brgy_element_ca.value = response[0].pat_curr_barangay;
                        brgy_element_ca.text =  response[0].pat_curr_barangay;
                        document.querySelector('#hperson-brgy-select-ca').appendChild(brgy_element_ca);
                        document.querySelector('#hperson-brgy-select-ca').value = brgy_element_ca.value


                        document.querySelector('#hperson-home-phone-no-ca').value = response[0].pat_curr_homephone_no
                        document.querySelector('#hperson-mobile-no-ca').value = response[0].pat_curr_mobile_no
                        document.querySelector('#hperson-email-ca').value = response[0].pat_email_ca

                         
                        // CURRENT WORKPLACE ADDRESS
                        document.querySelector('#hperson-house-no-cwa').value = response[0].pat_work_bldg
                        document.querySelector('#hperson-street-block-cwa').value = response[0].pat_work_street


                        document.querySelector('#hperson-region-select-cwa').value = response[0].pat_work_region
                        if(document.querySelector('#hperson-region-select-cwa').value === ""){
                            $('#hperson-region-select-cwa').empty()

                            let region_element_cwa = document.createElement('option')
                            region_element_cwa.value = response[0].pat_work_region;
                            region_element_cwa.text =  response[0].pat_work_region;
                            document.querySelector('#hperson-region-select-cwa').appendChild(region_element_cwa);
                            document.querySelector('#hperson-region-select-cwa').value = region_element_cwa.value
                        }

                        // create option element for the province select input
                        let province_element_cwa = document.createElement('option')
                        province_element_cwa.value = response[0].pat_work_province;
                        province_element_cwa.text =  response[0].pat_work_province;
                        document.querySelector('#hperson-province-select-cwa').appendChild(province_element_cwa);
                        document.querySelector('#hperson-province-select-cwa').value = province_element_cwa.value
                        

                        // create option element for the city select input
                        let city_element_cwa = document.createElement('option')
                        city_element_cwa.value = response[0].pat_work_municipality;
                        city_element_cwa.text =  response[0].pat_work_municipality;
                        document.querySelector('#hperson-city-select-cwa').appendChild(city_element_cwa);
                        document.querySelector('#hperson-city-select-cwa').value = city_element_cwa.value

                        // create option element for the barangay select input
                        let brgy_element_cwa = document.createElement('option')
                        brgy_element_cwa.value = response[0].pat_work_barangay;
                        brgy_element_cwa.text =  response[0].pat_work_barangay;
                        document.querySelector('#hperson-brgy-select-cwa').appendChild(brgy_element_cwa);
                        document.querySelector('#hperson-brgy-select-cwa').value = brgy_element_cwa.value


                        document.querySelector('#hperson-workplace-cwa').value = response[0].pat_namework_place
                        document.querySelector('#hperson-ll-mb-no-cwa').value = response[0].pat_work_landline_no
                        document.querySelector('#hperson-email-cwa').value = response[0].pat_work_email_add

                        // OFW
                        document.querySelector('#hperson-emp-name-ofw').value = response[0].ofw_employers_name
                        document.querySelector('#hperson-occupation-ofw').value = response[0].ofw_occupation
                        document.querySelector('#hperson-place-work-ofw').value = response[0].ofw_place_of_work
                        document.querySelector('#hperson-house-no-ofw').value = response[0].ofw_bldg
                        document.querySelector('#hperson-street-ofw').value = response[0].ofw_street

                        document.querySelector('#hperson-region-select-ofw').value = response[0].ofw_region
                        document.querySelector('#hperson-province-select-ofw').value = response[0].ofw_province
                        document.querySelector('#hperson-city-select-ofw').value = response[0].ofw_municipality
                        document.querySelector('#hperson-country-select-ofw').value = response[0].ofw_country

                        document.querySelector('#hperson-office-phone-no-ofw').value = response[0].ofw_office_phone_no
                        document.querySelector('#hperson-mobile-no-ofw').value = response[0].ofw_mobile_phone_no

                        for(let j = 0; j < all_input_arr.length; j++){
                            $(all_input_arr[j]).css('border' , '2px solid #bfbfbf')

                            $(all_input_arr[j]).css('pointer-events' , 'none')
                            $(all_input_arr[j]).css('background' , '#cccccc')
                            // $(all_input_arr[j]).css('border' , '2px solid red')
                        }
                        
                        $("#classification-dropdown").css('display' , 'block') 

                        $('#clear-patform-btn-id').text('Cancel')
                        $('#clear-patform-btn-id').css('width' , '90px')
                        $('#add-patform-btn-id').css('margin-left', '5%')
                        $('#add-patform-btn-id').css('pointer-events', 'none')
                        $('#add-patform-btn-id').css('opacity', '0.3')
                        
                        // $('#privacy-reminder-div').css('display' , 'flex')
                        // $('#add-clear-btn-div').addClass('mt-10')
                    }
                })
            });
        });
    }

    $(document).on('keypress', function(event) {
        if (event.which === 13 || event.keyCode === 13) {
            $('#search-patient-btn').trigger('click');
        }
    });
    
    // $('#pagination-div').off('click', '.pagination a').on('click' , function(e) {
    $('#search-patient-btn').on('click' , function(event){
        event.preventDefault();

        const search_lname = document.querySelector('#search-lname').value
        const search_fname = document.querySelector('#search-fname').value
        const search_mname = document.querySelector('#search-mname').value

        const data = {
            search_lname: search_lname,
            search_fname: search_fname,
            search_mname: search_mname
        }



        const search_query_result = document.querySelector('#search-result-div')
        while (search_query_result.hasChildNodes()) {
            search_query_result.removeChild(search_query_result.firstChild);
            }


        let lname_has_value = search_lname != ""
        let fname_has_value = search_fname != ""
        let mname_has_value = search_mname != ""

        const insuf_input_h1 = document.createElement('h1')
        insuf_input_h1.id = 'insuf_input_h1'
        // insuf_input_h1.textContent = "Please input two(2) or more characters."
        document.querySelector('#search-result-div').appendChild(insuf_input_h1)

        if(lname_has_value && search_lname.length < 2){
            insuf_input_h1.textContent = "Please input two(2) or more characters."
        }else if(fname_has_value && search_fname.length < 2){
            insuf_input_h1.textContent = "Please input two(2) or more characters."
        }else if(mname_has_value && search_mname.length < 2){
            insuf_input_h1.textContent = "Please input two(2) or more characters."
        }else if(mname_has_value === false && lname_has_value === false && fname_has_value === false){
            insuf_input_h1.textContent = "Please input two(2) or more characters."
        }
        else{
            function loadPage(page) {
                data['page'] = page
                $.ajax({
                    url: '../SDN/search_name_2.php',
                    type: 'POST',
                    data: data,
                    dataType : 'JSON',
                    success: function(response) {

                        if(response.results != ''){
                            $('#search-result-div').html(response.results);
                            $('#pagination-div').html(response.pagination);

                            searchSubDiv();
                        }else{
                            $('#search-result-div').css('color' , 'white')
                            $('#search-result-div').text(
                                'No result found'
                            );
                        }
                        
                    }
                });
            }
            loadPage(1);
            $('#pagination-div').on('click', '.pagination a', function(e) {
                e.preventDefault();
                var page = $(this).data('page');
                loadPage(page);
            });
        }
    })

    function onClickReferralCase(index, response, newTemp_response){
        // css toggle for referral case buttons
        $('.ref-counter').eq(index - 1).css('opacity', '1')
        for(let i = 0; i < $('.ref-counter').length; i++){
            if(i != index - 1){
                $('.ref-counter').eq(i).css('opacity', '0.3')
            }
        }
        
        index = index - 1

        $('#info-input-lname').text(response[1].patlast)
        $('#info-input-fname').text(response[1].patfirst)
        $('#info-input-mname').text(response[1].patmiddle)
        $('#info-input-sname').text(response[1].patsuffix)
        $('#info-input-bdate').text(response[0].patbdate)
        $('#info-input-age').text(response[0].pat_age)
        $('#info-input-sex').text(response[0].patsex)
        $('#info-input-brgy').text(response[0].pat_curr_barangay)
        $('#info-input-city').text(response[0].pat_curr_municipality)
        $('#info-input-prov').text(response[0].pat_curr_province)
        $('#info-input-region').text(response[0].pat_curr_region)
        $('#info-input-email').text(response[0].pat_email)
        $('#info-input-mobile').text("0" + response[0].pat_mobile_no)
        $('#info-input-telephone').text(response[0].pat_homephone_no)
        $('#info-input-rec_at').text(response[0].created_at)
        $('#info-input-reg_at').text(response[0].hpatcode)

        $('#info-input-pat-type').text(newTemp_response[index].type)
        $('#info-input-icd-type').text(newTemp_response[index].icd_diagnosis)
        $('#info-input-pat-class').text(newTemp_response[index].pat_class)
        $('#info-input-ref-date').text(newTemp_response[index].date_time)
        $('#info-input-ref-by').text(newTemp_response[index].referred_by)
        $('#info-input-ref-to').text(newTemp_response[index].refer_to)
        $('#info-input-approve-time').text(newTemp_response[index].approved_time)
        $('#info-input-reason-ref').text(newTemp_response[index].reason)
        $('#info-input-approve-details').text(newTemp_response[index].approval_details)

        // #6aa37d
        $('#pat-ref-status-span').css('color' , '#619e75')
        $('#pat-ref-status-span').text(newTemp_response[index].status)
    }

    $('#search-result-div').on('click', '#pat-history', function() {
        const data = {
            hpercode : $('.search-sub-code').eq(global_breakdown_index).text()
        }

        $.ajax({
            url: '../SDN/fetch_pat_history.php',
            method: "POST",
            data:data,
            dataType: 'JSON',
            success: function(response){
                let referral_counter = response[response.length - 1].length
                
                patHistoryModal.show()

                // initial data, but with the first referral case
                $('#info-input-lname').text(response[1].patlast)
                $('#info-input-fname').text(response[1].patfirst)
                $('#info-input-mname').text(response[1].patmiddle)
                $('#info-input-sname').text(response[1].patsuffix)
                $('#info-input-bdate').text(response[0].patbdate)
                $('#info-input-age').text(response[0].pat_age)
                $('#info-input-sex').text(response[0].patsex)
                $('#info-input-brgy').text(response[0].pat_curr_barangay)
                $('#info-input-city').text(response[0].pat_curr_municipality)
                $('#info-input-prov').text(response[0].pat_curr_province)
                $('#info-input-region').text(response[0].pat_curr_region)
                $('#info-input-email').text(response[0].pat_email)
                $('#info-input-mobile').text("0" + response[0].pat_mobile_no)
                $('#info-input-telephone').text(response[0].pat_homephone_no)
                $('#info-input-rec_at').text(response[0].created_at)
                $('#info-input-reg_at').text(response[0].hpatcode)

                $('#info-input-pat-type').text(response[1].type)
                $('#info-input-icd-type').text(response[1].icd_diagnosis)
                $('#info-input-pat-class').text(response[1].pat_class)
                $('#info-input-ref-date').text(response[1].date_time)
                $('#info-input-ref-by').text(response[1].referred_by)
                $('#info-input-ref-to').text(response[1].refer_to)
                $('#info-input-approve-time').text(response[1].approved_time)
                $('#info-input-reason-ref').text(response[1].reason)
                $('#info-input-approve-details').text(response[1].approval_details)

                $('#pat-ref-status-span').css('color' , '#619e75')
                $('#pat-ref-status-span').text(response[1].status)

                $('#info-input-pat-discharge').text((response[1].discharged_time) ? response[1].discharged_time : "N/A")

                // generate the number of referral case
                let temp_response = response;
                const [, ...newTemp_response] = temp_response; // Skip the first element and collect the rest
                newTemp_response.pop()


                // remove all the current buttons elements
                const parentElement = document.querySelector('.num-refer-div');
                Array.from(parentElement.querySelectorAll('.ref-counter')).forEach(button => button.remove());

                for(let i = 1; i <= referral_counter; i++){
                    let elem = document.createElement('button')
                    elem.className = 'ref-counter'

                    const dateTimeString = newTemp_response[i - 1]['date_time'];
                    const datePart = dateTimeString.split(' ')[0];
                    const formattedDate = datePart.split('-').join('/');
                    elem.textContent = `#${i}. ${formattedDate}`

                    if(i === 1){
                        elem.style.opacity = '1'
                    }else{
                        elem.style.opacity = '0.5'
                    }
                    document.querySelector('.num-refer-div').appendChild(elem)
                    elem.addEventListener('click' , () => onClickReferralCase(i, response, newTemp_response))
                }

                // pat-ref-sub-div
 
                // if(response[1].status === 'Discharged'){
                //     const containerDiv = document.createElement('div');
                //     containerDiv.className = 'input-div';

                //     const label = document.createElement('label');
                //     label.className = 'input-title-lbl';
                //     label.textContent = 'Discharged Time';

                //     const input = document.createElement('input');
                //     input.type = 'text';
                //     input.className = 'info-inputs';
                //     input.id = 'info-input-discharged-time';
                //     input.value = 'ER';

                //     containerDiv.appendChild(label);
                //     containerDiv.appendChild(input);

                //     const motherDiv = document.querySelector('.pat-ref-sub-div');
                //     motherDiv.appendChild(containerDiv);

                //     $('#info-input-discharged-time').val(response[1].discharged_time)
                // }else{
                //     const motherDiv = document.querySelector('.pat-ref-sub-div');
                //     const inputToRemove = document.getElementById('info-input-discharged-time');
                //     if (inputToRemove) {
                //         motherDiv.removeChild(inputToRemove.parentNode); // Remove the parent container div
                //     }
                // }
            }
        })
    });
})