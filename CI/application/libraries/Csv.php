<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class Csv 
{
    public $delimiter = ',';
    public $enclosure = '"';
    private $characters = null;
    private $rfc4180_regexp = null;
    private $rfc4180_enclosure = null;
    public  $escape = "\\";
    private $records = [];

    public function __construct()
    {
        setlocale(LC_ALL, 'ja_JP.UTF-8');
    }
 
    public function __destruct()
    {}
    
    private $bom = false;
    public function set_bom($flg){
       $this->bom = $flg;
    }
    public function test(){
        return 'text';
    }
    public function writer_create_from_string(){
        $this->characters = preg_quote($this->delimiter, '/').'|'.preg_quote($this->enclosure, '/');
        $this->rfc4180_regexp = '/[\s|'.$this->characters.']/x';
        $this->rfc4180_enclosure = $this->enclosure.$this->enclosure;
        return $this;
    }
    public function to_string($records)
    {
        $_data = '';
        foreach ($records as $record) {
            foreach ($record as &$field) {
                $field = (string) $field;
                if (1 === preg_match($this->rfc4180_regexp, $field)) {
                    $field = $this->enclosure.str_replace($this->enclosure, $this->rfc4180_enclosure, $field).$this->enclosure;
                }
            }
            unset($field);
            $_data .= implode($this->delimiter, $record)."\n";
        }
        return ($this->bom? "\xEF\xBB\xBF":''). $_data;
    }

    public function reader_create_from_path($filepath, $input_encoding = 'UTF-8'){
        if( file_exists($filepath) === false ) return false;
        $csv_text = file_get_contents($filepath);
        $this->encoding = mb_detect_encoding($csv_text,['CP932', 'UTF-8']);
        if( $input_encoding !== 'UTF-8') {
            $csv_text = mb_convert_encoding($csv_text, 'UTF-8', $input_encoding);
        }
        $rows = new SplTempFileObject();
        $rows->fwrite($csv_text);
        $rows->setFlags(
                    // SplFileObject::DROP_NEW_LINE |
                    SplFileObject::SKIP_EMPTY |
                    SplFileObject::READ_CSV); 
        $rows->rewind();
        $rows->setCsvControl($this->delimiter, $this->enclosure, $this->escape);
        
        $records = []; 
        foreach ($rows as $row) {
            $row = array_map(function($val){
                $val = trim($val);
                $val = preg_replace('/\A[\x00\s]++|[\x00\s]++\z/u', '', $val);
                return $val;
            }, $row);
            if( empty($row) ) continue;
            $records[] = $row;
        }
        $this->recodes = $records;
        return $this;
    }

    protected $header_offset = 0;
    public function set_header_offset($offset)
    {
        $this->header_offset = $offset;
    }

    public function get_header()
    {
        if(empty($this->recodes[$this->header_offset]))
            return false;

        return $this->recodes[$this->header_offset];
    }
    
    public function get_records()
    {
        $recodes = $this->recodes;
        for ($i = 0; $i <= $this->header_offset; $i++) array_shift($recodes);
        return $recodes;
    }
}
