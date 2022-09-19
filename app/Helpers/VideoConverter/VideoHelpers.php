<?php

namespace App\Helpers\VideoConverter;


class VideoHelpers
{
    private $ffmpegPath;
    private $filePath;
    private $fileName;
    private $data;

    public function __construct($file, $name)
    {
        $this->ffmpegPath = $this->getPathFfmpeg();
        $this->filePath = $file;
        $this->fileName = $name;
        $this->data['split_duration'] = 10;
        $this->data['size'] = '255x171';
    }

    /**
     * get path ffmpeg
     *
     * @return string path ffmpeg
     */
    public function getPathFfmpeg()
    {
        $ffmpegPath = '';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $ffmpegPath = base_path() . '\app\Helpers\VideoConverter\ffmpeg\ffmpeg_win\ffmpeg';
        } else {
            // $ffmpeg_path = base_path() . '/resources/assets/ffmpeg/ffmpeg_lin/ffmpeg.exe';
            $ffmpegPath = base_path() . '/app/Helpers/VideoConverter/ffmpeg/ffmpeg/ffmpeg';
        }

        return $ffmpegPath;
    }


    /**
     * function get duration video
     *
     * @return string duration video
     */
    public function getDuration()
    {
        $cmd = shell_exec("$this->ffmpegPath -i \"{$this->filePath}\" 2>&1");
        preg_match('/Duration: (.*?),/', $cmd, $matches);
        return $matches[1];
    }

    public function convertImages($video_image_path = '')
    {
        // echo $video_image_path;exit;

        $cmd = "$this->ffmpegPath -i \"{$this->filePath}\" -an -ss " . $this->data['split_duration'] . " -s {$this->data['size']} $video_image_path";

        if (!shell_exec($cmd)) {
            return true;
        } else {
            echo 'false';
            exit;
            return false;
        }
    }

    public function convertVideos($given_type, $v_path)
    {
        // if($given_type == "video/mp4"){
        // 	$this->webMConversion();
        // 	$this->oggConversion();
        // }else{
        // $this->mp4Conversion($v_path);
        // $this->webMConversion();
        // $this->oggConversion();
        // }
        $this->mp4Conversion($v_path);
    }

    private function oggConversion()
    {
        $cmd = "$this->ffmpegPath -i $this->filePath -acodec libvorbis -b:a 128k -vcodec libtheora -b:v 400k -f ogg ./uploads/videos/{$this->fileName}.ogv";
        if (!shell_exec($cmd)) {
            return true;
        } else {
            return false;
        }
    }

    private function webMConversion()
    {
        $cmd = "$this->ffmpegPath -i $this->filePath -acodec libvorbis -b:a 128k -ac 2 -vcodec libvpx -b:v 400k -f webm ./uploads/videos/{$this->fileName}.webm";
        if (!shell_exec($cmd)) {
            return true;
        } else {
            return false;
        }
    }


    private function mp4Conversion($v_path)
    {
        // $cmd = "$this->ffmpegPath -i $this->filePath -movflags +faststart -acodec aac -strict experimental ./uploads/videos/{$this->fileName}.mp4";
        $cmd = "$this->ffmpegPath -y -i \"{$this->filePath}\" -c:v libx265 -b:v 2600k -x265-params pass=1 -an -f mp4 /dev/null && \
				$this->ffmpegPath -i \"{$this->filePath}\" -c:v libx265 -b:v 2600k -x265-params pass=2 -c:a aac -b:a 128k $v_path";

        if (!shell_exec($cmd)) {
            return true;
        } else {
            return false;
        }
    }

    public function copyOptimized($qt_path, $v_path)
    {
        $cmd = "$qt_path  \"{$this->filePath}\" $v_path";


        return shell_exec($cmd);
    }
}
