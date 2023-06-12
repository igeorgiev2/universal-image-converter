<?php


function useImagick($directory, $from_file, $format)
{
    $converted_image_name = $directory . 'converted_' . pathinfo($from_file, PATHINFO_FILENAME) . '.' . $format;
    $imageImagick = new Imagick();
    $imageImagick->readImage($from_file);
    $imageImagick->setImageFormat(strtoupper($format));
    if ($imageImagick->writeImages($converted_image_name, false)) {
        $result = $_SERVER['SERVER_NAME'] . '/converter/' . $converted_image_name;
    } else
        $result = 'Error in conversion!';
    $imageImagick->clear();
    return $result;
}

// Delete all files older than ten minutes in the directory converted.
$time = 10 * 60; // 10 minutes in seconds
if (is_dir('converted/')) {
    if ($dh = opendir('converted/')) {
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' || $file != '..') {
                $filepath = 'converted/' . $file;
                if (filemtime($filepath) < (time() - $time)) {
                    unlink($filepath);
                }
            }
        }
        closedir($dh);
    }
}

if (isset($_FILES["file"])) {

    /* Get the name of the uploaded file and the output format */
    $file = $_FILES['file'];
    $file_name = $_FILES['file']['name'];
    $output_format = $_POST['outputFormat'];

    /* Sanitize the user input */
    $file_name = htmlspecialchars($file_name);
    $file_name = str_replace(' ', '', $file_name);
    $file_tmp = htmlspecialchars($file["tmp_name"]);
    $file_type = htmlspecialchars($file["type"]);
    $file_size = filter_var($file["size"], FILTER_SANITIZE_NUMBER_INT);

    /* Get the image format from the uploaded file */
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $file_mime = finfo_file($file_info, $file_tmp);

    if (substr($file_mime, 0, 5) != "image") {
        $response = array(
            'status' => 'Invalid file type!'
        );
        echo json_encode($response);
        exit();
    }

    /* Check file size */
    if ($file_size > 5000000) {
        $response = array(
            'status' => 'Invalid file size!'
        );
        echo json_encode($response);
        exit();
    }

    if (!in_array($output_format, array('xbm', 'avif', 'wbmp', 'heic', 'tga', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'pdf', 'psd'))) {
        $response = array(
            'status' => 'Invalid output format!'
        );
        echo json_encode($response);
        exit();
    }

    /* Choose where to save the uploaded file */
    $location = "converted/";

    /* Start conversion */
    try {

        $new_file_name = time() . "_" . $file_name;

        /* Save the uploaded file to the local filesystem */
        if (move_uploaded_file($file_tmp, $location . $new_file_name)) {
            if (in_array($output_format, array('xbm', 'avif', 'wbmp', 'heic', 'tga', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'pdf', 'psd'))) {
                $converted_image_url = useImagick($location, $location . $new_file_name, $output_format);
            }

            // Need additional work here. Install plugin like Potrace orlibmagickcore or https://imagemagick.org/script/magick-vector-graphics.php.
            if ($output_format == 'svg') {
                $converted_image_url = '';
            }

            $response = array(
                'status' => 'Success',
                'image_format' => $file_type,
                'output_format' => $output_format,
                'converted_image' => $converted_image_url
            );
            echo json_encode($response);
        } else {
            echo json_encode('Failure');
        }
    } catch (\Throwable $th) {
        $response = array(
            'status' => 'Error - ' . $th
        );
        echo json_encode($response);
    }

}

?>
