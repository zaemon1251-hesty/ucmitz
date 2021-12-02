<?php
/**
 * baserCMS :  Based Website Development Project <https://basercms.net>
 * Copyright (c) baserCMS Users Community <https://basercms.net/community/>
 *
 * @copyright       Copyright (c) baserCMS Users Community
 * @link            https://basercms.net baserCMS Project
 * @package         Baser.View
 * @since           baserCMS v 0.1.0
 * @license         https://basercms.net/license/index.html
 */

/**
 * @var object $dblogs
 * @var BcAppView $this
 */
?>
<?php if ($dblogs->count()): ?>
  <div class="bca-update-log">
    <?php $this->BcBaser->element('pagination', ['modules' => 4, 'options' => ['url' => ['action' => 'ajax_index']]]) ?>
    <ul class="clear bca-update-log__list">
      <?php foreach($dblogs as $dblog): ?>
        <li class="bca-update-log__list-item">
          <span class="date">
            <?php echo $this->BcTime->format($dblog->created, 'YYYY-MM-dd') ?>
          </span>
          <small><?php echo $this->BcTime->format($dblog->created, 'HH:mm:ss') ?>&nbsp;
            <?php if ($dblog->user): ?>
                <?php echo '[' . h($dblog->user->name) . ']' ?>
            <?php endif ?>
          </small><br/>
          <?php echo nl2br(h($dblog->message)) ?></li>
      <?php endforeach; ?>
    </ul>
    <?php $this->BcBaser->element('list_num', ['nums' => [5, 10, 20, 50]]) ?>
    <?php if ($this->BcBaser->isAdminUser()): ?>
      <div class="submit clear bca-update-log__delete">
        <?php echo $this->BcForm->postButton(__d('baser', 'ログを全て削除'), ['action' => 'delete_all'], [
            'class' => 'btn-gray submit-token bca-btn',
            'data-bca-btn-type' => 'delete',
            'confirm' => __d('baser', '最近の動きのログを削除します。いいですか？')
        ]) ?>
      </div>
    <?php endif ?>
  </div>
<?php endif ?>
