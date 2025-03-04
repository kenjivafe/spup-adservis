<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    @vite('resources/css/app.css')

    <style>
        .page-break {
            page-break-after: always;
        }

        {{ file_get_contents(public_path('css/app.css')) }}


        body {
            font-family: ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            color: #333;
            font-size: 14px;
        }

        @font-face {
            font-family: 'OldEnglish';
            src: url('/fonts/OLDENGL.TTF') format('truetype');
        }
    </style>
</head>
<body>
    <table style="width: 100%">
        <tr>
            <th style="color: green; text-align: left; width: 78%"><span>SPUP</span> <br> <span style="font-size: 30px">AdServIS</span></th>
            <td style="text-align:left; font-size: 13px">Date: {{ now()->format('l, F j, Y') }}</td>
        </tr>
        <tr>
            <th style="width: 78%"></th>
            <td style="text-align:left; font-size: 13px">Address: Tuguegarao City, Cagayan</td>
        </tr>
        <tr>
            <th style="font-family: 'OldEnglish', Fallback, sans-serif; text-align:left; width: 78%; font-size: 13px">St. Paul University Philippines</th>
            <td style="text-align:left; font-size: 13px">Phone: 396-1987 to 396-1994</td>
        </tr>
        <tr>
            <td style="text-align:left; width: 78%; font-size: 13px">Office of the Vice President for Administrative and General Services</td>
            <td style="text-align:left; font-size: 13px">Email: spupadmin@spup.edu.ph</td>
        </tr>
    </table>
    {{ $slot }}
</body>
</html>
