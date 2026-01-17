<?php  
function compressImage($file_tmp, $file_name, $mime_type, $quality = 70)
{
    // Lokasi file hasil kompres sementara
    $compressedTmp = sys_get_temp_dir() . '/' . uniqid('img_', true) . '.jpg';

    switch ($mime_type) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($file_tmp);
            imagejpeg($image, $compressedTmp, $quality);
            imagedestroy($image);
            break;

        case 'image/png':
            $image = imagecreatefrompng($file_tmp);
            imagejpeg($image, $compressedTmp, $quality); // konversi ke JPG
            imagedestroy($image);
            break;

        case 'image/webp':
            $image = imagecreatefromwebp($file_tmp);
            imagejpeg($image, $compressedTmp, $quality); // konversi ke JPG
            imagedestroy($image);
            break;

        default:
            // Kalau format tidak didukung, return file asli
            return [
                'tmp_path'  => $file_tmp,
                'file_name' => $file_name,
                'mime_type' => $mime_type
            ];
    }

    // Buat nama baru
    $baseName = pathinfo($file_name, PATHINFO_FILENAME);

    return [
        'tmp_path'  => $compressedTmp,
        'file_name' => $baseName,
        'mime_type' => 'image/jpeg'
    ];
}
?>