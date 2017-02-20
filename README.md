# Filedb aramazena arquivos no banco de dados - plugin para CakePHP 3.x

## Instalação

Recomendamos instalar como um pacote do composer:

```json
	"repositories": [
	    { "type": "vcs", "url": "https://git-agetic.ufms.br/nti/cakephp-file-database.git" }
	],
    "require": {
        "agetic/cakephp-file-db" : "@stable"
    }
```

Atualize os pacotes
```
$ composer update 
```


### Carregar o plugin

Adicione no final do arquivo `config/bootstrap.php`

```php
Plugin::load('FileDb');
```

## Como Usar

### Habilite o tema 
`src/Controller/AppController.php`

```php
...
```
