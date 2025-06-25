if (typeof fetchIntervalId === 'undefined') {
    var fetchIntervalId = null;
}

$(document).ready(function(){
    $('#myDataTable').DataTable({
        "bSort": false,
        "paging": true, 
        "pageLength": 6, 
        "lengthMenu": [ [6, 10, 25, 50, -1], [6, 10, 25, 50, "All"] ],
    });

    var dataTable = $('#myDataTable').DataTable();
    $('#myDataTable thead th').removeClass('sorting sorting_asc sorting_desc');
    dataTable.search('').draw(); 


    $('#incoming-search-btn').off('click', '#incoming-search-btn').on('click' , function(event){        
        
        let valid_search = false;
        let elements = [
            $('#incoming-referral-no-search').val(), 
            $('#incoming-last-name-search').val(), 
            $('#incoming-first-name-search').val(),
            $('#incoming-middle-name-search').val(), 
            $('#incoming-type-select').val(),  
            $('#incoming-agency-select').val(),  
            $('#incoming-status-select').val(), 
            $('#incoming-startDate-search').val(), 
            $('#incoming-endDate-search').val(),
            $('#incoming-tat-select').val(), 
            $('#incoming-sensitive-select').val()
        ]

        for (let val of elements) {
            if (val && val !== 'default') {
                valid_search = true;
                break; // No need to continue looping if any field is valid
            }
        }

        if(valid_search){
            $('.loader').show(); 
            $('#incoming-clear-search-btn').css('opacity' , '1')
            $('#incoming-clear-search-btn').css('pointer-events' , 'auto')

            // find all status that is, sent already on the interdept or On-Process
            let hpercode_arr = []
            for(let i = 0; i < document.querySelectorAll('.pat-status-incoming').length; i++){
                let pat_stat = document.querySelectorAll('.pat-status-incoming')

                const str = pat_stat[i].textContent.trim(); // Trim to remove leading and trailing whitespace
                if (str && typeof str === 'string') {
                    const hasTwoSpaces = str.match(/^[^\s]*\s[^\s]*\s[^\s]*$/);; // Check if the string contains two consecutive spaces
                    if (hasTwoSpaces) {
                        hpercode_arr.push(document.querySelectorAll('.hpercode')[i].value)
                    } 
                }

                if(pat_stat[i].textContent === 'On-Process'){
                    hpercode_arr.push(document.querySelectorAll('.hpercode')[i].value)
                }

                if(pat_stat[i].textContent === 'Pending'){
                    hpercode_arr.push(document.querySelectorAll('.hpercode')[i].value)
                }
            }


            let data = {
                hpercode_arr : hpercode_arr,
                ref_no : $('#incoming-referral-no-search').val(),
                last_name : $('#incoming-last-name-search').val(),
                first_name : $('#incoming-first-name-search').val(),
                middle_name : $('#incoming-middle-name-search').val(),
                case_type : $('#incoming-type-select').val(),
                agency : $('#incoming-agency-select').val(),
                sensitive : $('#incoming-sensitive-select').val(),
                startDate : $('#incoming-startDate-search').val(),
                endDate : $('#incoming-endDate-search').val(),
                // startDate : "2025-05-15",
                // endDate : "2025-05-16",
                tat : $('#incoming-tat-select').val(),
                status : $('#incoming-status-select').val(),
                where : 'search',
                where_type : 'incoming'
            }

            console.log(data)

            $.ajax({
                url: '../SDN/census_search.php',
                method: "POST", 
                data:data,
                // dataType:'JSON',
                success: function(response){
                    // console.log(response)

                    dataTable.clear();
                    dataTable.rows.add($(response)).draw();

                    length_curr_table = $('.tr-incoming').length
                    // for(let i = 0; i < length_curr_table; i++){
                    //     toggle_accordion_obj[i] = true
                    // }

                    const expand_elements = document.querySelectorAll('.accordion-btn');
                    expand_elements.forEach(function(element, index) {
                        element.addEventListener('click', function() {
                            // console.log(index)
                            global_breakdown_index = index;
                        });
                    });
                },
                complete: function () {
                  $('.loader').hide(); // Hide loader whether success or error
                }

            }) 
        }else{
            $('#modal-body-incoming').text('Invalid Search')
            $('#ok-modal-btn-incoming').text('Close')
            $('#ok-modal-btn-incoming').css('margin-right' , '0')
            $('#yes-modal-btn-incoming').css('display' , 'none')
            defaultMyModal.show()
        }

    })

    $('#incoming-clear-search-btn').off('click', '#incoming-clear-search-btn').on('click' , () =>{
        $.ajax({
            url: '../SDN/incoming_search.php',
            method: "POST", 
            data:{
                'where' : "clear"
            },
            success: function(response){
                // inactivityInterval = 10000
                // startInactivityTimer();
                handleUserInactivity()
                startContinuousFetch()
                $('#incoming-clear-search-btn').css('opacity' , '0.3')
                $('#incoming-clear-search-btn').css('pointer-events' , 'none')

                dataTable.clear();
                dataTable.rows.add($(response)).draw();

                length_curr_table = $('.tr-incoming').length
                for(let i = 0; i < length_curr_table; i++){
                    toggle_accordion_obj[i] = true
                }

                $('#incoming-referral-no-search').val("")
                $('#incoming-last-name-search').val("")
                $('#incoming-first-name-search').val("")
                $('#incoming-middle-name-search').val("")
                $('#incoming-type-select').val("")
                $('#incoming-agency-select').val("")
                $('#incoming-status-select').val("default")

                const expand_elements = document.querySelectorAll('.accordion-btn');
                expand_elements.forEach(function(element, index) {
                    element.addEventListener('click', function() {
                        // console.log(index)
                        global_breakdown_index = index;
                    });
                });

                enabledNextReferral()

                
            }
        }) 
    })

    $(document).on('click', '.toggle-contact-btn', function () {
        var $btn = $(this);
        var $row = $btn.closest('tr');

        $row.find('.contact-extra').slideToggle(200);

        // Optionally change button text
        if ($btn.text() === "More Details") {
            $btn.text("Less Details");
        } else {
            $btn.text("More Details");
        }
    });
})