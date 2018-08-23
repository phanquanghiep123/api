<?php 
namespace App\Core;

use App\Http\Controllers\Controller;
use DB;

class APIBackend extends Controller
{
    public $_TOKEN = "";
    public $_ROLE = "";
    public $_HEADERS = [];
    public $_HOST = "";
    public $_AUTH = [];
    public $_DATA = [];
    public $_HEADER = "";
    public $_FOOTER = "";
    public $_MODULE = null;
    public $_PAGINGNUMBER = 30;
    public $_ROLNAME = [
        "add" => ["add", "create", "save", "update", "store"],
        "delete" => ["delete", "remove", "destroy"],
        "view" => ["show", "view", "review", "index"],
        "update" => ["edit", "update"]
    ];
    private $_COMMONROUTE = [];
    private $_VIEWADD = "/create";
    private $_VIEWEDIT = "/edit";
    private $_VIEWLIST = "/lists";
    private $_VIEWADDAJAX = "/ajaxaddform";
    private $_VIEWEDITAJAX = "/ajaxeditform";
    public $_MODEL = null;
    public $_TABLE = null;
    public function __construct()
    {
        $this->_DATA["total"]      = 0;
        $this->_DATA["page"]       = 0;
        $this->_DATA["limit"]      = $this->_PAGINGNUMBER;
        $this->_DATA["response"]   = null;
        $this->_DATA["message"]    = null;
        $this->_DATA["status"]     = 0;
        $this->_DATA["public_url"] = asset("");
        $this->_HEADERS = $this->Customgetallheaders(); 
        $this->_TOKEN = @$this->_HEADERS["Authorization"];
        $this->_HOST = @$this->_HEADERS["Host"];
        $this->_AUTH = \App\Models\Users::where("token", "=", $this->_TOKEN)->first();
        if($this->_AUTH->is_sys != 1) return false;
    }
    public function checkAuth()
    { 
        if($this->_AUTH->is_sys != 1) return false;
        return $this->checkToken();
    }
    public function getAuth()
    {
        return $this->_AUTH;
    }
    public function checkToken()
    {
        $token = md5($this->_AUTH["id"] . $this->_HOST);
        if ($token == $this->_TOKEN) return true;
        $this->_DATA["message"] = "Authorization is not match Validate";
        return false;
    }
    public function _GetTableColumns($table)
    {
        return DB::getSchemaBuilder()->getColumnListing($table);
    }

    private $key_slug = 0;
    private $table_tree = "";
    private $table_tree_select = "";
    function slug($table, $colum, $name, $where = null)
    {
        $record = DB::table($table);
        if ($where != null) {
            $record->where($where);
        }
        $record = $record->select($colum)->get();
        $slug = $this->gen_slug($name);
        $arg_slug = array();
        if ($record->count() > 0) {
            foreach ($record as $key => $value) {
                $arg_slug[] = $value->{$colum};
            }
            $key_slug = $this->check_slug($slug, $arg_slug, $this->key_slug, $slug);
            if ($key_slug != 0) {
                $slug = $slug . "-" . $key_slug;
            }
        }
        $this->key_slug = 0;
        return $slug;
    }
    function check_slug($slug, $arg, $key_slug, $default)
    {
        if (in_array($slug, $arg) == true) {
            $this->key_slug++;
            $this->check_slug($default . "-" . $this->key_slug, $arg, $this->key_slug, $default);
        }
        return $this->key_slug;
    }
    function gen_slug($str)
    {
        $a = array("à", "á", "ạ", "ả", "ã", "â", "ầ", "ấ", "ậ", "ẩ", "ẫ", "ă", "ằ", "ắ", "ặ", "ẳ", "ẵ", "è", "é", "ẹ", "ẻ", "ẽ", "ê", "ề", "ế", "ệ", "ể", "ễ", "ì", "í", "ị", "ỉ", "ĩ", "ò", "ó", "ọ", "ỏ", "õ", "ô", "ồ", "ố", "ộ", "ổ", "ỗ", "ơ", "ờ", "ớ", "ợ", "ở", "ỡ", "ù", "ú", "ụ", "ủ", "ũ", "ư", "ừ", "ứ", "ự", "ử", "ữ", "ỳ", "ý", "ỵ", "ỷ", "ỹ", "đ", "À", "Á", "Ạ", "Ả", "Ã", "Â", "Ầ", "Ấ", "Ậ", "Ẩ", "Ẫ", "Ă", "Ằ", "Ắ", "Ặ", "Ẳ", "Ẵ", "È", "É", "Ẹ", "Ẻ", "Ẽ", "Ê", "Ề", "Ế", "Ệ", "Ể", "Ễ", "Ì", "Í", "Ị", "Ỉ", "Ĩ", "Ò", "Ó", "Ọ", "Ỏ", "Õ", "Ô", "Ồ", "Ố", "Ộ", "Ổ", "Ỗ", "Ơ", "Ờ", "Ớ", "Ợ", "Ở", "Ỡ", "Ù", "Ú", "Ụ", "Ủ", "Ũ", "Ư", "Ừ", "Ứ", "Ự", "Ử", "Ữ", "Ỳ", "Ý", "Ỵ", "Ỷ", "Ỹ", "Đ", " ", "ö", "ü");
        $b = array("a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "e", "e", "e", "e", "e", "e", "e", "e", "e", "e", "e", "i", "i", "i", "i", "i", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o ", "o", "o", "o", "o", "o", "u", "u", "u", "u", "u", "u", "u", "u", "u", "u", "u", "y", "y", "y", "y", "y", "d", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A ", "A", "A", "A", "A", "A", "E", "E", "E", "E", "E", "E", "E", "E", "E", "E", "E", "I", "I", "I", "I", "I", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O ", "O", "O", "O", "O", "O", "U", "U", "U", "U", "U", "U", "U", "U", "U", "U", "U", "Y", "Y", "Y", "Y", "Y", "D", "-", "o", "u");
        return strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'), array('', '-', ''), str_replace($a, $b, $str)));
    }
    function gen_name($str)
    {
        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'Ð', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', '?', '?', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', '?', '?', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', '?', 'O', 'o', 'O', 'o', 'O', 'o', 'Œ', 'œ', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'Š', 'š', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Ÿ', 'Z', 'z', 'Z', 'z', 'Ž', 'ž', '?', 'ƒ', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', '?', '?', '?', '?', '?', '?');
        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
        return strtolower(preg_replace(array('/[^a-zA-Z0-9 -.]/', '/[ -]+/', '/^-|-$/'), array('', '-', ''), str_replace($a, $b, $str)));
    }
    private function Customgetallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
