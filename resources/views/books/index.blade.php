<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books List</title>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            margin: 20px auto;
            max-width: 800px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .table th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .table tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    @if (session('success'))
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px;">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 10px;">
            {{ session('error') }}
        </div>
    @endif
    <div class="container">
        <h1>Books List</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Amount</th>
                    <th>owner</th>
                    <th>pay</th>
            
                </tr>
            </thead>
            <tbody>
                @foreach($books as $book)
                    <tr>
                        <td>{{ $book->id }}</td>
                        <td>{{ $book->title }}</td>
                        <td>{{ $book->author }}</td>
                        <td>{{ $book->amount }}</td>
                        <td>{{ $book->user->name }}</td>
                        <td>  <form action="{{ route('payment.purchase', $book->user->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="book_id" value="{{ $book->id }}">
                            <input type="hidden" name="amount" value="{{ $book->amount }}">
                            <input type="hidden" name=user_id value="{{ $book->user->id }}">
                            <button type="submit">Pay</button>
                        </form>
                        </td>
                       
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>