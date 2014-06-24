<?php
/**
 * CubeCart v5
 * ========================================
 * CubeCart is a registered trade mark of Devellion Limited
 * Copyright Devellion Limited 2010. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  http://www.cubecart.com/v5-software-license
 * ========================================
 * CubeCart is NOT Open Source.
 * Unauthorized reproduction is not allowed.
 */

class GD {

	private $_gdImageMax;
	private $_gdThumbMax;
	private $_gdJpegQuality;
	private $_gdTargetDir;

	private $_gdImageData;
	private $_gdImageExif;
	private $_gdImageType;

	private $_gdImageSource;
	private $_gdImageOutput;

	private $_gdWatermark;
	private $_gdUploaded = false;

	##############################################

	public function __construct($targetDir, $maxImage = false, $jpegQuality = 100) {
		if (substr($targetDir, -1) != '/') {
			$targetDir .= '/';
		}
		$this->_gdTargetDir  = $targetDir;
		$this->_gdImageMax  = $maxImage;
		$this->_gdJpegQuality = $jpegQuality;
	}

	public function gdClear() {
		$this->_gdImageOutput = false;
		$this->_gdImageSource = false;
		$this->_gdImageData  = false;
		$this->_gdImageExif  = false;
		$this->_gdWatermark  = false;
	}

	##############################################

	public function gdLoadFile($file) {
		if (file_exists($file)) {
			$this->_gdImageData = getimagesize($file);
			$this->_gdImageType = $this->_gdImageData[2];

			switch ($this->_gdImageType) {
			case IMAGETYPE_GIF:
				$this->_gdImageSource = imagecreatefromgif($file);
				break;
			case IMAGETYPE_JPEG:
				$this->_gdImageSource = imagecreatefromjpeg($file);
				if (function_exists('exif_read_data')) {
					$this->_gdImageExif = @exif_read_data($file);
				}
				break;
			case IMAGETYPE_PNG:
				$this->_gdImageSource = imagecreatefrompng($file);
				imagesavealpha($this->_gdImageSource, true);
				break;
			default:
				trigger_error(__METHOD__.' - Unknown file type');
				return false;
			}
			return true;
		}
		return false;
	}

	public function gdSave($filename, $resize = false, $thumbnail = false) {
		// Do we need to resize the file before saving?
		if ($resize || $this->_gdImageMax) {
			$this->gdResize(($resize) ? $resize : $this->_gdImageMax);
		}
		$im = $this->gdGetCurrentData();
		if ($im) {
			$file = $this->_gdTargetDir.$filename;
			switch ($this->_gdImageType) {
			case IMAGETYPE_GIF:
				$this->_gdImageSource = imagegif($im, $file);
				break;
			case IMAGETYPE_JPEG:
				$this->_gdImageSource = imagejpeg($im, $file, $this->_gdJpegQuality);
				break;
			case IMAGETYPE_PNG:
				imagesavealpha($im, true);
				$this->_gdImageSource = imagepng($im, $file);
				break;
			default:
				trigger_error(__METHOD__.' - Unknown file type', E_USER_NOTICE);
				return false;
			}
			return true;
		}
		return false;
	}

	public function gdThumbnail($filename, $output) {
		if (function_exists('exif_thumbnail') && file_exists($filename)) {
			$thumb = exif_thumbnail($filename);
			if ($thumb) {
				# $im = imagecreatefromstring($thumb);
			}
		}
		return false;
	}

	##############################################

	private function gdGetCurrentData() {
		// Detect what data source we should be using
		// If output is empty, use the source
		return (!empty($this->_gdImageOutput)) ? $this->_gdImageOutput : $this->_gdImageSource;
	}

	##############################################

	public function gdBatchUpload($watermark = false) {
		// Process a batch upload - take the $_FILES array directly
		$this->_gdUploaded = false;
		if (!empty($_FILES)) {
			foreach ($_FILES as $key => $values) {
				// Make sure the array isn't empty
				if ($values['size'] > 0 && !empty($values['tmp_name']) && !empty($values['name'])) {
					// Pass to gdUpload() for all the real trickery
					$this->gdUpload($values['tmp_name'], $values['name'], false, $watermark);
					$this->gdSave($values['name']);
					$this->gdClear();
					$this->_gdUploaded[] = $values['name'];
				}
			}
			return ($this->_gdUploaded) ? $this->_gdUploaded : true;
		}
		return false;
	}

	public function gdUpload($file, $name, $rotate = false, $watermark = false, $filter = false) {
		// First up, lets check the source file actually exists
		if ($this->gdLoadFile($file)) {
			// Rotate image
			if ($rotate) $this->gdRotate($rotate);
			// Apply filter
			if ($filter) $this->gdFilter($filter);
			// Add Watermark image
			# if ($watermark) $this->gdWatermark($watermark);
		}
		return false;
	}

	##############################################
	// Edit the image

	public function gdCrop($x, $y, $w, $h) {
		if ($im = $this->gdGetCurrentData()) {
			$oh = imagesy($im);
			$ow = imagesx($im);
			$h = ($oh < $h) ? $oh : $h;
			$w = ($ow < $w) ? $ow : $w;
			$this->_gdImageOutput = imagecreatetruecolor($w, $h);
			imagecopyresampled($this->_gdImageOutput, $im, 0, 0, $x, $y, $w, $h, $w, $h);
			imagesavealpha($this->_gdImageOutput, true);
			$this->_gdImageArray[0] = $w;
			$this->_gdImageArray[1] = $h;
		}
		# trigger_error(sprintf('File cropped to %dx%d@%dx%d', $w, $h, $x, $y), E_USER_NOTICE);
	}

	private function gdResize($resize) {
		// Resize the image, while maintaining the proportions
		$im = $this->gdGetCurrentData();
		if ($im) {
			// Get the existing image details
			$width = imagesx($im);
			$height = imagesy($im);
			// Calculate the resized dimensions
			$x_ratio = $resize / $width;
			$y_ratio = $resize / $height;
			// Perform a few calculations to work out the new (constrained) dimensions
			$proceed = true;
			if (($width <= $resize) && ($height <= $resize)) {
				// no resize needed
				$out_width = $width;
				$out_height = $height;
				$proceed = false;
			} else if (($x_ratio * $height) < $resize) {
					$out_height = ceil($x_ratio * $height);
					$out_width = $resize;
				} else {
				$out_width = ceil($y_ratio * $width);
				$out_height = $resize;
			}
			if ($proceed) {
				// Create the output file and resample
				$this->_gdImageOutput = imagecreatetruecolor($out_width, $out_height);
				imagealphablending($this->_gdImageOutput, false);
				imagesavealpha($this->_gdImageOutput, true);
				imagecopyresampled($this->_gdImageOutput, $im, 0, 0, 0, 0, $out_width, $out_height, $width, $height);
				return true;
			}
		}
		return false;
	}

	public function gdWatermark($watermark, $y = 'bottom', $x = 'right', $margin = 5) {
		if ($this->gdLoadWatermark($watermark)) {
			$im = $this->gdGetCurrentData();
			// Calculate the image dimensions
			$src_w  = imagesx($im);
			$src_h = imagesy($im);
			$logo_w = imagesx($logo);
			$logo_h = imagesy($logo);
			// Work out where we're gonna put the watermark
			switch ($x) {
			case 'left': $src_x = $margin; break;
			case 'right': $src_x = $src_w - ($logo_w+$margin); break;
			case 'center': $src_x = ($src_w/2) - ($logo_w/2); break;
				// Allow for exact positioning
			case is_numeric($x) : $src_x = $x; break;
			}
			switch ($y) {
			case 'top':  $src_y = $margin; break;
			case 'middle': $src_y = ($src_h/2) - ($logo_h/2); break;
			case 'bottom': $src_y = $src_h - ($logo_h+$margin); break;
				// Allow for exact positioning
			case is_numeric($y) : $src_y = $y; break;
			}
			imagesavealpha($img, true);
			imagesavealpha($logo, true);
			// Copy the logo onto the main image
			imagecopy($im, $logo, $src_x, $src_y, 0, 0, $logo_w, $logo_h);
			$this->_gdImageOutput = $im;
			return true;
		}
		return false;
	}

	private function gdWatermarkLoad($file) {
		if (file_exists($file)) {
			$logo = getimagesize($file);
			switch ($logo[2]) {
			case IMAGETYPE_GIF: $src =  imagecreatefromgif($file); break;
			case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($file); break;
			case IMAGETYPE_PNG: $src = imagecreatefrompng($file); break;
			case IMAGETYPE_WBMP: $src = imagecreatefromwbmp($file); break;
			case IMAGETYPE_XBM: $src = imagecreatefromxbm($file); break;
			default:
				trigger_error(__METHOD__.' - Unknown file type');
				return false;
			}
			imagesavealpha($src, true);
			$this->_gdWatermark = $src;

			return true;
		}
		return false;
	}

	private function gdRotate($degrees = 90) {
		// Rotate the image clockwise, preferably by increments of 90 degrees
		$im = $this->gdGetCurrentData();
		$degrees = (360 - $degrees);

		$this->_gdImageOutput = imagerotate($im, $degrees, 0);

		if ($degrees != 180) {
			#$width = $this->_gdImageArray[0];
			#$height = $this->_gdImageArray[1];
			$this->_gdImageArray[0] = imagesy($this->_gdImageOutput); //$height;
			$this->_gdImageArray[1] = imagesx($this->_gdImageOutput); //$width;
		}
	}

	private function gdFilter($filter, $arg = '') {
		// Apply a GD filter to the image, if needed
		$im = $this->gdGetCurrentData();
		switch (strtolower($filter)) {
		case 'negative': $imgfilter = IMG_FILTER_NEGATE; break;
		case 'greyscale':
		case 'grayscale': $imgfilter = IMG_FILTER_GRAYSCALE; break;
		case 'edges':  $imgfilter = IMG_FILTER_EDGEDETECT; break;
		case 'emboss':  $imgfilter = IMG_FILTER_EMBOSS; break;
		case 'gaussian': $imgfilter = IMG_FILTER_GAUSSIAN_BLUR; break;
		case 'sketchy':  $imgfilter = IMG_FILTER_MEAN_REMOVAL; break;
		}
		imagefilter($im, $imgfilter, $arg);
		$this->_gdImageOutput = $im;
	}
}