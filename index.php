<?php include('inc/head.php'); ?>

<?php
const DIR = 'files';
const EDITABLE_EXTENSION = ['text/html', 'text/plain'];
const IMAGE_EXTENSION = ['image/png', 'image/gif', 'image/jpeg'];
/**
 * @param string $dirPath
 */
function displayFile(string $dirPath)
{
    echo '<ul>';
    $dir = opendir($dirPath);
    while ($file = readdir($dir)) {
        if (!in_array($file, ['.', '..'])) {
            if (isset($_GET['filePath']) && isset($_GET['fileName'])) {
                if ($file == $_GET['fileName'] && $dirPath == $_GET['filePath']) {
                    echo '<li><a class="actived" href="?filePath=' . $dirPath . '&fileName=' . $file . '">' . $file . "</a></li>";
                } else {
                    echo '<li><a href="?filePath=' . $dirPath . '&fileName=' . $file . '">' . $file . "</a></li>";
                }
            }else {
                echo '<li><a href="?filePath=' . $dirPath . '&fileName=' . $file . '">' . $file . "</a></li>";
            }
            if (is_dir($dirPath . '/' . $file)) {
                displayFile($dirPath . '/' . $file);
            }
        }
    }
    echo '</ul>';
}
/**
 * @param string $fullPath
 * @param string $content
 * @throws Exception
 */
function writeInFile(string $fullPath, string $content)
{
    if (file_exists($fullPath)) {
        $file = fopen($fullPath, 'w');
        fwrite($file, $content);
        fclose($file);
    } else {
        throw new \Exception('Fichier inexistant.');
    }
}
/**
 * @param string $fullPath
 * @throws Exception
 */
function deleteFile(string $fullPath)
{
    if (file_exists($fullPath)) {
        unlink($fullPath);
        header('Location: index.php');
        exit;
    } else {
        throw new \Exception('Impossible de supprimer: ' . $fullPath);
    }
}
/**
 * @param $dir
 * @return bool
 */
function deleteTree($dir)
{
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? deleteTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}
if (!empty($_POST)) {
    if (isset($_POST['submit'])) {
        try {
            writeInFile($_POST['fileFullPath'], $_POST['content']);
        } catch (\Exception $e) {
            echo 'Exception reçue: ' . $e->getMessage();
        }
    } elseif (isset($_POST['delete'])) {
        if (is_file($_POST['fileFullPath'])) {
            try {
                deleteFile($_POST['fileFullPath']);
            } catch (\Exception $e) {
                echo 'Exception reçue: ' . $e->getMessage();
            }
        } elseif (is_dir($_POST['fileFullPath'])) {
            try {
                deleteTree($_POST['fileFullPath']);
                header('Location: index.php');
                exit;
            } catch (\Exception $e) {
                echo 'Exception reçue: ' . $e->getMessage();
            }
        }
    }
}
displayFile(DIR);
?>
    <form action="" method="post" role="form">
        <?php
        if (!empty($_GET)) {
            $fileFullPath = $_GET['filePath'] . '/' . $_GET['fileName'];
            if (file_exists($fileFullPath)) {
                if (in_array(mime_content_type($fileFullPath), EDITABLE_EXTENSION)) {
                    $content = file_get_contents($fileFullPath);
                    ?>
                    <div class="form-group">
                        <textarea name="content" id="content" class="form-control" cols="30"><?= $content ?></textarea>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                    <?php
                } elseif (in_array(mime_content_type($fileFullPath), IMAGE_EXTENSION)) {
                    ?>
                    <div class="container-img">
                        <img class="img-responsive " src="<?= $fileFullPath ?>" alt="">
                    </div>
                    <?php
                }
            }
        }
        ?>
        <input type="hidden" name="fileFullPath" value="<?php if (isset($fileFullPath)) {
            echo $fileFullPath;
        } ?>">
        <button type="delete" name="delete" class="btn btn-danger">Delete</button>
    </form>

<?php include('inc/foot.php'); ?>