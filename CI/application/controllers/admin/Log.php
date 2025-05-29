<?php
class Log extends Admin_Controller
{
    const ENTITY_NAME = '操作履歴';
    const VIEW_ICON = '<i class="fas fa-book"></i>';

    protected $_exec_methods = [                        // 実行を許可するメソッド名
        'index',
    ];

    public function index()
    {
        $user = $this->my_session->admin;
        if ( $user->flg_admin || in_array(USER_PERM_LOG, $user->flg_acl ?? [])  ) { // 管理権限なし
            parent::index();
        }
        else {
            $this->_forbidden();
        }
    }
}
