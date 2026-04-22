<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script
        src="https://cdn.jsdelivr.net/npm/@dragdroptouch/drag-drop-touch@latest/dist/drag-drop-touch.esm.min.js?autoload"
        type="module">
        </script>
    <script src="{{ asset('js/grid.js') }}" defer></script>
    <title>Grid</title>
</head>

<body class="bg-gray-100 min-h-screen">

  {{-- Hoofdcontainer --}}
  <div class="flex flex-col md:flex-row items-center md:items-start justify-center gap-8 p-6 md:p-12 max-w-7xl mx-auto">

    {{-- 1. Grid Sectie (Nu de "Hero") --}}
    <div class="w-full md:flex-1 flex justify-center">
      {{-- We verhogen max-w-sm naar max-w-2xl voor desktop --}}
      <div id="grid" class="grid grid-cols-4 gap-4 w-full max-w-md md:max-w-l">
        @for ($i = 1; $i <= 12; $i++)
          <button type="button" 
                  id="drop-zone-{{ $i }}"
                  class="cell flex items-center justify-center bg-white border-2 border-dashed border-gray-300 rounded-xl w-full aspect-square transition-all hover:border-blue-400 hover:bg-blue-50 shadow-sm" 
                  data-district="{{ $i }}">
            <span class="text-gray-400 font-bold text-lg md:text-3xl">{{ $i }}</span>
          </button>
        @endfor
      </div>
    </div>

    {{-- 2. Library Sectie (Nu een Sidebar) --}}
    <div class="library w-full md:w-80 bg-white p-6 rounded-2xl shadow-md border border-gray-200">
      <h2 class="text-xl font-bold mb-6 text-gray-800 text-center md:text-left">Library</h2>
      
      {{-- Mobiel: Scrollen | Desktop: Netjes 2 kolommen --}}
      <div class="flex flex-row md:grid md:grid-cols-2 gap-4 overflow-x-auto md:overflow-visible pb-4 md:pb-0 scrollbar-hide">
        @foreach($images as $img)
          <div class="flex-shrink-0 w-24 h-24 md:w-full md:aspect-square bg-gray-50 p-1 border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
            <img src="{{ asset('images/' . $img) }}" 
                 draggable="true" 
                 class="library-thumb w-full h-full object-cover rounded-md cursor-grab active:cursor-grabbing">
          </div>
        @endforeach
      </div>
    </div>

  </div>

</body>

</html>