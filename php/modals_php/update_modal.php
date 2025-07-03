
<div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="modal-title" class="modal-title" id="exampleModalLabel">Updates and Patch Notes</h5>
                <button type="button" data-bs-dismiss="modal" aria-label="Close" style="color:white">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="modal-body" class="modal-body">
                <section class="update-section">
                    <h6 class="update-section-title"><i class="fas fa-bullhorn"></i> Latest Updates</h6>
                    <ul class="update-list">
                        <li><strong>[2025-06-09]</strong> Added filters for Turnaround Time, Start Date and End Date, and Sensitive Case in Incoming Referrals.</li>
                        <li><strong>[2025-06-09]</strong> Added a sub-module for MSS/WCPU to allow the creation of passwords for sensitive or confidential referral cases. (For later use.)</li>
                        <li><strong>[2025-06-11]</strong> Viewing all contact information of RHUs and District Hospitals (Hospital No., Hospital Director No., Point Person No.).</li>
                    </ul>
                </section>

                <section class="update-section">
                    <h6 class="update-section-title"><i class="fas fa-tools"></i> Ongoing Development</h6>
                    <ul class="update-list">
                        <li>Management of feedback and concerns.</li>
                        <li>MSS and WCPU module.</li>
                        <li>Pending Concerns.</li>
                    </ul>
                </section>

                <section class="update-section">
                    <!-- Minor Concerns --> 
                    <h6 class="update-section-title"><i class="fas fa-exclamation-circle"></i> Pending Concerns</h6>
                    <ul class="update-list">
                        <li> Easier viewing and copying of the Referral Form</li>
                        <li> Unstable or delayed updates for new incoming referrals</li>
                        <li> Ability to cancel referrals even after they have been approved</li>
                    </ul>

                    <!-- Major Modules -->
                    <h6 class="update-section-title mt-4"><i class="fas fa-puzzle-piece"></i> Major Pending Modules</h6>
                    <ul class="update-list">
                        <li> MSS and WCPU Collaboration Module for Sensitive/Confidential Cases</li>
                    </ul>
                </section>

                <section class="update-section">
                    <h6 class="update-section-title"><i class="fas fa-comment-dots"></i> Feedback / Concerns</h6>
                    <form id="update-feedback-form">
                        <div class="form-group">
                            <textarea class="form-control" id="feedback-textarea" rows="4" placeholder="Let us know what you think..."></textarea>
                        </div>
                        <div class="text-end">
                            <button id="feedback-btn" type="submit" class="btn btn-primary mt-2">Submit Concern</button>
                        </div>
                    </form>
                    <!-- <span id="feedback-success-message">Successfully Submitted!</span> -->
                </section>
            </div>

        </div>
    </div>
</div>