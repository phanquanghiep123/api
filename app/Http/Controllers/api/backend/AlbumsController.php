<?php

namespace App\Http\Controllers\api\backend;

use Illuminate\Http\Request;
use App\Core\APIBackend;

class AlbumsController extends APIBackend
{
    public function gets (Request $request){
        $page   = $request->page ? $request->page : 0;
        $where  = []; 
        $offset = $page * $this->_PAGINGNUMBER;
        $this->_MODEL = new \App\Models\Albums();
        if($where != null){
            $this->_MODEL->where($where);
        }
        $count  = $this->_MODEL->count();
        $results = $this->_MODEL->offset($offset)->limit($this->_PAGINGNUMBER)->get();
        $this->_DATA["total"]   = $count;
        $this->_DATA["page"]    = $page;
        $this->_DATA["limit"]   = $this->_PAGINGNUMBER;
        $this->_DATA["respone"] = $results;
        $this->_DATA["status"]  = 1;
        return response()->json($this->_DATA,200);
    }
}
