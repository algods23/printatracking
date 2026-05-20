<?php
$c = file_get_contents('resources/views/tasks/create.blade.php');

$oldHTML = <<<'EOD'
            <!-- Image Upload -->
            <div>
                <label for="image" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Upload Image</label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center cursor-pointer hover:border-yellow-500 transition-colors" onclick="document.getElementById('image').click()">
                    <i data-lucide="image" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                    <p class="text-gray-600 dark:text-gray-400 mb-1">Click to upload or drag and drop</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500">PNG, JPG, GIF up to 2MB</p>
                    <input type="file" id="image" name="image" accept="image/*" class="hidden" onchange="previewImage(event)">
                </div>
                <div id="imagePreview" class="mt-4"></div>
                @error('image')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
EOD;

$newHTML = <<<'EOD'
            <!-- Attachments Upload -->
            <div>
                <label for="attachments" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Upload Files (Images, Layouts, etc.)</label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center cursor-pointer hover:border-yellow-500 transition-colors" onclick="document.getElementById('attachments').click()">
                    <i data-lucide="upload-cloud" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                    <p class="text-gray-600 dark:text-gray-400 mb-1">Click to upload multiple files or drag and drop</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500">PNG, JPG, PDF, PSD, AI, ZIP etc.</p>
                    <input type="file" id="attachments" name="attachments[]" multiple class="hidden" onchange="previewFiles(event)">
                </div>
                <div id="filePreview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                @error('attachments')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                @error('attachments.*')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
EOD;

$c = str_replace($oldHTML, $newHTML, $c);

$oldJS = <<<'EOD'
    function previewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('imagePreview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="max-h-48 rounded-lg">`;
            };
            reader.readAsDataURL(file);
        }
    }
EOD;

$newJS = <<<'EOD'
    function previewFiles(event) {
        const files = event.target.files;
        const preview = document.getElementById('filePreview');
        preview.innerHTML = '';
        
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML += `
                        <div class="relative group border border-gray-200 dark:border-gray-700 rounded-lg p-2 flex items-center justify-center bg-gray-50 dark:bg-gray-800">
                            <img src="${e.target.result}" alt="${file.name}" class="max-h-32 object-contain rounded">
                            <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs truncate p-1 rounded-b">${file.name}</div>
                        </div>`;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML += `
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-800 h-36">
                        <i data-lucide="file" class="w-8 h-8 text-gray-400 mb-2"></i>
                        <span class="text-xs text-center text-gray-600 dark:text-gray-300 w-full truncate" title="${file.name}">${file.name}</span>
                        <span class="text-[10px] text-gray-500 mt-1">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                    </div>`;
                lucide.createIcons();
            }
        });
    }
EOD;

$c = str_replace($oldJS, $newJS, $c);

file_put_contents('resources/views/tasks/create.blade.php', $c);
echo "Done views\n";
