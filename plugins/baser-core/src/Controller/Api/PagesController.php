<?php
/**
 * baserCMS :  Based Website Development Project <https://basercms.net>
 * Copyright (c) NPO baser foundation <https://baserfoundation.org/>
 *
 * @copyright     Copyright (c) NPO baser foundation
 * @link          https://basercms.net baserCMS Project
 * @since         5.0.0
 * @license       https://basercms.net/license/index.html MIT License
 */

namespace BaserCore\Controller\Api;

use BaserCore\Utility\BcUtil;
use BaserCore\Annotation\Note;
use BaserCore\Annotation\NoTodo;
use BaserCore\Annotation\Checked;
use BaserCore\Annotation\UnitTest;
use BaserCore\Service\PagesServiceInterface;
use Cake\ORM\Exception\PersistenceFailedException;

/**
 * Class PagesController
 * @uses PagesController
 */
class PagesController extends BcApiController
{

    /**
     * 固定ページ一覧取得
     * @param PagesServiceInterface $Pages
     * @checked
     * @noTodo
     * @unitTest
     */
    public function index(PagesServiceInterface $Pages)
    {
        $this->request->allowMethod('get');
        $this->set([
            'pages' => $this->paginate($Pages->getIndex())
        ]);
        $this->viewBuilder()->setOption('serialize', ['pages']);
    }

    /**
     * 固定ページ取得
     * @param PagesServiceInterface $Pages
     * @param int $id
     * @checked
     * @noTodo
     * @unitTest
     */
    public function view(PagesServiceInterface $Pages, $id)
    {
        $this->request->allowMethod('get');
        $this->set([
            'pages' => $Pages->get($id)
        ]);
        $this->viewBuilder()->setOption('serialize', ['pages']);
    }

    /**
     * 固定ページ登録
     * @param PagesServiceInterface $Pages
     * @checked
     * @unitTest
     * @noTodo
     */
    public function add(PagesServiceInterface $Pages)
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        try {
            $page = $Pages->create($this->request->getData());
            $message = __d('baser', '固定ページ「{0}」を追加しました。', $page->content->title);
            $this->set("page", $page);
            $this->set('content', $page->content);
        } catch (\Cake\ORM\Exception\PersistenceFailedException $e) {
            $page = $e->getEntity();
            $message = __d('baser', "入力エラーです。内容を修正してください。\n");
            $this->set(['errors' => $page->getErrors()]);
            $this->setResponse($this->response->withStatus(400));
        }
        $this->set(['message' => $message]);
        $this->viewBuilder()->setOption('serialize', ['message', 'content', 'errors']);
    }

    /**
     * 固定ページ削除
     * @param PagesServiceInterface $Pages
     * @param int $id
     * @checked
     * @noTodo
     * @unitTest
     */
    public function delete(PagesServiceInterface $Pages, $id)
    {
        $this->request->allowMethod(['delete']);
        $page = $Pages->get($id);
        try {
            if ($Pages->delete($id)) {
                $message = __d('baser', '固定ページ: {0} を削除しました。', $page->content->title);
            }
        } catch (\Cake\ORM\Exception\PersistenceFailedException $e) {
            $page = $e->getEntity();
            $message = __d('baser', 'データベース処理中にエラーが発生しました。') . $e->getMessage();
        }
        $this->set([
            'message' => $message,
            'page' => $page
        ]);
        $this->viewBuilder()->setOption('serialize', ['page', 'message']);
    }

    /**
     * 固定ページ情報編集
     * @param PagesServiceInterface $pages
     * @param int $id
     * @checked
     * @noTodo
     * @unitTest
     */
    public function edit(PagesServiceInterface $pages, $id)
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        try {
            $page = $pages->update($pages->get($id), $this->request->getData());
            $message = __d('baser', '固定ページ 「{0}」を更新しました。', $page->content->title);
        } catch (\Cake\ORM\Exception\PersistenceFailedException $e) {
            $this->setResponse($this->response->withStatus(400));
            $page = $e->getEntity();
            $message = __d('baser', '入力エラーです。内容を修正してください。');
        }
        $this->set([
            'message' => $message,
            'page' => $page,
            'errors' => $page->getErrors(),
        ]);
        $this->viewBuilder()->setOption('serialize', ['page', 'message', 'errors']);
    }

    /**
     * コピー
     * @param PagesServiceInterface $pages
     * @checked
     * @noTodo
     * @unitTest
     */
    public function copy(PagesServiceInterface $pages)
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        try {
            $page = $pages->copy($this->request->getData());
            $message = __d('baser', '固定ページのコピー「%s」を追加しました。', $page->content->title);
        } catch (PersistenceFailedException $e) {
            $this->setResponse($this->response->withStatus(500));
            $page = $e->getEntity();
            $message = __d('baser', 'コピーに失敗しました。データが不整合となっている可能性があります。');
        }
        $this->set([
            'message' => $message,
            'page' => $page,
            'content' => $page->content,
            'errors' => $page->getErrors(),
        ]);
        $this->viewBuilder()->setOption('serialize', ['page', 'content', 'message', 'errors']);
    }

}
