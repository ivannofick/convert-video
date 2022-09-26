<?php

namespace App\Helpers\VideoConverter;

use App\Helpers\CustomHelper;
use Illuminate\Support\Facades\Storage;


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

    /**
     * add image on video on first duration
     */
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

    /**
     * convert videos
     */
    public function convertVideos($given_type, $v_path)
    {
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

    /**
     * convert to webm
     */
    private function webMConversion()
    {
        $cmd = "$this->ffmpegPath -i $this->filePath -acodec libvorbis -b:a 128k -ac 2 -vcodec libvpx -b:v 400k -f webm ./uploads/videos/{$this->fileName}.webm";
        if (!shell_exec($cmd)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * convert to mp4
     */
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

    /**
     *
     * @deprecated
     */
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
     *
     * @deprecated
     */
    public function changeBitrate($video)
    {
        // $cmd = $this->ffmpegPath ." -i ".$this->filePath." -vcodec libx264 -crf 24 ".base_path()."\storage\app\public\outputName.mp4";
        $cmd = $this->ffmpegPath.' -i '.$video.' -vf "scale=1200:600" -b:v 10M '.base_path().'\storage\app\public\outputNamescale.mp4';
        dd($cmd);
        dd(system($cmd));
        system($cmd);
        return base_path().'\storage\app\public\outputNamescale.mp4';
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
        return base_path()."\storage\app\public\output2.mp4";
    }


    public function changeBitrates($video)
    {
        $cmd = "$this->ffmpegPath -i $video -pix_fmt yuv420p -crf 18 -max_muxing_queue_size 9999 ".base_path()."\storage\app\public\good1.mp4";

    }

    /**
     * compress video
     */
    public function compressVideo()
    {
        $inputVideo="C:\laragon\www\oml\storage\app\public\out.mp4";
        $outputVideo="C:\laragon\www\oml\storage\app\public\in-compress-with-small-size-1.mp4";
        $cmd = "$this->ffmpegPath -i \"{$this->filePath}\" -c:v libx264 -crf 23 -maxrate 1M -bufsize 2M ".$outputVideo; //FFmpeg command for compression video but idk whats resolution
        shell_exec($cmd);
        return $this->pushAwsS3($outputVideo);
    }


    /**
     * function push to aws
     * @param string url path
     */
    public function pushAwsS3($outputVideo)
    {
        $name = CustomHelper::randomString(8);
        $nameFile = "public\/".$name.".mp4";
        $disk = Storage::disk('s3')->put($nameFile, fopen($outputVideo, 'r+'));
        $contents = Storage::disk('s3')->url($nameFile);
        return $contents;
    }
}
