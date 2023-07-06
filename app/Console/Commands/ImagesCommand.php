<?php

namespace App\Console\Commands;

use App\Models\Image;
use Illuminate\Console\Command;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:save';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save images from products';

    /**
     * Execute the console command.
     */


    public function handle()
    {
        $images = Image::where('local_url',null)
            ->where('web_url', '!=', null)
            ->get();
        $count = $images->count();
        $this->output->progressStart($count);
        foreach ($images as $image){
            if ($image->web_url != null ){
                $local_url = $this->image_download($image->web_url);
                $image->update(['local_url' => $local_url]);
                $this->output->progressAdvance();
            }

        }
        $this->output->progressFinish();

    }

    private function image_download($url){
        $response = Http::get($url);
        if ($response->successful()) {
            // Save the image content to a temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'img');
            file_put_contents($tempFile, $response->body());

            // Store the temporary file to the public directory
            $filename = uniqid() . '.' . pathinfo($url, PATHINFO_EXTENSION);
            $path = Storage::disk('public')->putFileAs('product/photos', new File($tempFile), $filename);

            // Delete the temporary file
            unlink($tempFile);

            // Get the relative URL of the stored image
            $url = '/storage/' . $path;
            return $url;
        }
    }


}
