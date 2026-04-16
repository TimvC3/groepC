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
    <div class="cell bg-white border border-gray-300 rounded-xl p-6 text-center text-lg font-semibold cursor-pointer transition hover:bg-blue-100">1</div>
    <div class="cell bg-white border border-gray-300 rounded-xl p-6 text-center text-lg font-semibold cursor-pointer transition hover:bg-blue-100">2</div>
    <div class="cell bg-white border border-gray-300 rounded-xl p-6 text-center text-lg font-semibold cursor-pointer transition hover:bg-blue-100">3</div>
    <div class="cell bg-white border border-gray-300 rounded-xl p-6 text-center text-lg font-semibold cursor-pointer transition hover:bg-blue-100">4</div>
    <div class="cell bg-white border border-gray-300 rounded-xl p-6 text-center text-lg font-semibold cursor-pointer transition hover:bg-blue-100">5</div>
    <div class="cell bg-white border border-gray-300 rounded-xl p-6 text-center text-lg font-semibold cursor-pointer transition hover:bg-blue-100">6</div>
    <div class="cell bg-white border border-gray-300 rounded-xl p-6 text-center text-lg font-semibold cursor-pointer transition hover:bg-blue-100">7</div>
    <div class="cell bg-white border border-gray-300 rounded-xl p-6 text-center text-lg font-semibold cursor-pointer transition hover:bg-blue-100">8</div>
    <div class="cell bg-white border border-gray-300 rounded-xl p-6 text-center text-lg font-semibold cursor-pointer transition hover:bg-blue-100">9</div>
    <div class="cell bg-white border border-gray-300 rounded-xl p-6 text-center text-lg font-semibold cursor-pointer transition hover:bg-blue-100">10</div>
    <div class="cell bg-white border border-gray-300 rounded-xl p-6 text-center text-lg font-semibold cursor-pointer transition hover:bg-blue-100">11</div>
    <div class="cell bg-white border border-gray-300 rounded-xl p-6 text-center text-lg font-semibold cursor-pointer transition hover:bg-blue-100">12</div>
  </div>
</body>
</html>