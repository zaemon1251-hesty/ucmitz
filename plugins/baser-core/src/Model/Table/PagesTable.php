<?php
/**
 * baserCMS :  Based Website Development Project <https://basercms.net>
 * Copyright (c) NPO baser foundation <https://baserfoundation.org/>
 *
 * @copyright     Copyright (c) NPO baser foundation
 * @link          https://basercms.net baserCMS Project
 * @since         5.0.0
 * @license       http://basercms.net/license/index.html MIT License
 */

namespace BaserCore\Model\Table;

use ArrayObject;
use Cake\ORM\Table;
use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use BaserCore\Utility\BcUtil;
use Cake\Event\EventInterface;
use Cake\Validation\Validator;
use BaserCore\Model\Entity\Page;
use Cake\Datasource\EntityInterface;
use BaserCore\Utility\BcContainerTrait;
use BaserCore\Event\BcEventDispatcherTrait;
use BaserCore\Model\Behavior\BcSearchIndexManagerInterface;
use BaserCore\Annotation\Checked;
use BaserCore\Annotation\UnitTest;
use BaserCore\Annotation\NoTodo;
use BaserCore\Annotation\Note;

/**
 * Class PagesTable
 */
class PagesTable extends Table implements BcSearchIndexManagerInterface
{
    /**
     * Trait
     */
    use BcEventDispatcherTrait;
    use BcContainerTrait;

    /**
     * 検索テーブルへの保存可否
     *
     * @var boolean
     */
    public $searchIndexSaving = true;

    /**
     * 公開WebページURLリスト
     * キャッシュ用
     *
     * @var mixed
     */
    protected $_publishes = -1;

    /**
     * WebページURLリスト
     * キャッシュ用
     *
     * @var mixed
     */
    protected $_pages = -1;

    /**
     * 最終登録ID
     * モバイルページへのコピー処理でスーパークラスの最終登録IDが上書きされ、
     * コントローラーからは正常なIDが取得できないのでモバイルページへのコピー以外の場合だけ保存する
     *
     * @var int
     */
    private $__pageInsertID = null;

    /**
     * Initialize
     *
     * @param array $config テーブル設定
     * @return void
     * @checked
     * @unitTest
     * @noTodo
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->addBehavior('BaserCore.BcContents');
        $this->addBehavior('BaserCore.BcSearchIndexManager');
        $this->addBehavior('Timestamp');
        $this->Sites = TableRegistry::getTableLocator()->get('BaserCore.Sites');
        $this->Contents = TableRegistry::getTableLocator()->get('BaserCore.Contents');
    }

    /**
     * Validation Default
     *
     * @param Validator $validator
     * @return Validator
     * @checked
     * @noTodo
     * @unitTest
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator->setProvider('page', 'BaserCore\Model\Validation\PageValidation');

        $validator
        ->integer('id')
        ->numeric('id', __d('baser', 'IDに不正な値が利用されています。'), 'update')
        ->requirePresence('id', 'update');

        $validator
        ->scalar('contents')
        ->allowEmptyString('contents', null)
        ->maxLengthBytes('contents', 64000, __d('baser', '本稿欄に保存できるデータ量を超えています。'))
        ->add('contents', 'custom', [
            'rule' => ['phpValidSyntax'],
            'provider' => 'page',
            'message' => __d('baser', '本稿欄でPHPの構文エラーが発生しました。')
        ])
        ->add('contents', [
            'containsScript' => [
                'rule' => ['containsScript'],
                'provider' => 'bc',
                'message' => __d('baser', '本稿欄でスクリプトの入力は許可されていません。')
            ]
        ]);

        $validator
        ->scalar('draft')
        ->allowEmptyString('draft', null)
        ->maxLengthBytes('draft', 64000, __d('baser', '本稿欄に保存できるデータ量を超えています。'))
        ->add('draft', 'custom', [
            'rule' => ['phpValidSyntax'],
            'provider' => 'page',
            'message' => __d('baser', '本稿欄でPHPの構文エラーが発生しました。')
        ])
        ->add('draft', [
            'containsScript' => [
                'rule' => ['containsScript'],
                'provider' => 'bc',
                'message' => __d('baser', '本稿欄でスクリプトの入力は許可されていません。')
            ]
        ]);

        return $validator;
    }

    /**
     * afterSave
     *
     * @param  EventInterface $event
     * @param  EntityInterface $entity
     * @param  ArrayObject $options
     * @return void
     * @checked
     * @noTodo
     * @unitTest
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        // 検索用テーブルに登録
        if ($this->searchIndexSaving) {
            if (!empty($entity->content) && empty($entity->content->exclude_search)) {
                $this->saveSearchIndex($this->createSearchIndex($entity));
            } else {
                $this->deleteSearchIndex($entity->id);
            }
        }
    }

    /**
     * 検索用データを生成する
     *
     * @param Page $page
     * @return array|false
     * @checked
     * @unitTest
     * @noTodo
     */
    public function createSearchIndex($page)
    {
        if (!isset($page->id) || !isset($page->content->id)) {
            return false;
        }
        $content = $page->content;
        if (!isset($content->publish_begin)) {
            $content->publish_begin = '';
        }
        if (!isset($content->publish_end)) {
            $content->publish_end = '';
        }

        if (!$content->title) {
            $content->title = Inflector::camelize($content->name);
        }
        $modelId = $page->id;

        $host = '';
        $url = $content->url;
        if (!$content->site) {
            $site = $this->Sites->get($content->site_id);
        } else {
            $site = $content->site;
        }
        if ($site->useSubDomain) {
            $host = $site->alias;
            if ($site->domainType == 1) {
                $host .= '.' . BcUtil::getMainDomain();
            }
            $url = preg_replace('/^\/' . preg_quote($site->alias, '/') . '/', '', $url);
        }
        $detail = $page->contents;
        $description = '';
        if (!empty($content->description)) {
            $description = $content->description;
        }
        return [
            'model_id' => $modelId,
            'type' => __d('baser', 'ページ'),
            'content_id' => $content->id,
            'site_id' => $content->site_id,
            'title' => $content->title,
            'detail' => $description . ' ' . $detail,
            'url' => $url,
            'status' => $content->status,
            'publish_begin' => $content->publish_begin,
            'publish_end' => $content->publish_end
        ];
    }

    /**
     * ページデータをコピーする
     *
     * 固定ページテンプレートの生成処理を実行する必要がある為、
     * Content::copy() は利用しない
     *
     * @param array $postData
     * @return Page $result
     * @checked
     * @unitTest
     * @noTodo
     */
    public function copy($postData)
    {
        $page = $this->get($postData['entityId'], ['contain' => ['Contents' => ['Sites']]]);
        $oldPage = $page;
        $oldSiteId = $page->content->site_id;
        unset($postData['entityId'], $postData['contentId'], $page->id, $page->content->id, $page->created, $page->modified);
        foreach ($postData as $key => $value) {
            $page->content->{Inflector::underscore($key)} = $value;
        }
        // EVENT Page.beforeCopy
        $event = $this->dispatchLayerEvent('beforeCopy', [
            'page' => $page,
        ]);
        if ($event !== false) {
            $page = $event->getResult() === true? $event->getData('page') : $event->getResult();
            unset($event);
        }
        if (!is_null($postData['siteId']) && $postData['siteId'] !== $oldSiteId) {
            $page->content->parent_id = $this->Contents->copyContentFolderPath($page->content->url, $page->content->site_id);
        }
        $newPage = $this->patchEntity($this->newEmptyEntity(), $page->toArray());
        $page = $this->saveOrFail($newPage);
        if ($page->content->eyecatch) {
            $content = $this->Contents->renameToBasenameFields($page->content, true);
            $page->content = $content;
        }
        // EVENT Page.afterCopy
        $event = $this->dispatchLayerEvent('afterCopy', [
            'page' => $page,
            'oldPage' => $oldPage,
        ]);
        if ($event !== false) {
            $page = $event->getResult() === true? $event->getData('page') : $event->getResult();
        }
        return $page;
    }
}
