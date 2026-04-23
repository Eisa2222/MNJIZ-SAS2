
$(function () {
    const routeBaseName = window.routeBaseName || 'approval-workflow.advances';
    const approvalManager = ApprovalManager.initForPage(routeBaseName);

    if (window.location.hash) {
        $(`.nav-tabs a[href="${window.location.hash}"]`).tab('show');
    }
    $('.nav-tabs a').on('shown.bs.tab', e => {
        history.pushState(null, null, e.target.hash);
    });

    $(document).on('click', '.approval-action', function (e) {
        e.preventDefault();
        const requestId = $(this).data('id');
        const action = $(this).data('action');

        if (action === 'approve') {
            approvalManager.showApprovalConfirmation(requestId);
        } else if (action === 'revoke') {
            approvalManager.showRevokeConfirmation(requestId);
        }
    });

    $(document).on('click', '.reject-action', function (e) {
        e.preventDefault();
        const requestId = $(this).data('id');
        approvalManager.showRejectDialog(requestId);
    });
});
