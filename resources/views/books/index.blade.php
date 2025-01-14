<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books List</title>
<!-- لینک به فایل CSS تولیدی -->
<link rel="stylesheet" href="{{ asset('build/assets/app-uLkYPzzH.css') }}">
<script src="{{ asset('build/assets/app-CvSG40sc.js') }}" defer></script>



</head>
<body class="bg-gray-100 font-sans">
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-blue-500 to-purple-500 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <strong class="text-lg">
                <a href="/dashboard" class="hover:underline">Dashboard</a>
            </strong>
        </div>
    </nav>

    <!-- Success and Error Messages -->
    <div class="container mx-auto mt-6">
        @if (session('success'))
            <div class="bg-green-500 text-white p-3 rounded mb-4 shadow">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-500 text-white p-3 rounded mb-4 shadow">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->has('payment'))
            <div class="bg-red-500 text-white p-3 rounded mb-4 shadow">
                {{ $errors->first('payment') }}
            </div>
        @endif
    </div>

    <!-- Books Table -->
    <div class="container mx-auto bg-white p-6 shadow-lg rounded-lg">
        <div class="mb-4">
            <h2 class="text-2xl font-bold text-gray-700">Available Books</h2>
            <p class="text-gray-500">Browse and purchase your favorite books below:</p>
        </div>
        <table class="w-full text-left border-collapse border border-gray-200 rounded-lg overflow-hidden">
            <thead class="bg-gradient-to-r from-gray-200 to-gray-300 text-gray-700">
                <tr>
                    <th class="px-4 py-3 text-sm font-semibold">ID</th>
                    <th class="px-4 py-3 text-sm font-semibold">Title</th>
                    <th class="px-4 py-3 text-sm font-semibold">Author</th>
                    <th class="px-4 py-3 text-sm font-semibold">Amount</th>
                    <th class="px-4 py-3 text-sm font-semibold">Owner</th>
                    <th class="px-4 py-3 text-sm font-semibold">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($books as $book)
                    <tr class="bg-gray-50 hover:bg-gray-100 transition">
                        <td class="px-4 py-3 border border-gray-200">{{ $book->id }}</td>
                        <td class="px-4 py-3 border border-gray-200">{{ $book->title }}</td>
                        <td class="px-4 py-3 border border-gray-200">{{ $book->author }}</td>
                        <td class="px-4 py-3 border border-gray-200">{{ $book->amount }} Toman</td>
                        <td class="px-4 py-3 border border-gray-200">{{ $book->user->name }}</td>
                        <td class="px-4 py-3 border border-gray-200">
                            <form action="{{ route('payment.purchase') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="book_id" value="{{ $book->id }}">
                                <input type="hidden" name="amount" value="{{ $book->amount }}">
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-blue-700 transition">
                                    Pay
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
