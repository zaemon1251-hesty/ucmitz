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

namespace BaserCore\Test\TestCase\Controller\Admin;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * BaserCore\Controller\DashboardController Test Case
 */
class DashboardControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.BaserCore.Dblogs',
        'plugin.BaserCore.Users',
        'plugin.BaserCore.UsersUserGroups',
        'plugin.BaserCore.UserGroups',
    ];

    public $autoFixtures = false;

    /**
     * set up
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures('Dblogs', 'Users', 'UsersUserGroups', 'UserGroups');

        $config = $this->getTableLocator()->exists('Users')? [] : ['className' => 'BaserCore\Model\Table\UsersTable'];
        $Users = $this->getTableLocator()->get('Users', $config);
        $this->session(['AuthAdmin' => $Users->get(1)]);
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex()
    {
        $this->get('/baser/admin/');
        $this->assertResponseOk();
        $this->assertResponseContains('baserCMSニュース');
        $this->assertResponseContains('コンテンツ情報');
        $this->assertResponseContains('最近の動き');
    }
}
