<?php
$c = file_get_contents('resources/views/tasks/show.blade.php');

$oldHTML = <<<'EOD'
        <!-- Image -->
        @if($task->image_path)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Task Image</h3>
                </div>
                <div class="p-6">
                    <img src="{{ asset('storage/' . $task->image_path) }}" alt="Task image" class="max-w-full h-auto rounded-lg">
                </div>
            </div>
        @endif
EOD;

$newHTML = <<<'EOD'
        <!-- Attachments -->
        @if(!empty($task->attachments) || $task->image_path)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Attachments</h3>
                </div>
                <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @if($task->image_path)
                        <div class="relative group border border-gray-200 dark:border-gray-700 rounded-lg p-2 flex items-center justify-center bg-gray-50 dark:bg-gray-800">
                            <a href="{{ asset('storage/' . $task->image_path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $task->image_path) }}" alt="Task image" class="max-h-32 object-contain rounded">
                            </a>
                        </div>
                    @endif
                    @if(!empty($task->attachments))
                        @foreach($task->attachments as $attachment)
                            @php
                                $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $attachment);
                                $filename = basename($attachment);
                            @endphp
                            <div class="relative group border border-gray-200 dark:border-gray-700 rounded-lg p-2 flex items-center justify-center bg-gray-50 dark:bg-gray-800">
                                <a href="{{ asset('storage/' . $attachment) }}" target="_blank" class="w-full flex flex-col items-center justify-center">
                                    @if($isImage)
                                        <img src="{{ asset('storage/' . $attachment) }}" alt="{{ $filename }}" class="max-h-32 object-contain rounded">
                                    @else
                                        <i data-lucide="file" class="w-12 h-12 text-gray-400 mb-2"></i>
                                    @endif
                                    <div class="mt-2 text-xs text-center text-gray-600 dark:text-gray-300 w-full truncate px-2" title="{{ $filename }}">{{ $filename }}</div>
                                </a>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endif
EOD;

$c = str_replace($oldHTML, $newHTML, $c);

file_put_contents('resources/views/tasks/show.blade.php', $c);
echo "Done show views\n";
