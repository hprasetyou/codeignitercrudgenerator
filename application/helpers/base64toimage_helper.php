<?php

function base64_to_img($base64_string) {
    $img_ext = explode('/',explode(';',$base64_string)[0])[1];
    $n = uniqid('img_');
    $img_name = "/public/upload/images/original/$n.$img_ext";
    // open the output file for writing
    $ifp = fopen( __DIR__."/../..$img_name", 'w' ) or die("Unable to open file!");

    // split the string on commas
    // $data[ 0 ] == "data:image/png;base64"
    // $data[ 1 ] == <actual base64 string>
    $data = explode( ',', $base64_string );

    // we could add validation here with ensuring count( $data ) > 1
    fwrite( $ifp, base64_decode( $data[ 1 ] ) );

    // clean up the file resource
    fclose( $ifp );

    $dimen = array(
      array(
        'width'=>'320',
        'height'=>'240'
      ),
      array(
        'width'=>'120',
        'height'=>'120'
      )
    );

    foreach ($dimen as $key => $value) {
      # code...
      $w = $value['width'];
      $h = $value['height'];
      $img_name_thumb = "/public/upload/images/{$w}x{$h}/$n.$img_ext";
      $thumb = new Imagick(__DIR__."/../..$img_name");
      $thumb->resizeImage($w,$h,Imagick::FILTER_LANCZOS,1);
      $thumb->writeImage(__DIR__."/../..$img_name_thumb");
      $thumb->destroy();
    }



    return $img_name;
}
