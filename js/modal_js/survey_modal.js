function updateSurveyCounts() {
    $.ajax({
        url: '../../SDN/fetch_survey_counts.php',
        method: 'GET',
        dataType: 'json',
        success: function (data) {
            $('#approve-count').text(data.approve);
            $('#disapprove-count').text(data.disapprove);
        },
        error: function (xhr, status, error) {
            console.error('Failed to fetch survey counts:', error);
        }
    });
}

$(document).ready(function () {
    updateSurveyCounts();
    // Handle click on response
    $('.survey-response-btn').on('click', function () {
        const response = $(this).data('response'); // approve/disapprove
        const user = window.user_name || ''; // from session

        console.log(response, user)

        $.ajax({
            url: '../../SDN/submit_survey_response.php',
            type: 'POST',
            data: { response, user },
            success: function (res) {
                alert('Your feedback has been recorded. Thank you!');

                if (response.status === 'success') {
                    updateSurveyCounts(); // refresh counts
                }
            },
            error: function () {
                alert('Error saving your response. Please try again.');
            }
        });
    });
});
