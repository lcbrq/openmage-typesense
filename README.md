# LCB_Typesense

Basic approach to lightning-fast, open source Typesense search for OpenMage

# Version

Beta

# Requirements

```
composer require typesense/typesense-php
composer require symfony/http-client
composer require php-http/message-factory
```

# CLI

`php typesense.php --reindex-all` Reindex all enabled products
`php typesense.php --reindex-from-date` Reindex all products since given created_at date
`php typesense.php --reindex-from-id` Reindex all products from given id
