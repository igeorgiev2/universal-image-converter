<?php

function customSearch($keyword, $arrayToSearch) {
    foreach ($arrayToSearch as $key => $arrayItem) {
        if (stristr($arrayItem, $keyword)) {
            return $key;
        }
    }
}

function useImagick($directory, $from_file, $format) {
    $converted_image_name = $directory . 'converted_' . pathinfo($from_file, PATHINFO_FILENAME) . '.' . $format;
    $imageImagick = new Imagick();
    $imageImagick->readImage($from_file);
    $imageImagick->setImageFormat(strtoupper($format));
    if ($imageImagick->writeImages($converted_image_name, false)) {
        $result = $_SERVER['SERVER_NAME'] . '/converter/' . $converted_image_name;
    } else $result = 'Error in conversion!';
    $imageImagick->clear();
    return $result;
}


// Deletes all files older than ten minutes in the directory converted.
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
    
    if (!in_array($output_format, array('xbm', 'avif', 'wbmp', 'heic', 'tga', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'pdf'))) {
        $response = array(
            'status' => 'Invalid output format!'
        );
        echo json_encode($response);
         exit();
    }

    /* Choose where to save the uploaded file */
    $location = "converted/";

    /* Сtart converсion */
    try {
        
        $new_file_name = time() . "_" . $file_name;
         
        /* Save the uploaded file to the local filesystem */
        if (move_uploaded_file($file_tmp, $location . $new_file_name)) {
            
            // Convert from SVG file using Imagick module.
            if (substr($file_mime, 6, 3) == 'svg') {
                if (in_array($output_format, array('xbm', 'avif', 'wbmp', 'heic', 'tga', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'pdf'))) {
                    $converted_image_url = useImagick($location, $location . $new_file_name, $output_format);
                    unlink($location . $new_file_name);
                }
            }
            
            // Here must use additional Imagick plugin for SVG files libmagickcore or https://imagemagick.org/script/magick-vector-graphics.php.
            if ($output_format == 'svg') { /* 
                Need work!
            */ }
            
            if ($output_format == 'xbm') {
                $converted_image_url = useImagick($location, $location . $new_file_name, 'xbm');
            }

            if ($output_format == 'avif') {
                $converted_image_url = useImagick($location, $location . $new_file_name, 'avif');
            }            
            
            if ($output_format == 'wbmp') {
                $converted_image_url = useImagick($location, $location . $new_file_name, 'wbmp');
            }
            
            if ($output_format == 'xbm') {
                $converted_image_url = useImagick($location, $location . $new_file_name, 'xbm');
            }
            
            if ($output_format == 'heic') {
                $converted_image_url = useImagick($location, $location . $new_file_name, 'heic');
            }
            
            if ($output_format == 'tga') {
                $converted_image_url = useImagick($location, $location . $new_file_name, 'tga');
            }
            
            if ($output_format == 'tiff') {
                $converted_image_url = useImagick($location, $location . $new_file_name, 'tiff');
            }
            
            if ($output_format == 'pdf') {
                $converted_image_url = useImagick($location, $location . $new_file_name, 'pdf');
            }
            
            if ($output_format == 'jpeg') {
                $converted_image_url = useImagick($location, $location . $new_file_name, 'jpeg');
            }
            
            if ($output_format == 'png') {
                $converted_image_url = useImagick($location, $location . $new_file_name, 'png');
            }
            
            if ($output_format == 'gif') {
                $converted_image_url = useImagick($location, $location . $new_file_name, 'gif');
            }
            
            if ($output_format == 'webp') {
                $converted_image_url = useImagick($location, $location . $new_file_name, 'webp');
            }
            
            if ($output_format == 'bmp') {
                $converted_image_url = useImagick($location, $location . $new_file_name, 'bmp');
            }
            
            $response = array(
                'status' => 'Success',
                'image_format' => $file_type,
                'output_format' => $output_format,
                'converted_image' => $converted_image_url,
                'external_converted_image' => $result_lifecycle
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