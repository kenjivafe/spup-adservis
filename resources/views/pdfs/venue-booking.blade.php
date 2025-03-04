<x-pdf-layout>
    <br>
    <span> {{ $title }} </span> <br>
    <span style="font-weight: bold; font-size: 16px">Venue Booking #{{ $booking->id }} Details</span>
    <br>
    <br>
    <table style="width:100%">
        <tr>
          <th style="text-align: left; width: 50%" colspan="3">Facility/Function Room:</th>
          <th style="width: 1%"></th>
          <th style="text-align: left; width: 49%">Specifications (Arrangements/things needed):</th>
        </tr>
        <tr>
          <td style="text-align:left; border: 1px solid black; border-radius: 10px; padding: 10px; width: 50%" colspan="3">{{ $booking->venue->name }}</td>
          <th style="width: 1%"></th>
          <td style="text-align:left; border: 1px solid black; border-radius: 10px; padding: 10px; width: 49%" rowspan="5">{{ $booking->specifics ?? ' ' }}</td>
        </tr>
        <tr>
          <th style="text-align: left; width: 29%">Unit/Department:</th>
          <th style="width: 1%"></th>
          <th style="text-align: left; width: 20%">Participants:</th>
        </tr>
        <tr>
          <td style="text-align:left; width: 29%; border: 1px solid black; border-radius: 10px; padding: 10px">{{ $booking->unit->name }}</td>
          <th style="width: 1%"></th>
          <td style="text-align:left; width: 20%; border: 1px solid black; border-radius: 10px; padding: 10px">{{ $booking->participants }}</td>
        </tr>
        <tr>
          <th style="text-align: left; width: 50%" colspan="3">Person Responsible:</th>
        </tr>
        <tr>
          <td style="text-align:left; border: 1px solid black; border-radius: 10px; padding: 10px; width: 50%" colspan="3">{{ $booking->personResponsible->full_name }}</td>
        </tr>
        <tr>
          <th style="text-align: left; width: 50%" colspan="3">Purpose:</th>
          <th style="width: 1%"></th>
          <th style="text-align: left; width: 49%" colspan="3">Event starts at:</th>
        </tr>
        <tr>
          <td style="text-align:left; border: 1px solid black; border-radius: 10px; padding: 10px; width: 50%" colspan="3">{{ $booking->purpose }}</td>
          <th style="width: 1%"></th>
          <td style="text-align:left; border: 1px solid black; border-radius: 10px; padding: 10px; width: 49%">{{ date('h:i a, l, F j, Y', strtotime($booking->starts_at)) }}</td>
        </tr>
          <tr>
          <th style="text-align: left; width: 50%" colspan="3">Source of Fund:</th>
          <th style="width: 1%"></th>
          <th style="text-align: left; width: 49%" colspan="3">Event ends at:</th>
        </tr>
        <tr>
          <td style="text-align:left; border: 1px solid black; border-radius: 10px; padding: 10px; width: 50%" colspan="3">{{ $booking->fund_source }} Funds</td>
          <th style="width: 1%"></th>
          <td style="text-align:left; border: 1px solid black; border-radius: 10px; padding: 10px; width: 49%">{{ date('h:i a, l, F j, Y', strtotime($booking->ends_at)) }}</td>
        </tr>
      </table>

      <table style="width: 100%; margin-top: 12px">
        <tr>
            <th style="text-align: left; width: 13%">Noted by:</th>
            <th style="text-align: left; border-bottom: 1px solid black; width: 36%">{{ $booking->notedBy->full_name ?? ''}}</th>
            <td style="width: 1%"></td>
            <th style="text-align: left; width: 5%"></th>
            <th></th>
        </tr>
        </tr>
            <th></th>
            <td>
                <span style="text-align: left">(Department/Unit Head)</span>
                <span style="float: right">{{ $booking->noted_at ? date('m/d/Y h:i a', strtotime($booking->noted_at)) : '' }}</span>
            </td>
            <td style="width: 1%"></td>
            <th></th>
            <td></td>
        </tr>
        <tr>
            <th style="text-align: left; width: 13%">Approved by:</th>
            <th style="text-align: left; border-bottom: 1px solid black; width: 36%">{{ $booking->approvedBy->full_name ?? ''}}</th>
            <td style="width: 1%"></td>
            <th style="width: 5%"> – </th>
            <th style="text-align: left; border-bottom: 1px solid black;">{{ $booking->approvedByFinance->full_name ?? ''}}</th>
        </tr>
            <th></th>
            <td>
                <span style="text-align: left">(VP Admin)</span>
                <span style="float: right">{{ $booking->approved_at ? date('m/d/Y h:i a', strtotime($booking->approved_at)) : '' }}</span>
            </td>
            <td style="width: 1%"></td>
            <th></th>
            <td>
                <span style="text-align: left">(VP Finance)</span>
                <span style="float: right">{{ $booking->approved_by_finance_at ? date('m/d/Y h:i a', strtotime($booking->approved_by_finance_at)) : '' }}</span>
            </td>
        </tr>
        <tr>
            <th style="text-align: left; width: 13%">Received by:</th>
            <th style="text-align: left; border-bottom: 1px solid black; width: 36%">{{ $booking->receivedBy->full_name ?? ''}}</th>
            <td style="width: 1%"></td>
            <th style="text-align: left; width: 5%"></th>
            <th></th>
        </tr>
        </tr>
            <th></th>
            <td>
                <span style="text-align: left">(Facilitator)</span>
                <span style="float: right">{{ $booking->received_at ? date('m/d/Y h:i a', strtotime($booking->received_at)) : '' }}</span>
            </td>
            <td style="width: 1%"></td>
            <th></th>
            <td></td>
        </tr>
      </table>
</x-pdf-layout>
