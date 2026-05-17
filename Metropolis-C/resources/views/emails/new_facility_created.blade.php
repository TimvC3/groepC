<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; padding: 24px; }
        h1 { font-size: 20px; color: #1a1a1a; }
        .detail { margin: 8px 0; }
        .label { font-weight: bold; }
        table { border-collapse: collapse; margin-top: 16px; width: 100%; max-width: 400px; }
        th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; }
        th { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <h1>New Function Added</h1>

    <div class="detail"><span class="label">Name:</span> {{ $facility->name }}</div>
    <div class="detail"><span class="label">Category:</span> {{ $facility->category->name }}</div>
    @if($facility->icon)
        <div class="detail"><span class="label">Icon:</span> {{ $facility->icon }}</div>
    @endif
    <div class="detail"><span class="label">Added at:</span> {{ $facility->created_at->format('d M Y, H:i') }}</div>

    @if($facility->scores->isNotEmpty())
        <h2 style="font-size: 16px; margin-top: 24px;">Effect Scores</h2>
        <table>
            <thead>
                <tr>
                    <th>Effect Category</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach($facility->scores as $score)
                    <tr>
                        <td>{{ $score->category->name }}</td>
                        <td>{{ $score->score }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
