@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-semibold mb-6">Users</h1>
    <form method="GET" action="{{ route('users.index') }}" class="mb-4">
        <input type="text" name="search" placeholder="Search users..." value="{{ request('search') }}" class="border rounded py-2 px-4">
        <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Search</button>
    </form>

    <table class="min-w-full bg-white border rounded shadow">
        <thead>
            <tr>
                <th class="border px-4 py-2">Name</th>
                <th class="border px-4 py-2">Age</th>
                <th class="border px-4 py-2">Gender</th>
                <th class="border px-4 py-2">Created At</th>
                <th class="border px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td class="border px-4 py-2 text-center">{{ $user->name }}</td>
                <td class="border px-4 py-2 text-center">{{ $user->age }}</td>
                <td class="border px-4 py-2 text-center">{{ $user->gender }}</td>
                <td class="border px-4 py-2 text-center">{{ $user->created_at }}</td>
                <td class="border px-4 py-2 text-center">
                    <form action="{{ route('users.destroy', $user) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 text-white py-2 px-4 rounded">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $users->links() }}
</div>
@endsection
