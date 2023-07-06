<?php

namespace App\Console\Commands\Update;

use App\Models\Option;
use App\Models\Product;
use Goutte\Client;
use Illuminate\Console\Command;

class OptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'options:findnew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find new options';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = Product::doesntHave('options')->get();;

        $this->output->progressStart($products->count());
        foreach ($products as $product){
            $client = new Client();
            $url = "https://www.atbmarket.com$product->url";
            $crawler = $client->request('GET', $url);


            $crawler->filter('.product-characteristics__item')->each(function ($node) use ($product) {
                $option = [
                    'product_id' => $product->id,
                    'name' => $node->filter('.product-characteristics__name')->count() > 0
                        ? $node->filter('.product-characteristics__name')->text()
                        : null,
                    'value' => $node->filter('.product-characteristics__name')->count() > 0
                        ? $node->filter('.product-characteristics__value')->text()
                        : null,
                ];
                Option::firstOrCreate($option);
                $this->output->progressAdvance();

            });


        }
        $this->output->progressFinish();
    }
}
