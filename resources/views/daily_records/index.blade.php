@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-semibold mb-6">Daily Records</h1>
    <table class="min-w-full bg-white border rounded shadow">
        <thead>
            <tr>
                <th class="border px-4 py-2">Date</th>
                <th class="border px-4 py-2">Male Count</th>
                <th class="border px-4 py-2">Female Count</th>
                <th class="border px-4 py-2">Male Avg Age</th>
                <th class="border px-4 py-2">Female Avg Age</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td class="border px-4 py-2 text-center">{{ $record->date }}</td>
                <td class="border px-4 py-2 text-center">{{ $record->male_count }}</td>
                <td class="border px-4 py-2 text-center">{{ $record->female_count }}</td>
                <td class="border px-4 py-2 text-center">{{ $record->male_avg_age }}</td>
                <td class="border px-4 py-2 text-center">{{ $record->female_avg_age }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
