# Laravel Add Select

🧱 Add subSelect queries to your Laravel models using dynamic methods.

If you're not familiar with subSelect queries, I strongly recommend [this article by Johnathan Reinink](https://reinink.ca/articles/dynamic-relationships-in-laravel-using-subqueries#can-this-be-done-with-a-has-one).

## Installation

```bash
composer require lorisleiva/laravel-add-select
```

## Usage

Consider two Eloquent models `Book` and `Chapter` such that a book can have multiple chapters.

By using the `AddSubSelects` trait, you can now add subSelect queries to the `Book` model by following the naming convention `add{NewColumnName}Select`. For example, the following piece of code add two new subSelect queries to the columns `last_chapter_id` and `latest_version`.

```php
class Book extends Model
{
    use AddSubSelects;

    public function addLastChapterIdSelect()
    {
        return Chapter::select('id')
            ->whereColumn('book_id', 'books.id')
            ->latest()
    }

    public function addLatestVersionSelect()
    {
        return Chapter::select('version')
            ->whereColumn('book_id', 'books.id')
            ->orderByDesc('version')
    }
}
```

Now, you can eager-load these subSelect queries using the `withSelect` method.

```php
Book::withSelect('last_chapter_id', 'version')->get();
```

You can also eager-load models that are already in memory using the `loadSelect` method.

```php
$book->loadSelect('last_chapter_id', 'version');
```

If you haven't eager-loaded these subSelect queries in a model, you can still access them as attributes. The first time you access them, They will cause a new database query but the following times they will be available in the model's attributes.

```php
$book->last_chapter_id;
$book->version;
```

Finally, you can gloabally eager-load these subSelect queries by setting up the `withSelect` property on the Eloquent model.

```php
class Book extends Model
{
    use AddSubSelects;

    public $withSelect = ['last_chapter_id', 'latest_version'];
}
```
