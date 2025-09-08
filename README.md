## Documentation

This project is intented to normalize csv contents of Unifersa Provider <br/>
Also, it generates ai based texts for every product

Once database is seeded with the products, families, etc. it requires manual action <br/>
On family table, root elements (the ones with codigo_padre equals null) need to be manually assigned the value of "nombre_variantes"

This done, all commands should be complete functional <br/>
Take in consideration that when creating a csv with normalized content, only products which has already been processed with AI will be exported

## How to deploy

 - copy .env.example to .env and fullfill it
 - run the migrations

## Specific commands for the project

 - Download csv files and insert them in the database <br/>
```php unifersa u:download-csv```
 ### After the first execution, it is needed to fill the fields 'nombre_variantes' in families table
 ### Only the families with 'codigo_padre' equals null need this to be filled

 - Generate texts with AI (actually it supports OpenAI) for products <br/>
```php unifersa u:improve-texts-with-ai```

 - Export the contents to a csv so it can be imported in the desired eshop <br/>
```php unifersa u:export-db-to-csv```

 - Export the discontinued products to a csv to hide them in the shop <br/>
```php unifersa u:export-discontinued-products-to-csv```

TODO:
ymascoop, ver datos de conexion en .env
Archivo: Articulos.xls


Imágenes de marcas:
Marca 3L, o logo é https://www.unifersa.es/imagenes_articulos//marcas/3_3L.jpg
Marca JUBA, o logo é https://www.unifersa.es/imagenes_articulos//marcas/3_JUBA.jpg
ISASALINE -> https://www.unifersa.es/imagenes_articulos//marcas/3_ISSALINE.jpg
ATG -> https://www.unifersa.es/imagenes_articulos//marcas/3_ATG.jpg


Ver como importar as imaxes das marcas