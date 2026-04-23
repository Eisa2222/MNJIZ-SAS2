
$(document).ready(function () {
    const routeBaseName = window.routeBaseName || 'approval-workflow.offers';
    const approvalManager = ApprovalManager.initForPage(routeBaseName);
    initializeTabs();
    setupEventHandlers(approvalManager);
});


function initializeTabs() {
    if (window.location.hash) {
        $(`.nav-tabs a[href="${window.location.hash}"]`).tab('show');
    }
    $('.nav-tabs a').on('shown.bs.tab', function (e) {
        history.pushState(null, null, e.target.hash);
    });
}


function setupEventHandlers(approvalManager) {
    $(document).on('click', '.approval-action', function (e) {
        e.preventDefault();
        const itemId = $(this).data('id');
        const action = $(this).data('action');
        handleApprovalAction(approvalManager, itemId, action);
    });

    $(document).on('click', '.reject-action', function (e) {
        e.preventDefault();

        const itemId = $(this).data('id');
        approvalManager.showRejectDialog(itemId);
    });
}


function handleApprovalAction(approvalManager, itemId, action) {
    switch (action) {
        case 'approve':
            approvalManager.showApprovalConfirmation(itemId);
            break;
        case 'revoke':
            approvalManager.showRevokeConfirmation(itemId);
            break;
        default:
            console.warn(`Unknown approval action: ${action}`);
    }
}
