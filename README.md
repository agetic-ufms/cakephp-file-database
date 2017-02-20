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

### Criar a tabela
Usando migrations:
```
$ bin/cake migrations migrate -p FileDb
```


## Como Usar

### Habilite o behavior em seu Model:

Crie quantas configurações de arquivo desejar: 

```php
  $this->addBehavior('FileDb.FileDatabase',[
            [
                'alias' => 'Foto',
                'type' => 'hasOne',
                'form_field' => 'file_foto' // campo usado no formulário
            ],
            [
	            'alias' => 'Documento',
	            'type' => 'hasOne',
	            'form_field' => 'file_documento'
            ],
             [
	            'alias' => 'Album',
	            'type' => 'hasMany',
	            'form_field' => 'file_album'  
            ],
    ]);
```

### Adicione o campo no formulário:

```php
   <?= $this->Form->create($autor, ['enctype' => 'multipart/form-data']) ?>
   
	   <?= $this->Form->file('file_foto'); ?>
	   <?= $this->Form->file('file_documento'); ?>
	   
	   <?= $this->Form->file('file_album.1'); ?>
	   <?= $this->Form->file('file_album.2'); ?>
	   <?= $this->Form->file('file_album.3'); ?>
	   
   <?= $this->Form->button(__('Submit')) ?>
   <?= $this->Form->end() ?>
```

### Obtendo o arquivo:

Em seu controller:

```php
	
	$aluno = $this->Alunos->get($id, [
            'contain' => ['Foto', 'Album', 'Documento' ]
    ]);
```

### Download do arquivo:

Em seu controller:

```php
	public function download($id = null)
    {
        $aluno = $this->Alunos->get($id, [
            'contain' => ['Foto']
        ]);
    
        $file = $aluno->foto->file_content;
        $this->response->type($aluno->foto->file_type);
        $this->response->body(function () use ($file) {
            rewind($file);
            fpassthru($file);
            fclose($file);
        });
        
        $this->response->download($aluno->foto->file_name); // comente para não forçar o download
        return $this->response;
    }
```

### Removendo o arquivo:

A remoção é automática quando a entidade associada é removida. Ou se

Caso queira remover um arquivo específico ou vários, use o seguinte:

```php
	
	// file_id: id do arquivo!
	$this->Alunos->deleteFile($file_id);
	
	// id: do aluno, e tag = 'Foto' 
	$this->Alunos->deleteAllFiles($id, $tag=null)
	
```
