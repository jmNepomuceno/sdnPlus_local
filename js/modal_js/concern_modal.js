const dataTable_concerns_modal = () =>{
    try {
        $.ajax({
            url: '../../SDN/fetch_concerns.php',
            method: "POST",
            dataType : "json",
            success: function(response) {
                fetch_concernsData = response.concerns;  

                console.log(fetch_concernsData)
                try {
                    let dataSet = [];
                    for(let i = 0; i < response.concerns.length; i++){
                        const originalDate = response.concerns[i].concern_requestDate; // "2025-07-02 08:18:04"

                        const dateObj = new Date(originalDate);

                        const options = {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: true
                        };

                        const formattedDate = dateObj.toLocaleString('en-US', options);

                        console.log(formattedDate);

                        dataSet.push([
                            `<div><span>${response.concerns[i].concernID}</span></div>`,
                            `<div class="concern-by-td-div">
                                <span class="concern-by-name-td-div"><b>Name:</b> ${response.concerns[i].concern_requestor}</span>
                                <span class="concern-by-loc-td-div"><b>Location: </b> ${response.concerns[i].concern_loc}</span>
                            </div>`,
                            `<div>${formattedDate}</div>`,
                            `<button class="concern-action-btn btn btn-primary" data-role=${response.role}> View </button>`,
                        ]);
                    }  

                    if ($.fn.DataTable.isDataTable('#concerns-table')) {
                        $('#concerns-table').DataTable().destroy();
                        $('#concerns-table tbody').empty(); // Clear previous table body
                    }

                    $('#concerns-table').DataTable({
                        data: dataSet,
                        columns: [
                            { title: "REQUEST NO.", data:0 },
                            { title: "NAME OF REQUESTER", data:1 },
                            { title: "DATE REQUESTED", data:2 },
                            { title: "ACTION", data:3 },
                        ],
                        columnDefs: [
                            { targets: 0, createdCell: function(td) { $(td).addClass('item-req-no-td'); } },
                            { targets: 1, createdCell: function(td) { $(td).addClass('item-name-td'); } , width:"35%"},
                            { targets: 2, createdCell: function(td) { $(td).addClass('item-date-td'); } },
                            { targets: 3, createdCell: function(td) { $(td).addClass('item-action-td'); } },
                        ],
                        "autoWidth": false, // Prevents auto column sizing
                        // "paging": false,
                        // "info": false,
                        // "ordering": false,
                        // "stripeClasses": [],
                        // "searching": false,
                        
                    });

                    // **Set unique ID for each row after table initialization**
                    $('#concerns-table tbody tr').each(function(index) {
                        $(this).attr('class', `incoming-req-row-class`);
                    });

                } catch (innerError) {
                    console.error("Error processing response:", innerError);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX request failed:", error);
            }
        });
    } catch (ajaxError) {
        console.error("Unexpected error occurred:", ajaxError);
    }
}

$(document).ready(function(){
    dataTable_concerns_modal()

    $('#concerns-table tbody').on('click', '.concern-action-btn', function () {
        const tr = $(this).closest('tr');
        const row = $('#concerns-table').DataTable().row(tr);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            const concernID = tr.find('span').first().text();
            const userRole = $(this).data('role');  // ✅ get role from button

            // 🔷 Find full concern data
            const concernData = fetch_concernsData.find(item => item.concernID == concernID);
            console.log(concernData)

            const concernText = concernData.concern_txt || '(No concern text)';
            const existingResponse = concernData.concern_response; // can be null or empty

            let childContent = `
                <div style="padding: 10px;">
                    <strong>User Concern:</strong>
                    <p style="margin-top: 5px; color: #333;">${concernText}</p>
                    <hr>
            `;

            if (existingResponse && existingResponse.trim() !== '') {
                // Always show existing response if available
                childContent += `
                    <strong>Programmer's Response:</strong>
                    <p style="margin-top: 5px;">${existingResponse}</p>
                `;
            } else {
                if (userRole === 'admin') {
                    // Only admin sees textarea
                    childContent += `
                        <textarea class="form-control response-textarea" rows="3" placeholder="Type your response..."></textarea>
                        <button class="btn btn-sm btn-primary mt-2 submit-response-btn" data-id="${concernID}">Submit Response</button>
                    `;
                } else {
                    childContent += `
                        <em>No response yet from programmer.</em>
                    `;
                }
            }

            childContent += `</div>`;

            row.child(childContent).show();
            tr.addClass('shown');
        }
    });





    $('#concerns-table tbody').on('click', '.submit-response-btn', function () {
        const concernID = $(this).data('id');
        const textarea = $(this).siblings('.response-textarea');
        const responseText = textarea.val().trim();

        console.log(concernID, responseText)

        if (!responseText) {
            alert("Please enter a response.");
            return;
        }

        $.ajax({
            url: '../../SDN/submit_concern_response.php',
            method: 'POST',
            data: { concernID, responseText },
            success: function (res) {
                try {
                    const json = JSON.parse(res);
                    if (json.status === 'success') {
                        alert("Response saved!");
                        textarea.prop('disabled', true);
                        $(this).prop('disabled', true).text("Response Submitted");
                        dataTable_concerns_modal();
                    } else {
                        alert("Failed to save response.");
                    }
                } catch (e) {
                    console.error(e);
                    alert("Unexpected error.");
                }
            },
            error: function () {
                alert("Error connecting to server.");
            }
        });
    });

     $('#feedback-btn').off('click', '#feedback-btn').on('click' , function(event){  
        event.preventDefault();

        // feedback-textarea
        $.ajax({
            type: 'POST',
            url: '../../SDN/insert_feedback.php',
            data: {
                feedback: $('#feedback-textarea').val()
            },
            success: function(response) {
                console.log(response)
                // Handle success response
                $('#feedback-textarea').val(''); // Clear the textarea
                $('#feedback-btn').text('Feedback Sent!').prop('disabled', true);
                $('#feedback-btn').removeClass('btn-primary').addClass('btn-success');

                setTimeout(function() {
                    $('#feedback-btn').text('Submit Concern').prop('disabled', false);
                    $('#feedback-btn').removeClass('btn-success').addClass('btn-primary');
                }, 3000); // Reset button text after 3 seconds

                dataTable_concerns_modal();

            },
            error: function(xhr, status, error) {
                // Handle error response
                console.error('Error sending feedback:', error);

                $('#feedback-btn').text('Error! Try Again').prop('disabled', true);
                setTimeout(function() {
                    $('#feedback-btn').text('Submit Concern').prop('disabled', false);
                    $('#feedback-btn').removeClass('btn-success').addClass('btn-primary');

                }, 3000); // Reset button text after 3 seconds
            }
        });
    })

})