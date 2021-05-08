<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 9/3/2020
 * Time: 2:20 PM
 */

namespace SuperFrameworkEngine\App\UtilFileSystem;


class FileSystem
{
    /**
     * @param $url
     * @param $newFileName
     * @return string
     * @throws \Exception
     */
    function uploadImageByUrl($url, $newFileName) {
        if(filter_var($url, FILTER_VALIDATE_URL)) {
            if(!file_exists(public_path("uploads"))) {
                mkdir(public_path("uploads"));
            }

            if(!file_exists(public_path("uploads/".date("Y-m-d")))) {
                mkdir(public_path("uploads/".date("Y-m-d")));
            }

            $ext = strtolower(pathinfo($url,PATHINFO_EXTENSION));
            $ext = strtok($ext,"?");

            if(in_array($ext,["jpg","png","jpeg","webp"])) {
                $fileBlob = file_get_contents($url);
                if (file_put_contents(public_path("/uploads/".date("Y-m-d")."/".$newFileName.'.'.$ext), $fileBlob)) {
                    return "uploads/".date("Y-m-d")."/".$newFileName.'.'.$ext;
                } else {
                    throw new \Exception("File can't upload, please make sure that directory is exists or permission is writable");
                }
            } else {
                throw new \Exception("The file type is not an image!");
            }
        } else {
            throw new \InvalidArgumentException("The url is invalid!");
        }
    }

    /**
     * @param $inputName
     * @param $newFileName
     * @return null|string
     * @throws \Exception
     */
    function uploadImage($inputName, $newFileName) {
        if(isset($_FILES[$inputName]["tmp_name"])) {
            if(!file_exists(public_path("uploads"))) {
                mkdir(public_path("uploads"));
            }

            if(!file_exists(public_path("uploads/".date("Y-m-d")))) {
                mkdir(public_path("uploads/".date("Y-m-d")));
            }

            $ext = strtolower(pathinfo($_FILES[$inputName]['name'],PATHINFO_EXTENSION));

            $check = getimagesize($_FILES[$inputName]["tmp_name"]);
            if($check !== false) {
                if (move_uploaded_file($_FILES[$inputName]["tmp_name"], getcwd()."/uploads/".date("Y-m-d")."/".$newFileName.'.'.$ext)) {
                    return "uploads/".date("Y-m-d")."/".$newFileName.'.'.$ext;
                } else {
                    throw new \Exception("File can't upload, please make sure that directory is exists or permission is writable");
                }
            } else {
                throw new \Exception("The file type is not an image!");
            }
        } else {
            throw new \InvalidArgumentException("You did not select any file!");
        }
    }

    /**
     * @param $inputName
     * @param $newFileName
     * @return string
     * @throws \Exception
     */
    function uploadFile($inputName, $newFileName) {
        if(isset($_FILES[$inputName]["tmp_name"])) {
            if(!file_exists(public_path("uploads"))) {
                mkdir(public_path("uploads"));
            }

            if(!file_exists(public_path("uploads/".date("Y-m-d")))) {
                mkdir(public_path("uploads/".date("Y-m-d")));
            }

            $ext = strtolower(pathinfo($_FILES[$inputName]['name'],PATHINFO_EXTENSION));

            if (move_uploaded_file($_FILES[$inputName]["tmp_name"], getcwd()."/uploads/".date("Y-m-d")."/".$newFileName.'.'.$ext)) {
                return "uploads/".date("Y-m-d")."/".$newFileName.'.'.$ext;
            } else {
                throw new \Exception("File can't upload, please make sure that directory is exists or permission is writable");
            }
        } else {
            throw new \InvalidArgumentException("You did not select any file!");
        }
    }

}