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
Usando migrations para gerar a tabela de arquivos
```
$ bin/cake migrations migrate -p FileDb
```
Esse comando deve gerar a seguinte tabela. Prefira usar migrations.

```sql
CREATE TABLE attachments
(
  id serial NOT NULL,
  file_name character varying(255) NOT NULL,
  file_path character varying(255),
  file_type character varying(255) NOT NULL,
  file_size bigint NOT NULL,
  file_content bytea NOT NULL,
  model character varying(255) NOT NULL,
  tag character varying(255) NOT NULL,
  foreign_key integer NOT NULL,
  created timestamp without time zone NOT NULL,
  modified timestamp without time zone,
  CONSTRAINT attachments_pkey PRIMARY KEY (id)
);

```


## Como Usar

### Habilite o behavior em seu Model:

Crie quantas configurações de arquivo desejar: 

```php
  $this->addBehavior('FileDb.FileDatabase',[
            [
                'alias' => 'Foto',
                'type' => 'hasOne',
                'form_field' => 'file_foto' 		// campo usado no formulário
            ],
            [
	            'alias' => 'Documento',
	            'type' => 'hasOne',
	            'form_field' => 'file_documento'
            ],
             [
	            'alias' => 'Album',
	            'type' => 'hasMany',				// hasMany 
	            'form_field' => 'file_album'  
            ],
    ]);
```

### Adicione o campo no formulário:

```php
   <?= $this->Form->create($aluno, ['enctype' => 'multipart/form-data']) ?>
   
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

A remoção é automática quando a entidade associada é removida. Ou seja, se o aluno for removido, os arquivos também serão.

Caso queira remover um arquivo específico ou vários, use o seguinte:

```php
	// file_id: id do arquivo!
	$this->Alunos->deleteFile($file_id);
	
	// id: do aluno, e tag = 'Foto' 
	$this->Alunos->deleteAllFiles($id, $tag=null)
```

## Validação
```php
		// use bytes(inteiro) ou '1MB' humanizado
        $validator->add('file_upload', 'file', [
        		'rule' => ['fileSize', [ '>', 1024 ]],
        		'message' =>'Arquivo deve ser maior que 1MB'
        ]);
        
        // limite o tamanho considerandosua regra E: memory_limit, post_max_size e upload_max_filesize
        $validator->add('file_upload', 'file', [
        		'rule' => ['fileSize', [ '<', '2MB' ]],
        		'message' =>'Arquivo deve ser menor que 2MB'
        ]);

		$validator->add('file_upload','create', [
               		'rule' => ['extension', ['png', 'pdf']],
               		'message' =>'Extensão inválida'
        ]);
		
		$validator->add('file_upload','create', [
				'rule' => ['mimeType', ['image/jpeg', 'image/png']],
				'message' =>'Tipo inválido',
		]);

		// Erro quando arquivo não pode ser enviado ao servidor, geralmente por causa de:
		//         memory_limit
		//         post_max_size
		//         upload_max_filesize
		$validator->add('file_upload', 'file', [
				'rule' => ['uploadError'],
				'message' =>'Erro ao enviar o arquivo',
				'last'=> true
		]);
```

## TODO (a fazer)

* Opção de gravar em disco
