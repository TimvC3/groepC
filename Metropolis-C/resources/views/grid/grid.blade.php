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
    <button type="button" class="cell bg-white border border-gray-300 rounded-lg p-6 text-center cursor-pointer" aria-pressed="false" data-testid="district-1">
        1
    </button>
    <button type="button" class="cell bg-white border border-gray-300 rounded-lg p-6 text-center cursor-pointer" aria-pressed="false" data-testid="district-2">
        2
    </button>
    <button type="button" class="cell bg-white border border-gray-300 rounded-lg p-6 text-center cursor-pointer" aria-pressed="false" data-testid="district-3">
        3
    </button>
    <button type="button" class="cell bg-white border border-gray-300 rounded-lg p-6 text-center cursor-pointer" aria-pressed="false" data-testid="district-4">
        4
    </button>
    <button type="button" class="cell bg-white border border-gray-300 rounded-lg p-6 text-center cursor-pointer" aria-pressed="false" data-testid="district-5">
        5
    </button>
    <button type="button" class="cell bg-white border border-gray-300 rounded-lg p-6 text-center cursor-pointer" aria-pressed="false" data-testid="district-6">
        6
    </button>
    <button type="button" class="cell bg-white border border-gray-300 rounded-lg p-6 text-center cursor-pointer" aria-pressed="false" data-testid="district-7">
        7
    </button>
    <button type="button" class="cell bg-white border border-gray-300 rounded-lg p-6 text-center cursor-pointer" aria-pressed="false" data-testid="district-8">
        8
    </button>
    <button type="button" class="cell bg-white border border-gray-300 rounded-lg p-6 text-center cursor-pointer" aria-pressed="false" data-testid="district-9">
        9
    </button>
    <button type="button" class="cell bg-white border border-gray-300 rounded-lg p-6 text-center cursor-pointer" aria-pressed="false" data-testid="district-10">
        10
    </button>
    <button type="button" class="cell bg-white border border-gray-300 rounded-lg p-6 text-center cursor-pointer" aria-pressed="false" data-testid="district-11">
        11
    </button>
    <button type="button" class="cell bg-white border border-gray-300 rounded-lg p-6 text-center cursor-pointer" aria-pressed="false" data-testid="district-12">
        12
    </button>
  </div>
</body>
</html>