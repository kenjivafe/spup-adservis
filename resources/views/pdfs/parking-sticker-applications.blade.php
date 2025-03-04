<x-listpdf-layout>
    <br>
    <span> {{ $title }} </span> <br>
    <span style="font-weight: bold; font-size: 16px">{{ $status }}  {{ $department }}  Parking Sticker Applications {{ $date }}</span>
    <br>
    <br>
    <table style="border: 1px solid black; border-spacing: 0; border-radius:10px; width: 100%; padding: 10px; font-size: 12px ">
        <thead class="text-xs text-gray-700 uppercase">
            <tr>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    ID
                </th>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    Department
                </th>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    Plate Number
                </th>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    Vehicle Type
                </th>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    Applicant
                </th>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    Date Requested
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($applications as $application)
                <tr>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ $application->id }}
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ $application->department->name }}
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ $application->plate_number}}
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ $application->applicant->full_name }}
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ $application->vehicle->type }}
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ date('F j, Y', strtotime($application->created_at)) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-app-layout>
