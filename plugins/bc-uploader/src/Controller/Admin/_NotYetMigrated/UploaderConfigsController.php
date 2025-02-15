<?php
// TODO : コード確認要
return;
/**
 * baserCMS :  Based Website Development Project <https://basercms.net>
 * Copyright (c) baserCMS Users Community <https://basercms.net/community/>
 *
 * @copyright       Copyright (c) baserCMS Users Community
 * @link            https://basercms.net baserCMS Project
 * @package         Uploader.Controller
 * @since           baserCMS v 3.0.10
 * @license         https://basercms.net/license/index.html
 */

/**
 * ファイルアップローダーコントローラー
 *
 * @package         Uploader.Controller
 */
class UploaderConfigsController extends AppController
{
    /**
     * クラス名
     *
     * @var        string
     * @access    public
     */
    public $name = 'UploaderConfigs';
    /**
     * モデル
     *
     * @var        array
     * @access    public
     */
    public $uses = ['Plugin', 'BcUploader.UploaderConfig'];
    /**
     * コンポーネント
     *
     * @var        array
     * @access    public
     */
    public $components = ['BcAuth', 'Cookie', 'BcAuthConfigure'];
    /**
     * サブメニューエレメント
     *
     * @var    array
     * @access    public
     */
    public $subMenuElements = ['uploader'];

    /**
     * [ADMIN] アップローダー設定
     *
     * @return    void
     * @access    public
     */
    public function admin_index()
    {

        $this->setTitle(__d('baser', 'アップローダープラグイン設定'));
        if (!$this->request->getData()) {
            $this->request = $this->request->withData('UploaderConfig',  $this->UploaderConfig->findExpanded());
        } else {
            $this->UploaderConfig->set($this->request->getData());
            if ($this->UploaderConfig->validates()) {
                $this->UploaderConfig->saveKeyValue($this->request->getData());
                $this->BcMessage->setInfo(__d('baser', 'アップローダー設定を保存しました。'));
                $this->redirect(['action' => 'index']);
            } else {
                $this->BcMessage->setError(__d('baser', '入力エラーです。内容を修正してください。'));
            }
        }
    }
}
