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

    /**
     * change bitrate
     *
     *  // 240p = 350k
     *  // 360p = 700k
     *  // 480p = 1200k
     *  // 720p = 2500k
     *  // 1080p = 5000k
     */
    public function changeBitrate($video)
    {
        // $cmd = $this->ffmpegPath ." -i ".$this->filePath." -vcodec libx264 -crf 24 ".base_path()."\storage\app\public\outputName.mp4";
        $cmd = $this->ffmpegPath.' -i '.$video.' -vf "scale=1200:600" -b:v 10M '.base_path().'\storage\app\public\outputNamescale.mp4';
        dd($cmd);
        dd(system($cmd));
        system($cmd);
        echo "File has been converted";
    }

    /**
     * change resolution video
     *
     * @param File video
     */
    public function changeResolutionVideo($video)
    {
        $cmd = "$this->ffmpegPath -i $video -s 640x480 -max_muxing_queue_size 9999 ".base_path()."\storage\app\public\output2.mp4";
        shell_exec($cmd);
    }


    public function changeBitrates($video)
    {
        $cmd = "$this->ffmpegPath -i $video -pix_fmt yuv420p -crf 18 -max_muxing_queue_size 9999 ".base_path()."\storage\app\public\good1.mp4";

    }

    public function ifk()
    {
    //     MP4 - 1080p
    //     ffmpeg -i input.mov -preset slow -codec:a libfdk_aac -b:a 128k -codec:v libx264 -pix_fmt yuv420p -b:v 4500k -minrate 4500k -maxrate 9000k -bufsize 9000k -vf scale=-1:1080 output.mp4

    //     MP4 - 720p
    //     ffmpeg -i input.mov -preset slow -codec:a libfdk_aac -b:a 128k -codec:v libx264 -pix_fmt yuv420p -b:v 2500k -minrate 1500k -maxrate 4000k -bufsize 5000k -vf scale=-1:720 output.mp4

    //     MP4 - 480p
    //     ffmpeg -i input.mov -preset slow -codec:a libfdk_aac -b:a 128k -codec:v libx264 -pix_fmt yuv420p -b:v 1000k -minrate 500k -maxrate 2000k -bufsize 2000k -vf scale=-1:480 output.mp4

    //     MP4 - 360p
    //     ffmpeg -i input.mov -preset slow -codec:a libfdk_aac -b:a 128k -codec:v libx264 -pix_fmt yuv420p -b:v 750k -minrate 400k -maxrate 1000k -bufsize 1500k -vf scale=-1:360 output.mp4
    }
}
