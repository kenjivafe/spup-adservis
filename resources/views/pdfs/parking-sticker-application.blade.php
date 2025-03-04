<x-pdf-layout>
    <br>
    <span> {{ $title }} </span> <br>
    <span style="font-weight: bold; font-size: 16px">Parking Sticker Application #{{ $application->id }} Details</span>
    <br>
    <br>
    <table style="width:100%; border-spacing: 0">
        <tr>
            <th style="text-align: left; width: 48%" colspan="5">Name of Applicant</th>
            <th style="width: 2%"></th>
            <td style="text-align: left; vertical-align: top; width:50%; border: 1px solid black; border-radius: 10px; padding: 10px" rowspan="10">
                Sir/Madam:
                <br>
                <br>
                    &emsp; I respectfully apply for a SPUP vehicle entrance/parking sticker. I am willing to pay the amount of {{ $stickerCost }} for the sticker which is good for one school year and should be transferrable and that it is a privilege subject to present and future SPUP regulations. It may be withdrawn for cause by SPUP. The privilege to park in the designated areas is on a first come-first served basis. Parking may not be available during special occasions.
                <br>
                <br>
            </td>
        </tr>
        <tr>
            <td style="text-align: left; width: 48%; border: 1px solid black; border-radius: 10px; padding: 10px" colspan="5">{{ $application->applicant->full_name }} [{{ $application->applicant?->roles->first()->name }}]</td>
            <th></th>
        </tr>
        <tr>
            <th style="text-align: left; width: 48%" colspan="5">Contact No.</th>
            <th></th>
        </tr>
        <tr>
            <td style="text-align: left; width: 48%; border: 1px solid black; border-radius: 10px; padding: 10px" colspan="5">{{ $application->contact_number }}</td>
            <th></th>
        </tr>
        <tr>
            <th style="text-align: left; width: 48%" colspan="5">Parking Type</th>
            <th></th>
        </tr>
        <tr>
            <td style="text-align: left; width: 48%; border: 1px solid black; border-radius: 10px; padding: 10px" colspan="5">{{ $application->parking_type == 'full_parking' ? 'Full Parking' : 'Drop-Off' }}</td>
            <th></th>
        </tr>
        <tr>
            <th style="text-align: left; width: 48%" colspan="5">Department</th>
            <th></th>
        </tr>
        <tr>
            <td style="text-align: left; width: 48%; border: 1px solid black; border-radius: 10px; padding: 10px" colspan="5">{{ $application->department->name }}</td>
            <th></th>
        </tr>
        <tr>
            <th style="text-align: left; width: 16%">Vehicle Type</th>
            <th style="width: 2%"></th>
            <th style="text-align: left; width: 16%">Vehicle Color</th>
            <th style="width: 2%"></th>
            <th style="text-align: left; width: 16%">Plate No.</th>
            <th></th>
        </tr>
        <tr>
            <td style="text-align: left; width: 16%; border: 1px solid black; border-radius: 10px; padding: 10px">{{ $application->vehicle->type }}</td>
            <th style="width: 2%"></th>
            <td style="text-align: left; width: 16%; border: 1px solid black; border-radius: 10px; padding: 10px"><span style="  height: 15px; width: 100%; background-color: {{ $application->vehicle_color }}; border-radius: 50%; display: inline-block;"></span></td>
            <th style="width: 2%"></th>
            <td style="text-align: left; width: 16%; border: 1px solid black; border-radius: 10px; padding: 10px">{{ $application->plate_number }}</td>
            <th></th>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 20px">
        <tr>
            <th style="text-align: left; width: 13%"></th>
            <th style="text-align: left;"></th>
            <td style="width: 1%"></td>
            <th style="width: 13%">Approved by:</th>
            <th style="text-align: left; border-bottom: 1px solid black;">{{ $application->approver->full_name ?? ''}}</th>
        </tr>
            <th></th>
            <td>
                <span style="text-align: left"></span>
                <span style="float: right"></span>
            </td>
            <td style="width: 1%"></td>
            <th></th>
            <td>
                <span style="text-align: left">(VP Admin)</span>
                <span style="float: right">{{ $application->approved_at ? date('m/d/Y h:i a', strtotime($application->approved_at)) : '' }}</span>
            </td>
        </tr>
    </table>
</x-pdf-layout>
