<?php

namespace App\Console\Commands\Update;

use App\Models\Image;
use App\Models\Price;
use App\Models\Product;
use Goutte\Client;
use Illuminate\Console\Command;

class ProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update {--price}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->updateProducts();

    }

    private function updateProducts(){

        $products = Product::all();
        $this->output->progressStart($products->count());
        $client = new Client();
        $updated_count = 0;
        foreach ($products as $product){
            $uri = "https://www.atbmarket.com$product->url";
            $crawler = $client->request('GET', $uri);
            $product_data = [
                'title' => $crawler->filter('.product-page__title')->text(),
                'description' => $crawler->filter('.product-characteristics__desc > p')->count() > 0
                    ? $crawler->filter('.product-characteristics__desc > p')->text()
                    : null,

                'status' => 'good',
                'code' => $crawler->filter('.custom-tag__text > strong')->text(),


            ];
            $current_data = $product->toArray();

            $product->fill($product_data);

            if ($product->isDirty()) {

                $product->save();

                $updated_count++;
            }
            if($this->option('price')){
                $price = [
                    'product_id' => $product->id,
                    'price' => $crawler->filter('.product-about__buy-row > .product-price > .product-price__top > span')->text(),
                    'discount_price' => $crawler->filter('.product-about__buy-row > .product-price > .product-price__bottom > span')->count() > 0
                        ? filter_var($crawler->filter('.product-about__buy-row > .product-price > .product-price__bottom > span')->text(), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)
                        : null,
                ];
                Price::Create($price);
            }



            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
        $this->output->writeln("Обновлено {$updated_count}/{$products->count()} продуктов.");
    }
}
