{{-- Loaded on request (see the service provider): only the pages that show comments load them. --}}
@assets
    <link rel="stylesheet" href="{{ \Filament\Support\Facades\FilamentAsset::getStyleHref('commentions', 'kirschbaum-development/commentions') }}">
    <script type="module" src="{{ \Filament\Support\Facades\FilamentAsset::getScriptSrc('commentions-scripts', 'kirschbaum-development/commentions') }}"></script>
@endassets
