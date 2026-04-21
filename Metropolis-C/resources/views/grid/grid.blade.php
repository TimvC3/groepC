<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('js/grid.js') }}" defer></script>
    <title>Grid</title>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
  <div id="grid" class="grid grid-cols-4 gap-3 max-w-md w-full">
    @foreach ($grid as $cell)
        <button type="button" class="cell bg-white border border-gray-300 rounded-lg p-6 text-center cursor-pointer" aria-pressed="false" data-testid="district-{{ $loop->iteration }}">
            {{$cell->happyness }}
        </button>
    @endforeach
  </div>
</body>
</html>