<?php
$c = file_get_contents('app/Http/Controllers/TaskController.php');
$c = preg_replace('/if \(\$request->hasFile\(\'image\'\)\) \{\s*if \(\$task->image_path\) \{\s*Storage::disk\(\'public\'\)->delete\(\$task->image_path\);\s*\}\s*\$validated\[\'image_path\'\] = \$request->file\(\'image\'\)->store\(\'tasks\', \'public\'\);\s*\}/s', 'if ($request->hasFile(\'attachments\')) {
    $attachmentPaths = $task->attachments ?? [];
    foreach ($request->file(\'attachments\') as $file) {
        $attachmentPaths[] = $file->store(\'tasks/attachments\', \'public\');
    }
    $validated[\'attachments\'] = $attachmentPaths;
}', $c);

$c = preg_replace('/if \(\$task->image_path\) \{\s*Storage::disk\(\'public\'\)->delete\(\$task->image_path\);\s*\}/s', 'if ($task->image_path) {
    Storage::disk(\'public\')->delete($task->image_path);
}
if (!empty($task->attachments)) {
    foreach ($task->attachments as $attachment) {
        Storage::disk(\'public\')->delete($attachment);
    }
}', $c);

file_put_contents('app/Http/Controllers/TaskController.php', $c);
echo "Done\n";
