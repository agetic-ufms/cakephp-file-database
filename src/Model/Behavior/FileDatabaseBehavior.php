<?php

namespace FileDb\Model\Behavior;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Datasource\ConnectionManager;

/**
 * Class FileDatabaseBehavior
 * @package FileDb\Model\Behavior
 * @property Table $Attachments
 */

class FileDatabaseBehavior extends Behavior {

    /*
     * attachments model
     */
    public $Attachments = NULL;

    /*
     * default config key
     */
    public $config = [
        [
            'alias' => 'Attachments',
            'type' => 'hasOne',
            'form_field' => 'file_upload'
        ]
    ];

    /*
     * intialize function
     */
    public function initialize(array $config) {

        // set config
        if(!empty($config))
            $this->config = $config;


        $str_con = $this->_table->defaultConnectionName();
        $conn = ConnectionManager::get($str_con);

        // auto table
        $this->Attachments = TableRegistry::get ( 'Attachments', [
            'table' => 'attachments',
            'connection' => $conn
        ] );

        $this->Attachments->addBehavior ( 'Timestamp' );

        $assoc = [ ];
        foreach ( $this->config as $conf ) {
            $key = $conf ['alias'];

            // hasOne, hasMany
            // $this->_table->addAssociations (
            $assoc [$conf ['type']] [$key] = [

                'className' => 'Attachments',
                'foreignKey' => 'foreign_key',
                'dependent' => true,
                'conditions' => [
                    $key . '.model' => $this->_table->registryAlias (),
                    $key . '.tag' => $key
                ]
            ]
            ;
            // );
        }

        $this->_table->addAssociations ( $assoc );

        // $this->Attachments = TableRegistry::get ( 'Attachments', [
        // 'table' => $this->config ['table']
        // ] );

        // $this->Attachments->addBehavior ( 'Timestamp' );

        // // hasOne, hasMany
        // $this->_table->addAssociations ( [
        // $this->config ['type'] => [
        // $this->config ['alias'] => [
        // 'className' => 'Attachments',
        // 'foreignKey' => 'foreign_key',
        // 'dependent' => true,
        // 'conditions' => [
        // $this->config ['alias'] . '.model' => $this->_table->registryAlias ()
        // ]
        // ]
        // ]
        // ] );

        parent::initialize ( $config );
    }

    /*
     * save file
     */
    public function afterSave(Event $event, EntityInterface $entity) {
// 		 debug($entity);
        // debug($this->config);
        // exit;
        $originalEntity = $entity->getOriginalValues();
        foreach ( $this->config as $conf ) {
            $key = $conf ['alias'];

            if (isset ( $entity [$conf ['form_field']] )) {
                $files = $entity [$conf ['form_field']];

                $entities = [];
// 				if($conf ['type'] == 'hasMany') {
                foreach ($files as $f){
                    if(!is_array($f)){
                        if(!is_uploaded_file ($files['tmp_name']))
                            continue;

                        if(isset($originalEntity[$conf['form_field']]) && isset($originalEntity[$conf['form_field']]['id'])) {

                            $file_id = $originalEntity[$conf['form_field']]['id'];
                            $entities[] = $this->createEntityFile($files, $entity->id, $key, $file_id);

                        } else {

                            $entities[] = $this->createEntityFile($files, $entity->id, $key);

                        }

                        break;
                    }
                    else{
                        if(!is_uploaded_file ($f['tmp_name']))
                            continue;
                        $entities[] = $this->createEntityFile($f,$entity->id, $key) ;
                    }
                }
// 				}
// 				debug($entities);exit;
// 				$file_up = $entity [$conf ['form_field']];
                //debug(count($file_up)); continue;

// 				$file = $this->Attachments->newEntity ();

// 				$file->file_name = $file_up ['name'];
// 				$file->file_path = '';
// 				$file->file_size = $file_up ['size'];
// 				$file->file_type = $file_up ['type'];
// 				$file->tag = $key;
// 				$file->model = $this->_table->registryAlias ();
// 				$file->foreign_key = $entity->{$conf ['bind_key']};
// 				$file->file_content = file_get_contents ( $file_up ['tmp_name'] );
                if($entities){
                    if (! $this->Attachments->saveMany ( $entities )) {
                        return false;
                    }
                    if(!empty($originalFilesIds)) {
                        $this->Attachments->deleteAll(['id IN' => $originalFilesIds]);
                    }
                }
            }
        }
        //exit;
        // if (isset ( $entity [$this->config ['form_field']] )) {

        // $file_up = $entity [$this->config ['form_field']];

        // $file = $this->Attachments->newEntity ();

        // $file->file_name = $file_up ['name'];
        // $file->file_path = '';
        // $file->file_size = $file_up ['size'];
        // $file->file_type = $file_up ['type'];
        // $file->tag = $this->config ['alias'];
        // $file->model = $this->_table->registryAlias ();
        // $file->foreign_key = $entity->{$this->config ['bind_key']};
        // $file->file_content = file_get_contents ( $file_up ['tmp_name'] );

        // if (! $this->Attachments->save ( $file )) {
        // return false;
        // }
        // }
        // exit;
        return $entity;
    }

    private function createEntityFile($file_up, $fk, $tag, $file_id = null){
        if($file_id) {
            $file = $this->Attachments->get($file_id);
        } else {
            $file = $this->Attachments->newEntity ();
        }

        $file->file_name = $file_up ['name'];
        $file->file_path = '';
        $file->file_size = $file_up ['size'];
        $file->file_type = $file_up ['type'];
        $file->tag = $tag;
        $file->model = $this->_table->registryAlias ();
        $file->foreign_key = $fk;
        $file->file_content = file_get_contents ( $file_up ['tmp_name'] );
        return $file;
    }

    /*
     * delete file
     */
    public function deleteFile($file_id){
        return $this->Attachments->deleteAll(['id' => $file_id, 'model'=>$this->_table->registryAlias ()]);
    }

    /*
     * delete many files
     */
    public function deleteAllFiles($id, $tag=null){
        $where['foreign_key'] = $id;
        $where['model'] = $this->_table->registryAlias ();
        if($tag)
            $where['tag'] = $tag;

        return $this->Attachments->deleteAll($where);
    }

}