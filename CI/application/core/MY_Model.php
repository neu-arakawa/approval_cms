<?php

class MY_Model extends CI_Model
{
    protected $_table = null;                   // DBテーブル名（DBを使用しない場合はfalse）nullの場合はモデル名から自動的にセットされる
    protected $_title_field = 'title';          // 見出しとして使用するフィールド名
    protected $_entity_name = null;             // 扱うデータの名前（お知らせ、コラムなど）画面表示やログに使用される
                                                // 設定がなければコントローラに設定された_entity_nameの値が使用される
    // デフォルトの検索条件
    //
    // リクエスト値による検索条件の指定があった場合、
    // 下記に該当する場合は条件がマージされる。
    // where/where_in/where_not_in/like/not_like
    // 該当しない場合は置き換えされる。
    //
    // limitを設定すると、[20件 50件 100件 全件]などの
    // ユーザによるlimit選択機能が使用できなくなるので注意
    protected $_search_options = [
        'where' => [],
        'order_by' => [],
    ];
    // 例)
    // protected $_search_options = [
    //     'where' => ['title'=>'テスト', 'category'=>3],
    //     'where_in' => ['category' => [1,2,3]],
    //     'where_not_in' => ['category' => [1,2,3]],
    //     'like' => ['title' => 'テスト'],
    //     'not_like' => ['title' => 'テスト'],
    //     'from' => 'table1',
    //     'order_by' => ['modified'=>'ASC', 'id'=>'DESC'],
    //     'group_by' => ['title', 'category'],
    //     'having' => ['category >'=>3],
    //     'limit' => 20,
    //     'offset' => 10,
    //     'distinct' => true,
    //     'join' => [
    //         'table1' => [
    //             'cond' => 'table1.id = table2.id',
    //             'type' => 'left',
    //             'escape' => true,
    //         ],
    //     ],
    // ];

    // 検索条件定義(CakePHPのSearchPlugin風)
    // リクエスト値のキーと一致する定義があれば、
    // 送信された値を検索条件として組み立てる
    // (フィールド名) => ['type'=>(検索種類), 'field'=>(フィールド名の指定がある場合。文字列か配列)]
    protected $_search_fields = [];
    // 例)
    // protected $_search_fields = [
    //     'title' => ['type'=>'value'],                                    // 完全一致
    //     'title' => ['type'=>'not_value'],                                // 完全一致(否定)
    //     'title' => ['type'=>'value', 'field'=>['col1','col2']],          // 完全一致(複数フィールド)
    //     'title' => ['type'=>'like'],                                     // 部分一致
    //     'title' => ['type'=>'not_like'],                                 // 部分一致(否定)
    //     'title' => ['type'=>'like', 'field'=>['col1','col2']],           // 部分一致(複数フィールド)
    //     'title' => ['type'=>'like', 'before'=>true, 'after'=>false],     // 前方一致
    //     'title' => ['type'=>'like', 'before'=>false, 'after'=>true],     // 後方一致
    //     'title' => ['type'=>'value_in'],                                 // IN検索
    //     'title' => ['type'=>'not_value_in'],                             // IN検索(否定)
    //     'title' => ['type'=>'query', 'method'=>'_search_keyword', 'field'=>['col1','col2']], // 独自メソッド
    // ];

    // 検索用ソートキー定義
    // (ソートキー(POST/GETのsortで指定される名前)) => (ソート条件)
    protected $_search_sorts = [];
    // 例)
    // protected $_search_sorts = [
    //     // ソートの向きごとに定義する場合
    //     'modified' => [
    //         'ASC' => ['modified'=>'DESC', 'id'=>'DESC'],
    //         'DESC' => ['modified'=>'ASC', 'id'=>'DESC'],
    //     ],
    //     // ソートの向きごとに定義しない場合はASCの場合の条件として使用される。
    //     // DESCの場合は向きが自動的に反転される
    //     'created' => ['created'=>'DESC', 'id'=>'DESC'],
    // ];

    // CMS連携アップローダ設定
    protected $_uploader_model = '';            // アップロードサブディレクトリ名
    protected $_uploader_fields = [];           // アップロードに対応するフィールド名

    // 検索用リミット定義
    // （POST/GETのlimitパラメータで許される件数）
    // 無制限の場合は-1を設定
    protected $_search_limits = [20, 50, 100, -1];

    protected $_last_save_data = null;      // 最後に保存したデータ(保存時に自動で設定)
    public $preview_mode = false;           // プレビューモード(ONの場合、公開フラグなどの条件を無視して検索を行う)
    protected $_table_fields = [];            // カラム列のリスト

    // コンストラクタ
    public function __construct()
    {
        //parent::__construct();
        if ($this->_table === null) { // テーブル名がセットされていない
            $class_name = get_class($this);
            $this->_table = strtolower(substr($class_name, 0, strrpos($class_name, '_')));
        }
        if ($this->_table) {
            // DBテーブル列のリスト取得
            $this->_table_fields = $this->db->list_fields($this->_table);
        }
    }

    // テーブル列が存在するか判定する
    // $field: 判定するフィールド名
    protected function _field_exists($field) {
        if (empty($this->_table_fields)) return false;
        return in_array($field, $this->_table_fields);
    }

    // 最後に保存されたデータのタイトルを取得
    public function get_last_title()
    {
        if (empty($this->_title_field) || empty($this->_last_save_data[$this->_title_field])) return null;
        return $this->_last_save_data[$this->_title_field];
    }
    // 最後に保存されたデータのタイトルを取得
    public function get_last_data()
    {
        return $this->_last_save_data;
    }

    // limitのリストを取得する
    public function get_search_limits()
    {
        if (!empty($this->_search_limits)) {
            return $this->_search_limits;
        }
        return null;
    }

    // CMS連携アップローダモデル名を取得する
    public function get_uploader_model()
    {
        if (!empty($this->_uploader_model)) {
            return $this->_uploader_model;
        }
        return null;
    }

    // IDからデータを取得する
    // $id: 取得対象のID
    // $callbacks: true=コールバック有効、false=コールバック無効
    public function get_by_id($id, $callback=true)
    {
        // デフォルトの検索条件とマージして
        // 最終的な検索条件を生成
        $options = $this->_create_search_options(['where'=>["id"=>$id]]);
        $this->_build_query($options);
        // 検索実行
        $data = $this->db->get()->row_array();

        // after findのコール
        if (!empty($data)) {
            if ($callback && method_exists($this, '_after_find')) {
                $tmp = array($data);
                $tmp = $this->_after_find($tmp);
                if (!empty($tmp[0])) $data = $tmp[0];
            }
        }
        return $data;
    }

    // get_by_idの派生版
    public function get_by_etc($column, $value, $callback = true)
    {
        $options = $this->_create_search_options(['where' => [$column => $value]]);
        $this->_build_query($options);
        $data = $this->db->get()->row_array();

        // after findのコール
        if (!empty($data)) {
            if ($callback && method_exists($this, '_after_find')) {
                $tmp = array($data);
                $tmp = $this->_after_find($tmp);
                if (!empty($tmp[0])) $data = $tmp[0];
            }
        }
        return $data;
    }

    // 検索処理
    // $options: 検索データ（POST値とは別に、任意に条件を渡したい時用）
    // &$pager: ページネーションデータの参照
    // $auto: $this->_search_fieldsの定義に従い、POST/GETから検索条件を自動生成する
    // $callbacks: true=コールバック有効、false=コールバック無効
    public function search($options=[], &$pager=[], $auto=false, $callback=true)
    {
        $this->db->reset_query();

        if ($auto) { // 検索条件自動生成有効
            // $this->_search_fields の定義に基づいて
            // POST/GET値から検索条件の自動生成を行う
            $CI = get_instance();
            if (!empty($this->_search_fields)) { // 検索定義あり
                foreach ($this->_search_fields as $field => $opt) {
                    $req = $CI->input->post_get($field);
                    if (is_null($req) || (is_array($req) && count($req)===0) || (!is_array($req) && strlen($req)===0)) continue; // 入力がなければスキップ

                    // フィールドの定義があればそれを優先して使う
                    if (!empty($opt['field'])) $field = $opt['field'];
                    if (!is_array($field)) $field = array($field);
                    $not = strpos($opt['type'], 'not') === 0 ? true : false; // 否定指示の有無
                    switch ($opt['type']) {
                        case 'query': // 独自クエリ
                            // メソッドの定義がなければスキップ
                            if (empty($opt['method']) || !method_exists($this, $opt['method'])) break;
                            $where = $this->{$opt['method']}($req, $field);
                            if (!empty($where)) { // 検索条件生成成功
                                $options['where'][] = $where;
                            }
                            break;
                        case 'value': // 完全一致
                        case 'not_value':
                            $req = $this->db->escape($req);
                            $op = $not ? '<>' : '=';
                            foreach ($field as $f) {
                                $options['where'][] = "{$f} {$op} {$req}";
                            }
                            break;
                        case 'like': // 部分一致
                        case 'not_like':
                            $req = $this->db->escape_like_str($req);
                            $op = $not ? 'NOT LIKE' : 'LIKE';
                            $before = $after = '%';
                            if (isset($opt['before']) && $opt['before']===false) $after = '';
                            else if (isset($opt['after']) && $opt['after']===false) $before = '';
                            foreach ($field as $f) {
                                $options['where'][] = "{$f} {$op} '{$before}{$req}{$after}'";
                            }
                            break;
                        case 'value_in': // IN
                        case 'not_value_in':
                            if (!is_array($req)) break; // 配列でなければスキップ
                            foreach ($req as $k => $v) {
                                if (strlen($v)==0) {
                                    unset($req[$k]);
                                    continue;
                                }
                                $req[$k] = $this->db->escape($v);
                            }
                            $req = implode(',', $req);
                            $op = $not ? 'NOT IN' : 'IN';
                            foreach ($field as $f) {
                                $options['where'][] = "{$f} {$op} ({$req})";
                            }
                    }

                }
            }

            // ソート条件の取得
            if (!empty($this->_search_sorts)) {
                $sort_key = $CI->input->post_get('sort');
                $sort_dir = !empty($CI->input->post_get('direction')) ? strtoupper($CI->input->post_get('direction')) : '';
                if (empty($sort_dir) || !in_array($sort_dir, ['ASC','DESC'])) $sort_dir = 'ASC';

                if (!empty($this->_search_sorts[$sort_key])) { // ソート条件の指定あり
                    $def = $this->_search_sorts[$sort_key];
                    if (!empty($def[$sort_dir])) {
                        // ソートの向きごとに条件定義されていればそれを使用する
                        $def = $def[$sort_dir];
                    } else {
                        // ソートの向きがDESCの場合は、
                        // 標準の定義の向きを反転する
                        if ($sort_dir === 'DESC') {
                            foreach ($def as $field => $dir) {
                                if ($dir === 'ASC') $dir = 'DESC';
                                else $dir = 'ASC';
                                $def[$field] = $dir;
                            }
                        }
                    }
                    $options['order_by'] = $def;
                }
            }

            if (empty($options['limit'])) {
                // limit条件の取得
                $limit = null;
                if ($CI->input->post_get('limit') && !empty($this->_search_limits) && in_array($CI->input->post_get('limit'), $this->_search_limits)) {
                    // リクエスト値にlimitの指定があり、
                    // limitの候補の設定がある
                    $limit = $CI->input->post_get('limit');
                } else if (!empty($this->_search_options['limit'])) {
                    // 検索条件にlimitの指定がある場合
                    $limit = $this->_search_options['limit'];
                }
                $options['limit'] = $limit ? $limit : DEFAULT_DB_LIMIT;
            }

            // オフセットの取得
            $offset = $CI->input->post_get('offset');
            if (!$offset || !preg_match('/^\d+$/', $offset)) $offset = 0;
            $options['offset'] = $offset;

        }

        // デフォルトの検索条件とマージして
        // 最終的な検索条件を生成
        $options = $this->_create_search_options($options);
        $this->_build_query($options);

        // var_dump($this->db->get_compiled_select($this->_table));

        // 検索実行
        $results = $this->db->get()->result_array();
        $count = $this->db
                    ->query('SELECT FOUND_ROWS() AS count')
                    ->row()
                    ->count;

        // 検索結果の総数を取得する
        if ($callback && method_exists($this, '_after_find')) {
            // after findコールバックの実行
            $results = $this->_after_find($results);
        }

        // ページャ用データの作成
        $pager = [
            'count' => $count,
            'limit' => isset($options['limit']) ? $options['limit'] : null,
            'offset' => isset($options['offset']) ? $options['offset'] : null,
            'order_by' => isset($options['order_by']) ? $options['order_by']: null,
        ];

        return $results;
    }

    // データ名の設定
    public function fill_entity_name($name)
    {
        // モデルに既に設定されていない場合のみ設定
        if ($this->_entity_name === null) $this->_entity_name = $name;
    }

    // データを複製する
    // $id_or_data: 複製対象のIDまたはデータ
    // $atomic: true = トランザクションをこのメソッドで完結する
    //          false = 外部のメソッドでトランザクション処理を行う
    // $callbacks: true=コールバック有効、false=コールバック無効
    // 戻り値: 成功時=保存したデータのID 失敗時=false
    public function replicate($id_or_data, $atomic=true, $callback=true)
    {
        if (is_array($id_or_data)) $data = $id_or_data;
        else $data = $this->get_by_id($id_or_data);

        $save = $data;
        $save = $this->_create_replicate_data($save); // 複製用データ作成

        if ($atomic) $this->db->trans_begin();
        $this->_last_save_data = null;

        if ($callback && method_exists($this, '_before_save')) {
            // _before_saveコールバックが存在する
            $save = $this->_before_save($save, null, $data, 'replicate');
            if ($save === false) { // 処理がエラーの場合
                if ($atomic) $this->db->trans_rollback(); // 必要に応じてロークバック
                return false;
            }
        }

        // 複製実行
        $save_id = $this->db->insert($this->_table, $save) ? $this->db->insert_id() : false;
        $after_save_ret = true;
        if ($save_id && $callback && method_exists($this, '_after_save')) {
            // _after_saveコールバックが存在する
            $after_save_ret = $this->_after_save($save, $save_id, $data, 'replicate');
        }

        $after_replicate_ret = true;
        if ($save_id && $callback && method_exists($this, '_after_replicate')) {
            // _after_saveコールバックが存在する
            $after_replicate_ret = $this->_after_replicate($save, $data['id'], $save_id);
        }

        if ($save_id && $after_save_ret && $after_replicate_ret) { // 保存成功
            if ($atomic) $this->db->trans_commit(); // トランザクションコミット
            $this->_last_save_data = $save; // 保存データを一時記録

            log_message('debug', sprintf('%sの複製に成功しました。DATA:%s', $this->_entity_name, print_r($save,true)));
            return $save_id;
        } else { // 失敗
            if ($atomic) $this->db->trans_rollback(); // トランザクションロールバック

            log_message('error', sprintf('%sの複製に失敗しました。DATA:%s', $this->_entity_name, print_r($save,true)));
            return false;
        }
    }

    // データの一括複製
    // $ids: 複製対象の複数ID
    // $atomic: true = トランザクションをこのメソッドで完結する
    //          false = 外部のメソッドでトランザクション処理を行う
    public function replicate_all($ids, $atomic=true)
    {
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        if (!is_array($ids)) return false;

        if ($atomic) $this->db->trans_begin();

        $this->_last_save_data = null;

        $err_id = [];
        foreach ($ids as $id) {
            $ret = $this->replicate($id, false);
            if (!$ret) $err_id[] = $id; // 処理失敗
        }
        if (empty($err_id)) { // エラーなし
            if ($atomic) $this->db->trans_commit(); // トランザクションコミット

            $this->_last_save_data = $ids; // 保存データを一時記録
            log_message('debug', sprintf('%sの一括複製に成功しました。ID:%s', $this->_entity_name, print_r($ids,true)));
        } else { // 失敗
            if ($atomic) $this->db->trans_rollback(); // トランザクションロールバック

            log_message('error', sprintf('%sの一括複製に失敗しました。ID:%s 失敗したID:%s', $this->_entity_name, print_r($ids,true), print_r($err_id,true)));
        }
        return empty($err_id) ? true : false;
    }

    // データ削除処理
    // $id: 削除対象のID
    // $atomic: true = トランザクションをこのメソッドで完結する
    //          false = 外部のメソッドでトランザクション処理を行う
    // $callbacks: true=コールバック有効、false=コールバック無効
    public function delete($id, $atomic=true, $callback=true)
    {
        $data = $this->get_by_id($id);
        if (empty($data)) return false;

        if ($atomic) $this->db->trans_begin();
        $this->_last_save_data = null;

         if ($callback && method_exists($this, '_before_delete')) {
            // _before_deleteコールバックが存在する
            $ret = $this->_before_delete($id);
            if ($ret === false) { // 処理がエラーの場合
                if ($atomic) $this->db->trans_rollback(); // 必要に応じてロークバック
                return false;
            }
        }

        // 削除処理実行
        $ret = $this->db
                ->where('id', $id)
                ->delete($this->_table);
        $del_id = $ret ? $id : false;

        $after_delete_ret = true;
        if ($del_id && $callback && method_exists($this, '_after_delete')) {
            // _before_deleteコールバックが存在する
            $after_delete_ret = $this->_after_delete($id);
        }

        if ($del_id && $after_delete_ret) { // 削除成功
            if ($atomic) $this->db->trans_commit(); // トランザクションコミット
            $this->_last_save_data = $data; // 削除データを一時記録

            log_message('debug', sprintf('%sの削除に成功しました。DATA:%s', $this->_entity_name, print_r($data,true)));
        } else { // 削除失敗
            if ($atomic) $this->db->trans_rollback(); // トランザクションロールバック
            log_message('error', sprintf('%sの削除に失敗しました。DATA:%s', $this->_entity_name, print_r($data,true)));
        }
        return $del_id;
    }

    // データ一括削除処理
    // $ids: 削除対象の複数ID
    // $atomic: true = トランザクションをこのメソッドで完結する
    //          false = 外部のメソッドでトランザクション処理を行う
    public function delete_all($ids, $atomic=true)
    {
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        if (!is_array($ids)) return false;

        if ($atomic) $this->db->trans_begin();

        $err_id = [];
        foreach ($ids as $id) {
            $ret = $this->delete($id, false);
            if (!$ret) $err_id[] = $id; // 処理失敗
        }

        $this->_last_save_data = null;
        if (empty($err_id)) { // エラーなし
            if ($atomic) $this->db->trans_commit(); // トランザクションコミット

            $this->_last_save_data = $ids; // 保存データを一時記録
            log_message('debug', sprintf('%sの一括削除に成功しました。ID:%s', $this->_entity_name, print_r($ids,true)));
        } else { // 失敗
            if ($atomic) $this->db->trans_rollback(); // トランザクションロールバック

            log_message('error', sprintf('%sの一括削除に失敗しました。ID:%s 失敗したID:%s', $this->_entity_name, print_r($ids,true), print_r($err_id,true)));
        }
        return empty($err_id) ? true : false;
    }

    // 公開または非公開処理(公開フラグが存在しない場合は処理対象外)
    // $id: 処理対象のID
    // $flg: 公開フラグ
    // $atomic: true = トランザクションをこのメソッドで完結する
    //          false = 外部のメソッドでトランザクション処理を行う
    public function publish($id, $flg=true, $atomic=true)
    {
        // 公開フラグ列が存在しない場合はスキップ
        if (!$this->_field_exists('flg_publish')) return false;

        $data = $this->get_by_id($id);
        if (empty($data)) return false;

        if ($atomic) $this->db->trans_begin();
        $this->_last_save_data =null;

        // 保存データの作成
        $save = [
            'flg_publish' => $flg ? 1 : 0,
            'modified' => NOW,
        ];

        // 保存処理実行
        $save_id = $this->db->update($this->_table, $save, ['id' => $id]) ? $id : false;

        $action = $flg ? '公開' : '非公開';
        if ($save_id) { // 保存成功
            if ($atomic) $this->db->trans_commit(); // トランザクションコミット

            $this->_last_save_data = array_merge($data, $save); // 保存データを一時記録
            log_message('debug', sprintf('%sの%sに成功しました。ID:%s', $this->_entity_name, $action, $id));
        } else { // 失敗
            if ($atomic) $this->db->trans_rollback(); // トランザクションロールバック

            log_message('error', sprintf('%sの%sに失敗しました。ID:%s', $this->_entity_name, $action, $id));
        }
        return $save_id;
    }

    // データ一括公開処理
    // $ids: 公開対象の複数ID
    // $atomic: true = トランザクションをこのメソッドで完結する
    //          false = 外部のメソッドでトランザクション処理を行う
    public function publish_all($ids, $flg=true, $atomic=true)
    {
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        if (!is_array($ids)) return false;

        if ($atomic) $this->db->trans_begin();

        $err_id = [];
        foreach ($ids as $id) {
            $ret = $this->publish($id, $flg, false);
            if (!$ret) $err_id[] = $id; // 処理失敗
        }

        $this->_last_save_data = null;
        if (empty($err_id)) { // エラーなし
            if ($atomic) $this->db->trans_commit(); // トランザクションコミット

            $this->_last_save_data = $ids; // 保存データを一時記録
            log_message('debug', sprintf('%sの一括公開に成功しました。ID:%s', $this->_entity_name, print_r($ids,true)));
        } else { // 失敗
            if ($atomic) $this->db->trans_rollback(); // トランザクションロールバック

            log_message('error', sprintf('%sの一括公開に失敗しました。ID:%s 失敗したID:%s', $this->_entity_name, print_r($ids,true), print_r($err_id,true)));
        }
        return empty($err_id) ? true : false;
    }

    // データ保存処理
    // $data: 保存データ
    // $id: 更新対象のID(新規の場合はNULL)
    // $publish: 公開保存するかどうか
    // $atomic: true = トランザクションをこのメソッドで完結する
    //          false = 外部のメソッドでトランザクション処理を行う
    // $callbacks: true=コールバック有効、false=コールバック無効
    // 戻り値: 成功時=保存したデータのID 失敗時=false
    public function save($data, $id=null, $publish=true, $atomic=true, $callback=true)
    {
        if (empty($this->_table)) return false;

        $mode = !empty($id) ? 'update' : 'insert';

        $save = $data;
        $save = $this->_create_save_data($save, $mode, $publish); // 保存データの作成
        if( in_array('temporary_blob', $this->_table_fields) ) $save['temporary_blob'] = '';

        if ($atomic) $this->db->trans_begin();
        $this->_last_save_data = null;

        if ($callback & method_exists($this, '_before_save')) {
            // _before_saveコールバックが存在する
            $save = $this->_before_save($save, $id, $data, 'edit');
            if ($save === false) { // 処理がエラーの場合
                if ($atomic) $this->db->trans_rollback(); // 必要に応じてロークバック
                return false;
            }
        }
        if ($mode === 'update') { // 更新
            $save_id = $this->db->update($this->_table, $save, ['id' => $id]) ? $id : false;
            $action = '更新';
        } else { // 新規登録
            $save_id = $this->db->insert($this->_table, $save) ? $this->db->insert_id() : false;
            $action = '新規登録';
        }
        $after_save_ret = true;
        if ($save_id && $callback && method_exists($this, '_after_save')) {
            // _after_saveコールバックが存在する
            $after_save_ret = $this->_after_save($save, $save_id, $data, 'edit');
        }

        if ($save_id && $after_save_ret) { // 保存成功
            if ($atomic) $this->db->trans_commit(); // トランザクションコミット

            $this->_last_save_data = $save; // 保存データを一時記録
            log_message('debug', sprintf('%sの%sに成功しました。DATA:%s', $this->_entity_name, $action, print_r($save,true)));

            return $save_id;
        } else { // 失敗
            if ($atomic) $this->db->trans_rollback(); // トランザクションロールバック
            log_message('error', sprintf('%sの%sに失敗しました。DATA:%s', $this->_entity_name, $action, print_r($save,true)));

            return false;
        }
    }

    // 保存用データの作成
    // created, modified等の付加、その他
    // データに含まれない項目は更新対象に含めないためにその項目自体を設定しない
    // 0や空文字列、NULLなどは意図的に設定保存したいと判断してそのまま項目を使用する
    // $data: 元となるデータ
    // $mode: 新規登録=insert 更新=update
    // $publish: 公開フラグ
    protected function _create_save_data($data=null, $mode='insert', $publish=true)
    {
        $fields = $this->_table_fields;
        $ret = [];
        if (is_object($data)) $data = json_decode(json_encode($data), true); // オブジェクトを配列に変換
        foreach ($fields as $field) {
            if ($field === 'id') continue;

            if (($field === 'created' && $mode === 'insert') || $field === 'modified') {
                $value = NOW;
            } else if (isset($data[$field])) { // データに値がセットされている
                $value = $data[$field];
            } else { // データに値がセットされていない
                continue;
            }

            if ((!is_array($value) && strlen($value)) ||
                (is_array($value) && count($value))) $ret[$field] = $value;
            else $ret[$field] = null;
        }
        if ($this->_field_exists('flg_publish') && is_bool($publish)) { // 公開フラグカラムが存在する場合
            $ret['flg_publish'] = $publish ? 1 : 0;
        }
        return $ret;
    }

    // 複製用のデータの作成
    // idの削除
    // created,modifiedの時刻更新
    // 公開フラグのオフ
    // タイトルに相当するデータには「のコピー」を追加
    protected function _create_replicate_data($data)
    {
        $fields = $this->_table_fields;
        $remove_list = [
            'id',
        ];
        $ret = [];
        if (is_object($data)) $data = json_decode(json_encode($data), true); // オブジェクトを配列に変換
        foreach ($fields as $field) {
            if (in_array($field, $remove_list)) continue;
            if ($field === 'created' || $field === 'modified') $value = NOW;
            else if (isset($data[$field])) $value = $data[$field];
            else continue;

            if (is_array($value) && count($value) > 0) $ret[$field] = $value;
            else if (strlen($value)) $ret[$field] = $value;
            else $ret[$field] = null;
        }
        if ($this->_field_exists('flg_publish')) { // 公開フラグカラムがあれば、0に設定する
            $ret['flg_publish'] = 0;
        }
        if (!empty($this->_title_field) &&
            !empty($data[$this->_title_field]) &&
            in_array($this->_title_field, $fields)){
            // タイトルに相当するカラムがあれば、識別のためにタイトルを変更する
            $ret[$this->_title_field] = $data[$this->_title_field]. 'のコピー';
        }
        return $ret;
    }

    // 設定を渡してクエリビルドする
    // $options: 検索条件配列
    // $reset: true=クエリビルダのリセット
    protected function _build_query($options, $reset=false)
    {
        if ($reset) $this->db->reset_query(); // 必要があればクエリリセット

        // FROM
        $table = $this->_table;
        if (!empty($options['from'])) {
            $table = $options['from'];
        }
        $this->db->from($table);

        // JOIN
        if (!empty($options['join']) && is_array($options['join'])) {
            foreach ($options['join'] as $table => $arr) {
                if (!is_array($arr)) continue;  // 配列でなければスキップ
                if (empty($arr['cond'])) continue; // 条件は必須
                if (empty($arr['type'])) $arr['type'] = '';
                if (empty($arr['escape'])) $arr['escape'] = null;
                $this->db->join($table, $arr['cond'], $arr['type'], $arr['escape']);
            }
        }

        // WHERE
        if (!empty($options['where'])) {
            if (is_array($options['where'])) {
                foreach ($options['where'] as $k => $v) {
                    if (is_int($k)) {
                        $this->db->where($v);
                    } else {
                        $k = $this->_comp_table_name($k);
                        $this->db->where([$k => $v]);
                    }
                }
            } else {
                $this->db->where($options['where']);
            }

        }

        // WHERE IN
        if (!empty($options['where_in']) && is_array($options['where_in'])) {
            foreach ($options['where_in'] as $field => $value) {
                if (is_int($field) && is_array($value)) {
                    foreach ($value as $f => $v) {
                        $f = $this->_comp_table_name($f);
                        $this->db->where_in($f, $v);
                    }
                } else {
                    $field = $this->_comp_table_name($field);
                    $this->db->where_in($field, $value);
                }

            }
        }

        // WHERE NOT IN
        if (!empty($options['where_not_in']) && is_array($options['where_not_in'])) {
            foreach ($options['where_not_in'] as $field => $value) {
                if (is_int($field) && is_array($value)) {
                    foreach ($value as $f => $v) {
                        $f = $this->_comp_table_name($f);
                        $this->db->where_not_in($f, $v);
                    }
                } else {
                    $field = $this->_comp_table_name($field);
                    $this->db->where_not_in($field, $value);
                }

            }
        }

        // LIKE
        if (!empty($options['like']) && is_array($options['like'])) {
            foreach ($options['like'] as $field => $value) {
                if (is_int($field) && is_array($value)) {
                    foreach ($value as $f => $v) {
                        $f = $this->_comp_table_name($f);
                        $this->db->like($f, $v);
                    }
                } else {
                    $field = $this->_comp_table_name($field);
                    $this->db->like($field, $value);
                }
            }
        }

        // NOT LIKE
        if (!empty($options['not_like']) && is_array($options['not_like'])) {
            foreach ($options['not_like'] as $field => $value) {
                if (is_int($field) && is_array($value)) {
                    foreach ($value as $f => $v) {
                        $f = $this->_comp_table_name($f);
                        $this->db->not_like($f, $v);
                    }
                } else {
                    $field = $this->_comp_table_name($field);
                    $this->db->not_like($field, $value);
                }
            }
        }

        // GROUP BY
        if (!empty($options['group_by'])) {
            $group = $this->_comp_table_name($options['group_by']);
            $this->db->group_by($group);
        }

        // HAVING
        if (!empty($options['having'])) {
            $this->db->having($options['having']);
        }

        // ORDER BY
        if (!empty($options['order_by'])) {
            if (is_array($options['order_by'])) {
                foreach ($options['order_by'] as $field => $dir) {
                    $field = $this->_comp_table_name($field);
                    $this->db->order_by($field, $dir);
                }
            } else {
                $this->db->order_by($options['order_by']);
            }
        }

        // LIMIT
        if (!empty($options['limit'])) {
            $this->db->limit($options['limit']);
        }

        // OFFSET
        if (!empty($options['offset'])) {
            $this->db->offset($options['offset']);
        }

        // SELECT
        if (!empty($options['select'])) {
            $this->db->select('SQL_CALC_FOUND_ROWS '. $options['select'], false);
        } else {
            $this->db->select('SQL_CALC_FOUND_ROWS *', false);
        }

        // DISTINCT
        if (!empty($options['distinct'])) {
            $this->db->distinct();
        }
    }

    // DBカラム名に対してテーブル名を補完する
    protected function _comp_table_name($field)
    {
        if (is_array($field)) {
            foreach ($field as $idx => $f) {
                $field[$idx] = $this->_comp_table_name($f);
            }
        } else {
            if (!preg_match('/^[^\.]+\.[^\.]+$/', $field)) {
                // テーブル名が明示的に指定されていなければ補完する
                return $this->_table.'.'.$field;
            }
        }
        return $field;
    }

    // find()用のdatasouce option の取得
    // デフォルトで設定されている条件とマージする
    // その際、where条件は統合、その他については上書きとする
    protected function _create_search_options($options=[])
    {
        if (empty($this->_search_options)) return $options;
        if (empty($options)) $options = [];

        $default = $this->_search_options;
        if ($this->preview_mode) {
            // プレビューモード
            // 取得条件をクリアし、
            // 無条件でプレビューできるようにする
            if (!empty($default['where'])) unset($default['where']);
            if (!empty($default['where_in'])) unset($default['where_in']);
            if (!empty($default['where_not_in'])) unset($default['where_not_in']);
            if (!empty($default['like'])) unset($default['like']);
            if (!empty($default['not_like'])) unset($default['not_like']);
        }

        // デフォルトの条件と結合する
        // where / where_in / where_not_in / like / not_like は
        // 結合ではなく、上書きとする
        $merges = ['where', 'where_in', 'where_not_in', 'like', 'not_like'];
        foreach ($options as $key => $val) {
            if (!in_array($key, $merges) && !empty($default[$key])) unset($default[$key]);
        }
        $opt = $this->_merge_options($default, $options);
        if(isset($opt['limit']) && $opt['limit'] < 0) unset($opt['limit']);    // limitが負の値なら制限しない
        return $opt;
    }

    // 検索オプションのマージ（CakePHPのHash::mergeと同じ）
    protected function _merge_options(array $data, $merge) {
        $args = array_slice(func_get_args(), 1);
        $return = $data;

        foreach ($args as &$curArg) {
            $stack[] = array((array)$curArg, &$return);
        }
        unset($curArg);

        while (!empty($stack)) {
            foreach ($stack as $curKey => &$curMerge) {
                foreach ($curMerge[0] as $key => &$val) {
                    if (!empty($curMerge[1][$key]) && (array)$curMerge[1][$key] === $curMerge[1][$key] && (array)$val === $val) {
                        $stack[] = array(&$val, &$curMerge[1][$key]);
                    } elseif ((int)$key === $key && isset($curMerge[1][$key])) {
                        $curMerge[1][] = $val;
                    } else {
                        $curMerge[1][$key] = $val;
                    }
                }
                unset($stack[$curKey]);
            }
            unset($curMerge);
        }
        return $return;
    }

    // 公開済みかどうか
    protected function is_published ($data) {
        $flg_publish = isset($data['flg_publish']) ? (bool)$data['flg_publish'] : true;
        $start_date = empty($data['start_date']) ? null : $data['start_date'];
        $end_date = empty($data['end']) ? null : $data['end'];

        return $flg_publish && during_term($start_date, $end_date);
    }

    // 保存前処理
    // オーバーライドして使用
    // $data: 保存対象データ
    // $id: 保存対象のID(新規、および複製の場合はnull)
    // $raw: 指定された生のデータ(複製時は複製元データ)
    // $context: 呼び出し元の処理(edit / replicate)
    // 戻り値： 加工した保存対象データ or エラー時はfalse
    protected function _before_save($data, $id=null, $raw=[], $context='')
    {
        return $data;
    }

    // 保存後処理
    // リレーションテーブル操作については_before_saveではなくこちらに記述
    // オーバーライドして使用
    // $data: 保存対象データ
    // $id: 保存対象のID
    // $raw: 指定された生のデータ
    // $context: 呼び出し元の処理(edit / replicate)
    // 戻り値： 成功時=true 失敗時=false
    protected function _after_save($data, $id, $raw=[], $context='')
    {

        if (CMS_UPLOADER_ENABLED && !empty($this->_uploader_fields) && empty($raw['id'])) {
            // アップローダの設定があり、かつ新規作成の場合

            // アップロード一時ディレクトリ名を取得
            $new_id = $this->my_session->{UPLOADER_SESSION_ROOT_NAME.'.new_id'};
            if ($new_id) {
                $save = [];
                $upload_dir = preg_replace('#'.preg_quote(DOCUMENT_ROOT).'#', '', CMS_UPLOADER_UPLOAD_DIR);
                $upload_dir = preg_replace('/\/+$/', '', $upload_dir); // 末尾のスラッシュを取り除く
                $src_path = $upload_dir. '/'. $this->_uploader_model. '/'. $new_id;
                $dest_path = $upload_dir. '/'. $this->_uploader_model. '/'. $id;

                foreach ($this->_uploader_fields as $f) {
                    if (empty($data[$f])) continue;

                    $v = $data[$f];
                    // 新規作成時の一時的なパスを確定後のパスに置換する
                    $v = preg_replace('#'.preg_quote($src_path).'#', $dest_path, $v);
                    $save[$f] = $v;
                }

                $this->db->update($this->_table, $save, ['id' => $id]);
                // var_dump($save);

                $api_url = FULL_BASE_URL. UPLOADER_DIR.'?'.
                    http_build_query([
                        'api' => 'cmsmovedir',
                        'sub_dir' => $this->_uploader_model,
                        'src_id'=> $new_id,
                        'dest_id'=> $id,
                        'secret_key'=>CMS_UPLOADER_API_SECRET_KEY,
                    ]);
                @file_get_contents($api_url);
            }
        }

        return true;
    }

    // 複製後処理
    // オーバーライドして使用
    // $data: 保存データ
    // $src_id: 複製元のID
    // $dest_id: 複製先のID
    // 戻り値： 成功時=true 失敗時=false
    protected function _after_replicate($data, $src_id, $dest_id)
    {
        if ( CMS_UPLOADER_ENABLED && !empty($this->_uploader_fields)) {
            // アップローダの設定がある

            // アップロード一時ディレクトリ名を取得

            $save = [];
            $upload_dir = preg_replace('#'.preg_quote(DOCUMENT_ROOT).'#', '', CMS_UPLOADER_UPLOAD_DIR);
            $upload_dir = preg_replace('/\/+$/', '', $upload_dir); // 末尾のスラッシュを取り除く
            $src_path = $upload_dir. '/'. $this->_uploader_model. '/'. $src_id;
            $dest_path = $upload_dir. '/'. $this->_uploader_model. '/'. $dest_id;

            foreach ($this->_uploader_fields as $f) {
                if (empty($data[$f])) continue;

                $v = $data[$f];
                // 新規作成時の一時的なパスを確定後のパスに置換する
                $v = preg_replace('#'.preg_quote($src_path).'#', $dest_path, $v);
                $save[$f] = $v;
            }
            if (!empty($save)) {
                $this->db->update($this->_table, $save, ['id' => $dest_id]);
                // var_dump($save);

                $api_url = FULL_BASE_URL. UPLOADER_DIR.'?'.
                    http_build_query([
                        'api' => 'cmsreplicate',
                        'sub_dir' => $this->_uploader_model,
                        'src_id'=> $src_id,
                        'dest_id'=> $dest_id,
                        'secret_key'=>CMS_UPLOADER_API_SECRET_KEY,
                    ]);
                @file_get_contents($api_url);
            }
        }

        return true;
    }


    // 削除前処理
    // $id: 削除対象のID
    // 戻り値: 成功=true 失敗=false
    protected function _before_delete($id)
    {
        return true;
    }

    // 削除後処理
    // $id: 削除対象のID
    // 戻り値: 成功=true 失敗=false
    protected function _after_delete($id)
    {
        if (CMS_UPLOADER_ENABLED && !empty($this->_uploader_fields)) {
            // アップローダの設定がある

            $api_url = FULL_BASE_URL. UPLOADER_DIR.'?'.
                http_build_query([
                    'api' => 'cmsdelete',
                    'sub_dir' => $this->_uploader_model,
                    'del_id'=> $id,
                    'secret_key'=>CMS_UPLOADER_API_SECRET_KEY,
                ]);
            @file_get_contents($api_url);
        }

        return true;
    }

    // データ取得後処理
    // $data: 処理対象データ
    protected function _after_find($data)
    {
        if (!empty($data)) {
            foreach ($data as $idx => $arr) {
                $val = $this->_append_data($arr);
                if (!empty($val)) {
                    $data[$idx] = $val;
                }
            }
        }
        return $data;
    }

    // ファイル取得処理時に
    // 各レコードへ付加情報を追加する
    // publish: 公開期間と公開フラグを元に公開状態かどうか判定する
    // publish_date_str: 表示用の公開期間テキスト
    protected function _append_data($data)
    {
        if( is_admin() && !empty($data['temporary_blob'])){
            $id = $data['id'];
            $data = unserialize($data['temporary_blob']);
            $data['id'] = $id;
            $data['is_temporary'] = true;
        }

        // 公開状態の計算
        if ($this->_field_exists('start_date') && $this->_field_exists('end_date')) {
            // 公開期間データが存在する
            $st = !empty($data['start_date']) ? date('Y-m-d H:i', strtotime($data['start_date'])) : null;
            $ed = !empty($data['end_date']) ? date('Y-m-d H:i', strtotime($data['end_date'])) : null;
            $flg = !empty($data['flg_publish']) ? true : false;
            $data['during_term'] = during_term($st,$ed);
            $data['published'] = $data['during_term'] && $flg;
            $data['start_date'] = $st;
            $data['end_date'] = $ed;

            // 表示用の公開期間テキスト生成
            $date_disp_str = [];
            if (!empty($st)) {
                $date_disp_str[] = date('Y年n月j日 H時i分', strtotime($st));
            }
            $date_disp_str[] = ' 〜 ';
            if (!empty($ed)) {
                $st_tm = strtotime($st);
                $ed_tm = strtotime($ed);

                if (date('Y',$st_tm)==date('Y',$ed_tm)) {
                    $date_disp_str[] = date('n月j日 H時i分', $ed_tm);
                } else {
                    $date_disp_str[] = date('Y年n月j日 H時i分', $ed_tm);
                }
            }
            if (count($date_disp_str)===1) {
                $data['publish_date_str'] = '期限設定なし';
            } else {
                $data['publish_date_str'] = implode('<br>', $date_disp_str);
            }

        } else {
            $data['published'] = true;
            $data['during_term'] = true;
            $data['publish_date_str'] = null;
        }

        return $data;
    }

    // 検索処理用
    // キーワード検索
    // $data: 入力データ
    // $fields: 検索対象のフィールド名
    private function _search_keyword($data, $fields)
    {
        // キーワードをスペースで分割
        $arr = preg_split( '/[\s　]+/u', $data, 5, PREG_SPLIT_NO_EMPTY );

        $conditions = [];
        if( $arr ){
            foreach( $arr as $word ){
                $conv_words = convert_keyword( $word );

                $or = [];
                foreach( $fields as $f ){
                    foreach( $conv_words as $conv_word ){
                        $conv_word = $this->db->escape_like_str($conv_word);
                        $or[] = "$f LIKE '%{$conv_word}%'";
                    }
                }
                $conditions[] = '('. implode(' OR ', $or). ')';
            }
        }
        return implode(' AND ',$conditions);
    }

    // 検索処理用
    // キーワード検索
    // $data: 入力データ
    private function _search_published($data)
    {
        $now = date('Y-m-d H:i:s', NOW_TIME);
        $cond = "flg_publish=1 AND (
                    (start_date IS NULL AND end_date IS NULL) OR
                    (start_date IS NULL AND end_date >= '{$now}') OR
                    (start_date <= '{$now}' AND end_date IS NULL) OR
                    (start_date <= '{$now}' AND end_date >= '{$now}')
                )";
        if (empty($data)) {
            $cond = "NOT ($cond)";
        }
        return $cond;
    }

    // 検索処理用
    // 下書き検索
    private function _search_draft($data)
    {
        return "flg_publish = 0";
    }

    // 一時保存
    public function save_temporary($data, $id, $atomic=true, $callback=true)
    {
        if ($callback & method_exists($this, '_before_save')) {
            // _before_saveコールバックが存在する
            $data = $this->_before_save($data, $id, $data, 'temporary');
            if ($data === false) { // 処理がエラーの場合
                if ($atomic) $this->db->trans_rollback(); // 必要に応じてロークバック
                return false;
            }
        }
        $save = [];
        $save['modified'] = NOW;
        $save['temporary_blob'] = serialize($data);
        if( empty($id) ){
            $save_id = $this->db->insert($this->_table, $save) ? $this->db->insert_id() : false;
        }
        else {
            $save_id = $this->db->update($this->_table, $save, ['id' => $id]) ? $id : false;
        }

        $after_replicate_ret = true;
        if ($save_id && $callback && method_exists($this, '_after_save')) {
            // _after_saveコールバックが存在する
            $after_save_ret = $this->_after_save($save, $save_id, $data, 'temporary');
        }

        if ($save_id && $after_save_ret && $after_replicate_ret) { // 保存成功
            if ($atomic) $this->db->trans_commit(); // トランザクションコミット
            $this->_last_save_data = $data; 
            return $save_id;
        }

        return false;

    }
    // 一時保存
    public function clear_temporary($id)
    {
        $save = [];
        $save['modified'] = NOW;
        $save['temporary_blob'] = null;
        $this->_last_save_data = $this->get_by_id($id);
        return $this->db->update($this->_table, $save, ['id' => $id]) ? $id : false;
    }

    public function get_data_for_preview($id_md5){
        $this->preview_mode = true;
        $results = $this->search([
            'where' => [
                'md5(id) = "'.$id_md5.'" '
            ]
        ]);
        if( empty($results[0]) ) return [];
        $data = $results[0];
        if( !empty($data['temporary_blob']) ){
            $id = $data['id'];
            $data = unserialize($data['temporary_blob']);
            $data['id'] = $id;
            $data['is_temporary'] = true;
        }
        return $data;
    }


}
