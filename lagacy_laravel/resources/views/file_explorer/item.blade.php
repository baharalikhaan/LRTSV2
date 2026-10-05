@php
    $folderName = $item['name'];
    $isRoot = isset($item['relative_path']) && $item['relative_path'] === $folderName;
    $displayName = $folderName;
    $iconClass = 'fa-folder text-warning';
    $collapseId = 'collapse-' . md5($item['path']);

    if ($isRoot && isset($folderLabels[$folderName])) {
        $displayName = $folderLabels[$folderName]['label'];
        $iconClass = $folderLabels[$folderName]['icon'];
    }
@endphp

<li class="list-group-item border-0 px-1 py-1">
    @if ($item['type'] === 'folder')
        <div class="d-flex justify-content-between align-items-center">
            <span class="folder-toggle" role="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                aria-expanded="false" aria-controls="{{ $collapseId }}">
                <i class="fa-solid fa-plus-square me-2 toggle-icon"></i>
                <i class="fa-solid {{ $iconClass }} me-2"></i>
                <strong>{{ $displayName }}</strong>
            </span>

            <a href="{{ route('zip.folder', ['folder' => $item['path']]) }}" class="nav-link" title="Download ZIP">
                <i class="fa-solid fa-file-archive nav-icon text-teal"></i>
            </a>
        </div>

        @if (!empty($item['children']))
            <ul class="collapse mt-2 ms-4 nested" id="{{ $collapseId }}">
                @foreach ($item['children'] as $child)
                    @include('file_explorer.item', ['item' => $child, 'folderLabels' => $folderLabels])
                @endforeach
            </ul>
        @endif
    @else
        <div>
            @php
                $icon =
                    pathinfo($item['name'], PATHINFO_EXTENSION) === 'pdf'
                        ? 'fa-file-pdf text-danger'
                        : 'fa-file text-secondary';
            @endphp
            <a href="{{ route('zip.file', ['file' => $item['path']]) }}" class="nav-link" title="Download pdf"> <i
                    class="fa-solid {{ $icon }} me-2">


                </i>
                {{ $item['name'] }}

            </a>
        </div>
    @endif
</li>
