    public function compressVidio(Request $request)
    {
        $video = $request->file('video');
        $fileTmpName = $video->getPathName();
        $fileName = explode('.', $video->getClientOriginalName());
        $fileName = $fileName[0] . '_' . time() . rand(4, 9999);
        $fileType = $video->getClientMimeType();
        $extension = $video->getClientOriginalExtension();
        $fileTitle = $video->getClientOriginalName();

        $ffmpeg = new VideoHelpers($fileTmpName, $fileName);
        dd($ffmpeg->getSizeVideo());
    }
