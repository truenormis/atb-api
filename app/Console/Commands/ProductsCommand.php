<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Models\Price;
use App\Models\Product;
use App\Models\Subcategory;
use Goutte\Client;
use Illuminate\Console\Command;

class ProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:save';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subcategory = Subcategory::all();


        $categoryUrl = "https://www.atbmarket.com$subcategory->url";
        //dd($categoryUrl);
        $client = new Client();
        $products = $this->crawlProducts($client, $categoryUrl, $subcategory);
        return $products;
    }

    /**
     * Crawl and save products from the given category URL.
     *
     * @param \Goutte\Client $client
     * @param string $categoryUrl
     * @return array
     */
    private function crawlProducts(Client $client, string $categoryUrl, Subcategory $subcategory): array
    {
        $products = [];

        $page = 1;
        $shouldRepeat = true;

        while ($shouldRepeat) {
            $urls = $this->extractProductUrls($client, $categoryUrl, $page);
            $this->output->progressStart(count($urls));

            foreach ($urls as $url) {
                $product = $this->extractProductData($client, $url,$subcategory);
                $products['products'][] = $product;
                $this->output->progressAdvance();
            }

            $this->output->progressFinish();
            $page++;
            $shouldRepeat = $this->shouldRepeatCrawl($client, $categoryUrl, $page,$products,$subcategory);
        }

        return $products;
    }

    /**
     * Extract product URLs from the given category URL and page number.
     *
     * @param \Goutte\Client $client
     * @param string $categoryUrl
     * @param int $page
     * @return array
     */
    private function extractProductUrls(Client $client, string $categoryUrl, int $page): array
    {
        $crawler = $client->request('GET', "$categoryUrl?page=$page");
        $urls = [];

        $crawler->filter('div.catalog-item__title > a')->each(function ($node) use (&$urls) {
            $productUrl = [
                'url' => $node->attr('href'),
            ];

            $urls[] = $productUrl;
        });

        return $urls;
    }

    /**
     * Extract product data from the given product URL.
     *
     * @param \Goutte\Client $client
     * @param array $url
     * @return array
     */
    private function extractProductData(Client $client, array $url,Subcategory $subcategory): void
    {
        $crawler = $client->request('GET', $url['url']);
        $product = [
            'title' => $crawler->filter('.product-page__title')->text(),
            'url' => $url['url'],
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

    /**
     * Check if the crawling process should repeat for the next page.
     *
     * @param \Goutte\Client $client
     * @param string $categoryUrl
     * @param int $page
     * @return bool
     */
    private function shouldRepeatCrawl(Client $client, string $categoryUrl, int $page,&$products,$subcategory): bool
    {
        $crawler = $client->request('GET', "$categoryUrl?page=$page");
        if ($crawler->filter('.product-pagination__item.slider-arrow.next > a')->count() == 0){
            if ($crawler->filter('.product-pagination__item.slider-arrow.next')->count() != 0){
                $urls = $this->extractProductUrls($client, $categoryUrl, $page);
                $this->output->progressStart(count($urls));

                foreach ($urls as $url) {
                    $product = $this->extractProductData($client, $url,$subcategory);
                    $products['products'][] = $product;
                    $this->output->progressAdvance();
                }

                $this->output->progressFinish();
            }

            return false;
        }
        return true;
    }
}
