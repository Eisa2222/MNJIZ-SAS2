'use strict';

$(document).ready(function () {
    const routeBaseName = window.routeBaseName || 'approval-workflow.advances';
    const approvalManager = ApprovalManager.initForPage(routeBaseName);


    $(document).on('click', '.approval-action', function (e) {
        e.preventDefault();
        const itemId = $(this).data('id');
        const action = $(this).data('action');

        if (action === 'approve') {
            approvalManager.showApprovalConfirmation(itemId);
        } else if (action === 'revoke') {
            approvalManager.showRevokeConfirmation(itemId);
        }
    });

    $(document).on('click', '.reject-action', function (e) {
        e.preventDefault();
        const itemId = $(this).data('id');
        approvalManager.showRejectDialog(itemId);
    });
});
