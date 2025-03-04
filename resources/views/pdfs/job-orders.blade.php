<x-listpdf-layout>
    <br>
    <span> {{ $title }} </span> <br>
    <span style="font-weight: bold; font-size: 16px">{{ $status }}  {{ $unit }}  Job Orders {{ $date }}</span>
    <br>
    <br>
    <table style="border: 1px solid black; border-spacing: 0; border-radius:10px; width: 100%; padding: 10px; font-size: 12px ">
        <thead class="text-xs text-gray-700 uppercase">
            <tr>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    ID
                </th>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    Title
                </th>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    Unit
                </th>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    Date Requested
                </th>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    Date Needed
                </th>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    Status
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($jobOrders as $jobOrder)
                <tr>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ $jobOrder->id }}
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ $jobOrder->job_order_title }}
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ $jobOrder->requestedBy->unit->code}}
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ date('F j, Y', strtotime($jobOrder->date_requested)) }}
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ date('F j, Y', strtotime($jobOrder->date_needed)) }}
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ $jobOrder->status }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-app-layout>
