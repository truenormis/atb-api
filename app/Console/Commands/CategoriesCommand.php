<?php

namespace App\Console\Commands;

use App\Models\Subcategory;
use App\Models\Category;
use Goutte\Client;
use Illuminate\Console\Command;

class CategoriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'categories:save';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save categories';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = 'https://www.atbmarket.com';

        $client = new Client();
        $crawler = $client->request('GET', $url);


        $crawler->filter('li.category-menu__item')->each(function ($node) use (&$data) {
            $categoryLink = $node->filter('a')->attr('href');
            $categoryName = $node->filter('a')->text();

            $category = [
                'name' => $categoryName,
                'url' => $categoryLink
            ];

            $category_query = Category::firstOrCreate($category);

            $this->info("Category: $categoryName");
            $node->filter('li.submenu__item')->each(function ($subNode) use (&$category, &$category_query) {
                $subcategory = [
                    'name' => $subNode->text(),
                    'url' => $subNode->filter('a')->attr('href'),
                    'category_id' => $category_query->id
                ];

                Subcategory::firstOrCreate($subcategory);
                $this->info("Subcategory: $subcategory[name]");

            });



        });


    }
}
