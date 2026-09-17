<?php
      function readFileUtils($fileName) {
         $data = "";
         if (file_exists($fileName)) {
            if (is_readable($fileName)) {
               $fp = fopen($fileName, "r");
               if (!empty($fp)) {
                  $data = fread($fp, filesize($fileName));
                  fclose($fp);
               }
            }
         }
         return $data;
      }

	  function mkdirs($path, $mode=0777) {
         is_dir(dirname($path)) || mkdirs(dirname($path), $mode);
         return is_dir($path) || @mkdir($path, $mode);
	  }

	  function getUnDupFileName($fileName, $path) {
        $lastChar = substr($fileName, strlen($path));
        if (($lastChar != "/") || ($lastChar != "\\")) $path .= DIRECTORY_SEPARATOR;
        $lastDotPos = strpos($fileName, ".");
        $file = substr($fileName, 0, $lastDotPos);
        $ext = substr($fileName, $lastDotPos);
        $foundName = false;
        $fileTemp = $file;
        $i=1;
        while (!$foundName) {
           if (file_exists($path.$fileTemp.$ext)) {
              $fileTemp = $file."-".$i;
              $i++;
           } else {
              $fileName = $fileTemp.$ext;
              $foundName = true;
           }
        }
        return $fileName;
	  }

     function getContentType($filename) {
         $xtype = "";
         if (ereg(".mp3",$filename)){$xtype="audio/mpeg";}
         elseif(ereg(".zip",$filename)){$xtype="application/x-zip-compressed";}
         elseif(ereg(".exe",$filename)){$xtype="application/octet-stream";}
         elseif(ereg(".txt",$filename)){$xtype="text/plain charset='us-ascii'";}
         elseif(ereg(".doc",$filename)){$xtype="application/msword";}
         elseif(ereg(".xls",$filename)){$xtype="application/vnd.ms-excel";}
         elseif(ereg(".ppt",$filename)){$xtype="application/vnd.ms-powerpoint";}
         elseif(ereg(".gif",$filename)){$xtype="image/gif";}
         elseif(ereg(".png",$filename)){$xtype="image/png";}
         elseif(ereg(".jpg",$filename)){$xtype="image/jpg";}
         elseif(ereg(".wav",$filename)){$xtype="audio/x-wav";}
         elseif(ereg(".mpe",$filename)){$xtype="video/mpeg";}
         elseif(ereg(".mov",$filename)){$xtype="video/quicktime";}
         elseif(ereg(".avi",$filename)){$xtype="video/x-msvideo";}
         else { $xtype="application/force-download"; }
         return $xtype;  
      }
      
	 function  removeDir($dir)
    {
        if(is_dir($dir))
        {
            $dir = (substr($dir, -1) != "/")? $dir."/":$dir;
            $openDir = opendir($dir);
            while($file = readdir($openDir))
            {
                if(!in_array($file, array(".", "..")))
                {
                    if(!is_dir($dir.$file))
                        @unlink($dir.$file);
                    else
                        removeDir($dir.$file);
                }
            }
            closedir($openDir);
            @rmdir($dir);
        }
    } 
/*	 
    function downloadFile($srcFileName, $saveFileName, $page=null) {
//         $fileUtils = new FileUtils();
         $contentType = getContentType($srcFileName);
         $fullPath = $srcFileName;
         if (!file_exists($fullPath)) {
            echo "This file has been moved.";
            return sfView::HEADER_ONLY;
         }
         $fileSize = filesize($fullPath);
         if(ini_get('zlib.output_compression'))
            ini_set('zlib.output_compression', 'Off');
//         $contentType = "application/force-download";

//         $fileSize2=$fileSize-1;
//         header("Content-Range: bytes 0-$fileSize2/$fileSize");
//         header("Content-Length: ".$fileSize);
//         header("Content-Transfer-Encoding: binary");
//         header("Expires: 0");
//         header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
//         header("Content-Type: ".$contentType);

         header("Pragma: public"); // required
         header("Expires: 0");
         header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
         header("Cache-Control: private",false); // required for certain browsers 
         header("Content-Type: $contentType");
// change, added quotes to allow spaces in filenames, by Rajkumar Singh
//         header("Content-Disposition: attachment; filename=\"".basename($saveFileName)."\";" );
         header("Content-Transfer-Encoding: binary");
         header("Content-Length: ".filesize($fullPath));
         
         if($page != null) {
            if(strstr($page->getRequest()->getUserAgent(), "MSIE")) {
               $iefilename = preg_replace('/\./', '%2e', $saveFileName, substr_count($saveFileName, '.') - 1);
               header("Content-Disposition: attachment; filename=\"".$iefilename."\";");
            } else {
               header("Content-Disposition: attachment; filename=\"".$saveFileName."\";");
            }      
         }
         readfile("$fullPath");
//         header("Content-Disposition: attachment; filename=\"".$saveFileName."\";");
/*         
         $file = fopen($fullPath, "rb");
         if ($file) {
            $remainSize = $fileSize;
            $chunk = 1024;
            while ($remainSize>0) {
               echo fread($file, $chunk);
               $remainSize = $remainSize - $chunk;
               if ($remainSize < $chunk) $remainSize = $chunk;
               flush();
//               ob_flush();
            }
            fclose($file);
         }
*/
      //}
 /*     
      function GetFolderSize($d ="." ) { 
          // © kasskooye and patricia benedetto 
          $h = @opendir($d); 
          if($h==0)return 0; 
          if (!isset($sf)) $sf=0;

          while ($f=readdir($h)){ 
              if ( $f!= "..") { 
                  $sf+=filesize($nd=$d."/".$f); 
                  if($f!="."&&is_dir($nd)){ 
                      $sf += GetFolderSize ($nd); 
                  } 
              } 
          } 
          closedir($h); 
          return $sf ; 
      }
      
      function ByteSize($bytes) {
         $size = $bytes / 1024;
         if($size < 1024) {
            $size = number_format($size, 2);
            $size .= ' KB';
         } else {
            if($size / 1024 < 1024) {
               $size = number_format($size / 1024, 2);
               $size .= ' MB';
            } else if ($size / 1024 / 1024 < 1024) {
               $size = number_format($size / 1024 / 1024, 2);
               $size .= ' GB';
            } 
         }
         return $size;
      }
 */
?>    