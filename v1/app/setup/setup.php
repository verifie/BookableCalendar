<?php
$folderPath = __DIR__;

if (is_dir($folderPath)) {
    if ($dh = opendir($folderPath)) {
        echo "<h2>Files in the folder:</h2>";
        echo "<ul>";
        while (($file = readdir($dh)) !== false) {
            if ($file != "." && $file != "..") {
                $filePath = $folderPath . "/" . $file;
                echo "<li><a href='$file' target='_blank'>$file</a></li>";
            }
        }
        echo "</ul>";
        closedir($dh);
    } else {
        echo "Unable to open the folder.";
    }
} else {
    echo "Folder does not exist.";
}
?>
