<img alt="Logo" src="https://upload.wikimedia.org/wikipedia/uk/c/c4/%D0%9B%D0%BE%D0%B3%D0%BE%D1%82%D0%B8%D0%BF_%D0%90%D0%A2%D0%91.svg" width="100" height="100" title="ATB-logo"/>

# Unofficial ATB API

Unofficial api of the most popular trading network in Ukraine ATB [АТБ-Маркет](https://www.atbmarket.com/)

## FAQ

#### Why is it needed?

The site of the trading company has neither a normal website nor a normal application.

#### We take data from the site?

No, we download data from the server and use it conveniently and comfortably

## Installing

### Base installation command

```
git clone https://github.com/truenormis/atb-api
php artisan migrate
```

#### Get all products

```
php artisan categories:save
php artisan products:findnew
php artisan images:save
php artisan options:findnew
```

#### Updating

```
php artisan products:update {--price}
```

## API Reference

#### Get products

```
  GET /api/v1/products
```

| Key                 | Type     | Value                | Description                    |
|:--------------------|:---------|:---------------------|:-------------------------------|
| `sort`              | `string` | `titile`,`price`     | You can use **-** to **value** |
| `filter[country]`   | `string` | Example `Україна`    | Filter products by Country     |
| `filter[trademark]` | `string` | Example `Своя лінія` | Filter products by trademark   |

#### Get categories

```
  GET /api/v1/categories
```

#### Get Subcategories by category id

```
  GET /api/v1/categories/{category}
```

| Parameter | Type  | Description                       |
|:----------|:------|:----------------------------------|
| `id`      | `int` | **Required**. Id of item to fetch |
