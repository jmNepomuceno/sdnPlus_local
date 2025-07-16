
<div class="modal fade" id="concernModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="modal-title" class="modal-title" id="exampleModalLabel">Comments and Concerns</h5>
                <button type="button" data-bs-dismiss="modal" aria-label="Close" style="color:white">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div id="modal-body" class="modal-body">

                <section class="user-feedback-section">
                    <h6 class="user-feedback-title"><i class="fas fa-comment-dots"></i> Feedback / Concerns</h6>
                    <form id="user-feedback-form">
                        <div class="form-group">
                            <textarea class="form-control" id="feedback-textarea" rows="4" placeholder="Let us know what you think..."></textarea>
                        </div>
                        <div class="text-end">
                            <button id="feedback-btn" type="submit" class="btn btn-primary mt-2">Submit Concern</button>
                        </div>
                    </form>
                    <!-- <span id="feedback-success-message">Successfully Submitted!</span> -->
                </section>

                 <div class="table-container">
                    <table id="concerns-table" class="display">
                        <thead>
                            <tr>
                                <th>REQUEST NO.</th>
                                <th>NAME OF REQUESTER</th>
                                <th>DATE REQUESTED</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>

                        <tbody>
                        
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>