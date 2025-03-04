<x-listpdf-layout>
    <br>
    <span> {{ $title }} </span> <br>
    <span style="font-weight: bold; font-size: 16px">{{ $status }}  {{ $venue }}  Bookings {{ $date }}</span>
    <br>
    <br>
    <table style="border: 1px solid black; border-spacing: 0; border-radius:10px; width: 100%; padding: 10px; font-size: 12px ">
        <thead class="text-xs text-gray-700 uppercase">
            <tr>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    ID
                </th>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    Purpose
                </th>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    Venue
                </th>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    Date Requested
                </th>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    Event Date
                </th>
                <th style="text-align: left; padding: 20px" scope="col" class="px-6 py-3">
                    Status
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bookings as $booking)
                <tr>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ $booking->id }}
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ $booking->purpose }}
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ $booking->venue->name }}
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ date('F j, Y', strtotime($booking->date_requested)) }}
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ date('F j, Y', strtotime($booking->starts_at)) }}
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: left; padding: 20px" class="px-6 py-4">
                        {{ $booking->status }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-app-layout>
