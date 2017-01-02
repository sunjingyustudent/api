<?php

namespace Common\Org;
/**
 * 二进制转图像
 * @category   ORG
 * @package  ORG
 * @author David
 */
class Stream2Image {
    const ROOT_PATH = './';
    const FAIL_WRITE_DATA = '图片写入错误';
    //没有数据流
    const NO_STREAM_DATA = '图片数据错误';
    //图片类型不正确
    const NOT_CORRECT_TYPE = '图片格式错误';
    //不能创建文件
    const CAN_NOT_CREATE_FILE = '图片创建错误';
    //上传图片名称
    public $image_name;
    //图片保存名称
    public $save_name;
    //图片保存路径
    public $save_dir;
    //目录+图片完整路径
    public $save_fullpath;
    //打印的错误信息
    public $print_errInfo='';

    /**
     * 构造函数
     * @param String $save_name 保存图片名称
     * @param String $save_dir 保存路径名称
     */
    public function __construct($save_name, $save_dir) {
        //set_error_handler ( $this->error_handler () );

        //设置保存图片名称，若未设置，则随机产生一个唯一文件名
        $this->save_name = $save_name ? $save_name : md5 ( mt_rand (), uniqid () );
        //设置保存图片路径，若未设置，则使用年/月/日格式进行目录存储
        $this->save_dir =  $save_dir ? self::ROOT_PATH .$save_dir : self::ROOT_PATH .date ( 'Y/m/d' );

        //创建文件夹
        @$this->create_dir ( $this->save_dir );
        //设置目录+图片完整路径
        $this->save_fullpath = $this->save_dir . '/' . $this->save_name;
    }
    //兼容PHP4
    public function image($save_name) {
        $this->__construct( $save_name );
    }

    public function stream2Image($data) {
        //二进制数据流
        //$data = file_get_contents ( 'php://input' ) ? file_get_contents ( 'php://input' ) : gzuncompress ( $GLOBALS ['HTTP_RAW_POST_DATA'] );
        //数据流不为空，则进行保存操作
        if (! empty( $data )) {
            $im = imagecreatefromstring($data);
            if ($im !== false) {
                //创建并写入数据流，然后保存文件
                if (@$fp = fopen ( $this->save_fullpath, 'w+' )) {
                    fwrite ( $fp, $data );
                    fclose ( $fp );
                    $baseurl = $this->save_fullpath;
                    if ( $this->getimageInfo ( $baseurl )) {
                        //echo $baseurl;
                        $this->print_errInfo = '';
                        return true;
                    } else {
                        @unlink($this->save_fullpath);
                        $this->print_errInfo = self::NOT_CORRECT_TYPE;
                    }
                } else {
                    $this->print_errInfo = self::NOT_CORRECT_TYPE;
                }
            }else
            {
                $this->print_errInfo = self::NOT_CORRECT_TYPE;
            }
        } else {
            //没有接收到数据流
            $this->print_errInfo = self::NOT_CORRECT_TYPE;
        }
        return false;
    }
    /**
     * 创建文件夹
     * @param String $dirName 文件夹路径名
     */
    public function create_dir($dirName, $recursive = 1,$mode=0777) {
        ! is_dir ( $dirName ) && mkdir ( $dirName,$mode,$recursive );
    }
    /**
     * 获取图片信息，返回图片的宽、高、类型、大小、图片mine类型
     * @param String $imageName 图片名称
     */
    public function getimageInfo($imageName = '') {
        $imageInfo = getimagesize ( $imageName );
        if ($imageInfo !== false) {
            $imageType = strtolower ( substr ( image_type_to_extension ( $imageInfo [2] ), 1 ) );
            $imageSize = filesize ( $imageInfo );
            return $info = array ('width' => $imageInfo [0], 'height' => $imageInfo [1], 'type' => $imageType, 'size' => $imageSize, 'mine' => $imageInfo ['mine'] );
        } else {
            //不是合法的图片
            return false;
        }
    }

    /*private function error_handler($a, $b) {
        echo $a, $b;
    }*/

}