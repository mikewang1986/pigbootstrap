<?php 
/**
 *  数据模型基类
 */
bpBase::loadSysClass('db_factory', '', 0);
class model {
	//数据库配置
	protected $db_config = '';
	//调用数据库的配置项
	protected $db_setting = 'default';
	//数据库连接
	protected $db = '';
	//数据表名
	protected $table_name = '';
	//表前缀
	public  $db_tablepre = '';
	
	public function __construct() {
		if (!$this->db_config) {
			$this->db_config=loadConfig('db');
		}
		if (!isset($this->db_config[$this->db_setting])) {
			$this->db_setting = 'default';
		}
		$this->table_name = $this->db_config[$this->db_setting]['tablepre'].$this->table_name;
		
		$this->db_tablepre = $this->db_config[$this->db_setting]['tablepre'];
		$this->db = db_factory::get_instance($this->db_config)->get_database($this->db_setting);
	}
		
	/**
	 * 执行sql查询
	 * @param $where 		查询条件[例`name`='$name']
	 * @param $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
	 * @param $limit 		返回结果范围[例：10或10,10 默认为空]
	 * @param $order 		排序方式	[默认按数据库默认方式排序]
	 * @param $group 		分组方式	[默认为空]
	 * @param $key          返回数组按键名排序
	 * @return array		查询结果集数组
	 */
	final public function select($where = '', $data = '*', $limit = '', $order = '', $group = '', $key='') {
		if (is_array($where)) $where = $this->sqls($where);
		return $this->db->select($data, $this->table_name, $where, $limit, $order, $group, $key);
	}
	/**
	 * 执行sql查询
	 *
	 * @param string $sql
	 * @param $key          返回数组按键名排序
	 * @return array		查询结果集数组
	 */
	final public function selectBySql($sql,$key = '') {
		return $this->db->selectBySql($sql,$key = '');
	}

	/**
	 * 查询多条数据并分页
	 * @param $where
	 * @param $order
	 * @param $page
	 * @param $pagesize
	 * @return unknown_type
	 */
	final public function listinfo($where = '', $order = '', $page = 1, $pagesize = 20, $key='',$urlrule = '',$array = array()) {
		$where = to_sqls($where);
		$this->number = $this->count($where);
		if($page > 0){
			$page = max(intval($page), 1);
			$offset = $pagesize*($page-1);
			$this->pages = pages($this->number, $page, $pagesize, $urlrule, $array);
		}
		$array = array();
		if ($this->number > 0){
			if($page > 0){
				return $this->select($where, '*', "$offset, $pagesize", $order, '', $key);
			}else{
				return $this->select($where, '*', '' ,$order, '', $key);
			}
		} else {
			return array();
		}
	}
	final public function get_all($data = '*',$from='',$where = '',$order = '',$limit=''){
		if (!$from){
			$from=$this->table_name;
		}
		$sql='SELECT '.$data.' FROM '.$from;
		if ($where){
			$where = to_sqls($where);
			$sql.=' WHERE '.$where;
		}
		if ($order){
			$sql.=' ORDER BY '.$order;
		}
		if ($limit){
			$limitNums=explode(',',$limit);
			$sql.=' LIMIT '.intval($limitNums[0]).','.$limitNums[1];
		}
		$arr=$this->selectBySql($sql);
		return $arr;
	}
	final public function get_results($data = '*',$from='',$where = '',$order = '',$limit=''){
		$arr=$this->get_all($data,$from,$where,$order,$limit);
		return array2Objects($arr);
	}
	final public function get_resultsBySql($sql){
		$arr=$this->selectBySql($sql);
		return array2Objects($arr);
	}
	final public function get_resultsBySqlInArr($sql){
		$arr=$this->selectBySql($sql);
		return $arr;
	}
	/**
	 * 获取单条记录查询
	 * @param $where 		查询条件
	 * @param $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
	 * @param $order 		排序方式	[默认按数据库默认方式排序]
	 * @param $group 		分组方式	[默认为空]
	 * @return array/null	数据查询结果集,如果不存在，则返回空
	 */
	final public function get_one($where = '', $data = '*', $order = '', $group = '') {
		if (is_array($where)) $where = $this->sqls($where);
		return $this->db->get_one($data, $this->table_name, $where, $order, $group);
	}
	/**
	 * 获取变量
	 * @param $where 		查询条件
	 * @param $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
	 * @param $order 		排序方式	[默认按数据库默认方式排序]
	 * @param $group 		分组方式	[默认为空]
	 * @return array/null	数据查询结果集,如果不存在，则返回空
	 */
	final public function get_var($where = '', $data = '*', $order = '', $group = '') {
		$rt=$this->get_one($where,$data,$order,$group);
		return $rt[$data];
	}
	final public function get_varBySql($sql,$coumn) {
		$arr=$this->selectBySql($sql);
		return $arr[0][$coumn];
	}
	final public function get_row($where = '', $data = '*', $order = '', $group = '') {
		if (is_array($where)) $where = $this->sqls($where);
		$res=$this->db->get_one($data, $this->table_name, $where, $order, $group);
		$obj=null;
		if ($res){
			foreach ($res as $k=>$v){
				$obj->$k=$v;
			}
		}
		return $obj;
	}
	/**
	 * 直接执行sql查询
	 * @param $sql							查询sql语句
	 * @return	boolean/query resource		如果为查询语句，返回资源句柄，否则返回true/false
	 */
	final public function query($sql) {
		return $this->db->query($sql);
	}
	
	/**
	 * 执行添加记录操作
	 * @param $data 		要增加的数据，参数为数组。数组key为字段值，数组值为数据取值
	 * @param $return_insert_id 是否返回新建ID号
	 * @param $replace 是否采用 replace into的方式添加数据
	 * @return boolean
	 */
	final public function insert($data, $return_insert_id = false, $replace = false) {
		return $this->db->insert($data, $this->table_name, $return_insert_id, $replace);
	}
	
	/**
	 * 获取主键字段信息
	 * @return array
	 */
	final public function getPK(){
		return $this->db->getPK($this->table_name);
	}
	
	
	/**
	 * 执行更新记录操作
	 * @param $data 		要更新的数据内容，参数可以为数组也可以为字符串，建议数组。
	 * 						为数组时数组key为字段值，数组值为数据取值
	 * 						为字符串时[例：`name`='phpcms',`hits`=`hits`+1]。
	 *						为数组时[例: array('name'=>'phpcms','password'=>'123456')]
	 *						数组的另一种使用array('name'=>'+=1', 'base'=>'-=1');程序会自动解析为`name` = `name` + 1, `base` = `base` - 1
	 * @param $where 		更新数据时的条件,可为数组或字符串
	 * @return boolean
	 */
	final public function update($data, $where = '') {
		if (is_array($where)) $where = $this->sqls($where);
		return $this->db->update($data, $this->table_name, $where);
	}
	
	/**
	 * 执行删除记录操作
	 * @param $where 		删除数据条件,不允许为空。
	 * @return boolean
	 */
	final public function delete($where) {
		if (is_array($where)) $where = $this->sqls($where);
		return $this->db->delete($this->table_name, $where);
	}
	
	/**
	 * 计算记录数
	 * @param string/array $where 查询条件
	 */
	final public function count($where = '') {
		$r = $this->get_one($where, "COUNT(*) AS num");
		return $r['num'];
	}
	
	/**
	 * 将数组转换为SQL语句
	 * @param array $where 要生成的数组
	 * @param string $font 连接串。
	 */
	final public function sqls($where, $font = ' AND ') {
		if (is_array($where)) {
			$sql = '';
			foreach ($where as $key=>$val) {
				$whereStr = $this->parseWhereItem($this->parseKey($key),$val);
				$sql .= $sql ? " $font $whereStr " : " $whereStr";
			}
			return $sql;
		} else {
			return $where;
		}
	}
	protected function parseWhereItem($key,$val){
        $whereStr = '';
		$comparison = array('eq'=>'=','neq'=>'<>','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notlike'=>'NOT LIKE','like'=>'LIKE','in'=>'IN','notin'=>'NOT IN');
        if(is_array($val)){
            if(is_string($val[0])) {
                if(preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT)$/i',$val[0])) { // 比较运算
                    $whereStr .= $key.' '.$comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
                }elseif(preg_match('/^(NOTLIKE|LIKE)$/i',$val[0])){// 模糊查找
                    if(is_array($val[1])) {
                        $likeLogic  =   isset($val[2])?strtoupper($val[2]):'OR';
                        $likeStr    =   $comparison[strtolower($val[0])];
                        $like       =   array();
                        foreach ($val[1] as $item){
                            $like[] = $key.' '.$likeStr.' '.$this->parseValue($item);
                        }
                        $whereStr .= '('.implode(' '.$likeLogic.' ',$like).')';
                    }else{
                        $whereStr .= $key.' '.$comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
                    }
                }elseif('exp'==strtolower($val[0])){ // 使用表达式
                    $whereStr .= ' ('.$key.' '.$val[1].') ';
                }elseif(preg_match('/IN/i',$val[0])){ // IN 运算
                    if(isset($val[2]) && 'exp'==$val[2]) {
                        $whereStr .= $key.' '.strtoupper($val[0]).' '.$val[1];
                    }else{
                        if(is_string($val[1])) {
                             $val[1] =  explode(',',$val[1]);
                        }
                        $zone      =   implode(',',$this->parseValue($val[1]));
                        $whereStr .= $key.' '.strtoupper($val[0]).' ('.$zone.')';
                    }
                }elseif(preg_match('/BETWEEN/i',$val[0])){ // BETWEEN运算
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $whereStr .=  ' ('.$key.' '.strtoupper($val[0]).' '.$this->parseValue($data[0]).' AND '.$this->parseValue($data[1]).' )';
                }else{
                    throw_exception(L('_EXPRESS_ERROR_').':'.$val[0]);
                }
            }else {
                $count = count($val);
                if(in_array(strtoupper(trim($val[$count-1])),array('AND','OR','XOR'))) {
                    $rule   = strtoupper(trim($val[$count-1]));
                    $count  = $count -1;
                }else{
                    $rule   = 'AND';
                }
                for($i=0;$i<$count;$i++) {
                    $data = is_array($val[$i])?$val[$i][1]:$val[$i];
                    if('exp'==strtolower($val[$i][0])) {
                        $whereStr .= '('.$key.' '.$data.') '.$rule.' ';
                    }else{
                        $op = is_array($val[$i])?$comparison[strtolower($val[$i][0])]:'=';
                        $whereStr .= '('.$key.' '.$op.' '.$this->parseValue($data).') '.$rule.' ';
                    }
                }
                $whereStr = substr($whereStr,0,-4);
            }
        }else {
            //对字符串类型字段采用模糊匹配
            $whereStr .= $key.' = '.$this->parseValue($val);
        }
        return $whereStr;
    }
	
	/**
     * 字段名分析
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey(&$key) {
        return $key;
    }
	protected function parseValue($value) {
        if(is_string($value)) {
            $value =  '\''.$this->escapeString($value).'\'';
        }elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
            $value =  $this->escapeString($value[1]);
        }elseif(is_array($value)) {
            $value =  array_map(array($this, 'parseValue'),$value);
        }elseif(is_bool($value)){
            $value =  $value ? '1' : '0';
        }elseif(is_null($value)){
            $value =  'null';
        }
        return $value;
    }
	/**
     * SQL指令安全过滤
     * @access public
     * @param string $str  SQL字符串
     * @return string
     */
    public function escapeString($str) {
        return addslashes($str);
    }
	/**
	 * 获取最后数据库操作影响到的条数
	 * @return int
	 */
	final public function affected_rows() {
		return $this->db->affected_rows();
	}
	
	/**
	 * 获取数据表主键
	 * @return array
	 */
	final public function get_primary() {
		return $this->db->get_primary($this->table_name);
	}
	
	/**
	 * 获取表字段
	 * @param string $table_name    表名
	 * @return array
	 */
	final public function get_fields($table_name = '') {
		if (empty($table_name)) {
			$table_name = $this->table_name;
		} else {
			$table_name = $this->db_tablepre.$table_name;
		}
		return $this->db->get_fields($table_name);
	}
	
	/**
	 * 检查表是否存在
	 * @param $table 表名
	 * @return boolean
	 */
	final public function table_exists($table){
		return $this->db->table_exists($this->db_tablepre.$table);
	}
	
	/**
	 * 检查字段是否存在
	 * @param $field 字段名
	 * @return boolean
	 */
	public function field_exists($field) {
		$fields = $this->db->get_fields($this->table_name);
		return array_key_exists($field, $fields);
	}
	
	final public function list_tables() {
		return $this->db->list_tables();
	}
	/**
	 * 返回数据结果集
	 * @param $query （mysql_query返回值）
	 * @return array
	 */
	final public function fetch_array() {
		$data = array();
		while($r = $this->db->fetch_next()) {
			$data[] = $r;		
		}
		return $data;
	}
	
	/**
	 * 返回数据库版本号
	 */
	final public function version() {
		return $this->db->version();
	}
}