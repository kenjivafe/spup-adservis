<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 14px;
        }
        .header, .footer {
            text-align: right;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .field {
            margin-bottom: 10px;
        }
        .field label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
        }
        .field span {
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="header">

        <h1>Job Order</h1>
    </div>
    <div class="section">
        <h2>General Information</h2>
        <div class="field">
            <label>Title:</label>
            <span>{{ $jobOrder->job_order_title }}</span>
        </div>
        <div class="field">
            <label>Unit Name:</label>
            <span>{{ $jobOrder->unit_name }}</span>
        </div>
        <div class="field">
            <label>Date Requested:</label>
            <span>{{ \Carbon\Carbon::parse($jobOrder->date_requested)->format('M j, Y') }}</span>
        </div>
        <div class="field">
            <label>Date Needed:</label>
            <span>{{ \Carbon\Carbon::parse($jobOrder->date_needed)->format('M j, Y') }}</span>
        </div>
    </div>
    <div class="section">
        <h2>Details</h2>
        <div class="field">
            <label>Particulars:</label>
            <span>{{ $jobOrder->particulars }}</span>
        </div>
        <div class="field">
            <label>Materials:</label>
            <span>{{ $jobOrder->materials }}</span>
        </div>
    </div>
    <div class="section">
        <h2>Assignment</h2>
        <div class="field">
            <label>Assigned To:</label>
            <span>{{ $jobOrder->assigned_to ? $jobOrder->assignedTo->full_name : '' }}</span>
        </div>
        <div class="field">
            <label>Date Begun:</label>
            <span>{{ $jobOrder->date_begun ? \Carbon\Carbon::parse($jobOrder->date_begun)->format('M j, Y') : '' }}</span>
        </div>
        <div class="field">
            <label>Date Completed:</label>
            <span>{{ $jobOrder->date_completed ? \Carbon\Carbon::parse($jobOrder->date_completed)->format('M j, Y') : '' }}</span>
        </div>
    </div>
    <div class="section">
        <h2>Approval Flow</h2>
        <div class="field">
            <label>Requested By:</label>
            <span>{{ $jobOrder->requested_by ? $jobOrder->requestedBy->full_name : '' }}</span>
        </div>
        <div class="field">
            <label>Recommended By:</label>
            <span>{{ $jobOrder->recommended_by ? $jobOrder->recommendedBy->full_name : '' }}</span>
        </div>
        <div class="field">
            <label>Approved By:</label>
            <span>{{ $jobOrder->approved_by ? $jobOrder->approvedBy->full_name : '' }}</span>
        </div>
        <div class="field">
            <label>Accomplished By:</label>
            <span>{{ $jobOrder->accomplished_by ? $jobOrder->accomplishedBy->full_name : '' }}</span>
        </div>
        <div class="field">
            <label>Checked By:</label>
            <span>{{ $jobOrder->checked_by ? $jobOrder->checkedBy->full_name : '' }}</span>
        </div>
        <div class="field">
            <label>Confirmed By:</label>
            <span>{{ $jobOrder->confirmed_by ? $jobOrder->confirmedBy->full_name : '' }}</span>
        </div>
    </div>
    <div class="footer">
    </div>
</body>
</html>
