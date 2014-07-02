<?php
ImageOps::add_alias("jpeg", "jpg");
ImageOps::add_alias("tiff", "tif");

ImageOps::add_library("native", "*", array(
  // Checks if file is an image (ie. bitmap graphic format)
  "is_image" => function($image) {
    return in_array($image->ext, array("gif", "jpeg", "png", "mng", "tiff"));
  },

);
