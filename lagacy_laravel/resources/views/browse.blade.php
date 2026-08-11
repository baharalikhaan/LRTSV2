<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse</title>
    <!-- Include required libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
</head>
<body>
    <div id="folder-tree">
        <!-- Render your folder structure here -->
        <ul>
            @foreach ($folders as $folder)
                <li data-jstree='{ "opened" : true }'>
                    {{ $folder->name }}
                    @if (count($folder->subfolders) > 0)
                        <ul>
                            @foreach ($folder->subfolders as $subfolder)
                                <li>{{ $subfolder->name }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if (count($folder->files) > 0)
                        <ul>
                            @foreach ($folder->files as $file)
                                <li>{{ $file->name }}</li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Include your context menu plugin script here -->
    <script src="path/to/context-menu-plugin.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize jsTree
            $('#folder-tree').jstree();

            // Bind context menu to files and folders
            $('#folder-tree').on('contextmenu', 'li', function (e) {
                // Prevent the default context menu
                e.preventDefault();

                // Show your custom context menu here
                // Example: $('#context-menu').show();
            });
        });
    </script>
</body>
</html>
