<x-pdf-layout>
    <br>
    <span> {{ $title }} </span> <br>
    <span style="font-weight: bold; font-size: 16px">Job Order #{{ $jobOrder->id }} Details</span>
    <br>
    <br>
    <table style="width: 100%">
        <tr>
            <th style="text-align:left; width: 49%">Job Order Title:</th>
            <th style="width: 1%"></th>
            <th style="text-align:left">Date Requested:</th>
        </tr>
        <tr>
            <td style="text-align:left; width: 49%; border: 1px solid black; border-radius: 10px; padding: 10px">{{ $jobOrder->job_order_title }}</td>
            <td style="width: 1%"></td>
            <td style="text-align:left; border: 1px solid black; border-radius: 10px; padding: 10px">{{ date('l, F j, Y', strtotime($jobOrder->date_requested)) }}</td>
        </tr>
        <tr>
            <th style="text-align:left; width: 49%">Unit:</th>
            <th style="width: 1%"></th>
            <th style="text-align:left">Date Needed:</th>
        </tr>
        <tr>
            <td style="text-align:left; width: 49%; border: 1px solid black; border-radius: 10px; padding: 10px">{{ $jobOrder->requestedBy->unit->name }}</td>
            <td style="width: 1%"></td>
            <td style="text-align:left; border: 1px solid black; border-radius: 10px; padding: 10px">{{ date('l, F j, Y', strtotime($jobOrder->date_needed)) }}</td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 12px; border-spacing: 0; border-radius: 10px">
        <tr>
            <th style="
                text-align:left; width: 33%; padding:10px;
                border-left: 1px solid black; border-top: 1px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black;
                border-top-left-radius: 10px;">Particulars:</th>
            <th style="
                text-align:left; width: 33%; padding:10px;
                border-left: 0.5px solid black; border-top: 1px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black">Materials Needed:</th>
            <th style="
                text-align:left; width: 33%; padding:10px;
                border-left: 0.5px solid black; border-top: 1px solid black; border-right: 1px solid black; border-bottom: 0.5px solid black;
                border-top-right-radius: 10px;">Assigned to:</th>
        </tr>
        <tr style="height: 200px; border: 1px solid black">
            <td style="
                text-align:left; width: 33%; padding:10px;
                border-left: 1px solid black; border-top: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 1px solid black;
                border-bottom-left-radius: 10px;">{{ $jobOrder->particulars ?? '' }}</td>
            <td style="
                text-align:left; width: 33%; padding:10px;
                border-left: 0.5px solid black; border-top: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 1px solid black;">{{ $jobOrder->materials ?? '' }}</td>
            <td style="
                text-align:left; width: 33%; padding:10px;
                border-left: 0.5px solid black; border-top: 0.5px solid black; border-right: 1px solid black; border-bottom: 1px solid black;
                border-bottom-right-radius: 10px;">
                <b>{{ $jobOrder->assignedTo->full_name ?? '' }}</b>
                <p class="font-light">({{ $jobOrder->assignedTo?->roles->first()->name ?? 'Not Assigned Yet' }})</p>
                <br>
                <p class="font-light">Date Begun: {{ $jobOrder->date_begun ? date('l, F j, Y', strtotime($jobOrder->date_begun)) : 'N/A' }}</p>
                <p class="font-light">Date Finished: {{ $jobOrder->date_completed ? date('l, F j, Y', strtotime($jobOrder->date_completed)) :    'N/A' }}</p>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 12px">
        <tr>
            <th style="text-align: left; width: 13%">Requested by:</th>
            <th style="text-align: left; border-bottom: 1px solid black; width: 36%">{{ $jobOrder->requestedBy->full_name ?? ''}}</th>
            <td style="width: 1%"></td>
            <th style="text-align: left; width: 13%">Accomplished by:</th>
            <th style="text-align: left; border-bottom: 1px solid black;">{{ $jobOrder->accomplishedBy->full_name ?? ''}}</th>
        </tr>
        <tr>
            <th></th>
            <td>
                <span style="text-align: left">(Unit Head)</span>
                <span style="float: right">{{ $jobOrder->created_at ? date('m/d/Y h:i a', strtotime($jobOrder->created_at)) : '' }}</span>
            </td>
            <td style="width: 1%"></td>
            <th></th>
            <td>
                <span style="text-align: left">(Maintenance)</span>
                <span style="float: right">{{ $jobOrder->accomplished_at ? date('m/d/Y h:i a', strtotime($jobOrder->accomplished_at)) : '' }}</span>
            </td>
        </tr>
        <tr>
            <th style="text-align: left; width: 13%">Recommended by:</th>
            <th style="text-align: left; border-bottom: 1px solid black; width: 36%">{{ $jobOrder->recommendedBy->full_name ?? ''}}</th>
            <td style="width: 1%"></td>
            <th style="text-align: left; width: 13%">Checked by:</th>
            <th style="text-align: left; border-bottom: 1px solid black">{{ $jobOrder->checkedBy->full_name ?? ''}}</th>
        </tr>
        <tr>
            <th></th>
            <td>
                <span style="text-align: left">(Physical Plant/General Services Head)</span>
                <span style="float: right">{{ $jobOrder->recommended_at ? date('m/d/Y h:i a', strtotime($jobOrder->recommended_at)) : '' }}</span>
            </td>
            <td style="width: 1%"></td>
            <th></th>
            <td>
                <span style="text-align: left">(Physical Plant/General Services Head)</span>
                <span style="float: right">{{ $jobOrder->checked_at ? date('m/d/Y h:i a', strtotime($jobOrder->checked_at)) : '' }}</span>
            </td>
        </tr>
        <tr>
            <th style="text-align: left; width: 13%">Approved by:</th>
            <th style="text-align: left; border-bottom: 1px solid black; width: 36%">{{ $jobOrder->approvedBy->full_name ?? ''}}</th>
            <td style="width: 1%"></td>
            <th style="text-align: left; width: 13%">Confirmed by:</th>
            <th style="text-align: left; border-bottom: 1px solid black">{{ $jobOrder->confirmedBy->full_name ?? ''}}</th>
        </tr>
        <tr>
            <th></th>
            <td>
                <span style="text-align: left">(VP Admin)</span>
                <span style="float: right">{{ $jobOrder->approved_at ? date('m/d/Y h:i a', strtotime($jobOrder->approved_at)) : '' }}</span>
            </td>
            <td style="width: 1%"></td>
            <th></th>
            <td>
                <span style="text-align: left">(Unit Head)</span>
                <span style="float: right">{{ $jobOrder->confirmed_at ? date('m/d/Y h:i a', strtotime($jobOrder->confirmed_at)) : '' }}</span>
            </td>
        </tr>
    </table>
</x-pdf-layout>
