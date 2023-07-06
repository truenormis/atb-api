<?php

namespace App\Console\Commands\Update;

use App\Models\Image;
use App\Models\Price;
use App\Models\Product;
use App\Models\Subcategory;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class FindNewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:findnew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find new products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->findnew();
    }

    private function findnew(){
        $subcategories_url = Subcategory::all();
        $client = new \Goutte\Client();
        $page = 1;
        $urls = [];
        $shouldRepeat = false;
        $this->output->progressStart($subcategories_url->count());
        while ($shouldRepeat == false){
            list($crawler, $urls) = $this->get_urls($subcategories_url, $client, $page, $urls);
            if ($crawler->filter('.product-pagination__item.slider-arrow.next > a')->count() == 0){
                if ($crawler->filter('.product-pagination__item.slider-arrow.next')->count() != 0){
                    list($crawler, $urls) = $this->get_urls($subcategories_url, $client, $page, $urls);
                }
                $shouldRepeat = true;
            }
            $page++;
        }

        $this->output->progressFinish();
        $this->output->progressStart(count($urls));
        foreach ($urls as $url){
            $this->extractProductData($client,$url['url'],$url['subcategory']);
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
    }

    /**
     * @param $subcategories_url
     * @param \Goutte\Client $client
     * @param int $page
     * @param array $urls
     * @return array
     */
    public function get_urls($subcategories_url, \Goutte\Client $client, int $page, array $urls): array
    {
        foreach ($subcategories_url as $subcategory_url) {
            $crawler = $client->request('GET', "https://www.atbmarket.com$subcategory_url->url?page=$page");


            $crawler->filter('div.catalog-item__title > a')->each(function ($node) use ($subcategory_url, &$urls) {
                $productUrl = [
                    'url' => $node->attr('href'),
                    'subcategory' => $subcategory_url,
                ];
                if (Product::where('url', $productUrl)->count() == 0) {
                    $urls[] = $productUrl;
                }

            });
            $this->output->progressAdvance();
        }
        return array($crawler, $urls);
    }

    private function extractProductData(\Goutte\Client $client, string $url, Subcategory $subcategory): void
    {
        $crawler = $client->request('GET', "https://www.atbmarket.com$url");
        $product = [
            'title' => $crawler->filter('.product-page__title')->text(),
            'url' => $url,
            'description' => $crawler->filter('.product-characteristics__desc > p')->count() > 0
                ? $crawler->filter('.product-characteristics__desc > p')->text()
                : null,

            'status' => 'good',
            'code' => $crawler->filter('.custom-tag__text > strong')->text(),
            'category_id' => $subcategory->category->id,
            'subcategory_id' => $subcategory->id,

        ];
        $product = Product::Create($product);

        $price = [
            'product_id' => $product->id,
            'price' => $crawler->filter('.product-about__buy-row > .product-price > .product-price__top > span')->text(),
            'discount_price' => $crawler->filter('.product-about__buy-row > .product-price > .product-price__bottom > span')->count() > 0
                ? filter_var($crawler->filter('.product-about__buy-row > .product-price > .product-price__bottom > span')->text(), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)
                : null,
        ];

        $image = [
            'product_id' => $product->id,
            'web_url' => $crawler->filter('.cardproduct-tabs__item > picture > source')->count() > 0
                ? $crawler->filter('.cardproduct-tabs__item > picture > source')->attr('srcset')
                : null,
        ];

        Price::Create($price);
        Image::Create($image);

    }
}
